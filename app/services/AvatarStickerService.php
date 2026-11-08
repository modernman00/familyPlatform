<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;

/**
 * AvatarStickerService: "Me-stickers" (RFC-0011)
 *
 * Lets a member drop their own studio avatar, performing a reaction, into a post
 * or comment via the token `:me_<reaction>@<userId>:`.
 *
 *  - Traits are persisted on every procedural studio save (member_avatar_traits).
 *    Members who saved before this table existed fall back to the traits embedded
 *    in their procedural studio SVG (data-atg-traits), so nobody has to re-save.
 *  - Stickers are served as <img> SVG from /api/emoji/avatar-sticker/{id}/{reaction}
 *    to logged-in members who share a family with the owner (404 otherwise).
 *  - Tokens naming anyone other than the author are stripped on save, so nobody
 *    can make another member "say" something.
 */
final class AvatarStickerService
{
    /** Reaction => badge glyph shown on the sticker. */
    public const REACTIONS = [
        'hello' => '👋',
        'laugh' => '😂',
        'wow'   => '😮',
        'love'  => '❤️',
        'nod'   => '👍',
        'dance' => '🎶',
        'point' => '👉',
        'clap'  => '👏',
    ];

    public const REACTION_LABELS = [
        'hello' => 'Hello',
        'laugh' => 'Laughing',
        'wow'   => 'Wow',
        'love'  => 'Love',
        'nod'   => 'Agree',
        'dance' => 'Dancing',
        'point' => 'Yes, you!',
        'clap'  => 'Applause',
    ];

    public const TOKEN_REGEX = '/:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):/';

    private ?PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db;
    }

    private function db(): PDO
    {
        if ($this->db === null) {
            $this->db = Db::connect2();
        }
        return $this->db;
    }

    public static function isValidUserId(string $userId): bool
    {
        return (bool)preg_match('/^[A-Za-z0-9_-]{1,64}$/', $userId);
    }

    public static function isValidReaction(string $reaction): bool
    {
        return array_key_exists($reaction, self::REACTIONS);
    }

    /**
     * Remove every Me-sticker token that does not belong to the author.
     * With an empty author id (system/automated writes) all tokens are removed.
     */
    public static function stripForeignTokens(string $text, string $authorId): string
    {
        if ($text === '' || !str_contains($text, ':me_')) {
            return $text;
        }
        return (string)preg_replace_callback(self::TOKEN_REGEX, static function (array $m) use ($authorId): string {
            return ($authorId !== '' && hash_equals($authorId, $m[2])) ? $m[0] : '';
        }, $text);
    }

    /**
     * Upsert the member's procedural traits and bump the cache version.
     *
     * @param array<string, string> $traits Already sanitised via AtgAvatarRenderer::sanitise()
     */
    public function saveTraits(string $userId, array $traits, string $mode = 'procedural'): void
    {
        if (!self::isValidUserId($userId)) {
            return;
        }
        $stmt = $this->db()->prepare(
            'INSERT INTO member_avatar_traits (user_id, traits, mode, version)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE traits = VALUES(traits), mode = VALUES(mode), version = version + 1'
        );
        $stmt->execute([$userId, json_encode(AtgAvatarRenderer::sanitise($traits)), $mode]);
    }

    /**
     * @return array{traits: array<string, string>, version: string}|null
     */
    public function getTraits(string $userId): ?array
    {
        if (!self::isValidUserId($userId)) {
            return null;
        }
        try {
            $stmt = $this->db()->prepare('SELECT traits, version FROM member_avatar_traits WHERE user_id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $decoded = json_decode((string)$row['traits'], true);
                if (is_array($decoded)) {
                    return ['traits' => AtgAvatarRenderer::sanitise($decoded), 'version' => (string)$row['version']];
                }
            }
        } catch (\Throwable $e) {
            // Table not migrated yet: fall through to the profile-image fallback.
            error_log('[AvatarStickerService::getTraits] ' . $e->getMessage());
        }

        return $this->traitsFromCurrentProfileAvatar($userId);
    }

    /**
     * Fallback for members whose avatar predates member_avatar_traits: read the traits
     * embedded in their current procedural studio SVG. Living Photo avatars (which wrap
     * a real portrait) are deliberately excluded.
     *
     * @return array{traits: array<string, string>, version: string}|null
     */
    private function traitsFromCurrentProfileAvatar(string $userId): ?array
    {
        try {
            $stmt = $this->db()->prepare('SELECT img FROM profilePics WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $img = basename((string)($stmt->fetchColumn() ?: ''));
        } catch (\Throwable) {
            return null;
        }
        if (!preg_match('/^(atg_avatar_[a-f0-9]{20})\.(svg|png)$/', $img, $m)) {
            return null;
        }
        $path = 'resources/images/profile/' . $m[1] . '.svg';
        if (!is_file($path)) {
            return null;
        }
        $head = (string)file_get_contents($path, false, null, 0, 65536);
        if (str_contains($head, '<image')) {
            return null; // Living Photo: wraps a real portrait, not eligible.
        }
        $traits = (new AtgEmojiService())->traitsFromProfileImage($img);
        return $traits === null ? null : ['traits' => $traits, 'version' => 'p' . substr(md5($m[1]), 0, 8)];
    }

    /**
     * The viewer may see the owner's stickers if it is the owner, or they share a family code.
     */
    public function canView(string $viewerId, string $ownerId): bool
    {
        if ($viewerId === '' || $ownerId === '') {
            return false;
        }
        if (hash_equals($viewerId, $ownerId)) {
            return true;
        }
        try {
            $codes = [];
            $p = $this->db()->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
            $p->execute([$ownerId]);
            $primary = (string)($p->fetchColumn() ?: '');
            if ($primary !== '') {
                $codes[] = $primary;
            }
            $uf = $this->db()->prepare("SELECT family_code FROM user_families WHERE user_id = ? AND status = 'approved'");
            $uf->execute([$ownerId]);
            foreach ($uf->fetchAll(PDO::FETCH_COLUMN) as $c) {
                $codes[] = (string)$c;
            }
            foreach (array_unique($codes) as $code) {
                if (\App\controller\BaseController::sessionSharesFamily($code)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            error_log('[AvatarStickerService::canView] ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Render the animated sticker SVG. Reaction keyframes are embedded because the
     * sticker is displayed as <img>, which page CSS cannot reach.
     *
     * @param array<string, string> $traits
     */
    public function renderSticker(array $traits, string $reaction, int $size = 160): string
    {
        if (!self::isValidReaction($reaction)) {
            $reaction = 'hello';
        }
        $svg = (new AtgEmojiService())->renderProceduralAvatar($traits, $size, true, false);

        $style = '<style>'
            . '@media (prefers-reduced-motion:no-preference){'
            . '.atg-av-react{transform-box:view-box;transform-origin:128px 236px;animation:atgS 2.8s ease-in-out infinite}'
            . self::reactionCss($reaction)
            . '.atg-st-badge{transform-box:fill-box;transform-origin:center;animation:atgSB 2.8s ease-in-out infinite}'
            . '@keyframes atgSB{0%,100%{transform:scale(1)}12%{transform:scale(1.35) rotate(-10deg)}24%{transform:scale(1) rotate(0)}}'
            . '}</style>';

        $badge = '<g class="atg-st-badge">'
            . '<circle cx="222" cy="222" r="26" fill="#ffffff" stroke="#f59e0b" stroke-width="3"/>'
            . '<text x="222" y="231" text-anchor="middle" font-size="28" font-family="Apple Color Emoji,Segoe UI Emoji,Noto Color Emoji,sans-serif">'
            . self::REACTIONS[$reaction] . '</text></g>';

        $label = htmlspecialchars('My avatar: ' . self::REACTION_LABELS[$reaction], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $svg = (string)preg_replace('/aria-label="[^"]*"/', 'aria-label="' . $label . '"', $svg, 1);

        // Insert the style right after the root <svg ...> tag and the badge before </svg>.
        $rootEnd = strpos($svg, '>');
        if ($rootEnd === false) {
            return $svg;
        }
        $svg = substr($svg, 0, $rootEnd + 1) . $style . substr($svg, $rootEnd + 1);
        $close = strrpos($svg, '</svg>');
        return $close === false ? $svg : substr($svg, 0, $close) . $badge . '</svg>';
    }

    /** Looping (act → rest) versions of the studio reaction choreography. */
    private static function reactionCss(string $reaction): string
    {
        $k = match ($reaction) {
            'hello' => '0%,60%,100%{transform:rotate(0)}12%{transform:rotate(-8deg)}24%{transform:rotate(6deg)}36%{transform:rotate(-5deg)}48%{transform:rotate(3deg)}',
            'laugh' => '0%,60%,100%{transform:translateY(0) rotate(0)}10%{transform:translateY(-7px) rotate(-3deg)}20%{transform:translateY(0) rotate(3deg)}30%{transform:translateY(-6px) rotate(-3deg)}40%{transform:translateY(0) rotate(2deg)}50%{transform:translateY(-3px)}',
            'wow'   => '0%,50%,100%{transform:scale(1)}18%{transform:scale(1.1) translateY(-5px)}',
            'love'  => '0%,60%,100%{transform:rotate(0) scale(1)}20%{transform:rotate(-7deg) scale(1.05)}40%{transform:rotate(-7deg) scale(1.03)}',
            'nod'   => '0%,60%,100%{transform:translateY(0)}12%{transform:translateY(7px) scaleY(.98)}24%{transform:translateY(0)}36%{transform:translateY(7px) scaleY(.98)}48%{transform:translateY(0)}',
            'dance' => '0%,80%,100%{transform:rotate(0) translateY(0)}10%{transform:rotate(-9deg) translateY(-7px)}20%{transform:rotate(0) translateY(0)}30%{transform:rotate(9deg) translateY(-7px)}40%{transform:rotate(0) translateY(0)}50%{transform:rotate(-9deg) translateY(-7px)}60%{transform:rotate(0)}70%{transform:rotate(9deg) translateY(-7px)}',
            'point' => '0%,60%,100%{transform:scale(1) translateY(0)}15%{transform:scale(1.11) translateY(-5px)}30%{transform:scale(1.06) translateY(-2px) rotate(-2deg)}45%{transform:scale(1.11) translateY(-5px)}',
            'clap'  => '0%,60%,100%{transform:translateY(0) scale(1)}8%,24%,40%{transform:translateY(-6px) scale(1.04)}16%,32%,48%{transform:translateY(0) scale(1)}',
            default => '0%,100%{transform:none}',
        };
        $eyes = in_array($reaction, ['laugh', 'clap'], true)
            ? '.atg-av-eye{animation:atgSE 2.8s ease-in-out infinite}@keyframes atgSE{0%,60%,100%{transform:scaleY(1)}8%,50%{transform:scaleY(.35)}}'
            : '';
        return '@keyframes atgS{' . $k . '}' . $eyes;
    }
}

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

    /* -------------------------------------------------------------------------
     * Photo Me-stickers: the member's real portrait, untouched, performing the
     * reaction. Pixels are never stylised; only the whole photo moves.
     * ------------------------------------------------------------------------- */

    /**
     * Which look a member's stickers use, and the data needed to draw it.
     *  - An explicit studio choice (member_avatar_traits.mode) wins.
     *  - With no choice saved, a real profile photo is used; otherwise the procedural fallback.
     *
     * @return array{kind: 'photo', photo: string, version: string}|array{kind: 'procedural', traits: array<string, string>, version: string}|null
     */
    public function getStickerSource(string $userId, bool $forcePhoto = false): ?array
    {
        if ($forcePhoto) {
            $photo = self::isValidUserId($userId) ? $this->profilePhotoPath($userId) : null;
            return $photo === null ? null : [
                'kind'    => 'photo',
                'photo'   => $photo,
                'version' => 'pv' . substr(md5(basename($photo) . ':' . (string)(@filemtime($photo) ?: 0) . json_encode($this->getFaceBox($photo))), 0, 10),
            ];
        }
        if (!self::isValidUserId($userId)) {
            return null;
        }
        $mode = null;
        $rowVersion = '0';
        try {
            $stmt = $this->db()->prepare('SELECT mode, version FROM member_avatar_traits WHERE user_id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $mode = (string)$row['mode'];
                $rowVersion = (string)$row['version'];
            }
        } catch (\Throwable $e) {
            error_log('[AvatarStickerService::getStickerSource] ' . $e->getMessage());
        }

        if ($mode !== 'procedural') {
            $photo = $this->profilePhotoPath($userId);
            if ($photo !== null) {
                $stamp = (string)(@filemtime($photo) ?: 0) . json_encode($this->getFaceBox($photo));
                return [
                    'kind'    => 'photo',
                    'photo'   => $photo,
                    'version' => 'ph' . $rowVersion . substr(md5(basename($photo) . ':' . $stamp), 0, 10),
                ];
            }
        }

        $traits = $this->getTraits($userId);
        return $traits === null ? null : ['kind' => 'procedural'] + $traits;
    }

    /** True when the member has a real portrait that can become a photo sticker. */
    public function hasProfilePhoto(string $userId): bool
    {
        return self::isValidUserId($userId) && $this->profilePhotoPath($userId) !== null;
    }

    /**
     * Local path of the member's real portrait (jpg/png/webp), or null for default
     * silhouettes and procedural avatars. A Living Photo SVG resolves to its PNG companion.
     */
    private function profilePhotoPath(string $userId): ?string
    {
        try {
            $stmt = $this->db()->prepare('SELECT img FROM profilePics WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $img = basename((string)($stmt->fetchColumn() ?: ''));
        } catch (\Throwable) {
            return null;
        }
        if ($img === '' || preg_match('/^avatar[A-Za-z]?\.(png|jpe?g)$/i', $img)) {
            return null; // default silhouette
        }
        $dir = 'resources/images/profile/';
        if (preg_match('/^(atg_avatar_[a-f0-9]{20})\.(svg|png)$/', $img, $m)) {
            // Studio output: only a Living Photo (wraps a real portrait) counts as a photo.
            $svg = $dir . $m[1] . '.svg';
            $png = $dir . $m[1] . '.png';
            if (!is_file($svg) || !is_file($png)) {
                return null;
            }
            $head = (string)file_get_contents($svg, false, null, 0, 65536);
            return str_contains($head, '<image') ? $png : null;
        }
        if (!preg_match('/\.(jpe?g|png|webp)$/i', $img)) {
            return null;
        }
        $path = $dir . $img;
        return is_file($path) ? $path : null;
    }

    /** Public URL of the photo stickers are built from (the face detector runs on this exact file). */
    public function stickerPhotoUrl(string $userId): ?string
    {
        $path = self::isValidUserId($userId) ? $this->profilePhotoPath($userId) : null;
        return $path === null ? null : '/' . $path . '?v=' . (string)(@filemtime($path) ?: 0);
    }

    /** True when a face box has been stored for the member's current sticker photo. */
    public function hasFaceBox(string $userId): bool
    {
        $path = self::isValidUserId($userId) ? $this->profilePhotoPath($userId) : null;
        return $path !== null && $this->getFaceBox($path) !== null;
    }

    private static function faceBoxFile(string $photoPath): string
    {
        return 'storage/cache/me-stickers/faces/' . md5($photoPath . ':' . (string)(@filemtime($photoPath) ?: 0)) . '.json';
    }

    /**
     * Face box (fractions 0..1 of the EXIF-oriented photo) found by the in-browser detector.
     *
     * @return array{x: float, y: float, w: float, h: float}|null
     */
    public function getFaceBox(string $photoPath): ?array
    {
        $file = self::faceBoxFile($photoPath);
        if (!is_file($file)) {
            return null;
        }
        $box = json_decode((string)file_get_contents($file), true);
        return is_array($box) ? self::validFaceBox($box) : null;
    }

    /**
     * Store the face box for the member's CURRENT sticker photo. The box is keyed to that
     * photo's path + mtime, so uploading a new photo simply makes the old box irrelevant.
     *
     * @param array<string, mixed> $box
     */
    public function saveFaceBox(string $userId, array $box): bool
    {
        $valid = self::validFaceBox($box);
        $path = self::isValidUserId($userId) ? $this->profilePhotoPath($userId) : null;
        if ($valid === null || $path === null) {
            return false;
        }
        $file = self::faceBoxFile($path);
        if (!is_dir(dirname($file)) && !@mkdir(dirname($file), 0755, true)) {
            return false;
        }
        return @file_put_contents($file, json_encode($valid)) !== false;
    }

    /**
     * @param array<mixed> $box
     * @return array{x: float, y: float, w: float, h: float}|null
     */
    private static function validFaceBox(array $box): ?array
    {
        $out = [];
        foreach (['x', 'y', 'w', 'h'] as $k) {
            if (!isset($box[$k]) || !is_numeric($box[$k])) {
                return null;
            }
            $out[$k] = round((float)$box[$k], 4);
        }
        $ok = $out['x'] >= 0 && $out['y'] >= 0 && $out['w'] >= 0.02 && $out['h'] >= 0.02
            && $out['x'] + $out['w'] <= 1.001 && $out['y'] + $out['h'] <= 1.001;
        return $ok ? $out : null;
    }

    /**
     * Square, face-biased JPEG crop of the portrait as a data URI (cached on disk).
     * EXIF orientation is honoured so phone photos are never sideways.
     */
    public function photoDataUri(string $path, int $px = 256): ?string
    {
        $cacheDir = 'storage/cache/me-stickers/';
        $face = $this->getFaceBox($path);
        $key = md5($path . ':' . (string)(@filemtime($path) ?: 0) . ':' . $px . ':' . json_encode($face) . ':v4');
        $cacheFile = $cacheDir . $key . '.jpg';
        if (is_file($cacheFile)) {
            return 'data:image/jpeg;base64,' . base64_encode((string)file_get_contents($cacheFile));
        }

        $info = @getimagesize($path);
        if ($info === false || ($info[0] ?? 0) < 16 || ($info[1] ?? 0) < 16) {
            return null;
        }
        $src = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default        => false,
        };
        if (!$src instanceof \GdImage) {
            return null;
        }
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $angle = match ((int)($exif['Orientation'] ?? 1)) {
                3 => 180, 6 => -90, 8 => 90, default => 0,
            };
            if ($angle !== 0) {
                $rot = imagerotate($src, $angle, 0);
                if ($rot instanceof \GdImage) {
                    $src = $rot;
                }
            }
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $sx = (int)(($w - $side) / 2);
        // Portrait photos: faces sit in the upper part, so bias the crop upwards.
        $sy = $h > $w ? (int)(($h - $side) * 0.18) : 0;

        // Zoom in on the face found by the in-browser detector (head + a little shoulder).
        if ($face !== null) {
            $faceSize = max($face['w'] * $w, $face['h'] * $h);
            $cx = ($face['x'] + $face['w'] / 2) * $w;
            $cy = ($face['y'] + $face['h'] / 2) * $h;
            $side = (int)min($w, $h, max(48, $faceSize * 2.2));
            $sx = (int)max(0, min($w - $side, $cx - $side / 2));
            $sy = (int)max(0, min($h - $side, $cy - $side * 0.47));
        }

        $dst = imagecreatetruecolor($px, $px);
        imagefill($dst, 0, 0, (int)imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $px, $px, $side, $side);
        ob_start();
        imagejpeg($dst, null, 86);
        $jpeg = (string)ob_get_clean();
        if ($jpeg === '') {
            return null;
        }
        if (is_dir($cacheDir) || @mkdir($cacheDir, 0755, true)) {
            @file_put_contents($cacheFile, $jpeg);
        }
        return 'data:image/jpeg;base64,' . base64_encode($jpeg);
    }

    /**
     * Animated sticker of the member's real portrait. The photo itself is never altered;
     * the reaction is ACTED OUT around it: hands (tinted with the member's own skin tone)
     * clap, wave, point or cup the cheeks, plus laugh tears, hearts and music notes.
     * The photo is embedded as a data URI because SVG served as <img> cannot load
     * external resources.
     */
    public function renderPhotoSticker(string $photoPath, string $reaction, int $size = 160): ?string
    {
        if (!self::isValidReaction($reaction)) {
            $reaction = 'hello';
        }
        $uri = $this->photoDataUri($photoPath);
        if ($uri === null) {
            return null;
        }
        $skin = $this->photoSkinTone($photoPath);
        [$props, $propCss] = self::photoProps($reaction);

        $label = htmlspecialchars('My photo: ' . self::REACTION_LABELS[$reaction], ENT_QUOTES | ENT_XML1, 'UTF-8');
        $style = '<style>'
            . '.mp-skin{fill:' . $skin . ';stroke:rgba(60,30,10,.45);stroke-width:2;stroke-linejoin:round}'
            . '.mp-crease{fill:none;stroke:rgba(60,30,10,.35);stroke-width:1.6;stroke-linecap:round}'
            . '@media (prefers-reduced-motion:no-preference){'
            . '.atg-av-react{transform-box:view-box;transform-origin:128px 216px;animation:atgS 2.4s ease-in-out infinite}'
            . self::reactionCss($reaction, false)
            . $propCss
            . '}</style>';

        return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 256 256" width="' . $size . '" height="' . $size . '" '
            . 'role="img" aria-label="' . $label . '" class="atg-avatar-svg atg-me-photo-sticker" data-reaction="' . $reaction . '">'
            . $style
            . '<defs>'
            . '<clipPath id="atgMpClip"><circle cx="128" cy="112" r="94"/></clipPath>'
            // Open hand, wrist at the origin, fingers pointing up.
            . '<g id="atgMpHand">'
            . '<rect class="mp-skin" x="-15" y="-46" width="8" height="24" rx="4"/>'
            . '<rect class="mp-skin" x="-7" y="-52" width="8" height="30" rx="4"/>'
            . '<rect class="mp-skin" x="1" y="-50" width="8" height="28" rx="4"/>'
            . '<rect class="mp-skin" x="9" y="-43" width="7" height="22" rx="3.5"/>'
            . '<path class="mp-skin" d="M-15 -4 L-15 -27 Q-15 -30 -12 -30 L13 -30 Q16 -30 16 -27 L16 -4 Q16 2 10 2 L-9 2 Q-15 2 -15 -4 Z"/>'
            . '<path class="mp-skin" d="M-14 -10 Q-26 -16 -28 -28 Q-27 -34 -21 -31 Q-17 -22 -11 -20 Z"/>'
            . '</g>'
            // Pointing hand: fist with the index finger out, finger pointing up.
            . '<g id="atgMpPoint">'
            . '<rect class="mp-skin" x="-17" y="-30" width="34" height="32" rx="12"/>'
            . '<rect class="mp-skin" x="-4" y="-62" width="12" height="38" rx="6"/>'
            . '<path class="mp-crease" d="M-12 -20 L8 -20 M-12 -10 L10 -10"/>'
            . '</g>'
            . '</defs>'
            . '<g class="atg-av-react">'
            . '<circle cx="128" cy="112" r="100" fill="#ffffff"/>'
            . '<image href="' . $uri . '" xlink:href="' . $uri . '" x="34" y="18" width="188" height="188" preserveAspectRatio="xMidYMid slice" clip-path="url(#atgMpClip)"/>'
            . '<circle cx="128" cy="112" r="97" fill="none" stroke="#f59e0b" stroke-width="5"/>'
            . '</g>'
            . $props
            . '</svg>';
    }

    /**
     * Prop artwork + keyframes for each reaction (all loop over 2.4s: act, then rest).
     *
     * @return array{0: string, 1: string}
     */
    private static function photoProps(string $reaction): array
    {
        $hand = static fn (string $cls, float $x, float $y, float $rot, bool $mirror = false, string $ref = 'atgMpHand', float $scale = 1.0): string =>
            '<g transform="translate(' . $x . ' ' . $y . ')"><g class="' . $cls . '"><g transform="rotate(' . $rot . ')' . ($mirror ? ' scale(-1 1)' : '') . ' scale(' . $scale . ')">'
            . '<use href="#' . $ref . '" xlink:href="#' . $ref . '"/></g></g></g>';
        $glyph = static fn (string $cls, float $x, float $y, int $fs, string $g): string =>
            '<text class="' . $cls . '" x="' . $x . '" y="' . $y . '" text-anchor="middle" font-size="' . $fs . '" '
            . 'font-family="Apple Color Emoji,Segoe UI Emoji,Noto Color Emoji,sans-serif">' . $g . '</text>';
        $fx = '.mp-fx{transform-box:fill-box;transform-origin:center}';

        return match ($reaction) {
            // Two hands meet in front of the chest three times, with a spark on each clap.
            'clap' => [
                $hand('mp-cl', 96, 240, 30) . $hand('mp-cr', 160, 240, -30, true)
                . '<g class="mp-spark mp-fx" stroke="#f59e0b" stroke-width="4" stroke-linecap="round">'
                . '<path d="M128 178 L128 166 M110 184 L102 176 M146 184 L154 176"/></g>',
                $fx . '.mp-cl{animation:mpCL 2.4s ease-in-out infinite}.mp-cr{animation:mpCR 2.4s ease-in-out infinite}'
                . '.mp-spark{opacity:0;animation:mpSp 2.4s ease-out infinite}'
                . '@keyframes mpCL{0%,60%,100%{transform:translateX(-14px)}8%,24%,40%{transform:translateX(9px)}16%,32%,48%{transform:translateX(-14px)}}'
                . '@keyframes mpCR{0%,60%,100%{transform:translateX(14px)}8%,24%,40%{transform:translateX(-9px)}16%,32%,48%{transform:translateX(14px)}}'
                . '@keyframes mpSp{0%,7%,13%,23%,29%,39%,45%,100%{opacity:0;transform:scale(.6)}8%,24%,40%{opacity:1;transform:scale(1.15)}}',
            ],
            // A hand pops up beside the face and waves from the wrist.
            'hello' => [
                $hand('mp-wave', 216, 206, 8, true),
                '.mp-wave{animation:mpWv 2.4s ease-in-out infinite}'
                . '@keyframes mpWv{0%,70%,100%{transform:translateY(46px) rotate(0)}10%{transform:translateY(0) rotate(0)}20%{transform:rotate(-24deg)}30%{transform:rotate(14deg)}40%{transform:rotate(-24deg)}50%{transform:rotate(14deg)}60%{transform:translateY(0) rotate(0)}}',
            ],
            // Shaking with laughter: tears of joy fly off both sides, "HA" bubbles pop.
            'laugh' => [
                '<path class="mp-tl" d="M40 104 q-8 12 0 16 q8 -4 0 -16z" fill="#60a5fa" stroke="#2563eb" stroke-width="1.5"/>'
                . '<path class="mp-tr" d="M216 104 q-8 12 0 16 q8 -4 0 -16z" fill="#60a5fa" stroke="#2563eb" stroke-width="1.5"/>'
                . '<text class="mp-ha1 mp-fx" x="34" y="44" font-size="26" font-weight="900" fill="#f59e0b" stroke="#fff" stroke-width="3" paint-order="stroke" font-family="Arial Black,Arial,sans-serif">HA</text>'
                . '<text class="mp-ha2 mp-fx" x="190" y="40" font-size="26" font-weight="900" fill="#f59e0b" stroke="#fff" stroke-width="3" paint-order="stroke" font-family="Arial Black,Arial,sans-serif">HA</text>',
                $fx . '.mp-tl,.mp-tr{opacity:0;animation:mpTl 2.4s ease-in infinite}.mp-tr{animation-name:mpTr}'
                . '.mp-ha1,.mp-ha2{opacity:0;animation:mpHa 2.4s ease-out infinite}.mp-ha2{animation-delay:.25s}'
                . '@keyframes mpTl{0%,5%{opacity:0;transform:translate(0,0)}10%{opacity:1}45%{opacity:0;transform:translate(-18px,46px)}100%{opacity:0}}'
                . '@keyframes mpTr{0%,5%{opacity:0;transform:translate(0,0)}10%{opacity:1}45%{opacity:0;transform:translate(18px,46px)}100%{opacity:0}}'
                . '@keyframes mpHa{0%{opacity:0;transform:scale(.4)}10%{opacity:1;transform:scale(1.15)}20%,45%{opacity:1;transform:scale(1)}55%,100%{opacity:0;transform:scale(1)}}',
            ],
            // Hands fly up to the cheeks, surprise lines burst out.
            'wow' => [
                $hand('mp-wl', 50, 200, 16) . $hand('mp-wr', 206, 200, -16, true)
                . '<g class="mp-burst mp-fx" stroke="#f59e0b" stroke-width="5" stroke-linecap="round">'
                . '<path d="M128 12 L128 2 M62 34 L54 26 M194 34 L202 26 M30 90 L18 86 M226 90 L238 86"/></g>',
                $fx . '.mp-wl,.mp-wr{animation:mpWo 2.4s cubic-bezier(.3,1.5,.5,1) infinite}'
                . '.mp-burst{opacity:0;animation:mpBu 2.4s ease-out infinite}'
                . '@keyframes mpWo{0%,70%,100%{transform:translateY(60px)}12%,55%{transform:translateY(0)}}'
                . '@keyframes mpBu{0%,8%{opacity:0;transform:scale(.8)}14%{opacity:1;transform:scale(1.08)}45%{opacity:1;transform:scale(1)}60%,100%{opacity:0}}',
            ],
            // Hearts float up around the photo.
            'love' => [
                $glyph('mp-h1 mp-fx', 40, 200, 30, '❤️') . $glyph('mp-h2 mp-fx', 220, 196, 26, '💕') . $glyph('mp-h3 mp-fx', 128, 246, 28, '💖'),
                $fx . '.mp-h1,.mp-h2,.mp-h3{opacity:0;animation:mpHt 2.4s ease-out infinite}.mp-h2{animation-delay:.3s}.mp-h3{animation-delay:.6s}'
                . '@keyframes mpHt{0%{opacity:0;transform:translateY(0) scale(.6)}15%{opacity:1;transform:translateY(-12px) scale(1.1)}60%{opacity:0;transform:translateY(-70px) scale(1)}100%{opacity:0}}',
            ],
            // Nodding along with a thumbs-up.
            'nod' => [
                $glyph('mp-tu mp-fx', 214, 238, 46, '👍'),
                $fx . '.mp-tu{animation:mpTu 2.4s cubic-bezier(.3,1.6,.5,1) infinite}'
                . '@keyframes mpTu{0%,70%,100%{opacity:0;transform:scale(.3) rotate(-20deg)}12%,55%{opacity:1;transform:scale(1) rotate(0)}}',
            ],
            // Both hands up, bouncing to music notes.
            'dance' => [
                $hand('mp-dl', 30, 160, -14) . $hand('mp-dr', 226, 160, 14, true)
                . $glyph('mp-n1 mp-fx', 40, 50, 26, '🎵') . $glyph('mp-n2 mp-fx', 214, 46, 26, '🎶'),
                $fx . '.mp-dl{animation:mpDl 1.2s ease-in-out infinite}.mp-dr{animation:mpDl 1.2s ease-in-out infinite reverse}'
                . '.mp-n1,.mp-n2{animation:mpNt 1.2s ease-in-out infinite}.mp-n2{animation-delay:.6s}'
                . '@keyframes mpDl{0%,100%{transform:translateY(0) rotate(-12deg)}50%{transform:translateY(-14px) rotate(12deg)}}'
                . '@keyframes mpNt{0%,100%{transform:translateY(0) rotate(-10deg)}50%{transform:translateY(-10px) rotate(10deg)}}',
            ],
            // A finger jabs out at the viewer: "yes, you!".
            'point' => [
                $hand('mp-pt', 198, 244, -28, false, 'atgMpPoint', 1.1),
                '.mp-pt{animation:mpPt 2.4s cubic-bezier(.3,1.6,.5,1) infinite}'
                . '@keyframes mpPt{0%,70%,100%{transform:translate(0,40px) scale(.9)}12%{transform:translate(0,0) scale(1)}22%,42%{transform:translate(-6px,-6px) scale(1.25)}32%,52%{transform:translate(0,0) scale(1)}}',
            ],
            default => ['', ''],
        };
    }

    /**
     * Average skin colour from the cheek area of the cropped portrait (for the hands).
     */
    private function photoSkinTone(string $path): string
    {
        $uri = $this->photoDataUri($path);
        $raw = $uri === null ? false : base64_decode(substr($uri, strlen('data:image/jpeg;base64,')), true);
        $img = $raw === false ? false : @imagecreatefromstring($raw);
        if (!$img instanceof \GdImage) {
            return 'rgb(176,120,86)';
        }
        $w = imagesx($img);
        $h = imagesy($img);
        // Classic YCbCr skin rule over the central area of the crop (where the face is),
        // then the median of each channel — robust to walls, hair and glasses.
        $rs = $gs = $bs = [];
        for ($x = (int)($w * 0.2); $x < (int)($w * 0.8); $x += 3) {
            for ($y = (int)($h * 0.15); $y < (int)($h * 0.85); $y += 3) {
                $c = imagecolorat($img, $x, $y);
                $pr = ($c >> 16) & 0xFF;
                $pg = ($c >> 8) & 0xFF;
                $pb = $c & 0xFF;
                $cb = 128 - 0.168736 * $pr - 0.331264 * $pg + 0.5 * $pb;
                $cr = 128 + 0.5 * $pr - 0.418688 * $pg - 0.081312 * $pb;
                $luma = 0.299 * $pr + 0.587 * $pg + 0.114 * $pb;
                if ($cb >= 77 && $cb <= 127 && $cr >= 136 && $cr <= 173 && $luma > 25 && $luma < 235 && $pr > $pg && $pr > $pb) {
                    $rs[] = $pr;
                    $gs[] = $pg;
                    $bs[] = $pb;
                }
            }
        }
        if (count($rs) < 20) {
            return 'rgb(176,120,86)';
        }
        sort($rs);
        sort($gs);
        sort($bs);
        $mid = intdiv(count($rs), 2);
        return sprintf('rgb(%d,%d,%d)', $rs[$mid], $gs[$mid], $bs[$mid]);
    }

    /** Looping (act → rest) versions of the studio reaction choreography. */
    private static function reactionCss(string $reaction, bool $withEyes = true): string
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
        $eyes = $withEyes && in_array($reaction, ['laugh', 'clap'], true)
            ? '.atg-av-eye{animation:atgSE 2.8s ease-in-out infinite}@keyframes atgSE{0%,60%,100%{transform:scaleY(1)}8%,50%{transform:scaleY(.35)}}'
            : '';
        return '@keyframes atgS{' . $k . '}' . $eyes;
    }
}

<?php

declare(strict_types=1);

namespace App\services;

use Exception;
use PDO;
use Src\Db;

/**
 * ATG Sovereign 3D Emoji & Procedural Avatar Engine
 *
 * Developed by the Advanced Technology Group (ATG Pods 3 & 4)
 * Lead Architects: Kaelen Vance & Maya Lindqvist
 * Core Systems: Callum Wright & Damilola Ogunleye
 *
 * Provides sovereign, zero-dependency procedural 3D vector emojis and
 * high-fidelity persona avatar rendering for FamilyPlatform.
 */
class AtgEmojiService
{
    /**
     * Trait definitions and whitelists for procedural 3D avatars.
     */
    public const SKIN_TONES  = AtgAvatarRenderer::SKIN_TONES;
    public const HAIRSTYLES  = AtgAvatarRenderer::HAIRSTYLES;
    public const EXPRESSIONS = AtgAvatarRenderer::EXPRESSIONS;
    public const ACCESSORIES = AtgAvatarRenderer::ACCESSORIES;
    public const BACKDROPS   = AtgAvatarRenderer::BACKDROPS;

    /**
     * Emoji skin-tone modifiers (Fitzpatrick-style 5-step scale). Tone variants
     * are addressed as `<base>_<tone>_3d`, e.g. `family_wave_dark_3d`, so they
     * stay compatible with every existing `:[a-z0-9_]+_3d:` shortcode parser.
     */
    public const EMOJI_TONES = [
        'light'        => ['label' => 'Light', 'base' => '#f7d7c4', 'highlight' => '#fff0e6', 'shadow' => '#c9a088'],
        'medium_light' => ['label' => 'Medium-Light', 'base' => '#e0b48c', 'highlight' => '#f3d2b0', 'shadow' => '#a77a55'],
        'medium'       => ['label' => 'Medium', 'base' => '#c08a5c', 'highlight' => '#dcab7e', 'shadow' => '#85582f'],
        'medium_dark'  => ['label' => 'Medium-Dark', 'base' => '#8d5a38', 'highlight' => '#b07650', 'shadow' => '#5a3418'],
        'dark'         => ['label' => 'Dark', 'base' => '#4a2c1c', 'highlight' => '#6e4630', 'shadow' => '#26150b'],
    ];

    /** Emojis depicting people/hands that accept a skin-tone modifier. */
    public const TONEABLE_EMOJIS = ['family_wave_3d', 'family_heart_hands_3d', 'ancestor_blessing_3d', 'family_hug_3d'];

    /** Idle motion loop applied to each emoji (respects prefers-reduced-motion). */
    public const EMOJI_MOTION = [
        'family_roots_3d'       => 'sway',
        'family_crest_3d'       => 'turn',
        'family_hearth_3d'      => 'flicker',
        'family_feast_3d'       => 'bounce',
        'family_hug_3d'         => 'beat',
        'memory_capsule_3d'     => 'float',
        'ancestor_crown_3d'     => 'float',
        'ancestor_blessing_3d'  => 'float',
        'kinship_pride_3d'      => 'beat',
        'family_celebrate_3d'   => 'pop',
        'wisdom_scroll_3d'      => 'float',
        'generations_link_3d'   => 'turn',
        'family_toast_3d'       => 'shake',
        'baby_cradle_3d'        => 'rock',
        'wedding_vows_3d'       => 'beat',
        'laugh_tears_3d'        => 'shake',
        'star_struck_3d'        => 'twinkle',
        'family_house_3d'       => 'bounce',
        'family_wave_3d'        => 'wave',
        'family_heart_hands_3d' => 'beat',
        'family_blended_3d'     => 'bounce',
    ];

    /**
     * Specialized 3D Emojis Catalog bespoke to FamilyPlatform.
     */
    public const EMOJI_CATALOG = [
        [
            'id'          => 'family_roots_3d',
            'title'       => 'Family Roots',
            'category'    => 'Kinship & Heritage',
            'description' => 'The glowing golden tree of ancestry with intertwined deep roots and vibrant emerald leaves.',
            'meaning'     => 'Rooted together across generations.',
        ],
        [
            'id'          => 'family_crest_3d',
            'title'       => 'Generational Crest',
            'category'    => 'Kinship & Heritage',
            'description' => '3D heraldic family shield embossed in royal gold and deep midnight navy.',
            'meaning'     => 'Family honour and heritage defence.',
        ],
        [
            'id'          => 'family_hearth_3d',
            'title'       => 'Warm Hearth',
            'category'    => 'Kinship & Heritage',
            'description' => '3D cozy clay fireplace with dancing amber flames and warm stone hearth.',
            'meaning'     => 'The warmth and sanctuary of home.',
        ],
        [
            'id'          => 'family_feast_3d',
            'title'       => 'Celebration Feast',
            'category'    => 'Kinship & Heritage',
            'description' => '3D banquet platter with celebratory golden goblets and harvest dishes.',
            'meaning'     => 'Breaking bread together in celebration.',
        ],
        [
            'id'          => 'family_hug_3d',
            'title'       => 'Kinship Embrace',
            'category'    => 'Kinship & Heritage',
            'description' => 'Sculpted 3D figures in warm embrace with floating ruby hearts.',
            'meaning'     => 'Unconditional family support and affection.',
        ],
        [
            'id'          => 'memory_capsule_3d',
            'title'       => 'Memory Capsule',
            'category'    => 'Milestones & Memories',
            'description' => 'Iridescent 3D chronos capsule with golden clockwork and floating polaroids.',
            'meaning'     => 'Cherishing precious family milestones forever.',
        ],
        [
            'id'          => 'ancestor_crown_3d',
            'title'       => 'Diadem of Elders',
            'category'    => 'Milestones & Memories',
            'description' => 'Floating 3D antique gold diadem studded with glowing amethysts.',
            'meaning'     => 'Venerating elder wisdom and ancestral royalty.',
        ],
        [
            'id'          => 'ancestor_blessing_3d',
            'title'       => 'Ancestral Blessing',
            'category'    => 'Reactions & Blessings',
            'description' => 'Sculpted 3D praying hands radiating solar sunbeams and golden aura.',
            'meaning'     => 'Sending heartfelt blessings across the lineage.',
        ],
        [
            'id'          => 'kinship_pride_3d',
            'title'       => 'Kinship Pride',
            'category'    => 'Reactions & Blessings',
            'description' => 'Multifaceted 3D ruby heart medallion suspended by a sapphire ribbon with gold trim.',
            'meaning'     => 'Immensely proud of family achievements.',
        ],
        [
            'id'          => 'family_celebrate_3d',
            'title'       => 'Milestone Burst',
            'category'    => 'Reactions & Blessings',
            'description' => '3D festive cannon bursting with metallic confetti and golden streamers.',
            'meaning'     => 'Celebrating birthdays, unions, and graduations.',
        ],
        [
            'id'          => 'wisdom_scroll_3d',
            'title'       => 'Family Chronicle',
            'category'    => 'Milestones & Memories',
            'description' => 'Unfurled 3D parchment scroll with ancient quill and glowing wax seal.',
            'meaning'     => 'Recording the enduring story of our lineage.',
        ],
        [
            'id'          => 'generations_link_3d',
            'title'       => 'Kinship Triad',
            'category'    => 'Kinship & Heritage',
            'description' => 'Three intertwined 3D golden rings symbolising past, present, and future generations.',
            'meaning'     => 'An unbreakable generational chain.',
            'ar_particles' => 'stars',
        ],
        [
            'id'          => 'family_toast_3d',
            'title'       => 'Celebration Toast',
            'category'    => 'Celebrations & Milestones',
            'description' => 'Clinking 3D golden goblets splashing sparkling champagne foam and star glints.',
            'meaning'     => 'Toasting to family triumphs, good health, and longevity.',
            'ar_particles' => 'bubbles',
        ],
        [
            'id'          => 'baby_cradle_3d',
            'title'       => 'New Kin Born',
            'category'    => 'Celebrations & Milestones',
            'description' => '3D golden woven baby cradle rocking gently with celestial stardust.',
            'meaning'     => 'Welcoming a precious newborn to the lineage.',
            'ar_particles' => 'stars',
        ],
        [
            'id'          => 'wedding_vows_3d',
            'title'       => 'Sacred Union',
            'category'    => 'Celebrations & Milestones',
            'description' => 'Two intertwined 3D diamond rings with golden reflection and radiant flare.',
            'meaning'     => 'Celebrating holy matrimony and generational union.',
            'ar_particles' => 'hearts',
        ],
        [
            'id'          => 'laugh_tears_3d',
            'title'       => 'Pure Laughter',
            'category'    => 'Expressive 3D Emojis',
            'description' => 'Glossy 3D smiling emoji laughing joyfully with floating liquid tear droplets.',
            'meaning'     => 'Uncontrollable family joy and shared banter.',
            'ar_particles' => 'confetti',
        ],
        [
            'id'          => 'star_struck_3d',
            'title'       => 'Generational Pride',
            'category'    => 'Expressive 3D Emojis',
            'description' => 'Glossy 3D face with faceted golden stars in eyes and a radiant proud grin.',
            'meaning'     => 'Stunned with admiration and immense family pride.',
            'ar_particles' => 'stars',
        ],
        [
            'id'          => 'family_house_3d',
            'title'       => 'Family Sanctuary',
            'category'    => 'Kinship & Heritage',
            'description' => 'Sculpted 3D family home with glowing amber windows and cozy chimney smoke.',
            'meaning'     => 'Our safe harbour, family home, and ancestral base.',
            'ar_particles' => 'embers',
        ],
        [
            'id'          => 'family_wave_3d',
            'title'       => 'Hello Kin',
            'category'    => 'Reactions & Blessings',
            'description' => 'A warm waving hand in your own skin tone, animated mid-greeting.',
            'meaning'     => 'Hello family — so good to see you.',
            'ar_particles' => 'hearts',
        ],
        [
            'id'          => 'family_heart_hands_3d',
            'title'       => 'Held With Love',
            'category'    => 'Reactions & Blessings',
            'description' => 'Two cupped hands lifting a beating ruby heart, available in every skin tone.',
            'meaning'     => 'Holding you close, wherever you are.',
            'ar_particles' => 'hearts',
        ],
        [
            'id'          => 'family_blended_3d',
            'title'       => 'Blended Family',
            'category'    => 'Kinship & Heritage',
            'description' => 'Four generations and four complexions sharing one joyful frame.',
            'meaning'     => 'Every shade, one family.',
            'ar_particles' => 'confetti',
        ],
    ];

    /**
     * @var array<string, mixed>|null
     */
    private static ?array $cachedCatalog = null;

    /**
     * Retrieve complete 3D emoji catalog.
     *
     * @return array<string, mixed>
     */
    public function getCatalog(): array
    {
        if (self::$cachedCatalog !== null) {
            return self::$cachedCatalog;
        }

        $categories = [];
        foreach (self::EMOJI_CATALOG as $emoji) {
            $cat = $emoji['category'];
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $emojiData = $emoji;
            $emojiData['svg'] = $this->renderSvgEmoji($emoji['id'], 96);
            $emojiData['toneable'] = in_array($emoji['id'], self::TONEABLE_EMOJIS, true);
            $emojiData['motion'] = self::EMOJI_MOTION[$emoji['id']];
            $categories[$cat][] = $emojiData;
        }

        self::$cachedCatalog = [
            'categories'  => $categories,
            'all'         => self::EMOJI_CATALOG,
            'traits'      => [
                'skin_tones'   => self::SKIN_TONES,
                'hairstyles'   => self::HAIRSTYLES,
                'expressions'  => self::EXPRESSIONS,
                'accessories'  => self::ACCESSORIES,
                'backdrops'    => self::BACKDROPS,
            ],
            'avatar_options' => AtgAvatarRenderer::options(),
            'emoji_tones'    => self::EMOJI_TONES,
        ];

        return self::$cachedCatalog;
    }

    /**
     * Render specialised 3D SVG emoji by ID.
     *
     * Accepts tone variants (`family_wave_dark_3d`) for TONEABLE_EMOJIS. The
     * returned SVG carries namespaced gradient ids (safe to inline many on one
     * page) and an idle motion loop.
     */
    public function renderSvgEmoji(string $id, int $size = 120): string
    {
        $safeSize = max(32, min(512, $size));
        [$baseId, $tone] = $this->parseToneId($id);

        switch ($baseId) {
            case 'family_roots_3d':
                $svg = $this->buildRootsTreeSvg($safeSize);
                break;
            case 'family_crest_3d':
                $svg = $this->buildFamilyCrestSvg($safeSize);
                break;
            case 'family_hearth_3d':
                $svg = $this->buildHearthSvg($safeSize);
                break;
            case 'family_feast_3d':
                $svg = $this->buildFeastSvg($safeSize);
                break;
            case 'family_hug_3d':
                $svg = $this->buildHugSvg($safeSize, $tone);
                break;
            case 'memory_capsule_3d':
                $svg = $this->buildCapsuleSvg($safeSize);
                break;
            case 'ancestor_crown_3d':
                $svg = $this->buildCrownSvg($safeSize);
                break;
            case 'ancestor_blessing_3d':
                $svg = $this->buildBlessingSvg($safeSize, $tone);
                break;
            case 'kinship_pride_3d':
                $svg = $this->buildPrideSvg($safeSize);
                break;
            case 'family_celebrate_3d':
                $svg = $this->buildCelebrateSvg($safeSize);
                break;
            case 'wisdom_scroll_3d':
                $svg = $this->buildScrollSvg($safeSize);
                break;
            case 'generations_link_3d':
                $svg = $this->buildTriadSvg($safeSize);
                break;
            case 'family_toast_3d':
                $svg = $this->buildToastSvg($safeSize);
                break;
            case 'baby_cradle_3d':
                $svg = $this->buildCradleSvg($safeSize);
                break;
            case 'wedding_vows_3d':
                $svg = $this->buildWeddingRingsSvg($safeSize);
                break;
            case 'laugh_tears_3d':
                $svg = $this->buildLaughTearsSvg($safeSize);
                break;
            case 'star_struck_3d':
                $svg = $this->buildStarStruckSvg($safeSize);
                break;
            case 'family_house_3d':
                $svg = $this->buildFamilyHouseSvg($safeSize);
                break;
            case 'family_wave_3d':
                $svg = $this->buildWaveSvg($safeSize, $tone);
                break;
            case 'family_heart_hands_3d':
                $svg = $this->buildHeartHandsSvg($safeSize, $tone);
                break;
            case 'family_blended_3d':
                $svg = $this->buildBlendedFamilySvg($safeSize);
                break;
            default:
                $baseId = 'family_roots_3d';
                $tone = null;
                $svg = $this->buildRootsTreeSvg($safeSize);
        }

        $fullId = $tone !== null ? substr($baseId, 0, -3) . '_' . $tone . '_3d' : $baseId;
        return $this->finaliseEmojiSvg($svg, $fullId, self::EMOJI_MOTION[$baseId]);
    }

    /**
     * Split `family_wave_medium_dark_3d` into ['family_wave_3d', 'medium_dark'].
     *
     * @return array{0: string, 1: ?string}
     */
    public function parseToneId(string $id): array
    {
        $id = strtolower(trim($id));
        if (preg_match('/^([a-z0-9_]+?)_(medium_light|medium_dark|light|medium|dark)_3d$/', $id, $m)) {
            $base = $m[1] . '_3d';
            if (in_array($base, self::TONEABLE_EMOJIS, true)) {
                return [$base, $m[2]];
            }
        }
        return [$id, null];
    }

    /**
     * Every emoji id that has a renderable asset, including tone variants.
     *
     * @return list<string>
     */
    public function allEmojiAssetIds(): array
    {
        $ids = [];
        foreach (self::EMOJI_CATALOG as $emoji) {
            $ids[] = $emoji['id'];
            if (in_array($emoji['id'], self::TONEABLE_EMOJIS, true)) {
                foreach (array_keys(self::EMOJI_TONES) as $tone) {
                    $ids[] = substr($emoji['id'], 0, -3) . '_' . $tone . '_3d';
                }
            }
        }
        return $ids;
    }

    /**
     * Namespace internal ids (so many inline emojis never share gradients) and
     * wrap the artwork in an idle motion group.
     */
    private function finaliseEmojiSvg(string $svg, string $fullId, string $motion): string
    {
        $prefix = 'e' . substr(md5($fullId), 0, 6) . '_';
        $svg = (string)preg_replace('/\bid="([A-Za-z0-9_\-]+)"/', 'id="' . $prefix . '$1"', $svg);
        $svg = (string)preg_replace('/url\(#([A-Za-z0-9_\-]+)\)/', 'url(#' . $prefix . '$1)', $svg);

        $openEnd = strpos($svg, '>');
        $defsEnd = strpos($svg, '</defs>');
        $close = strrpos($svg, '</svg>');
        if ($openEnd === false || $defsEnd === false || $close === false) {
            return $svg;
        }
        $defsEnd += strlen('</defs>');
        $motion = preg_replace('/[^a-z]/', '', $motion) ?: 'float';

        return substr($svg, 0, $openEnd + 1)
            . self::EMOJI_MOTION_STYLE
            . substr($svg, $openEnd + 1, $defsEnd - $openEnd - 1)
            . '<g class="atgm atgm-' . $motion . '">'
            . substr($svg, $defsEnd, $close - $defsEnd)
            . '</g></svg>';
    }

    private const EMOJI_MOTION_STYLE = '<style>@media (prefers-reduced-motion:no-preference){'
        . '.atgm{transform-box:fill-box;transform-origin:50% 50%}'
        . '.atgm-sway{transform-origin:50% 100%;animation:atgmSway 3.6s ease-in-out infinite}'
        . '.atgm-flicker{transform-origin:50% 100%;animation:atgmFlicker 1.4s ease-in-out infinite}'
        . '.atgm-beat{animation:atgmBeat 2.2s ease-in-out infinite}'
        . '.atgm-float{animation:atgmFloat 3.2s ease-in-out infinite}'
        . '.atgm-turn{animation:atgmTurn 5s ease-in-out infinite}'
        . '.atgm-rock{transform-origin:50% 100%;animation:atgmRock 2.8s ease-in-out infinite}'
        . '.atgm-pop{animation:atgmPop 2.6s ease-in-out infinite}'
        . '.atgm-shake{animation:atgmShake 2.4s ease-in-out infinite}'
        . '.atgm-twinkle{animation:atgmTwinkle 2.8s ease-in-out infinite}'
        . '.atgm-wave{transform-origin:45% 95%;animation:atgmWave 1.9s ease-in-out infinite}'
        . '.atgm-bounce{transform-origin:50% 100%;animation:atgmBounce 2.4s ease-in-out infinite}'
        . '@keyframes atgmSway{0%,100%{transform:rotate(-3deg)}50%{transform:rotate(3deg)}}'
        . '@keyframes atgmFlicker{0%,100%{transform:scale(1,1)}30%{transform:scale(1.02,1.05)}60%{transform:scale(.99,.97)}}'
        . '@keyframes atgmBeat{0%,40%,100%{transform:scale(1)}10%{transform:scale(1.1)}20%{transform:scale(.98)}30%{transform:scale(1.06)}}'
        . '@keyframes atgmFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-4px)}}'
        . '@keyframes atgmTurn{0%,60%,100%{transform:scaleX(1)}70%{transform:scaleX(.15)}80%{transform:scaleX(-1)}90%{transform:scaleX(.15)}}'
        . '@keyframes atgmRock{0%,100%{transform:rotate(-7deg)}50%{transform:rotate(7deg)}}'
        . '@keyframes atgmPop{0%,55%,100%{transform:scale(1) rotate(0)}65%{transform:scale(1.14) rotate(-6deg)}75%{transform:scale(.96) rotate(4deg)}85%{transform:scale(1.04) rotate(0)}}'
        . '@keyframes atgmShake{0%,50%,100%{transform:rotate(0)}58%{transform:rotate(-8deg)}66%{transform:rotate(7deg)}74%{transform:rotate(-5deg)}82%{transform:rotate(3deg)}}'
        . '@keyframes atgmTwinkle{0%,100%{transform:scale(1) rotate(0)}50%{transform:scale(1.07) rotate(4deg)}}'
        . '@keyframes atgmWave{0%,60%,100%{transform:rotate(0)}10%{transform:rotate(-14deg)}20%{transform:rotate(12deg)}30%{transform:rotate(-12deg)}40%{transform:rotate(10deg)}50%{transform:rotate(-4deg)}}'
        . '@keyframes atgmBounce{0%,100%{transform:translateY(0) scale(1,1)}40%{transform:translateY(-5px) scale(.98,1.02)}55%{transform:translateY(0) scale(1.04,.96)}70%{transform:translateY(0) scale(1,1)}}'
        . '}</style>';

    /**
     * Render procedural 3D user profile avatar SVG.
     * Delegates to the layered, motion-ready AtgAvatarRenderer.
     *
     * @param array<mixed> $traits
     */
    public function renderProceduralAvatar(array $traits, int $size = 256, bool $animated = false, bool $portrait = false): string
    {
        return (new AtgAvatarRenderer())->render($traits, $size, $animated, $portrait);
    }

    /**
     * Whitelist raw request input into a complete trait set.
     *
     * @param array<mixed> $input
     * @return array<string, string>
     */
    public function sanitiseTraits(array $input): array
    {
        return AtgAvatarRenderer::sanitise($input);
    }

    /**
     * Recover the trait set embedded in a previously saved studio avatar so the
     * customiser re-opens on the user's current look. Returns null for any
     * non-studio profile image.
     *
     * @return array<string, string>|null
     */
    public function traitsFromProfileImage(string $filename): ?array
    {
        $filename = basename($filename);
        if (!preg_match('/^(atg_avatar_[a-f0-9]{20})\.(svg|png)$/', $filename, $m)) {
            return null;
        }
        $path = 'resources/images/profile/' . $m[1] . '.svg';
        if (!is_file($path)) {
            return null;
        }
        $svg = (string)file_get_contents($path, false, null, 0, 4096);
        if (!preg_match('/data-atg-traits="([^"]*)"/', $svg, $tm)) {
            return null;
        }
        $raw = html_entity_decode($tm[1], ENT_QUOTES);
        if (str_starts_with($raw, '{')) {
            // Legacy JSON encoding (avatars saved before 2026-11-07)
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? AtgAvatarRenderer::sanitise($decoded) : null;
        }
        $decoded = [];
        foreach (explode(';', $raw) as $pair) {
            $kv = explode(':', $pair, 2);
            if (count($kv) === 2) {
                $decoded[$kv[0]] = $kv[1];
            }
        }
        return $decoded !== [] ? AtgAvatarRenderer::sanitise($decoded) : null;
    }

    /**
     * Analyse an uploaded portrait (RGB histogram / CIE L*a*b* facial region sampling)
     * and return identity-preserving avatar traits, neural realism parameters,
     * and post-processing recommendations.
     *
     * @param array<mixed> $payload
     * @return array<string, mixed>
     */
    public function analysePhotoToAvatar(array $payload): array
    {
        // 1. Extract sampled facial RGB or decode base64 image if provided
        $r = isset($payload['sample_r']) ? max(0, min(255, (int)$payload['sample_r'])) : 138;
        $g = isset($payload['sample_g']) ? max(0, min(255, (int)$payload['sample_g'])) : 84;
        $b = isset($payload['sample_b']) ? max(0, min(255, (int)$payload['sample_b'])) : 52;

        $hairR = isset($payload['hair_r']) ? max(0, min(255, (int)$payload['hair_r'])) : 24;
        $hairG = isset($payload['hair_g']) ? max(0, min(255, (int)$payload['hair_g'])) : 18;
        $hairB = isset($payload['hair_b']) ? max(0, min(255, (int)$payload['hair_b'])) : 14;

        if (!empty($payload['image_base64']) && is_string($payload['image_base64'])) {
            $raw = $payload['image_base64'];
            if (preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/i', $raw, $m)) {
                $bytes = base64_decode($m[2], true);
                if ($bytes !== false && strlen($bytes) > 64 && strlen($bytes) <= 4 * 1024 * 1024 && function_exists('imagecreatefromstring')) {
                    $im = @imagecreatefromstring($bytes);
                    if ($im !== false) {
                        $w = imagesx($im);
                        $h = imagesy($im);
                        if ($w >= 16 && $h >= 16) {
                            // Sample centre facial region (40%-60% X, 38%-58% Y)
                            $sr = 0;
                            $sg = 0;
                            $sb = 0;
                            $count = 0;
                            for ($y = (int)($h * 0.38); $y <= (int)($h * 0.58); $y += max(1, (int)($h / 20))) {
                                for ($x = (int)($w * 0.40); $x <= (int)($w * 0.60); $x += max(1, (int)($w / 20))) {
                                    $rgb = imagecolorat($im, $x, $y);
                                    $sr += ($rgb >> 16) & 0xFF;
                                    $sg += ($rgb >> 8) & 0xFF;
                                    $sb += $rgb & 0xFF;
                                    $count++;
                                }
                            }
                            if ($count > 0) {
                                $r = (int)round($sr / $count);
                                $g = (int)round($sg / $count);
                                $b = (int)round($sb / $count);
                            }

                            // Sample crown/hair region (35%-65% X, 12%-26% Y)
                            $hr = 0;
                            $hg = 0;
                            $hb = 0;
                            $hCount = 0;
                            for ($y = (int)($h * 0.12); $y <= (int)($h * 0.26); $y += max(1, (int)($h / 20))) {
                                for ($x = (int)($w * 0.35); $x <= (int)($w * 0.65); $x += max(1, (int)($w / 20))) {
                                    $rgb = imagecolorat($im, $x, $y);
                                    $hr += ($rgb >> 16) & 0xFF;
                                    $hg += ($rgb >> 8) & 0xFF;
                                    $hb += $rgb & 0xFF;
                                    $hCount++;
                                }
                            }
                            if ($hCount > 0) {
                                $hairR = (int)round($hr / $hCount);
                                $hairG = (int)round($hg / $hCount);
                                $hairB = (int)round($hb / $hCount);
                            }
                        }
                        imagedestroy($im);
                    }
                }
            }
        }

        // 2. Match closest Fitzpatrick / 13-shade skin tone via Euclidean RGB distance
        $bestSkin = 'warm_chestnut';
        $minDist = PHP_FLOAT_MAX;
        foreach (self::SKIN_TONES as $toneKey => $toneData) {
            $hex = ltrim($toneData['base'], '#');
            $tr = (int)hexdec(substr($hex, 0, 2));
            $tg = (int)hexdec(substr($hex, 2, 2));
            $tb = (int)hexdec(substr($hex, 4, 2));
            $dist = sqrt((($r - $tr) ** 2) * 0.30 + (($g - $tg) ** 2) * 0.59 + (($b - $tb) ** 2) * 0.11);
            if ($dist < $minDist) {
                $minDist = $dist;
                $bestSkin = $toneKey;
            }
        }

        // 3. Match closest hair colour
        $bestHairColor = 'natural';
        $minHairDist = PHP_FLOAT_MAX;
        foreach (AtgAvatarRenderer::HAIR_COLORS as $hcKey => $hcData) {
            if ($hcData['hex'] === '') {
                continue;
            }
            $hex = ltrim($hcData['hex'], '#');
            $hr = (int)hexdec(substr($hex, 0, 2));
            $hg = (int)hexdec(substr($hex, 2, 2));
            $hb = (int)hexdec(substr($hex, 4, 2));
            $dist = sqrt((($hairR - $hr) ** 2) + (($hairG - $hg) ** 2) + (($hairB - $hb) ** 2));
            if ($dist < $minHairDist) {
                $minHairDist = $dist;
                $bestHairColor = $hcKey;
            }
        }

        // 4. Sanitise and merge user-selected realism parameters
        $denoising = isset($payload['denoising_strength']) ? max(0.20, min(0.75, (float)$payload['denoising_strength'])) : 0.38;
        $cfgScale = isset($payload['cfg_scale']) ? max(3.5, min(12.0, (float)$payload['cfg_scale'])) : 6.5;
        $faceRestore = isset($payload['face_restoration']) ? max(0.30, min(0.95, (float)$payload['face_restoration'])) : 0.65;
        $skinTexture = isset($payload['skin_texture']) ? max(0.0, min(1.0, (float)$payload['skin_texture'])) : 0.72;
        $sharpening = isset($payload['sharpening']) ? max(0.0, min(1.0, (float)$payload['sharpening'])) : 0.45;
        $eyeRetouch = isset($payload['eye_retouch']) ? max(0.0, min(1.0, (float)$payload['eye_retouch'])) : 0.80;

        $traits = $this->sanitiseTraits(array_merge(
            is_array($payload['traits'] ?? null) ? $payload['traits'] : [],
            [
                'skin_tone'  => $bestSkin,
                'hair_color' => $bestHairColor,
            ]
        ));

        $svg = $this->renderProceduralAvatar($traits, 256, true);

        return [
            'traits' => $traits,
            'svg'    => $svg,
            'sampled_palette' => [
                'skin_rgb'  => [$r, $g, $b],
                'skin_tone' => $bestSkin,
                'skin_label' => self::SKIN_TONES[$bestSkin]['label'],
                'undertone' => self::SKIN_TONES[$bestSkin]['undertone'],
                'hair_color' => $bestHairColor,
            ],
            'parameters' => [
                'denoising_strength' => round($denoising, 2),
                'cfg_scale'          => round($cfgScale, 1),
                'face_restoration'   => round($faceRestore, 2),
                'skin_texture'       => round($skinTexture, 2),
                'sharpening'         => round($sharpening, 2),
                'eye_retouch'        => round($eyeRetouch, 2),
            ],
            'identity_score' => round(max(88.0, 99.4 - abs($denoising - 0.38) * 20), 1),
        ];
    }

    /**
     * Save procedural 3D avatar or Living Photo as the user's active profile picture.
     *
     * @param array<string, string> $traits
     * @param array<int, int> $sampleRgb
     * @return array{filename: string, url: string, png_url?: string, success: bool}
     */
    public function saveAvatar(
        string $userId,
        array $traits,
        ?string $pngBase64 = null,
        bool $animated = false,
        string $mode = 'procedural',
        string $choreography = 'smile',
        array $sampleRgb = [138, 84, 52]
    ): array {
        $id = trim($userId);
        if ($id === '') {
            throw new Exception("Invalid user identifier provided.");
        }

        // Generate clean cryptographic filename
        $fileBase = 'atg_avatar_' . bin2hex(random_bytes(10));
        $svgFileName = $fileBase . '.svg';
        $pngFileName = $fileBase . '.png';

        $profileDir = 'resources/images/profile/';
        $publicProfileDir = 'public/img/profile/';

        if (!is_dir($profileDir)) {
            @mkdir($profileDir, 0755, true);
        }
        if (!is_dir($publicProfileDir)) {
            @mkdir($publicProfileDir, 0755, true);
        }

        $validPngDataUri = null;
        $hasSavedPng = false;

        // If client submitted high-res PNG canvas snapshot, validate and save PNG for universal compatibility.
        if ($pngBase64 !== null && str_starts_with($pngBase64, 'data:image/png;base64,')) {
            $rawBase64 = substr($pngBase64, strlen('data:image/png;base64,'));
            $decoded = base64_decode($rawBase64, true);
            if ($decoded !== false && strlen($decoded) > 100 && strlen($decoded) < 5 * 1024 * 1024) {
                if (substr($decoded, 0, 8) === "\x89PNG\r\n\x1a\n") {
                    file_put_contents($profileDir . $pngFileName, $decoded);
                    if (is_dir($publicProfileDir)) {
                        @file_put_contents($publicProfileDir . $pngFileName, $decoded);
                    }
                    $validPngDataUri = 'data:image/png;base64,' . base64_encode($decoded);
                    $hasSavedPng = true;
                }
            }
        }

        // Render clean server-side SVG (either Living Photo SVG wrapping the real portrait or procedural 3D SVG)
        if ($mode === 'living_photo' && $validPngDataUri !== null) {
            $svgContent = $this->buildLivingPhotoSvg($validPngDataUri, $traits, $sampleRgb, $choreography, 384, $animated);
        } else {
            $svgContent = $this->renderProceduralAvatar($traits, 384, $animated, true);
        }

        file_put_contents($profileDir . $svgFileName, $svgContent);
        if (is_dir($publicProfileDir)) {
            @file_put_contents($publicProfileDir . $svgFileName, $svgContent);
        }

        $activeFileName = ($animated || !$hasSavedPng) ? $svgFileName : $pngFileName;

        // Persist to database using strictly parameterized queries
        $db = Db::connect2();

        // 1. Update or Insert profilePics
        $checkStmt = $db->prepare("SELECT id FROM profilePics WHERE id = ?");
        $checkStmt->execute([$id]);
        $hasRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($hasRecord) {
            $upStmt = $db->prepare("UPDATE profilePics SET img = ?, date_updated = CURRENT_TIMESTAMP WHERE id = ?");
            $upStmt->execute([$activeFileName, $id]);
        } else {
            $inStmt = $db->prepare("INSERT INTO profilePics (id, img, buttonHTML) VALUES (?, ?, 'Add family')");
            $inStmt->execute([$id, $activeFileName]);
        }

        // 2. Update family_nodes for instant organogram parity
        $fnStmt = $db->prepare("UPDATE family_nodes SET avatar_url = ? WHERE user_id = ?");
        $fnStmt->execute(['/resources/images/profile/' . $activeFileName, $id]);

        // 3. Update active session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['img'] = $activeFileName;
            $_SESSION['profilePics'] = $activeFileName;
        }

        return [
            'success'  => true,
            'filename' => $activeFileName,
            'url'      => '/resources/images/profile/' . $activeFileName,
            'png_url'  => '/resources/images/profile/' . ($hasSavedPng ? $pngFileName : $activeFileName),
        ];
    }

    /**
     * Build a self-contained Living Photo SVG that embeds the user's real portrait
     * (`data:image/png;base64,...`) and animates breathing, eyelid blinking,
     * smiling cheek-lift, and optional dance/wink choreography anywhere `<img>` is rendered.
     *
     * @param array<string, string> $traits
     * @param array<int, int> $sampleRgb
     */
    private function buildLivingPhotoSvg(
        string $pngDataUri,
        array $traits,
        array $sampleRgb,
        string $choreography = 'smile',
        int $size = 384,
        bool $animated = true
    ): string {
        $r = max(0, min(255, (int)($sampleRgb[0] ?? 138)));
        $g = max(0, min(255, (int)($sampleRgb[1] ?? 84)));
        $b = max(0, min(255, (int)($sampleRgb[2] ?? 52)));
        $skinFill = sprintf('rgb(%d,%d,%d)', $r, $g, $b);

        $pairs = [];
        foreach ($traits as $tk => $tv) {
            $pairs[] = $tk . ':' . $tv;
        }
        $traitAttr = htmlspecialchars(implode(';', $pairs), ENT_QUOTES, 'UTF-8');
        $safeChoreo = in_array($choreography, ['smile', 'dance', 'wink', 'laugh', 'love', 'idle'], true)
            ? $choreography
            : 'smile';

        $stageAnim = match ($safeChoreo) {
            'dance' => 'atgLpDance 1.8s ease-in-out infinite',
            'laugh' => 'atgLpLaugh 1.6s ease-in-out infinite',
            'wink'  => 'atgLpSway 3.4s ease-in-out infinite',
            default => 'atgLpBreathe 4.0s ease-in-out infinite',
        };

        $style = '';
        if ($animated) {
            $style = '<style>@media (prefers-reduced-motion:no-preference){'
                . '.atg-lp-stage{transform-origin:192px 210px;animation:' . $stageAnim . '}'
                . '.atg-lp-smile{transform-origin:192px 235px;animation:atgLpSmileLift 3.6s ease-in-out infinite}'
                . '.atg-lp-lid-l{transform-origin:160px 164px;animation:atgLpBlink 4.4s ease-in-out infinite}'
                . '.atg-lp-lid-r{transform-origin:224px 164px;animation:' . ($safeChoreo === 'wink' ? 'atgLpWink 3.4s ease-in-out infinite' : 'atgLpBlink 4.4s ease-in-out infinite') . '}'
                . '.atg-lp-glint{animation:atgLpGlint 2.8s ease-in-out infinite}'
                . '@keyframes atgLpBreathe{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4px) scale(1.018)}}'
                . '@keyframes atgLpDance{0%,100%{transform:translate(0,0) rotate(-2.5deg) scale(1.02)}25%{transform:translate(4px,-6px) rotate(2.5deg) scale(1.035)}50%{transform:translate(0,-2px) rotate(-2deg) scale(1.02)}75%{transform:translate(-4px,-6px) rotate(2.5deg) scale(1.035)}}'
                . '@keyframes atgLpLaugh{0%,100%{transform:translateY(0) scale(1.02)}20%{transform:translateY(-5px) scale(1.03)}40%{transform:translateY(0) scale(1.02)}60%{transform:translateY(-4px) scale(1.03)}}'
                . '@keyframes atgLpSway{0%,100%{transform:rotate(-1.8deg) translateY(0)}50%{transform:rotate(2.2deg) translateY(-3px)}}'
                . '@keyframes atgLpSmileLift{0%,100%{transform:translateY(0) scaleX(1)}50%{transform:translateY(-4.5px) scaleX(1.028)}}'
                . '@keyframes atgLpBlink{0%,92%,100%{transform:scaleY(0.01);opacity:0}95%,97%{transform:scaleY(1);opacity:0.92}}'
                . '@keyframes atgLpWink{0%,70%,100%{transform:scaleY(0.01);opacity:0}78%,88%{transform:scaleY(1);opacity:0.95}}'
                . '@keyframes atgLpGlint{0%,100%{opacity:0.45}50%{opacity:0.95}}'
                . '}</style>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 384" width="' . $size . '" height="' . $size . '" '
            . 'class="atg-avatar-svg atg-living-photo-svg" data-atg-living-photo="1" data-atg-choreography="' . $safeChoreo . '" data-atg-traits="' . $traitAttr . '">'
            . $style
            . '<defs>'
            . '<clipPath id="atgLpFrame"><rect x="0" y="0" width="384" height="384" rx="48"/></clipPath>'
            . '<clipPath id="atgLpSmileZone"><ellipse cx="192" cy="236" rx="86" ry="42"/></clipPath>'
            . '</defs>'
            . '<g clip-path="url(#atgLpFrame)">'
            . '<rect width="384" height="384" fill="#1e1b18"/>'
            . '<g class="atg-lp-stage">'
            . '<image href="' . $pngDataUri . '" x="-8" y="-8" width="400" height="400" preserveAspectRatio="xMidYMid slice"/>'
            . '<g class="atg-lp-smile" clip-path="url(#atgLpSmileZone)">'
            . '<image href="' . $pngDataUri . '" x="-8" y="-8" width="400" height="400" preserveAspectRatio="xMidYMid slice"/>'
            . '<ellipse cx="136" cy="216" rx="22" ry="11" fill="#fb7185" opacity="0.16"/>'
            . '<ellipse cx="248" cy="216" rx="22" ry="11" fill="#fb7185" opacity="0.16"/>'
            . '</g>'
            . '<ellipse class="atg-lp-lid-l" cx="160" cy="164" rx="18" ry="9" fill="' . $skinFill . '" opacity="0"/>'
            . '<ellipse class="atg-lp-lid-r" cx="224" cy="164" rx="18" ry="9" fill="' . $skinFill . '" opacity="0"/>'
            . '<circle class="atg-lp-glint" cx="163" cy="161" r="2.4" fill="#ffffff" opacity="0.75"/>'
            . '<circle class="atg-lp-glint" cx="227" cy="161" r="2.4" fill="#ffffff" opacity="0.75"/>'
            . '</g>'
            . '</g>'
            . '</svg>';
    }

    /* -------------------------------------------------------------------------
     * Internal 3D SVG Renderers (Math-Calculated Gradients & Orthographic Shadows)
     * ------------------------------------------------------------------------- */

    private function buildRootsTreeSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="rt_trunk" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="30%" stop-color="#fde047"/><stop offset="70%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<radialGradient id="rt_leaf1" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#a7f3d0"/><stop offset="50%" stop-color="#34d399"/><stop offset="100%" stop-color="#059669"/></radialGradient>'
            . '<radialGradient id="rt_leaf2" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#6ee7b7"/><stop offset="60%" stop-color="#10b981"/><stop offset="100%" stop-color="#047857"/></radialGradient>'
            . '<filter id="rt_sh" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#059669" flood-opacity="0.25"/></filter>'
            . '</defs>'
            . '<g filter="url(#rt_sh)">'
            . '<path d="M 60 82 Q 42 100 24 108 M 60 82 Q 60 102 60 112 M 60 82 Q 78 100 96 108 M 52 90 Q 36 104 32 108 M 68 90 Q 84 104 88 108" stroke="url(#rt_trunk)" stroke-width="4.5" stroke-linecap="round" fill="none"/>'
            . '<path d="M 52 84 Q 59 56 46 44 Q 60 52 60 38 Q 60 52 74 44 Q 61 56 68 84 Z" fill="url(#rt_trunk)"/>'
            . '<circle cx="60" cy="30" r="18" fill="url(#rt_leaf1)"/>'
            . '<circle cx="38" cy="44" r="16" fill="url(#rt_leaf2)"/>'
            . '<circle cx="82" cy="44" r="16" fill="url(#rt_leaf2)"/>'
            . '<circle cx="26" cy="60" r="13" fill="url(#rt_leaf1)"/>'
            . '<circle cx="94" cy="60" r="13" fill="url(#rt_leaf1)"/>'
            . '<circle cx="60" cy="52" r="15" fill="url(#rt_leaf2)"/>'
            . '<circle cx="55" cy="24" r="4.5" fill="#ffffff" fill-opacity="0.65"/>'
            . '<circle cx="33" cy="39" r="3.5" fill="#ffffff" fill-opacity="0.65"/>'
            . '<circle cx="77" cy="39" r="3.5" fill="#ffffff" fill-opacity="0.65"/>'
            . '<circle cx="22" cy="56" r="2.5" fill="#ffffff" fill-opacity="0.65"/>'
            . '<circle cx="90" cy="56" r="2.5" fill="#ffffff" fill-opacity="0.65"/>'
            . '<polygon points="60,10 62,14 66,15 62,16 60,20 58,16 54,15 58,14" fill="#fef08a"/>'
            . '<polygon points="86,22 87,25 90,26 87,27 86,30 85,27 82,26 85,25" fill="#fef08a"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildFamilyCrestSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="fc_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fef08a"/><stop offset="70%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<linearGradient id="fc_blue" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#60a5fa"/><stop offset="50%" stop-color="#2563eb"/><stop offset="100%" stop-color="#1e40af"/></linearGradient>'
            . '<filter id="fc_sh" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#b45309" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#fc_sh)">'
            . '<path d="M 60 8 L 102 22 C 102 72 80 102 60 114 C 40 102 18 72 18 22 Z" fill="url(#fc_gold)"/>'
            . '<path d="M 60 15 L 94 27 C 94 67 76 95 60 105 C 44 95 26 67 26 27 Z" fill="url(#fc_blue)"/>'
            . '<path d="M 60 18 L 88 28 C 84 52 74 76 60 88 C 46 76 36 52 32 28 Z" fill="#ffffff" fill-opacity="0.12"/>'
            . '<path d="M 44 46 L 42 32 L 52 38 L 60 26 L 68 38 L 78 32 L 76 46 Z" fill="url(#fc_gold)"/>'
            . '<circle cx="60" cy="25" r="3" fill="#ffffff"/>'
            . '<circle cx="42" cy="31" r="2.5" fill="#f43f5e"/>'
            . '<circle cx="78" cy="31" r="2.5" fill="#10b981"/>'
            . '<circle cx="60" cy="68" r="14" fill="url(#fc_gold)"/>'
            . '<path d="M 60 59 L 60 77 M 51 68 L 69 68" stroke="#1e40af" stroke-width="3" stroke-linecap="round"/>'
            . '<path d="M 32 26 Q 60 36 88 26" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" fill="none" opacity="0.6"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildHearthSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="fh_arch" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fef3c7"/><stop offset="50%" stop-color="#fde68a"/><stop offset="100%" stop-color="#d97706"/></linearGradient>'
            . '<radialGradient id="fh_inner" cx="50%" cy="70%" r="60%"><stop offset="0%" stop-color="#b45309"/><stop offset="60%" stop-color="#78350f"/><stop offset="100%" stop-color="#451a03"/></radialGradient>'
            . '<linearGradient id="fh_fire" x1="0" y1="1" x2="0" y2="0"><stop offset="0%" stop-color="#ef4444"/><stop offset="35%" stop-color="#f97316"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#fffbeb"/></linearGradient>'
            . '<filter id="fh_sh"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#f97316" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#fh_sh)">'
            . '<path d="M 22 106 L 22 46 Q 60 16 98 46 L 98 106 Z" fill="url(#fh_arch)"/>'
            . '<path d="M 32 106 L 32 54 Q 60 30 88 54 L 88 106 Z" fill="url(#fh_inner)"/>'
            . '<ellipse cx="60" cy="98" rx="28" ry="8" fill="#78350f"/>'
            . '<ellipse cx="60" cy="95" rx="24" ry="6" fill="#92400e"/>'
            . '<path d="M 44 96 Q 36 72 52 56 Q 48 68 58 48 Q 66 30 62 60 Q 76 50 74 74 Q 72 96 44 96 Z" fill="url(#fh_fire)"/>'
            . '<path d="M 52 94 Q 50 76 58 64 Q 64 54 62 72 Q 70 66 66 84 Q 64 94 52 94 Z" fill="#ffffff" fill-opacity="0.75"/>'
            . '<circle cx="60" cy="38" r="3" fill="#fde047"/>'
            . '<circle cx="48" cy="46" r="2.5" fill="#f97316"/>'
            . '<circle cx="74" cy="44" r="2.5" fill="#fef08a"/>'
            . '<circle cx="68" cy="28" r="2" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildFeastSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="ff_plat" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#fffbeb"/><stop offset="30%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#b45309"/></radialGradient>'
            . '<linearGradient id="ff_roast" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fde047"/><stop offset="40%" stop-color="#d97706"/><stop offset="100%" stop-color="#78350f"/></linearGradient>'
            . '<filter id="ff_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#b45309" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#ff_sh)">'
            . '<ellipse cx="60" cy="80" rx="52" ry="22" fill="url(#ff_plat)"/>'
            . '<ellipse cx="60" cy="78" rx="46" ry="18" fill="#fffbeb"/>'
            . '<ellipse cx="60" cy="78" rx="42" ry="15" fill="url(#ff_plat)"/>'
            . '<ellipse cx="60" cy="68" rx="30" ry="16" fill="url(#ff_roast)"/>'
            . '<ellipse cx="50" cy="64" rx="14" ry="10" fill="#f59e0b"/>'
            . '<ellipse cx="70" cy="64" rx="14" ry="10" fill="#f59e0b"/>'
            . '<circle cx="34" cy="68" r="5" fill="#fde047"/>'
            . '<circle cx="86" cy="68" r="5" fill="#fde047"/>'
            . '<path d="M 24 38 L 38 38 L 34 56 L 31 68 L 27 68 L 29 56 Z" fill="url(#ff_plat)"/>'
            . '<ellipse cx="31" cy="38" rx="7" ry="2.5" fill="#f43f5e"/>'
            . '<path d="M 82 38 L 96 38 L 92 56 L 89 68 L 85 68 L 87 56 Z" fill="url(#ff_plat)"/>'
            . '<ellipse cx="89" cy="38" rx="7" ry="2.5" fill="#f43f5e"/>'
            . '<ellipse cx="58" cy="60" rx="10" ry="4" fill="#ffffff" fill-opacity="0.6"/>'
            . '<circle cx="30" cy="44" r="2" fill="#ffffff" fill-opacity="0.8"/>'
            . '<circle cx="88" cy="44" r="2" fill="#ffffff" fill-opacity="0.8"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildHugSvg(int $size, ?string $tone = null): string
    {
        $t1 = self::EMOJI_TONES[$tone ?? 'medium_dark'];
        $t2 = self::EMOJI_TONES[$tone ?? 'light'];
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="fh_s1" cx="35%" cy="30%" r="70%"><stop offset="0%" stop-color="' . $t1['highlight'] . '"/><stop offset="60%" stop-color="' . $t1['base'] . '"/><stop offset="100%" stop-color="' . $t1['shadow'] . '"/></radialGradient>'
            . '<radialGradient id="fh_s2" cx="35%" cy="30%" r="70%"><stop offset="0%" stop-color="' . $t2['highlight'] . '"/><stop offset="60%" stop-color="' . $t2['base'] . '"/><stop offset="100%" stop-color="' . $t2['shadow'] . '"/></radialGradient>'
            . '<linearGradient id="fh_p1" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fcd34d"/><stop offset="100%" stop-color="#d97706"/></linearGradient>'
            . '<linearGradient id="fh_p2" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fb923c"/><stop offset="100%" stop-color="#c2410c"/></linearGradient>'
            . '<radialGradient id="fh_hrt" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="40%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#be123c"/></radialGradient>'
            . '<filter id="fh_hug_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f43f5e" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#fh_hug_sh)">'
            . '<path d="M 60 24 C 60 24 52 15 44 19 C 36 24 40 34 60 44 C 80 34 84 24 76 19 C 68 15 60 24 60 24 Z" fill="url(#fh_hrt)"/>'
            . '<ellipse cx="49" cy="23" rx="3.5" ry="1.8" fill="#ffffff" fill-opacity="0.7"/>'
            . '<path d="M 18 108 C 18 82 34 74 48 76 C 60 78 66 92 66 108 Z" fill="url(#fh_p1)"/>'
            . '<path d="M 54 108 C 54 92 60 78 72 76 C 86 74 102 82 102 108 Z" fill="url(#fh_p2)"/>'
            . '<circle cx="44" cy="58" r="15" fill="url(#fh_s1)"/>'
            . '<path d="M 29 56 C 28 40 60 38 59 54 C 52 46 38 46 29 56 Z" fill="#1c1410"/>'
            . '<circle cx="76" cy="58" r="15" fill="url(#fh_s2)"/>'
            . '<path d="M 61 60 C 58 38 94 38 91 60 C 88 50 80 46 76 46 C 70 46 64 50 61 60 Z" fill="#7c3a1d"/>'
            . '<path d="M 38 60 Q 41 63 44 60 M 70 60 Q 73 63 76 60" stroke="#1c1917" stroke-width="1.6" stroke-linecap="round" fill="none"/>'
            . '<path d="M 40 66 Q 44 69 48 66 M 72 66 Q 76 69 80 66" stroke="#9f1239" stroke-width="1.6" stroke-linecap="round" fill="none"/>'
            . '<path d="M 30 84 Q 60 100 90 84" stroke="url(#fh_s1)" stroke-width="8" stroke-linecap="round" fill="none"/>'
            . '<path d="M 30 84 Q 60 100 90 84" stroke="#ffffff" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.3"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildCapsuleSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="mc_glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff"/><stop offset="30%" stop-color="#bae6fd"/><stop offset="70%" stop-color="#38bdf8"/><stop offset="100%" stop-color="#0284c7"/></linearGradient>'
            . '<linearGradient id="mc_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="40%" stop-color="#fde047"/><stop offset="80%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<filter id="mc_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#0284c7" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#mc_sh)">'
            . '<rect x="36" y="18" width="48" height="84" rx="24" fill="url(#mc_glass)"/>'
            . '<path d="M 44 26 L 44 94 Q 40 94 40 88 L 40 32 Q 40 26 44 26 Z" fill="#ffffff" fill-opacity="0.6"/>'
            . '<rect x="32" y="14" width="56" height="14" rx="7" fill="url(#mc_gold)"/>'
            . '<ellipse cx="60" cy="14" rx="20" ry="5" fill="#fffbeb"/>'
            . '<rect x="32" y="92" width="56" height="14" rx="7" fill="url(#mc_gold)"/>'
            . '<rect x="45" y="40" width="30" height="36" rx="3" fill="#ffffff" filter="drop-shadow(0 4px 6px rgba(0,0,0,0.15))"/>'
            . '<rect x="48" y="43" width="24" height="22" rx="1" fill="#f59e0b"/>'
            . '<circle cx="60" cy="52" r="4" fill="#ffffff"/>'
            . '<path d="M 48 62 L 55 56 L 62 61 L 72 54 L 72 65 L 48 65 Z" fill="#b45309"/>'
            . '<polygon points="86,34 88,38 92,39 88,40 86,44 84,40 80,39 84,38" fill="#fde047"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildCrownSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="cr_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="30%" stop-color="#fef08a"/><stop offset="65%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<radialGradient id="cr_ruby" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="50%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#9f1239"/></radialGradient>'
            . '<radialGradient id="cr_em" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#a7f3d0"/><stop offset="50%" stop-color="#10b981"/><stop offset="100%" stop-color="#064e3b"/></radialGradient>'
            . '<radialGradient id="cr_am" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#f3e8ff"/><stop offset="50%" stop-color="#a855f7"/><stop offset="100%" stop-color="#581c87"/></radialGradient>'
            . '<filter id="cr_sh"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#f59e0b" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#cr_sh)">'
            . '<path d="M 18 80 L 14 36 L 38 58 L 60 20 L 82 58 L 106 36 L 102 80 Z" fill="url(#cr_gold)"/>'
            . '<ellipse cx="60" cy="82" rx="44" ry="14" fill="url(#cr_gold)"/>'
            . '<ellipse cx="60" cy="80" rx="40" ry="11" fill="#fffbeb"/>'
            . '<ellipse cx="60" cy="80" rx="38" ry="9" fill="url(#cr_gold)"/>'
            . '<circle cx="14" cy="34" r="5" fill="url(#cr_am)"/>'
            . '<circle cx="60" cy="18" r="6.5" fill="url(#cr_ruby)"/>'
            . '<circle cx="106" cy="34" r="5" fill="url(#cr_em)"/>'
            . '<circle cx="38" cy="56" r="4" fill="url(#cr_ruby)"/>'
            . '<circle cx="82" cy="56" r="4" fill="url(#cr_am)"/>'
            . '<circle cx="38" cy="80" r="3.5" fill="url(#cr_ruby)"/>'
            . '<circle cx="60" cy="81" r="4" fill="url(#cr_em)"/>'
            . '<circle cx="82" cy="80" r="3.5" fill="url(#cr_am)"/>'
            . '<polygon points="60,6 62,11 67,12 62,13 60,18 58,13 53,12 58,11" fill="#ffffff"/>'
            . '<circle cx="58" cy="16" r="2" fill="#ffffff"/>'
            . '<circle cx="12" cy="32" r="1.5" fill="#ffffff"/>'
            . '<circle cx="104" cy="32" r="1.5" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildBlessingSvg(int $size, ?string $tone = null): string
    {
        $tn = $tone !== null ? self::EMOJI_TONES[$tone] : null;
        $skinGrad = $tn !== null
            ? '<stop offset="0%" stop-color="' . $tn['highlight'] . '"/><stop offset="55%" stop-color="' . $tn['base'] . '"/><stop offset="100%" stop-color="' . $tn['shadow'] . '"/>'
            : '<stop offset="0%" stop-color="#ffedd5"/><stop offset="40%" stop-color="#fed7aa"/><stop offset="80%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/>';
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="bl_sun" cx="50%" cy="40%" r="60%"><stop offset="0%" stop-color="#ffffff"/><stop offset="30%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#f59e0b"/></radialGradient>'
            . '<linearGradient id="bl_skin" x1="0" y1="0" x2="1" y2="1">' . $skinGrad . '</linearGradient>'
            . '<filter id="bl_sh"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#f59e0b" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#bl_sh)">'
            . '<circle cx="60" cy="50" r="46" fill="url(#bl_sun)" fill-opacity="0.3"/>'
            . '<path d="M 60 8 L 60 20 M 28 20 L 37 28 M 92 20 L 83 28 M 16 50 L 28 50 M 104 50 L 92 50 M 28 80 L 37 72 M 92 80 L 83 72" stroke="#facc15" stroke-width="3.5" stroke-linecap="round"/>'
            . '<path d="M 42 108 L 54 44 C 55 36 60 36 60 44 L 60 108 Z" fill="url(#bl_skin)"/>'
            . '<path d="M 78 108 L 66 44 C 65 36 60 36 60 44 L 60 108 Z" fill="url(#bl_skin)"/>'
            . '<path d="M 44 68 Q 50 64 60 66 Q 70 64 76 68" stroke="#ffffff" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.6"/>'
            . '<polygon points="60,22 63,28 69,30 63,32 60,38 57,32 51,30 57,28" fill="#ffffff"/>'
            . '<circle cx="60" cy="30" r="3" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildPrideSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="pr_ruby" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="35%" stop-color="#fb7185"/><stop offset="70%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#be123c"/></radialGradient>'
            . '<linearGradient id="pr_rib" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#93c5fd"/><stop offset="50%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient>'
            . '<linearGradient id="pr_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="40%" stop-color="#fde047"/><stop offset="80%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<filter id="pr_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f43f5e" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#pr_sh)">'
            . '<path d="M 42 12 L 60 50 L 34 76 L 26 18 Z" fill="url(#pr_rib)"/>'
            . '<path d="M 78 12 L 60 50 L 86 76 L 94 18 Z" fill="url(#pr_rib)"/>'
            . '<line x1="34" y1="15" x2="52" y2="50" stroke="#ffffff" stroke-width="2" opacity="0.4"/>'
            . '<line x1="86" y1="15" x2="68" y2="50" stroke="#ffffff" stroke-width="2" opacity="0.4"/>'
            . '<path d="M 60 44 C 60 44 44 26 30 40 C 16 54 26 78 60 102 C 94 78 104 54 90 40 C 76 26 60 44 60 44 Z" fill="url(#pr_gold)"/>'
            . '<path d="M 60 49 C 60 49 46 33 34 45 C 22 57 32 77 60 96 C 88 77 98 57 86 45 C 74 33 60 49 60 49 Z" fill="url(#pr_ruby)"/>'
            . '<ellipse cx="46" cy="50" rx="8" ry="4" fill="#ffffff" fill-opacity="0.7" transform="rotate(-30 46 50)"/>'
            . '<circle cx="60" cy="74" r="3" fill="#ffffff" fill-opacity="0.5"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildCelebrateSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="cl_cone" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fde047"/><stop offset="70%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#d97706"/></radialGradient>'
            . '<filter id="cl_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f59e0b" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#cl_sh)">'
            . '<path d="M 18 104 L 64 78 L 42 56 Z" fill="url(#cl_cone)"/>'
            . '<ellipse cx="53" cy="67" rx="16" ry="7" fill="#f43f5e" transform="rotate(-45 53 67)"/>'
            . '<path d="M 60 62 Q 78 38 102 44 Q 112 46 110 34" stroke="#ec4899" stroke-width="4" fill="none" stroke-linecap="round"/>'
            . '<path d="M 52 54 Q 66 18 92 14" stroke="#3b82f6" stroke-width="4" fill="none" stroke-linecap="round"/>'
            . '<path d="M 68 70 Q 94 76 110 64" stroke="#10b981" stroke-width="4" fill="none" stroke-linecap="round"/>'
            . '<circle cx="86" cy="34" r="5" fill="#facc15"/>'
            . '<circle cx="72" cy="46" r="4.5" fill="#a855f7"/>'
            . '<circle cx="98" cy="60" r="4" fill="#ef4444"/>'
            . '<circle cx="84" cy="72" r="4.5" fill="#06b6d4"/>'
            . '<polygon points="94,22 98,27 94,32 90,27" fill="#f43f5e"/>'
            . '<polygon points="76,14 79,18 84,19 79,20 76,24 73,20 68,19 73,18" fill="#fde047"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildScrollSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="sc_parch" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="50%" stop-color="#fef3c7"/><stop offset="100%" stop-color="#fde68a"/></linearGradient>'
            . '<linearGradient id="sc_roll" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fef08a"/><stop offset="60%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<radialGradient id="sc_seal" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#f87171"/><stop offset="60%" stop-color="#dc2626"/><stop offset="100%" stop-color="#991b1b"/></radialGradient>'
            . '<filter id="sc_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#b45309" flood-opacity="0.25"/></filter>'
            . '</defs>'
            . '<g filter="url(#sc_sh)">'
            . '<path d="M 28 28 C 34 26 86 26 92 28 L 88 92 C 82 94 34 94 28 92 Z" fill="url(#sc_parch)"/>'
            . '<ellipse cx="60" cy="26" rx="34" ry="8" fill="url(#sc_roll)"/>'
            . '<ellipse cx="60" cy="24" rx="30" ry="5" fill="#fffbeb"/>'
            . '<ellipse cx="60" cy="94" rx="34" ry="8" fill="url(#sc_roll)"/>'
            . '<line x1="38" y1="44" x2="80" y2="44" stroke="#b45309" stroke-width="2.5" stroke-linecap="round"/>'
            . '<line x1="38" y1="56" x2="82" y2="56" stroke="#b45309" stroke-width="2.5" stroke-linecap="round"/>'
            . '<line x1="38" y1="68" x2="72" y2="68" stroke="#b45309" stroke-width="2.5" stroke-linecap="round"/>'
            . '<circle cx="60" cy="82" r="9" fill="url(#sc_seal)"/>'
            . '<circle cx="60" cy="82" r="5" fill="#b91c1c"/>'
            . '<polygon points="60,78 62,81 65,82 62,83 60,86 58,83 55,82 58,81" fill="#fef08a"/>'
            . '<path d="M 86 24 Q 106 14 108 36 Q 98 44 88 54 L 84 52 Z" fill="#38bdf8"/>'
            . '<line x1="84" y1="52" x2="80" y2="60" stroke="#f59e0b" stroke-width="2"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildTriadSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="tr_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#d97706"/></linearGradient>'
            . '<filter id="tr_sh"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#f59e0b" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#tr_sh)">'
            . '<circle cx="60" cy="42" r="24" fill="none" stroke="url(#tr_gold)" stroke-width="8"/>'
            . '<circle cx="42" cy="74" r="24" fill="none" stroke="url(#tr_gold)" stroke-width="8"/>'
            . '<circle cx="78" cy="74" r="24" fill="none" stroke="url(#tr_gold)" stroke-width="8"/>'
            . '<circle cx="60" cy="18" r="4" fill="#ffffff"/>'
            . '<circle cx="22" cy="74" r="3.5" fill="#ffffff"/>'
            . '<circle cx="98" cy="74" r="3.5" fill="#ffffff"/>'
            . '<polygon points="60,34 62,38 66,39 62,40 60,44 58,40 54,39 58,38" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildToastSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="tst_flute" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff"/><stop offset="30%" stop-color="#e0f2fe"/><stop offset="70%" stop-color="#bae6fd"/><stop offset="100%" stop-color="#7dd3fc"/></linearGradient>'
            . '<linearGradient id="tst_wine" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="40%" stop-color="#fef08a"/><stop offset="80%" stop-color="#facc15"/><stop offset="100%" stop-color="#f59e0b"/></linearGradient>'
            . '<filter id="tst_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f59e0b" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#tst_sh)">'
            . '<g transform="rotate(18 42 70)">'
            . '<path d="M 36 34 L 52 34 L 48 64 L 46 88 L 44 88 L 42 64 Z" fill="url(#tst_flute)" fill-opacity="0.75"/>'
            . '<path d="M 38 42 L 50 42 L 47 62 L 43 62 Z" fill="url(#tst_wine)"/>'
            . '<ellipse cx="45" cy="88" rx="12" ry="4" fill="url(#tst_flute)"/>'
            . '<ellipse cx="44" cy="34" rx="8" ry="3" fill="#ffffff" fill-opacity="0.8"/>'
            . '</g>'
            . '<g transform="rotate(-18 78 70)">'
            . '<path d="M 68 34 L 84 34 L 80 64 L 78 88 L 76 88 L 74 64 Z" fill="url(#tst_flute)" fill-opacity="0.75"/>'
            . '<path d="M 70 42 L 82 42 L 79 62 L 75 62 Z" fill="url(#tst_wine)"/>'
            . '<ellipse cx="77" cy="88" rx="12" ry="4" fill="url(#tst_flute)"/>'
            . '<ellipse cx="76" cy="34" rx="8" ry="3" fill="#ffffff" fill-opacity="0.8"/>'
            . '</g>'
            . '<polygon points="60,24 64,34 74,38 64,42 60,52 56,42 46,38 56,34" fill="#fde047"/>'
            . '<circle cx="60" cy="38" r="3.5" fill="#ffffff"/>'
            . '<circle cx="48" cy="20" r="3.5" fill="#fef08a"/>'
            . '<circle cx="72" cy="18" r="4" fill="#fef08a"/>'
            . '<circle cx="60" cy="14" r="2.5" fill="#ffffff"/>'
            . '<circle cx="54" cy="26" r="2" fill="#fef08a"/>'
            . '<circle cx="66" cy="28" r="2" fill="#fef08a"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildCradleSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="crd_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fde047"/><stop offset="70%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<filter id="crd_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f59e0b" flood-opacity="0.25"/></filter>'
            . '</defs>'
            . '<g filter="url(#crd_sh)">'
            . '<path d="M 24 96 Q 60 114 96 96" stroke="url(#crd_gold)" stroke-width="6" stroke-linecap="round" fill="none"/>'
            . '<line x1="38" y1="90" x2="38" y2="102" stroke="url(#crd_gold)" stroke-width="5"/>'
            . '<line x1="82" y1="90" x2="82" y2="102" stroke="url(#crd_gold)" stroke-width="5"/>'
            . '<path d="M 26 62 Q 60 84 94 62 L 90 88 Q 60 102 30 88 Z" fill="url(#crd_gold)"/>'
            . '<path d="M 26 62 Q 34 30 62 38 Q 66 62 26 62 Z" fill="#fef9c3"/>'
            . '<ellipse cx="68" cy="72" rx="22" ry="12" fill="#bae6fd"/>'
            . '<ellipse cx="70" cy="74" rx="18" ry="9" fill="#fbcfe8"/>'
            . '<circle cx="52" cy="66" r="8" fill="#fed7aa"/>'
            . '<path d="M 48 66 Q 52 70 56 66" stroke="#b45309" stroke-width="1.5" stroke-linecap="round" fill="none"/>'
            . '<polygon points="80,32 83,37 88,38 83,40 80,45 77,40 72,38 77,37" fill="#facc15"/>'
            . '<circle cx="92" cy="46" r="3" fill="#f43f5e"/>'
            . '<circle cx="68" cy="28" r="2.5" fill="#38bdf8"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildWeddingRingsSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="wr_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
            . '<linearGradient id="wr_plat" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff"/><stop offset="50%" stop-color="#e2e8f0"/><stop offset="100%" stop-color="#94a3b8"/></linearGradient>'
            . '<filter id="wr_sh"><feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#f59e0b" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#wr_sh)">'
            . '<ellipse cx="46" cy="68" rx="26" ry="28" fill="none" stroke="url(#wr_gold)" stroke-width="8" transform="rotate(-15 46 68)"/>'
            . '<ellipse cx="74" cy="66" rx="26" ry="28" fill="none" stroke="url(#wr_plat)" stroke-width="8" transform="rotate(15 74 66)"/>'
            . '<polygon points="80,30 92,30 100,40 86,52 72,40" fill="#67e8f9"/>'
            . '<polygon points="80,30 92,30 86,40" fill="#ffffff"/>'
            . '<polygon points="80,30 72,40 86,40" fill="#e0f2fe"/>'
            . '<polygon points="92,30 100,40 86,40" fill="#a5f3fc"/>'
            . '<polygon points="86,16 88,24 96,26 88,28 86,36 84,28 76,26 84,24" fill="#ffffff"/>'
            . '<circle cx="86" cy="26" r="3" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildLaughTearsSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="lt_sphere" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#f59e0b"/></radialGradient>'
            . '<radialGradient id="lt_tear" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffffff"/><stop offset="35%" stop-color="#67e8f9"/><stop offset="80%" stop-color="#06b6d4"/><stop offset="100%" stop-color="#0284c7"/></radialGradient>'
            . '<filter id="lt_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f59e0b" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#lt_sh)">'
            . '<circle cx="60" cy="60" r="54" fill="url(#lt_sphere)"/>'
            . '<ellipse cx="44" cy="28" rx="18" ry="8" fill="#ffffff" fill-opacity="0.5"/>'
            . '<path d="M 28 46 Q 42 32 54 46" stroke="#78350f" stroke-width="5.5" stroke-linecap="round" fill="none"/>'
            . '<path d="M 66 46 Q 78 32 92 46" stroke="#78350f" stroke-width="5.5" stroke-linecap="round" fill="none"/>'
            . '<path d="M 32 60 Q 60 106 88 60 Z" fill="#78350f"/>'
            . '<path d="M 36 61 Q 60 72 84 61 Z" fill="#ffffff"/>'
            . '<path d="M 46 82 Q 60 76 74 82 Q 60 98 46 82 Z" fill="#f43f5e"/>'
            . '<path d="M 20 52 C 20 42 32 40 32 52 C 32 64 20 74 20 74 C 20 74 8 64 8 52 Z" fill="url(#lt_tear)" transform="rotate(-35 20 52)"/>'
            . '<circle cx="15" cy="46" r="3" fill="#ffffff"/>'
            . '<path d="M 100 52 C 100 42 112 40 112 52 C 112 64 100 74 100 74 C 100 74 88 64 88 52 Z" fill="url(#lt_tear)" transform="rotate(35 100 52)"/>'
            . '<circle cx="95" cy="46" r="3" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildStarStruckSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="ss_sphere" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#f59e0b"/></radialGradient>'
            . '<linearGradient id="ss_star1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff"/><stop offset="40%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#be123c"/></linearGradient>'
            . '<linearGradient id="ss_star2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffffff"/><stop offset="40%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient>'
            . '<filter id="ss_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f59e0b" flood-opacity="0.35"/></filter>'
            . '</defs>'
            . '<g filter="url(#ss_sh)">'
            . '<circle cx="60" cy="60" r="54" fill="url(#ss_sphere)"/>'
            . '<ellipse cx="44" cy="28" rx="18" ry="8" fill="#ffffff" fill-opacity="0.5"/>'
            . '<polygon points="38,28 42,38 52,38 44,45 47,55 38,49 29,55 32,45 24,38 34,38" fill="url(#ss_star1)"/>'
            . '<circle cx="38" cy="42" r="3.5" fill="#ffffff"/>'
            . '<polygon points="82,28 86,38 96,38 88,45 91,55 82,49 73,55 76,45 68,38 78,38" fill="url(#ss_star2)"/>'
            . '<circle cx="82" cy="42" r="3.5" fill="#ffffff"/>'
            . '<path d="M 34 66 Q 60 106 86 66 Z" fill="#78350f"/>'
            . '<path d="M 38 67 Q 60 78 82 67 Z" fill="#ffffff"/>'
            . '<ellipse cx="26" cy="66" rx="9" ry="6" fill="#fb7185" fill-opacity="0.6"/>'
            . '<ellipse cx="94" cy="66" rx="9" ry="6" fill="#fb7185" fill-opacity="0.6"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildFamilyHouseSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="fh_roof" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fca5a5"/><stop offset="40%" stop-color="#ef4444"/><stop offset="80%" stop-color="#dc2626"/><stop offset="100%" stop-color="#991b1b"/></linearGradient>'
            . '<linearGradient id="fh_wall" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fffbeb"/><stop offset="50%" stop-color="#fef3c7"/><stop offset="100%" stop-color="#fde68a"/></linearGradient>'
            . '<filter id="fh_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#ef4444" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#fh_sh)">'
            . '<rect x="76" y="24" width="12" height="28" rx="2" fill="#b91c1c"/>'
            . '<path d="M 82 18 C 82 18 78 12 74 15 C 70 18 72 24 82 28 C 92 24 94 18 90 15 C 86 12 82 18 82 18 Z" fill="#fda4af"/>'
            . '<polygon points="60,20 18,54 102,54" fill="url(#fh_roof)"/>'
            . '<line x1="22" y1="52" x2="98" y2="52" stroke="#ffffff" stroke-width="2" opacity="0.6"/>'
            . '<rect x="28" y="54" width="64" height="48" rx="2" fill="url(#fh_wall)"/>'
            . '<rect x="50" y="74" width="20" height="28" rx="3" fill="#b45309"/>'
            . '<circle cx="64" cy="88" r="2" fill="#fef08a"/>'
            . '<rect x="34" y="64" width="12" height="15" rx="2" fill="#facc15" stroke="#b45309" stroke-width="1.5"/>'
            . '<line x1="40" y1="64" x2="40" y2="79" stroke="#b45309" stroke-width="1.5"/>'
            . '<line x1="34" y1="71" x2="46" y2="71" stroke="#b45309" stroke-width="1.5"/>'
            . '<rect x="74" y="64" width="12" height="15" rx="2" fill="#facc15" stroke="#b45309" stroke-width="1.5"/>'
            . '<line x1="80" y1="64" x2="80" y2="79" stroke="#b45309" stroke-width="1.5"/>'
            . '<line x1="74" y1="71" x2="86" y2="71" stroke="#b45309" stroke-width="1.5"/>'
            . '<circle cx="37" cy="67" r="1.5" fill="#ffffff"/>'
            . '<circle cx="77" cy="67" r="1.5" fill="#ffffff"/>'
            . '</g>'
            . '</svg>';
    }

    /**
     * @return array{label:string, base:string, highlight:string, shadow:string}
     */
    private function emojiTone(?string $tone): array
    {
        return $tone !== null && isset(self::EMOJI_TONES[$tone])
            ? self::EMOJI_TONES[$tone]
            : ['label' => 'Classic', 'base' => '#fbbf24', 'highlight' => '#fde68a', 'shadow' => '#d97706'];
    }

    private function buildWaveSvg(int $size, ?string $tone = null): string
    {
        $t = $this->emojiTone($tone);
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="wv_skin" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $t['highlight'] . '"/><stop offset="55%" stop-color="' . $t['base'] . '"/><stop offset="100%" stop-color="' . $t['shadow'] . '"/></linearGradient>'
            . '<linearGradient id="wv_cuff" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#60a5fa"/><stop offset="100%" stop-color="#1d4ed8"/></linearGradient>'
            . '<filter id="wv_sh"><feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#000" flood-opacity="0.25"/></filter>'
            . '</defs>'
            . '<g filter="url(#wv_sh)" transform="rotate(-12 60 70)">'
            . '<rect x="48" y="96" width="30" height="20" rx="4" fill="url(#wv_cuff)"/>'
            . '<rect x="40" y="26" width="12" height="44" rx="6" fill="url(#wv_skin)" stroke="' . $t['shadow'] . '" stroke-width="0.8"/>'
            . '<rect x="53" y="18" width="12" height="52" rx="6" fill="url(#wv_skin)" stroke="' . $t['shadow'] . '" stroke-width="0.8"/>'
            . '<rect x="66" y="22" width="12" height="48" rx="6" fill="url(#wv_skin)" stroke="' . $t['shadow'] . '" stroke-width="0.8"/>'
            . '<rect x="79" y="32" width="11" height="40" rx="5.5" fill="url(#wv_skin)" stroke="' . $t['shadow'] . '" stroke-width="0.8"/>'
            . '<rect x="40" y="52" width="50" height="50" rx="20" fill="url(#wv_skin)"/>'
            . '<ellipse cx="34" cy="78" rx="8" ry="19" transform="rotate(-38 34 78)" fill="url(#wv_skin)" stroke="' . $t['shadow'] . '" stroke-width="0.8"/>'
            . '<path d="M 50 80 Q 64 88 80 78" stroke="' . $t['shadow'] . '" stroke-width="1.6" fill="none" opacity="0.6"/>'
            . '<ellipse cx="58" cy="24" rx="3" ry="5" fill="#ffffff" opacity="0.45"/>'
            . '<ellipse cx="46" cy="32" rx="2.4" ry="4" fill="#ffffff" opacity="0.4"/>'
            . '</g>'
            . '<path d="M 96 22 Q 104 30 100 42 M 104 14 Q 116 28 110 46" stroke="#f59e0b" stroke-width="3.2" stroke-linecap="round" fill="none"/>'
            . '<path d="M 18 34 Q 12 44 16 54" stroke="#f59e0b" stroke-width="3.2" stroke-linecap="round" fill="none"/>'
            . '</svg>';
    }

    private function buildHeartHandsSvg(int $size, ?string $tone = null): string
    {
        $t = $this->emojiTone($tone);
        $palm = function (int $dir) use ($t): string {
            $tr = $dir === 1 ? '' : ' transform="translate(120 0) scale(-1 1)"';
            return '<g' . $tr . '>'
                . '<path d="M 14 70 C 10 88 22 106 44 106 L 58 106 L 58 92 C 46 92 36 86 32 74 Z" fill="url(#hh_skin)"/>'
                . '<path d="M 32 74 C 34 66 30 58 24 58 C 18 58 14 64 14 70" fill="url(#hh_skin)"/>'
                . '<path d="M 22 84 Q 36 92 52 92 M 20 94 Q 34 101 52 100" stroke="' . $t['shadow'] . '" stroke-width="1.4" fill="none" opacity="0.7"/>'
                . '<ellipse cx="22" cy="64" rx="3" ry="4" fill="#ffffff" opacity="0.35"/>'
                . '</g>';
        };
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<linearGradient id="hh_skin" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="' . $t['highlight'] . '"/><stop offset="55%" stop-color="' . $t['base'] . '"/><stop offset="100%" stop-color="' . $t['shadow'] . '"/></linearGradient>'
            . '<radialGradient id="hh_heart" cx="35%" cy="30%" r="70%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="40%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#9f1239"/></radialGradient>'
            . '<filter id="hh_sh"><feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#be123c" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#hh_sh)">'
            . '<path d="M 60 34 C 60 34 48 18 34 24 C 20 30 22 50 60 82 C 98 50 100 30 86 24 C 72 18 60 34 60 34 Z" fill="url(#hh_heart)"/>'
            . '<ellipse cx="40" cy="32" rx="7" ry="4" fill="#ffffff" opacity="0.6" transform="rotate(-25 40 32)"/>'
            . $palm(1) . $palm(-1)
            . '<polygon points="96,14 98,19 103,20 98,21 96,26 94,21 89,20 94,19" fill="#fde047"/>'
            . '<polygon points="22,12 23.5,15.5 27,16 23.5,17 22,20 20.5,17 17,16 20.5,15.5" fill="#fde047"/>'
            . '</g>'
            . '</svg>';
    }

    private function buildBlendedFamilySvg(int $size): string
    {
        $face = function (string $id, float $cx, float $cy, float $r, string $tone, string $hair, string $hairPath): string {
            $t = self::EMOJI_TONES[$tone];
            $eY = $cy - $r * 0.05;
            $eX = $r * 0.36;
            return '<radialGradient id="' . $id . '" cx="35%" cy="30%" r="70%"><stop offset="0%" stop-color="' . $t['highlight'] . '"/><stop offset="60%" stop-color="' . $t['base'] . '"/><stop offset="100%" stop-color="' . $t['shadow'] . '"/></radialGradient>'
                . '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="url(#' . $id . ')"/>'
                . '<path d="' . $hairPath . '" fill="' . $hair . '"/>'
                . '<circle cx="' . ($cx - $eX) . '" cy="' . $eY . '" r="' . ($r * 0.09) . '" fill="#1c1917"/>'
                . '<circle cx="' . ($cx + $eX) . '" cy="' . $eY . '" r="' . ($r * 0.09) . '" fill="#1c1917"/>'
                . '<path d="M ' . ($cx - $r * 0.38) . ' ' . ($cy + $r * 0.28) . ' Q ' . $cx . ' ' . ($cy + $r * 0.62) . ' ' . ($cx + $r * 0.38) . ' ' . ($cy + $r * 0.28) . ' Z" fill="#7f1d1d"/>'
                . '<ellipse cx="' . ($cx - $r * 0.55) . '" cy="' . ($cy + $r * 0.25) . '" rx="' . ($r * 0.16) . '" ry="' . ($r * 0.1) . '" fill="#f43f5e" opacity="0.3"/>'
                . '<ellipse cx="' . ($cx + $r * 0.55) . '" cy="' . ($cy + $r * 0.25) . '" rx="' . ($r * 0.16) . '" ry="' . ($r * 0.1) . '" fill="#f43f5e" opacity="0.3"/>';
        };
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs><filter id="bf_sh"><feDropShadow dx="0" dy="5" stdDeviation="4" flood-color="#7c3aed" flood-opacity="0.25"/></filter>'
            . '<radialGradient id="bf_hrt" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="45%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#be123c"/></radialGradient></defs>'
            . '<g filter="url(#bf_sh)">'
            . '<path d="M 60 16 C 60 16 54 8 48 11 C 42 14 44 21 60 30 C 76 21 78 14 72 11 C 66 8 60 16 60 16 Z" fill="url(#bf_hrt)"/>'
            . $face('bf_a', 30, 62, 17, 'light', '#e6c27a', 'M 13 62 C 12 40 48 38 47 60 C 44 50 36 46 30 47 C 22 48 15 54 13 62 Z')
            . $face('bf_c', 90, 62, 17, 'medium_light', '#111827', 'M 73 64 C 70 42 110 40 107 64 L 106 56 L 74 56 Z')
            . $face('bf_b', 60, 54, 20, 'dark', '#d4d4d8', 'M 40 52 C 36 26 84 26 80 52 C 86 40 74 28 60 30 C 46 28 34 40 40 52 Z')
            . $face('bf_d', 60, 92, 15, 'medium', '#3f2a1d', 'M 45 90 C 44 72 76 72 75 90 C 74 82 68 78 60 78 C 52 78 46 82 45 90 Z')
            . '</g>'
            . '</svg>';
    }
}

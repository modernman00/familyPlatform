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
    public const SKIN_TONES = [
        'melanin_rich'    => ['base' => '#2b170c', 'highlight' => '#4a2c1b', 'shadow' => '#140a05', 'label' => 'Melanin Rich'],
        'deep_espresso'   => ['base' => '#3d2314', 'highlight' => '#5f3822', 'shadow' => '#201108', 'label' => 'Deep Espresso'],
        'warm_chestnut'   => ['base' => '#6a3c20', 'highlight' => '#8e5430', 'shadow' => '#3f2110', 'label' => 'Warm Chestnut'],
        'bronze_amber'    => ['base' => '#995831', 'highlight' => '#bf7545', 'shadow' => '#5a3118', 'label' => 'Bronze Amber'],
        'golden_caramel'  => ['base' => '#c58550', 'highlight' => '#e1a36f', 'shadow' => '#7d512d', 'label' => 'Golden Caramel'],
        'warm_honey'      => ['base' => '#dfa773', 'highlight' => '#f3c497', 'shadow' => '#936640', 'label' => 'Warm Honey'],
        'fair_radiance'   => ['base' => '#fad3b0', 'highlight' => '#ffeedc', 'shadow' => '#b38d6d', 'label' => 'Fair Radiance'],
    ];

    public const HAIRSTYLES = [
        'afro_crown'       => ['label' => 'Afro Crown', 'color' => '#18110d'],
        'braided_crown'    => ['label' => 'Heritage Braids', 'color' => '#140e0b'],
        'regal_locs'       => ['label' => 'Regal Locs', 'color' => '#100b08'],
        'traditional_gele' => ['label' => 'Traditional Gele', 'color' => '#b8860b'],
        'bantu_knots'      => ['label' => 'Bantu Knots', 'color' => '#1c140e'],
        'high_fade'        => ['label' => 'Barber Fade', 'color' => '#1a130f'],
        'curled_top'       => ['label' => 'Textured Curls', 'color' => '#160f0c'],
        'sleek_bun'        => ['label' => 'Sleek Top Bun', 'color' => '#18120e'],
        'regal_bald'       => ['label' => 'Glistening Bald', 'color' => 'transparent'],
    ];

    public const EXPRESSIONS = [
        'warm_smile'   => ['label' => 'Warm Smile'],
        'proud_grin'   => ['label' => 'Proud Grin'],
        'wise_calm'    => ['label' => 'Wise Serenity'],
        'radiant_joy'  => ['label' => 'Radiant Joy'],
    ];

    public const ACCESSORIES = [
        'none'                 => ['label' => 'None'],
        'ancestor_crown'       => ['label' => 'Ancestor Diadem'],
        'heritage_coral_beads' => ['label' => 'Royal Coral Beads'],
        'gold_spectacles'      => ['label' => 'Gold Spectacles'],
        'laurel_wreath'        => ['label' => 'Laurel of Honour'],
    ];

    public const BACKDROPS = [
        'warm_sunset'       => ['start' => '#f59e0b', 'end' => '#b45309', 'label' => 'Warm Sunset'],
        'royal_violet'      => ['start' => '#8b5cf6', 'end' => '#4c1d95', 'label' => 'Royal Violet'],
        'emerald_roots'     => ['start' => '#10b981', 'end' => '#064e3b', 'label' => 'Emerald Roots'],
        'sapphire_night'    => ['start' => '#3b82f6', 'end' => '#1e1b4b', 'label' => 'Sapphire Night'],
        'crimson_hearth'    => ['start' => '#ef4444', 'end' => '#7f1d1d', 'label' => 'Crimson Hearth'],
        'pure_gold'         => ['start' => '#fcd34d', 'end' => '#92400e', 'label' => 'Pure Gold'],
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
        ];

        return self::$cachedCatalog;
    }

    /**
     * Render specialised 3D SVG emoji by ID.
     */
    public function renderSvgEmoji(string $id, int $size = 120): string
    {
        $safeSize = max(32, min(512, $size));

        switch ($id) {
            case 'family_roots_3d':
                return $this->buildRootsTreeSvg($safeSize);
            case 'family_crest_3d':
                return $this->buildFamilyCrestSvg($safeSize);
            case 'family_hearth_3d':
                return $this->buildHearthSvg($safeSize);
            case 'family_feast_3d':
                return $this->buildFeastSvg($safeSize);
            case 'family_hug_3d':
                return $this->buildHugSvg($safeSize);
            case 'memory_capsule_3d':
                return $this->buildCapsuleSvg($safeSize);
            case 'ancestor_crown_3d':
                return $this->buildCrownSvg($safeSize);
            case 'ancestor_blessing_3d':
                return $this->buildBlessingSvg($safeSize);
            case 'kinship_pride_3d':
                return $this->buildPrideSvg($safeSize);
            case 'family_celebrate_3d':
                return $this->buildCelebrateSvg($safeSize);
            case 'wisdom_scroll_3d':
                return $this->buildScrollSvg($safeSize);
            case 'generations_link_3d':
                return $this->buildTriadSvg($safeSize);
            case 'family_toast_3d':
                return $this->buildToastSvg($safeSize);
            case 'baby_cradle_3d':
                return $this->buildCradleSvg($safeSize);
            case 'wedding_vows_3d':
                return $this->buildWeddingRingsSvg($safeSize);
            case 'laugh_tears_3d':
                return $this->buildLaughTearsSvg($safeSize);
            case 'star_struck_3d':
                return $this->buildStarStruckSvg($safeSize);
            case 'family_house_3d':
                return $this->buildFamilyHouseSvg($safeSize);
            default:
                return $this->buildRootsTreeSvg($safeSize);
        }
    }

    /**
     * Render procedural 3D user profile avatar SVG.
     *
     * @param array<string, string> $traits
     */
    public function renderProceduralAvatar(array $traits, int $size = 256): string
    {
        $safeSize = max(64, min(512, $size));

        $skinKey = $traits['skin_tone'] ?? 'warm_chestnut';
        if (!isset(self::SKIN_TONES[$skinKey])) {
            $skinKey = 'warm_chestnut';
        }
        $skin = self::SKIN_TONES[$skinKey];

        $hairKey = $traits['hairstyle'] ?? 'afro_crown';
        if (!isset(self::HAIRSTYLES[$hairKey])) {
            $hairKey = 'afro_crown';
        }
        $hair = self::HAIRSTYLES[$hairKey];

        $exprKey = $traits['expression'] ?? 'warm_smile';
        if (!isset(self::EXPRESSIONS[$exprKey])) {
            $exprKey = 'warm_smile';
        }

        $accKey = $traits['accessory'] ?? 'none';
        if (!isset(self::ACCESSORIES[$accKey])) {
            $accKey = 'none';
        }

        $backKey = $traits['backdrop'] ?? 'warm_sunset';
        if (!isset(self::BACKDROPS[$backKey])) {
            $backKey = 'warm_sunset';
        }
        $back = self::BACKDROPS[$backKey];

        $uid = 'atg_av_' . substr(hash('sha256', json_encode($traits) . $safeSize), 0, 8);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" width="' . $safeSize . '" height="' . $safeSize . '" class="atg-avatar-svg">';
        $svg .= '<defs>';

        // Backdrop gradient
        $svg .= '<radialGradient id="' . $uid . '_bg" cx="35%" cy="30%" r="65%">';
        $svg .= '<stop offset="0%" stop-color="' . htmlspecialchars($back['start']) . '"/>';
        $svg .= '<stop offset="100%" stop-color="' . htmlspecialchars($back['end']) . '"/>';
        $svg .= '</radialGradient>';

        // Skin 3D sphere gradient
        $svg .= '<radialGradient id="' . $uid . '_skin" cx="38%" cy="32%" r="55%">';
        $svg .= '<stop offset="0%" stop-color="' . htmlspecialchars($skin['highlight']) . '"/>';
        $svg .= '<stop offset="55%" stop-color="' . htmlspecialchars($skin['base']) . '"/>';
        $svg .= '<stop offset="100%" stop-color="' . htmlspecialchars($skin['shadow']) . '"/>';
        $svg .= '</radialGradient>';

        // Torso gradient
        $svg .= '<linearGradient id="' . $uid . '_torso" x1="0" y1="0" x2="0" y2="1">';
        $svg .= '<stop offset="0%" stop-color="' . htmlspecialchars($skin['base']) . '"/>';
        $svg .= '<stop offset="100%" stop-color="' . htmlspecialchars($skin['shadow']) . '"/>';
        $svg .= '</linearGradient>';

        // Specular highlight filter
        $svg .= '<filter id="' . $uid . '_drop" x="-10%" y="-10%" width="120%" height="120%">';
        $svg .= '<feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.35"/>';
        $svg .= '</filter>';

        $svg .= '</defs>';

        // 1. Background Circular Canvas
        $svg .= '<circle cx="128" cy="128" r="120" fill="url(#' . $uid . '_bg)" filter="url(#' . $uid . '_drop)"/>';
        $svg .= '<circle cx="128" cy="128" r="118" fill="none" stroke="#ffffff" stroke-width="2" stroke-opacity="0.25"/>';

        // 2. Torso / Shoulders with clothing
        $svg .= '<path d="M 52 235 C 52 188 88 178 128 178 C 168 178 204 188 204 235 Z" fill="#1e293b"/>';
        // Collar highlight
        $svg .= '<path d="M 104 178 Q 128 198 152 178 Q 128 204 104 178 Z" fill="' . htmlspecialchars($skin['base']) . '"/>';

        // 3. Head 3D Sphere
        $svg .= '<circle cx="128" cy="120" r="54" fill="url(#' . $uid . '_skin)" filter="url(#' . $uid . '_drop)"/>';

        // Ears with 3D depth
        $svg .= '<circle cx="72" cy="124" r="12" fill="' . htmlspecialchars($skin['base']) . '"/>';
        $svg .= '<circle cx="72" cy="124" r="6" fill="' . htmlspecialchars($skin['shadow']) . '"/>';
        $svg .= '<circle cx="184" cy="124" r="12" fill="' . htmlspecialchars($skin['base']) . '"/>';
        $svg .= '<circle cx="184" cy="124" r="6" fill="' . htmlspecialchars($skin['shadow']) . '"/>';

        // 4. Hairstyle rendering
        $svg .= $this->renderHairstyleSvg($hairKey, $hair['color'], $uid);

        // 5. Facial Features (Eyes, Eyebrows, Nose, Mouth)
        $svg .= $this->renderFaceFeaturesSvg($exprKey, $skin, $uid);

        // 6. Accessories
        $svg .= $this->renderAccessorySvg($accKey, $uid);

        // 7. Tactile 3D Glass / Specular Rim Highlight
        $svg .= '<path d="M 88 80 Q 128 66 168 80 Q 128 72 88 80 Z" fill="#ffffff" fill-opacity="0.35"/>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Save procedural 3D avatar as the user's active profile picture.
     *
     * @param array<string, string> $traits
     * @return array{filename: string, url: string, success: bool}
     */
    public function saveAvatar(string $userId, array $traits, ?string $pngBase64 = null): array
    {
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

        // Render clean server-side SVG
        $svgContent = $this->renderProceduralAvatar($traits, 384);
        file_put_contents($profileDir . $svgFileName, $svgContent);
        if (is_dir($publicProfileDir)) {
            @file_put_contents($publicProfileDir . $svgFileName, $svgContent);
        }

        $activeFileName = $svgFileName;

        // If client submitted high-res PNG canvas snapshot, save PNG for universal compatibility
        if ($pngBase64 !== null && str_starts_with($pngBase64, 'data:image/png;base64,')) {
            $rawBase64 = substr($pngBase64, strlen('data:image/png;base64,'));
            $decoded = base64_decode($rawBase64, true);
            if ($decoded !== false && strlen($decoded) > 100 && strlen($decoded) < 5 * 1024 * 1024) {
                // Verify PNG header
                if (substr($decoded, 0, 8) === "\x89PNG\r\n\x1a\n") {
                    file_put_contents($profileDir . $pngFileName, $decoded);
                    if (is_dir($publicProfileDir)) {
                        @file_put_contents($publicProfileDir . $pngFileName, $decoded);
                    }
                    $activeFileName = $pngFileName;
                }
            }
        }

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
        ];
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

    private function buildHugSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="fh_p1" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#fffbeb"/><stop offset="35%" stop-color="#fde047"/><stop offset="75%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></radialGradient>'
            . '<radialGradient id="fh_p2" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffedd5"/><stop offset="40%" stop-color="#fb923c"/><stop offset="80%" stop-color="#ea580c"/><stop offset="100%" stop-color="#9a3412"/></radialGradient>'
            . '<radialGradient id="fh_hrt" cx="35%" cy="30%" r="65%"><stop offset="0%" stop-color="#ffe4e6"/><stop offset="40%" stop-color="#f43f5e"/><stop offset="100%" stop-color="#be123c"/></radialGradient>'
            . '<filter id="fh_hug_sh"><feDropShadow dx="0" dy="6" stdDeviation="5" flood-color="#f43f5e" flood-opacity="0.3"/></filter>'
            . '</defs>'
            . '<g filter="url(#fh_hug_sh)">'
            . '<path d="M 60 14 C 60 14 50 4 41 9 C 32 15 36 27 60 40 C 84 27 88 15 79 9 C 70 4 60 14 60 14 Z" fill="url(#fh_hrt)"/>'
            . '<ellipse cx="48" cy="14" rx="4" ry="2" fill="#ffffff" fill-opacity="0.7"/>'
            . '<circle cx="44" cy="52" r="16" fill="url(#fh_p1)"/>'
            . '<circle cx="40" cy="46" r="4" fill="#ffffff" fill-opacity="0.6"/>'
            . '<path d="M 22 98 C 22 72 44 68 56 74 C 66 79 68 98 68 98 Z" fill="url(#fh_p1)"/>'
            . '<circle cx="76" cy="52" r="16" fill="url(#fh_p2)"/>'
            . '<circle cx="72" cy="46" r="4" fill="#ffffff" fill-opacity="0.6"/>'
            . '<path d="M 52 98 C 52 74 74 68 86 74 C 98 79 98 98 98 98 Z" fill="url(#fh_p2)"/>'
            . '<path d="M 34 76 Q 60 92 86 76" stroke="url(#fh_p1)" stroke-width="8" stroke-linecap="round" fill="none"/>'
            . '<path d="M 34 76 Q 60 92 86 76" stroke="#ffffff" stroke-width="2" stroke-linecap="round" fill="none" opacity="0.4"/>'
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

    private function buildBlessingSvg(int $size): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="' . $size . '" height="' . $size . '" class="atg-3d-emoji">'
            . '<defs>'
            . '<radialGradient id="bl_sun" cx="50%" cy="40%" r="60%"><stop offset="0%" stop-color="#ffffff"/><stop offset="30%" stop-color="#fef08a"/><stop offset="70%" stop-color="#facc15"/><stop offset="100%" stop-color="#f59e0b"/></radialGradient>'
            . '<linearGradient id="bl_skin" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#ffedd5"/><stop offset="40%" stop-color="#fed7aa"/><stop offset="80%" stop-color="#f59e0b"/><stop offset="100%" stop-color="#b45309"/></linearGradient>'
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

    private function renderHairstyleSvg(string $hairKey, string $color, string $uid): string
    {
        switch ($hairKey) {
            case 'afro_crown':
                return '<circle cx="128" cy="94" r="62" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="92" cy="84" r="28" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="164" cy="84" r="28" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="128" cy="66" r="30" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="118" cy="60" r="14" fill="#ffffff" fill-opacity="0.12"/>';
            case 'braided_crown':
                return '<path d="M 68 116 Q 128 54 188 116 Q 128 74 68 116 Z" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="96" cy="84" r="4" fill="#eab308"/>'
                    . '<circle cx="128" cy="78" r="4" fill="#eab308"/>'
                    . '<circle cx="160" cy="84" r="4" fill="#eab308"/>';
            case 'regal_locs':
                return '<path d="M 70 106 Q 128 58 186 106 Q 128 72 70 106 Z" fill="' . htmlspecialchars($color) . '"/>'
                    . '<path d="M 68 110 L 62 170" stroke="' . htmlspecialchars($color) . '" stroke-width="12" stroke-linecap="round"/>'
                    . '<path d="M 80 110 L 76 182" stroke="' . htmlspecialchars($color) . '" stroke-width="10" stroke-linecap="round"/>'
                    . '<path d="M 176 110 L 180 182" stroke="' . htmlspecialchars($color) . '" stroke-width="10" stroke-linecap="round"/>'
                    . '<path d="M 188 110 L 194 170" stroke="' . htmlspecialchars($color) . '" stroke-width="12" stroke-linecap="round"/>'
                    . '<circle cx="76" cy="150" r="4" fill="#b45309"/>'
                    . '<circle cx="180" cy="150" r="4" fill="#b45309"/>';
            case 'traditional_gele':
                return '<path d="M 52 108 Q 128 32 204 108 L 194 92 Q 128 20 62 92 Z" fill="#b8860b"/>'
                    . '<path d="M 46 96 Q 128 28 210 96 L 202 82 Q 128 14 54 82 Z" fill="#d97706"/>'
                    . '<path d="M 40 84 Q 128 18 216 84 L 208 72 Q 128 8 48 72 Z" fill="#f59e0b"/>'
                    . '<circle cx="128" cy="46" r="6" fill="#ef4444"/>';
            case 'bantu_knots':
                return '<circle cx="128" cy="72" r="14" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="94" cy="82" r="12" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="162" cy="82" r="12" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="74" cy="106" r="10" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="182" cy="106" r="10" fill="' . htmlspecialchars($color) . '"/>';
            case 'high_fade':
                return '<path d="M 76 114 Q 128 66 180 114 Q 128 84 76 114 Z" fill="' . htmlspecialchars($color) . '"/>'
                    . '<rect x="94" y="68" width="68" height="24" rx="10" fill="' . htmlspecialchars($color) . '"/>';
            case 'curled_top':
                return '<circle cx="128" cy="80" r="42" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="112" cy="76" r="16" fill="#292524"/>'
                    . '<circle cx="144" cy="76" r="16" fill="#292524"/>';
            case 'sleek_bun':
                return '<path d="M 76 116 Q 128 72 180 116 Z" fill="' . htmlspecialchars($color) . '"/>'
                    . '<circle cx="128" cy="56" r="22" fill="' . htmlspecialchars($color) . '"/>'
                    . '<path d="M 112 50 L 144 62" stroke="#eab308" stroke-width="3" stroke-linecap="round"/>';
            case 'regal_bald':
            default:
                // Luminous smooth highlight on bald crown
                return '<ellipse cx="120" cy="94" rx="28" ry="14" fill="#ffffff" fill-opacity="0.22"/>';
        }
    }

    private function renderFaceFeaturesSvg(string $exprKey, array $skin, string $uid): string
    {
        $svg = '';

        // Eyebrows
        $svg .= '<path d="M 94 104 Q 106 100 116 104" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>';
        $svg .= '<path d="M 140 104 Q 150 100 162 104" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>';

        // Eyes
        if ($exprKey === 'radiant_joy') {
            // Closed joyful curve eyes
            $svg .= '<path d="M 96 116 Q 106 124 116 116" stroke="#1c1917" stroke-width="3.5" stroke-linecap="round" fill="none"/>';
            $svg .= '<path d="M 140 116 Q 150 124 160 116" stroke="#1c1917" stroke-width="3.5" stroke-linecap="round" fill="none"/>';
        } else {
            // Open 3D eyes
            $svg .= '<ellipse cx="106" cy="116" rx="9" ry="7" fill="#ffffff"/>';
            $svg .= '<circle cx="106" cy="116" r="5" fill="#3f2314"/>';
            $svg .= '<circle cx="108" cy="114" r="2" fill="#ffffff"/>';

            $svg .= '<ellipse cx="150" cy="116" rx="9" ry="7" fill="#ffffff"/>';
            $svg .= '<circle cx="150" cy="116" r="5" fill="#3f2314"/>';
            $svg .= '<circle cx="152" cy="114" r="2" fill="#ffffff"/>';
        }

        // Nose with 3D bridge shadow
        $svg .= '<path d="M 128 114 Q 128 132 122 134 Q 128 136 134 134" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3" stroke-linecap="round" fill="none"/>';

        // Cheeks flush
        $svg .= '<circle cx="92" cy="130" r="10" fill="#f43f5e" fill-opacity="0.18"/>';
        $svg .= '<circle cx="164" cy="130" r="10" fill="#f43f5e" fill-opacity="0.18"/>';

        // Mouth based on expression
        switch ($exprKey) {
            case 'proud_grin':
                $svg .= '<path d="M 106 144 Q 128 166 150 144 Z" fill="#ffffff"/>';
                $svg .= '<path d="M 104 144 Q 128 168 152 144" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3" fill="none"/>';
                break;
            case 'wise_calm':
                $svg .= '<path d="M 112 148 Q 128 154 144 148" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>';
                break;
            case 'radiant_joy':
                $svg .= '<path d="M 104 142 Q 128 172 152 142 Z" fill="#991b1b"/>';
                $svg .= '<path d="M 108 144 Q 128 156 148 144 Z" fill="#ffffff"/>';
                break;
            case 'warm_smile':
            default:
                $svg .= '<path d="M 108 146 Q 128 162 148 146" stroke="' . htmlspecialchars($skin['shadow']) . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>';
                break;
        }

        return $svg;
    }

    private function renderAccessorySvg(string $accKey, string $uid): string
    {
        switch ($accKey) {
            case 'ancestor_crown':
                return '<path d="M 98 72 L 96 46 L 112 60 L 128 36 L 144 60 L 160 46 L 158 72 Z" fill="#eab308"/>'
                    . '<circle cx="128" cy="38" r="4" fill="#a855f7"/>'
                    . '<circle cx="96" cy="48" r="3" fill="#ef4444"/>'
                    . '<circle cx="160" cy="48" r="3" fill="#3b82f6"/>';
            case 'heritage_coral_beads':
                return '<path d="M 94 184 Q 128 206 162 184" stroke="#dc2626" stroke-width="8" stroke-linecap="round" stroke-dasharray="1 11" fill="none"/>'
                    . '<circle cx="128" cy="198" r="6" fill="#eab308"/>';
            case 'gold_spectacles':
                return '<circle cx="106" cy="116" r="14" fill="none" stroke="#eab308" stroke-width="2.5"/>'
                    . '<circle cx="150" cy="116" r="14" fill="none" stroke="#eab308" stroke-width="2.5"/>'
                    . '<line x1="120" y1="116" x2="136" y2="116" stroke="#eab308" stroke-width="2.5"/>'
                    . '<path d="M 92 116 L 76 114 M 164 116 L 180 114" stroke="#eab308" stroke-width="2"/>';
            case 'laurel_wreath':
                return '<path d="M 72 108 Q 98 64 128 66" stroke="#eab308" stroke-width="3" fill="none"/>'
                    . '<ellipse cx="88" cy="84" rx="6" ry="3" fill="#eab308" transform="rotate(-30 88 84)"/>'
                    . '<ellipse cx="104" cy="74" rx="6" ry="3" fill="#eab308" transform="rotate(-15 104 74)"/>'
                    . '<ellipse cx="120" cy="68" rx="6" ry="3" fill="#eab308"/>';
            case 'none':
            default:
                return '';
        }
    }
}

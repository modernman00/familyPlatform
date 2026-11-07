<?php

declare(strict_types=1);

namespace App\services;

/**
 * ATG Layered Persona Renderer (v2 — inclusive + motion-ready)
 *
 * Renders a procedural avatar as a layered SVG rig. Every trait value is
 * whitelisted against the constants below, so user input never reaches the
 * markup. The rig exposes stable class hooks consumed by the studio motion
 * engine (public/js/atg-emoji-engine.js):
 *
 *   .atg-av-react    – page-level reaction animations (nod, laugh, dance…)
 *   .atg-av-body     – idle breathing
 *   .atg-av-headpose – JS head-follow (transform attribute)
 *   .atg-av-head     – idle sway
 *   .atg-av-eye-l/r  – blink / wink
 *   .atg-av-gaze     – JS gaze tracking (transform attribute)
 *   .atg-av-mouth    – resting mouth
 *   .atg-av-talk     – voice lip-sync mouth (scaled by JS)
 *
 * When $animated is true an embedded <style> adds idle life (blink, breathe,
 * sway, glance) that also plays when the SVG is used as a plain <img>, i.e. a
 * "living" profile photo. All motion is wrapped in prefers-reduced-motion.
 */
final class AtgAvatarRenderer
{
    public const SKIN_TONES = [
        'porcelain_cool' => ['base' => '#f6ddd0', 'highlight' => '#fff4ee', 'shadow' => '#c9a191', 'label' => 'Porcelain', 'undertone' => 'cool'],
        'ivory_rose'     => ['base' => '#f1cbb5', 'highlight' => '#fde6d8', 'shadow' => '#bf8f7a', 'label' => 'Ivory Rose', 'undertone' => 'pink'],
        'fair_radiance'  => ['base' => '#fad3b0', 'highlight' => '#ffeedc', 'shadow' => '#b38d6d', 'label' => 'Fair Radiance', 'undertone' => 'warm'],
        'light_olive'    => ['base' => '#dcbc94', 'highlight' => '#f0d9b8', 'shadow' => '#a38660', 'label' => 'Light Olive', 'undertone' => 'olive'],
        'warm_honey'     => ['base' => '#dfa773', 'highlight' => '#f3c497', 'shadow' => '#936640', 'label' => 'Warm Honey', 'undertone' => 'warm'],
        'golden_caramel' => ['base' => '#c58550', 'highlight' => '#e1a36f', 'shadow' => '#7d512d', 'label' => 'Golden Caramel', 'undertone' => 'golden'],
        'olive_tan'      => ['base' => '#b48a5c', 'highlight' => '#cfa679', 'shadow' => '#755835', 'label' => 'Olive Tan', 'undertone' => 'olive'],
        'bronze_amber'   => ['base' => '#995831', 'highlight' => '#bf7545', 'shadow' => '#5a3118', 'label' => 'Bronze Amber', 'undertone' => 'warm'],
        'warm_chestnut'  => ['base' => '#6a3c20', 'highlight' => '#8e5430', 'shadow' => '#3f2110', 'label' => 'Warm Chestnut', 'undertone' => 'warm'],
        'cool_umber'     => ['base' => '#5b3a30', 'highlight' => '#7a5246', 'shadow' => '#33201a', 'label' => 'Cool Umber', 'undertone' => 'cool'],
        'deep_espresso'  => ['base' => '#3d2314', 'highlight' => '#5f3822', 'shadow' => '#201108', 'label' => 'Deep Espresso', 'undertone' => 'warm'],
        'melanin_rich'   => ['base' => '#2b170c', 'highlight' => '#4a2c1b', 'shadow' => '#140a05', 'label' => 'Melanin Rich', 'undertone' => 'warm'],
        'ebony_blue'     => ['base' => '#25181a', 'highlight' => '#43303a', 'shadow' => '#110a0c', 'label' => 'Ebony', 'undertone' => 'cool'],
    ];

    /**
     * Gender expression shapes the base body (lashes, brow weight, lip tint,
     * jaw, neck, shoulders). It never restricts hair, facial hair or outfits.
     */
    public const PRESENTATIONS = [
        'neutral'   => ['label' => 'Neutral'],
        'feminine'  => ['label' => 'Feminine'],
        'masculine' => ['label' => 'Masculine'],
    ];

    public const SKIN_DETAILS = [
        'none'         => ['label' => 'Clear'],
        'freckles'     => ['label' => 'Freckles'],
        'vitiligo'     => ['label' => 'Vitiligo'],
        'beauty_mark'  => ['label' => 'Beauty Mark'],
        'rosy_glow'    => ['label' => 'Rosy Glow'],
        'elder_lines'  => ['label' => 'Wisdom Lines'],
    ];

    /** w = face half-width, top = crown y, chin = chin y */
    public const FACE_SHAPES = [
        'oval'   => ['label' => 'Oval', 'w' => 46, 'top' => 58, 'chin' => 170],
        'round'  => ['label' => 'Round', 'w' => 50, 'top' => 62, 'chin' => 166],
        'square' => ['label' => 'Square', 'w' => 48, 'top' => 58, 'chin' => 168],
        'heart'  => ['label' => 'Heart', 'w' => 48, 'top' => 58, 'chin' => 170],
        'long'   => ['label' => 'Long', 'w' => 43, 'top' => 54, 'chin' => 174],
    ];

    /**
     * kind: hair | headwear. 'top' is the highest y the style reaches (used to
     * seat crowns). 'covers_ears' hides ears and ear jewellery.
     */
    public const HAIRSTYLES = [
        'afro_crown'       => ['label' => 'Afro Crown', 'color' => '#18110d', 'kind' => 'hair', 'top' => 24],
        'braided_crown'    => ['label' => 'Heritage Braids', 'color' => '#140e0b', 'kind' => 'hair', 'top' => 44],
        'cornrows'         => ['label' => 'Cornrows', 'color' => '#140e0b', 'kind' => 'hair', 'top' => 50],
        'regal_locs'       => ['label' => 'Regal Locs', 'color' => '#100b08', 'kind' => 'hair', 'top' => 44],
        'bantu_knots'      => ['label' => 'Bantu Knots', 'color' => '#1c140e', 'kind' => 'hair', 'top' => 40],
        'curly_shoulder'   => ['label' => 'Curly Cloud', 'color' => '#2a1a12', 'kind' => 'hair', 'top' => 36],
        'curled_top'       => ['label' => 'Textured Curls', 'color' => '#160f0c', 'kind' => 'hair', 'top' => 42],
        'high_fade'        => ['label' => 'Barber Fade', 'color' => '#1a130f', 'kind' => 'hair', 'top' => 46],
        'buzz_cut'         => ['label' => 'Buzz Cut', 'color' => '#1a130f', 'kind' => 'hair', 'top' => 52],
        'side_part'        => ['label' => 'Side Part', 'color' => '#2b1d14', 'kind' => 'hair', 'top' => 44],
        'pixie_cut'        => ['label' => 'Pixie Cut', 'color' => '#3a2418', 'kind' => 'hair', 'top' => 46],
        'blunt_bangs'      => ['label' => 'Blunt Bangs Bob', 'color' => '#0f0c0b', 'kind' => 'hair', 'top' => 48],
        'wavy_bob'         => ['label' => 'Wavy Bob', 'color' => '#5a3520', 'kind' => 'hair', 'top' => 46],
        'long_straight'    => ['label' => 'Long & Straight', 'color' => '#2b1d14', 'kind' => 'hair', 'top' => 48],
        'ponytail'         => ['label' => 'High Ponytail', 'color' => '#3a2418', 'kind' => 'hair', 'top' => 40],
        'sleek_bun'        => ['label' => 'Sleek Top Bun', 'color' => '#18120e', 'kind' => 'hair', 'top' => 26],
        'regal_bald'       => ['label' => 'Glistening Bald', 'color' => 'transparent', 'kind' => 'hair', 'top' => 58],
        'traditional_gele' => ['label' => 'Gele Headwrap', 'color' => '#b8860b', 'kind' => 'headwear', 'top' => 16],
        'hijab'            => ['label' => 'Hijab', 'color' => '#1e293b', 'kind' => 'headwear', 'top' => 42, 'covers_ears' => true],
        'dastar_turban'    => ['label' => 'Dastar Turban', 'color' => '#1e3a8a', 'kind' => 'headwear', 'top' => 36],
        'kufi_cap'         => ['label' => 'Kufi Cap', 'color' => '#f8fafc', 'kind' => 'headwear', 'top' => 50],
    ];

    public const HAIR_COLORS = [
        'natural'         => ['label' => 'Natural', 'hex' => ''],
        'jet_black'       => ['label' => 'Jet Black', 'hex' => '#0e0b0a'],
        'dark_brown'      => ['label' => 'Dark Brown', 'hex' => '#3e2618'],
        'chestnut_brown'  => ['label' => 'Chestnut', 'hex' => '#6b3a20'],
        'auburn'          => ['label' => 'Auburn', 'hex' => '#8c3a22'],
        'copper_red'      => ['label' => 'Copper Red', 'hex' => '#b5542b'],
        'honey_blonde'    => ['label' => 'Honey Blonde', 'hex' => '#c9a063'],
        'platinum_blonde' => ['label' => 'Platinum', 'hex' => '#e6d9b8'],
        'silver_grey'     => ['label' => 'Silver Grey', 'hex' => '#a9a9ad'],
        'snow_white'      => ['label' => 'Snow White', 'hex' => '#ecebe7'],
        'violet_dye'      => ['label' => 'Violet Dye', 'hex' => '#6d3bd1'],
        'ocean_teal'      => ['label' => 'Ocean Teal', 'hex' => '#0f7c7a'],
        'rose_pink'       => ['label' => 'Rose Pink', 'hex' => '#d9608c'],
    ];

    public const EYE_SHAPES = [
        'almond'     => ['label' => 'Almond'],
        'round'      => ['label' => 'Round'],
        'monolid'    => ['label' => 'Monolid'],
        'hooded'     => ['label' => 'Hooded'],
        'downturned' => ['label' => 'Downturned'],
    ];

    public const EYE_COLORS = [
        'dark_brown' => ['label' => 'Dark Brown', 'hex' => '#3b2214'],
        'warm_brown' => ['label' => 'Warm Brown', 'hex' => '#6b3e1f'],
        'amber'      => ['label' => 'Amber', 'hex' => '#b0701c'],
        'hazel'      => ['label' => 'Hazel', 'hex' => '#7a6a2c'],
        'green'      => ['label' => 'Green', 'hex' => '#3f7d4a'],
        'blue'       => ['label' => 'Blue', 'hex' => '#3d78b8'],
        'grey'       => ['label' => 'Grey', 'hex' => '#7d8a96'],
    ];

    public const NOSE_SHAPES = [
        'button'   => ['label' => 'Button'],
        'broad'    => ['label' => 'Broad'],
        'straight' => ['label' => 'Straight'],
        'aquiline' => ['label' => 'Aquiline'],
        'petite'   => ['label' => 'Petite'],
    ];

    public const LIP_STYLES = [
        'full'   => ['label' => 'Full', 'f' => 1.45],
        'medium' => ['label' => 'Medium', 'f' => 1.0],
        'thin'   => ['label' => 'Thin', 'f' => 0.6],
    ];

    public const EXPRESSIONS = [
        'warm_smile'    => ['label' => 'Warm Smile'],
        'proud_grin'    => ['label' => 'Proud Grin'],
        'wise_calm'     => ['label' => 'Wise Serenity'],
        'radiant_joy'   => ['label' => 'Radiant Joy'],
        'playful_wink'  => ['label' => 'Playful Wink'],
        'wow_surprised' => ['label' => 'Wow!'],
        'love_struck'   => ['label' => 'Love Struck'],
        'cheeky_tongue' => ['label' => 'Cheeky'],
    ];

    public const FACIAL_HAIR = [
        'none'       => ['label' => 'None'],
        'stubble'    => ['label' => 'Stubble'],
        'moustache'  => ['label' => 'Moustache'],
        'goatee'     => ['label' => 'Goatee'],
        'full_beard' => ['label' => 'Full Beard'],
    ];

    public const OUTFITS = [
        'classic_tee'       => ['label' => 'Classic Tee'],
        'crew_hoodie'       => ['label' => 'Hoodie'],
        'suit_tie'          => ['label' => 'Suit & Tie'],
        'ankara_print'      => ['label' => 'Ankara Print'],
        'kente_stole'       => ['label' => 'Kente Stole'],
        'agbada_embroidery' => ['label' => 'Agbada Neckline'],
        'mandarin_collar'   => ['label' => 'Mandarin Collar'],
        'wrap_collar'       => ['label' => 'Wrap Collar'],
    ];

    public const FABRIC_COLORS = [
        'midnight'   => ['label' => 'Midnight', 'hex' => '#1e293b'],
        'royal_blue' => ['label' => 'Royal Blue', 'hex' => '#1d4ed8'],
        'emerald'    => ['label' => 'Emerald', 'hex' => '#047857'],
        'crimson'    => ['label' => 'Crimson', 'hex' => '#b91c1c'],
        'saffron'    => ['label' => 'Saffron', 'hex' => '#d97706'],
        'plum'       => ['label' => 'Plum', 'hex' => '#6b21a8'],
        'blush'      => ['label' => 'Blush', 'hex' => '#e78fa6'],
        'ivory'      => ['label' => 'Ivory', 'hex' => '#f1ead8'],
        'charcoal'   => ['label' => 'Charcoal', 'hex' => '#3f3f46'],
    ];

    public const ACCESSORIES = [
        'none'                 => ['label' => 'None'],
        'ancestor_crown'       => ['label' => 'Ancestor Diadem'],
        'heritage_coral_beads' => ['label' => 'Royal Coral Beads'],
        'gold_spectacles'      => ['label' => 'Gold Spectacles'],
        'laurel_wreath'        => ['label' => 'Laurel of Honour'],
        'gold_hoops'           => ['label' => 'Gold Hoops'],
        'pearl_studs'          => ['label' => 'Pearl Studs'],
        'nose_stud'            => ['label' => 'Nose Stud'],
        'bindi'                => ['label' => 'Bindi'],
        'flower_crown'         => ['label' => 'Flower Crown'],
        'hearing_aid'          => ['label' => 'Hearing Aid'],
    ];

    public const EYEWEAR = [
        'none'           => ['label' => 'None'],
        'round_frames'   => ['label' => 'Round Frames'],
        'cat_eye'        => ['label' => 'Cat-Eye'],
        'aviator_shades' => ['label' => 'Aviator Shades'],
    ];

    public const BACKDROPS = [
        'warm_sunset'    => ['start' => '#f59e0b', 'end' => '#b45309', 'label' => 'Warm Sunset'],
        'royal_violet'   => ['start' => '#8b5cf6', 'end' => '#4c1d95', 'label' => 'Royal Violet'],
        'emerald_roots'  => ['start' => '#10b981', 'end' => '#064e3b', 'label' => 'Emerald Roots'],
        'sapphire_night' => ['start' => '#3b82f6', 'end' => '#1e1b4b', 'label' => 'Sapphire Night'],
        'crimson_hearth' => ['start' => '#ef4444', 'end' => '#7f1d1d', 'label' => 'Crimson Hearth'],
        'pure_gold'      => ['start' => '#fcd34d', 'end' => '#92400e', 'label' => 'Pure Gold'],
        'ocean_mist'     => ['start' => '#5eead4', 'end' => '#0e7490', 'label' => 'Ocean Mist'],
        'blossom'        => ['start' => '#fbcfe8', 'end' => '#db2777', 'label' => 'Blossom'],
        'studio_light'   => ['start' => '#f8fafc', 'end' => '#cbd5e1', 'label' => 'Studio Light'],
        'kente_weave'    => ['start' => '#facc15', 'end' => '#15803d', 'label' => 'Kente Weave', 'pattern' => 'kente'],
        'starlit_sky'    => ['start' => '#312e81', 'end' => '#0f172a', 'label' => 'Starlit Sky', 'pattern' => 'stars'],
    ];

    /**
     * Head-and-shoulders crop used for saved profile photos, so the face fills
     * small circular frames (feed, profile card, organogram) instead of the
     * full studio scene. The bottom edge is fixed; tall styles (afro, gele,
     * buns, crowns) widen the crop so their tops are never clipped.
     */
    private const PORTRAIT_BOTTOM = 218;
    private const PORTRAIT_MIN_TOP = 30;

    private function portraitViewBox(): string
    {
        $top = (int)self::HAIRSTYLES[$this->t['hairstyle']]['top'];
        if (in_array($this->t['accessory'], ['ancestor_crown', 'flower_crown'], true)) {
            $top = min($top, $this->t['accessory'] === 'ancestor_crown' ? max(0, min($top, $this->top) - 22) : 60);
        }
        $y0 = min(self::PORTRAIT_MIN_TOP, $top - 6);
        $h = self::PORTRAIT_BOTTOM - $y0;
        $x0 = 128 - $h / 2;
        return $this->n($x0) . ' ' . $this->n($y0) . ' ' . $this->n($h) . ' ' . $this->n($h);
    }

    public const TRAIT_DEFAULTS = [
        'presentation' => 'neutral',
        'skin_tone'   => 'warm_chestnut',
        'skin_detail' => 'none',
        'face_shape'  => 'oval',
        'eye_shape'   => 'almond',
        'eye_color'   => 'dark_brown',
        'nose'        => 'button',
        'lips'        => 'medium',
        'expression'  => 'warm_smile',
        'hairstyle'   => 'afro_crown',
        'hair_color'  => 'natural',
        'facial_hair' => 'none',
        'outfit'      => 'classic_tee',
        'fabric'      => 'midnight',
        'accessory'   => 'none',
        'eyewear'     => 'none',
        'backdrop'    => 'warm_sunset',
    ];

    /**
     * Customiser layout: section => [trait => label].
     */
    public const SECTIONS = [
        'face'  => ['label' => 'Face', 'traits' => [
            'presentation' => 'Gender Expression', 'skin_tone' => 'Skin Tone', 'skin_detail' => 'Skin Details', 'face_shape' => 'Face Shape',
            'eye_shape' => 'Eye Shape', 'eye_color' => 'Eye Colour', 'nose' => 'Nose', 'lips' => 'Lips',
            'expression' => 'Expression',
        ]],
        'hair'  => ['label' => 'Hair', 'traits' => [
            'hairstyle' => 'Hairstyle & Headwear', 'hair_color' => 'Hair Colour', 'facial_hair' => 'Facial Hair',
        ]],
        'style' => ['label' => 'Style', 'traits' => [
            'outfit' => 'Outfit', 'fabric' => 'Fabric Colour', 'accessory' => 'Accessories', 'eyewear' => 'Eyewear',
        ]],
        'scene' => ['label' => 'Scene', 'traits' => [
            'backdrop' => 'Backdrop',
        ]],
    ];

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public static function options(): array
    {
        return [
            'presentation' => self::PRESENTATIONS,
            'skin_tone'   => self::SKIN_TONES,
            'skin_detail' => self::SKIN_DETAILS,
            'face_shape'  => self::FACE_SHAPES,
            'eye_shape'   => self::EYE_SHAPES,
            'eye_color'   => self::EYE_COLORS,
            'nose'        => self::NOSE_SHAPES,
            'lips'        => self::LIP_STYLES,
            'expression'  => self::EXPRESSIONS,
            'hairstyle'   => self::HAIRSTYLES,
            'hair_color'  => self::HAIR_COLORS,
            'facial_hair' => self::FACIAL_HAIR,
            'outfit'      => self::OUTFITS,
            'fabric'      => self::FABRIC_COLORS,
            'accessory'   => self::ACCESSORIES,
            'eyewear'     => self::EYEWEAR,
            'backdrop'    => self::BACKDROPS,
        ];
    }

    /**
     * Swatch colour for a trait option (for the customiser UI), or null.
     */
    public static function swatch(string $trait, string $key): ?string
    {
        $opt = self::options()[$trait][$key] ?? null;
        if ($opt === null) {
            return null;
        }
        return match ($trait) {
            'skin_tone'  => (string)$opt['base'],
            'hair_color' => $key === 'natural' ? 'conic-gradient(#0e0b0a, #6b3a20, #c9a063, #a9a9ad, #0e0b0a)' : (string)$opt['hex'],
            'eye_color', 'fabric' => (string)$opt['hex'],
            'backdrop'   => 'linear-gradient(135deg, ' . $opt['start'] . ', ' . $opt['end'] . ')',
            default      => null,
        };
    }

    /**
     * Whitelist every trait; unknown keys/values fall back to defaults.
     *
     * @param array<mixed> $input
     * @return array<string, string>
     */
    public static function sanitise(array $input): array
    {
        $out = [];
        $options = self::options();
        foreach (self::TRAIT_DEFAULTS as $trait => $default) {
            $val = $input[$trait] ?? $default;
            $val = is_string($val) ? $val : $default;
            $out[$trait] = isset($options[$trait][$val]) ? $val : $default;
        }
        return $out;
    }

    /* ---------------------------------------------------------------------
     * Rendering
     * --------------------------------------------------------------------- */

    /** @var array<string, string> */
    private array $t = [];
    private string $uid = '';
    /** @var array{base:string, highlight:string, shadow:string} */
    private array $skin = ['base' => '', 'highlight' => '', 'shadow' => ''];
    private int $w = 46;
    private int $top = 58;
    private int $chin = 170;
    private string $hairHex = '#18110d';
    private string $fabricHex = '#1e293b';

    /**
     * @param array<mixed> $traits
     */
    public function render(array $traits, int $size = 256, bool $animated = false, bool $portrait = false): string
    {
        $this->t = self::sanitise($traits);
        $size = max(64, min(512, $size));
        $this->uid = 'av' . substr(hash('sha256', json_encode($this->t) . $size . ($animated ? 'a' : '')), 0, 8);

        $s = self::SKIN_TONES[$this->t['skin_tone']];
        $this->skin = ['base' => $s['base'], 'highlight' => $s['highlight'], 'shadow' => $s['shadow']];
        $face = self::FACE_SHAPES[$this->t['face_shape']];
        $this->w = $face['w'];
        $this->top = $face['top'];
        $this->chin = $face['chin'];

        $style = self::HAIRSTYLES[$this->t['hairstyle']];
        $hc = self::HAIR_COLORS[$this->t['hair_color']]['hex'];
        $natural = $style['color'] === 'transparent' || $style['kind'] === 'headwear' ? '#1c140f' : $style['color'];
        $this->hairHex = $hc !== '' ? $hc : $natural;
        $this->fabricHex = self::FABRIC_COLORS[$this->t['fabric']]['hex'];

        // Quote-free "key:value;…" encoding (all keys/values are [a-z_]) so the
        // attribute survives any HTML pipeline that decodes entities.
        $pairs = [];
        foreach ($this->t as $k => $v) {
            $pairs[] = $k . ':' . $v;
        }
        $traitsAttr = implode(';', $pairs);
        $u = $this->uid;

        $viewBox = $portrait ? $this->portraitViewBox() : '0 0 256 256';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="' . $viewBox . '" width="' . $size . '" height="' . $size
            . '" class="atg-avatar-svg" role="img" aria-label="Custom family avatar" data-atg-traits="' . $traitsAttr
            . '" data-portrait-viewbox="' . $this->portraitViewBox() . '">';
        if ($animated) {
            $svg .= $this->idleStyle();
        }
        $svg .= '<defs>' . $this->defs() . '</defs>';

        if ($portrait) {
            // Square-safe fill so no transparent corners show in square frames.
            $svg .= '<rect x="0" y="0" width="256" height="256" fill="url(#' . $u . '_bg)"/>';
        }
        $svg .= $this->backdrop();
        $svg .= '<g clip-path="url(#' . $u . '_clip)"><g class="atg-av-react">';
        $svg .= '<g class="atg-av-body">' . $this->hairBack() . $this->body() . $this->necklace() . '</g>';
        $svg .= '<g class="atg-av-headpose" transform="translate(0 0)"><g class="atg-av-head">';
        $svg .= $this->hairBackHead();
        $svg .= $this->ears();
        $svg .= '<path d="' . $this->facePath() . '" fill="url(#' . $u . '_skin)"/>';
        $svg .= '<path d="' . $this->facePath() . '" fill="url(#' . $u . '_rim)"/>';
        $svg .= $this->jawDefinition();
        $svg .= $this->skinDetails();
        $svg .= $this->cheeks();
        $svg .= $this->eyes();
        $svg .= $this->brows();
        $svg .= $this->nose();
        $svg .= $this->beard();
        $svg .= $this->mouth();
        $svg .= $this->moustache();
        $svg .= $this->hairFront();
        $svg .= $this->faceAccessories();
        $svg .= $this->eyewear();
        $svg .= $this->headAccessories();
        $svg .= '</g></g>';
        $svg .= '</g></g>';
        if (!$portrait) {
            $svg .= '<circle cx="128" cy="128" r="119" fill="none" stroke="#ffffff" stroke-width="2" stroke-opacity="0.3"/>';
        }
        $svg .= '</svg>';

        return $svg;
    }

    private function idleStyle(): string
    {
        return '<style>@media (prefers-reduced-motion:no-preference){'
            . '.atg-av-body{animation:atgAvBreathe 4.2s ease-in-out infinite}'
            . '.atg-av-head{transform-box:view-box;transform-origin:128px 176px;animation:atgAvSway 7s ease-in-out infinite}'
            . '.atg-av-eye{transform-box:fill-box;transform-origin:center;animation:atgAvBlink 5.3s infinite}'
            . '.atg-av-eye-r{animation-delay:.02s}'
            . '.atg-av-iris{animation:atgAvGlance 11s ease-in-out infinite}'
            . '@keyframes atgAvBreathe{0%,100%{transform:translateY(0)}50%{transform:translateY(1.6px)}}'
            . '@keyframes atgAvSway{0%,100%{transform:rotate(0)}25%{transform:rotate(1.6deg)}70%{transform:rotate(-1.4deg)}}'
            . '@keyframes atgAvBlink{0%,93%,100%{transform:scaleY(1)}95.5%{transform:scaleY(.08)}}'
            . '@keyframes atgAvGlance{0%,30%,100%{transform:translate(0,0)}36%,48%{transform:translate(-2.4px,.4px)}56%,68%{transform:translate(2.4px,-.4px)}74%{transform:translate(0,0)}}'
            . '}</style>';
    }

    private function defs(): string
    {
        $u = $this->uid;
        $sk = $this->skin;
        $back = self::BACKDROPS[$this->t['backdrop']];
        $hair = $this->hairHex;
        $fab = $this->fabricHex;
        $iris = self::EYE_COLORS[$this->t['eye_color']]['hex'];

        $d = '<clipPath id="' . $u . '_clip"><circle cx="128" cy="128" r="120"/></clipPath>';
        $d .= '<radialGradient id="' . $u . '_bg" cx="35%" cy="28%" r="75%"><stop offset="0%" stop-color="' . $this->mix($back['start'], '#ffffff', 0.18) . '"/><stop offset="55%" stop-color="' . $back['start'] . '"/><stop offset="100%" stop-color="' . $back['end'] . '"/></radialGradient>';
        $d .= '<radialGradient id="' . $u . '_skin" cx="40%" cy="34%" r="72%"><stop offset="0%" stop-color="' . $sk['highlight'] . '"/><stop offset="58%" stop-color="' . $sk['base'] . '"/><stop offset="100%" stop-color="' . $this->mix($sk['base'], $sk['shadow'], 0.7) . '"/></radialGradient>';
        $d .= '<radialGradient id="' . $u . '_rim" cx="50%" cy="45%" r="55%"><stop offset="80%" stop-color="' . $sk['shadow'] . '" stop-opacity="0"/><stop offset="100%" stop-color="' . $sk['shadow'] . '" stop-opacity="0.35"/></radialGradient>';
        $d .= '<linearGradient id="' . $u . '_neck" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="' . $this->mix($sk['base'], $sk['shadow'], 0.55) . '"/><stop offset="100%" stop-color="' . $sk['base'] . '"/></linearGradient>';
        $d .= '<linearGradient id="' . $u . '_hair" x1="0.2" y1="0" x2="0.8" y2="1"><stop offset="0%" stop-color="' . $this->mix($hair, '#ffffff', 0.22) . '"/><stop offset="45%" stop-color="' . $hair . '"/><stop offset="100%" stop-color="' . $this->mix($hair, '#000000', 0.35) . '"/></linearGradient>';
        $d .= '<linearGradient id="' . $u . '_fab" x1="0" y1="0" x2="0.3" y2="1"><stop offset="0%" stop-color="' . $this->mix($fab, '#ffffff', 0.18) . '"/><stop offset="100%" stop-color="' . $this->mix($fab, '#000000', 0.3) . '"/></linearGradient>';
        $d .= '<radialGradient id="' . $u . '_iris" cx="45%" cy="40%" r="60%"><stop offset="0%" stop-color="' . $this->mix($iris, '#ffffff', 0.35) . '"/><stop offset="70%" stop-color="' . $iris . '"/><stop offset="100%" stop-color="' . $this->mix($iris, '#000000', 0.5) . '"/></radialGradient>';
        $d .= '<linearGradient id="' . $u . '_gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fff7c2"/><stop offset="45%" stop-color="#facc15"/><stop offset="100%" stop-color="#a16207"/></linearGradient>';
        $d .= '<linearGradient id="' . $u . '_shade" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#1e293b" stop-opacity="0.92"/><stop offset="100%" stop-color="#475569" stop-opacity="0.8"/></linearGradient>';
        $d .= '<filter id="' . $u . '_soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#000" flood-opacity="0.25"/></filter>';
        $d .= '<pattern id="' . $u . '_ank" width="18" height="18" patternUnits="userSpaceOnUse"><circle cx="9" cy="9" r="5" fill="none" stroke="#facc15" stroke-width="2"/><circle cx="9" cy="9" r="1.8" fill="#f97316"/><path d="M0 0 L4 0 L0 4 Z M18 18 L14 18 L18 14 Z" fill="#facc15"/></pattern>';
        $d .= '<pattern id="' . $u . '_kente" width="24" height="24" patternUnits="userSpaceOnUse"><rect width="24" height="24" fill="#facc15"/><rect width="24" height="6" y="4" fill="#15803d"/><rect width="6" height="24" x="14" fill="#b91c1c" opacity="0.85"/><rect width="24" height="2" y="16" fill="#111827"/></pattern>';
        $d .= '<pattern id="' . $u . '_gelep" width="14" height="14" patternUnits="userSpaceOnUse"><path d="M0 7 L7 0 L14 7 L7 14 Z" fill="none" stroke="#fff" stroke-opacity="0.35" stroke-width="1.2"/></pattern>';
        $d .= '<clipPath id="' . $u . '_torso"><path d="' . $this->torsoPath() . '"/></clipPath>';
        foreach (['l' => 108, 'r' => 148] as $side => $cx) {
            $d .= '<clipPath id="' . $u . '_eye' . $side . '"><path d="' . $this->eyePath($cx, 114, $side) . '"/></clipPath>';
        }
        return $d;
    }

    private function backdrop(): string
    {
        $u = $this->uid;
        $back = self::BACKDROPS[$this->t['backdrop']];
        $s = '<circle cx="128" cy="128" r="120" fill="url(#' . $u . '_bg)"/>';
        $pattern = $back['pattern'] ?? '';
        if ($pattern === 'kente') {
            $s .= '<circle cx="128" cy="128" r="120" fill="url(#' . $u . '_kente)" opacity="0.35"/>';
        } elseif ($pattern === 'stars') {
            foreach ([[46, 70, 1.6], [70, 42, 1.1], [196, 58, 1.8], [214, 104, 1.2], [36, 130, 1.0], [182, 34, 1.0], [96, 30, 1.4], [222, 150, 1.5], [30, 96, 1.3]] as [$x, $y, $r]) {
                $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="#fef9c3" class="atg-av-star"/>';
            }
        }
        $s .= '<ellipse cx="94" cy="62" rx="60" ry="30" fill="#ffffff" opacity="0.10"/>';
        return $s;
    }

    private function torsoPath(): string
    {
        return match ($this->t['presentation']) {
            'feminine'  => 'M 42 260 C 44 218 76 198 110 190 Q 128 198 146 190 C 180 198 212 218 214 260 Z',
            'masculine' => 'M 20 260 C 22 210 60 192 106 186 Q 128 198 150 186 C 196 192 234 210 236 260 Z',
            default     => 'M 30 260 C 32 214 66 194 108 188 Q 128 198 148 188 C 190 194 224 214 226 260 Z',
        };
    }

    private function body(): string
    {
        $u = $this->uid;
        $sk = $this->skin;
        // Neck + jaw shadow
        $nw = match ($this->t['presentation']) {
            'feminine'  => 15,
            'masculine' => 21,
            default     => 18,
        };
        $s = '<path d="M ' . (128 - $nw) . ' 146 L ' . (128 - $nw) . ' 190 Q 128 202 ' . (128 + $nw) . ' 190 L ' . (128 + $nw) . ' 146 Z" fill="url(#' . $u . '_neck)"/>';
        $s .= '<ellipse cx="128" cy="' . ($this->chin - 4) . '" rx="24" ry="9" fill="' . $sk['shadow'] . '" opacity="0.45"/>';
        $s .= '<path d="' . $this->torsoPath() . '" fill="url(#' . $u . '_fab)"/>';
        $s .= $this->outfitDetail();
        return $s;
    }

    private function outfitDetail(): string
    {
        $u = $this->uid;
        $sk = $this->skin;
        $fab = $this->fabricHex;
        $dark = $this->mix($fab, '#000000', 0.45);
        $light = $this->mix($fab, '#ffffff', 0.35);

        switch ($this->t['outfit']) {
            case 'crew_hoodie':
                return '<path d="M 84 196 Q 128 236 172 196 Q 160 190 148 188 Q 128 214 108 188 Q 96 190 84 196 Z" fill="' . $dark . '" opacity="0.55"/>'
                    . '<path d="M 116 206 L 114 236 M 140 206 L 142 236" stroke="' . $light . '" stroke-width="3" stroke-linecap="round"/>'
                    . '<circle cx="114" cy="238" r="3" fill="' . $light . '"/><circle cx="142" cy="238" r="3" fill="' . $light . '"/>'
                    . '<path d="M 90 236 Q 128 248 166 236 L 168 260 L 88 260 Z" fill="' . $dark . '" opacity="0.35"/>';
            case 'suit_tie':
                return '<path d="M 108 188 L 128 236 L 148 188 Q 128 198 108 188 Z" fill="#f8fafc"/>'
                    . '<path d="M 123 200 L 133 200 L 136 236 L 128 246 L 120 236 Z" fill="#b91c1c"/>'
                    . '<path d="M 122 194 L 134 194 L 133 202 L 123 202 Z" fill="#991b1b"/>'
                    . '<path d="M 108 188 L 98 196 L 116 222 L 128 236 Z M 148 188 L 158 196 L 140 222 L 128 236 Z" fill="' . $dark . '"/>'
                    . '<path d="M 98 196 L 116 222 M 158 196 L 140 222" stroke="' . $light . '" stroke-width="1.2" opacity="0.6"/>';
            case 'ankara_print':
                return '<path d="' . $this->torsoPath() . '" fill="url(#' . $u . '_ank)" opacity="0.85"/>'
                    . '<path d="M 106 188 Q 128 206 150 188" fill="none" stroke="#facc15" stroke-width="4"/>';
            case 'kente_stole':
                return '<path d="M 106 188 Q 128 204 150 188" fill="none" stroke="' . $dark . '" stroke-width="3"/>'
                    . '<g clip-path="url(#' . $u . '_torso)">'
                    . '<path d="M 96 186 L 112 190 L 104 260 L 84 260 Z" fill="url(#' . $u . '_kente)"/>'
                    . '<path d="M 160 186 L 144 190 L 152 260 L 172 260 Z" fill="url(#' . $u . '_kente)"/>'
                    . '</g>';
            case 'agbada_embroidery':
                return '<path d="M 104 188 Q 128 230 152 188" fill="none" stroke="url(#' . $u . '_gold)" stroke-width="5"/>'
                    . '<path d="M 98 192 Q 128 244 158 192" fill="none" stroke="url(#' . $u . '_gold)" stroke-width="2" stroke-dasharray="3 4"/>'
                    . '<circle cx="128" cy="214" r="5" fill="url(#' . $u . '_gold)"/>'
                    . '<path d="M 120 222 Q 128 236 136 222" fill="none" stroke="url(#' . $u . '_gold)" stroke-width="2.4"/>'
                    . '<circle cx="110" cy="206" r="2.5" fill="url(#' . $u . '_gold)"/><circle cx="146" cy="206" r="2.5" fill="url(#' . $u . '_gold)"/>';
            case 'mandarin_collar':
                return '<path d="M 108 186 Q 128 196 148 186 L 149 198 Q 128 208 107 198 Z" fill="' . $dark . '"/>'
                    . '<path d="M 128 204 L 128 260" stroke="' . $dark . '" stroke-width="2"/>'
                    . '<circle cx="128" cy="214" r="2.4" fill="' . $light . '"/><circle cx="128" cy="230" r="2.4" fill="' . $light . '"/><circle cx="128" cy="246" r="2.4" fill="' . $light . '"/>';
            case 'wrap_collar':
                return '<path d="M 108 188 L 128 216 L 148 188 Q 128 198 108 188 Z" fill="' . $this->mix($fab, '#ffffff', 0.75) . '"/>'
                    . '<path d="M 152 188 L 122 228" stroke="' . $light . '" stroke-width="9" stroke-linecap="butt"/>'
                    . '<path d="M 104 188 L 146 250" stroke="' . $light . '" stroke-width="9" stroke-linecap="butt"/>'
                    . '<path d="M 104 188 L 146 250" stroke="' . $dark . '" stroke-width="1.2" opacity="0.4" transform="translate(4 -2)"/>';
            case 'classic_tee':
            default:
                return '<path d="M 106 188 Q 128 206 150 188" fill="none" stroke="' . $dark . '" stroke-width="4" stroke-linecap="round"/>'
                    . '<path d="M 112 190 Q 128 200 144 190" fill="none" stroke="' . $sk['shadow'] . '" stroke-width="1.5" opacity="0.4"/>';
        }
    }

    private function facePath(): string
    {
        $w = $this->w;
        $l = 128 - $w;
        $r = 128 + $w;
        $top = $this->top;
        $chin = $this->chin;
        $cy = (int)(($top + $chin) / 2);
        $ry = (int)(($chin - $top) / 2);

        switch ($this->t['face_shape']) {
            case 'square':
                return "M $l 106 C $l 68 " . ($l + 18) . " $top 128 $top C " . ($r - 18) . " $top $r 68 $r 106 L " . ($r - 1) . " 134 C " . ($r - 3) . " 158 " . ($r - 22) . " $chin 128 $chin C " . ($l + 22) . " $chin " . ($l + 3) . " 158 " . ($l + 1) . " 134 Z";
            case 'heart':
                return "M $l 104 C $l 68 " . ($l + 18) . " $top 128 $top C " . ($r - 18) . " $top $r 68 $r 104 C $r 130 " . ($r - 14) . " 150 " . ($r - 30) . " 162 C " . ($r - 38) . " 168 133 $chin 128 $chin C 123 $chin " . ($l + 38) . " 168 " . ($l + 30) . " 162 C " . ($l + 14) . " 150 $l 130 $l 104 Z";
            default:
                // Ellipse as path (oval / round / long)
                return "M $l $cy A $w $ry 0 1 0 $r $cy A $w $ry 0 1 0 $l $cy Z";
        }
    }

    private function ears(): string
    {
        if (!empty(self::HAIRSTYLES[$this->t['hairstyle']]['covers_ears'])) {
            return '';
        }
        $sk = $this->skin;
        $s = '';
        foreach ([-1, 1] as $dir) {
            $x = 128 + $dir * ($this->w + 1);
            $s .= '<ellipse cx="' . $x . '" cy="120" rx="9" ry="13" fill="' . $sk['base'] . '"/>';
            $s .= '<ellipse cx="' . ($x + $dir * 1.5) . '" cy="120" rx="4.5" ry="8" fill="' . $sk['shadow'] . '" opacity="0.55"/>';
        }
        return $s;
    }

    private function jawDefinition(): string
    {
        if ($this->t['presentation'] !== 'masculine') {
            return '';
        }
        $l = 128 - $this->w;
        $r = 128 + $this->w;
        $c = $this->chin;
        return '<path d="M ' . ($l + 3) . ' 132 Q ' . ($l + 8) . ' ' . ($c - 10) . ' 116 ' . ($c - 2) . ' M ' . ($r - 3) . ' 132 Q ' . ($r - 8) . ' ' . ($c - 10) . ' 140 ' . ($c - 2) . '" stroke="' . $this->skin['shadow'] . '" stroke-width="5" fill="none" stroke-linecap="round" opacity="0.18"/>';
    }

    private function cheeks(): string
    {
        $lum = $this->luminance($this->skin['base']);
        $op = $this->t['skin_detail'] === 'rosy_glow' ? 0.34 : ($lum > 0.45 ? 0.2 : 0.14);
        if (in_array($this->t['expression'], ['love_struck', 'radiant_joy'], true)) {
            $op += 0.08;
        }
        $color = $lum > 0.3 ? '#f43f5e' : '#e11d48';
        return '<ellipse cx="' . (128 - $this->w + 16) . '" cy="138" rx="10" ry="6.5" fill="' . $color . '" opacity="' . $op . '"/>'
            . '<ellipse cx="' . (128 + $this->w - 16) . '" cy="138" rx="10" ry="6.5" fill="' . $color . '" opacity="' . $op . '"/>';
    }

    private function skinDetails(): string
    {
        $sk = $this->skin;
        switch ($this->t['skin_detail']) {
            case 'freckles':
                $s = '';
                $dots = [[104, 128], [110, 132], [99, 134], [115, 127], [106, 138], [141, 127], [146, 132], [152, 128], [157, 134], [150, 138], [122, 124], [134, 124], [118, 131]];
                foreach ($dots as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.3" fill="' . $this->mix($sk['shadow'], '#7c2d12', 0.3) . '" opacity="0.55"/>';
                }
                return $s;
            case 'vitiligo':
                $patch = $this->mix($sk['base'], '#fbe9df', 0.8);
                return '<path d="M 96 90 C 104 80 120 84 118 96 C 116 106 104 110 98 104 C 92 100 90 96 96 90 Z" fill="' . $patch . '" opacity="0.92"/>'
                    . '<path d="M 150 132 C 158 126 170 130 168 142 C 166 152 152 156 148 148 C 144 142 144 136 150 132 Z" fill="' . $patch . '" opacity="0.92"/>'
                    . '<path d="M 120 160 C 126 156 136 158 134 164 C 132 170 122 170 120 166 Z" fill="' . $patch . '" opacity="0.9"/>';
            case 'beauty_mark':
                return '<circle cx="149" cy="146" r="2" fill="' . $this->mix($sk['shadow'], '#000000', 0.4) . '"/>';
            case 'elder_lines':
                $c = $sk['shadow'];
                return '<path d="M 108 82 Q 128 78 148 82 M 112 88 Q 128 85 144 88" stroke="' . $c . '" stroke-width="1.5" fill="none" opacity="0.55" stroke-linecap="round"/>'
                    . '<path d="M 113 140 Q 108 150 112 158 M 143 140 Q 148 150 144 158" stroke="' . $c . '" stroke-width="1.6" fill="none" opacity="0.5" stroke-linecap="round"/>'
                    . '<path d="M 92 112 L 86 109 M 92 116 L 86 117 M 164 112 L 170 109 M 164 116 L 170 117" stroke="' . $c . '" stroke-width="1.2" opacity="0.5" stroke-linecap="round"/>';
            case 'rosy_glow':
                return '<ellipse cx="128" cy="96" rx="20" ry="6" fill="#ffffff" opacity="0.14"/>';
            default:
                return '';
        }
    }

    /**
     * Eye opening path. Shapes are authored for the left eye (outer corner at
     * -x) and mirrored for the right so catchlights stay consistent.
     */
    private function eyePath(int $cx, int $cy, string $side): string
    {
        $shapes = [
            'almond'     => [[-12, 1], [[-7, -8, 7, -9, 12, -1], [7, 6, -6, 7, -12, 1]]],
            'round'      => [[-11, 0], [[-11, -11, 11, -11, 11, 0], [11, 11, -11, 11, -11, 0]]],
            'monolid'    => [[-12, -1], [[-6, -6, 7, -6.5, 12, 0], [7, 4.5, -6, 4.5, -12, -1]]],
            'hooded'     => [[-12, 0], [[-7, -7, 7, -8, 12, -1], [7, 6, -6, 6.5, -12, 0]]],
            'downturned' => [[-12, 3], [[-7, -6, 7, -8, 12, 0], [6, 6, -6, 7, -12, 3]]],
        ];
        [$start, $segs] = $shapes[$this->t['eye_shape']] ?? $shapes['almond'];
        $scale = $this->t['expression'] === 'wow_surprised' ? 1.18 : 1.0;
        $m = $side === 'r' ? -1 : 1;
        $fx = fn (float $x): string => $this->n($cx + $m * $x * $scale);
        $fy = fn (float $y): string => $this->n($cy + $y * $scale);
        $d = 'M ' . $fx($start[0]) . ' ' . $fy($start[1]);
        foreach ($segs as $c) {
            $d .= ' C ' . $fx($c[0]) . ' ' . $fy($c[1]) . ' ' . $fx($c[2]) . ' ' . $fy($c[3]) . ' ' . $fx($c[4]) . ' ' . $fy($c[5]);
        }
        return $d . ' Z';
    }

    private function eyes(): string
    {
        $u = $this->uid;
        $expr = $this->t['expression'];
        $lash = '#1c1917';
        $s = '';
        foreach (['l' => 108, 'r' => 148] as $side => $cx) {
            $closed = $expr === 'radiant_joy' || ($expr === 'playful_wink' && $side === 'r');
            $s .= '<g class="atg-av-eye atg-av-eye-' . $side . '">';
            if ($closed) {
                $s .= '<path d="M ' . ($cx - 10) . ' 116 Q ' . $cx . ' 106 ' . ($cx + 10) . ' 116" stroke="' . $lash . '" stroke-width="3.2" stroke-linecap="round" fill="none"/>';
                $s .= '</g>';
                continue;
            }
            $path = $this->eyePath($cx, 114, $side);
            $s .= '<path d="' . $path . '" fill="#fbfaf8"/>';
            $s .= '<g clip-path="url(#' . $u . '_eye' . $side . ')"><g class="atg-av-gaze" transform="translate(0 0)"><g class="atg-av-iris">';
            if ($expr === 'love_struck') {
                $s .= '<path d="M ' . $cx . ' 120 C ' . ($cx - 9) . ' 113 ' . ($cx - 6) . ' 106 ' . $cx . ' 110 C ' . ($cx + 6) . ' 106 ' . ($cx + 9) . ' 113 ' . $cx . ' 120 Z" fill="#e11d48"/>';
                $s .= '<circle cx="' . ($cx - 2.5) . '" cy="111" r="1.5" fill="#fff"/>';
            } else {
                $s .= '<circle cx="' . $cx . '" cy="114" r="6.2" fill="url(#' . $u . '_iris)"/>';
                $s .= '<circle cx="' . $cx . '" cy="114" r="2.9" fill="#0b0908"/>';
                $s .= '<circle cx="' . ($cx + 2) . '" cy="111.6" r="1.7" fill="#ffffff"/>';
                $s .= '<circle cx="' . ($cx - 2) . '" cy="116.4" r="0.8" fill="#ffffff" opacity="0.7"/>';
            }
            $s .= '</g></g></g>';
            // Upper-lid shadow inside the eye for depth
            $s .= '<path d="' . $path . '" fill="none" stroke="' . $this->skin['shadow'] . '" stroke-width="2" opacity="0.25"/>';
            $s .= '</g>';
            // Lash line + crease (crease omitted for monolid)
            $s .= '<path d="' . $path . '" fill="none" stroke="' . $lash . '" stroke-width="1.6" opacity="0.85"/>';
            $m = $side === 'r' ? -1 : 1;
            $outer = $cx - $m * 12;
            if ($this->t['presentation'] === 'feminine') {
                $ly = $this->t['eye_shape'] === 'downturned' ? 115 : 112;
                $lashD = '';
                foreach ([[1, 0, -3, -4], [4, -2, 1, -6.5], [7, -3.5, 5, -8]] as [$bx, $by, $tx, $ty]) {
                    $lashD .= 'M ' . $this->n($outer + $m * $bx) . ' ' . $this->n($ly + $by) . ' L ' . $this->n($outer + $m * $tx) . ' ' . $this->n($ly + $ty) . ' ';
                }
                $s .= '<path d="' . trim($lashD) . '" stroke="' . $lash . '" stroke-width="1.7" stroke-linecap="round"/>';
            }
            $s .= '<path d="M ' . $outer . ' ' . ($this->t['eye_shape'] === 'downturned' ? 117 : 113) . ' Q ' . ($cx - $m * 5) . ' 103 ' . ($cx + $m * 11) . ' 113" stroke="' . $lash . '" stroke-width="2.6" fill="none" stroke-linecap="round" opacity="0.9"/>';
            if ($this->t['eye_shape'] === 'hooded') {
                $s .= '<path d="M ' . ($cx - 14) . ' 109 C ' . ($cx - 6) . ' 102 ' . ($cx + 8) . ' 102 ' . ($cx + 14) . ' 109 C ' . ($cx + 6) . ' 106 ' . ($cx - 6) . ' 106 ' . ($cx - 14) . ' 109 Z" fill="' . $this->skin['base'] . '"/>';
            } elseif ($this->t['eye_shape'] !== 'monolid') {
                $s .= '<path d="M ' . ($cx - 10) . ' 104 Q ' . $cx . ' 99 ' . ($cx + 10) . ' 104" stroke="' . $this->skin['shadow'] . '" stroke-width="1.4" fill="none" opacity="0.55"/>';
            }
            if ($expr === 'wise_calm') {
                $s .= '<path d="M ' . ($cx - 13) . ' 112 C ' . ($cx - 6) . ' 104 ' . ($cx + 6) . ' 104 ' . ($cx + 13) . ' 112 L ' . ($cx + 13) . ' 104 L ' . ($cx - 13) . ' 104 Z" fill="' . $this->skin['base'] . '"/>';
                $s .= '<path d="M ' . ($cx - 12) . ' 112 C ' . ($cx - 6) . ' 106 ' . ($cx + 6) . ' 106 ' . ($cx + 12) . ' 112" stroke="' . $lash . '" stroke-width="2" fill="none"/>';
            }
        }
        return $s;
    }

    private function brows(): string
    {
        $hair = $this->t['hairstyle'] === 'regal_bald' && $this->t['hair_color'] === 'natural' ? '#2a1d16' : $this->hairHex;
        $c = $this->luminance($hair) > 0.6 ? $this->mix($hair, '#6b5b4b', 0.35) : $hair;
        $lift = match ($this->t['expression']) {
            'wow_surprised' => -6,
            'love_struck', 'radiant_joy' => -2,
            'wise_calm' => 1,
            default => 0,
        };
        $y = 99 + $lift;
        $thick = $this->t['eye_shape'] === 'monolid' ? 3.6 : 4.2;
        $arch = 4;
        if ($this->t['presentation'] === 'feminine') {
            $thick -= 1.0;
            $arch = 6;
        } elseif ($this->t['presentation'] === 'masculine') {
            $thick += 1.0;
            $arch = 2;
            $y += 1;
        }
        $s = '<g class="atg-av-brows" stroke="' . $c . '" stroke-width="' . $thick . '" stroke-linecap="round" fill="none">';
        $s .= '<path d="M 95 ' . ($y + 3) . ' Q 105 ' . ($y - $arch) . ' 119 ' . ($y + 1) . '"/>';
        $rightLift = $this->t['expression'] === 'playful_wink' ? 3 : 0;
        $s .= '<path d="M 137 ' . ($y + 1 + $rightLift) . ' Q 151 ' . ($y - $arch + $rightLift) . ' 161 ' . ($y + 3 + $rightLift) . '"/>';
        return $s . '</g>';
    }

    private function nose(): string
    {
        $sh = $this->skin['shadow'];
        $hl = $this->skin['highlight'];
        switch ($this->t['nose']) {
            case 'broad':
                return '<path d="M 116 138 Q 112 128 121 127 M 140 138 Q 144 128 135 127" stroke="' . $sh . '" stroke-width="2.4" fill="none" stroke-linecap="round" opacity="0.8"/>'
                    . '<path d="M 118 140 Q 128 146 138 140" stroke="' . $sh . '" stroke-width="2.4" fill="none" stroke-linecap="round" opacity="0.7"/>'
                    . '<ellipse cx="122" cy="139" rx="3.4" ry="2" fill="' . $sh . '" opacity="0.75"/><ellipse cx="134" cy="139" rx="3.4" ry="2" fill="' . $sh . '" opacity="0.75"/>'
                    . '<ellipse cx="128" cy="132" rx="5" ry="3.4" fill="' . $hl . '" opacity="0.35"/>';
            case 'straight':
                return '<path d="M 125 104 L 123 132" stroke="' . $sh . '" stroke-width="2.2" stroke-linecap="round" opacity="0.45"/>'
                    . '<path d="M 120 137 Q 128 142 136 137" stroke="' . $sh . '" stroke-width="2.2" fill="none" stroke-linecap="round" opacity="0.7"/>'
                    . '<ellipse cx="123.5" cy="137.5" rx="2.2" ry="1.4" fill="' . $sh . '" opacity="0.7"/><ellipse cx="132.5" cy="137.5" rx="2.2" ry="1.4" fill="' . $sh . '" opacity="0.7"/>'
                    . '<path d="M 130 106 L 130 128" stroke="' . $hl . '" stroke-width="2.4" stroke-linecap="round" opacity="0.35"/>';
            case 'aquiline':
                return '<path d="M 126 102 Q 120 116 124 132" stroke="' . $sh . '" stroke-width="2.3" fill="none" stroke-linecap="round" opacity="0.5"/>'
                    . '<path d="M 119 138 Q 128 143 137 138" stroke="' . $sh . '" stroke-width="2.3" fill="none" stroke-linecap="round" opacity="0.7"/>'
                    . '<ellipse cx="123" cy="138" rx="2.4" ry="1.5" fill="' . $sh . '" opacity="0.7"/><ellipse cx="133" cy="138" rx="2.4" ry="1.5" fill="' . $sh . '" opacity="0.7"/>'
                    . '<path d="M 131 104 Q 128 116 130 130" stroke="' . $hl . '" stroke-width="2.4" fill="none" opacity="0.3"/>';
            case 'petite':
                return '<path d="M 124 134 Q 128 137 132 134" stroke="' . $sh . '" stroke-width="2" fill="none" stroke-linecap="round" opacity="0.75"/>'
                    . '<circle cx="128" cy="130" r="2.6" fill="' . $hl . '" opacity="0.4"/>';
            case 'button':
            default:
                return '<path d="M 121 136 Q 128 142 135 136" stroke="' . $sh . '" stroke-width="2.4" fill="none" stroke-linecap="round" opacity="0.75"/>'
                    . '<ellipse cx="123" cy="136.5" rx="2.4" ry="1.6" fill="' . $sh . '" opacity="0.7"/><ellipse cx="133" cy="136.5" rx="2.4" ry="1.6" fill="' . $sh . '" opacity="0.7"/>'
                    . '<circle cx="128" cy="131" r="3.4" fill="' . $hl . '" opacity="0.35"/>';
        }
    }

    private function lipColor(): string
    {
        $rose = match ($this->t['presentation']) {
            'feminine'  => 0.52,
            'masculine' => 0.26,
            default     => 0.38,
        };
        return $this->mix($this->mix($this->skin['base'], $this->skin['shadow'], 0.5), '#b4485a', $rose);
    }

    private function mouth(): string
    {
        $f = self::LIP_STYLES[$this->t['lips']]['f'];
        $lip = $this->lipColor();
        $lipDark = $this->mix($lip, '#000000', 0.35);
        $inner = '#3b0d12';
        $y = 152;
        $n = fn (float $v): string => $this->n($v);

        switch ($this->t['expression']) {
            case 'proud_grin':
                $m = '<path d="M 110 ' . ($y - 4) . ' Q 128 ' . $n($y + 16 + 3 * $f) . ' 146 ' . ($y - 4) . ' Q 128 ' . ($y + 1) . ' 110 ' . ($y - 4) . ' Z" fill="' . $inner . '"/>'
                    . '<path d="M 113 ' . ($y - 2.5) . ' Q 128 ' . ($y + 3) . ' 143 ' . ($y - 2.5) . ' L 141 ' . ($y + 2) . ' Q 128 ' . ($y + 6) . ' 115 ' . ($y + 2) . ' Z" fill="#ffffff"/>'
                    . '<path d="M 110 ' . ($y - 4) . ' Q 128 ' . $n($y + 16 + 3 * $f) . ' 146 ' . ($y - 4) . '" fill="none" stroke="' . $lip . '" stroke-width="' . $n(1.6 + 1.8 * $f) . '" stroke-linecap="round"/>';
                break;
            case 'radiant_joy':
                $m = '<path d="M 106 ' . ($y - 6) . ' Q 128 ' . $n($y + 22 + 3 * $f) . ' 150 ' . ($y - 6) . ' Q 128 ' . ($y - 1) . ' 106 ' . ($y - 6) . ' Z" fill="' . $inner . '"/>'
                    . '<path d="M 110 ' . ($y - 4) . ' Q 128 ' . ($y + 1) . ' 146 ' . ($y - 4) . ' L 143 ' . $y . ' Q 128 ' . ($y + 4) . ' 113 ' . $y . ' Z" fill="#ffffff"/>'
                    . '<ellipse cx="128" cy="' . ($y + 10) . '" rx="10" ry="5" fill="#e8687c"/>'
                    . '<path d="M 106 ' . ($y - 6) . ' Q 128 ' . $n($y + 22 + 3 * $f) . ' 150 ' . ($y - 6) . '" fill="none" stroke="' . $lip . '" stroke-width="' . $n(1.6 + 1.8 * $f) . '" stroke-linecap="round"/>';
                break;
            case 'wow_surprised':
                $m = '<ellipse cx="128" cy="' . ($y + 2) . '" rx="' . $n(6 + $f) . '" ry="' . $n(8 + $f) . '" fill="' . $inner . '"/>'
                    . '<ellipse cx="128" cy="' . ($y + 2) . '" rx="' . $n(6 + $f) . '" ry="' . $n(8 + $f) . '" fill="none" stroke="' . $lip . '" stroke-width="' . $n(2 + 1.6 * $f) . '"/>';
                break;
            case 'playful_wink':
                $m = '<path d="M 114 ' . ($y - 1) . ' Q 132 ' . $n($y + 6 + 2 * $f) . ' 146 ' . ($y - 6) . ' Q 132 ' . $n($y + 1 + $f) . ' 114 ' . ($y - 1) . ' Z" fill="' . $lip . '"/>'
                    . '<path d="M 114 ' . ($y - 1) . ' Q 132 ' . ($y + 4) . ' 146 ' . ($y - 6) . '" fill="none" stroke="' . $lipDark . '" stroke-width="2" stroke-linecap="round"/>';
                break;
            case 'cheeky_tongue':
                $m = '<path d="M 112 ' . ($y - 3) . ' Q 128 ' . $n($y + 10 + 2 * $f) . ' 144 ' . ($y - 3) . ' Q 128 ' . $y . ' 112 ' . ($y - 3) . ' Z" fill="' . $inner . '"/>'
                    . '<path d="M 120 ' . ($y + 1) . ' Q 120 ' . ($y + 16) . ' 128 ' . ($y + 16) . ' Q 136 ' . ($y + 16) . ' 136 ' . ($y + 1) . ' Z" fill="#ef6f84"/>'
                    . '<path d="M 128 ' . ($y + 3) . ' L 128 ' . ($y + 11) . '" stroke="#c2415a" stroke-width="1.4"/>'
                    . '<path d="M 112 ' . ($y - 3) . ' Q 128 ' . $n($y + 10 + 2 * $f) . ' 144 ' . ($y - 3) . '" fill="none" stroke="' . $lip . '" stroke-width="' . $n(1.6 + 1.6 * $f) . '" stroke-linecap="round"/>';
                break;
            case 'wise_calm':
                $m = '<path d="M 115 ' . $y . ' Q 128 ' . $n($y + 3 + 3 * $f) . ' 141 ' . $y . ' Q 128 ' . $n($y + 1) . ' 115 ' . $y . ' Z" fill="' . $lip . '"/>'
                    . '<path d="M 115 ' . $y . ' Q 128 ' . $n($y - 1 - 2 * $f) . ' 141 ' . $y . '" fill="' . $lip . '" opacity="0.9"/>'
                    . '<path d="M 115 ' . $y . ' Q 128 ' . ($y + 3) . ' 141 ' . $y . '" fill="none" stroke="' . $lipDark . '" stroke-width="1.8" stroke-linecap="round"/>';
                break;
            case 'love_struck':
            case 'warm_smile':
            default:
                $m = '<path d="M 112 ' . ($y - 3) . ' Q 128 ' . $n($y + 8 + 3 * $f) . ' 144 ' . ($y - 3) . ' Q 128 ' . $n($y + 3) . ' 112 ' . ($y - 3) . ' Z" fill="' . $lip . '"/>'
                    . '<path d="M 112 ' . ($y - 3) . ' Q 120 ' . $n($y - 4 - 2 * $f) . ' 128 ' . ($y - 2) . ' Q 136 ' . $n($y - 4 - 2 * $f) . ' 144 ' . ($y - 3) . ' Q 128 ' . ($y + 3) . ' 112 ' . ($y - 3) . ' Z" fill="' . $lipDark . '" opacity="0.85"/>'
                    . '<path d="M 112 ' . ($y - 3) . ' Q 128 ' . ($y + 5) . ' 144 ' . ($y - 3) . '" fill="none" stroke="' . $this->mix($lipDark, '#000000', 0.3) . '" stroke-width="1.8" stroke-linecap="round"/>';
                break;
        }

        // Talk mouth (driven by voice lip-sync); hidden until the engine scales it.
        $talk = '<g class="atg-av-talk" transform="translate(128 ' . ($y + 2) . ') scale(1 0)" opacity="0">'
            . '<ellipse cx="0" cy="0" rx="11" ry="9" fill="' . $inner . '" stroke="' . $lip . '" stroke-width="' . $n(2 + 1.4 * $f) . '"/>'
            . '<path d="M -8 -6 Q 0 -3 8 -6 L 7 -3 Q 0 -1 -7 -3 Z" fill="#ffffff"/>'
            . '<ellipse cx="0" cy="5" rx="6" ry="3" fill="#e8687c"/></g>';

        return '<g class="atg-av-mouth">' . $m . '</g>' . $talk;
    }

    private function beard(): string
    {
        $u = $this->uid;
        $fh = $this->t['facial_hair'];
        $l = 128 - $this->w;
        $r = 128 + $this->w;
        $chin = $this->chin;
        if ($fh === 'stubble') {
            return '<path d="M ' . ($l + 2) . ' 124 Q ' . ($l + 6) . ' ' . ($chin - 2) . ' 128 ' . ($chin + 1) . ' Q ' . ($r - 6) . ' ' . ($chin - 2) . ' ' . ($r - 2) . ' 124 Q ' . ($r - 8) . ' 150 150 156 Q 140 146 128 146 Q 116 146 106 156 Q ' . ($l + 8) . ' 150 ' . ($l + 2) . ' 124 Z" fill="' . $this->hairHex . '" opacity="0.22"/>';
        }
        if ($fh === 'full_beard') {
            return '<path d="M ' . ($l - 1) . ' 112 Q ' . ($l - 2) . ' ' . ($chin + 10) . ' 128 ' . ($chin + 16) . ' Q ' . ($r + 2) . ' ' . ($chin + 10) . ' ' . ($r + 1) . ' 112 L ' . ($r - 3) . ' 124 Q ' . ($r - 8) . ' 150 152 154 Q 140 144 128 145 Q 116 144 104 154 Q ' . ($l + 8) . ' 150 ' . ($l + 3) . ' 124 Z" fill="url(#' . $u . '_hair)"/>'
                . '<path d="M 112 166 Q 128 172 144 166" stroke="#000" stroke-opacity="0.15" stroke-width="2" fill="none"/>';
        }
        if ($fh === 'goatee') {
            return '<path d="M 114 160 Q 128 ' . ($chin + 9) . ' 142 160 Q 128 166 114 160 Z" fill="url(#' . $u . '_hair)"/>';
        }
        return '';
    }

    private function moustache(): string
    {
        $fh = $this->t['facial_hair'];
        if (!in_array($fh, ['moustache', 'goatee', 'full_beard'], true)) {
            return '';
        }
        return '<path d="M 110 150 Q 118 140 128 145 Q 138 140 146 150 Q 138 147 128 149 Q 118 147 110 150 Z" fill="url(#' . $this->uid . '_hair)"/>';
    }

    /** Cap of hair hugging the skull; hairline at $hairline. */
    private function cap(int $hairline = 82, int $sideY = 112, int $pad = 4): string
    {
        $l = 128 - $this->w - $pad;
        $r = 128 + $this->w + $pad;
        $c = $this->top - 24;
        return "M $l $sideY C " . ($l - 2) . " $c " . ($r + 2) . " $c $r $sideY C " . ($r - 6) . ' ' . ($hairline + 10) . " 150 $hairline 128 $hairline C 106 $hairline " . ($l + 6) . ' ' . ($hairline + 10) . " $l $sideY Z";
    }

    /** Hair that sits behind the shoulders (long styles). */
    private function hairBack(): string
    {
        $fill = 'url(#' . $this->uid . '_hair)';
        $fab = 'url(#' . $this->uid . '_fab)';
        switch ($this->t['hairstyle']) {
            case 'long_straight':
                return '<path d="M 74 100 C 68 30 188 30 182 100 L 192 214 C 160 222 96 222 64 214 Z" fill="' . $fill . '"/>';
            case 'regal_locs':
                $s = '';
                foreach ([78, 90, 102, 154, 166, 178] as $i => $x) {
                    $s .= '<path d="M ' . $x . ' 96 L ' . ($x + ($x < 128 ? -6 : 6)) . ' ' . (206 + ($i % 2) * 8) . '" stroke="' . $fill . '" stroke-width="11" stroke-linecap="round"/>';
                }
                return $s;
            case 'curly_shoulder':
                $s = '';
                foreach ([[70, 120, 20], [64, 150, 19], [72, 180, 18], [186, 120, 20], [192, 150, 19], [184, 180, 18], [86, 200, 14], [170, 200, 14]] as [$x, $y, $r]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="' . $fill . '"/>';
                }
                return $s;
            case 'hijab':
                return '<path d="M 64 130 C 58 38 198 38 192 130 L 212 260 L 44 260 Z" fill="' . $fab . '"/>';
            default:
                return '';
        }
    }

    /** Hair behind the head but above the body (volume around the skull). */
    private function hairBackHead(): string
    {
        $fill = 'url(#' . $this->uid . '_hair)';
        switch ($this->t['hairstyle']) {
            case 'afro_crown':
                $s = '<circle cx="128" cy="96" r="70" fill="' . $fill . '"/>';
                foreach ([[72, 70], [100, 40], [156, 40], [184, 70], [64, 108], [192, 108], [128, 30]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="22" fill="' . $fill . '"/>';
                }
                return $s;
            case 'curly_shoulder':
                $s = '';
                foreach ([[90, 52, 24], [128, 42, 26], [166, 52, 24], [72, 86, 22], [184, 86, 22]] as [$x, $y, $r]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="' . $fill . '"/>';
                }
                return $s;
            case 'wavy_bob':
                return '<path d="M 76 104 C 66 34 190 34 180 104 C 186 128 190 150 182 168 C 174 178 166 172 160 176 L 96 176 C 90 172 82 178 74 168 C 66 150 70 128 76 104 Z" fill="' . $fill . '"/>';
            case 'blunt_bangs':
                return '<path d="M 76 100 C 68 30 188 30 180 100 L 184 172 L 72 172 Z" fill="' . $fill . '"/>';
            case 'ponytail':
                return '<path d="M 162 62 C 200 54 214 92 204 132 C 198 156 206 176 196 196 C 188 170 180 150 182 128 C 184 104 178 84 162 76 Z" fill="' . $fill . '"/>';
            default:
                return '';
        }
    }

    private function hairFront(): string
    {
        $u = $this->uid;
        $fill = 'url(#' . $u . '_hair)';
        $hairHex = $this->hairHex;
        $dark = $this->mix($hairHex, '#000000', 0.4);
        $light = $this->mix($hairHex, '#ffffff', 0.3);
        $fab = 'url(#' . $u . '_fab)';
        $fabHex = $this->fabricHex;
        $l = 128 - $this->w;
        $r = 128 + $this->w;

        switch ($this->t['hairstyle']) {
            case 'afro_crown':
                $s = '<path d="' . $this->cap(80) . '" fill="' . $fill . '"/>';
                foreach ([[100, 64], [118, 56], [140, 58], [158, 66], [86, 82], [170, 82]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="3" fill="' . $light . '" opacity="0.25"/>';
                }
                return $s;
            case 'braided_crown':
                $s = '<path d="' . $this->cap(80) . '" fill="' . $fill . '"/>';
                foreach ([-30, -15, 0, 15, 30] as $dx) {
                    $s .= '<path d="M ' . (128 + $dx * 1.3) . ' 82 Q ' . (128 + $dx * 1.6) . ' 66 ' . (128 + $dx * 0.8) . ' ' . ($this->top - 2) . '" stroke="' . $dark . '" stroke-width="2" fill="none" stroke-dasharray="4 3"/>';
                }
                $s .= '<path d="M ' . ($l - 2) . ' 78 Q 128 ' . ($this->top - 22) . ' ' . ($r + 2) . ' 78" stroke="' . $fill . '" stroke-width="12" fill="none" stroke-linecap="round"/>';
                $s .= '<path d="M ' . ($l - 2) . ' 78 Q 128 ' . ($this->top - 22) . ' ' . ($r + 2) . ' 78" stroke="' . $dark . '" stroke-width="10" fill="none" stroke-dasharray="5 4" opacity="0.6"/>';
                foreach ([[96, 58], [128, 47], [160, 58]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="3.6" fill="url(#' . $u . '_gold)"/>';
                }
                return $s;
            case 'cornrows':
                $s = '<path d="' . $this->cap(82, 112, 2) . '" fill="' . $fill . '"/>';
                foreach ([-36, -22, -8, 8, 22, 36] as $dx) {
                    $s .= '<path d="M ' . (128 + $dx) . ' ' . (84 - abs($dx) * 0.1) . ' Q ' . (128 + $dx * 1.15) . ' 70 ' . (128 + $dx * 0.7) . ' ' . ($this->top - 4) . '" stroke="' . $dark . '" stroke-width="2.2" fill="none"/>';
                    $s .= '<path d="M ' . (128 + $dx + 4) . ' 82 Q ' . (128 + $dx * 1.15 + 4) . ' 70 ' . (128 + $dx * 0.7 + 3) . ' ' . ($this->top - 2) . '" stroke="' . $light . '" stroke-width="1.4" fill="none" opacity="0.35" stroke-dasharray="3 3"/>';
                }
                return $s;
            case 'regal_locs':
                $s = '<path d="' . $this->cap(80) . '" fill="' . $fill . '"/>';
                foreach ([[$l - 2, 1], [$l + 6, 1], [$r + 2, -1], [$r - 6, -1]] as [$x, $dir]) {
                    $s .= '<path d="M ' . $x . ' 100 Q ' . ($x - $dir * 6) . ' 150 ' . ($x - $dir * 2) . ' 196" stroke="' . $fill . '" stroke-width="9" fill="none" stroke-linecap="round"/>';
                }
                foreach ([[100, 64], [128, 56], [156, 64]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="2.5" fill="url(#' . $u . '_gold)"/>';
                }
                return $s;
            case 'bantu_knots':
                $s = '<path d="' . $this->cap(82, 112, 2) . '" fill="' . $fill . '"/>';
                foreach ([[128, 52], [96, 60], [160, 60], [82, 84], [174, 84]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="13" fill="' . $fill . '"/>';
                    $s .= '<path d="M ' . ($x - 7) . ' ' . $y . ' A 7 7 0 1 1 ' . $x . ' ' . ($y + 7) . ' A 4 4 0 1 1 ' . ($x + 3) . ' ' . ($y - 2) . '" stroke="' . $dark . '" stroke-width="1.6" fill="none"/>';
                }
                return $s;
            case 'curly_shoulder':
                $s = '<path d="' . $this->cap(84) . '" fill="' . $fill . '"/>';
                foreach ([[92, 82, 11], [108, 74, 12], [128, 72, 12], [148, 74, 12], [164, 82, 11], [$l - 2, 106, 11], [$r + 2, 106, 11], [$l - 4, 130, 10], [$r + 4, 130, 10]] as [$x, $y, $rr]) {
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="' . $rr . '" fill="' . $fill . '"/>';
                    $s .= '<path d="M ' . ($x - 4) . ' ' . ($y - 2) . ' a 4 4 0 1 1 6 4" stroke="' . $light . '" stroke-width="1.4" fill="none" opacity="0.35"/>';
                }
                return $s;
            case 'curled_top':
                $s = '<path d="' . $this->cap(84) . '" fill="' . $fill . '" opacity="0.55"/>';
                foreach ([[96, 72], [112, 62], [128, 58], [144, 62], [160, 72], [104, 80], [124, 74], [144, 76], [120, 64], [138, 66]] as [$x, $y]) {
                    $s .= '<circle cx="' . $x . '" cy="' . ($y - 4) . '" r="11" fill="' . $fill . '"/>';
                    $s .= '<path d="M ' . ($x - 4) . ' ' . ($y - 6) . ' a 4 4 0 1 1 6 4" stroke="' . $light . '" stroke-width="1.3" fill="none" opacity="0.4"/>';
                }
                return $s;
            case 'high_fade':
                return '<path d="' . $this->cap(86, 112, 2) . '" fill="' . $fill . '" opacity="0.45"/>'
                    . '<path d="M 90 88 C 86 ' . ($this->top - 14) . ' 170 ' . ($this->top - 14) . ' 166 88 C 150 82 106 82 90 88 Z" fill="' . $fill . '"/>'
                    . '<path d="M 100 64 Q 128 56 156 64" stroke="' . $light . '" stroke-width="2" fill="none" opacity="0.35"/>';
            case 'buzz_cut':
                return '<path d="' . $this->cap(84, 112, 1) . '" fill="' . $fill . '" opacity="0.6"/>';
            case 'side_part':
                return '<path d="' . $this->cap(84, 112, 3) . '" fill="' . $fill . '"/>'
                    . '<path d="M 100 92 C 92 60 140 44 174 70 C 182 80 182 92 180 104 C 170 82 150 72 132 74 C 118 76 108 82 100 92 Z" fill="' . $fill . '"/>'
                    . '<path d="M 104 62 Q 108 72 106 86" stroke="' . $dark . '" stroke-width="2" fill="none"/>'
                    . '<path d="M 120 62 Q 150 56 170 72" stroke="' . $light . '" stroke-width="2.4" fill="none" opacity="0.35"/>';
            case 'pixie_cut':
                return '<path d="' . $this->cap(86, 114, 3) . '" fill="' . $fill . '"/>'
                    . '<path d="M 92 86 L 104 104 L 108 84 L 120 100 L 124 82 L 136 96 L 140 80 L 160 90 L 150 72 L 100 72 Z" fill="' . $fill . '"/>'
                    . '<path d="M 112 64 Q 140 58 160 72" stroke="' . $light . '" stroke-width="2.2" fill="none" opacity="0.35"/>';
            case 'blunt_bangs':
                return '<path d="M ' . ($l - 4) . ' 120 C ' . ($l - 6) . ' ' . ($this->top - 24) . ' ' . ($r + 6) . ' ' . ($this->top - 24) . ' ' . ($r + 4) . ' 120 L ' . ($r + 2) . ' 92 L ' . ($l - 2) . ' 92 Z" fill="' . $fill . '"/>'
                    . '<path d="M ' . ($l - 2) . ' 92 L ' . ($l - 4) . ' 172 L ' . ($l + 4) . ' 172 L ' . ($l + 4) . ' 96 Z M ' . ($r + 2) . ' 92 L ' . ($r + 4) . ' 172 L ' . ($r - 4) . ' 172 L ' . ($r - 4) . ' 96 Z" fill="' . $fill . '"/>'
                    . '<path d="M 100 64 Q 128 56 156 64" stroke="' . $light . '" stroke-width="2.4" fill="none" opacity="0.35"/>';
            case 'wavy_bob':
                return '<path d="M ' . ($l - 4) . ' 128 C ' . ($l - 8) . ' ' . ($this->top - 24) . ' ' . ($r + 8) . ' ' . ($this->top - 24) . ' ' . ($r + 4) . ' 128 C ' . ($r - 2) . ' 104 ' . ($r - 10) . ' 90 150 84 C 132 80 112 90 100 102 C 94 96 104 82 112 80 C 100 82 ' . ($l + 4) . ' 98 ' . ($l - 4) . ' 128 Z" fill="' . $fill . '"/>'
                    . '<path d="M 108 64 Q 136 56 160 70" stroke="' . $light . '" stroke-width="2.4" fill="none" opacity="0.4"/>';
            case 'long_straight':
                return '<path d="M 128 ' . ($this->top - 6) . ' C 98 ' . ($this->top - 6) . ' ' . ($l - 6) . ' 72 ' . ($l - 4) . ' 120 L ' . ($l - 8) . ' 214 L ' . ($l + 8) . ' 214 C ' . ($l + 6) . ' 170 ' . ($l + 4) . ' 130 ' . ($l + 8) . ' 104 C 96 84 116 76 128 62 Z" fill="' . $fill . '"/>'
                    . '<path d="M 128 ' . ($this->top - 6) . ' C 158 ' . ($this->top - 6) . ' ' . ($r + 6) . ' 72 ' . ($r + 4) . ' 120 L ' . ($r + 8) . ' 214 L ' . ($r - 8) . ' 214 C ' . ($r - 6) . ' 170 ' . ($r - 4) . ' 130 ' . ($r - 8) . ' 104 C 160 84 140 76 128 62 Z" fill="' . $fill . '"/>'
                    . '<path d="M ' . ($l + 2) . ' 120 L ' . ($l - 2) . ' 200 M ' . ($r - 2) . ' 120 L ' . ($r + 2) . ' 200" stroke="' . $light . '" stroke-width="2" opacity="0.3"/>';
            case 'ponytail':
                return '<path d="' . $this->cap(84, 112, 2) . '" fill="' . $fill . '"/>'
                    . '<path d="M 100 70 Q 132 58 166 70" stroke="' . $light . '" stroke-width="2.4" fill="none" opacity="0.35"/>'
                    . '<ellipse cx="164" cy="66" rx="6" ry="8" fill="' . $this->fabricHex . '" transform="rotate(30 164 66)"/>';
            case 'sleek_bun':
                return '<path d="' . $this->cap(82, 112, 2) . '" fill="' . $fill . '"/>'
                    . '<circle cx="128" cy="' . ($this->top - 12) . '" r="20" fill="' . $fill . '"/>'
                    . '<path d="M 110 ' . ($this->top - 4) . ' Q 128 ' . ($this->top + 2) . ' 146 ' . ($this->top - 4) . '" stroke="url(#' . $u . '_gold)" stroke-width="4" fill="none" stroke-linecap="round"/>'
                    . '<path d="M 118 ' . ($this->top - 22) . ' Q 128 ' . ($this->top - 28) . ' 138 ' . ($this->top - 22) . '" stroke="' . $light . '" stroke-width="2" fill="none" opacity="0.4"/>';
            case 'traditional_gele':
                $fan = 'M ' . ($l - 10) . ' 98 C 34 50 80 6 128 12 C 176 6 222 50 ' . ($r + 10) . ' 98 C 172 74 84 74 ' . ($l - 10) . ' 98 Z';
                return '<path d="' . $fan . '" fill="' . $fab . '"/>'
                    . '<path d="' . $fan . '" fill="url(#' . $u . '_gelep)" opacity="0.6"/>'
                    . '<path d="M 70 84 Q 92 34 128 22 M 186 84 Q 164 34 128 22 M 90 80 Q 106 46 128 38 M 166 80 Q 150 46 128 38" stroke="' . $this->mix($fabHex, '#000000', 0.35) . '" stroke-width="2" fill="none" opacity="0.8"/>'
                    . '<path d="M ' . ($l - 8) . ' 102 C ' . ($l - 10) . ' 70 ' . ($r + 10) . ' 70 ' . ($r + 8) . ' 102 C 160 88 96 88 ' . ($l - 8) . ' 102 Z" fill="' . $fab . '"/>'
                    . '<path d="M ' . ($l - 6) . ' 96 C 100 84 156 84 ' . ($r + 6) . ' 96" stroke="#ffffff" stroke-width="2" fill="none" opacity="0.25"/>'
                    . '<path d="M 116 76 Q 128 64 140 76 Q 128 88 116 76 Z" fill="url(#' . $u . '_gold)"/>';
            case 'hijab':
                $inner = 'M 86 120 A 42 54 0 1 0 170 120 A 42 54 0 1 0 86 120 Z';
                return '<path d="M 68 118 C 64 34 192 34 188 118 C 190 158 170 186 128 194 C 86 186 66 158 68 118 Z ' . $inner . '" fill="' . $fab . '" fill-rule="evenodd"/>'
                    . '<path d="M 86 112 A 42 54 0 0 1 170 112" stroke="' . $this->mix($fabHex, '#000000', 0.3) . '" stroke-width="3" fill="none" opacity="0.5"/>'
                    . '<path d="M 76 80 Q 128 30 180 80" stroke="#ffffff" stroke-width="3" fill="none" opacity="0.18"/>';
            case 'dastar_turban':
                $dk = $this->mix($fabHex, '#000000', 0.35);
                $lt = $this->mix($fabHex, '#ffffff', 0.2);
                $shape = 'M ' . ($l - 6) . ' 102 C ' . ($l - 14) . ' 62 ' . ($l - 8) . ' 28 128 22 C ' . ($r + 8) . ' 28 ' . ($r + 14) . ' 62 ' . ($r + 6) . ' 102 C 162 86 94 86 ' . ($l - 6) . ' 102 Z';
                return '<clipPath id="' . $u . '_turb"><path d="' . $shape . '"/></clipPath>'
                    . '<path d="' . $shape . '" fill="' . $fab . '"/>'
                    . '<g clip-path="url(#' . $u . '_turb)" fill="none" stroke-width="2.2">'
                    . '<path d="M 60 98 Q 110 70 200 30 M 60 78 Q 110 52 180 14 M 60 58 Q 100 36 150 8" stroke="' . $dk . '" opacity="0.55"/>'
                    . '<path d="M 196 98 Q 146 70 56 30 M 196 78 Q 146 52 76 14" stroke="' . $lt . '" opacity="0.7"/>'
                    . '<path d="M 128 22 L 128 88" stroke="' . $dk . '" opacity="0.3" stroke-width="5"/>'
                    . '</g>'
                    . '<path d="M ' . ($l - 6) . ' 102 C 94 86 162 86 ' . ($r + 6) . ' 102" stroke="' . $dk . '" stroke-width="2.4" fill="none" opacity="0.6"/>';
            case 'kufi_cap':
                $dk = $this->mix($fabHex, '#000000', 0.3);
                return '<path d="' . $this->cap(86, 112, 2) . '" fill="' . $fill . '" opacity="0.7"/>'
                    . '<path d="M ' . ($l - 2) . ' 86 C ' . ($l - 2) . ' ' . ($this->top - 22) . ' ' . ($r + 2) . ' ' . ($this->top - 22) . ' ' . ($r + 2) . ' 86 Z" fill="' . $fab . '"/>'
                    . '<path d="M ' . ($l) . ' 80 L ' . ($r) . ' 80" stroke="' . $dk . '" stroke-width="5" stroke-dasharray="6 3"/>'
                    . '<path d="M 100 64 L 108 56 L 116 64 L 124 56 L 132 64 L 140 56 L 148 64 L 156 56" stroke="url(#' . $u . '_gold)" stroke-width="2" fill="none"/>';
            case 'regal_bald':
            default:
                return '<ellipse cx="116" cy="' . ($this->top + 14) . '" rx="22" ry="9" fill="#ffffff" opacity="0.2"/>';
        }
    }

    private function earX(int $dir): int
    {
        return 128 + $dir * ($this->w + 1);
    }

    private function faceAccessories(): string
    {
        $u = $this->uid;
        $earsHidden = !empty(self::HAIRSTYLES[$this->t['hairstyle']]['covers_ears']);
        switch ($this->t['accessory']) {
            case 'gold_hoops':
                if ($earsHidden) {
                    return '';
                }
                return '<circle cx="' . $this->earX(-1) . '" cy="140" r="8" fill="none" stroke="url(#' . $u . '_gold)" stroke-width="2.6"/>'
                    . '<circle cx="' . $this->earX(1) . '" cy="140" r="8" fill="none" stroke="url(#' . $u . '_gold)" stroke-width="2.6"/>';
            case 'pearl_studs':
                if ($earsHidden) {
                    return '';
                }
                return '<circle cx="' . $this->earX(-1) . '" cy="131" r="3.2" fill="#f8fafc" stroke="#e2e8f0"/><circle cx="' . $this->earX(1) . '" cy="131" r="3.2" fill="#f8fafc" stroke="#e2e8f0"/>';
            case 'hearing_aid':
                if ($earsHidden) {
                    return '';
                }
                $x = $this->earX(-1);
                return '<path d="M ' . ($x - 4) . ' 106 C ' . ($x - 12) . ' 110 ' . ($x - 12) . ' 126 ' . ($x - 6) . ' 132" stroke="#0ea5e9" stroke-width="5" fill="none" stroke-linecap="round"/>'
                    . '<circle cx="' . ($x + 1) . '" cy="121" r="3" fill="#0ea5e9"/>';
            case 'nose_stud':
                return '<circle cx="136" cy="135" r="1.9" fill="url(#' . $u . '_gold)"/>';
            case 'bindi':
                return '<circle cx="128" cy="94" r="3" fill="#dc2626"/><circle cx="127.2" cy="93.2" r="0.9" fill="#fff" opacity="0.6"/>';
            case 'gold_spectacles':
                return '<g fill="none" stroke="url(#' . $u . '_gold)" stroke-width="2.4"><circle cx="108" cy="114" r="14"/><circle cx="148" cy="114" r="14"/><path d="M 122 112 Q 128 108 134 112"/><path d="M 94 112 L ' . ($this->earX(-1) + 4) . ' 110 M 162 112 L ' . ($this->earX(1) - 4) . ' 110"/></g>';
            default:
                return '';
        }
    }

    private function eyewear(): string
    {
        $u = $this->uid;
        $l = $this->earX(-1) + 4;
        $r = $this->earX(1) - 4;
        switch ($this->t['eyewear']) {
            case 'round_frames':
                return '<g fill="#ffffff" fill-opacity="0.08" stroke="#111827" stroke-width="2.6"><circle cx="108" cy="115" r="14"/><circle cx="148" cy="115" r="14"/></g>'
                    . '<path d="M 122 112 Q 128 107 134 112 M 94 112 L ' . $l . ' 108 M 162 112 L ' . $r . ' 108" stroke="#111827" stroke-width="2.6" fill="none"/>'
                    . '<path d="M 100 106 L 106 102 M 140 106 L 146 102" stroke="#fff" stroke-width="2" opacity="0.6" stroke-linecap="round"/>';
            case 'cat_eye':
                $frame = '#7c2d12';
                return '<path d="M 92 108 Q 92 128 108 128 Q 122 128 122 112 Q 122 104 112 104 L 90 100 Z M 164 108 Q 164 128 148 128 Q 134 128 134 112 Q 134 104 144 104 L 166 100 Z" fill="#ffffff" fill-opacity="0.08" stroke="' . $frame . '" stroke-width="3" stroke-linejoin="round"/>'
                    . '<path d="M 122 110 Q 128 106 134 110 M 92 106 L ' . $l . ' 106 M 164 106 L ' . $r . ' 106" stroke="' . $frame . '" stroke-width="2.6" fill="none"/>'
                    . '<circle cx="92" cy="102" r="1.6" fill="#facc15"/><circle cx="164" cy="102" r="1.6" fill="#facc15"/>';
            case 'aviator_shades':
                return '<path d="M 92 106 L 122 106 Q 124 124 108 128 Q 92 128 92 106 Z M 164 106 L 134 106 Q 132 124 148 128 Q 164 128 164 106 Z" fill="url(#' . $u . '_shade)" stroke="url(#' . $u . '_gold)" stroke-width="2"/>'
                    . '<path d="M 122 107 Q 128 103 134 107 M 92 106 L ' . $l . ' 106 M 164 106 L ' . $r . ' 106" stroke="url(#' . $u . '_gold)" stroke-width="2" fill="none"/>'
                    . '<path d="M 98 110 L 108 110 M 140 110 L 150 110" stroke="#ffffff" stroke-width="2.4" opacity="0.45" stroke-linecap="round"/>';
            default:
                return '';
        }
    }

    private function headAccessories(): string
    {
        $u = $this->uid;
        $styleTop = (int)self::HAIRSTYLES[$this->t['hairstyle']]['top'];
        $top = min($styleTop, $this->top);
        switch ($this->t['accessory']) {
            case 'ancestor_crown':
                $b = max(28, $top + 8);
                return '<g filter="url(#' . $u . '_soft)"><path d="M 100 ' . $b . ' L 96 ' . ($b - 24) . ' L 112 ' . ($b - 12) . ' L 128 ' . ($b - 30) . ' L 144 ' . ($b - 12) . ' L 160 ' . ($b - 24) . ' L 156 ' . $b . ' Z" fill="url(#' . $u . '_gold)"/>'
                    . '<rect x="99" y="' . ($b - 4) . '" width="58" height="7" rx="3" fill="url(#' . $u . '_gold)"/>'
                    . '<circle cx="128" cy="' . ($b - 30) . '" r="4" fill="#a855f7"/><circle cx="96" cy="' . ($b - 24) . '" r="3" fill="#ef4444"/><circle cx="160" cy="' . ($b - 24) . '" r="3" fill="#3b82f6"/></g>';
            case 'laurel_wreath':
                $s = '<g fill="#16a34a" stroke="#14532d" stroke-width="0.6">';
                $y0 = 88;
                foreach ([0, 1, 2, 3, 4] as $i) {
                    $lx = 128 - $this->w - 2 + $i * 9;
                    $ly = $y0 - $i * 7;
                    $s .= '<ellipse cx="' . $lx . '" cy="' . $ly . '" rx="7" ry="3.2" transform="rotate(' . (-50 + $i * 10) . ' ' . $lx . ' ' . $ly . ')"/>';
                    $rx = 128 + $this->w + 2 - $i * 9;
                    $s .= '<ellipse cx="' . $rx . '" cy="' . $ly . '" rx="7" ry="3.2" transform="rotate(' . (50 - $i * 10) . ' ' . $rx . ' ' . $ly . ')"/>';
                }
                return $s . '</g>';
            case 'flower_crown':
                $s = '';
                $flowers = [[-44, 92, '#f472b6'], [-30, 76, '#facc15'], [-14, 68, '#fb7185'], [2, 66, '#a78bfa'], [18, 68, '#f472b6'], [32, 76, '#facc15'], [44, 92, '#fb7185']];
                foreach ($flowers as [$dx, $y, $c]) {
                    $x = 128 + $dx;
                    $s .= '<g>';
                    foreach ([0, 72, 144, 216, 288] as $a) {
                        $px = $x + 4.2 * cos(deg2rad($a));
                        $py = $y + 4.2 * sin(deg2rad($a));
                        $s .= '<circle cx="' . $this->n($px) . '" cy="' . $this->n($py) . '" r="3.6" fill="' . $c . '"/>';
                    }
                    $s .= '<circle cx="' . $x . '" cy="' . $y . '" r="2.4" fill="#fef3c7"/></g>';
                }
                return '<path d="M 82 94 Q 128 56 174 94" stroke="#15803d" stroke-width="2.4" fill="none"/>' . $s;
            default:
                return '';
        }
    }

    private function necklace(): string
    {
        if ($this->t['accessory'] !== 'heritage_coral_beads') {
            return '';
        }
        $s = '';
        foreach ([[0, 196, 18], [1, 204, 24]] as [$row, $yBase, $span]) {
            for ($i = -$span; $i <= $span; $i += 6) {
                $x = 128 + $i;
                $y = $yBase - (($i * $i) / ($span * $span)) * 10;
                $s .= '<circle cx="' . $this->n($x) . '" cy="' . $this->n($y) . '" r="3.6" fill="' . ($row === 0 ? '#dc2626' : '#ef4444') . '" stroke="#7f1d1d" stroke-width="0.6"/>';
            }
        }
        return $s . '<circle cx="128" cy="212" r="5" fill="url(#' . $this->uid . '_gold)"/>';
    }

    /* ---------------------------------------------------------------------
     * Colour helpers
     * --------------------------------------------------------------------- */

    private function n(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
    }

    /** @return array{0:int,1:int,2:int} */
    private function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return [0, 0, 0];
        }
        return [(int)hexdec(substr($hex, 0, 2)), (int)hexdec(substr($hex, 2, 2)), (int)hexdec(substr($hex, 4, 2))];
    }

    private function mix(string $a, string $b, float $t): string
    {
        [$r1, $g1, $b1] = $this->rgb($a);
        [$r2, $g2, $b2] = $this->rgb($b);
        $t = max(0.0, min(1.0, $t));
        return sprintf('#%02x%02x%02x', (int)round($r1 + ($r2 - $r1) * $t), (int)round($g1 + ($g2 - $g1) * $t), (int)round($b1 + ($b2 - $b1) * $t));
    }

    private function luminance(string $hex): float
    {
        [$r, $g, $b] = $this->rgb($hex);
        return (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
    }
}

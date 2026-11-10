<?php

declare(strict_types=1);

namespace App\services;

use GdImage;
use RuntimeException;

/**
 * AtgFestiveCardRenderer — Couture Card Synthesis Engine (ATG Pod 4)
 *
 * Renders the designed card as real images with native GD + FreeType (£0 SaaS cost):
 *   - 'og'       1200x630  landscape — WhatsApp / iMessage / Facebook link preview
 *   - 'portrait' 1080x1350 portrait  — "Save image", native share-sheet file, family feed image
 *
 * Every archetype maps to a surface family so the shared image looks like the card the
 * sender designed: paper (classic / hollow / origami), velvet (spatial_3d), glass (translucent),
 * obsidian (plaque) and holo (hyper_reality). Drawn at 2x and downsampled for anti-aliasing.
 *
 * Authored by: Callum Wright & Damilola "Dami" Ogunleye (ATG Pod 4)
 */
final class AtgFestiveCardRenderer
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;
    private const SS = 2; // supersampling factor
    private const CACHE_VERSION = 'v2';

    /** @var array<string, array{0:int,1:int}> */
    private const VARIANTS = [
        'og' => [1200, 630],
        'portrait' => [1080, 1350],
    ];

    /**
     * Render the landscape card as PNG binary (kept for /greetings/i/{token}.png and v1 callers).
     *
     * @param array<string, mixed> $card
     * @return string Raw PNG binary
     */
    public static function renderPng(array $card): string
    {
        $cacheFile = self::cachePath($card, 'og', 'png');
        $cached = self::readCache($cacheFile);
        if ($cached !== null) {
            return $cached;
        }

        $img = self::compose($card, 'og');
        ob_start();
        imagepng($img, null, 7);
        $png = (string)ob_get_clean();
        imagedestroy($img);

        self::writeCache($cacheFile, $png);
        return $png;
    }

    /**
     * Render a variant as progressive JPEG (small enough for WhatsApp's preview limits).
     *
     * @param array<string, mixed> $card
     */
    public static function renderJpeg(array $card, string $variant = 'og'): string
    {
        $variant = isset(self::VARIANTS[$variant]) ? $variant : 'og';
        $cacheFile = self::cachePath($card, $variant, 'jpg');
        $cached = self::readCache($cacheFile);
        if ($cached !== null) {
            return $cached;
        }

        $img = self::compose($card, $variant);
        imageinterlace($img, true);
        ob_start();
        imagejpeg($img, null, $variant === 'og' ? 86 : 90);
        $jpg = (string)ob_get_clean();
        imagedestroy($img);

        self::writeCache($cacheFile, $jpg);
        return $jpg;
    }

    /**
     * Write the portrait render to the deploy-protected uploads dir so the family feed can show it.
     *
     * @param array<string, mixed> $card
     * @return string|null Public web path
     */
    public static function exportFeedImage(array $card): ?string
    {
        try {
            $token = preg_replace('/[^a-f0-9]/i', '', (string)($card['share_token'] ?? '')) ?? '';
            if ($token === '') {
                return null;
            }
            $dir = AtgFestiveCardService::projectRoot() . AtgFestiveCardService::HERO_WEB_DIR;
            if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
                return null;
            }
            $name = 'card_' . $token . '.jpg';
            if (@file_put_contents($dir . '/' . $name, self::renderJpeg($card, 'portrait')) === false) {
                return null;
            }
            return AtgFestiveCardService::HERO_WEB_DIR . '/' . $name;
        } catch (\Throwable $e) {
            error_log('[AtgFestiveCardRenderer] Feed export failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Render SVG vector markup for high-res browser scaling or vector fallback.
     *
     * @param array<string, mixed> $card
     * @return string SVG XML
     */
    public static function renderSvg(array $card): string
    {
        $theme = $card['theme_meta'] ?? AtgFestiveCardService::THEMES['royal_crimson'];
        $occasion = $card['occasion_meta'] ?? AtgFestiveCardService::OCCASIONS['christmas'];

        $e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $deep = $e($theme['deep_color'] ?? '#120409');
        $bg1 = $e($theme['primary_color'] ?? '#881337');
        $accent = $e($theme['accent_color'] ?? '#d4af37');
        $light = $e($theme['light_color'] ?? '#fef08a');
        $headline = $e($card['headline'] ?? $occasion['default_headline']);
        $verse = $e($card['verse'] ?? $occasion['default_verse']);
        $sender = $e($card['sender_name'] ?? 'The Family');
        $signOff = $e($card['sign_off'] ?? 'With all our love,');
        $badge = $e(mb_strtoupper((string)($card['eyebrow'] ?? $occasion['badge'] ?? 'Festive Greetings')));

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$deep}"/>
      <stop offset="55%" stop-color="{$bg1}"/>
      <stop offset="100%" stop-color="{$deep}"/>
    </linearGradient>
    <linearGradient id="metal" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$light}"/>
      <stop offset="40%" stop-color="{$accent}"/>
      <stop offset="70%" stop-color="{$light}"/>
      <stop offset="100%" stop-color="{$accent}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bgGrad)"/>
  <rect x="30" y="30" width="1140" height="570" fill="none" stroke="url(#metal)" stroke-width="3" rx="6"/>
  <rect x="42" y="42" width="1116" height="546" fill="none" stroke="{$accent}" stroke-opacity="0.5" stroke-width="1" rx="4"/>
  <text x="600" y="130" text-anchor="middle" font-family="Cinzel, Georgia, serif" font-size="20" fill="{$accent}" letter-spacing="6">{$badge}</text>
  <text x="600" y="230" text-anchor="middle" font-family="'Playfair Display', Georgia, serif" font-size="54" fill="#fbf6ea">{$headline}</text>
  <foreignObject x="200" y="270" width="800" height="170">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 26px; line-height: 1.5; color: #f1ece2; text-align:center;">{$verse}</div>
  </foreignObject>
  <text x="600" y="480" text-anchor="middle" font-family="'Cormorant Garamond', Georgia, serif" font-style="italic" font-size="24" fill="#e7dfcf">{$signOff}</text>
  <text x="600" y="535" text-anchor="middle" font-family="'Great Vibes', cursive" font-size="54" fill="url(#metal)">{$sender}</text>
</svg>
SVG;
    }

    // =====================================================================
    // Composition
    // =====================================================================

    /**
     * @param array<string, mixed> $card
     */
    private static function compose(array $card, string $variant): GdImage
    {
        if (!function_exists('imagettftext')) {
            throw new RuntimeException('GD FreeType support is required to render festive cards.');
        }

        [$w, $h] = self::VARIANTS[$variant];
        $S = self::SS;
        $W = $w * $S;
        $H = $h * $S;

        $img = imagecreatetruecolor($W, $H);
        if (!$img) {
            throw new RuntimeException('GD imagecreatetruecolor failed to initialise.');
        }
        imagealphablending($img, true);
        imagesavealpha($img, false);

        $t = self::palette($card);
        $surface = (string)(($card['template_style_meta']['surface'] ?? null) ?: self::surfaceFor((string)($card['template_style'] ?? 'classic')));
        $style = (string)($card['template_style'] ?? 'classic');

        self::paintSurface($img, $surface, $style, $t, $W, $H);

        if ($variant === 'og') {
            self::layoutLandscape($img, $card, $t, $surface, $style, $W, $H);
        } else {
            self::layoutPortrait($img, $card, $t, $surface, $style, $W, $H);
        }

        $out = imagecreatetruecolor($w, $h);
        imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $W, $H);
        imagedestroy($img);
        return $out;
    }

    /**
     * @param array<string, mixed> $card
     * @param array<string, mixed> $t
     */
    private static function layoutLandscape(GdImage $img, array $card, array $t, string $surface, string $style, int $W, int $H): void
    {
        $S = self::SS;
        self::paintFrame($img, $surface, $t, $W, $H);
        self::paintHero($img, $card, $t, $surface, $style, 78 * $S, 74 * $S, 430 * $S, 482 * $S);

        self::paintCopy($img, $card, $t, $surface, [
            'mid' => 844 * $S,
            'colW' => 540 * $S,
            'top' => 78 * $S,
            'bottom' => 566 * $S,
            'eyebrow' => 17,
            'rule' => 150,
            'head' => [[54, 2], [48, 2], [46, 3], [41, 3], [36, 3], [32, 3]],
            'recipient' => 36,
            'verse' => [[23, 4], [23, 3], [21, 3], [21, 2], [20, 2]],
            'signoff' => 21,
            'sender' => 46,
        ]);

        $brandCol = $surface !== 'paper' ? [214, 205, 188] : self::mix($t['ink'], [120, 110, 100], 0.4);
        self::textCentredTracked($img, self::font('cinzel'), 11 * $S, 844 * $S, 596 * $S, 'FAMILYPLATFORM  ·  TAP TO OPEN YOUR ENVELOPE', $brandCol, 0.22, 40);
    }

    /**
     * @param array<string, mixed> $card
     * @param array<string, mixed> $t
     */
    private static function layoutPortrait(GdImage $img, array $card, array $t, string $surface, string $style, int $W, int $H): void
    {
        $S = self::SS;
        $mid = (int)($W / 2);
        self::paintFrame($img, $surface, $t, $W, $H);

        $y = 112 * $S;
        $eyebrow = mb_strtoupper(self::str($card, 'eyebrow', 'Festive Greetings'));
        self::textCentredTracked($img, self::font('cinzel'), 22 * $S, $mid, $y, self::truncate($eyebrow, 36), $t['accent'], 0.32);
        self::ornamentRule($img, $mid, $y + 28 * $S, 180 * $S, $t['accent']);

        $hasPhoto = self::photoPath($card) !== null;
        $hw = ($hasPhoto ? 580 : 380) * $S;
        $hh = ($hasPhoto ? 520 : 380) * $S;
        $hy = 176 * $S;
        self::paintHero($img, $card, $t, $surface, $style, (int)($mid - $hw / 2), $hy, $hw, $hh);

        self::paintCopy($img, $card, $t, $surface, [
            'mid' => $mid,
            'colW' => 900 * $S,
            'top' => $hy + $hh + 18 * $S,
            'bottom' => 1268 * $S,
            'eyebrow' => 0,
            'rule' => 0,
            'head' => [[62, 1], [58, 2], [52, 2], [46, 2], [42, 3], [38, 3]],
            'recipient' => 44,
            'verse' => $hasPhoto ? [[28, 4], [27, 3], [25, 3], [25, 2], [24, 2]] : [[30, 7], [29, 6], [28, 5], [27, 4], [26, 3]],
            'signoff' => 26,
            'sender' => 58,
        ]);

        $brandCol = $surface !== 'paper' ? [214, 205, 188] : self::mix($t['ink'], [120, 110, 100], 0.4);
        self::textCentredTracked($img, self::font('cinzel'), 12 * $S, $mid, 1306 * $S, 'CRAFTED ON FAMILYPLATFORM', $brandCol, 0.3, 40);
    }

    /**
     * Measured flow layout for the copy block: tries progressively tighter type settings until
     * eyebrow + headline + recipient + verse + sign-off + sender fit the budget, then centres it.
     *
     * @param array<string, mixed> $card
     * @param array<string, mixed> $t
     * @param array<string, mixed> $o
     */
    private static function paintCopy(GdImage $img, array $card, array $t, string $surface, array $o): void
    {
        $S = self::SS;
        $dark = $surface !== 'paper';
        $ink = $dark ? [251, 246, 234] : $t['ink'];
        $soft = $dark ? [228, 220, 206] : self::mix($t['ink'], [90, 80, 70], 0.3);
        $mid = (int)$o['mid'];
        $colW = (int)$o['colW'];
        $budget = (int)$o['bottom'] - (int)$o['top'];

        $headline = self::str($card, 'headline', 'Season’s Greetings');
        $recipient = trim(self::str($card, 'recipient_name', ''));
        $verse = self::str($card, 'verse', '');
        $fHead = self::font('playfair');
        $fVerse = self::font('cormorant');

        $eyebrowH = $o['eyebrow'] > 0 ? (int)(($o['eyebrow'] + 46) * $S) : 0;
        $recH = $recipient !== '' ? (int)(($o['recipient'] * 1.75) * $S) : 0;
        $tailH = (int)(($o['signoff'] * 1.9 + $o['sender'] * 1.25) * $S);

        $plan = null;
        foreach ($o['head'] as [$hs, $hMax]) {
            $hpx = $hs * $S;
            $hLines = self::wrap($headline, $fHead, $hpx, $colW, 99);
            if (count($hLines) > $hMax) {
                continue;
            }
            $headH = (int)(count($hLines) * $hpx * 1.17);
            foreach ($o['verse'] as [$vs, $vMax]) {
                $vpx = $vs * $S;
                $vLines = self::wrap($verse, $fVerse, $vpx, (int)($colW * 0.94), $vMax);
                $verseH = (int)(count($vLines) * $vpx * 1.42 + 14 * $S);
                $total = $eyebrowH + $headH + $recH + $verseH + $tailH;
                if ($total <= $budget) {
                    $plan = compact('hLines', 'hpx', 'vLines', 'vpx', 'total');
                    break 2;
                }
            }
        }
        if ($plan === null) {
            [$hs] = end($o['head']);
            [$vs] = end($o['verse']);
            $hpx = $hs * $S;
            $vpx = $vs * $S;
            $hLines = self::wrap($headline, $fHead, $hpx, $colW, 3);
            $vLines = self::wrap($verse, $fVerse, $vpx, (int)($colW * 0.94), 1);
            $plan = ['hLines' => $hLines, 'hpx' => $hpx, 'vLines' => $vLines, 'vpx' => $vpx, 'total' => $budget];
        }

        $y = (int)$o['top'] + (int)max(0, ($budget - $plan['total']) / 2);

        if ($o['eyebrow'] > 0) {
            $y += (int)($o['eyebrow'] * $S * 1.1);
            $eyebrow = mb_strtoupper(self::str($card, 'eyebrow', 'Festive Greetings'));
            self::textCentredTracked($img, self::font('cinzel'), (int)($o['eyebrow'] * $S), $mid, $y, self::truncate($eyebrow, 40), $t['accent'], 0.32);
            self::ornamentRule($img, $mid, $y + 24 * $S, (int)($o['rule'] * $S), $t['accent']);
            $y += (int)(46 * $S - $o['eyebrow'] * $S * 0.1);
        }

        foreach ($plan['hLines'] as $line) {
            $y += (int)($plan['hpx'] * 1.17);
            if ($dark) {
                self::textCentred($img, $fHead, $plan['hpx'], $mid + 2 * $S, $y + 3 * $S, $line, [0, 0, 0], 85);
            }
            self::textCentred($img, $fHead, $plan['hpx'], $mid, $y, $line, $ink);
        }

        if ($recipient !== '') {
            $y += (int)($o['recipient'] * 1.75 * $S);
            self::metalText($img, self::font('script'), (int)($o['recipient'] * $S), $mid, $y, self::truncate('Dearest ' . $recipient . ',', 34), $t, true);
        }

        $y += 14 * $S;
        foreach ($plan['vLines'] as $line) {
            $y += (int)($plan['vpx'] * 1.42);
            self::textCentred($img, $fVerse, $plan['vpx'], $mid, $y, $line, $soft);
        }

        $y += (int)($o['signoff'] * 1.9 * $S);
        self::textCentred($img, self::font('cormorant_italic'), (int)($o['signoff'] * $S), $mid, $y, self::truncate(self::str($card, 'sign_off', 'With love,'), 48), $soft);
        $y += (int)($o['sender'] * 1.18 * $S);
        self::metalText($img, self::font('script'), (int)($o['sender'] * $S), $mid, $y, self::truncate(self::str($card, 'sender_name', 'The Family'), 30), $t, true);
    }

    // =====================================================================
    // Surfaces & frames
    // =====================================================================

    /**
     * @param array<string, mixed> $t
     */
    private static function paintSurface(GdImage $img, string $surface, string $style, array $t, int $W, int $H): void
    {
        switch ($surface) {
            case 'paper':
                $paperA = $t['paper'];
                $paperB = self::mix($t['paper'], [214, 200, 178], 0.28);
                self::blitGradient($img, 0, 0, $W, $H, [[0, $paperA], [0.55, self::mix($paperA, [255, 255, 255], 0.3)], [1, $paperB]], 120);
                self::paperGrain($img, $W, $H);
                if ($style === 'origami') {
                    // Folded planes: three facets with crease shading
                    $f = (int)($W / 3);
                    self::rectAlpha($img, 0, 0, $f, $H, [0, 0, 0], 120);
                    self::rectAlpha($img, $f * 2, 0, $W, $H, [255, 255, 255], 105);
                    self::rectAlpha($img, $f - 2, 0, $f + 2, $H, [0, 0, 0], 105);
                    self::rectAlpha($img, $f * 2 - 2, 0, $f * 2 + 2, $H, [255, 255, 255], 70);
                    // Folded corner flaps in theme colour
                    $c = $t['primary'];
                    $s = (int)(min($W, $H) * 0.2);
                    self::polyAlpha($img, [0, 0, $s, 0, 0, $s], $c, 0);
                    self::polyAlpha($img, [0, 0, $s, 0, (int)($s * 0.45), (int)($s * 0.45)], [255, 255, 255], 95);
                    self::polyAlpha($img, [$W, $H, $W - $s, $H, $W, $H - $s], $c, 0);
                    self::polyAlpha($img, [$W, $H, $W - $s, $H, $W - (int)($s * 0.45), $H - (int)($s * 0.45)], [0, 0, 0], 90);
                }
                if ($style === 'hollow') {
                    self::laceBorder($img, $t, $W, $H);
                }
                // Soft vignette
                self::radial($img, (int)($W / 2), (int)($H / 2), (int)(max($W, $H) * 1.1), [255, 255, 255], 60, true);
                break;

            case 'obsidian':
                self::blitGradient($img, 0, 0, $W, $H, [[0, [12, 12, 14]], [0.5, [26, 25, 29]], [1, [6, 6, 7]]], 135);
                self::marbleVeins($img, $W, $H);
                // Polished reflection sweep
                self::blitGradient($img, 0, 0, $W, $H, [[0, [255, 255, 255, 127]], [0.42, [255, 255, 255, 127]], [0.5, [255, 255, 255, 112]], [0.58, [255, 255, 255, 127]], [1, [255, 255, 255, 127]]], 120, true);
                break;

            case 'holo':
                self::blitGradient($img, 0, 0, $W, $H, [[0, $t['deep']], [0.5, self::mix($t['primary'], [20, 16, 40], 0.4)], [1, $t['deep']]], 135);
                self::holoFoil($img, $W, $H);
                break;

            case 'glass':
                self::blitGradient($img, 0, 0, $W, $H, [[0, $t['deep']], [1, self::mix($t['deep'], $t['primary'], 0.5)]], 135);
                $m = max($W, $H);
                self::radial($img, (int)($W * 0.18), (int)($H * 0.2), (int)($m * 0.75), $t['primary'], 20);
                self::radial($img, (int)($W * 0.85), (int)($H * 0.3), (int)($m * 0.6), $t['accent'], 40);
                self::radial($img, (int)($W * 0.6), (int)($H * 0.95), (int)($m * 0.7), self::mix($t['primary'], [120, 160, 255], 0.35), 30);
                // Frosted crystal pane
                $p = (int)(min($W, $H) * 0.045);
                self::roundRectAlpha($img, $p, $p, $W - $p, $H - $p, (int)($p * 0.9), [255, 255, 255], 112);
                self::roundRectStroke($img, $p, $p, $W - $p, $H - $p, (int)($p * 0.9), [255, 255, 255], 80, 2 * self::SS);
                self::rectAlpha($img, $p + $p, $p + 1, $W - 2 * $p, $p + 2 * self::SS, [255, 255, 255], 50);
                break;

            case 'velvet':
            default:
                self::blitGradient($img, 0, 0, $W, $H, [[0, $t['deep']], [0.55, $t['primary']], [1, $t['deep']]], 125);
                $m = max($W, $H);
                self::radial($img, (int)($W * 0.3), (int)($H * 0.4), (int)($m * 0.8), $t['accent'], 88);
                self::bokeh($img, $t, $W, $H);
                self::radial($img, (int)($W / 2), (int)($H / 2), (int)($m * 1.25), [0, 0, 0], 40, true);
                break;
        }
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function paintFrame(GdImage $img, string $surface, array $t, int $W, int $H): void
    {
        $S = self::SS;
        if ($surface === 'obsidian') {
            // Bevelled brass frame with rivets
            $b = 22 * $S;
            self::metalBand($img, 0, 0, $W, $b, $t, 0);
            self::metalBand($img, 0, $H - $b, $W, $H, $t, 0.5);
            self::metalBand($img, 0, 0, $b, $H, $t, 0.25);
            self::metalBand($img, $W - $b, 0, $W, $H, $t, 0.75);
            self::rectAlpha($img, 0, 0, $W, 2 * $S, [255, 255, 255], 60);
            self::rectAlpha($img, $b - 3 * $S, $b - 3 * $S, $W - $b + 3 * $S, $b, [0, 0, 0], 40);
            self::rectAlpha($img, $b - 3 * $S, $H - $b, $W - $b + 3 * $S, $H - $b + 3 * $S, [255, 255, 255], 90);
            $inset = $b + 16 * $S;
            self::strokeRect($img, $inset, $inset, $W - $inset, $H - $inset, $t['accent'], 70, 2 * $S);
            foreach ([[$b / 2, $b / 2], [$W - $b / 2, $b / 2], [$b / 2, $H - $b / 2], [$W - $b / 2, $H - $b / 2]] as [$rx, $ry]) {
                self::rivet($img, (int)$rx, (int)$ry, (int)(8 * $S), $t);
            }
            return;
        }

        $o = ($surface === 'glass' ? 46 : 30) * $S;
        if ($surface === 'paper') {
            // Gold-foil double rule + corner flourishes
            self::metalStroke($img, $o, $o, $W - $o, $H - $o, $t, 4 * $S);
            self::strokeRect($img, $o + 12 * $S, $o + 12 * $S, $W - $o - 12 * $S, $H - $o - 12 * $S, $t['accent'], 55, 1 * $S);
            foreach ([[$o, $o, 1, 1], [$W - $o, $o, -1, 1], [$o, $H - $o, 1, -1], [$W - $o, $H - $o, -1, -1]] as [$x, $y, $dx, $dy]) {
                self::cornerFlourish($img, (int)$x, (int)$y, $dx, $dy, $t);
            }
            return;
        }

        if ($surface === 'holo') {
            self::rainbowStroke($img, $o, $o, $W - $o, $H - $o, 3 * $S);
            self::strokeRect($img, $o + 10 * $S, $o + 10 * $S, $W - $o - 10 * $S, $H - $o - 10 * $S, [255, 255, 255], 95, 1 * $S);
            return;
        }

        if ($surface === 'glass') {
            return; // the pane itself is the frame
        }

        self::metalStroke($img, $o, $o, $W - $o, $H - $o, $t, 3 * $S);
        self::strokeRect($img, $o + 12 * $S, $o + 12 * $S, $W - $o - 12 * $S, $H - $o - 12 * $S, $t['accent'], 75, 1 * $S);
        foreach ([[$o, $o], [$W - $o, $o], [$o, $H - $o], [$W - $o, $H - $o]] as [$x, $y]) {
            self::diamond($img, (int)$x, (int)$y, 9 * $S, $t['accent']);
        }
    }

    /**
     * Hero photo (style-specific frame) or monogram medallion, plus wax seal.
     *
     * @param array<string, mixed> $card
     * @param array<string, mixed> $t
     */
    private static function paintHero(GdImage $img, array $card, array $t, string $surface, string $style, int $x, int $y, int $w, int $h): void
    {
        $S = self::SS;
        $path = self::photoPath($card);
        $shape = match ($style) {
            'classic', 'spatial_3d', 'hollow' => 'arch',
            'plaque' => 'ellipse',
            'origami' => 'rect',
            default => 'round',
        };

        if ($path === null) {
            // Monogram medallion
            $d = min($w, $h);
            $cx = (int)($x + $w / 2);
            $cy = (int)($y + $h / 2);
            self::softShadow($img, $cx - (int)($d / 2), $cy - (int)($d / 2) + 14 * $S, $d, $d, 'ellipse', 70);
            $ring = self::metalLayer($d, $d, $t);
            self::blitShape($img, $ring, $cx - (int)($d / 2), $cy - (int)($d / 2), 'ellipse');
            imagedestroy($ring);
            $inner = (int)($d * 0.88);
            $fill = $surface === 'paper' ? $t['primary'] : $t['deep'];
            $disc = self::gradientLayer($inner, $inner, [[0, self::mix($fill, [255, 255, 255], 0.12)], [1, self::mix($fill, [0, 0, 0], 0.35)]], 135);
            self::blitShape($img, $disc, $cx - (int)($inner / 2), $cy - (int)($inner / 2), 'ellipse');
            imagedestroy($disc);
            self::ellipseStroke($img, $cx, $cy, (int)($d * 0.8), $t['accent'], 60, 2 * $S);
            $mono = self::str($card, 'monogram', 'F');
            $size = (int)($d * (mb_strlen($mono) > 1 ? 0.3 : 0.4));
            $bb = imagettfbbox($size, 0, self::font('cinzel'), $mono);
            $th = abs($bb[5] - $bb[1]);
            self::metalText($img, self::font('cinzel'), $size, $cx, (int)($cy + $th / 2), $mono, $t, true);
            return;
        }

        // Drop shadow + metal (or paper) frame
        $pad = ($shape === 'rect' ? 16 : 10) * $S;
        self::softShadow($img, $x, $y + 16 * $S, $w, $h, $shape, 62);
        if ($shape === 'rect') {
            self::rectAlpha($img, $x, $y, $x + $w, $y + $h, [255, 253, 248], 0);
        } elseif ($surface === 'holo') {
            $rb = self::rainbowLayer($w, $h);
            self::blitShape($img, $rb, $x, $y, $shape);
            imagedestroy($rb);
        } elseif ($surface === 'glass') {
            self::shapeAlpha($img, $x, $y, $w, $h, $shape, [255, 255, 255], 70);
        } else {
            $frame = self::metalLayer($w, $h, $t);
            self::blitShape($img, $frame, $x, $y, $shape);
            imagedestroy($frame);
        }

        $px = $x + $pad;
        $py = $y + $pad;
        $pw = $w - 2 * $pad;
        $ph = $h - 2 * $pad - ($shape === 'rect' ? 40 * $S : 0); // polaroid lip
        $photo = self::coverPhoto($path, $pw, $ph);
        if ($photo) {
            self::blitShape($img, $photo, $px, $py, $shape);
            imagedestroy($photo);
        }
        // Inner hairline
        if ($shape === 'ellipse') {
            self::ellipseStroke($img, (int)($px + $pw / 2), (int)($py + $ph / 2), $pw, [255, 255, 255], 90, 1 * $S, $ph);
        }

        // Wax seal on heritage, spatial and origami styles
        if (in_array($style, ['classic', 'spatial_3d', 'origami', 'hollow'], true)) {
            $sd = (int)(min($w, $h) * 0.24);
            self::waxSeal($img, $x + $w - (int)($sd * 0.35), $y + $h - (int)($sd * 0.4), $sd, $t, self::str($card, 'monogram', 'F'));
        }
    }

    // =====================================================================
    // Decorative primitives
    // =====================================================================

    /**
     * @param array<string, mixed> $t
     */
    private static function waxSeal(GdImage $img, int $cx, int $cy, int $d, array $t, string $mono): void
    {
        $seal = $t['seal'];
        self::softShadow($img, $cx - (int)($d / 2), $cy - (int)($d / 2) + (int)($d * 0.08), $d, $d, 'ellipse', 55);
        // Irregular poured-wax edge
        $dark = self::mix($seal, [0, 0, 0], 0.35);
        for ($i = 0; $i < 14; $i++) {
            $a = $i / 14 * M_PI * 2;
            $r = $d * 0.43;
            $bd = (int)($d * (0.18 + 0.05 * sin($i * 2.3)));
            imagefilledellipse($img, (int)($cx + cos($a) * $r), (int)($cy + sin($a) * $r), $bd, $bd, self::col($img, $dark));
        }
        $steps = 18;
        for ($i = 0; $i < $steps; $i++) {
            $k = $i / $steps;
            $c = self::mix($dark, self::mix($seal, [255, 255, 255], 0.28), $k);
            $dd = (int)($d * (0.92 - 0.55 * $k));
            imagefilledellipse($img, (int)($cx - $d * 0.06 * $k), (int)($cy - $d * 0.06 * $k), $dd, $dd, self::col($img, $c));
        }
        self::ellipseStroke($img, $cx, $cy, (int)($d * 0.64), self::mix($seal, [0, 0, 0], 0.4), 30, max(2, (int)($d * 0.025)));
        $size = (int)($d * (mb_strlen($mono) > 1 ? 0.2 : 0.27));
        $bb = imagettfbbox($size, 0, self::font('cinzel'), $mono);
        $th = abs($bb[5] - $bb[1]);
        self::textCentred($img, self::font('cinzel'), $size, $cx + 1, (int)($cy + $th / 2) + 2, $mono, self::mix($seal, [0, 0, 0], 0.5), 40);
        self::metalText($img, self::font('cinzel'), $size, $cx, (int)($cy + $th / 2), $mono, $t, true);
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function laceBorder(GdImage $img, array $t, int $W, int $H): void
    {
        // Laser-cut holes revealing the foil underlay, with an inner shadow lip
        $S = self::SS;
        $r = 7 * $S;
        $gap = 30 * $S;
        $inset = 62 * $S;
        $holes = [];
        for ($x = $inset; $x <= $W - $inset; $x += $gap) {
            $holes[] = [$x, $inset];
            $holes[] = [$x, $H - $inset];
        }
        for ($y = $inset + $gap; $y < $H - $inset; $y += $gap) {
            $holes[] = [$inset, $y];
            $holes[] = [$W - $inset, $y];
        }
        foreach ($holes as [$hx, $hy]) {
            imagefilledellipse($img, (int)$hx, (int)$hy + $S, 2 * $r + 2 * $S, 2 * $r + 2 * $S, self::col($img, [0, 0, 0], 95));
            imagefilledellipse($img, (int)$hx, (int)$hy, 2 * $r, 2 * $r, self::col($img, self::mix($t['primary'], $t['accent'], 0.25)));
            imagefilledellipse($img, (int)$hx - (int)($r * 0.25), (int)$hy - (int)($r * 0.25), $r, $r, self::col($img, self::mix($t['accent'], [255, 255, 255], 0.3), 70));
        }
    }

    private static function paperGrain(GdImage $img, int $W, int $H): void
    {
        $tile = imagecreatetruecolor(160, 160);
        imagealphablending($tile, false);
        imagesavealpha($tile, true);
        imagefill($tile, 0, 0, imagecolorallocatealpha($tile, 0, 0, 0, 127));
        mt_srand(42);
        for ($i = 0; $i < 1400; $i++) {
            $v = mt_rand(0, 1) ? 255 : 90;
            imagesetpixel($tile, mt_rand(0, 159), mt_rand(0, 159), imagecolorallocatealpha($tile, $v, $v - 10, $v - 25, mt_rand(108, 122)));
        }
        mt_srand();
        imagesettile($img, $tile);
        imagefilledrectangle($img, 0, 0, $W, $H, IMG_COLOR_TILED);
        imagedestroy($tile);
    }

    private static function marbleVeins(GdImage $img, int $W, int $H): void
    {
        $lw = (int)($W / 4);
        $lh = (int)($H / 4);
        $layer = imagecreatetruecolor($lw, $lh);
        imagefill($layer, 0, 0, imagecolorallocate($layer, 0, 0, 0));
        mt_srand(7);
        for ($v = 0; $v < 14; $v++) {
            $x = mt_rand(-50, $lw);
            $y = mt_rand(0, $lh);
            $ang = mt_rand(-40, 40) / 100;
            $shade = mt_rand(60, 150);
            imagesetthickness($layer, 1);
            for ($s = 0; $s < 60; $s++) {
                $ang += mt_rand(-14, 14) / 100;
                $nx = $x + cos($ang) * 9;
                $ny = $y + sin($ang) * 9;
                imageline($layer, (int)$x, (int)$y, (int)$nx, (int)$ny, imagecolorallocate($layer, $shade, $shade, $shade + 6));
                $x = $nx;
                $y = $ny;
            }
        }
        mt_srand();
        for ($i = 0; $i < 3; $i++) {
            imagefilter($layer, IMG_FILTER_GAUSSIAN_BLUR);
        }
        // Screen-ish blend: use the vein brightness as white alpha
        $big = imagecreatetruecolor($W, $H);
        imagecopyresampled($big, $layer, 0, 0, 0, 0, $W, $H, $lw, $lh);
        imagedestroy($layer);
        $over = imagecreatetruecolor($W, $H);
        imagealphablending($over, false);
        imagesavealpha($over, true);
        $step = 2;
        for ($yy = 0; $yy < $H; $yy += $step) {
            for ($xx = 0; $xx < $W; $xx += $step) {
                $lum = imagecolorat($big, $xx, $yy) & 0xFF;
                $a = 127 - (int)min(48, $lum * 0.4);
                imagefilledrectangle($over, $xx, $yy, $xx + $step - 1, $yy + $step - 1, imagecolorallocatealpha($over, 235, 232, 226, $a));
            }
        }
        imagedestroy($big);
        imagealphablending($img, true);
        imagecopy($img, $over, 0, 0, 0, 0, $W, $H);
        imagedestroy($over);
    }

    private static function holoFoil(GdImage $img, int $W, int $H): void
    {
        $lw = 96;
        $lh = (int)max(2, round(96 * $H / $W));
        $layer = imagecreatetruecolor($lw, $lh);
        imagealphablending($layer, false);
        imagesavealpha($layer, true);
        for ($y = 0; $y < $lh; $y++) {
            for ($x = 0; $x < $lw; $x++) {
                $u = ($x / $lw) * 0.8 + ($y / $lh) * 0.6;
                $hue = fmod($u * 540 + 20 * sin($y / 6), 360);
                [$r, $g, $b] = self::hsl($hue, 0.7, 0.74);
                $band = 0.5 + 0.5 * sin($u * 11);
                $a = (int)(127 - 18 - 26 * $band);
                imagesetpixel($layer, $x, $y, imagecolorallocatealpha($layer, $r, $g, $b, max(0, $a)));
            }
        }
        imagecopyresampled($img, $layer, 0, 0, 0, 0, $W, $H, $lw, $lh);
        imagedestroy($layer);
        // Glitter
        mt_srand(11);
        for ($i = 0; $i < (int)($W * $H / 2200); $i++) {
            $s = mt_rand(1, 3) * self::SS;
            imagefilledellipse($img, mt_rand(0, $W), mt_rand(0, $H), $s, $s, self::col($img, [255, 255, 255], mt_rand(40, 100)));
        }
        mt_srand();
        // Specular sweep
        self::radial($img, (int)($W * 0.7), (int)($H * 0.25), (int)(max($W, $H) * 0.7), [255, 255, 255], 88);
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function bokeh(GdImage $img, array $t, int $W, int $H): void
    {
        mt_srand(23);
        for ($i = 0; $i < 26; $i++) {
            $d = mt_rand(8, 46) * self::SS;
            self::radial($img, mt_rand(0, $W), mt_rand(0, $H), $d, $t['light'], mt_rand(55, 95));
        }
        mt_srand();
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function cornerFlourish(GdImage $img, int $x, int $y, int $dx, int $dy, array $t): void
    {
        $S = self::SS;
        $c = self::col($img, $t['accent']);
        imagesetthickness($img, 2 * $S);
        $len = 54 * $S;
        imageline($img, $x + $dx * 18 * $S, $y + $dy * 18 * $S, $x + $dx * $len, $y + $dy * 18 * $S, $c);
        imageline($img, $x + $dx * 18 * $S, $y + $dy * 18 * $S, $x + $dx * 18 * $S, $y + $dy * $len, $c);
        imagesetthickness($img, 1);
        self::diamond($img, $x + $dx * 18 * $S, $y + $dy * 18 * $S, 8 * $S, $t['accent']);
        imagefilledellipse($img, $x + $dx * ($len + 6 * $S), $y + $dy * 18 * $S, 5 * $S, 5 * $S, $c);
        imagefilledellipse($img, $x + $dx * 18 * $S, $y + $dy * ($len + 6 * $S), 5 * $S, 5 * $S, $c);
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function ornamentRule(GdImage $img, int $cx, int $y, int $half, array $rgb): void
    {
        $S = self::SS;
        imagesetthickness($img, 1 * $S);
        $c = self::col($img, $rgb, 30);
        imageline($img, $cx - $half, $y, $cx - 14 * $S, $y, $c);
        imageline($img, $cx + 14 * $S, $y, $cx + $half, $y, $c);
        imagesetthickness($img, 1);
        self::diamond($img, $cx, $y, 7 * $S, $rgb);
        imagefilledellipse($img, $cx - $half, $y, 4 * $S, 4 * $S, self::col($img, $rgb));
        imagefilledellipse($img, $cx + $half, $y, 4 * $S, 4 * $S, self::col($img, $rgb));
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function diamond(GdImage $img, int $cx, int $cy, int $r, array $rgb): void
    {
        imagefilledpolygon($img, [$cx, $cy - $r, $cx + $r, $cy, $cx, $cy + $r, $cx - $r, $cy], self::col($img, $rgb));
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function rivet(GdImage $img, int $cx, int $cy, int $r, array $t): void
    {
        imagefilledellipse($img, $cx, $cy + 2, 2 * $r + 4, 2 * $r + 4, self::col($img, [0, 0, 0], 70));
        for ($i = 0; $i < 10; $i++) {
            $k = $i / 10;
            $c = self::mix(self::mix($t['accent'], [0, 0, 0], 0.45), $t['light'], $k);
            $d = (int)(2 * $r * (1 - 0.6 * $k));
            imagefilledellipse($img, (int)($cx - $r * 0.25 * $k), (int)($cy - $r * 0.25 * $k), $d, $d, self::col($img, $c));
        }
        imagesetthickness($img, max(1, (int)($r / 5)));
        imageline($img, $cx - (int)($r * 0.6), $cy, $cx + (int)($r * 0.6), $cy, self::col($img, self::mix($t['accent'], [0, 0, 0], 0.55)));
        imagesetthickness($img, 1);
    }

    // =====================================================================
    // Metal, gradients, shapes
    // =====================================================================

    /**
     * @param array<string, mixed> $t
     * @return array<int, array{0:float,1:array<int,int>}>
     */
    private static function metalStops(array $t): array
    {
        $a = $t['accent'];
        $l = $t['light'];
        $d = self::mix($a, [0, 0, 0], 0.42);
        return [[0, $l], [0.22, $a], [0.42, $d], [0.6, $l], [0.78, $a], [1, $d]];
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function metalLayer(int $w, int $h, array $t): GdImage
    {
        return self::gradientLayer($w, $h, self::metalStops($t), 125);
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function metalBand(GdImage $img, int $x0, int $y0, int $x1, int $y1, array $t, float $phase): void
    {
        $angle = (int)(($x1 - $x0) > ($y1 - $y0) ? 100 : 170) + (int)($phase * 360);
        self::blitGradient($img, $x0, $y0, $x1 - $x0, $y1 - $y0, self::metalStops($t), $angle % 360);
    }

    /**
     * @param array<string, mixed> $t
     */
    private static function metalStroke(GdImage $img, int $x0, int $y0, int $x1, int $y1, array $t, int $th): void
    {
        self::metalBand($img, $x0, $y0, $x1, $y0 + $th, $t, 0);
        self::metalBand($img, $x0, $y1 - $th, $x1, $y1, $t, 0.5);
        self::metalBand($img, $x0, $y0, $x0 + $th, $y1, $t, 0.2);
        self::metalBand($img, $x1 - $th, $y0, $x1, $y1, $t, 0.7);
    }

    private static function rainbowLayer(int $w, int $h): GdImage
    {
        $stops = [];
        for ($i = 0; $i <= 6; $i++) {
            $stops[] = [$i / 6, self::hsl(fmod($i * 60 + 300, 360), 0.85, 0.7)];
        }
        return self::gradientLayer($w, $h, $stops, 135);
    }

    private static function rainbowStroke(GdImage $img, int $x0, int $y0, int $x1, int $y1, int $th): void
    {
        foreach ([[$x0, $y0, $x1, $y0 + $th], [$x0, $y1 - $th, $x1, $y1], [$x0, $y0, $x0 + $th, $y1], [$x1 - $th, $y0, $x1, $y1]] as [$a, $b, $c, $d]) {
            $layer = self::rainbowLayer($c - $a, $d - $b);
            imagecopy($img, $layer, $a, $b, 0, 0, $c - $a, $d - $b);
            imagedestroy($layer);
        }
    }

    /**
     * Gradient layer (alpha-aware) built tiny and upscaled — smooth and fast.
     *
     * @param array<int, array{0:float|int,1:array<int,int>}> $stops colours as [r,g,b] or [r,g,b,alpha]
     */
    private static function gradientLayer(int $w, int $h, array $stops, int $angle, bool $alpha = false): GdImage
    {
        $w = max(1, $w);
        $h = max(1, $h);
        $sw = max(2, min($w, 128));
        $sh = max(2, min($h, 128));
        $small = imagecreatetruecolor($sw, $sh);
        imagealphablending($small, false);
        imagesavealpha($small, true);
        $rad = deg2rad($angle);
        $dx = sin($rad);
        $dy = -cos($rad);
        // Project corners to normalise
        $proj = [];
        foreach ([[0, 0], [1, 0], [0, 1], [1, 1]] as [$u, $v]) {
            $proj[] = $u * $dx * $w + $v * $dy * $h;
        }
        $min = min($proj);
        $range = max(1e-6, max($proj) - $min);
        for ($y = 0; $y < $sh; $y++) {
            for ($x = 0; $x < $sw; $x++) {
                $u = $x / ($sw - 1);
                $v = $y / ($sh - 1);
                $k = (($u * $dx * $w + $v * $dy * $h) - $min) / $range;
                $c = self::stopColour($stops, $k);
                imagesetpixel($small, $x, $y, imagecolorallocatealpha($small, $c[0], $c[1], $c[2], $alpha ? ($c[3] ?? 0) : 0));
            }
        }
        $big = imagecreatetruecolor($w, $h);
        imagealphablending($big, false);
        imagesavealpha($big, true);
        imagecopyresampled($big, $small, 0, 0, 0, 0, $w, $h, $sw, $sh);
        imagedestroy($small);
        return $big;
    }

    /**
     * @param array<int, array{0:float|int,1:array<int,int>}> $stops
     */
    private static function blitGradient(GdImage $img, int $x, int $y, int $w, int $h, array $stops, int $angle, bool $alpha = false): void
    {
        if ($w <= 0 || $h <= 0) {
            return;
        }
        $layer = self::gradientLayer($w, $h, $stops, $angle, $alpha);
        imagealphablending($img, true);
        imagecopy($img, $layer, $x, $y, 0, 0, $w, $h);
        imagedestroy($layer);
    }

    /**
     * @param array<int, array{0:float|int,1:array<int,int>}> $stops
     * @return array<int,int>
     */
    private static function stopColour(array $stops, float $k): array
    {
        $k = max(0.0, min(1.0, $k));
        for ($i = 0; $i < count($stops) - 1; $i++) {
            [$k0, $c0] = $stops[$i];
            [$k1, $c1] = $stops[$i + 1];
            if ($k >= $k0 && $k <= $k1) {
                $f = ($k1 - $k0) > 0 ? ($k - $k0) / ($k1 - $k0) : 0;
                $out = [];
                for ($j = 0; $j < max(count($c0), count($c1)); $j++) {
                    $a = $c0[$j] ?? ($j === 3 ? 0 : 0);
                    $b = $c1[$j] ?? ($j === 3 ? 0 : 0);
                    $out[] = (int)round($a + ($b - $a) * $f);
                }
                return $out;
            }
        }
        return $stops[count($stops) - 1][1];
    }

    /**
     * Soft radial glow (or vignette when $invert) using a tiny alpha map upscaled.
     *
     * @param array<int,int> $rgb
     */
    private static function radial(GdImage $img, int $cx, int $cy, int $d, array $rgb, int $minAlpha, bool $invert = false): void
    {
        $n = 96;
        $layer = imagecreatetruecolor($n, $n);
        imagealphablending($layer, false);
        imagesavealpha($layer, true);
        for ($y = 0; $y < $n; $y++) {
            for ($x = 0; $x < $n; $x++) {
                $dist = sqrt((($x - $n / 2) ** 2) + (($y - $n / 2) ** 2)) / ($n / 2);
                $f = max(0.0, 1 - $dist);
                $f = $f * $f * (3 - 2 * $f);
                if ($invert) {
                    $f = 1 - $f;
                }
                $a = (int)round(127 - (127 - $minAlpha) * $f);
                imagesetpixel($layer, $x, $y, imagecolorallocatealpha($layer, $rgb[0], $rgb[1], $rgb[2], max(0, min(127, $a))));
            }
        }
        imagealphablending($img, true);
        imagecopyresampled($img, $layer, $cx - (int)($d / 2), $cy - (int)($d / 2), 0, 0, $d, $d, $n, $n);
        imagedestroy($layer);
    }

    private static function softShadow(GdImage $img, int $x, int $y, int $w, int $h, string $shape, int $alpha): void
    {
        $k = 8;
        $pad = 6;
        $lw = max(4, (int)($w / $k) + 2 * $pad);
        $lh = max(4, (int)($h / $k) + 2 * $pad);
        $layer = imagecreatetruecolor($lw, $lh);
        imagefill($layer, 0, 0, imagecolorallocate($layer, 255, 255, 255));
        $black = imagecolorallocate($layer, 0, 0, 0);
        self::drawShape($layer, $pad, $pad, (int)($w / $k), (int)($h / $k), $shape, $black);
        for ($i = 0; $i < 4; $i++) {
            imagefilter($layer, IMG_FILTER_GAUSSIAN_BLUR);
        }
        $W = $lw * $k;
        $H = $lh * $k;
        $big = imagecreatetruecolor($W, $H);
        imagecopyresampled($big, $layer, 0, 0, 0, 0, $W, $H, $lw, $lh);
        imagedestroy($layer);
        $over = imagecreatetruecolor($W, $H);
        imagealphablending($over, false);
        imagesavealpha($over, true);
        $step = 4;
        for ($yy = 0; $yy < $H; $yy += $step) {
            for ($xx = 0; $xx < $W; $xx += $step) {
                $lum = imagecolorat($big, $xx, $yy) & 0xFF;
                $a = 127 - (int)((255 - $lum) / 255 * (127 - $alpha));
                imagefilledrectangle($over, $xx, $yy, $xx + $step - 1, $yy + $step - 1, imagecolorallocatealpha($over, 0, 0, 0, $a));
            }
        }
        imagedestroy($big);
        imagealphablending($img, true);
        imagecopy($img, $over, $x - $pad * $k, $y - $pad * $k, 0, 0, $W, $H);
        imagedestroy($over);
    }

    /** Draw a filled shape into an image in a single colour. */
    private static function drawShape(GdImage $img, int $x, int $y, int $w, int $h, string $shape, int $colour): void
    {
        switch ($shape) {
            case 'ellipse':
                imagefilledellipse($img, (int)($x + $w / 2), (int)($y + $h / 2), $w, $h, $colour);
                break;
            case 'arch':
                $r = (int)($w / 2);
                imagefilledrectangle($img, $x, $y + $r, $x + $w - 1, $y + $h - 1, $colour);
                imagefilledellipse($img, $x + $r, $y + $r, $w, $w, $colour);
                break;
            case 'round':
                $r = (int)(min($w, $h) * 0.08);
                imagefilledrectangle($img, $x + $r, $y, $x + $w - $r, $y + $h - 1, $colour);
                imagefilledrectangle($img, $x, $y + $r, $x + $w - 1, $y + $h - $r, $colour);
                foreach ([[$x + $r, $y + $r], [$x + $w - $r - 1, $y + $r], [$x + $r, $y + $h - $r - 1], [$x + $w - $r - 1, $y + $h - $r - 1]] as [$cx, $cy]) {
                    imagefilledellipse($img, (int)$cx, (int)$cy, 2 * $r, 2 * $r, $colour);
                }
                break;
            default:
                imagefilledrectangle($img, $x, $y, $x + $w - 1, $y + $h - 1, $colour);
        }
    }

    /** Copy $layer onto $img clipped to a shape (magenta-key mask: fast, no per-pixel PHP loop). */
    private static function blitShape(GdImage $img, GdImage $layer, int $x, int $y, string $shape): void
    {
        $w = imagesx($layer);
        $h = imagesy($layer);
        $work = imagecreatetruecolor($w, $h);
        imagecopy($work, $layer, 0, 0, 0, 0, $w, $h);
        $mask = imagecreatetruecolor($w, $h);
        $magenta = imagecolorallocate($mask, 255, 0, 255);
        $hole = imagecolorallocate($mask, 0, 255, 0);
        imagefill($mask, 0, 0, $magenta);
        self::drawShape($mask, 0, 0, $w, $h, $shape, $hole);
        imagecolortransparent($mask, $hole);
        imagecopy($work, $mask, 0, 0, 0, 0, $w, $h);
        imagedestroy($mask);
        imagecolortransparent($work, imagecolorallocate($work, 255, 0, 255));
        imagecopy($img, $work, $x, $y, 0, 0, $w, $h);
        imagedestroy($work);
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function shapeAlpha(GdImage $img, int $x, int $y, int $w, int $h, string $shape, array $rgb, int $alpha): void
    {
        imagealphablending($img, true);
        self::drawShape($img, $x, $y, $w, $h, $shape, self::col($img, $rgb, $alpha));
    }

    /** Cover-crop a photo into w x h. */
    private static function coverPhoto(string $path, int $w, int $h): ?GdImage
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }
        $src = match ($info['mime'] ?? '') {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
        if (!$src) {
            return null;
        }
        $sw = imagesx($src);
        $sh = imagesy($src);
        $scale = max($w / $sw, $h / $sh);
        $cw = (int)round($w / $scale);
        $ch = (int)round($h / $scale);
        $sx = (int)(($sw - $cw) / 2);
        $sy = (int)max(0, ($sh - $ch) * 0.35); // bias towards faces near the top
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $w, $h, $cw, $ch);
        imagedestroy($src);
        // Nudge exact key colours so masking never punches holes in the photo
        return $dst;
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function rectAlpha(GdImage $img, int $x0, int $y0, int $x1, int $y1, array $rgb, int $alpha): void
    {
        imagealphablending($img, true);
        imagefilledrectangle($img, $x0, $y0, $x1, $y1, self::col($img, $rgb, $alpha));
    }

    /**
     * @param array<int,int> $pts
     * @param array<int,int> $rgb
     */
    private static function polyAlpha(GdImage $img, array $pts, array $rgb, int $alpha): void
    {
        imagealphablending($img, true);
        imagefilledpolygon($img, $pts, self::col($img, $rgb, $alpha));
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function roundRectAlpha(GdImage $img, int $x0, int $y0, int $x1, int $y1, int $r, array $rgb, int $alpha): void
    {
        // Draw opaque into a key layer first so overlapping primitives don't double the alpha
        $w = $x1 - $x0;
        $h = $y1 - $y0;
        $layer = imagecreatetruecolor($w, $h);
        imagealphablending($layer, false);
        imagesavealpha($layer, true);
        imagefill($layer, 0, 0, imagecolorallocatealpha($layer, 0, 0, 0, 127));
        $c = imagecolorallocatealpha($layer, $rgb[0], $rgb[1], $rgb[2], $alpha);
        imagefilledrectangle($layer, $r, 0, $w - $r, $h - 1, $c);
        imagefilledrectangle($layer, 0, $r, $w - 1, $h - $r, $c);
        foreach ([[$r, $r], [$w - $r - 1, $r], [$r, $h - $r - 1], [$w - $r - 1, $h - $r - 1]] as [$cx, $cy]) {
            imagefilledellipse($layer, (int)$cx, (int)$cy, 2 * $r, 2 * $r, $c);
        }
        imagealphablending($img, true);
        imagecopy($img, $layer, $x0, $y0, 0, 0, $w, $h);
        imagedestroy($layer);
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function roundRectStroke(GdImage $img, int $x0, int $y0, int $x1, int $y1, int $r, array $rgb, int $alpha, int $th): void
    {
        $c = self::col($img, $rgb, $alpha);
        imagesetthickness($img, $th);
        imageline($img, $x0 + $r, $y0, $x1 - $r, $y0, $c);
        imageline($img, $x0 + $r, $y1, $x1 - $r, $y1, $c);
        imageline($img, $x0, $y0 + $r, $x0, $y1 - $r, $c);
        imageline($img, $x1, $y0 + $r, $x1, $y1 - $r, $c);
        imagearc($img, $x0 + $r, $y0 + $r, 2 * $r, 2 * $r, 180, 270, $c);
        imagearc($img, $x1 - $r, $y0 + $r, 2 * $r, 2 * $r, 270, 360, $c);
        imagearc($img, $x0 + $r, $y1 - $r, 2 * $r, 2 * $r, 90, 180, $c);
        imagearc($img, $x1 - $r, $y1 - $r, 2 * $r, 2 * $r, 0, 90, $c);
        imagesetthickness($img, 1);
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function strokeRect(GdImage $img, int $x0, int $y0, int $x1, int $y1, array $rgb, int $alpha, int $th): void
    {
        imagesetthickness($img, $th);
        imagerectangle($img, $x0, $y0, $x1, $y1, self::col($img, $rgb, $alpha));
        imagesetthickness($img, 1);
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function ellipseStroke(GdImage $img, int $cx, int $cy, int $w, array $rgb, int $alpha, int $th, ?int $h = null): void
    {
        $h ??= $w;
        $c = self::col($img, $rgb, $alpha);
        for ($i = 0; $i < $th; $i++) {
            imageellipse($img, $cx, $cy, $w - $i * 2, $h - $i * 2, $c);
        }
    }

    // =====================================================================
    // Typography
    // =====================================================================

    /**
     * @param array<int,int> $rgb
     */
    private static function textCentred(GdImage $img, string $font, int $size, int $cx, int $baseline, string $text, array $rgb, int $alpha = 0): void
    {
        $bb = imagettfbbox($size, 0, $font, $text);
        $w = $bb[2] - $bb[0];
        imagettftext($img, $size, 0, (int)($cx - $w / 2 - $bb[0]), $baseline, self::col($img, $rgb, $alpha), $font, $text);
    }

    /**
     * Letter-spaced small caps (Cinzel) — GD has no tracking, so place glyphs individually.
     *
     * @param array<int,int> $rgb
     */
    private static function textCentredTracked(GdImage $img, string $font, int $size, int $cx, int $baseline, string $text, array $rgb, float $tracking, int $alpha = 0): void
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $gap = (int)($size * $tracking);
        $widths = [];
        $total = 0;
        foreach ($chars as $ch) {
            $bb = imagettfbbox($size, 0, $font, $ch === ' ' ? 'n' : $ch);
            $cw = $ch === ' ' ? (int)(($bb[2] - $bb[0]) * 0.6) : ($bb[2] - $bb[0]);
            $widths[] = $cw;
            $total += $cw + $gap;
        }
        $total -= $gap;
        $x = (int)($cx - $total / 2);
        $c = self::col($img, $rgb, $alpha);
        foreach ($chars as $i => $ch) {
            if ($ch !== ' ') {
                imagettftext($img, $size, 0, $x, $baseline, $c, $font, $ch);
            }
            $x += $widths[$i] + $gap;
        }
    }

    /**
     * Gold-foil text: glyph alpha from FreeType, colour from the theme's metal gradient.
     *
     * @param array<string, mixed> $t
     */
    private static function metalText(GdImage $img, string $font, int $size, int $x, int $baseline, string $text, array $t, bool $centred = false): void
    {
        $bb = imagettfbbox($size, 0, $font, $text);
        $w = $bb[2] - $bb[0];
        $top = min($bb[5], $bb[7]);
        $bottom = max($bb[1], $bb[3]);
        $pad = (int)($size * 0.4);
        $lw = $w + 2 * $pad;
        $lh = ($bottom - $top) + 2 * $pad;
        if ($lw <= 0 || $lh <= 0) {
            return;
        }
        $left = $centred ? (int)($x - $w / 2) : $x;

        // Shadow for legibility / emboss
        imagettftext($img, $size, 0, $left - $bb[0] + 1, $baseline + max(2, (int)($size / 22)), self::col($img, [0, 0, 0], 88), $font, $text);

        $mask = imagecreatetruecolor($lw, $lh);
        imagefill($mask, 0, 0, imagecolorallocate($mask, 0, 0, 0));
        imagettftext($mask, $size, 0, $pad - $bb[0], $pad - $top, imagecolorallocate($mask, 255, 255, 255), $font, $text);

        $grad = self::gradientLayer($lw, $lh, self::metalStops($t), 160);
        $out = imagecreatetruecolor($lw, $lh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $clear = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $clear);
        for ($yy = 0; $yy < $lh; $yy++) {
            for ($xx = 0; $xx < $lw; $xx++) {
                $lum = imagecolorat($mask, $xx, $yy) & 0xFF;
                if ($lum === 0) {
                    continue;
                }
                $g = imagecolorat($grad, $xx, $yy);
                imagesetpixel($out, $xx, $yy, imagecolorallocatealpha($out, ($g >> 16) & 0xFF, ($g >> 8) & 0xFF, $g & 0xFF, 127 - (int)($lum / 2)));
            }
        }
        imagedestroy($mask);
        imagedestroy($grad);
        imagealphablending($img, true);
        imagecopy($img, $out, $left - $pad, $baseline + $top - $pad, 0, 0, $lw, $lh);
        imagedestroy($out);
    }

    /**
     * @param array<int,int> $sizes in px at 1x
     * @return array{0: array<int,string>, 1: int}
     */
    private static function fitLines(string $text, string $font, array $sizes, int $maxW, int $maxLines, int $S): array
    {
        foreach ($sizes as $s) {
            $px = $s * $S;
            $lines = self::wrap($text, $font, $px, $maxW, 99);
            if (count($lines) <= $maxLines) {
                return [$lines, $px];
            }
        }
        $px = end($sizes) * $S;
        return [self::wrap($text, $font, $px, $maxW, $maxLines), $px];
    }

    /**
     * Pixel-accurate word wrap with ellipsis on overflow.
     *
     * @return array<int,string>
     */
    private static function wrap(string $text, string $font, int $size, int $maxW, int $maxLines): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($text === '') {
            return [];
        }
        $words = explode(' ', $text);
        $lines = [];
        $line = '';
        foreach ($words as $word) {
            $try = $line === '' ? $word : $line . ' ' . $word;
            $bb = imagettfbbox($size, 0, $font, $try);
            if (($bb[2] - $bb[0]) > $maxW && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $try;
            }
        }
        if ($line !== '') {
            $lines[] = $line;
        }
        if (count($lines) > $maxLines) {
            $lines = array_slice($lines, 0, $maxLines);
            $last = $lines[$maxLines - 1];
            while ($last !== '') {
                $bb = imagettfbbox($size, 0, $font, $last . '…');
                if (($bb[2] - $bb[0]) <= $maxW) {
                    break;
                }
                $last = (string)preg_replace('/\s*\S+$/u', '', $last);
            }
            $lines[$maxLines - 1] = rtrim($last, ' ,.;:') . '…';
        }
        return $lines;
    }

    private static function font(string $key): string
    {
        static $resolved = [];
        if (isset($resolved[$key])) {
            return $resolved[$key];
        }
        $dir = AtgFestiveCardService::projectRoot() . '/resources/fonts/festive/';
        $mpdf = AtgFestiveCardService::projectRoot() . '/vendor/mpdf/mpdf/ttfonts/';
        $map = [
            'cinzel' => [$dir . 'Cinzel.ttf', $mpdf . 'DejaVuSerif.ttf'],
            'playfair' => [$dir . 'PlayfairDisplay.ttf', $mpdf . 'DejaVuSerif.ttf'],
            'cormorant' => [$dir . 'CormorantGaramond.ttf', $mpdf . 'DejaVuSerif.ttf'],
            'cormorant_italic' => [$dir . 'CormorantGaramond-Italic.ttf', $mpdf . 'DejaVuSerif-Italic.ttf'],
            'script' => [$dir . 'GreatVibes-Regular.ttf', $mpdf . 'DejaVuSerif-Italic.ttf'],
            'sans' => [$dir . 'Montserrat.ttf', $mpdf . 'DejaVuSans.ttf'],
        ];
        $candidates = array_merge($map[$key] ?? [], [
            $mpdf . 'DejaVuSans.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Supplemental/Georgia.ttf',
        ]);
        foreach ($candidates as $f) {
            if (is_file($f) && is_readable($f)) {
                return $resolved[$key] = $f;
            }
        }
        throw new RuntimeException('No TrueType font available for festive card rendering.');
    }

    // =====================================================================
    // Utilities
    // =====================================================================

    /**
     * @param array<string, mixed> $card
     * @return array<string, mixed>
     */
    private static function palette(array $card): array
    {
        $theme = is_array($card['theme_meta'] ?? null) ? $card['theme_meta'] : (AtgFestiveCardService::THEMES[(string)($card['theme'] ?? '')] ?? AtgFestiveCardService::THEMES['royal_crimson']);
        $p = self::hexToRgb((string)($theme['primary_color'] ?? '#7a0f2a'));
        return [
            'primary' => $p,
            'accent' => self::hexToRgb((string)($theme['accent_color'] ?? '#d4af37')),
            'deep' => self::hexToRgb((string)($theme['deep_color'] ?? '#120409')),
            'light' => self::hexToRgb((string)($theme['light_color'] ?? '#f6e7a8')),
            'paper' => self::hexToRgb((string)($theme['paper_color'] ?? '#fbf3e4')),
            'ink' => self::hexToRgb((string)($theme['ink_color'] ?? '#3a0a17')),
            'seal' => self::hexToRgb((string)($theme['seal_color'] ?? '#9f1239')),
        ];
    }

    private static function surfaceFor(string $style): string
    {
        return (string)(AtgFestiveCardService::TEMPLATE_STYLES[$style]['surface'] ?? 'paper');
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function photoPath(array $card): ?string
    {
        $url = (string)($card['hero_photo_url'] ?? '');
        return $url !== '' ? AtgFestiveCardService::assetPath($url) : null;
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function str(array $card, string $key, string $default): string
    {
        $v = $card[$key] ?? null;
        $v = is_scalar($v) ? html_entity_decode(trim((string)$v), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
        return $v !== '' ? $v : $default;
    }

    private static function truncate(string $text, int $max): string
    {
        return mb_strlen($text) <= $max ? $text : rtrim(mb_substr($text, 0, $max - 1)) . '…';
    }

    /**
     * @param array<int,int> $rgb
     */
    private static function col(GdImage $img, array $rgb, int $alpha = 0): int
    {
        return (int)imagecolorallocatealpha($img, max(0, min(255, $rgb[0])), max(0, min(255, $rgb[1])), max(0, min(255, $rgb[2])), max(0, min(127, $alpha)));
    }

    /**
     * @param array<int,int> $a
     * @param array<int,int> $b
     * @return array{0:int,1:int,2:int}
     */
    private static function mix(array $a, array $b, float $k): array
    {
        return [
            (int)round($a[0] + ($b[0] - $a[0]) * $k),
            (int)round($a[1] + ($b[1] - $a[1]) * $k),
            (int)round($a[2] + ($b[2] - $a[2]) * $k),
        ];
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function hsl(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };
        return [(int)(($r + $m) * 255), (int)(($g + $m) * 255), (int)(($b + $m) * 255)];
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return [136, 19, 55];
        }
        return [
            (int)hexdec(substr($hex, 0, 2)),
            (int)hexdec(substr($hex, 2, 2)),
            (int)hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * @param array<string, mixed> $card
     */
    private static function cachePath(array $card, string $variant, string $ext): string
    {
        $token = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($card['share_token'] ?? 'default')) ?: 'default';
        return AtgFestiveCardService::projectRoot() . '/storage/cache/festive_cards/' . $token . '_' . $variant . '_' . self::CACHE_VERSION . '.' . $ext;
    }

    private static function readCache(string $file): ?string
    {
        if (is_file($file) && filesize($file) > 1000) {
            $data = @file_get_contents($file);
            if (is_string($data) && $data !== '') {
                return $data;
            }
        }
        return null;
    }

    private static function writeCache(string $file, string $data): void
    {
        if ($data === '') {
            return;
        }
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($file, $data);
    }
}

<?php

declare(strict_types=1);

namespace App\services;

use RuntimeException;

/**
 * AtgFestiveCardRenderer — Sovereign Card Synthesis Engine (ATG Pod 4)
 *
 * Synthesizes high-definition 1200x630 OpenGraph social previews and cards
 * using native PHP GD / SVG rendering at £0 cloud SaaS cost.
 *
 * Authored by: Callum Wright & Damilola "Dami" Ogunleye (ATG Pod 4)
 */
final class AtgFestiveCardRenderer
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    /**
     * Render the festive card as PNG binary string with internal disk caching.
     *
     * @param array<string, mixed> $card
     * @return string Raw PNG binary
     */
    public static function renderPng(array $card): string
    {
        $token = (string)($card['share_token'] ?? 'default');
        $cacheDir = __DIR__ . '/../../public/resources/cache/festive_cards';
        $cacheFile = $cacheDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $token) . '.png';

        if (file_exists($cacheFile) && filesize($cacheFile) > 1000) {
            $cached = @file_get_contents($cacheFile);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $theme = $card['theme_meta'] ?? AtgFestiveCardService::THEMES['royal_crimson'];
        $occasion = $card['occasion_meta'] ?? AtgFestiveCardService::OCCASIONS['christmas'];

        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if (!$img) {
            throw new RuntimeException('GD imagecreatetruecolor failed to initialize.');
        }

        // Enable alpha blending
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Resolve theme colors
        $bgHex = (string)($theme['primary_color'] ?? '#881337');
        $accentHex = (string)($theme['accent_color'] ?? '#f59e0b');

        $bgRgb = self::hexToRgb($bgHex);
        $accentRgb = self::hexToRgb($accentHex);

        $bgColor = imagecolorallocate($img, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        $accentColor = imagecolorallocate($img, $accentRgb[0], $accentRgb[1], $accentRgb[2]);
        $goldLight = imagecolorallocate($img, 254, 240, 138);
        $white = imagecolorallocate($img, 255, 255, 255);
        $subtext = imagecolorallocate($img, 226, 232, 240);

        // Fill background
        imagefill($img, 0, 0, $bgColor);

        // Draw rich subtle vignette gradient bands
        for ($i = 0; $i < self::HEIGHT; $i += 4) {
            $ratio = $i / self::HEIGHT;
            $darkR = (int)max(0, $bgRgb[0] * (1 - $ratio * 0.45));
            $darkG = (int)max(0, $bgRgb[1] * (1 - $ratio * 0.45));
            $darkB = (int)max(0, $bgRgb[2] * (1 - $ratio * 0.45));
            $bandColor = imagecolorallocate($img, $darkR, $darkG, $darkB);
            imagefilledrectangle($img, 0, $i, self::WIDTH, $i + 4, $bandColor);
        }

        // Draw luxury decorative double border
        imagesetthickness($img, 3);
        imagerectangle($img, 30, 30, self::WIDTH - 30, self::HEIGHT - 30, $accentColor);
        imagesetthickness($img, 1);
        imagerectangle($img, 42, 42, self::WIDTH - 42, self::HEIGHT - 42, $goldLight);

        // Draw ornate corner accents (filigree squares/diamonds)
        self::drawCornerDiamonds($img, 36, $accentColor);

        // Draw festive badge & family name
        $headline = htmlspecialchars_decode((string)($card['headline'] ?? $occasion['default_headline']), ENT_QUOTES);
        $verse = htmlspecialchars_decode((string)($card['verse'] ?? $occasion['default_verse']), ENT_QUOTES);
        $sender = htmlspecialchars_decode((string)($card['sender_name'] ?? 'The Family'), ENT_QUOTES);
        $signOff = htmlspecialchars_decode((string)($card['sign_off'] ?? 'With all our love,'), ENT_QUOTES);
        $badgeText = strtoupper((string)($occasion['badge'] ?? 'FESTIVE GREETINGS'));

        $fontFile = self::resolveTtfFont();

        if ($fontFile && function_exists('imagettftext')) {
            // Badge text top center
            imagettftext($img, 15, 0, 100, 105, $goldLight, $fontFile, "✦ {$badgeText} ✦");

            // Headline
            imagettftext($img, 34, 0, 100, 185, $white, $fontFile, self::truncateString($headline, 48));

            // Wrapped Verse
            $wrappedVerse = self::wrapText($verse, 62);
            $y = 265;
            foreach (explode("\n", $wrappedVerse) as $line) {
                if ($y > 450) break;
                imagettftext($img, 20, 0, 100, $y, $subtext, $fontFile, $line);
                $y += 38;
            }

            // Sign-off & Sender
            imagettftext($img, 18, 0, 100, 520, $accentColor, $fontFile, $signOff);
            imagettftext($img, 24, 0, 100, 560, $goldLight, $fontFile, $sender);

            // Watermark bottom right
            imagettftext($img, 14, 0, self::WIDTH - 380, 560, $accentColor, $fontFile, 'FAMILYPLATFORM.COM');
        } else {
            // High-fidelity fallback using standard built-in GD fonts
            imagestring($img, 4, 100, 90, "✦ {$badgeText} ✦", $goldLight);
            imagestring($img, 5, 100, 150, self::truncateString($headline, 55), $white);

            $wrappedVerse = self::wrapText($verse, 65);
            $y = 220;
            foreach (explode("\n", $wrappedVerse) as $line) {
                if ($y > 420) break;
                imagestring($img, 4, 100, $y, $line, $subtext);
                $y += 28;
            }

            imagestring($img, 4, 100, 480, $signOff, $accentColor);
            imagestring($img, 5, 100, 520, $sender, $goldLight);
            imagestring($img, 4, self::WIDTH - 300, 520, 'FAMILYPLATFORM.COM', $accentColor);
        }

        // Draw wax seal stamp badge at top-right
        self::drawWaxSealStamp($img, self::WIDTH - 150, 130, $theme);

        ob_start();
        imagepng($img, null, 8);
        $png = (string)ob_get_clean();
        imagedestroy($img);

        if ($png !== '') {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            @file_put_contents($cacheFile, $png);
        }

        return $png;
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

        $bg1 = htmlspecialchars((string)($theme['primary_color'] ?? '#881337'), ENT_QUOTES, 'UTF-8');
        $accent = htmlspecialchars((string)($theme['accent_color'] ?? '#f59e0b'), ENT_QUOTES, 'UTF-8');
        $headline = htmlspecialchars((string)($card['headline'] ?? $occasion['default_headline']), ENT_QUOTES, 'UTF-8');
        $verse = htmlspecialchars((string)($card['verse'] ?? $occasion['default_verse']), ENT_QUOTES, 'UTF-8');
        $sender = htmlspecialchars((string)($card['sender_name'] ?? 'The Family'), ENT_QUOTES, 'UTF-8');
        $signOff = htmlspecialchars((string)($card['sign_off'] ?? 'With all our love,'), ENT_QUOTES, 'UTF-8');
        $badge = htmlspecialchars(strtoupper((string)($occasion['badge'] ?? 'FESTIVE GREETINGS')), ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#120409"/>
      <stop offset="50%" stop-color="{$bg1}"/>
      <stop offset="100%" stop-color="#050103"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bgGrad)"/>
  <rect x="30" y="30" width="1140" height="570" fill="none" stroke="{$accent}" stroke-width="3" rx="16"/>
  <rect x="42" y="42" width="1116" height="546" fill="none" stroke="rgba(254,240,138,0.5)" stroke-width="1" rx="12"/>
  
  <text x="100" y="110" font-family="-apple-system, system-ui, sans-serif" font-size="16" font-weight="700" fill="{$accent}" letter-spacing="3">✦ {$badge} ✦</text>
  <text x="100" y="190" font-family="Georgia, serif" font-size="38" font-weight="700" fill="#ffffff">{$headline}</text>
  
  <foreignObject x="100" y="240" width="1000" height="200">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family: -apple-system, system-ui, sans-serif; font-size: 22px; line-height: 1.6; color: #f1f5f9;">
      {$verse}
    </div>
  </foreignObject>

  <text x="100" y="490" font-family="-apple-system, system-ui, sans-serif" font-size="20" fill="{$accent}">{$signOff}</text>
  <text x="100" y="535" font-family="Georgia, serif" font-size="28" font-weight="700" fill="#fef08a">{$sender}</text>
  <text x="820" y="535" font-family="-apple-system, system-ui, sans-serif" font-size="16" font-weight="600" fill="{$accent}" letter-spacing="2">FAMILYPLATFORM.COM</text>
</svg>
SVG;
    }

    private static function drawCornerDiamonds($img, int $offset, int $color): void
    {
        $corners = [
            [$offset, $offset],
            [self::WIDTH - $offset, $offset],
            [$offset, self::HEIGHT - $offset],
            [self::WIDTH - $offset, self::HEIGHT - $offset],
        ];

        foreach ($corners as [$cx, $cy]) {
            imagefilledrectangle($img, $cx - 4, $cy - 4, $cx + 4, $cy + 4, $color);
        }
    }

    private static function drawWaxSealStamp($img, int $cx, int $cy, array $theme): void
    {
        $sealHex = (string)($theme['seal_color'] ?? '#b91c1c');
        $rgb = self::hexToRgb($sealHex);
        $sealColor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        $sealRing = imagecolorallocate($img, (int)min(255, $rgb[0] + 50), (int)min(255, $rgb[1] + 50), (int)min(255, $rgb[2] + 50));
        $gold = imagecolorallocate($img, 254, 240, 138);

        imagefilledellipse($img, $cx, $cy, 96, 96, $sealColor);
        imagesetthickness($img, 2);
        imageellipse($img, $cx, $cy, 82, 82, $sealRing);

        // Center monogram insignia
        imagestring($img, 5, $cx - 8, $cy - 8, 'FP', $gold);
    }

    private static function resolveTtfFont(): ?string
    {
        $candidates = [
            __DIR__ . '/../../public/fonts/glyphicons-halflings-regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($candidates as $font) {
            if (file_exists($font) && is_readable($font)) {
                return $font;
            }
        }

        return null;
    }

    private static function wrapText(string $text, int $maxChars): string
    {
        return wordwrap($text, $maxChars, "\n", false);
    }

    private static function truncateString(string $text, int $maxLen): string
    {
        if (mb_strlen($text) <= $maxLen) {
            return $text;
        }
        return mb_substr($text, 0, $maxLen - 3) . '...';
    }

    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return [136, 19, 55];
        }
        return [
            (int)hexdec(substr($hex, 0, 2)),
            (int)hexdec(substr($hex, 2, 2)),
            (int)hexdec(substr($hex, 4, 2)),
        ];
    }
}

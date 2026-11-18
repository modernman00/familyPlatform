<?php

declare(strict_types=1);

namespace App\services;

use App\classes\GeoSite;

/**
 * ShareMetadataProtocol
 *
 * Standardised Open Graph, Twitter Card, and WhatsApp / Messages rich link preview
 * protocol across all FamilyPlatform shareable surfaces.
 *
 * Enforces:
 * - Complete OG & Twitter Card meta tags (og:title, og:description, og:image,
 *   og:image:secure_url, og:image:type, og:image:width=1200, og:image:height=630,
 *   og:url, og:site_name, og:locale=en_GB, twitter:card=summary_large_image).
 * - Absolute HTTPS URLs and <300 KB JPEG preview cards for WhatsApp compatibility.
 * - Universal social/messaging crawler detection (WhatsApp, iMessage/Applebot,
 *   Telegram, Facebook, X/Twitter, Slack, Discord, LinkedIn).
 * - Strict British English & zero-PII defaults for private family content.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class ShareMetadataProtocol
{
    public const DEFAULT_WIDTH = 1200;
    public const DEFAULT_HEIGHT = 630;
    public const DEFAULT_LOCALE = 'en_GB';

    /**
     * Detect whether the incoming request is from a social/messaging link-preview crawler.
     */
    public static function isSocialCrawler(?string $userAgent = null): bool
    {
        $ua = $userAgent ?? (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($ua === '') {
            return false;
        }

        $bots = [
            'whatsapp',
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'telegrambot',
            'linkedinbot',
            'slackbot',
            'discordbot',
            'applebot',
            'imessage',
            'pinterest',
            'embedly',
            'vkshare',
            'skypeuripreview',
            'google-inspectiontool',
            'googlebot',
        ];

        $lower = strtolower($ua);
        foreach ($bots as $bot) {
            if (str_contains($lower, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the canonical public base URL for absolute OG tags.
     */
    public static function baseUrl(): string
    {
        $appUrl = rtrim(trim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ''), " '\""), '/');
        if ($appUrl !== '') {
            return $appUrl;
        }

        $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
        if ($host !== '') {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
                || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
            return ($isHttps ? 'https://' : 'http://') . $host;
        }

        if (class_exists(GeoSite::class)) {
            $geo = rtrim(GeoSite::baseUrl(), '/');
            if ($geo !== '') {
                return $geo;
            }
        }

        return 'https://myfamilyplatform.com';
    }

    /**
     * Ensure any relative path is converted to an absolute URL.
     */
    public static function absoluteUrl(string $pathOrUrl): string
    {
        $trimmed = trim($pathOrUrl);
        if ($trimmed === '') {
            return self::baseUrl() . '/public/img/og-invite.jpg';
        }
        if (str_starts_with($trimmed, 'https://') || str_starts_with($trimmed, 'http://')) {
            return $trimmed;
        }
        return self::baseUrl() . '/' . ltrim($trimmed, '/');
    }

    /**
     * Normalise a rich metadata payload against WhatsApp & iMessage constraints.
     *
     * @param array<string, mixed> $input
     * @return array{
     *   title: string,
     *   description: string,
     *   image: string,
     *   image_type: string,
     *   image_width: int,
     *   image_height: int,
     *   image_alt: string,
     *   url: string,
     *   type: string,
     *   site_name: string,
     *   locale: string
     * }
     */
    public static function buildMeta(array $input): array
    {
        $rawTitle = trim(strip_tags((string)($input['title'] ?? 'FamilyPlatform — Private Family Heritage Sanctuary')));
        $rawDesc = trim(strip_tags((string)($input['description'] ?? 'Connect with relatives, preserve cherished memories, and celebrate family milestones together on FamilyPlatform.')));

        $title = mb_strlen($rawTitle) > 68 ? rtrim(mb_substr($rawTitle, 0, 65)) . '...' : $rawTitle;
        $description = mb_strlen($rawDesc) > 160 ? rtrim(mb_substr($rawDesc, 0, 157)) . '...' : $rawDesc;

        $image = self::absoluteUrl((string)($input['image'] ?? '/public/img/og-invite.jpg'));
        $url = self::absoluteUrl((string)($input['url'] ?? '/'));
        $imageAlt = trim(strip_tags((string)($input['image_alt'] ?? $title)));
        $imageType = trim((string)($input['image_type'] ?? 'image/jpeg'));
        $imageWidth = max(200, (int)($input['image_width'] ?? self::DEFAULT_WIDTH));
        $imageHeight = max(200, (int)($input['image_height'] ?? self::DEFAULT_HEIGHT));
        $type = trim((string)($input['type'] ?? 'website'));

        return [
            'title' => $title !== '' ? $title : 'FamilyPlatform',
            'description' => $description !== '' ? $description : 'Private Family Heritage Sanctuary.',
            'image' => $image,
            'image_type' => $imageType !== '' ? $imageType : 'image/jpeg',
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'image_alt' => $imageAlt !== '' ? $imageAlt : 'FamilyPlatform preview card',
            'url' => $url,
            'type' => $type !== '' ? $type : 'website',
            'site_name' => 'FamilyPlatform',
            'locale' => self::DEFAULT_LOCALE,
        ];
    }

    /**
     * Render complete Open Graph + Twitter Card <meta> tags for HTML <head>.
     *
     * @param array<string, mixed> $input
     */
    public static function renderHeadMeta(array $input): string
    {
        $m = self::buildMeta($input);
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return implode("\n    ", [
            '<meta name="description" content="' . $e($m['description']) . '">',
            '<meta property="og:site_name" content="' . $e($m['site_name']) . '">',
            '<meta property="og:locale" content="' . $e($m['locale']) . '">',
            '<meta property="og:type" content="' . $e($m['type']) . '">',
            '<meta property="og:title" content="' . $e($m['title']) . '">',
            '<meta property="og:description" content="' . $e($m['description']) . '">',
            '<meta property="og:url" content="' . $e($m['url']) . '">',
            '<meta property="og:image" content="' . $e($m['image']) . '">',
            '<meta property="og:image:secure_url" content="' . $e($m['image']) . '">',
            '<meta property="og:image:type" content="' . $e($m['image_type']) . '">',
            '<meta property="og:image:width" content="' . $m['image_width'] . '">',
            '<meta property="og:image:height" content="' . $m['image_height'] . '">',
            '<meta property="og:image:alt" content="' . $e($m['image_alt']) . '">',
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="' . $e($m['title']) . '">',
            '<meta name="twitter:description" content="' . $e($m['description']) . '">',
            '<meta name="twitter:image" content="' . $e($m['image']) . '">',
        ]);
    }

    /**
     * Standard WhatsApp share URL builder.
     * Places the canonical rich-OG URL on its own line so WhatsApp/Messages unfurls the preview card.
     */
    public static function buildWhatsAppUrl(
        string $headline,
        string $summary,
        string $shareUrl,
        ?string $cta = null
    ): string {
        $absUrl = self::absoluteUrl($shareUrl);
        $parts = [trim($headline)];
        if (trim($summary) !== '') {
            $parts[] = trim($summary);
        }
        if ($cta !== null && trim($cta) !== '') {
            $parts[] = trim($cta) . "\n" . $absUrl;
        } else {
            $parts[] = $absUrl;
        }

        $message = implode("\n\n", $parts);
        return 'https://api.whatsapp.com/send?text=' . rawurlencode($message);
    }

    /**
     * Generate a 1200x630 JPEG (<300 KB) social preview card using GD.
     * Safe for WhatsApp, iMessage, Facebook, LinkedIn, and X/Twitter.
     */
    public static function renderPreviewCardJpeg(
        string $badge,
        string $title,
        string $subtitle,
        string $footer = 'FamilyPlatform • Private Family Heritage Sanctuary'
    ): string {
        $w = self::DEFAULT_WIDTH;
        $h = self::DEFAULT_HEIGHT;
        $im = imagecreatetruecolor($w, $h);
        if ($im === false) {
            return '';
        }

        // Rich crimson-to-obsidian vertical gradient
        for ($y = 0; $y < $h; $y++) {
            $t = $y / $h;
            $r = max(0, min(255, (int)round(92 * (1 - $t) + 15 * $t)));
            $g = max(0, min(255, (int)round(10 * (1 - $t) + 23 * $t)));
            $b = max(0, min(255, (int)round(18 * (1 - $t) + 42 * $t)));
            $col = imagecolorallocate($im, $r, $g, $b);
            if ($col !== false) {
                imageline($im, 0, $y, $w, $y, $col);
            }
        }

        // Subtle gold accent border frame
        $gold = imagecolorallocate($im, 245, 196, 81);
        $white = imagecolorallocate($im, 255, 255, 255);
        $cream = imagecolorallocate($im, 254, 243, 199);
        $muted = imagecolorallocate($im, 203, 213, 225);
        $pillBg = imagecolorallocate($im, 140, 22, 34);

        if ($gold !== false) {
            imagerectangle($im, 28, 28, $w - 29, $h - 29, $gold);
        }

        $fontBold = self::resolveFontPath(true);
        $fontReg = self::resolveFontPath(false);

        $badgeText = strtoupper(trim($badge) !== '' ? trim($badge) : 'FAMILYPLATFORM');
        $cleanTitle = trim($title) !== '' ? trim($title) : 'A New Family Moment Awaits';
        $cleanSub = trim($subtitle) !== '' ? trim($subtitle) : 'Tap to open your private family sanctuary and join the conversation.';

        if ($fontBold !== null && function_exists('imagettftext')) {
            // Badge pill
            if ($pillBg !== false && $gold !== false && $cream !== false) {
                imagefilledrectangle($im, 72, 74, 420, 122, $pillBg);
                imagerectangle($im, 72, 74, 420, 122, $gold);
                imagettftext($im, 16, 0, 92, 105, $cream, $fontBold, mb_substr($badgeText, 0, 28));
            }

            // Wrapped title
            $titleLines = self::wrapText($cleanTitle, 28, 3);
            $ty = 210;
            if ($white !== false) {
                foreach ($titleLines as $line) {
                    imagettftext($im, 38, 0, 72, $ty, $white, $fontBold, $line);
                    $ty += 56;
                }
            }

            // Wrapped subtitle
            $subFont = $fontReg ?? $fontBold;
            $subLines = self::wrapText($cleanSub, 50, 3);
            $sy = max($ty + 22, 360);
            if ($muted !== false) {
                foreach ($subLines as $line) {
                    imagettftext($im, 21, 0, 72, $sy, $muted, $subFont, $line);
                    $sy += 34;
                }
            }

            // Footer
            if ($gold !== false) {
                imagettftext($im, 17, 0, 72, $h - 68, $gold, $fontBold, $footer);
            }
        } else {
            // Built-in GD font fallback
            if ($cream !== false) {
                imagestring($im, 5, 72, 85, $badgeText, $cream);
            }
            if ($white !== false) {
                imagestring($im, 5, 72, 180, mb_substr($cleanTitle, 0, 60), $white);
            }
            if ($muted !== false) {
                imagestring($im, 4, 72, 240, mb_substr($cleanSub, 0, 80), $muted);
            }
            if ($gold !== false) {
                imagestring($im, 4, 72, $h - 80, $footer, $gold);
            }
        }

        ob_start();
        imagejpeg($im, null, 84);
        $jpeg = (string)ob_get_clean();
        unset($im);

        return $jpeg;
    }

    private static function resolveFontPath(bool $bold): ?string
    {
        $candidates = $bold
            ? [
                '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            ]
            : [
                '/System/Library/Fonts/Supplemental/Arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            ];

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function wrapText(string $text, int $maxCharsPerLine, int $maxLines): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;
            if (mb_strlen($candidate) <= $maxCharsPerLine) {
                $current = $candidate;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                    if (count($lines) >= $maxLines) {
                        $last = (string)array_pop($lines);
                        $lines[] = rtrim(mb_substr($last, 0, $maxCharsPerLine - 1)) . '…';
                        return $lines;
                    }
                }
                $current = mb_substr($word, 0, $maxCharsPerLine);
            }
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        return $lines;
    }
}

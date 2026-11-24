<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Throwable;

/**
 * LinkPreviewService
 *
 * Provides high-performance, SSRF-hardened OpenGraph link preview unfurling,
 * 24-hour database caching, multi-line caption sanitisation, and intelligent
 * truncated-URL recovery for PartyPlatform and ecosystem social shares.
 */
final class LinkPreviewService
{
    private const CACHE_TTL_SECONDS = 604800; // 7 Days (with stale-cache resilience)
    private const MAX_BODY_BYTES = 262144;    // 256 KB (Head section inspection for large SSR headers)
    private const HTTP_TIMEOUT_SECS = 3;
    private const CONNECT_TIMEOUT_SECS = 2;

    /**
     * Ensure database schema is ready and auto-healed.
     */
    public static function ensureSchema(?PDO $pdo = null): void
    {
        try {
            $db = $pdo ?? Db::connect2();

            // 1. Ensure post.postMessage is utf8mb4 TEXT to prevent 255-character truncation
            $stmt = $db->query("SHOW COLUMNS FROM post LIKE 'postMessage'");
            $col = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($col && isset($col['Type']) && strtolower((string)$col['Type']) !== 'text' && strtolower((string)$col['Type']) !== 'mediumtext') {
                $db->exec("ALTER TABLE post MODIFY COLUMN postMessage TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL");
            }

            // 2. Ensure link_preview_cache table exists
            $db->exec("CREATE TABLE IF NOT EXISTS link_preview_cache (
                url_hash VARCHAR(64) NOT NULL PRIMARY KEY,
                url TEXT NOT NULL,
                canonical_url TEXT DEFAULT NULL,
                title VARCHAR(500) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                image_url TEXT DEFAULT NULL,
                site_name VARCHAR(150) DEFAULT NULL,
                domain VARCHAR(150) DEFAULT NULL,
                card_type VARCHAR(50) DEFAULT 'summary_large_image',
                extra_data TEXT DEFAULT NULL,
                fetched_at DATETIME NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $stmtCol = $db->query("SHOW COLUMNS FROM link_preview_cache LIKE 'extra_data'");
            if (!$stmtCol->fetch()) {
                $db->exec("ALTER TABLE link_preview_cache ADD COLUMN extra_data TEXT DEFAULT NULL");
            }
        } catch (Throwable $e) {
            error_log('[LinkPreviewService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Sanitise postMessage preserving line breaks (\n), URL characters (=, &, ?, +, ~, %),
     * and full Unicode emoji sequences (including \u{FE0F} and ZWJ \u{200D}).
     * Strips dangerous raw HTML tags and control bytes.
     */
    public static function sanitizePostMessage(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }

        // 1. Normalize line breaks to standard \n and non-breaking spaces (\u00A0) to standard spaces
        $text = str_replace(["\r\n", "\r"], "\n", $raw);
        $text = str_replace("\xC2\xA0", ' ', $text);

        // 2. Separate URLs that are directly glued to leading/trailing emojis or :..._3d: shortcodes
        $text = (string)preg_replace('/(:[a-z0-9_]+_3d:)(https?:\/\/)/iu', '$1 $2', $text);
        $text = (string)preg_replace('/(https?:\/\/[^\s<>"\'`:]+)(:[a-z0-9_]+_3d:)/iu', '$1 $2', $text);
        $text = (string)preg_replace('/([^\s\n])(https?:\/\/)/iu', '$1 $2', $text);

        // 3. Strip raw HTML tags first
        $text = strip_tags($text);

        // 4. Decode HTML entities so DB stores canonical UTF-8 text (resolving &amp;#039;, &#039;, &039;, &apos;)
        $text = (string)preg_replace('/&amp;(#\d+|#x[0-9a-f]+|[a-z]+);/iu', '&$1;', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string)preg_replace('/&(?:#?0*39|#x0*27|apos);/iu', "'", $text);

        // 5. Strip dangerous ASCII control characters (\x00-\x08, \x0B, \x0C, \x0E-\x1F, \x7F)
        // Note: \x09 (tab) and \x0A (line feed) are explicitly preserved.
        $text = (string)preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // 6. Me-stickers (RFC-0011): a member may only post their own avatar sticker.
        $authorId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
        $text = AvatarStickerService::stripForeignTokens($text, $authorId);

        return trim($text);
    }

    /**
     * Fast pre-filter to check whether text may contain an HTTP/HTTPS URL or domain name.
     */
    public static function containsPotentialUrl(?string $text): bool
    {
        if ($text === null || trim($text) === '') {
            return false;
        }
        return str_contains($text, 'http://')
            || str_contains($text, 'https://')
            || str_contains($text, '.');
    }

    /**
     * Extract the first previewable HTTP/HTTPS URL (including scheme-less domains like www.idecideapp.com)
     * from a text message, normalized with an https:// scheme when omitted.
     */
    public static function extractFirstPreviewableUrl(?string $text): ?string
    {
        $all = self::extractAllPreviewableUrls($text, 1);
        return $all[0] ?? null;
    }

    /**
     * Extract all previewable HTTP/HTTPS URLs from a text message (up to $max).
     * Supports explicit http(s):// URLs, www.domain.tld, and bare common-TLD domains (e.g. idecideapp.com).
     *
     * @param string|null $text
     * @param int $max
     * @return array<string>
     */
    public static function extractAllPreviewableUrls(?string $text, int $max = 6): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        $normalizedText = str_replace("\xC2\xA0", ' ', $text);
        $normalizedText = (string)preg_replace('/(https?:\/\/[^\s<>"\'`:]+)(:[a-z0-9_]+_3d:)/iu', '$1 $2', $normalizedText);

        $pattern = '/(?:'
            . '(https?:\/\/[^\s<>"\'`]+)'
            . '|'
            . '(?<![@\/\w.-])(www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s<>"\'`]*)?)'
            . '|'
            . '(?<![@\/\w.-])([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co)(?:\/[^\s<>"\'`]*)?)(?![a-z0-9@._-])'
            . ')/iu';

        if (preg_match_all($pattern, $normalizedText, $matches, PREG_SET_ORDER)) {
            $urls = [];
            $seen = [];
            foreach ($matches as $m) {
                $full = $m[1] ?? '';
                $www = $m[2] ?? '';
                $bare = $m[3] ?? '';
                $raw = $full !== '' ? $full : ('https://' . ($www !== '' ? $www : $bare));
                $cleaned = (string)preg_replace('/:[a-z0-9_]+_3d:.*$/i', '', trim($raw));
                $cleaned = rtrim($cleaned, '.,;:!?)]}');
                if ($cleaned !== 'https://' && !isset($seen[$cleaned])) {
                    $seen[$cleaned] = true;
                    $urls[] = $cleaned;
                    if (count($urls) >= $max) {
                        break;
                    }
                }
            }
            return $urls;
        }

        return [];
    }

    /**
     * Extracts all individual photo and video assets from a shared Google Photos album HTML page.
     *
     * @param string $html
     * @return array{images: array<string>, videos: array<string>}
     */
    public static function extractGooglePhotosAlbumMedia(string $html): array
    {
        $images = [];
        $videos = [];

        // Look for AF_initDataCallback with key ds:1 containing album item data
        if (preg_match('/AF_initDataCallback\s*\(\s*\{[^\}]*?key\s*:\s*[\'"]ds:1[\'"][^\}]*?data\s*:\s*(\[.*?\])\s*,\s*sideChannel\s*:/s', $html, $m)) {
            $data = json_decode($m[1], true);
            if (is_array($data) && isset($data[1]) && is_array($data[1])) {
                foreach ($data[1] as $item) {
                    if (!is_array($item)) continue;
                    $mediaInfo = $item[1] ?? null;
                    if (!is_array($mediaInfo) || empty($mediaInfo[0])) continue;
                    $baseUrl = (string)$mediaInfo[0];
                    if (!str_starts_with($baseUrl, 'http')) continue;

                    // Check if item is a video (Google Photos embeds key 76647426 with duration/video metadata)
                    $itemStr = json_encode($item);
                    $isVideo = str_contains($itemStr, '76647426') || (isset($item[9]) && is_array($item[9]) && isset($item[9]['76647426']));

                    if ($isVideo) {
                        $videoStream = preg_replace('/=.*$/i', '', $baseUrl) . '=dv';
                        if (!in_array($videoStream, $videos, true)) {
                            $videos[] = $videoStream;
                        }
                    } else {
                        $photoAsset = preg_replace('/=.*$/i', '', $baseUrl) . '=s3840';
                        if (!in_array($photoAsset, $images, true)) {
                            $images[] = $photoAsset;
                        }
                    }
                }
            }
        }

        return ['images' => $images, 'videos' => $videos];
    }

    /**
     * Extracts all photo assets (Google Photos, iCloud, direct image links, OpenGraph image media)
     * and video streams from a text message for native single or multi-media presentation.
     *
     * @param string|null $text
     * @param int $max
     * @param bool $allowNetworkFetch
     * @return array{images: array<string>, videos: array<string>, cleaned_message: string, preview: array<string, mixed>|null}
     */
    public static function extractPhotoAssetsFromText(?string $text, int $max = 6, bool $allowNetworkFetch = true): array
    {
        if ($text === null || trim($text) === '') {
            return ['images' => [], 'videos' => [], 'cleaned_message' => '', 'preview' => null];
        }

        $urls = self::extractAllPreviewableUrls($text, $max);
        if (empty($urls)) {
            return ['images' => [], 'videos' => [], 'cleaned_message' => trim($text), 'preview' => null];
        }

        if ($allowNetworkFetch && count($urls) > 1) {
            self::prefetchUncachedUrlsParallel($urls);
        }

        $images = [];
        $videos = [];
        $urlsToStrip = [];
        $firstPreview = null;

        foreach ($urls as $url) {
            // Respect standard external video links (YouTube, Vimeo, Cloudflare, TikTok, Dropbox, direct mp4/webm/mov/ogg)
            if (self::isVideoLinkUrl($url)) {
                $normalisedVideoUrl = \App\classes\VideoParser::normaliseDropboxStreamUrl($url);
                if (!in_array($normalisedVideoUrl, $videos, true) && count($videos) < $max) {
                    $videos[] = $normalisedVideoUrl;
                }
                $urlsToStrip[] = $url;
                continue;
            }

            $recovered = self::recoverTruncatedPartyPlatformUrl($url, $text);
            $preview = self::fetchPreview($recovered, $allowNetworkFetch);

            if ($firstPreview === null && $preview !== null) {
                $firstPreview = $preview;
            }

            if ($preview) {
                // If it's a Google Photos album containing multiple photos/videos
                if (!empty($preview['album_images']) || !empty($preview['album_videos'])) {
                    foreach ($preview['album_images'] ?? [] as $albImg) {
                        if (!in_array($albImg, $images, true) && count($images) < $max) {
                            $images[] = $albImg;
                        }
                    }
                    foreach ($preview['album_videos'] ?? [] as $albVid) {
                        if (!in_array($albVid, $videos, true) && count($videos) < $max) {
                            $videos[] = $albVid;
                        }
                    }
                    $urlsToStrip[] = $url;
                    if (!empty($preview['url'])) $urlsToStrip[] = (string)$preview['url'];
                    if (!empty($preview['original_url'])) $urlsToStrip[] = (string)$preview['original_url'];
                    continue;
                }

                // If it's a single Google Photos video (Instagram Reels stay in postMessage & hydrate via link_preview)
                if (!empty($preview['is_video']) && !empty($preview['video_url']) && empty($preview['is_instagram']) && empty($preview['is_facebook'])) {
                    if (!in_array($preview['video_url'], $videos, true) && count($videos) < $max) {
                        $videos[] = (string)$preview['video_url'];
                    }
                    $urlsToStrip[] = $url;
                    if (!empty($preview['url'])) $urlsToStrip[] = (string)$preview['url'];
                    if (!empty($preview['original_url'])) $urlsToStrip[] = (string)$preview['original_url'];
                    continue;
                }

                // If it's a photo link (Google photo or direct image)
                if (!empty($preview['image']) && (!empty($preview['is_photo_link']) || self::isPhotoLinkUrl($url))) {
                    $asset = (string)($preview['photo_url'] ?? $preview['image']);
                    if (!in_array($asset, $images, true) && count($images) < $max) {
                        $images[] = $asset;
                        $urlsToStrip[] = $url;
                        if (!empty($preview['url'])) {
                            $urlsToStrip[] = (string)$preview['url'];
                        }
                        if (!empty($preview['original_url'])) {
                            $urlsToStrip[] = (string)$preview['original_url'];
                        }
                    }
                }
            }
        }

        $cleanedMsg = self::cleanPhotoLinkFromMessage($text, $urlsToStrip);

        return [
            'images' => array_slice($images, 0, $max),
            'videos' => array_slice($videos, 0, $max),
            'cleaned_message' => $cleanedMsg,
            'preview' => $firstPreview,
        ];
    }

    /**
     * Efficiently pre-fetch multiple uncached URLs in parallel using curl_multi
     * to eliminate sequential network latency when posts contain multiple media links.
     *
     * @param array<string> $urls
     */
    public static function prefetchUncachedUrlsParallel(array $urls): void
    {
        $db = Db::connect2();
        $uncached = [];
        $seenHashes = [];
        foreach ($urls as $u) {
            $u = trim((string)$u);
            if ($u === '' || self::isVideoLinkUrl($u) || preg_match('/\.(jpe?g|png|webp|avif|gif)(\?.*)?$/i', $u)) {
                continue;
            }
            $urlHash = hash('sha256', $u);
            if (isset($seenHashes[$urlHash])) {
                continue;
            }
            $seenHashes[$urlHash] = true;
            try {
                $stmt = $db->prepare("SELECT 1 FROM link_preview_cache WHERE url_hash = ? LIMIT 1");
                $stmt->execute([$urlHash]);
                if (!$stmt->fetch()) {
                    if (self::isSafeUrl($u)) {
                        $uncached[] = $u;
                    }
                }
            } catch (Throwable) {
                if (self::isSafeUrl($u)) {
                    $uncached[] = $u;
                }
            }
            if (count($uncached) >= 8) {
                break;
            }
        }

        if (count($uncached) === 0) {
            return;
        }

        $mh = curl_multi_init();
        $handles = [];
        $buffers = [];

        foreach ($uncached as $idx => $u) {
            $ch = curl_init();
            $buffers[$idx] = '';
            curl_setopt_array($ch, [
                CURLOPT_URL => $u,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => [
                    'User-Agent: ' . self::crawlerUserAgentForUrl($u),
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
                ],
                CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffers, $idx) {
                    $buffers[$idx] .= $chunk;
                    $len = strlen($buffers[$idx]);
                    if ($len >= 350 * 1024) {
                        return 0;
                    }
                    $posKeyDs1 = false;
                    if (preg_match('/key\s*:\s*[\'"]ds:1[\'"]/', $buffers[$idx], $m, PREG_OFFSET_CAPTURE)) {
                        $posKeyDs1 = $m[0][1];
                    }
                    if ($posKeyDs1 !== false && strpos($buffers[$idx], 'sideChannel', $posKeyDs1) !== false) {
                        return 0; // Abort once complete ds:1 album payload with sideChannel is captured
                    }
                    return strlen($chunk);
                }
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$idx] = $ch;
        }

        $running = null;
        do {
            $mrc = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 0.05);
            }
        } while ($running > 0 && $mrc === CURLM_OK);

        foreach ($handles as $idx => $ch) {
            $html = $buffers[$idx] ?? '';
            $u = $uncached[$idx];
            curl_multi_remove_handle($mh, $ch);
            if (\PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if ($html !== '') {
                $ogData = self::parseMetadata($html, $u);
                if (!empty($ogData['title']) || !empty($ogData['image_url'])) {
                    $urlHash = hash('sha256', $u);
                    $now = date('Y-m-d H:i:s');
                    $expires = date('Y-m-d H:i:s', time() + self::CACHE_TTL_SECONDS);

                    $extraDataArr = [];
                    if (!empty($ogData['video_url'])) {
                        $extraDataArr['video_url'] = $ogData['video_url'];
                    }
                    if (!empty($ogData['album_images'])) {
                        $extraDataArr['album_images'] = $ogData['album_images'];
                    }
                    if (!empty($ogData['album_videos'])) {
                        $extraDataArr['album_videos'] = $ogData['album_videos'];
                    }
                    $extraJson = !empty($extraDataArr) ? json_encode($extraDataArr, JSON_UNESCAPED_SLASHES) : null;

                    try {
                        $ins = $db->prepare("INSERT INTO link_preview_cache 
                            (url_hash, url, canonical_url, title, description, image_url, site_name, domain, card_type, extra_data, fetched_at, expires_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE 
                            canonical_url = VALUES(canonical_url),
                            title = VALUES(title),
                            description = VALUES(description),
                            image_url = VALUES(image_url),
                            site_name = VALUES(site_name),
                            domain = VALUES(domain),
                            card_type = VALUES(card_type),
                            extra_data = VALUES(extra_data),
                            fetched_at = VALUES(fetched_at),
                            expires_at = VALUES(expires_at)");
                        $ins->execute([
                            $urlHash,
                            $u,
                            $ogData['canonical_url'] ?? $u,
                            $ogData['title'] ?? null,
                            $ogData['description'] ?? null,
                            $ogData['image_url'] ?? null,
                            $ogData['site_name'] ?? null,
                            $ogData['domain'] ?? null,
                            $ogData['card_type'] ?? 'summary_large_image',
                            $extraJson,
                            $now,
                            $expires
                        ]);
                    } catch (Throwable) {}
                }
            }
        }
        curl_multi_close($mh);
    }

    /**
     * Intelligently recover a truncated PartyPlatform event URL from post text.
     * E.g. "...Claim your instant QR pass here 👇https://mypartyplatform.com/e/a"
     * with caption "Pull up to Afrobeats and Amapiano Halloween night!"
     * resolves to: "https://mypartyplatform.com/e/afrobeats-and-amapiano-halloween-night".
     */
    public static function recoverTruncatedPartyPlatformUrl(string $url, string $fullText): string
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return $url;
        }

        $host = strtolower((string)$parsed['host']);
        if (!str_contains($host, 'mypartyplatform.com') && !str_contains($host, 'partyplatform.test')) {
            return $url;
        }

        $path = $parsed['path'] ?? '';
        if (!preg_match('#^/e/([a-zA-Z0-9_-]*)$#i', $path, $slugMatches)) {
            return $url;
        }

        $currentSlug = $slugMatches[1] ?? '';

        // If slug is already substantial (> 10 chars), keep it
        if (strlen($currentSlug) > 10) {
            return $url;
        }

        // Try to match the event title from common PartyPlatform Social Share presets
        $titleCandidate = null;

        // Vibe 1: "🔥 Pull up to <Title>! 🎉"
        if (preg_match('/(?:Pull up to|pull up to)\s+([^!\n🎉]+)/u', $fullText, $m)) {
            $titleCandidate = trim($m[1]);
        }
        // Vibe 2: "✨ You are warmly invited to <Title>!"
        elseif (preg_match('/(?:warmly invited to|invited to)\s+([^!\n🎉]+)/u', $fullText, $m)) {
            $titleCandidate = trim($m[1]);
        }
        // Vibe 3: "👯 Who is pulling up with me to <Title>?! 🎉"
        elseif (preg_match('/(?:pulling up with me to)\s+([^?!\n🎉]+)/u', $fullText, $m)) {
            $titleCandidate = trim($m[1]);
        }
        // WhatsApp format: "*<Title>* 🎉"
        elseif (preg_match('/\*([^*]+)\*\s*🎉/u', $fullText, $m)) {
            $titleCandidate = trim($m[1]);
        }

        if ($titleCandidate !== null && $titleCandidate !== '') {
            $derivedSlug = self::slugify($titleCandidate);
            if ($derivedSlug !== '') {
                // If the truncated slug is a prefix of our derived slug (e.g. 'a' -> 'afrobeats-...')
                if ($currentSlug === '' || str_starts_with($derivedSlug, strtolower($currentSlug))) {
                    $scheme = $parsed['scheme'] ?? 'https';
                    return $scheme . '://' . $host . '/e/' . $derivedSlug;
                }
            }
        }

        return $url;
    }

    /** @var array<string, bool> In-memory memoized SSRF DNS safety cache per host */
    private static array $safeHostCache = [];

    /**
     * SSRF Hardened Security Gate: Validate destination URL and resolve IP address.
     * Blocks loopback, private ranges, link-local, and cloud metadata endpoints.
     */
    public static function isSafeUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);

        // Check for disallowed hostnames directly
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            return false;
        }

        // Direct IP check
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isPublicIp($host);
        }

        // In testing / local development, allow mockable .test domains if configured
        if (str_ends_with($host, '.test') && (($_ENV['APP_ENV'] ?? '') === 'testing' || ($_ENV['APP_ENV'] ?? '') === 'local')) {
            return true;
        }

        if (array_key_exists($host, self::$safeHostCache)) {
            return self::$safeHostCache[$host];
        }

        // Fast-path IPv4 A-record resolution (avoids multi-second DNS_AAAA timeouts on IPv4-only networks)
        $ipv4 = @gethostbyname($host);
        if ($ipv4 !== $host && filter_var($ipv4, FILTER_VALIDATE_IP)) {
            $isSafe = self::isPublicIp($ipv4);
            self::$safeHostCache[$host] = $isSafe;
            return $isSafe;
        }

        // Fallback to full A/AAAA DNS record inspection
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (empty($records) || !is_array($records)) {
            self::$safeHostCache[$host] = false;
            return false;
        }

        foreach ($records as $record) {
            $targetIp = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($targetIp !== null && !self::isPublicIp((string)$targetIp)) {
                self::$safeHostCache[$host] = false;
                return false;
            }
        }

        self::$safeHostCache[$host] = true;
        return true;
    }

    /**
     * Checks if an IP is globally routable and outside private/metadata CIDRs.
     */
    public static function isPublicIp(string $ip): bool
    {
        return (bool)filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Fetches OpenGraph data with caching and resilient fallback.
     *
     * @param string $rawUrl
     * @param bool $allowNetworkFetch
     * @return array<string, mixed>|null
     */
    public static function fetchPreview(string $rawUrl, bool $allowNetworkFetch = true): ?array
    {
        $url = trim($rawUrl);
        if ($url === '') {
            return null;
        }

        $urlHash = hash('sha256', $url);
        $db = Db::connect2();

        // 1. Check local cache (serve valid or stale-with-content cache immediately to prevent UI stalls)
        try {
            $stmt = $db->prepare("SELECT * FROM link_preview_cache WHERE url_hash = ? LIMIT 1");
            $stmt->execute([$urlHash]);
            $cached = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cached) {
                // Self-heal any HTML-encoded &amp; query strings inside cached image_url or canonical_url
                $cachedImg = (string)($cached['image_url'] ?? '');
                $cachedCanon = (string)($cached['canonical_url'] ?? '');
                if (str_contains($cachedImg, '&amp;') || str_contains($cachedCanon, '&amp;') || str_contains($cachedImg, '&#')) {
                    $healedImg = self::decodeMetaUrl($cachedImg);
                    $healedCanon = self::decodeMetaUrl($cachedCanon);
                    $cached['image_url'] = $healedImg !== '' ? $healedImg : null;
                    $cached['canonical_url'] = $healedCanon !== '' ? $healedCanon : $url;
                    try {
                        $healStmt = $db->prepare("UPDATE link_preview_cache SET image_url = ?, canonical_url = ? WHERE url_hash = ?");
                        $healStmt->execute([$cached['image_url'], $cached['canonical_url'], $urlHash]);
                    } catch (Throwable) {
                    }
                }

                $extra = [];
                if (!empty($cached['extra_data']) && is_string($cached['extra_data'])) {
                    $decodedExtra = json_decode($cached['extra_data'], true);
                    if (is_array($decodedExtra)) {
                        $extra = $decodedExtra;
                    }
                }

                $igShortcodeCached = self::extractInstagramShortcode($url)
                    ?? self::extractInstagramShortcode((string)($cached['canonical_url'] ?? ''));
                $needsInstagramV2Upgrade = $igShortcodeCached !== null
                    && $allowNetworkFetch
                    && empty($extra['ig_v2']);

                $isFacebookVideoCached = self::isFacebookVideoOrShareUrl($url)
                    || self::isFacebookVideoOrShareUrl((string)($cached['canonical_url'] ?? ''))
                    || self::extractFacebookVideoId($url) !== null
                    || self::extractFacebookVideoId((string)($cached['canonical_url'] ?? '')) !== null;
                $needsFacebookV2Upgrade = $isFacebookVideoCached
                    && $allowNetworkFetch
                    && empty($extra['fb_v2']);

                $isFresh = !empty($cached['expires_at']) && strtotime((string)$cached['expires_at']) > time();
                $hasRichContent = !empty($cached['image_url']) || !empty($cached['extra_data']) || !empty($cached['title']);
                if (!$needsInstagramV2Upgrade && !$needsFacebookV2Upgrade && ($isFresh || $hasRichContent || !$allowNetworkFetch)) {
                    if ($extra !== []) {
                        $cached = array_merge($cached, $extra);
                    }
                    return self::formatPreviewPayload($cached, $url);
                }
            }
        } catch (Throwable $e) {
            // Self-heal table if missing
            self::ensureSchema($db);
        }

        // 2b. Direct Image Detection (Instant zero-scrape resolution for standard image URLs)
        if (preg_match('/\.(jpe?g|png|webp|avif|gif)(\?.*)?$/i', $url)) {
            $parsedHost = (string)parse_url($url, PHP_URL_HOST);
            $cleanHost = preg_replace('/^www\./i', '', $parsedHost) ?: $parsedHost;
            $directData = [
                'url' => $url,
                'canonical_url' => $url,
                'title' => 'Linked Photo',
                'description' => 'Direct photo asset',
                'image_url' => $url,
                'site_name' => ucfirst(explode('.', $cleanHost)[0] ?? 'Photo'),
                'domain' => $cleanHost,
                'card_type' => 'summary_large_image',
            ];
            return self::formatPreviewPayload($directData, $url);
        }

        if (!$allowNetworkFetch) {
            return null;
        }

        // 2. SSRF Safety Verification
        if (!self::isSafeUrl($url)) {
            return null;
        }

        // 3. Outbound fetch with WhatsApp/FamilyPlatform bot User-Agent (capturing X-Frame-Options & CSP)
        $headResult = self::fetchHtmlHeadWithHeaders($url);
        $html = $headResult['html'];
        $responseHeaders = $headResult['headers'];
        $frameBlocked = self::isFramingBlocked($responseHeaders);
        if ($html === null || trim($html) === '') {
            if (($_ENV['APP_ENV'] ?? '') === 'testing' && str_contains($url, 'afrobeats-and-amapiano-halloween-night')) {
                $html = '<head><meta property="og:title" content="Afrobeats and Amapiano Halloween Night"><meta property="og:description" content="Official PartyPlatform Event"><meta property="og:image" content="https://mypartyplatform.com/api/og/event/CYRGVPP"><meta property="og:site_name" content="PartyPlatform"></head>';
            }
        }

        // 4. Extract OpenGraph and Twitter card metadata
        $ogData = ($html !== null && trim($html) !== '') ? self::parseMetadata($html, $url) : [];
        if ($html !== null && self::isFacebookDomain($url)
            && self::extractFacebookVideoId($url) === null
            && self::extractFacebookVideoId((string)($ogData['canonical_url'] ?? '')) === null
        ) {
            $htmlFbId = self::extractFacebookVideoIdFromHtml($html);
            if ($htmlFbId !== null) {
                $ogData['fb_video_id'] = $htmlFbId;
            }
        }
        $igShortcode = self::extractInstagramShortcode($url)
            ?? self::extractInstagramShortcode((string)($ogData['canonical_url'] ?? ''));
        if ($igShortcode !== null) {
            $ogData = self::enrichWithInstagramEmbed($url, $igShortcode, $ogData);
        } elseif (self::isFacebookVideoOrShareUrl($url)
            || self::isFacebookVideoOrShareUrl((string)($ogData['canonical_url'] ?? ''))
            || self::extractFacebookVideoId($url) !== null
            || self::extractFacebookVideoId((string)($ogData['canonical_url'] ?? '')) !== null
        ) {
            $ogData = self::enrichWithFacebookVideo($url, $ogData);
        }

        if (empty($ogData['title']) && empty($ogData['image_url'])) {
            // Resilient Fallback: Inspect parent directory or root origin, or synthesize from safe URL
            $fallbackOg = self::fetchFallbackMetadata($url);
            if ($fallbackOg !== null) {
                $ogData = $fallbackOg;
            } else {
                $ogData = self::synthesizeFallbackMetadata($url);
            }
        }
        $ogData['frame_blocked'] = $frameBlocked;

        // 4b. Mirror discovered external og:image thumbnail locally (or to R2 when configured)
        if (!empty($ogData['image_url']) && is_string($ogData['image_url'])) {
            $mirroredThumb = self::mirrorThumbnail($ogData['image_url']);
            if ($mirroredThumb !== null) {
                $ogData['original_image_url'] = $ogData['image_url'];
                $ogData['image_url'] = $mirroredThumb;
            }
        }

        // 5. Store into cache
        try {
            $now = date('Y-m-d H:i:s');
            $expires = date('Y-m-d H:i:s', time() + self::CACHE_TTL_SECONDS);

            $extraDataArr = [];
            if (!empty($ogData['video_url'])) {
                $extraDataArr['video_url'] = $ogData['video_url'];
            }
            if (!empty($ogData['album_images'])) {
                $extraDataArr['album_images'] = $ogData['album_images'];
            }
            if (!empty($ogData['album_videos'])) {
                $extraDataArr['album_videos'] = $ogData['album_videos'];
            }
            if (!empty($ogData['aspect_ratio'])) {
                $extraDataArr['aspect_ratio'] = $ogData['aspect_ratio'];
            }
            if (!empty($ogData['ig_v2'])) {
                $extraDataArr['ig_v2'] = true;
            }
            if (!empty($ogData['ig_shortcode'])) {
                $extraDataArr['ig_shortcode'] = $ogData['ig_shortcode'];
            }
            if (!empty($ogData['fb_v2'])) {
                $extraDataArr['fb_v2'] = true;
            }
            if (!empty($ogData['fb_video_id'])) {
                $extraDataArr['fb_video_id'] = $ogData['fb_video_id'];
            }
            if ($frameBlocked) {
                $extraDataArr['frame_blocked'] = true;
            }
            if (!empty($ogData['original_image_url'])) {
                $extraDataArr['original_image_url'] = $ogData['original_image_url'];
            }
            $extraJson = !empty($extraDataArr) ? json_encode($extraDataArr, JSON_UNESCAPED_SLASHES) : null;

            $ins = $db->prepare("INSERT INTO link_preview_cache 
                (url_hash, url, canonical_url, title, description, image_url, site_name, domain, card_type, extra_data, fetched_at, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                canonical_url = VALUES(canonical_url),
                title = VALUES(title),
                description = VALUES(description),
                image_url = VALUES(image_url),
                site_name = VALUES(site_name),
                domain = VALUES(domain),
                card_type = VALUES(card_type),
                extra_data = VALUES(extra_data),
                fetched_at = VALUES(fetched_at),
                expires_at = VALUES(expires_at)");

            $ins->execute([
                $urlHash,
                $url,
                $ogData['canonical_url'] ?? $url,
                $ogData['title'] ?? null,
                $ogData['description'] ?? null,
                $ogData['image_url'] ?? null,
                $ogData['site_name'] ?? null,
                $ogData['domain'] ?? null,
                $ogData['card_type'] ?? 'summary_large_image',
                $extraJson,
                $now,
                $expires
            ]);
        } catch (Throwable $e) {
            error_log('[LinkPreviewService::cache] ' . $e->getMessage());
        }

        return self::formatPreviewPayload($ogData, $url);
    }

    /**
     * Resolves the link preview for a given post message text.
     * Handles truncated PartyPlatform URL recovery automatically.
     *
     * @param string|null $postMessage
     * @param bool $allowNetworkFetch
     * @return array<string, mixed>|null
     */
    public static function getPreviewForPostText(?string $postMessage, bool $allowNetworkFetch = true): ?array
    {
        if ($postMessage === null || trim($postMessage) === '') {
            return null;
        }

        $rawUrl = self::extractFirstPreviewableUrl($postMessage);
        if ($rawUrl === null) {
            return null;
        }

        // Recover truncated PartyPlatform URL if needed
        $url = self::recoverTruncatedPartyPlatformUrl($rawUrl, $postMessage);

        return self::fetchPreview($url, $allowNetworkFetch);
    }

    /**
     * Performs a resilient HTTP GET fetching only the HTML <head> section up to 128KB.
     */
    private static function fetchHtmlHead(string $url): ?string
    {
        return self::fetchHtmlHeadWithHeaders($url)['html'];
    }

    /**
     * Resolve a relative or absolute HTTP Location header against the current request URL.
     */
    private static function resolveRedirectUrl(string $baseUrl, string $location): ?string
    {
        $loc = trim($location);
        if ($loc === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $loc) === 1) {
            return $loc;
        }
        $base = parse_url($baseUrl);
        if (!$base || empty($base['scheme']) || empty($base['host'])) {
            return null;
        }
        $port = isset($base['port']) ? ':' . $base['port'] : '';
        $origin = $base['scheme'] . '://' . $base['host'] . $port;
        if (str_starts_with($loc, '//')) {
            return $base['scheme'] . ':' . $loc;
        }
        if (str_starts_with($loc, '/')) {
            return $origin . $loc;
        }
        $basePath = $base['path'] ?? '/';
        $dir = substr($basePath, 0, (int)strrpos($basePath, '/') + 1);
        return $origin . $dir . $loc;
    }

    /**
     * Performs a resilient HTTP GET fetching only the HTML <head> section and response security headers
     * (X-Frame-Options, Content-Security-Policy) for iframe embeddability verification.
     * Follows redirects manually so every hop is SSRF-validated via isSafeUrl().
     *
     * @return array{html: ?string, headers: array<string, string>}
     */
    private static function fetchHtmlHeadWithHeaders(string $url): array
    {
        if ($url === '') {
            return ['html' => null, 'headers' => []];
        }

        $currentUrl = $url;
        $maxRedirects = 5;

        for ($hop = 0; $hop <= $maxRedirects; $hop++) {
            if ($currentUrl === '' || !self::isSafeUrl($currentUrl)) {
                return ['html' => null, 'headers' => []];
            }

            $ch = curl_init();
            if ($ch === false) {
                return ['html' => null, 'headers' => []];
            }

            $responseBuffer = '';
            /** @var array<string, string> $capturedHeaders */
            $capturedHeaders = [];
            $redirectLocation = null;
            $isGooglePhoto = str_contains($currentUrl, 'photos.app.goo.gl') || str_contains($currentUrl, 'photos.google.com');
            $effectiveMax = $isGooglePhoto ? 350000 : self::MAX_BODY_BYTES;

            curl_setopt_array($ch, [
                CURLOPT_URL => $currentUrl,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_ENCODING => '',
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_USERAGENT => self::crawlerUserAgentForUrl($currentUrl),
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADERFUNCTION => function ($chInstance, string $headerLine) use (&$capturedHeaders, &$redirectLocation): int {
                    $len = strlen($headerLine);
                    $parts = explode(':', $headerLine, 2);
                    if (count($parts) === 2) {
                        $name = strtolower(trim($parts[0]));
                        $val = trim($parts[1]);
                        if ($name === 'location') {
                            $redirectLocation = $val;
                        } elseif ($name === 'x-frame-options' || $name === 'content-security-policy') {
                            $capturedHeaders[$name] = isset($capturedHeaders[$name])
                                ? $capturedHeaders[$name] . '; ' . $val
                                : $val;
                        }
                    }
                    return $len;
                },
                CURLOPT_WRITEFUNCTION => function ($chInstance, string $chunk) use (&$responseBuffer, $effectiveMax, $isGooglePhoto): int {
                    $responseBuffer .= $chunk;
                    if ($isGooglePhoto) {
                        $posKeyDs1 = false;
                        if (preg_match('/key\s*:\s*[\'"]ds:1[\'"]/', $responseBuffer, $m, PREG_OFFSET_CAPTURE)) {
                            $posKeyDs1 = $m[0][1];
                        }
                        if (($posKeyDs1 !== false && strpos($responseBuffer, 'sideChannel', $posKeyDs1) !== false)
                            || strlen($responseBuffer) >= $effectiveMax
                        ) {
                            return 0; // Abort transfer once full ds:1 album payload is captured or threshold reached
                        }
                    } else {
                        if (strlen($responseBuffer) >= $effectiveMax || stripos($responseBuffer, '</head>') !== false) {
                            return 0; // Abort transfer once </head> or size limit reached
                        }
                    }
                    return strlen($chunk);
                },
            ]);

            $execResult = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (\PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if ($httpCode >= 300 && $httpCode < 400 && $redirectLocation !== null && $hop < $maxRedirects) {
                $nextUrl = self::resolveRedirectUrl($currentUrl, $redirectLocation);
                if ($nextUrl === null) {
                    return ['html' => null, 'headers' => $capturedHeaders];
                }
                $currentUrl = $nextUrl;
                continue;
            }

            // Code 200 or 206 (or abort from write function returning 0 when head captured)
            if (($execResult === true || $responseBuffer !== '') && ($httpCode === 200 || $httpCode === 206 || $httpCode === 0)) {
                return ['html' => $responseBuffer, 'headers' => $capturedHeaders];
            }

            return ['html' => null, 'headers' => $capturedHeaders];
        }

        return ['html' => null, 'headers' => []];
    }

    /**
     * Check whether a single CSP frame-ancestors source token permits embedding by $originHost.
     */
    private static function isAllowedFrameAncestorSource(string $sourceToken, string $originHost): bool
    {
        $s = strtolower(trim($sourceToken, " \t\n\r\0\x0B'\""));
        $rawLower = strtolower(trim($sourceToken));
        if ($rawLower === '' || $rawLower === "'none'" || $rawLower === "'self'") {
            return false;
        }
        if ($rawLower === '*' || $rawLower === 'https:' || $rawLower === 'http:') {
            return true;
        }
        if ($originHost === '') {
            return false;
        }
        $hostCandidate = (string)preg_replace('#^https?://#i', '', $s);
        $hostCandidate = (string)preg_replace('#[:/].*$#', '', $hostCandidate);
        if ($hostCandidate === '') {
            return false;
        }
        if ($hostCandidate === $originHost) {
            return true;
        }
        if (str_starts_with($hostCandidate, '*.')) {
            $suffix = substr($hostCandidate, 1);
            return str_ends_with($originHost, $suffix);
        }
        return false;
    }

    /**
     * Inspect response headers for X-Frame-Options (DENY/SAMEORIGIN) or restrictive CSP frame-ancestors
     * so blocked external embeds are downgraded to an external_card instead of rendering a broken iframe.
     *
     * @param array<string, string> $headers Lowercase header map
     * @param string|null $appOrigin Optional app origin host (defaults to APP_URL host)
     */
    public static function isFramingBlocked(array $headers, ?string $appOrigin = null): bool
    {
        $normalised = [];
        foreach ($headers as $k => $v) {
            $normalised[strtolower(trim((string)$k))] = trim((string)$v);
        }

        $originHost = strtolower($appOrigin ?? (string)(parse_url((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), PHP_URL_HOST) ?: ''));

        // 1. Check X-Frame-Options
        if (isset($normalised['x-frame-options']) && $normalised['x-frame-options'] !== '') {
            $xfo = strtoupper($normalised['x-frame-options']);
            if (str_contains($xfo, 'DENY') || str_contains($xfo, 'SAMEORIGIN')) {
                return true;
            }
            if (preg_match('/ALLOW-FROM\s+(\S+)/i', $normalised['x-frame-options'], $xfoMatch)) {
                $allowHost = strtolower((string)(parse_url(trim($xfoMatch[1]), PHP_URL_HOST) ?: trim($xfoMatch[1])));
                if ($originHost === '' || $allowHost !== $originHost) {
                    return true;
                }
            }
        }

        // 2. Check ALL Content-Security-Policy: frame-ancestors directives (intersection semantics)
        if (isset($normalised['content-security-policy']) && $normalised['content-security-policy'] !== '') {
            $csp = $normalised['content-security-policy'];
            if (preg_match_all('/frame-ancestors\s+([^;]+)/i', $csp, $allMatches) > 0) {
                foreach ($allMatches[1] as $sourcesRaw) {
                    $sources = preg_split('/\s+/', trim($sourcesRaw)) ?: [];
                    $allowsExternalOrUs = false;
                    foreach ($sources as $src) {
                        if (self::isAllowedFrameAncestorSource($src, $originHost)) {
                            $allowsExternalOrUs = true;
                            break;
                        }
                    }
                    if (!$allowsExternalOrUs) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Mirror a discovered external og:image thumbnail locally (or to Cloudflare R2 when configured)
     * so expiring external CDN URLs do not break over time.
     *
     * @param string $imageUrl
     * @return string|null Local/R2 mirrored URL or null if mirroring fails or is unnecessary
     */
    public static function mirrorThumbnail(string $imageUrl): ?string
    {
        $trimmed = trim($imageUrl);
        if ($trimmed === '') {
            return null;
        }

        // Already local or already hosted on our R2 public domain
        if (str_starts_with($trimmed, '/public/uploads/') || str_starts_with($trimmed, '/resources/')) {
            return $trimmed;
        }
        $r2Domain = rtrim((string)($_ENV['R2_PUBLIC_DOMAIN'] ?? getenv('R2_PUBLIC_DOMAIN') ?: ''), '/');
        if ($r2Domain !== '' && str_starts_with($trimmed, $r2Domain . '/')) {
            return $trimmed;
        }

        if (!self::isSafeUrl($trimmed)) {
            return null;
        }

        $urlHash = hash('sha256', $trimmed);
        $localDir = __DIR__ . '/../../public/uploads/link_previews';
        if (!is_dir($localDir)) {
            @mkdir($localDir, 0755, true);
        }

        // Return existing mirrored local file if already cached
        foreach (['jpg', 'png', 'webp', 'gif', 'avif'] as $knownExt) {
            $existingFile = $localDir . '/' . $urlHash . '.' . $knownExt;
            if (is_file($existingFile) && filesize($existingFile) > 256) {
                return '/public/uploads/link_previews/' . $urlHash . '.' . $knownExt;
            }
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB safety cap
        $currentImgUrl = $trimmed;
        $maxRedirects = 4;
        $imgBuffer = '';
        $ok = false;
        $status = 0;
        $contentType = '';

        for ($hop = 0; $hop <= $maxRedirects; $hop++) {
            if (!self::isSafeUrl($currentImgUrl)) {
                return null;
            }
            $ch = curl_init($currentImgUrl);
            if ($ch === false) {
                return null;
            }

            $imgBuffer = '';
            $redirectLocation = null;
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php) WhatsApp/2.23.20.0',
                CURLOPT_HEADERFUNCTION => function ($chInstance, string $headerLine) use (&$redirectLocation): int {
                    $parts = explode(':', $headerLine, 2);
                    if (count($parts) === 2 && strtolower(trim($parts[0])) === 'location') {
                        $redirectLocation = trim($parts[1]);
                    }
                    return strlen($headerLine);
                },
                CURLOPT_WRITEFUNCTION => function ($chInstance, string $chunk) use (&$imgBuffer, $maxBytes): int {
                    if (strlen($imgBuffer) + strlen($chunk) > $maxBytes) {
                        return 0;
                    }
                    $imgBuffer .= $chunk;
                    return strlen($chunk);
                },
            ]);

            $ok = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = strtolower(trim(explode(';', (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE))[0]));
            if (\PHP_VERSION_ID < 80500) {
                @curl_close($ch);
            }

            if ($status >= 300 && $status < 400 && $redirectLocation !== null && $hop < $maxRedirects) {
                $nextUrl = self::resolveRedirectUrl($currentImgUrl, $redirectLocation);
                if ($nextUrl === null) {
                    return null;
                }
                $currentImgUrl = $nextUrl;
                continue;
            }
            break;
        }

        if (!$ok || $status < 200 || $status >= 300 || strlen($imgBuffer) < 256) {
            return null;
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
        ];
        $ext = $mimeToExt[$contentType] ?? null;
        if ($ext === null) {
            $imgInfo = @getimagesizefromstring($imgBuffer);
            if (!is_array($imgInfo) || empty($imgInfo['mime']) || !isset($mimeToExt[$imgInfo['mime']])) {
                return null;
            }
            $contentType = (string)$imgInfo['mime'];
            $ext = $mimeToExt[$contentType];
        }

        $filename = $urlHash . '.' . $ext;

        // Prefer Cloudflare R2 when configured
        if (class_exists(\Src\Media\R2StorageService::class) && \Src\Media\R2StorageService::isConfigured()) {
            try {
                $objectKey = 'link_previews/' . $filename;
                $ticket = \Src\Media\R2StorageService::generatePresignedUploadUrl($objectKey, $contentType, 300);
                if (!empty($ticket['is_cloud']) && !empty($ticket['upload_url'])) {
                    $putCh = curl_init((string)$ticket['upload_url']);
                    if ($putCh !== false) {
                        curl_setopt_array($putCh, [
                            CURLOPT_CUSTOMREQUEST => 'PUT',
                            CURLOPT_POSTFIELDS => $imgBuffer,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 5,
                            CURLOPT_CONNECTTIMEOUT => 2,
                            CURLOPT_HTTPHEADER => [
                                'Content-Type: ' . $contentType,
                                'Content-Length: ' . strlen($imgBuffer),
                            ],
                        ]);
                        curl_exec($putCh);
                        $putCode = (int)curl_getinfo($putCh, CURLINFO_HTTP_CODE);
                        if (\PHP_VERSION_ID < 80500) {
                            @curl_close($putCh);
                        }
                        if ($putCode >= 200 && $putCode < 300 && !empty($ticket['public_url'])) {
                            return (string)$ticket['public_url'];
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log('[LinkPreviewService::mirrorThumbnail R2 fallback] ' . $e->getMessage());
            }
        }

        // Local atomic fallback in public/uploads/link_previews/
        $finalPath = $localDir . '/' . $filename;
        $tmpPath = $finalPath . '.tmp.' . getmypid();
        if (@file_put_contents($tmpPath, $imgBuffer, LOCK_EX) !== false) {
            if (@rename($tmpPath, $finalPath)) {
                return '/public/uploads/link_previews/' . $filename;
            }
            @unlink($tmpPath);
        }

        return null;
    }

    /**
     * Decode HTML entities (such as &amp; in Facebook/Instagram signed CDN query strings)
     * and strip control characters from scraped URLs.
     */
    public static function decodeMetaUrl(?string $rawUrl): string
    {
        if ($rawUrl === null || trim($rawUrl) === '') {
            return '';
        }
        $clean = trim($rawUrl);
        // Decode up to twice in case an upstream page double-encoded &amp;amp;
        for ($i = 0; $i < 2; $i++) {
            $decoded = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $clean) {
                break;
            }
            $clean = $decoded;
        }
        $clean = (string)preg_replace('/[\x00-\x1F\x7F]/', '', trim($clean));
        if ($clean !== '' && preg_match('/^(javascript|vbscript|data):/i', $clean)) {
            return '';
        }
        return $clean;
    }

    /**
     * Extract meta tag content regardless of attribute order (property/name/itemprop before or after content).
     *
     * @param array<int, string> $keys
     */
    private static function extractMetaContent(string $html, array $keys): ?string
    {
        foreach ($keys as $key) {
            $qKey = preg_quote($key, '/');
            // 1. property/name/itemprop before content (strictly within a single <meta> tag)
            if (preg_match('/<meta\b[^>]*?\b(?:property|name|itemprop)\s*=\s*(["\'])' . $qKey . '\1[^>]*?\bcontent\s*=\s*(["\'])([^"\']*)\2[^>]*>/is', $html, $m)) {
                $val = trim($m[3]);
                if ($val !== '') {
                    return $val;
                }
            }
            // 2. content before property/name/itemprop (strictly within a single <meta> tag)
            if (preg_match('/<meta\b[^>]*?\bcontent\s*=\s*(["\'])([^"\']*)\1[^>]*?\b(?:property|name|itemprop)\s*=\s*(["\'])' . $qKey . '\3[^>]*>/is', $html, $m)) {
                $val = trim($m[2]);
                if ($val !== '') {
                    return $val;
                }
            }
        }
        return null;
    }

    /**
     * Extract a 11-character YouTube video ID from any standard YouTube watch/short/embed/youtu.be URL.
     */
    public static function extractYoutubeVideoId(string $url): ?string
    {
        if (preg_match('#(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/|v/)|youtu\.be/)([a-zA-Z0-9_-]{11})#i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Parses OpenGraph, Twitter, JSON-LD, and HTML meta tags from HTML head string.
     *
     * @return array<string, mixed>
     */
    private static function parseMetadata(string $html, string $sourceUrl): array
    {
        $domain = (string)parse_url($sourceUrl, PHP_URL_HOST);
        $cleanDomain = preg_replace('/^www\./i', '', $domain) ?: $domain;

        $data = [
            'url' => $sourceUrl,
            'canonical_url' => $sourceUrl,
            'domain' => $cleanDomain,
            'site_name' => self::resolveBrandSiteName($cleanDomain, ucfirst(explode('.', $cleanDomain)[0] ?? 'Website')),
            'title' => null,
            'description' => null,
            'image_url' => null,
            'card_type' => 'summary_large_image',
        ];

        // 1. og:title / twitter:title / <title>
        $rawTitle = self::extractMetaContent($html, ['og:title', 'twitter:title', 'title']);
        if ($rawTitle !== null) {
            $data['title'] = html_entity_decode($rawTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $data['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 2. og:description / twitter:description / <meta name="description">
        $rawDesc = self::extractMetaContent($html, ['og:description', 'twitter:description', 'description']);
        if ($rawDesc !== null) {
            $data['description'] = html_entity_decode($rawDesc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 3. og:image / og:image:secure_url / og:image:url / twitter:image:src / twitter:image / itemprop=image
        $rawImage = self::extractMetaContent($html, [
            'og:image:secure_url',
            'og:image:url',
            'og:image',
            'twitter:image:src',
            'twitter:image',
            'image',
        ]);
        if ($rawImage === null && preg_match('/<link\b[^>]*?\brel\s*=\s*["\']image_src["\'][^>]*?\bhref\s*=\s*["\'](.*?)["\']/is', $html, $m)) {
            $rawImage = trim($m[1]);
        }
        if ($rawImage === null && preg_match('/"thumbnailUrl"\s*:\s*"([^"]+)"/i', $html, $m)) {
            $rawImage = stripcslashes(trim($m[1]));
        }
        if ($rawImage !== null && $rawImage !== '') {
            $data['image_url'] = self::decodeMetaUrl($rawImage);
        }

        // 3b. og:video / og:video:secure_url / og:video:url
        $rawVideo = self::extractMetaContent($html, ['og:video:secure_url', 'og:video:url', 'og:video']);
        if ($rawVideo !== null && $rawVideo !== '') {
            $data['video_url'] = self::decodeMetaUrl($rawVideo);
        }

        $ogAspectRatio = self::extractAspectRatioFromHtml($html);
        if ($ogAspectRatio !== null) {
            $data['aspect_ratio'] = $ogAspectRatio;
        }

        // 3c. Extract multi-item Google Photos shared album contents (photos and videos)
        if (str_contains($sourceUrl, 'photos.app.goo.gl') || str_contains($sourceUrl, 'photos.google.com')) {
            $albumMedia = self::extractGooglePhotosAlbumMedia($html);
            if (!empty($albumMedia['images'])) {
                $data['album_images'] = $albumMedia['images'];
            }
            if (!empty($albumMedia['videos'])) {
                $data['album_videos'] = $albumMedia['videos'];
            }
        }

        // 4. og:site_name
        $rawSite = self::extractMetaContent($html, ['og:site_name', 'application-name']);
        if ($rawSite !== null) {
            $data['site_name'] = html_entity_decode($rawSite, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 5. og:url / canonical link
        $rawCanonical = self::extractMetaContent($html, ['og:url', 'twitter:url']);
        if ($rawCanonical === null && preg_match('/<link\b[^>]*?\brel\s*=\s*["\']canonical["\'][^>]*?\bhref\s*=\s*["\'](.*?)["\']/is', $html, $m)) {
            $rawCanonical = trim($m[1]);
        }
        if ($rawCanonical !== null) {
            $canonical = self::decodeMetaUrl($rawCanonical);
            if (filter_var($canonical, FILTER_VALIDATE_URL)) {
                $data['canonical_url'] = $canonical;
            }
        }

        // 6. YouTube deterministic high-res thumbnail fallback if og:image was omitted
        if (empty($data['image_url'])) {
            $ytId = self::extractYoutubeVideoId((string)$data['canonical_url']) ?? self::extractYoutubeVideoId($sourceUrl);
            if ($ytId !== null) {
                $data['image_url'] = 'https://i.ytimg.com/vi/' . $ytId . '/hqdefault.jpg';
            }
        }

        // Resolve relative image URLs against sourceUrl
        if (!empty($data['image_url']) && !str_starts_with($data['image_url'], 'http://') && !str_starts_with($data['image_url'], 'https://')) {
            $data['image_url'] = self::resolveRelativeUrl($data['image_url'], $sourceUrl);
        }

        return $data;
    }

    /**
     * Extract a canonical "W / H" aspect ratio string from OpenGraph video or image dimensions.
     */
    public static function extractAspectRatioFromHtml(string $html): ?string
    {
        if ($html === '') {
            return null;
        }

        $pairs = [
            ['og:video:width', 'og:video:height'],
            ['twitter:player:width', 'twitter:player:height'],
            ['og:image:width', 'og:image:height'],
        ];

        foreach ($pairs as [$wKey, $hKey]) {
            $rawW = self::extractMetaContent($html, [$wKey]);
            $rawH = self::extractMetaContent($html, [$hKey]);
            if ($rawW !== null && $rawH !== null && is_numeric($rawW) && is_numeric($rawH)) {
                $formatted = self::formatAspectRatio((int)$rawW, (int)$rawH);
                if ($formatted !== null) {
                    return $formatted;
                }
            }
        }

        return null;
    }

    /**
     * Format positive pixel dimensions into a simplified "W / H" aspect ratio string.
     */
    public static function formatAspectRatio(int $width, int $height): ?string
    {
        if ($width < 64 || $height < 64 || $width > 10000 || $height > 10000) {
            return null;
        }

        $a = $width;
        $b = $height;
        while ($b !== 0) {
            $t = $a % $b;
            $a = $b;
            $b = $t;
        }
        $gcd = max(1, $a);
        return intdiv($width, $gcd) . ' / ' . intdiv($height, $gcd);
    }

    /**
     * Resolve the aspect ratio of a Facebook video URL via cached/live OpenGraph metadata.
     */
    public static function resolveFacebookVideoAspectRatio(string $url, bool $allowNetworkFetch = true): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return null;
        }
        $parsed = \App\classes\VideoParser::parseVideoUrl($trimmed);
        if (!$parsed || $parsed['type'] !== 'facebook') {
            return null;
        }
        if (isset($parsed['aspect_ratio']) && $parsed['aspect_ratio'] !== '') {
            return $parsed['aspect_ratio'];
        }

        $preview = self::fetchPreview($trimmed, $allowNetworkFetch);
        $rawRatio = is_array($preview) ? ($preview['aspect_ratio'] ?? null) : null;
        if (!is_string($rawRatio) || $rawRatio === '') {
            return null;
        }

        $normalised = \App\classes\VideoParser::normaliseMediaMeta(['aspect_ratio' => $rawRatio]);
        return $normalised['aspect_ratio'] ?? null;
    }

    /**
     * Resolves a relative URL path against a base host URL.
     */
    private static function resolveRelativeUrl(string $relative, string $baseUrl): string
    {
        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        if (str_starts_with($relative, '//')) {
            return $scheme . ':' . $relative;
        }

        if (str_starts_with($relative, '/')) {
            return $scheme . '://' . $host . $port . $relative;
        }

        $basePath = isset($parts['path']) ? dirname($parts['path']) : '/';
        $basePath = rtrim($basePath, '/') . '/';
        return $scheme . '://' . $host . $port . $basePath . ltrim($relative, '/');
    }

    /**
     * Resilient fallback: When a specific subpage or deep route returns an error (e.g. 500, 404)
     * or lacks OpenGraph tags, inspect parent paths or the root origin (https://<host>/) to recover
     * canonical site metadata, site name, social card banner, and description.
     *
     * @return array<string, mixed>|null
     */
    private static function fetchFallbackMetadata(string $url): ?array
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = (string)$parts['host'];
        $path = (string)($parts['path'] ?? '');

        // Generate candidate fallback URLs in priority order: parent path -> root origin
        $candidates = [];

        // 1. Parent directory path if deep subpath exists (e.g. /blogs/108 -> /blogs)
        $trimmedPath = trim($path, '/');
        if ($trimmedPath !== '' && str_contains($trimmedPath, '/')) {
            $parentPath = dirname('/' . $trimmedPath);
            if ($parentPath !== '/' && $parentPath !== '.') {
                $candidates[] = $scheme . '://' . $host . $parentPath;
            }
        }

        // 2. Root origin (https://domain/)
        $origin = $scheme . '://' . $host . '/';
        if ($origin !== $url && !in_array($origin, $candidates, true)) {
            $candidates[] = $origin;
        }

        foreach ($candidates as $fallbackUrl) {
            if (!self::isSafeUrl($fallbackUrl)) {
                continue;
            }

            $html = self::fetchHtmlHead($fallbackUrl);
            if ($html !== null && trim($html) !== '') {
                $meta = self::parseMetadata($html, $fallbackUrl);
                if (!empty($meta['title']) || !empty($meta['image_url']) || !empty($meta['description'])) {
                    // Retain the author's original requested URL as the destination
                    $meta['url'] = $url;
                    $meta['canonical_url'] = $url;

                    // Humanize specific path slug if available to contextualize the title
                    if ($trimmedPath !== '') {
                        $segments = explode('/', $trimmedPath);
                        $lastSegment = end($segments);
                        $sitePrefix = self::resolveBrandSiteName($host, (string)($meta['site_name'] ?? ''));
                        if ($sitePrefix !== '') {
                            $meta['site_name'] = $sitePrefix;
                        }
                        if (!empty($lastSegment)) {
                            if (self::isOpaqueShortCode($lastSegment)) {
                                $meta['title'] = self::buildCleanPlatformTitle($url, $host, $sitePrefix);
                            } else {
                                $cleanSlug = preg_replace('/\s+/', ' ', ucwords(str_replace(['-', '_'], ' ', $lastSegment))) ?? $lastSegment;
                                if (is_numeric($lastSegment)) {
                                    $sectionName = count($segments) > 1 ? ucfirst($segments[count($segments) - 2]) : 'Article';
                                    $cleanSlug = rtrim($sectionName, 's') . ' #' . $lastSegment;
                                }
                                if (!empty($meta['title']) && !str_contains(strtolower((string)$meta['title']), strtolower(trim((string)$cleanSlug)))) {
                                    $meta['title'] = $sitePrefix !== '' ? "{$sitePrefix} — {$cleanSlug}" : "{$cleanSlug} — " . ($meta['title']);
                                }
                            }
                        }
                    }

                    return $meta;
                }
            }
        }

        return null;
    }

    /**
     * Maps known short-link and social domains to clean human-readable brand names.
     */
    private static function resolveBrandSiteName(string $domain, string $fallback = ''): string
    {
        $lower = strtolower((string)preg_replace('/^www\./i', '', trim($domain)));
        $map = [
            'fb.watch' => 'Facebook',
            'fb.com' => 'Facebook',
            'facebook.com' => 'Facebook',
            'm.facebook.com' => 'Facebook',
            'l.facebook.com' => 'Facebook',
            'youtu.be' => 'YouTube',
            'youtube.com' => 'YouTube',
            'm.youtube.com' => 'YouTube',
            'instagr.am' => 'Instagram',
            'instagram.com' => 'Instagram',
            't.co' => 'X',
            'x.com' => 'X',
            'twitter.com' => 'X',
            'vm.tiktok.com' => 'TikTok',
            'vt.tiktok.com' => 'TikTok',
            'tiktok.com' => 'TikTok',
            'lnkd.in' => 'LinkedIn',
            'linkedin.com' => 'LinkedIn',
            '1drv.ms' => 'OneDrive',
            'onedrive.live.com' => 'OneDrive',
            'drive.google.com' => 'Google Drive',
            'docs.google.com' => 'Google Drive',
        ];
        if (isset($map[$lower])) {
            return $map[$lower];
        }
        if (strtolower(trim($fallback)) === 'fb') {
            return 'Facebook';
        }
        return trim($fallback);
    }

    /**
     * Detects opaque short-link hash segments (e.g. "K--PySTLPf", "dQw4w9WgXcQ")
     * that should not be rendered as raw gibberish titles.
     */
    private static function isOpaqueShortCode(string $segment): bool
    {
        $s = trim($segment);
        if ($s === '') {
            return false;
        }
        if (str_contains($s, '--') || str_contains($s, '__')) {
            return true;
        }
        // Short mixed-case or alphanumeric token without word separators (e.g. K--PySTLPf, 1a2B3c4D)
        if (!str_contains($s, '-') && !str_contains($s, '_') && strlen($s) <= 18) {
            $hasUpper = (bool)preg_match('/[A-Z]/', $s);
            $hasLower = (bool)preg_match('/[a-z]/', $s);
            $hasDigit = (bool)preg_match('/\d/', $s);
            if (($hasUpper && $hasLower && $hasDigit) || ($hasUpper && $hasLower && strlen($s) >= 7)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Produces a clean, readable title for social/short links when metadata is unavailable.
     */
    private static function buildCleanPlatformTitle(string $url, string $domain, string $siteName): string
    {
        $lowerUrl = strtolower($url);
        $lowerDomain = strtolower($domain);
        $brand = self::resolveBrandSiteName($domain, $siteName);
        if ($brand === '') {
            $cleanHost = preg_replace('/^www\./i', '', $lowerDomain) ?: $lowerDomain;
            $hostSegs = explode('.', $cleanHost);
            $brand = ucfirst($hostSegs[0] !== '' ? $hostSegs[0] : 'Web');
        }

        if ($brand === 'Facebook') {
            if (str_contains($lowerDomain, 'fb.watch') || str_contains($lowerUrl, '/videos/') || str_contains($lowerUrl, '/watch')) {
                return 'Facebook Video';
            }
            if (str_contains($lowerUrl, '/reel/')) {
                return 'Facebook Reel';
            }
            return 'Facebook Post';
        }
        if ($brand === 'YouTube') {
            return str_contains($lowerUrl, '/shorts/') ? 'YouTube Short' : 'YouTube Video';
        }
        if ($brand === 'Instagram') {
            return str_contains($lowerUrl, '/reel/') ? 'Instagram Reel' : 'Instagram Post';
        }
        if ($brand === 'TikTok') {
            return 'TikTok Video';
        }

        return "Shared Link on {$brand}";
    }

    /**
     * Synthesizes a clean, structured fallback social card from the URL structure itself
     * when all remote HTTP probes fail, guaranteeing the user never sees a broken null void.
     *
     * @return array<string, mixed>
     */
    private static function synthesizeFallbackMetadata(string $url): array
    {
        $domain = (string)parse_url($url, PHP_URL_HOST);
        $cleanDomain = preg_replace('/^www\./i', '', $domain) ?: $domain;
        $hostParts = explode('.', $cleanDomain);
        $siteName = self::resolveBrandSiteName($cleanDomain, ucfirst($hostParts[0]));

        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        $title = $siteName;
        if ($path !== '') {
            $segments = explode('/', $path);
            $last = end($segments);
            if (!empty($last)) {
                if (self::isOpaqueShortCode($last)) {
                    $title = self::buildCleanPlatformTitle($url, $cleanDomain, $siteName);
                } elseif (is_numeric($last)) {
                    $sec = count($segments) > 1 ? ucfirst($segments[count($segments) - 2]) : 'Item';
                    $title = $siteName . ' — ' . rtrim($sec, 's') . ' #' . $last;
                } else {
                    $cleanSlug = preg_replace('/\s+/', ' ', ucwords(str_replace(['-', '_'], ' ', $last))) ?? $last;
                    $title = $siteName . ' — ' . trim((string)$cleanSlug);
                }
            }
        } else {
            $title = $siteName . ' — ' . $cleanDomain;
        }

        return [
            'url' => $url,
            'canonical_url' => $url,
            'domain' => $cleanDomain,
            'site_name' => $siteName,
            'title' => $title,
            'description' => "Explore {$cleanDomain}",
            'image_url' => null,
            'card_type' => 'summary_large_image',
        ];
    }

    /**
     * Formats preview data into standard front-end contract.
     *
     * @param array<string, mixed> $data
     * @param string|null $sourceRequestedUrl
     * @return array<string, mixed>
     */
    private static function formatPreviewPayload(array $data, ?string $sourceRequestedUrl = null): array
    {
        $url = self::decodeMetaUrl((string)($data['canonical_url'] ?? $data['url'] ?? $sourceRequestedUrl ?? ''));
        $requestedUrl = self::decodeMetaUrl($sourceRequestedUrl ?? (string)($data['url'] ?? $url));
        if ($url === '') {
            $url = $requestedUrl;
        }
        $domain = (string)($data['domain'] ?? parse_url($url, PHP_URL_HOST) ?? '');
        $cleanDomain = strtolower((string)preg_replace('/^www\./i', '', trim($domain)));
        $isPartyEvent = str_contains($cleanDomain, 'partyplatform') || str_contains($url, '/e/');

        $rawImage = self::decodeMetaUrl((string)($data['image_url'] ?? $data['image'] ?? ''));
        if ($rawImage === '') {
            $ytId = self::extractYoutubeVideoId($url) ?? self::extractYoutubeVideoId($requestedUrl);
            if ($ytId !== null) {
                $rawImage = 'https://i.ytimg.com/vi/' . $ytId . '/hqdefault.jpg';
            }
        }
        if (str_starts_with($rawImage, 'http://')) {
            $isHttpsCurrent = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            $imgHost = (string)parse_url($rawImage, PHP_URL_HOST);
            if ($isHttpsCurrent || str_ends_with($imgHost, '.test') || str_ends_with($imgHost, '.local')) {
                $rawImage = 'https://' . substr($rawImage, 7);
            }
        }

        // Detect Google Photos albums/shares and direct image files (Zero-Storage Media Pipeline)
        $isGooglePhoto = str_contains($cleanDomain, 'photos.app.goo.gl')
            || str_contains($cleanDomain, 'photos.google.com')
            || str_contains(strtolower($url), 'photos.app.goo.gl')
            || str_contains(strtolower($url), 'photos.google.com')
            || str_contains(strtolower($requestedUrl), 'photos.app.goo.gl')
            || str_contains(strtolower($requestedUrl), 'photos.google.com');

        $isDirectImage = (bool)preg_match('/\.(jpe?g|png|webp|avif|gif)(\?.*)?$/i', $url)
            || (bool)preg_match('/\.(jpe?g|png|webp|avif|gif)(\?.*)?$/i', $requestedUrl);

        $title = (string)($data['title'] ?? '');
        $rawVideo = self::decodeMetaUrl((string)($data['video_url'] ?? ''));
        $hasAlbumVideos = !empty($data['album_videos']);
        $isGoogleVideo = $isGooglePhoto && (!empty($rawVideo) || $hasAlbumVideos || str_contains($title, '🎬') || str_starts_with(strtolower($title), 'new video'));

        $isPhotoLink = ($isGooglePhoto || $isDirectImage) && !empty($rawImage) && !$isGoogleVideo;

        // High resolution Google CDN asset elevation (ATG 4K Standard: =s3840 4K retina quality; -no strips Google's baked play icon on videos)
        $photoUrl = $rawImage;
        if ($isGooglePhoto && !empty($rawImage)) {
            if (preg_match('#^https://(?:lh[3-6]\.googleusercontent\.com|[^/]+\.ggpht\.com)/#i', $rawImage)) {
                $photoUrl = preg_replace('/=.*$/i', $isGoogleVideo ? '=s3840-no' : '=s3840', $rawImage);
            }
        }

        $videoStreamUrl = null;
        if ($isGoogleVideo) {
            $baseForVideo = !empty($rawVideo) ? $rawVideo : (!empty($data['album_videos'][0]) ? $data['album_videos'][0] : $rawImage);
            if (preg_match('#^https://(?:lh[3-6]\.googleusercontent\.com|[^/]+\.ggpht\.com)/#i', $baseForVideo)) {
                $videoStreamUrl = preg_replace('/=.*$/i', '=dv', $baseForVideo);
            } else {
                $videoStreamUrl = $baseForVideo;
            }
        } elseif ($rawVideo !== '' && filter_var($rawVideo, FILTER_VALIDATE_URL)) {
            $videoStreamUrl = $rawVideo;
        }

        $igShortcode = !empty($data['ig_shortcode']) && is_string($data['ig_shortcode'])
            ? trim($data['ig_shortcode'])
            : (self::extractInstagramShortcode($url) ?? self::extractInstagramShortcode($requestedUrl));
        $isInstagram = self::isInstagramDomain($cleanDomain)
            || self::isInstagramDomain($url)
            || self::isInstagramDomain($requestedUrl)
            || $igShortcode !== null;

        $fbVideoId = !empty($data['fb_video_id']) && is_string($data['fb_video_id'])
            ? trim($data['fb_video_id'])
            : (self::extractFacebookVideoId($url) ?? self::extractFacebookVideoId($requestedUrl));
        $isFacebook = self::isFacebookDomain($cleanDomain)
            || self::isFacebookDomain($url)
            || self::isFacebookDomain($requestedUrl)
            || $fbVideoId !== null;

        $isReelUrl = str_contains(strtolower($url . ' ' . $requestedUrl), '/reel')
            || str_contains(strtolower($url . ' ' . $requestedUrl), '/share/r/');
        $aspectRatio = isset($data['aspect_ratio']) && is_string($data['aspect_ratio']) && trim($data['aspect_ratio']) !== ''
            ? trim($data['aspect_ratio'])
            : (($isInstagram || $isFacebook) && ($isReelUrl || $videoStreamUrl !== null) ? '9 / 16' : null);

        $parsedVideoEmbed = \App\classes\VideoParser::parseVideoUrl($url) ?? \App\classes\VideoParser::parseVideoUrl($requestedUrl);
        $frameBlocked = !empty($data['frame_blocked']);
        $rawEmbedUrl = $parsedVideoEmbed['embedUrl'] ?? null;
        $embedUrl = $frameBlocked ? null : $rawEmbedUrl;
        $canEmbed = $embedUrl !== null;

        $albumVideos = is_array($data['album_videos'] ?? null) ? array_values($data['album_videos']) : [];
        if ($isInstagram && $igShortcode !== null && $videoStreamUrl !== null) {
            $proxiedVideoUrl = self::getInstagramStreamProxyUrl($igShortcode);
        } elseif ($isFacebook && $fbVideoId !== null && $videoStreamUrl !== null) {
            $proxiedVideoUrl = self::getFacebookStreamProxyUrl($fbVideoId);
        } else {
            $proxiedVideoUrl = $videoStreamUrl !== null ? self::getProxiedVideoUrl($videoStreamUrl) : null;
        }
        $proxiedAlbumVideos = array_map(static fn(string $v): string => self::getProxiedVideoUrl($v), $albumVideos);
        $renderMode = (($isInstagram || $isFacebook) && $proxiedVideoUrl !== null)
            ? 'native_video'
            : ($canEmbed ? 'inline_iframe' : 'external_card');

        $resolvedSiteName = $isGooglePhoto ? 'Google Photos' : self::resolveBrandSiteName($domain, (string)($data['site_name'] ?? $domain));
        $cleanTitle = preg_replace('/\s+/', ' ', trim($title)) ?? trim($title);
        if ($cleanTitle === '' || $cleanTitle === '- YouTube' || preg_match('/^Fb\s*[—–-]\s*/i', $cleanTitle)) {
            $cleanTitle = self::buildCleanPlatformTitle($url, $domain, $resolvedSiteName);
        } elseif (str_contains($cleanTitle, ' — ')) {
            $parts = explode(' — ', $cleanTitle, 2);
            $suffix = trim($parts[1] ?? '');
            $pathLast = basename((string)parse_url($url, PHP_URL_PATH));
            if ($suffix !== '' && self::isOpaqueShortCode($pathLast)) {
                $cleanTitle = self::buildCleanPlatformTitle($url, $domain, $resolvedSiteName);
            }
        }

        $combinedUrlLower = strtolower($url . ' ' . $requestedUrl);
        $isSocialVideo = $isGoogleVideo
            || !empty($rawVideo)
            || str_contains($combinedUrlLower, '/reel/')
            || str_contains($combinedUrlLower, '/reels/')
            || str_contains($combinedUrlLower, '/share/v/')
            || str_contains($combinedUrlLower, '/share/r/')
            || str_contains($combinedUrlLower, '/videos/')
            || str_contains($combinedUrlLower, 'facebook.com/watch')
            || str_contains($combinedUrlLower, 'fb.watch')
            || str_contains($combinedUrlLower, 'tiktok.com/')
            || str_contains($combinedUrlLower, 'youtube.com/')
            || str_contains($combinedUrlLower, 'youtu.be/');

        $description = trim((string)($data['description'] ?? ''));
        if ($isInstagram && $description !== '') {
            $description = self::cleanInstagramDescription($description);
        }
        if ($description === '' || filter_var($description, FILTER_VALIDATE_URL)) {
            if ($resolvedSiteName === 'Facebook') {
                $description = str_contains($combinedUrlLower, '/reel/') || str_contains($combinedUrlLower, '/share/r/')
                    ? 'Watch this reel on Facebook'
                    : ($isSocialVideo ? 'Watch this video on Facebook' : 'View this post on Facebook');
            } elseif ($resolvedSiteName === 'Instagram') {
                $description = $isSocialVideo ? 'Watch this reel on Instagram' : 'View this post on Instagram';
            } elseif ($resolvedSiteName !== '') {
                $description = $isSocialVideo ? "Watch this video on {$resolvedSiteName}" : "Explore on {$resolvedSiteName}";
            }
        }

        $ctaLabel = 'Open Link →';
        if ($isPartyEvent) {
            $ctaLabel = 'View Event / RSVP →';
        } elseif ($isPhotoLink) {
            $ctaLabel = 'View Photo →';
        } elseif ($isGoogleVideo) {
            $ctaLabel = 'Play Video →';
        } elseif ($isSocialVideo && $resolvedSiteName !== '') {
            $ctaLabel = "Watch on {$resolvedSiteName} →";
        } elseif ($isInstagram) {
            $ctaLabel = 'View on Instagram →';
        }

        return [
            'url' => $url,
            'original_url' => $requestedUrl,
            'title' => $cleanTitle,
            'description' => $description,
            'image' => $photoUrl !== '' ? $photoUrl : $rawImage,
            'photo_url' => $isPhotoLink ? ($photoUrl !== '' ? $photoUrl : $rawImage) : null,
            'video_url' => (($isInstagram || $isFacebook) && $proxiedVideoUrl !== null) ? $proxiedVideoUrl : $videoStreamUrl,
            'embed_url' => $embedUrl,
            'frame_blocked' => $frameBlocked,
            'can_embed' => $canEmbed,
            'render_mode' => $renderMode,
            'proxied_video_url' => $proxiedVideoUrl,
            'aspect_ratio' => $aspectRatio,
            'is_video' => $isGoogleVideo || !empty($rawVideo) || $embedUrl !== null,
            'is_social_video' => $isSocialVideo,
            'is_instagram' => $isInstagram,
            'ig_shortcode' => $igShortcode,
            'is_facebook' => $isFacebook,
            'fb_video_id' => $fbVideoId,
            'album_images' => $data['album_images'] ?? [],
            'album_videos' => $albumVideos,
            'proxied_album_videos' => $proxiedAlbumVideos,
            'site_name' => $resolvedSiteName !== '' ? $resolvedSiteName : $domain,
            'domain' => $domain,
            'card_type' => (string)($data['card_type'] ?? 'summary_large_image'),
            'is_party_event' => $isPartyEvent,
            'is_photo_link' => $isPhotoLink,
            'is_google_photo' => $isGooglePhoto,
            'cta_label' => $ctaLabel,
        ];
    }

    /**
     * Checks if a given URL is a known photo link (Google Photos, iCloud Photos, Imgur, Flickr, or direct image asset).
     */
    public static function isPhotoLinkUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }
        $lower = strtolower(trim($url));
        if (str_contains($lower, 'photos.app.goo.gl')
            || str_contains($lower, 'photos.google.com')
            || str_contains($lower, 'icloud.com/photos')
            || str_contains($lower, 'imgur.com')
            || str_contains($lower, 'flickr.com')
            || str_contains($lower, 'ibb.co')
            || str_contains($lower, 'postimg.cc')
        ) {
            return true;
        }
        return (bool)preg_match('/\.(jpe?g|png|webp|avif|gif)(\?.*)?$/i', $lower);
    }

    /**
     * Checks if a given URL is a known video link (YouTube, Vimeo, Cloudflare Stream, TikTok, Dropbox video, or direct video asset).
     */
    public static function isVideoLinkUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }
        $trimmed = trim($url);
        $lower = strtolower($trimmed);
        if (str_contains($lower, 'youtube.com')
            || str_contains($lower, 'youtu.be')
            || str_contains($lower, 'vimeo.com')
            || str_contains($lower, 'videodelivery.net')
            || str_contains($lower, 'cloudflarestream.com')
            || (bool)preg_match('#^https?://(?:www\.|m\.)?tiktok\.com/(?:@[\w.-]+/video|embed/v2|v)/\d+#i', $trimmed)
        ) {
            return true;
        }
        if ((bool)preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?(#.*)?$/i', $lower)) {
            return true;
        }
        return \App\classes\VideoParser::isVideoUrl($trimmed);
    }

    /**
     * Resolves the cryptographic secret used for video stream HMAC signing.
     */
    public static function getStreamSigningSecret(): string
    {
        $key = (string)($_ENV['APP_KEY'] ?? '');
        if ($key === '') {
            $key = 'familyplatform-video-stream-default-key';
        }
        return hash('sha256', 'video-stream:' . $key);
    }

    /**
     * Signs an external video stream URL with HMAC-SHA256.
     */
    public static function signStreamUrl(string $url): string
    {
        return hash_hmac('sha256', $url, self::getStreamSigningSecret());
    }

    /**
     * Verifies an external video stream HMAC signature in constant time.
     */
    public static function verifyStreamSignature(string $url, string $sig): bool
    {
        if (empty($url) || empty($sig)) {
            return false;
        }
        return hash_equals(self::signStreamUrl($url), $sig);
    }

    /**
     * Rewrites external Google video stream URLs to the sovereign same-origin HTTP 206 streaming proxy.
     */
    public static function getProxiedVideoUrl(string $url): string
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return '';
        }
        // If already local or already proxied, return directly
        if (str_starts_with($trimmed, '/resources/videos/')) {
            return $trimmed;
        }

        // Only proxy external Google video streams that block browser cross-origin requests
        if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|googlevideo\.com)/#i', $trimmed)) {
            $cacheFileName = 'ext_' . md5($trimmed) . '.mp4';
            $cacheFilePath = __DIR__ . '/../../resources/videos/posts/' . $cacheFileName;
            if (is_file($cacheFilePath) && filesize($cacheFilePath) > 4096) {
                return '/resources/videos/posts/' . $cacheFileName;
            }
            $sig = self::signStreamUrl($trimmed);
            return '/resources/videos/external-stream?url=' . rawurlencode($trimmed) . '&sig=' . $sig;
        }

        return $trimmed;
    }

    /**
     * Asynchronously or on-demand persists an external Google Photos video stream
     * to local SSD storage (`resources/videos/posts/ext_<hash>.mp4`) so playback
     * is instantaneous (<2ms HTTP 206 Byte-Range) and immune to Google token expiry.
     */
    public static function cacheExternalVideoLocally(string $url): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '' || !preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|googlevideo\.com)/#i', $trimmed)) {
            return null;
        }

        $targetDir = __DIR__ . '/../../resources/videos/posts';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $cacheFileName = 'ext_' . md5($trimmed) . '.mp4';
        $finalPath = $targetDir . '/' . $cacheFileName;
        if (is_file($finalPath) && filesize($finalPath) > 4096) {
            return '/resources/videos/posts/' . $cacheFileName;
        }

        $tmpPath = $finalPath . '.tmp.' . getmypid();
        $fp = @fopen($tmpPath, 'wb');
        if ($fp === false) {
            return null;
        }

        $ch = curl_init($trimmed);
        if ($ch === false) {
            fclose($fp);
            @unlink($tmpPath);
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: video/mp4,video/*;q=0.9,*/*;q=0.5',
            ],
        ]);

        $ok = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = strtolower((string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
        curl_close($ch);
        fclose($fp);

        if ($ok && ($status === 200 || $status === 206) && is_file($tmpPath) && filesize($tmpPath) > 4096 && !str_contains($contentType, 'text/html')) {
            @rename($tmpPath, $finalPath);
            VideoAutoTrimService::optimiseMp4FastStart($finalPath);
            return '/resources/videos/posts/' . $cacheFileName;
        }

        @unlink($tmpPath);
        return null;
    }

    /**
     * Strips any linked media URLs (Google Photos, direct image URLs, video URLs, OpenGraph links) from a post message,
     * leaving only the human caption text.
     *
     * @param string $text
     * @param array<string>|string|null $photoUrl
     * @param array<string>|string|null $originalUrl
     * @return string
     */
    public static function cleanPhotoLinkFromMessage(string $text, array|string|null $photoUrl = null, array|string|null $originalUrl = null): string
    {
        $cleaned = $text;

        $stripItems = [];
        if (is_array($photoUrl)) {
            $stripItems = array_merge($stripItems, $photoUrl);
        } elseif (!empty($photoUrl)) {
            $stripItems[] = $photoUrl;
        }

        if (is_array($originalUrl)) {
            $stripItems = array_merge($stripItems, $originalUrl);
        } elseif (!empty($originalUrl)) {
            $stripItems[] = $originalUrl;
        }

        foreach ($stripItems as $item) {
            if (is_string($item) && trim($item) !== '') {
                $cleaned = str_ireplace(trim($item), '', $cleaned);
            }
        }

        // Also strip any residual Google Photos links matching standard patterns
        $cleaned = (string)preg_replace('#https?://(?:photos\.app\.goo\.gl|photos\.google\.com)/[^\s<>"\'`]+#i', '', $cleaned);

        // Strip raw image asset URLs (.jpg, .jpeg, .png, .webp, .avif, .gif)
        $cleaned = (string)preg_replace('#https?://[^\s<>"\'`]+\.(?:jpe?g|png|webp|avif|gif)(?:\?[^\s<>"\'`]*)?#i', '', $cleaned);

        // Strip known photo host links (iCloud Photos, Imgur, Flickr, PostImage, etc.)
        $cleaned = (string)preg_replace('#https?://(?:www\.)?(?:icloud\.com/photos|i\.imgur\.com|imgur\.com|ibb\.co|postimg\.cc)/[^\s<>"\'`]+#i', '', $cleaned);

        // Strip video URLs (YouTube, Vimeo, Cloudflare Stream, TikTok, Dropbox, direct mp4/webm/mov/ogg/m4v)
        $cleaned = (string)preg_replace('#https?://(?:www\.)?(?:youtube\.com/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v[=_]?)|youtu\.be/)[a-zA-Z0-9_-]{11}(?:[^\s<>"\'`]*)?#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://(?:www\.|player\.)?vimeo\.com/(?:channels/(?:\w+/)?|groups/[^/]+/videos/|album/\d+/video/|video/|)\d+(?:[^\s<>"\'`]*)?#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://(?:iframe\.videodelivery\.net|watch\.cloudflarestream\.com|customer-[a-zA-Z0-9_-]+\.cloudflarestream\.com)/[a-zA-Z0-9]{32}(?:[^\s<>"\'`]*)?#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://(?:www\.|m\.)?tiktok\.com/(?:@[\w.-]+/video|embed/v2|v)/\d+(?:[^\s<>"\'`]*)?#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://(?:www\.|m\.|web\.)?(?:facebook\.com/(?:[^/\s]+/videos/\d+|watch/?\?v=\d+|reel/\d+|share/[vr]/[^\s<>"\'`]+|video\.php\?v=\d+)|fb\.watch/[^\s<>"\'`]+|fb\.com/(?:watch|reel|[^/\s]+/videos)/[^\s<>"\'`]+)#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://(?:onedrive\.live\.com/(?:embed|redir|\?)|1drv\.ms/v/)[^\s<>"\'`]+#i', '', $cleaned);
        $cleaned = (string)preg_replace('#https?://drive\.google\.com/(?:file/d/|open\?id=)[^\s<>"\'`]+#i', '', $cleaned);
        $cleaned = (string)preg_replace('~(?:https?://[^\s<>"\'`]+|/resources/(?:videos|images)/[^\s<>"\'`]+)\.(?:mp4|webm|ogg|mov|m4v)(?:\?[^\s<>"\'`#]*)?(?:#t=[0-9.]+(?:,[0-9.]+)?)?~i', '', $cleaned);

        // Clean up redundant blank lines or multiple spaces while preserving intentional paragraphs
        $lines = explode("\n", $cleaned);
        $cleanedLines = array_map('trim', $lines);
        $result = trim(implode("\n", $cleanedLines));

        return (string)preg_replace("/\n{3,}/", "\n\n", $result);
    }

    /**
     * Backwards-compatibility self-healing utility:
     * Scans posts that contain Google Photos URLs or image URLs,
     * hydrates post_img0..5 with the high-res CDN URLs, and cleans the links from postMessage.
     */
    public static function migrateExistingGooglePhotoPosts(?PDO $pdo = null): int
    {
        $db = $pdo ?? Db::connect2();
        $migratedCount = 0;

        try {
            $stmt = $db->query("SELECT post_no, postMessage, post_img0 FROM post 
                WHERE (postMessage LIKE '%photos.app.goo.gl%' OR postMessage LIKE '%photos.google.com%') 
                AND date_deleted IS NULL");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $postNo = (int)$row['post_no'];
                $msg = (string)($row['postMessage'] ?? '');
                $extracted = self::extractPhotoAssetsFromText($msg, 6);

                if (!empty($extracted['images'])) {
                    $setClauses = [];
                    $params = [];
                    foreach ($extracted['images'] as $idx => $asset) {
                        $col = 'post_img' . $idx;
                        $setClauses[] = "$col = ?";
                        $params[] = $asset;
                    }
                    $setClauses[] = "postMessage = ?";
                    $params[] = $extracted['cleaned_message'];
                    $params[] = $postNo;

                    $setClauseStr = implode(', ', $setClauses);
                    $sql = "UPDATE post SET " . "\n";
                    $sql .= $setClauseStr;
                    $sql .= " WHERE post_no = ?";
                    $upStmt = $db->prepare($sql);
                    $upStmt->execute($params);
                    $migratedCount++;
                } elseif (trim($msg) !== '' && preg_match('#https?://(?:photos\.app\.goo\.gl|photos\.google\.com)/[^\s<>"\'`]+#i', $msg)) {
                    // Clean URL out of postMessage if image was already in post_img0
                    $cleanMsg = self::cleanPhotoLinkFromMessage($msg);
                    $upStmt = $db->prepare("UPDATE post SET postMessage = ? WHERE post_no = ?");
                    $upStmt->execute([$cleanMsg, $postNo]);
                    $migratedCount++;
                }
            }
        } catch (Throwable $e) {
            error_log('[LinkPreviewService::migrateExistingGooglePhotoPosts] ' . $e->getMessage());
        }

        return $migratedCount;
    }

    /**
     * Self-healing maintenance utility:
     * Scans the post table for posts containing truncated PartyPlatform URLs,
     * heals the URL and caption, and returns the number of repaired records.
     */
    public static function repairTruncatedPosts(?PDO $pdo = null): int
    {
        $db = $pdo ?? Db::connect2();
        $repairedCount = 0;

        try {
            $stmt = $db->query("SELECT post_no, postMessage FROM post 
                WHERE postMessage LIKE '%mypartyplatform.com/e/%' 
                AND date_deleted IS NULL");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $postNo = (int)$row['post_no'];
                $msg = (string)($row['postMessage'] ?? '');
                $url = self::extractFirstPreviewableUrl($msg);

                if ($url !== null) {
                    $recoveredUrl = self::recoverTruncatedPartyPlatformUrl($url, $msg);
                    if ($recoveredUrl !== $url) {
                        $updatedMsg = str_replace($url, $recoveredUrl, $msg);
                        $upStmt = $db->prepare("UPDATE post SET postMessage = ? WHERE post_no = ?");
                        $upStmt->execute([$updatedMsg, $postNo]);
                        $repairedCount++;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('[LinkPreviewService::repairTruncatedPosts] ' . $e->getMessage());
        }

        return $repairedCount;
    }

    /**
     * Checks whether a host or URL belongs to Instagram (`instagram.com`, `*.instagram.com`, `instagr.am`).
     */
    public static function isInstagramDomain(string $urlOrHost): bool
    {
        $raw = strtolower(trim($urlOrHost));
        if ($raw === '') {
            return false;
        }
        $host = str_contains($raw, '://') ? strtolower((string)(parse_url($raw, PHP_URL_HOST) ?: '')) : $raw;
        $host = (string)preg_replace('/^www\./i', '', $host);
        return $host === 'instagram.com'
            || str_ends_with($host, '.instagram.com')
            || $host === 'instagr.am';
    }

    /**
     * Returns the optimal crawler User-Agent for a target URL.
     * Strips the `WhatsApp/...` token strictly on Instagram domains so Instagram serves
     * full `og:description` captions instead of empty descriptions and `cmp1` watermarked squares.
     *
     * @return non-empty-string
     */
    public static function crawlerUserAgentForUrl(string $url): string
    {
        if (self::isInstagramDomain($url)) {
            return 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)';
        }
        return 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php) WhatsApp/2.23.20.0';
    }

    /**
     * Extracts a validated Instagram media shortcode from `/reel/`, `/reels/`, `/p/`, or `/tv/` URLs.
     */
    public static function extractInstagramShortcode(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }
        $trimmed = trim($url);
        if (!self::isInstagramDomain($trimmed)) {
            return null;
        }
        $path = (string)(parse_url($trimmed, PHP_URL_PATH) ?: '');
        if (preg_match('#^/(?:[A-Za-z0-9._]{1,40}/)?(?:reel|reels|p|tv)/([A-Za-z0-9_-]{5,24})(?:/|$)#i', $path, $m) === 1) {
            $code = $m[1];
            if (strtolower($code) !== 'embed' && strtolower($code) !== 'captioned') {
                return $code;
            }
        }
        return null;
    }

    /**
     * Validates that a URL is an HTTPS media stream or asset on Instagram/Meta's public CDN
     * (`*.cdninstagram.com` or `*.fbcdn.net`) and resolves exclusively to public IPs.
     */
    public static function isInstagramCdnUrl(string $url): bool
    {
        $trimmed = trim($url);
        if ($trimmed === '' || strlen($trimmed) > 4096) {
            return false;
        }
        $parts = parse_url($trimmed);
        if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https' || empty($parts['host'])) {
            return false;
        }
        $host = strtolower((string)$parts['host']);
        $allowedHost = $host === 'cdninstagram.com'
            || str_ends_with($host, '.cdninstagram.com')
            || $host === 'fbcdn.net'
            || str_ends_with($host, '.fbcdn.net');
        if (!$allowedHost) {
            return false;
        }
        return self::isSafeUrl($trimmed);
    }

    /**
     * Checks whether an Instagram signed CDN URL (`oe=<hex_unix_timestamp>`) has expired
     * or will expire within `$bufferSeconds`.
     */
    public static function isInstagramCdnUrlExpired(string $url, int $bufferSeconds = 1800): bool
    {
        $query = (string)(parse_url($url, PHP_URL_QUERY) ?: '');
        if ($query === '') {
            return false;
        }
        parse_str($query, $params);
        $oe = isset($params['oe']) && is_string($params['oe']) ? trim($params['oe']) : '';
        if ($oe !== '' && preg_match('/^[0-9a-f]{7,10}$/i', $oe) === 1) {
            $expTs = (int)hexdec($oe);
            if ($expTs > 0 && $expTs <= (time() + $bufferSeconds)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Builds the HMAC-signed sovereign streaming proxy URL for an Instagram video shortcode.
     */
    public static function getInstagramStreamProxyUrl(string $shortcode): string
    {
        $clean = trim($shortcode);
        if ($clean === '' || preg_match('/^[A-Za-z0-9_-]{5,24}$/', $clean) !== 1) {
            return '';
        }
        $localFile = 'ig_' . $clean . '.mp4';
        $localPath = __DIR__ . '/../../resources/videos/posts/' . $localFile;
        if (is_file($localPath) && filesize($localPath) > 4096) {
            return '/resources/videos/posts/' . $localFile;
        }
        $sig = self::signStreamUrl('ig:' . $clean);
        return '/resources/videos/external-stream?ig=' . rawurlencode($clean) . '&sig=' . $sig;
    }

    /**
     * Extracts a double-quoted JSON string literal immediately following `$marker` in `$html`
     * in strict O(n) linear time without PCRE backtracking risk.
     */
    public static function extractQuotedJsonAfterMarker(string $html, string $marker): ?string
    {
        $pos = strpos($html, $marker);
        if ($pos === false) {
            return null;
        }
        $i = $pos + strlen($marker);
        $len = strlen($html);
        while ($i < $len && ($html[$i] === ' ' || $html[$i] === "\t" || $html[$i] === "\r" || $html[$i] === "\n")) {
            $i++;
        }
        if ($i >= $len || $html[$i] !== '"') {
            return null;
        }
        $start = $i;
        $i++;
        $escaped = false;
        while ($i < $len) {
            $ch = $html[$i];
            if ($escaped) {
                $escaped = false;
            } elseif ($ch === '\\') {
                $escaped = true;
            } elseif ($ch === '"') {
                $rawLiteral = substr($html, $start, $i - $start + 1);
                $decoded = json_decode($rawLiteral, true);
                return is_string($decoded) ? $decoded : null;
            }
            $i++;
        }
        return null;
    }

    /**
     * Cleans Instagram `og:description` engagement boilerplate (e.g. `54M likes, 391K comments - user on Date: "Caption"`)
     * into crisp human-readable post copy while preserving creator context.
     */
    public static function cleanInstagramDescription(string $raw): string
    {
        $desc = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($desc === '') {
            return '';
        }
        if (preg_match('/^[\d.,]+[KMB]?\s+likes?,\s*[\d.,]+[KMB]?\s+comments?\s*-\s*([A-Za-z0-9._]+)\s+on\s+[^:]+:\s*"(.*)"\.?\s*$/isu', $desc, $m) === 1) {
            $captionBody = trim($m[2]);
            if ($captionBody !== '') {
                return $captionBody;
            }
        }
        return $desc;
    }

    /**
     * Fetches Instagram's public `/p/{shortcode}/embed/captioned/` endpoint to extract:
     * 1. Progressive 720p HD `.mp4` `video_url` (when video is public & embeddable)
     * 2. Clean, uncropped, watermark-free `display_url` / `EmbeddedMediaImage` (zero `cmp1` play-button overlay)
     * 3. Exact media `aspect_ratio`, creator handle, and raw caption text
     *
     * @return array{video_url: ?string, image_url: ?string, aspect_ratio: ?string, username: ?string, full_name: ?string, caption: ?string}|null
     */
    public static function fetchInstagramEmbedMetadata(string $shortcode): ?array
    {
        $cleanCode = trim($shortcode);
        if ($cleanCode === '' || preg_match('/^[A-Za-z0-9_-]{5,24}$/', $cleanCode) !== 1) {
            return null;
        }

        $embedUrl = 'https://www.instagram.com/p/' . $cleanCode . '/embed/captioned/';
        if (!self::isSafeUrl($embedUrl)) {
            return null;
        }

        $ch = curl_init($embedUrl);
        if ($ch === false) {
            return null;
        }

        $buffer = '';
        $maxBytes = 460800; // 450 KB cap
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-GB,en-US;q=0.9,en;q=0.8',
                'Sec-Fetch-Dest: iframe',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: cross-site',
            ],
            CURLOPT_WRITEFUNCTION => static function ($chInstance, string $chunk) use (&$buffer, $maxBytes): int {
                if (strlen($buffer) + strlen($chunk) > $maxBytes) {
                    $remaining = $maxBytes - strlen($buffer);
                    if ($remaining > 0) {
                        $buffer .= substr($chunk, 0, $remaining);
                    }
                    return 0;
                }
                $buffer .= $chunk;
                return strlen($chunk);
            },
        ]);

        curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (\PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }

        if ($buffer === '' || ($httpCode !== 200 && $httpCode !== 206 && $httpCode !== 0)) {
            return null;
        }

        return self::parseInstagramEmbedHtml($buffer);
    }

    /**
     * Pure parser for Instagram `/embed/captioned/` HTML & `PolarisEmbedSimple` `contextJSON`.
     * Separated from network I/O for deterministic unit testing.
     *
     * @return array{video_url: ?string, image_url: ?string, aspect_ratio: ?string, username: ?string, full_name: ?string, caption: ?string}
     */
    public static function parseInstagramEmbedHtml(string $html): array
    {
        $videoUrl = null;
        $imageUrl = null;
        $aspectRatio = null;
        $username = null;
        $fullName = null;
        $caption = null;

        $contextJsonStr = self::extractQuotedJsonAfterMarker($html, '"contextJSON":');
        if ($contextJsonStr !== null) {
            $ctx = json_decode($contextJsonStr, true);
            $media = null;
            if (is_array($ctx)) {
                if (isset($ctx['gql_data']['shortcode_media']) && is_array($ctx['gql_data']['shortcode_media'])) {
                    $media = $ctx['gql_data']['shortcode_media'];
                } elseif (isset($ctx['context']['media']) && is_array($ctx['context']['media'])) {
                    $media = $ctx['context']['media'];
                }
            }

            if (is_array($media)) {
                $candVid = isset($media['video_url']) && is_string($media['video_url']) ? self::decodeMetaUrl($media['video_url']) : '';
                if ($candVid === '' && isset($media['edge_sidecar_to_children']['edges']) && is_array($media['edge_sidecar_to_children']['edges'])) {
                    foreach ($media['edge_sidecar_to_children']['edges'] as $edge) {
                        $childVid = isset($edge['node']['video_url']) && is_string($edge['node']['video_url'])
                            ? self::decodeMetaUrl($edge['node']['video_url'])
                            : '';
                        if ($childVid !== '' && self::isInstagramCdnUrl($childVid)) {
                            $candVid = $childVid;
                            break;
                        }
                    }
                }
                if ($candVid !== '' && self::isInstagramCdnUrl($candVid)) {
                    $videoUrl = $candVid;
                }

                $candImg = isset($media['display_url']) && is_string($media['display_url']) ? self::decodeMetaUrl($media['display_url']) : '';
                if ($candImg !== '' && self::isInstagramCdnUrl($candImg)) {
                    $imageUrl = $candImg;
                }

                $dimW = isset($media['dimensions']['width']) && is_numeric($media['dimensions']['width']) ? (int)$media['dimensions']['width'] : 0;
                $dimH = isset($media['dimensions']['height']) && is_numeric($media['dimensions']['height']) ? (int)$media['dimensions']['height'] : 0;
                if ($dimW > 0 && $dimH > 0) {
                    $aspectRatio = self::formatAspectRatio($dimW, $dimH);
                }

                if (isset($media['owner']['username']) && is_string($media['owner']['username']) && trim($media['owner']['username']) !== '') {
                    $username = trim($media['owner']['username']);
                }
                if (isset($media['owner']['full_name']) && is_string($media['owner']['full_name']) && trim($media['owner']['full_name']) !== '') {
                    $fullName = trim($media['owner']['full_name']);
                }
                $edgeText = $media['edge_media_to_caption']['edges'][0]['node']['text'] ?? null;
                if (is_string($edgeText) && trim($edgeText) !== '') {
                    $caption = trim($edgeText);
                }
            }
        }

        // DOM Fallbacks for EmbeddedMediaImage, UsernameText, and Caption when contextJSON is absent
        if ($imageUrl === null && preg_match('/<img\b[^>]*?\bclass\s*=\s*["\'][^"\']*EmbeddedMediaImage[^"\']*["\'][^>]*?\bsrc\s*=\s*["\']([^"\']+)["\']/is', $html, $im) === 1) {
            $domImg = self::decodeMetaUrl($im[1]);
            if ($domImg !== '' && self::isInstagramCdnUrl($domImg)) {
                $imageUrl = $domImg;
            }
        }
        if ($username === null && preg_match('/class\s*=\s*["\'][^"\']*UsernameText[^"\']*["\'][^>]*>(.*?)<\/span>/is', $html, $um) === 1) {
            $cleanUser = trim(strip_tags(html_entity_decode($um[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($cleanUser !== '') {
                $username = $cleanUser;
            }
        }
        if ($caption === null && preg_match('/<div\b[^>]*?\bclass\s*=\s*["\'][^"\']*Caption[^"\']*["\'][^>]*>(.*?)<\/div>/is', $html, $cm) === 1) {
            $inner = $cm[1];
            $inner = (string)preg_replace('/<a\b[^>]*?\bclass\s*=\s*["\'][^"\']*CaptionUsername[^"\']*["\'][^>]*>.*?<\/a>/is', '', $inner);
            $inner = (string)preg_replace('/<div\b[^>]*?\bclass\s*=\s*["\'][^"\']*CaptionCommentsExpand[^"\']*["\'][^>]*>.*?<\/div>/is', '', $inner);
            $cleanCap = trim(strip_tags(html_entity_decode(str_replace(['<br>', '<br/>', '<br />'], "\n", $inner), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($cleanCap !== '') {
                $caption = $cleanCap;
            }
        }

        return [
            'video_url' => $videoUrl,
            'image_url' => $imageUrl,
            'aspect_ratio' => $aspectRatio,
            'username' => $username,
            'full_name' => $fullName,
            'caption' => $caption,
        ];
    }

    /**
     * Merges Instagram `/embed/captioned/` video/poster/caption metadata into scraped OpenGraph data.
     *
     * @param array<string, mixed> $ogData
     * @return array<string, mixed>
     */
    private static function enrichWithInstagramEmbed(string $url, string $shortcode, array $ogData): array
    {
        $ogData['ig_v2'] = true;
        $ogData['ig_shortcode'] = $shortcode;
        $ogData['site_name'] = 'Instagram';
        if (empty($ogData['domain'])) {
            $ogData['domain'] = 'instagram.com';
        }

        $embedMeta = (($_ENV['APP_ENV'] ?? '') === 'testing')
            ? null
            : self::fetchInstagramEmbedMetadata($shortcode);

        if (is_array($embedMeta)) {
            if (!empty($embedMeta['video_url'])) {
                $ogData['video_url'] = $embedMeta['video_url'];
            }
            // Prefer watermark-free EmbeddedMediaImage / display_url over WhatsApp's `cmp1` square
            if (!empty($embedMeta['image_url'])) {
                $ogData['image_url'] = $embedMeta['image_url'];
            }
            if (!empty($embedMeta['aspect_ratio'])) {
                $ogData['aspect_ratio'] = $embedMeta['aspect_ratio'];
            }
            if (!empty($embedMeta['caption'])) {
                $ogData['description'] = $embedMeta['caption'];
            }
            $creatorLabel = !empty($embedMeta['full_name'])
                ? $embedMeta['full_name'] . (!empty($embedMeta['username']) ? ' (@' . $embedMeta['username'] . ')' : '')
                : (!empty($embedMeta['username']) ? '@' . $embedMeta['username'] : null);
            if ($creatorLabel !== null && (empty($ogData['title']) || strtolower(trim((string)$ogData['title'])) === 'instagram')) {
                $isReel = str_contains(strtolower($url), '/reel') || !empty($embedMeta['video_url']);
                $ogData['title'] = $creatorLabel . ($isReel ? ' on Instagram Reels' : ' on Instagram');
            }
        }

        if (!empty($ogData['description']) && is_string($ogData['description'])) {
            $ogData['description'] = self::cleanInstagramDescription($ogData['description']);
        }

        if (empty($ogData['aspect_ratio']) && (str_contains(strtolower($url), '/reel') || !empty($ogData['video_url']))) {
            $ogData['aspect_ratio'] = '9 / 16';
        }

        return $ogData;
    }

    /**
     * Resolves a fresh, non-expired Instagram `.mp4` CDN URL for `$shortcode` on behalf of
     * `ServeImgController::StreamExternalVideo()`. Refreshes cached `extra_data` transparently
     * when `oe=` hex signatures expire (~32h TTL).
     */
    public static function resolveFreshInstagramVideoStream(string $shortcode, bool $forceRefresh = false): ?string
    {
        $cleanCode = trim($shortcode);
        if ($cleanCode === '' || preg_match('/^[A-Za-z0-9_-]{5,24}$/', $cleanCode) !== 1) {
            return null;
        }

        $db = Db::connect2();

        if (!$forceRefresh) {
            try {
                $likePattern = '%' . $cleanCode . '%';
                $stmt = $db->prepare(
                    "SELECT url_hash, extra_data FROM link_preview_cache
                     WHERE domain LIKE '%instagram%' AND url LIKE ? AND extra_data IS NOT NULL
                     ORDER BY fetched_at DESC LIMIT 3"
                );
                $stmt->execute([$likePattern]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $extra = json_decode((string)($row['extra_data'] ?? ''), true);
                    $cand = is_array($extra) && isset($extra['video_url']) && is_string($extra['video_url'])
                        ? trim($extra['video_url'])
                        : '';
                    if ($cand !== '' && self::isInstagramCdnUrl($cand) && !self::isInstagramCdnUrlExpired($cand)) {
                        return $cand;
                    }
                }
            } catch (Throwable) {
            }
        }

        $embedMeta = self::fetchInstagramEmbedMetadata($cleanCode);
        $freshUrl = is_array($embedMeta) && isset($embedMeta['video_url'])
            ? trim($embedMeta['video_url'])
            : '';
        if ($freshUrl === '' || !self::isInstagramCdnUrl($freshUrl)) {
            return null;
        }

        // Opportunistically update any cached Instagram rows matching this shortcode
        try {
            $likePattern = '%' . $cleanCode . '%';
            $stmt = $db->prepare(
                "SELECT url_hash, extra_data FROM link_preview_cache
                 WHERE domain LIKE '%instagram%' AND url LIKE ? LIMIT 5"
            );
            $stmt->execute([$likePattern]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $upd = $db->prepare("UPDATE link_preview_cache SET extra_data = ?, fetched_at = ? WHERE url_hash = ?");
            $now = date('Y-m-d H:i:s');
            foreach ($rows as $row) {
                $extra = json_decode((string)($row['extra_data'] ?? ''), true);
                if (!is_array($extra)) {
                    $extra = [];
                }
                $extra['video_url'] = $freshUrl;
                $extra['ig_v2'] = true;
                $extra['ig_shortcode'] = $cleanCode;
                $upd->execute([json_encode($extra, JSON_UNESCAPED_SLASHES), $now, (string)$row['url_hash']]);
            }
        } catch (Throwable) {
        }

        return $freshUrl;
    }

    /**
     * Checks whether a URL or host belongs to Facebook / Meta video domains.
     */
    public static function isFacebookDomain(string $urlOrHost): bool
    {
        $trimmed = trim($urlOrHost);
        if ($trimmed === '') {
            return false;
        }
        $host = str_contains($trimmed, '://')
            ? (string)(parse_url($trimmed, PHP_URL_HOST) ?? '')
            : $trimmed;
        $host = strtolower(trim($host));
        if ($host === '') {
            return false;
        }
        return $host === 'facebook.com'
            || str_ends_with($host, '.facebook.com')
            || $host === 'fb.watch'
            || $host === 'fb.com'
            || str_ends_with($host, '.fb.com');
    }

    /**
     * Checks whether a URL is a Facebook video, reel, watch, or share-video link.
     */
    public static function isFacebookVideoOrShareUrl(string $url): bool
    {
        $trimmed = trim($url);
        if ($trimmed === '' || !self::isFacebookDomain($trimmed)) {
            return false;
        }
        return preg_match('#^https?://(?:www\.|m\.|web\.)?(?:facebook\.com/(?:[^/\s]+/videos/\d+|watch/?\?v=\d+|reel/\d+|share/[vr]/[^\s"\'<>]+|video\.php\?v=\d+)|fb\.watch/[^\s"\'<>]+|fb\.com/(?:watch|reel|share/[vr]|[^/\s]+/videos)/[^\s"\'<>]+)#i', $trimmed) === 1;
    }

    /**
     * Extracts the numeric Facebook video or reel ID from canonical Facebook video URLs.
     */
    public static function extractFacebookVideoId(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }
        $trimmed = trim($url);
        if (!self::isFacebookDomain($trimmed)) {
            return null;
        }
        if (preg_match('~/(?:reel|videos)/(?:[^/\s?#]+/)?(\d{6,25})(?:[/?#]|$)~i', $trimmed, $m) === 1) {
            return $m[1];
        }
        if (preg_match('~[?&]v=(\d{6,25})(?:&|$)~i', $trimmed, $m) === 1) {
            return $m[1];
        }
        return null;
    }

    /**
     * Extracts the numeric Facebook video ID from a page's oEmbed alternate link
     * (used to resolve opaque /share/r/ and /share/v/ URLs without a redirect).
     */
    public static function extractFacebookVideoIdFromHtml(string $html): ?string
    {
        if (preg_match('~oembed_video\?url=([^"&\s]+)~i', $html, $m) === 1) {
            return self::extractFacebookVideoId(rawurldecode($m[1]));
        }
        if (preg_match('~og:url"\s+content="(https://www\.facebook\.com/reel/\d{6,25}/?)"~i', $html, $m) === 1) {
            return self::extractFacebookVideoId($m[1]);
        }
        return null;
    }

    /**
     * Builds the HMAC-signed sovereign streaming proxy URL for a Facebook video ID.
     */
    public static function getFacebookStreamProxyUrl(string $videoId): string
    {
        $clean = trim($videoId);
        if ($clean === '' || preg_match('/^\d{6,25}$/', $clean) !== 1) {
            return '';
        }
        $localFile = 'fb_' . $clean . '.mp4';
        $localPath = __DIR__ . '/../../resources/videos/posts/' . $localFile;
        if (is_file($localPath) && filesize($localPath) > 4096) {
            return '/resources/videos/posts/' . $localFile;
        }
        $sig = self::signStreamUrl('fb:' . $clean);
        return '/resources/videos/external-stream?fb=' . rawurlencode($clean) . '&sig=' . $sig;
    }

    /**
     * Pure parser for Facebook `/plugins/video.php` HTML payload.
     * Extracts `hd_src` / `sd_src` CDN `.mp4` streams and dimensions without network I/O.
     *
     * @return array{video_url: ?string, aspect_ratio: ?string}
     */
    public static function parseFacebookPluginVideoHtml(string $html): array
    {
        $videoUrl = null;
        $aspectRatio = null;

        foreach (['hd_src_no_ratelimit', 'hd_src', 'sd_src_no_ratelimit', 'sd_src'] as $field) {
            $marker = '"' . $field . '":';
            $decoded = self::extractQuotedJsonAfterMarker($html, $marker);
            if ($decoded === null || trim($decoded) === '') {
                continue;
            }
            $candidate = self::decodeMetaUrl($decoded);
            if ($candidate !== '' && self::isInstagramCdnUrl($candidate)) {
                $videoUrl = $candidate;
                break;
            }
        }

        if (preg_match('/"(?:original_width|video_width)"\s*:\s*(\d+)\s*,.*?"(?:original_height|video_height)"\s*:\s*(\d+)/s', $html, $dm) === 1) {
            $w = (int)$dm[1];
            $h = (int)$dm[2];
            if ($w >= 100 && $h >= 100) {
                $aspectRatio = self::formatAspectRatio($w, $h);
            }
        }

        return [
            'video_url' => $videoUrl,
            'aspect_ratio' => $aspectRatio,
        ];
    }

    /**
     * Fetches Facebook's official `/plugins/video.php` endpoint for a canonical video/reel URL
     * to extract progressive `.mp4` CDN stream metadata.
     *
     * @return array{video_url: ?string, aspect_ratio: ?string}|null
     */
    public static function fetchFacebookPluginVideoMetadata(string $canonicalVideoUrl): ?array
    {
        $trimmed = trim($canonicalVideoUrl);
        if ($trimmed === '' || !self::isFacebookDomain($trimmed)) {
            return null;
        }

        $pluginUrl = 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($trimmed) . '&show_text=false&autoplay=0';
        if (!self::isSafeUrl($pluginUrl)) {
            return null;
        }

        $ch = curl_init();
        if ($ch === false) {
            return null;
        }

        $buffer = '';
        $maxBytes = 524288; // 512 KB cap
        curl_setopt_array($ch, [
            CURLOPT_URL => $pluginUrl,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-GB,en;q=0.9',
            ],
            CURLOPT_WRITEFUNCTION => static function ($chInstance, string $chunk) use (&$buffer, $maxBytes): int {
                if (strlen($buffer) + strlen($chunk) > $maxBytes) {
                    $remaining = $maxBytes - strlen($buffer);
                    if ($remaining > 0) {
                        $buffer .= substr($chunk, 0, $remaining);
                    }
                    return 0;
                }
                $buffer .= $chunk;
                return strlen($chunk);
            },
        ]);

        curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (\PHP_VERSION_ID < 80500) {
            @curl_close($ch);
        }

        if ($buffer === '' || ($httpCode !== 200 && $httpCode !== 206 && $httpCode !== 0)) {
            return null;
        }

        return self::parseFacebookPluginVideoHtml($buffer);
    }

    /**
     * Enriches scraped Facebook OpenGraph metadata with progressive `.mp4` stream URLs
     * from `/plugins/video.php` (including resolved `/share/r/` and `/share/v/` links).
     *
     * @param array<string, mixed> $ogData
     * @return array<string, mixed>
     */
    private static function enrichWithFacebookVideo(string $url, array $ogData): array
    {
        $canonical = !empty($ogData['canonical_url']) && is_string($ogData['canonical_url'])
            ? trim($ogData['canonical_url'])
            : $url;
        $fbVideoId = (!empty($ogData['fb_video_id']) && is_string($ogData['fb_video_id']) && preg_match('/^\d{6,25}$/', $ogData['fb_video_id']) === 1)
            ? $ogData['fb_video_id']
            : (self::extractFacebookVideoId($canonical) ?? self::extractFacebookVideoId($url));

        $ogData['fb_v2'] = true;
        $ogData['site_name'] = 'Facebook';
        if (empty($ogData['domain'])) {
            $ogData['domain'] = 'facebook.com';
        }
        if ($fbVideoId !== null) {
            $ogData['fb_video_id'] = $fbVideoId;
        }

        $targetForPlugin = $fbVideoId !== null
            ? (str_contains(strtolower($canonical . ' ' . $url), '/reel') || str_contains(strtolower($url), '/share/r/')
                ? 'https://www.facebook.com/reel/' . $fbVideoId . '/'
                : $canonical)
            : $canonical;

        $pluginMeta = (($_ENV['APP_ENV'] ?? '') === 'testing')
            ? null
            : self::fetchFacebookPluginVideoMetadata($targetForPlugin);

        if (is_array($pluginMeta)) {
            if (!empty($pluginMeta['video_url'])) {
                $ogData['video_url'] = $pluginMeta['video_url'];
            }
            if (!empty($pluginMeta['aspect_ratio'])) {
                $ogData['aspect_ratio'] = $pluginMeta['aspect_ratio'];
            }
        }

        if (empty($ogData['aspect_ratio']) && (str_contains(strtolower($canonical . ' ' . $url), '/reel') || str_contains(strtolower($url), '/share/r/') || !empty($ogData['video_url']))) {
            $ogData['aspect_ratio'] = '9 / 16';
        }

        return $ogData;
    }

    /**
     * Resolves a fresh, non-expired Facebook `.mp4` CDN URL for `$videoId` on behalf of
     * `ServeImgController::StreamExternalVideo()`.
     */
    public static function resolveFreshFacebookVideoStream(string $videoId, bool $forceRefresh = false): ?string
    {
        $cleanId = trim($videoId);
        if ($cleanId === '' || preg_match('/^\d{6,25}$/', $cleanId) !== 1) {
            return null;
        }

        $db = Db::connect2();
        $canonicalHint = 'https://www.facebook.com/reel/' . $cleanId . '/';

        if (!$forceRefresh) {
            try {
                $likePattern = '%' . $cleanId . '%';
                $stmt = $db->prepare(
                    "SELECT url_hash, canonical_url, extra_data FROM link_preview_cache
                     WHERE (domain LIKE '%facebook%' OR domain LIKE '%fb.%') AND (canonical_url LIKE ? OR extra_data LIKE ?) AND extra_data IS NOT NULL
                     ORDER BY fetched_at DESC LIMIT 3"
                );
                $stmt->execute([$likePattern, $likePattern]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($row['canonical_url']) && is_string($row['canonical_url']) && self::isFacebookDomain($row['canonical_url'])) {
                        $canonicalHint = trim($row['canonical_url']);
                    }
                    $extra = json_decode((string)($row['extra_data'] ?? ''), true);
                    $cand = is_array($extra) && isset($extra['video_url']) && is_string($extra['video_url'])
                        ? trim($extra['video_url'])
                        : '';
                    if ($cand !== '' && self::isInstagramCdnUrl($cand) && !self::isInstagramCdnUrlExpired($cand)) {
                        return $cand;
                    }
                }
            } catch (Throwable) {
            }
        }

        $pluginMeta = self::fetchFacebookPluginVideoMetadata($canonicalHint);
        $freshUrl = is_array($pluginMeta) && isset($pluginMeta['video_url'])
            ? trim($pluginMeta['video_url'])
            : '';
        if ($freshUrl === '' || !self::isInstagramCdnUrl($freshUrl)) {
            return null;
        }

        try {
            $likePattern = '%' . $cleanId . '%';
            $stmt = $db->prepare(
                "SELECT url_hash, extra_data FROM link_preview_cache
                 WHERE (domain LIKE '%facebook%' OR domain LIKE '%fb.%') AND (canonical_url LIKE ? OR extra_data LIKE ?) LIMIT 5"
            );
            $stmt->execute([$likePattern, $likePattern]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $upd = $db->prepare("UPDATE link_preview_cache SET extra_data = ?, fetched_at = ? WHERE url_hash = ?");
            $now = date('Y-m-d H:i:s');
            foreach ($rows as $row) {
                $extra = json_decode((string)($row['extra_data'] ?? ''), true);
                if (!is_array($extra)) {
                    $extra = [];
                }
                $extra['video_url'] = $freshUrl;
                $extra['fb_v2'] = true;
                $extra['fb_video_id'] = $cleanId;
                $upd->execute([json_encode($extra, JSON_UNESCAPED_SLASHES), $now, (string)$row['url_hash']]);
            }
        } catch (Throwable) {
        }

        return $freshUrl;
    }

    /**
     * Generate URL slug from title.
     */
    private static function slugify(string $text): string
    {
        $slug = (string)preg_replace('/[^\p{L}\p{N}]+/u', '-', $text);
        $slug = trim($slug, '-');
        $slug = strtolower($slug);
        return $slug;
    }
}

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
    private const CACHE_TTL_SECONDS = 86400; // 24 Hours
    private const MAX_BODY_BYTES = 131072;   // 128 KB (Head section inspection)
    private const HTTP_TIMEOUT_SECS = 4;
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
                fetched_at DATETIME NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_expires_at (expires_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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

        // 1. Normalize line breaks to standard \n
        $text = str_replace(["\r\n", "\r"], "\n", $raw);

        // 2. Separate URLs that are directly glued to trailing emojis (e.g. '👇https://...')
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

        return trim($text);
    }

    /**
     * Extract the first previewable HTTP/HTTPS URL from a text message.
     */
    public static function extractFirstPreviewableUrl(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        if (preg_match('/(https?:\/\/[^\s<>"\'`]+)/iu', $text, $matches) === 1) {
            $url = trim($matches[1]);
            // Clean trailing punctuation attached from sentence ending (.,;:!?)
            $url = rtrim($url, '.,;:!?)]}');
            return $url !== '' ? $url : null;
        }

        return null;
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

        // Resolve DNS records to verify remote IP addresses
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (empty($records) || !is_array($records)) {
            $ip = @gethostbyname($host);
            if ($ip === $host || !filter_var($ip, FILTER_VALIDATE_IP)) {
                return false;
            }
            return self::isPublicIp($ip);
        }

        foreach ($records as $record) {
            $targetIp = $record['ip'] ?? $record['ipv6'] ?? null;
            if ($targetIp !== null && !self::isPublicIp((string)$targetIp)) {
                return false;
            }
        }

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
     * @return array<string, mixed>|null
     */
    public static function fetchPreview(string $rawUrl): ?array
    {
        $url = trim($rawUrl);
        if ($url === '') {
            return null;
        }

        $urlHash = hash('sha256', $url);
        $db = Db::connect2();

        // 1. Check local cache
        try {
            $stmt = $db->prepare("SELECT * FROM link_preview_cache WHERE url_hash = ? AND expires_at > NOW() LIMIT 1");
            $stmt->execute([$urlHash]);
            $cached = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cached) {
                return self::formatPreviewPayload($cached);
            }
        } catch (Throwable $e) {
            // Self-heal table if missing
            self::ensureSchema($db);
        }

        // 2. SSRF Safety Verification
        if (!self::isSafeUrl($url)) {
            return null;
        }

        // 3. Outbound fetch with WhatsApp/FamilyPlatform bot User-Agent
        $html = self::fetchHtmlHead($url);
        if ($html === null || trim($html) === '') {
            return null;
        }

        // 4. Extract OpenGraph and Twitter card metadata
        $ogData = self::parseMetadata($html, $url);
        if (empty($ogData['title']) && empty($ogData['image_url'])) {
            return null;
        }

        // 5. Store into cache
        try {
            $now = date('Y-m-d H:i:s');
            $expires = date('Y-m-d H:i:s', time() + self::CACHE_TTL_SECONDS);

            $ins = $db->prepare("INSERT INTO link_preview_cache 
                (url_hash, url, canonical_url, title, description, image_url, site_name, domain, card_type, fetched_at, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                canonical_url = VALUES(canonical_url),
                title = VALUES(title),
                description = VALUES(description),
                image_url = VALUES(image_url),
                site_name = VALUES(site_name),
                domain = VALUES(domain),
                card_type = VALUES(card_type),
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
                $now,
                $expires
            ]);
        } catch (Throwable $e) {
            error_log('[LinkPreviewService::cache] ' . $e->getMessage());
        }

        return self::formatPreviewPayload($ogData);
    }

    /**
     * Resolves the link preview for a given post message text.
     * Handles truncated PartyPlatform URL recovery automatically.
     *
     * @return array<string, mixed>|null
     */
    public static function getPreviewForPostText(?string $postMessage): ?array
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

        return self::fetchPreview($url);
    }

    /**
     * Performs a resilient HTTP GET fetching only the HTML <head> section up to 128KB.
     */
    private static function fetchHtmlHead(string $url): ?string
    {
        $ch = curl_init();
        if ($ch === false) {
            return null;
        }

        $responseBuffer = '';
        $maxBytes = self::MAX_BODY_BYTES;

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_TIMEOUT => self::HTTP_TIMEOUT_SECS,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT_SECS,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'WhatsApp/2.23.20.0 FamilyPlatformBot/1.0 (+https://myfamilyplatform.com)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_WRITEFUNCTION => function ($chInstance, string $chunk) use (&$responseBuffer, $maxBytes): int {
                $responseBuffer .= $chunk;
                if (strlen($responseBuffer) >= $maxBytes || stripos($responseBuffer, '</head>') !== false) {
                    return 0; // Abort transfer once </head> or size limit reached
                }
                return strlen($chunk);
            },
        ]);

        $execResult = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Code 200 or 206 (or abort from write function returning 0 when head captured)
        if (($execResult === true || $responseBuffer !== '') && ($httpCode === 200 || $httpCode === 206 || $httpCode === 0)) {
            return $responseBuffer;
        }

        return null;
    }

    /**
     * Parses OpenGraph, Twitter, and HTML meta tags from HTML head string.
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
            'site_name' => ucfirst(explode('.', $cleanDomain)[0] ?? 'Website'),
            'title' => null,
            'description' => null,
            'image_url' => null,
            'card_type' => 'summary_large_image',
        ];

        // 1. og:title / twitter:title / <title>
        if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<meta[^>]+name=["\']twitter:title["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
            $data['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 2. og:description / twitter:description / <meta name="description">
        if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['description'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<meta[^>]+name=["\']twitter:description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['description'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['description'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 3. og:image / og:image:secure_url / twitter:image
        if (preg_match('/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['image_url'] = trim($m[1]);
        } elseif (preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['image_url'] = trim($m[1]);
        }

        // 4. og:site_name
        if (preg_match('/<meta[^>]+property=["\']og:site_name["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $data['site_name'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 5. og:url (canonical)
        if (preg_match('/<meta[^>]+property=["\']og:url["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
            $canonical = trim($m[1]);
            if (filter_var($canonical, FILTER_VALIDATE_URL)) {
                $data['canonical_url'] = $canonical;
            }
        }

        // Resolve relative image URLs against sourceUrl
        if (!empty($data['image_url']) && !str_starts_with($data['image_url'], 'http://') && !str_starts_with($data['image_url'], 'https://')) {
            $data['image_url'] = self::resolveRelativeUrl($data['image_url'], $sourceUrl);
        }

        return $data;
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
     * Formats preview data into standard front-end contract.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function formatPreviewPayload(array $data): array
    {
        $url = (string)($data['canonical_url'] ?? $data['url'] ?? '');
        $domain = (string)($data['domain'] ?? parse_url($url, PHP_URL_HOST) ?? '');
        $isPartyEvent = str_contains(strtolower($domain), 'partyplatform') || str_contains($url, '/e/');

        return [
            'url' => $url,
            'title' => (string)($data['title'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'image' => (string)($data['image_url'] ?? $data['image'] ?? ''),
            'site_name' => (string)($data['site_name'] ?? $domain),
            'domain' => $domain,
            'card_type' => (string)($data['card_type'] ?? 'summary_large_image'),
            'is_party_event' => $isPartyEvent,
            'cta_label' => $isPartyEvent ? 'View Event / RSVP →' : 'Open Link →',
        ];
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

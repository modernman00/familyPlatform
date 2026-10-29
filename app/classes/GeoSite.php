<?php

declare(strict_types=1);

namespace App\classes;

use Src\SeoEngine;

/**
 * GeoSite — environment-driven canonical origin and crawler-manifest renderer.
 *
 * Single source of truth for the public origin used in sitemap.xml, robots.txt
 * and llms*.txt. Nothing here hard-codes a production domain: the origin is
 * always resolved from configuration, in this order:
 *
 *   1. GEO_CANONICAL_URL   explicit override
 *   2. APP_URL             when it is a real (non-local) host
 *   3. APP_URL declared in .env_production / .env-production (so a developer
 *      machine running `php scripts/generate_sitemap.php` still emits the
 *      production origin rather than a *.test host)
 *
 * If none resolves, it fails loudly instead of guessing a domain.
 *
 * Sitemap and robots output are delegated to the portfolio's shared-lib
 * {@see SeoEngine} so the format rules live in one place.
 */
final class GeoSite
{
    /** Host suffixes that must never appear in a public crawler manifest. */
    private const LOCAL_SUFFIXES = ['.test', '.local', '.localhost', '.invalid', '.internal'];

    /** Placeholder substituted into llms*.txt templates. */
    public const PLACEHOLDER = '{{SITE_URL}}';

    /**
     * Paths crawlers must not visit (authenticated, API and operational areas).
     *
     * @var list<string>
     */
    private const DISALLOW = [
        '/app/',
        '/member/',
        '/profilePage',
        '/organogram',
        '/storage/',
        '/cron/',
    ];

    private static ?string $cachedOrigin = null;

    /**
     * Canonical public origin, e.g. "https://example.com" (no trailing slash).
     *
     * @throws \RuntimeException when no production origin can be resolved.
     */
    public static function baseUrl(): string
    {
        if (self::$cachedOrigin !== null) {
            return self::$cachedOrigin;
        }

        $candidates = [
            self::env('GEO_CANONICAL_URL'),
            self::env('APP_URL'),
            self::productionEnvFileUrl(),
        ];

        foreach ($candidates as $candidate) {
            $origin = self::normaliseOrigin($candidate);
            if ($origin !== null && !self::isLocalHost((string) parse_url($origin, PHP_URL_HOST))) {
                return self::$cachedOrigin = $origin;
            }
        }

        throw new \RuntimeException(
            'Canonical site URL could not be resolved. Set GEO_CANONICAL_URL, a production APP_URL, '
            . 'or provide APP_URL in .env_production.'
        );
    }

    /**
     * Reset the memoised origin (used by tests that change the environment).
     */
    public static function reset(): void
    {
        self::$cachedOrigin = null;
    }

    /**
     * Render a template file, replacing {{SITE_URL}} with the canonical origin.
     */
    public static function renderTemplate(string $absolutePath): string
    {
        $raw = is_readable($absolutePath) ? file_get_contents($absolutePath) : false;
        if ($raw === false) {
            throw new \RuntimeException('GEO template not readable: ' . basename($absolutePath));
        }

        return str_replace(self::PLACEHOLDER, self::baseUrl(), $raw);
    }

    /**
     * robots.txt body, built by shared-lib's SeoEngine from the canonical origin.
     */
    public static function robotsTxt(): string
    {
        $base = self::baseUrl();

        return SeoEngine::generateRobotsTxt($base . '/sitemap.xml', self::DISALLOW)
            . "\n# Machine-readable site summary for AI assistants\n# llms: {$base}/llms.txt\n";
    }

    /**
     * Send a plain-text response rendered from a template in public/.
     * Fails closed with 503 if the canonical origin is not configured.
     */
    public static function serveTemplate(string $publicFile): void
    {
        $path = dirname(__DIR__, 2) . '/public/' . basename($publicFile);

        try {
            $body = self::renderTemplate($path);
        } catch (\Throwable $e) {
            error_log('[GeoSite] ' . $e->getMessage());
            http_response_code(503);
            header('Content-Type: text/plain; charset=utf-8');
            echo "Temporarily unavailable.\n";
            return;
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        echo $body;
    }

    /**
     * Normalise a URL-ish string to "scheme://host[:port]" or null if unusable.
     */
    private static function normaliseOrigin(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['scheme'])) {
            return null;
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $origin = strtolower($parts['scheme']) . '://' . strtolower($parts['host']);
        if (!empty($parts['port'])) {
            $origin .= ':' . (int) $parts['port'];
        }

        return $origin;
    }

    private static function isLocalHost(string $host): bool
    {
        $host = strtolower($host);
        if ($host === '' || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return true;
        }
        foreach (self::LOCAL_SUFFIXES as $suffix) {
            if (str_ends_with($host, $suffix)) {
                return true;
            }
        }

        return false;
    }

    private static function env(string $key): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Read only APP_URL from the project's production env file. No other value
     * in that file is parsed or retained.
     */
    private static function productionEnvFileUrl(): ?string
    {
        $root = dirname(__DIR__, 2);
        foreach (['.env_production', '.env-production'] as $name) {
            $file = $root . '/' . $name;
            if (!is_readable($file)) {
                continue;
            }
            $contents = file_get_contents($file);
            if ($contents !== false
                && preg_match('/^\s*APP_URL\s*=\s*["\']?([^"\'\s#]+)/m', $contents, $m) === 1
            ) {
                return $m[1];
            }
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\services;

use App\model\BlogData;

/**
 * Class SitemapService
 *
 * Autonomous XML Sitemap and Generative Engine Optimization (GEO) registry generator.
 * Aggregates public whitelist routes and published editorial blogs into a validated
 * sitemap complying with the Sitemaps.org 0.9 schema standard.
 */
final class SitemapService
{
    /**
     * Target filesystem destination for the published XML sitemap in public/.
     */
    private const SITEMAP_PATH = __DIR__ . '/../../public/sitemap.xml';

    /**
     * Target filesystem destination for the root XML sitemap.
     */
    private const ROOT_SITEMAP_PATH = __DIR__ . '/../../sitemap.xml';

    /**
     * Default canonical base URL for production indexing.
     */
    private const DEFAULT_BASE_URL = 'https://myfamilyplatform.com';

    /**
     * Core static public routes with SEO prioritization and change frequencies.
     *
     * @var array<int, array{path: string, priority: string, changefreq: string}>
     */
    private const STATIC_ROUTES = [
        ['path' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['path' => '/tools/family-memory-prompts', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['path' => '/tools/kinship-calculator', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['path' => '/how-to-use', 'priority' => '0.9', 'changefreq' => 'weekly'],
        ['path' => '/why-familyplatform', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['path' => '/playbook', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['path' => '/blog', 'priority' => '0.8', 'changefreq' => 'daily'],
        ['path' => '/aboutus', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['path' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['path' => '/privacy', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ['path' => '/terms', 'priority' => '0.5', 'changefreq' => 'monthly'],
    ];

    /**
     * Generate and atomically write the public sitemap.xml.
     *
     * @return array{success: bool, url_count: int, file_path: string, bytes: int, error?: string}
     */
    public static function generate(): array
    {
        $baseUrl = self::resolveBaseUrl();
        $urls = [];
        $today = date('Y-m-d');

        // 1. Ingest Core Whitelisted Static Pages
        foreach (self::STATIC_ROUTES as $route) {
            $urls[] = [
                'loc' => $baseUrl . $route['path'],
                'lastmod' => $today,
                'changefreq' => $route['changefreq'],
                'priority' => $route['priority'],
            ];
        }

        // 2. Dynamically Ingest Published Blog Posts
        try {
            $blogs = BlogData::getAllPublishedBlogs();
            foreach ($blogs as $blog) {
                $slug = trim((string) ($blog['slug'] ?? ''));
                if ($slug === '') {
                    $slug = (string) ($blog['id'] ?? '');
                }

                if ($slug === '') {
                    continue;
                }

                $rawDate = (string) ($blog['updated_at'] ?? $blog['created_at'] ?? '');
                $lastmod = $today;
                if ($rawDate !== '') {
                    $timestamp = strtotime($rawDate);
                    if ($timestamp !== false) {
                        $lastmod = date('Y-m-d', $timestamp);
                    }
                }

                $urls[] = [
                    'loc' => $baseUrl . '/blog/' . rawurlencode($slug),
                    'lastmod' => $lastmod,
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        } catch (\Throwable $e) {
            // Defensive Resilience: Log error but preserve static route generation
            error_log('[SitemapService] Warning: Could not query published blogs: ' . $e->getMessage());
        }

        // 3. Assemble Validated XML Document
        $xmlContent = self::buildXml($urls);

        // 4. Atomic Write Guard (both public/ and root destinations)
        $targetFile = self::SITEMAP_PATH;
        $tempFile = $targetFile . '.tmp.' . bin2hex(random_bytes(4));

        $bytes = file_put_contents($tempFile, $xmlContent, LOCK_EX);
        if ($bytes === false) {
            return [
                'success' => false,
                'url_count' => 0,
                'file_path' => $targetFile,
                'bytes' => 0,
                'error' => 'Failed to write temporary sitemap file.',
            ];
        }

        if (!rename($tempFile, $targetFile)) {
            @unlink($tempFile);
            return [
                'success' => false,
                'url_count' => 0,
                'file_path' => $targetFile,
                'bytes' => 0,
                'error' => 'Failed to atomically replace sitemap.xml.',
            ];
        }

        // Also duplicate to root sitemap.xml for direct server DocumentRoot accessibility
        @file_put_contents(self::ROOT_SITEMAP_PATH, $xmlContent, LOCK_EX);

        return [
            'success' => true,
            'url_count' => count($urls),
            'file_path' => $targetFile,
            'bytes' => $bytes,
        ];
    }

    /**
     * Build standard Sitemaps.org compliant XML string.
     *
     * @param array<int, array{loc: string, lastmod: string, changefreq: string, priority: string}> $urls
     */
    private static function buildXml(array $urls): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $dom->appendChild($urlset);

        foreach ($urls as $item) {
            $urlElement = $dom->createElement('url');

            $loc = $dom->createElement('loc', htmlspecialchars($item['loc'], ENT_XML1, 'UTF-8'));
            $urlElement->appendChild($loc);

            $lastmod = $dom->createElement('lastmod', $item['lastmod']);
            $urlElement->appendChild($lastmod);

            $changefreq = $dom->createElement('changefreq', $item['changefreq']);
            $urlElement->appendChild($changefreq);

            $priority = $dom->createElement('priority', $item['priority']);
            $urlElement->appendChild($priority);

            $urlset->appendChild($urlElement);
        }

        return (string) $dom->saveXML();
    }

    /**
     * Resolve the canonical base URL without trailing slash.
     */
    private static function resolveBaseUrl(): string
    {
        $envUrl = (string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '');
        if ($envUrl !== '' && filter_var($envUrl, FILTER_VALIDATE_URL)) {
            $host = parse_url($envUrl, PHP_URL_HOST);
            if ($host && !str_contains($host, '.test') && !str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1')) {
                return rtrim($envUrl, '/');
            }
        }

        return self::DEFAULT_BASE_URL;
    }
}

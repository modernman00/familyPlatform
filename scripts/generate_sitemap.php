<?php
declare(strict_types=1);

/**
 * Dynamic XML Sitemap Generator — FamilyPlatform
 * Generates standards-compliant XML sitemaps for search engines (Google, Bing).
 * Synchronises both root and public/ sitemap.xml with live public routes & published blogs.
 *
 * Enforced by: Technical Approval Team (TAT 2.0)
 * Architecture: Zero-defect fail-closed generation with database graceful fallback.
 */

$rootDir = dirname(__DIR__);
$publicDir = $rootDir . '/public';

// Bootstrap env + autoload so the canonical origin is read from configuration
// (GEO_CANONICAL_URL / production APP_URL / .env_production) — never hard-coded.
require_once $rootDir . '/app/config/init.php';

try {
    $baseUrl = \App\classes\GeoSite::baseUrl();
} catch (\Throwable $e) {
    fwrite(STDERR, "❌ " . $e->getMessage() . "\n");
    exit(1);
}

$corePages = [
    [
        'loc' => $baseUrl . '/',
        'changefreq' => 'daily',
        'priority' => '1.0',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/tools/family-memory-prompts',
        'changefreq' => 'weekly',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/tools/kinship-calculator',
        'changefreq' => 'weekly',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/why-familyplatform',
        'changefreq' => 'weekly',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/aboutus',
        'changefreq' => 'monthly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/playbook',
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/how-to-use',
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/blog',
        'changefreq' => 'daily',
        'priority' => '0.8',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/contact',
        'changefreq' => 'monthly',
        'priority' => '0.7',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/privacy',
        'changefreq' => 'yearly',
        'priority' => '0.3',
        'lastmod' => date('Y-m-d'),
    ],
    [
        'loc' => $baseUrl . '/terms',
        'changefreq' => 'yearly',
        'priority' => '0.3',
        'lastmod' => date('Y-m-d'),
    ],
];

// Query published blogs dynamically if database is available
$blogPages = [];
try {
    if (!class_exists('\Src\Db')) {
        $initFile = $rootDir . '/app/config/init.php';
        if (file_exists($initFile)) {
            require_once $initFile;
        }
    }

    if (class_exists('\Src\Db')) {
        $db = \Src\Db::connect2();
        $stmt = $db->prepare("SELECT slug, updated_at, created_at FROM blogs WHERE status = ? ORDER BY id DESC");
        $stmt->execute(['published']);
        $blogs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (is_array($blogs)) {
            foreach ($blogs as $blog) {
                $slug = trim((string)($blog['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }
                $rawDate = (string)($blog['updated_at'] ?? $blog['created_at'] ?? date('Y-m-d'));
                $dateTimestamp = strtotime($rawDate);
                $lastmod = ($dateTimestamp !== false) ? date('Y-m-d', $dateTimestamp) : date('Y-m-d');

                $blogPages[] = [
                    'loc' => $baseUrl . '/blog/' . rawurlencode($slug),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'lastmod' => $lastmod,
                ];
            }
        }
    }
} catch (\Throwable $e) {
    // Graceful degradation: log error without breaking generation
    error_log("Sitemap generation database warning: " . $e->getMessage());
}

// Include Programmatic SEO Prompt Guides
$promptPages = [];
if (class_exists('\App\services\PromptGuideService')) {
    foreach (\App\services\PromptGuideService::all() as $guideSlug => $guide) {
        $promptPages[] = [
            'loc' => $baseUrl . '/prompts/' . $guideSlug,
            'changefreq' => 'weekly',
            'priority' => '0.8',
            'lastmod' => date('Y-m-d'),
        ];
    }
}

$allPages = array_merge($corePages, $promptPages, $blogPages);

// Build the XML via shared-lib (rejects .test/.local/localhost hosts by design)
$xml = \Src\SeoEngine::generateSitemap($allPages);

// Write both root and public/ sitemap.xml
$rootSitemap = $rootDir . '/sitemap.xml';
$pubSitemap = $publicDir . '/sitemap.xml';

file_put_contents($rootSitemap, $xml);
file_put_contents($pubSitemap, $xml);

echo "✅ Sitemap generated successfully with " . count($allPages) . " URLs.\n";
echo "   ↳ Root   : {$rootSitemap}\n";
echo "   ↳ Public : {$pubSitemap}\n";

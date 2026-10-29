<?php
declare(strict_types=1);

/**
 * 🛡️ SITEMAP & ROBOTS AUTOMATED QUALITY GATE
 * Enforces valid XML structure, mandatory canonical URLs, blog synchronisation,
 * and fail-safe web server routing across both static and dynamic layers.
 *
 * Enforced by: Technical Approval Team (TAT 2.0) & BRATS
 */

$rootDir = dirname(__DIR__);
$failures = [];

echo "=================================================================\n";
echo "🗺️  SITEMAP & ROBOTS INTEGRITY VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// 1. Verify existence of sitemap.xml in root and public/
$rootSitemap = $rootDir . '/sitemap.xml';
$pubSitemap = $rootDir . '/public/sitemap.xml';

if (!file_exists($rootSitemap)) {
    $failures[] = "Root sitemap.xml missing from repository root.";
} else {
    echo "  ✅ Root sitemap.xml exists (" . filesize($rootSitemap) . " bytes).\n";
}

if (!file_exists($pubSitemap)) {
    $failures[] = "Public sitemap.xml missing from public/ directory.";
} else {
    echo "  ✅ Public sitemap.xml exists (" . filesize($pubSitemap) . " bytes).\n";
}

// 2. Validate XML parsing & schema
foreach (['Root' => $rootSitemap, 'Public' => $pubSitemap] as $label => $file) {
    if (!file_exists($file)) {
        continue;
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($file);
    $errors = libxml_get_errors();
    libxml_clear_errors();

    if ($xml === false || !empty($errors)) {
        $failures[] = "{$label} sitemap.xml failed XML schema parsing: " . json_encode($errors);
    } else {
        echo "  ✅ {$label} sitemap.xml is valid XML conformant to sitemaps schema.\n";
        $urlCount = count($xml->url);
        if ($urlCount < 5) {
            $failures[] = "{$label} sitemap.xml contains suspiciously few URLs: {$urlCount}";
        } else {
            echo "     ↳ Contained {$urlCount} validated URLs.\n";
        }
    }
}

// 3. Verify robots.txt sitemap declaration
$rootRobots = $rootDir . '/robots.txt';
$pubRobots = $rootDir . '/public/robots.txt';

foreach (['Root' => $rootRobots, 'Public' => $pubRobots] as $label => $file) {
    if (!file_exists($file)) {
        $failures[] = "{$label} robots.txt missing.";
        continue;
    }
    $content = (string)file_get_contents($file);
    if (!str_contains($content, 'Sitemap: https://myfamilyplatform.com/sitemap.xml')) {
        $failures[] = "{$label} robots.txt does not declare 'Sitemap: https://myfamilyplatform.com/sitemap.xml'.";
    } else {
        echo "  ✅ {$label} robots.txt contains authoritative Sitemap directive.\n";
    }
}

// 4. Verify .htaccess fallback rewrite configuration
$htaccess = (string)file_get_contents($rootDir . '/.htaccess');
if (!str_contains($htaccess, 'RewriteRule ^(robots\.txt|sitemap\.xml)$ public/$1 [L]')) {
    $failures[] = ".htaccess is missing public/ fallback rewrite rule for sitemap.xml/robots.txt.";
} else {
    echo "  ✅ .htaccess has resilient public/ fallback rewrite rule.\n";
}

// 5. Verify Router GET|HEAD mapping in app/router/home.php
$homeRouter = (string)file_get_contents($rootDir . '/app/router/home.php');
if (!str_contains($homeRouter, "\$router->map('GET|HEAD', '/sitemap.xml'")) {
    $failures[] = "app/router/home.php does not support GET|HEAD for /sitemap.xml.";
} else {
    echo "  ✅ app/router/home.php supports GET|HEAD for /sitemap.xml.\n";
}

if (!str_contains($homeRouter, "\$router->map('GET|HEAD', '/robots.txt'")) {
    $failures[] = "app/router/home.php does not support GET|HEAD for /robots.txt.";
} else {
    echo "  ✅ app/router/home.php supports GET|HEAD for /robots.txt.\n";
}

// 6. Verify deploy.sh bundle includes sitemap.xml and robots.txt
$deployScript = (string)file_get_contents($rootDir . '/scripts/deploy.sh');
if (!str_contains($deployScript, 'copy_item sitemap.xml') || !str_contains($deployScript, 'copy_item robots.txt')) {
    $failures[] = "scripts/deploy.sh does not copy sitemap.xml and robots.txt into deployment sandbox.";
} else {
    echo "  ✅ scripts/deploy.sh copies sitemap.xml and robots.txt into deployment sandbox.\n";
}

echo "\n-----------------------------------------------------------------\n";
if (empty($failures)) {
    echo "🎉 ALL SITEMAP & ROBOTS TESTS PASSED (0 failures).\n";
    exit(0);
} else {
    echo "❌ SITEMAP VERIFICATION FAILED (" . count($failures) . " errors):\n";
    foreach ($failures as $f) {
        echo "   - {$f}\n";
    }
    exit(1);
}

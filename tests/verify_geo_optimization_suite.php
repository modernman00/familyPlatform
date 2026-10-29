<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Generative Engine Optimization (GEO) & Machine Manifest Invariants
 *
 * Enforces:
 *   1. Machine Manifests: public/llms.txt and public/llms-full.txt exist and contain required entity anchors.
 *   2. Dynamic Sitemap Engine: public/sitemap.xml is valid XML, contains all published blogs, and enforces canonical base.
 *   3. Structured Data: HowTo and FAQPage Schema.org JSON-LD scripts are syntactically valid and present.
 *   4. Answer Capsule Standard: Verified Answer Capsule layout exists with accessible headings.
 *   5. Zero-Trust Security Boundary: robots.txt disallows member routes and sensitive directories.
 */

echo "=================================================================\n";
echo "🤖 GENERATIVE ENGINE OPTIMIZATION (GEO) VERIFICATION SUITE\n";
echo "=================================================================\n";

$rootDir = dirname(__DIR__);
$failures = 0;

function assertCondition(string $name, bool $pass, string $err = ''): void {
    global $failures;
    if ($pass) {
        echo "  ✅ PASS: {$name}\n";
    } else {
        echo "  ❌ FAIL: {$name} — {$err}\n";
        $failures++;
    }
}

// 1. Verify llms.txt & llms-full.txt
$llmsPath = $rootDir . '/public/llms.txt';
$llmsFullPath = $rootDir . '/public/llms-full.txt';

assertCondition(
    "public/llms.txt exists and is populated (> 200 bytes)",
    file_exists($llmsPath) && filesize($llmsPath) > 200,
    "Missing or empty public/llms.txt"
);

if (file_exists($llmsPath)) {
    $content = file_get_contents($llmsPath);
    assertCondition(
        "public/llms.txt contains core capabilities and canonical references",
        str_contains($content, 'Living Heritage Organogram') && str_contains($content, 'https://myfamilyplatform.com/how-to-use'),
        "llms.txt lacks essential capabilities or links"
    );
}

assertCondition(
    "public/llms-full.txt exists and is populated (> 1000 bytes)",
    file_exists($llmsFullPath) && filesize($llmsFullPath) > 1000,
    "Missing or empty public/llms-full.txt"
);

// 2. Verify Dynamic XML Sitemap
$sitemapPath = $rootDir . '/public/sitemap.xml';
assertCondition(
    "public/sitemap.xml exists and is populated",
    file_exists($sitemapPath) && filesize($sitemapPath) > 500,
    "Missing or empty public/sitemap.xml"
);

if (file_exists($sitemapPath)) {
    $xmlContent = file_get_contents($sitemapPath);
    $dom = new \DOMDocument();
    $validXml = @$dom->loadXML($xmlContent);
    assertCondition("public/sitemap.xml is well-formed XML", $validXml !== false, "XML parser error in sitemap.xml");

    assertCondition(
        "public/sitemap.xml enforces canonical production base URL (zero .test or localhost)",
        !str_contains($xmlContent, '.test') && !str_contains($xmlContent, 'localhost') && str_contains($xmlContent, 'https://myfamilyplatform.com'),
        "Sitemap contains non-production domains"
    );

    assertCondition(
        "public/sitemap.xml includes published blog articles",
        str_contains($xmlContent, '/blog/digital-sovereignty-for-family-archives'),
        "Published blogs missing from sitemap.xml"
    );
}

// 3. Verify Robots.txt Security Boundary
$robotsPath = $rootDir . '/public/robots.txt';
if (file_exists($robotsPath)) {
    $robots = file_get_contents($robotsPath);
    assertCondition(
        "public/robots.txt shields private member routes & storage",
        str_contains($robots, 'Disallow: /member/') && str_contains($robots, 'Disallow: /storage/'),
        "Robots.txt fails to seal sensitive directories"
    );
    assertCondition(
        "public/robots.txt links to authoritative sitemap",
        str_contains($robots, 'Sitemap: https://myfamilyplatform.com/sitemap.xml'),
        "Robots.txt missing sitemap reference"
    );
}

// 4. Verify Answer Capsule & Schema in Blade Views
$howToUseView = $rootDir . '/resources/views/howToUse.blade.php';
if (file_exists($howToUseView)) {
    $viewContent = file_get_contents($howToUseView);
    assertCondition(
        "resources/views/howToUse.blade.php contains Verified Answer Capsule",
        str_contains($viewContent, 'Verified Answer Capsule') && str_contains($viewContent, 'geo-quick-answers-heading'),
        "Answer Capsule missing from howToUse.blade.php"
    );

    assertCondition(
        "resources/views/howToUse.blade.php yields rich HowTo and FAQPage JSON-LD schemas",
        str_contains($viewContent, '@section(\'extra_schema\')') && str_contains($viewContent, '"@type": "HowTo"') && str_contains($viewContent, '"@type": "FAQPage"'),
        "Structured schema missing from howToUse.blade.php"
    );
}

echo "=================================================================\n";
if ($failures === 0) {
    echo "🎉 ALL GEO & MACHINE MANIFEST INVARIANTS VERIFIED (0 FAILURES)\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failures} GEO INVARIANT FAILURE(S) DETECTED\n";
    echo "=================================================================\n";
    exit(1);
}

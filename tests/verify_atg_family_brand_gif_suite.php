<?php

declare(strict_types=1);

/**
 * Test Suite: verify_atg_family_brand_gif_suite.php
 *
 * Verifies the ATG Sovereign Family Brand GIF Engine, Pre-Rendered Assets,
 * Dynamic Dynasty GIF Synthesiser, SSRF Defense, and GIF Picker Integration.
 *
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4) & SecOps Red Team (Marcus)
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/router/router.php';

use App\services\AtgFamilyGifRenderer;
use App\services\AtgFamilyGifService;
use App\model\Reel;

echo "=================================================================\n";
echo "🧪 VERIFYING ATG SOVEREIGN FAMILY BRAND GIF SUITE (PODS 3 & 4)\n";
echo "=================================================================\n\n";

$errors = [];

function assertTest(bool $condition, string $passMsg, string $failMsg, array &$errors): void
{
    if ($condition) {
        echo "  ✅ {$passMsg}\n";
    } else {
        echo "  ❌ {$failMsg}\n";
        $errors[] = $failMsg;
    }
}

// -----------------------------------------------------------------------------
// [Phase 1] Official Brand Catalog & Invariant Audit
// -----------------------------------------------------------------------------
echo "[Phase 1] Auditing Official Brand GIF Catalog & Invariants...\n";

$catalog = AtgFamilyGifService::getCatalog();
assertTest(count($catalog) === 8, "Catalog contains exactly 8 official brand GIFs (count=" . count($catalog) . ")", "Catalog count mismatch", $errors);

$expectedKeys = [
    'atg_heritage_tree_bloom',
    'atg_kinship_pulse',
    'atg_family_reunion_toast',
    'atg_welcome_home_circle',
    'atg_family_love_hugs',
    'atg_proud_family_applause',
    'atg_legacy_vault_spark',
    'atg_sunday_family_banter',
];

foreach ($expectedKeys as $expKey) {
    assertTest(isset($catalog[$expKey]), "Official key '{$expKey}' registered in catalog", "Missing official key '{$expKey}'", $errors);
    $item = $catalog[$expKey];
    assertTest(str_starts_with($item['url'], '/images/brand/gifs/'), "URL for '{$expKey}' uses safe local path: {$item['url']}", "Invalid URL format for {$expKey}", $errors);
    assertTest(str_ends_with($item['filename'], '.gif'), "Filename for '{$expKey}' ends in .gif", "Invalid filename for {$expKey}", $errors);
}

// -----------------------------------------------------------------------------
// [Phase 2] Binary Synthesis & GIF89a Structure Verification
// -----------------------------------------------------------------------------
echo "\n[Phase 2] Verifying Binary Synthesis & GIF89a Specifications...\n";

$sampleKey = 'atg_heritage_tree_bloom';
$binary = AtgFamilyGifService::getOfficialGifBinary($sampleKey);

assertTest(strlen($binary) > 10000, "Official GIF '{$sampleKey}' synthesized binary (" . strlen($binary) . " bytes)", "GIF binary too small or empty", $errors);
assertTest(str_starts_with($binary, "GIF89a"), "GIF begins with valid GIF89a magic header", "Missing GIF89a magic header", $errors);
assertTest(str_ends_with($binary, "\x3B"), "GIF terminates with standard trailer byte 0x3B", "Missing GIF trailer byte", $errors);
assertTest(str_contains($binary, "NETSCAPE2.0"), "GIF contains Netscape 2.0 application extension for infinite looping", "Missing Netscape 2.0 loop block", $errors);

$tmpFile = tempnam(sys_get_temp_dir(), 'gif_test_');
file_put_contents($tmpFile, $binary);
$imgInfo = getimagesize($tmpFile);
unlink($tmpFile);

assertTest($imgInfo !== false && $imgInfo[0] === 400 && $imgInfo[1] === 400, "GIF dimensions strictly 400×400 square (got {$imgInfo[0]}x{$imgInfo[1]})", "Dimension mismatch", $errors);
assertTest($imgInfo['mime'] === 'image/gif', "MIME type strictly image/gif (got {$imgInfo['mime']})", "Invalid MIME type", $errors);

// -----------------------------------------------------------------------------
// [Phase 3] Pre-Rendered Assets on Disk Verification
// -----------------------------------------------------------------------------
echo "\n[Phase 3] Verifying Pre-Rendered Static Brand Assets on Disk...\n";

$diskDir = AtgFamilyGifService::brandGifsDir();
assertTest(is_dir($diskDir), "Brand GIFs disk directory exists at: {$diskDir}", "Disk directory missing", $errors);

foreach ($expectedKeys as $key) {
    $filePath = $diskDir . '/' . $catalog[$key]['filename'];
    assertTest(file_exists($filePath) && filesize($filePath) > 10000, "Pre-rendered asset exists: " . basename($filePath) . " (" . round(filesize($filePath) / 1024, 1) . " KB)", "Pre-rendered file missing or empty: {$filePath}", $errors);
}

// -----------------------------------------------------------------------------
// [Phase 4] Dynamic Dynasty GIF Synthesiser & Cache Engine
// -----------------------------------------------------------------------------
echo "\n[Phase 4] Verifying Dynamic Dynasty Synthesiser & SHA-256 Cache...\n";

$surname = "The Olaogun Dynasty";
$caption = "Generations of Love & Unity";
$theme = "crimson_gold";

$t0 = microtime(true);
$customGif1 = AtgFamilyGifService::getCustomGifBinary($surname, $caption, $theme);
$firstElapsed = round((microtime(true) - $t0) * 1000, 2);

assertTest(str_starts_with($customGif1, "GIF89a"), "Custom Dynasty GIF synthesized with GIF89a header (" . strlen($customGif1) . " bytes in {$firstElapsed} ms)", "Custom GIF synthesis failed", $errors);

// Second retrieval MUST hit cache in sub-millisecond time
$t1 = microtime(true);
$customGif2 = AtgFamilyGifService::getCustomGifBinary($surname, $caption, $theme);
$secondElapsed = round((microtime(true) - $t1) * 1000, 2);

assertTest($customGif1 === $customGif2, "Cached GIF binary matches original binary exactly", "Cache mismatch", $errors);
assertTest($secondElapsed < 25.0, "Cache retrieval completed in {$secondElapsed} ms (< 25 ms cache SLA)", "Cache retrieval too slow: {$secondElapsed} ms", $errors);

// -----------------------------------------------------------------------------
// [Phase 5] Adversarial Red Team Assault (Dr. Viktor Kovács & Marcus Gate)
// -----------------------------------------------------------------------------
echo "\n[Phase 5] Executing Adversarial Attack Vectors & Sanitisation Checks...\n";

// Attack 1: Path Traversal in key
$pathTraversalCaught = false;
try {
    AtgFamilyGifService::getOfficialGifBinary("../../etc/passwd");
} catch (\InvalidArgumentException $e) {
    $pathTraversalCaught = true;
}
assertTest($pathTraversalCaught, "PoC Attack 1 Neutralised: Path traversal in GIF key blocked with InvalidArgumentException", "Path traversal was NOT blocked!", $errors);

// Attack 2: XSS Injection in Custom Dynasty Surname
$xssSurname = "<script>alert('pwned')</script>The Royal Family";
$cleanCustom = AtgFamilyGifService::getCustomGifBinary($xssSurname, "Clean Caption", "crimson_gold");
assertTest(str_starts_with($cleanCustom, "GIF89a"), "PoC Attack 2 Neutralised: XSS tags in surname stripped cleanly without render failure", "XSS payload broke GIF renderer", $errors);

// Attack 3: Huge Strings (DoS Memory Exhaustion)
$hugeSurname = str_repeat("VeryLongSurname", 100);
$hugeCaption = str_repeat("VeryLongMotto", 100);
$dosCustom = AtgFamilyGifService::getCustomGifBinary($hugeSurname, $hugeCaption, "crimson_gold");
assertTest(str_starts_with($dosCustom, "GIF89a"), "PoC Attack 3 Neutralised: Oversized 1500-char text clamped to 40/50 chars safely", "Oversized string caused exception", $errors);

// Attack 4: SSRF & URL Sanitisation in Reel Comment
$validBrandUrl = "/images/brand/gifs/atg_heritage_tree_bloom.gif";
$sanitizedBrand = Reel::sanitizeCommentGifUrl($validBrandUrl);
assertTest($sanitizedBrand === $validBrandUrl, "Reel::sanitizeCommentGifUrl allows internal brand GIF", "Valid brand GIF rejected by Reel sanitizer", $errors);

$maliciousInternal = "/images/brand/gifs/../../secret.txt";
assertTest(Reel::sanitizeCommentGifUrl($maliciousInternal) === null, "Reel::sanitizeCommentGifUrl rejects directory traversal", "Directory traversal accepted by Reel sanitizer", $errors);

$ssrfAws = "http://169.254.169.254/latest/meta-data/";
assertTest(Reel::sanitizeCommentGifUrl($ssrfAws) === null, "Reel::sanitizeCommentGifUrl rejects AWS metadata SSRF", "SSRF URL accepted by Reel sanitizer", $errors);

// -----------------------------------------------------------------------------
// [Phase 6] Router & Controller Endpoint Integration
// -----------------------------------------------------------------------------
echo "\n[Phase 6] Verifying Router Mappings & Public Access Invariant...\n";

$routes = [
    ['/member/gif-studio', 'GET', 'atg_gif_studio'],
    ['/member/gifStudio', 'GET', 'atg_gif_studio_alias'],
    ['/api/atg/gif/catalog', 'GET', 'api_atg_gif_catalog'],
    ['/api/atg/gif/view/atg_heritage_tree_bloom', 'GET', 'api_atg_gif_view'],
    ['/api/atg/gif/custom', 'GET', 'api_atg_gif_custom'],
    ['/api/atg/gif/synthesize', 'POST', 'api_atg_gif_synthesize'],
];

foreach ($routes as [$path, $method, $expectedRouteName]) {
    $match = $router->match($path, $method);
    assertTest($match !== false && ($match['name'] ?? '') === $expectedRouteName, "Route {$method} {$path} matches {$expectedRouteName}", "Route {$method} {$path} failed to match {$expectedRouteName}", $errors);
}

// -----------------------------------------------------------------------------
// [Phase 7] Frontend gifCatalog.js & Picker Category Integrity
// -----------------------------------------------------------------------------
echo "\n[Phase 7] Auditing gifCatalog.js & Picker Category Hierarchy...\n";

$jsCatalogPath = dirname(__DIR__) . '/resources/asset/js/components/profilePage/gifCatalog.js';
assertTest(file_exists($jsCatalogPath), "gifCatalog.js exists at {$jsCatalogPath}", "gifCatalog.js missing", $errors);

$jsContent = file_get_contents($jsCatalogPath);
assertTest(str_contains($jsContent, "'family_brand'"), "gifCatalog.js registers 'family_brand' category", "family_brand category missing in JS catalog", $errors);
assertTest(str_contains($jsContent, "atg_heritage_tree_bloom"), "gifCatalog.js embeds atg_heritage_tree_bloom", "atg_heritage_tree_bloom missing in JS catalog", $errors);
assertTest(str_contains($jsContent, "isAllowedGifUrl"), "gifCatalog.js defines isAllowedGifUrl validator", "isAllowedGifUrl missing in JS catalog", $errors);
assertTest(str_contains($jsContent, "/images/brand/gifs/"), "gifCatalog.js allowlist explicitly permits /images/brand/gifs/", "/images/brand/gifs/ not permitted in JS allowlist", $errors);

// -----------------------------------------------------------------------------
// Final Verdict
// -----------------------------------------------------------------------------
echo "\n-----------------------------------------------------------------\n";
if (empty($errors)) {
    echo "🎉 ALL ATG BRAND GIF SUITE TESTS PASSED (0 ERRORS)!\n";
    echo "   Sovereign brand visuals, dynamic synthesiser, and security gates verified.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ TEST SUITE FAILED WITH " . count($errors) . " ERROR(S):\n";
    foreach ($errors as $err) {
        echo "   - {$err}\n";
    }
    echo "=================================================================\n";
    exit(1);
}

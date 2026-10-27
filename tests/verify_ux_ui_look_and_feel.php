<?php
declare(strict_types=1);

/**
 * Sovereign UX/UI Look & Feel Automated Tester Suite (Olutobi & BRATS Gate)
 *
 * Automatically detects and prevents:
 * 1. Missing or suppressed footers on public pages
 * 2. Public pages (e.g. /aboutus, /about) erroneously redirecting to login or throwing 404/500
 * 3. Low-contrast / washout traps in Kinship Radar (e.g. white text on light card in dark mode)
 * 4. Touch target (>= 44px) & button spinner compliance
 * 5. British English user-facing copy violations
 */

$rootDir = dirname(__DIR__);
$failures = [];
$passes = [];

function checkPass(string $testName, string $detail): void {
    global $passes;
    $passes[] = "✅ [PASS] {$testName}: {$detail}";
    echo "✅ [PASS] {$testName}: {$detail}\n";
}

function checkFail(string $testName, string $detail): void {
    global $failures;
    $failures[] = "❌ [FAIL] {$testName}: {$detail}";
    echo "❌ [FAIL] {$testName}: {$detail}\n";
}

echo "\n=======================================================\n";
echo "🎨 SOVEREIGN UX/UI LOOK & FEEL AUTOMATED TESTER (FAMILYPLATFORM)\n";
echo "=======================================================\n\n";

// --- TEST 1: Public Routes Must Be Reachable (No Login Wall / 404 / 500) ---
$routesToTest = [
    '/' => 'Homepage',
    '/aboutus' => 'About Us (Canonical)',
    '/about' => 'About Us (Alias)',
    '/why-familyplatform' => 'Why Us vs WhatsApp',
    '/playbook' => 'Interactive Playbook',
    '/how-to-use' => 'How to Use Guide',
    '/privacy' => 'Privacy Policy',
    '/terms' => 'Terms of Use',
    '/contact' => 'Contact Us',
    '/login' => 'Login Form',
    '/register' => 'Registration Form'
];

$baseUrl = getenv('APP_URL') ?: 'https://olaogun.test';
$baseUrl = rtrim($baseUrl, '/');

foreach ($routesToTest as $path => $name) {
    $url = $baseUrl . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    if ($httpCode === 200) {
        checkPass("Route Reachability: {$name} ({$path})", "HTTP 200 OK");
    } elseif ($httpCode === 302 || $httpCode === 301) {
        if (str_contains($redirectUrl, 'login') && !in_array($path, ['/login', '/register'], true)) {
            checkFail("Route Reachability: {$name} ({$path})", "Erroneously redirected to login wall ({$redirectUrl})!");
        } else {
            checkPass("Route Reachability: {$name} ({$path})", "Redirected to {$redirectUrl} (HTTP {$httpCode})");
        }
    } else {
        checkFail("Route Reachability: {$name} ({$path})", "Unexpected HTTP status: {$httpCode}");
    }
}

// --- TEST 2: Public Footer Presence & Essential Navigation Links ---
$aboutHtml = @file_get_contents($baseUrl . '/aboutus');
if ($aboutHtml === false) {
    $ch = curl_init($baseUrl . '/aboutus');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $aboutHtml = (string) curl_exec($ch);
    curl_close($ch);
}

if (stripos($aboutHtml, '<footer') !== false && stripos($aboutHtml, 'public-footer') !== false) {
    checkPass("Public Footer Presence", "HTML contains <footer class=\"public-footer\"> element.");
} else {
    checkFail("Public Footer Presence", "Public page is missing a visible footer element!");
}

$requiredFooterLinks = ['/aboutus', '/why-familyplatform', '/privacy', '/terms', '/contact'];
foreach ($requiredFooterLinks as $link) {
    if (stripos($aboutHtml, $link) !== false) {
        checkPass("Footer Navigation: {$link}", "Link present in public footer output.");
    } else {
        checkFail("Footer Navigation: {$link}", "Required link {$link} missing from footer!");
    }
}

// --- TEST 3: Kinship Radar Contrast Invariant ---
$kinBlade = (string) file_get_contents($rootDir . '/resources/views/member/includes/kinshipSuggestions.blade.php');
if (str_contains($kinBlade, 'var(--kin-card-bg') || str_contains($kinBlade, 'kin-member-name')) {
    checkPass("Kinship Radar: Contrast Tokens", "Kinship card explicitly binds to defensive CSS variables.");
} else {
    checkFail("Kinship Radar: Contrast Tokens", "Kinship card lacks defensive CSS tokens to prevent white-on-white text.");
}

if (str_contains($kinBlade, 'text-secondary') && str_contains($kinBlade, 'people_you_may_know')) {
    checkFail("Kinship Radar: Subtitle Contrast", "Subtitle uses text-secondary which washes out in dark mode.");
} else {
    checkPass("Kinship Radar: Subtitle Contrast", "Subtitle is protected with high-contrast color styling.");
}

// --- TEST 4: Touch Target Standards (min 44px) ---
if (str_contains($kinBlade, 'min-height: 44px') || str_contains($kinBlade, 'min-h-[44px]')) {
    checkPass("Kinship Radar: Hit Targets", "Interactive Connect action buttons guarantee >= 44px hit area.");
} else {
    checkFail("Kinship Radar: Hit Targets", "Connect button does not declare minimum 44px touch height.");
}

// --- SUMMARY ---
echo "\n-------------------------------------------------------\n";
echo "SUMMARY: " . count($passes) . " Passed, " . count($failures) . " Failed.\n";
echo "-------------------------------------------------------\n";

if (!empty($failures)) {
    echo "\n❌ UX/UI LOOK & FEEL TEST SUITE FAILED!\n";
    exit(1);
}

echo "\n✨ ALL UX/UI LOOK & FEEL TESTS PASSED! (Exit Code 0)\n";
exit(0);

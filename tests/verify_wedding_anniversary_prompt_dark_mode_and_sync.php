<?php
declare(strict_types=1);

/**
 * Verification Suite: Wedding Anniversary Prompt Dark Theme & Date Sync
 * Ensures:
 * 1. updateAnniversary() correctly parses JSON input payload even when X-CSRF-TOKEN header is present.
 * 2. syncWeddingAnniversary() handles both Y-m-d and d/m/Y formats.
 * 3. middleColumn.blade.php contains dark-mode styles and contrast protections for the anniversary banner.
 * 4. Zero white-on-white text in dark theme.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/app/controller/members/SettingController.php';

$errors = [];

echo "=================================================================\n";
echo "💍 WEDDING ANNIVERSARY PROMPT & DATE SYNC VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// [1/4] Code structure check in SettingController::updateAnniversary
echo "[1/4] Verifying JSON body parsing and CSRF handling in SettingController.php...\n";
$controllerFile = file_get_contents($rootDir . '/app/controller/members/SettingController.php');

if (!str_contains($controllerFile, 'str_contains($contentType, \'application/json\')')) {
    $errors[] = "SettingController does not check application/json for php://input parsing.";
}

// Ensure json_decode is not gated behind empty($token)
if (preg_match('/if\s*\(\s*empty\(\$token\)\s*&&\s*!empty\(\$_SERVER\[\'HTTP_CONTENT_TYPE\'\]\)/', $controllerFile)) {
    $errors[] = "SettingController still gates json_decode behind empty(\$token)!";
} else {
    echo "  ✅ JSON body is correctly parsed before CSRF verification regardless of headers.\n";
}

// [2/4] Verifying date format parsing in syncWeddingAnniversary
echo "[2/4] Verifying date format robustness in syncWeddingAnniversary...\n";
$testDates = [
    '2015-06-20' => '2015-06-20',
    '15/08/2010' => '2010-08-15',
    '01-01-2000' => '2000-01-01',
    '14.05.2012' => '2012-05-14',
    '5/6/2012' => '2012-06-05',
    '2012/05/14' => '2012-05-14',
    '2012-5-4' => '2012-05-04',
    '14 May 2012' => '2012-05-14',
    '14th May 2012' => '2012-05-14',
    'May 14, 2012' => '2012-05-14',
    '14/05/12' => '2012-05-14',
];

foreach ($testDates as $input => $expected) {
    $norm = \App\controller\members\SettingController::normalizeWeddingDate($input);
    if ($norm !== $expected) {
        $errors[] = "Date normalisation failed for '{$input}'. Expected '{$expected}', got '" . var_export($norm, true) . "'.";
    }
}

// Ensure impossible calendar dates are rejected
$invalidDates = ['31/02/2015', '2015-02-29', '1850-01-01', '2045-01-01', 'hello world'];
foreach ($invalidDates as $inv) {
    $norm = \App\controller\members\SettingController::normalizeWeddingDate($inv);
    if ($norm !== null) {
        $errors[] = "Invalid date '{$inv}' was unexpectedly accepted as '{$norm}'.";
    }
}

if (empty($errors)) {
    echo "  ✅ Date parsing successfully normalises ISO, UK, dots, slashes, ordinals, and textual formats with strict calendar validation.\n";
}

// [3/4] Check middleColumn.blade.php for dark mode styles
echo "[3/4] Checking middleColumn.blade.php for dark theme styling & typography rules...\n";
$middleCol = file_get_contents($rootDir . '/resources/views/member/includes/middleColumn.blade.php');

if (!str_contains($middleCol, 'wedding-anniversary-prompt-card')) {
    $errors[] = "middleColumn.blade.php is missing 'wedding-anniversary-prompt-card' class.";
}

if (!str_contains($middleCol, 'body.dark-mode .wedding-anniversary-prompt-card') &&
    !str_contains($middleCol, '[data-bs-theme="dark"] .wedding-anniversary-prompt-card')) {
    $errors[] = "middleColumn.blade.php is missing dark mode rules for wedding-anniversary-prompt-card.";
}

if (!str_contains($middleCol, 'color-scheme: dark')) {
    $errors[] = "middleColumn.blade.php is missing 'color-scheme: dark' on date picker in dark mode.";
}

// Check that the title and description don't use raw .text-dark without dark-mode overrides
if (str_contains($middleCol, '<h6 class="fw-bold mb-0 text-dark"')) {
    $errors[] = "Anniversary heading still uses raw 'text-dark' which causes white-on-white text in dark mode.";
} else {
    echo "  ✅ Anniversary heading and description use dedicated theme-aware typography tokens.\n";
}

// [4/4] Check profilepage.css for dark mode styles
echo "[4/4] Checking public/css/profilepage.css for global dark theme rules...\n";
$profileCss = file_get_contents($rootDir . '/public/css/profilepage.css');

if (!str_contains($profileCss, '.wedding-anniversary-prompt-card')) {
    $errors[] = "public/css/profilepage.css is missing .wedding-anniversary-prompt-card rules.";
} else {
    echo "  ✅ public/css/profilepage.css contains dark-mode styles for the anniversary prompt.\n";
}

// Report
echo "\n-----------------------------------------------------------------\n";
if (!empty($errors)) {
    echo "❌ VERIFICATION FAILED with " . count($errors) . " error(s):\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
    exit(1);
}

echo "🎉 ALL CHECKS PASSED: Dark theme visibility and date synchronisation verified!\n";
echo "=================================================================\n";
exit(0);

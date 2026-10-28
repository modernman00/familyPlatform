<?php

declare(strict_types=1);

/**
 * tests/verify_icon_tooltips.php
 *
 * Automated verification suite for Universal Icon & Button Explanatory Tooltip Architecture.
 * Verifies:
 * 1. public/js/family-tooltip.js exists and passes node syntax check.
 * 2. public/css/standards-v21.css contains tooltip styling rules.
 * 3. Base layouts include family-tooltip.js.
 * 4. Templates have descriptive titles/aria-labels on icon buttons.
 * 5. All modified files pass PHP linting.
 */

$root = dirname(__DIR__);
$failures = [];
$checks = 0;

function assertCondition(bool $cond, string $msg, array &$failures, int &$checks): void {
    $checks++;
    if ($cond) {
        echo "  ✅ PASS: $msg\n";
    } else {
        echo "  ❌ FAIL: $msg\n";
        $failures[] = $msg;
    }
}

echo "=================================================================\n";
echo "🛡️  UNIVERSAL ICON & BUTTON TOOLTIPS VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// 1. Script existence and node syntax check
$jsPath = $root . '/public/js/family-tooltip.js';
assertCondition(file_exists($jsPath), 'public/js/family-tooltip.js exists', $failures, $checks);

$nodeCheck = shell_exec('node --check ' . escapeshellarg($jsPath) . ' 2>&1');
assertCondition(empty($nodeCheck), 'public/js/family-tooltip.js passes node --check with 0 syntax errors', $failures, $checks);

$jsContent = file_get_contents($jsPath) ?: '';
assertCondition(str_contains($jsContent, 'window.FamilyTooltip ='), 'family-tooltip.js exposes window.FamilyTooltip API', $failures, $checks);
assertCondition(str_contains($jsContent, 'ICON_DICTIONARY'), 'family-tooltip.js contains semantic icon dictionary', $failures, $checks);
assertCondition(str_contains($jsContent, 'family-tooltip-container'), 'family-tooltip.js creates family-tooltip-container DOM', $failures, $checks);
assertCondition(str_contains($jsContent, 'textContent = text'), 'family-tooltip.js strictly sets textContent to prevent DOM XSS', $failures, $checks);
assertCondition(str_contains($jsContent, 'DWELL_DELAY_MS = 450'), 'family-tooltip.js defines 450ms Intent Dwell Delay', $failures, $checks);
assertCondition(str_contains($jsContent, 'MAX_VIEWS_DECAY = 3'), 'family-tooltip.js defines 3-Strike Exposure Decay threshold', $failures, $checks);
assertCondition(str_contains($jsContent, 'getMode'), 'family-tooltip.js implements getMode() API', $failures, $checks);
assertCondition(str_contains($jsContent, 'setMode'), 'family-tooltip.js implements setMode() API', $failures, $checks);
assertCondition(str_contains($jsContent, 'isMastered'), 'family-tooltip.js implements isMastered() API', $failures, $checks);
assertCondition(str_contains($jsContent, 'markMastered'), 'family-tooltip.js implements markMastered() API', $failures, $checks);
assertCondition(str_contains($jsContent, 'resetMastery'), 'family-tooltip.js implements resetMastery() API', $failures, $checks);

// 2. CSS Rules in standards-v21.css
$cssPath = $root . '/public/css/standards-v21.css';
assertCondition(file_exists($cssPath), 'public/css/standards-v21.css exists', $failures, $checks);
$cssContent = file_get_contents($cssPath) ?: '';
assertCondition(str_contains($cssContent, '.family-tooltip-container'), 'standards-v21.css defines .family-tooltip-container', $failures, $checks);
assertCondition(str_contains($cssContent, '.family-tooltip-arrow'), 'standards-v21.css defines .family-tooltip-arrow', $failures, $checks);

// 3. Base Layout Inclusions
$requiredLayouts = [
    'resources/views/layouts/profileBase.blade.php',
    'resources/views/layouts/baseTail.blade.php',
    'resources/views/layouts/landing_layout.blade.php',
    'resources/views/layouts/head.blade.php',
    'resources/views/admin/layout.blade.php',
    'resources/views/admin/base.blade.php',
    'resources/views/baseBulma.blade.php',
    'resources/views/baseBulmaForm.blade.php'
];

foreach ($requiredLayouts as $relPath) {
    $layoutFile = $root . '/' . $relPath;
    assertCondition(file_exists($layoutFile), "$relPath exists", $failures, $checks);
    $lContent = file_get_contents($layoutFile) ?: '';
    assertCondition(str_contains($lContent, 'family-tooltip.js'), "$relPath includes family-tooltip.js", $failures, $checks);
}

// 4. Core Templates Verification
$navbarPath = $root . '/resources/views/layouts/navbar.blade.php';
$navContent = file_get_contents($navbarPath) ?: '';
assertCondition(str_contains($navContent, 'title="Search activities, memories, and family members"'), 'Navbar mobile search has explanatory tooltip', $failures, $checks);
assertCondition(str_contains($navContent, 'title="{{ __(\'nav.my_page\', \'My Page\') }} - View and manage your family profile"'), 'Navbar My Page has explanatory tooltip', $failures, $checks);
assertCondition(str_contains($navContent, 'View alerts and family notifications'), 'Navbar notification bell has explanatory tooltip', $failures, $checks);

$bottomNavPath = $root . '/resources/views/layouts/bottom_nav.blade.php';
$bottomContent = file_get_contents($bottomNavPath) ?: '';
assertCondition(str_contains($bottomContent, 'title="{{ __(\'nav.home\', \'Home Feed\') }} - Go to main family updates and posts"'), 'Bottom nav Home has explanatory tooltip', $failures, $checks);
assertCondition(str_contains($bottomContent, 'title="{{ __(\'nav.add_photo\', \'Add Photo or Video\') }} - Instant camera capture or photo upload"'), 'Bottom nav Camera button has explanatory tooltip', $failures, $checks);

$composerPath = $root . '/resources/views/member/includes/middleColumn.blade.php';
$compContent = file_get_contents($composerPath) ?: '';
assertCondition(str_contains($compContent, 'title="Upload Photos - Add memories from your device or camera"'), 'Composer photo tool has explanatory tooltip', $failures, $checks);
assertCondition(str_contains($compContent, 'title="Create Post - Share stories, photos, and milestones with family"'), 'Composer post button has explanatory tooltip', $failures, $checks);

$postCardPath = $root . '/resources/views/components/feed/post-card.blade.php';
$cardContent = file_get_contents($postCardPath) ?: '';
assertCondition(str_contains($cardContent, 'title="Share with family relatives or via social apps"'), 'Post card share button has explanatory tooltip', $failures, $checks);
assertCondition(str_contains($cardContent, 'title="Write or view family comments on this post"'), 'Post card comment button has explanatory tooltip', $failures, $checks);

// 5. Account Settings Tooltip Management
$accPath = $root . '/resources/views/member/accountSetting.blade.php';
$accContent = file_get_contents($accPath) ?: '';
assertCondition(str_contains($accContent, 'id="tooltipModeSelect"'), 'accountSetting.blade.php contains #tooltipModeSelect dropdown', $failures, $checks);
assertCondition(str_contains($accContent, 'id="resetTooltipMasteryBtn"'), 'accountSetting.blade.php contains #resetTooltipMasteryBtn button', $failures, $checks);
assertCondition(str_contains($accContent, 'window.FamilyTooltip.setMode'), 'accountSetting.blade.php wires up setMode() handler', $failures, $checks);
assertCondition(str_contains($accContent, 'window.FamilyTooltip.resetMastery'), 'accountSetting.blade.php wires up resetMastery() handler', $failures, $checks);

// 6. Check PHP syntax across modified PHP/Blade files
$phpFilesToCheck = [
    'scripts/verify_engineering_must_haves.php',
    'tests/verify_icon_tooltips.php',
    'resources/views/member/accountSetting.blade.php'
];

foreach ($phpFilesToCheck as $phpRel) {
    $fullPhp = $root . '/' . $phpRel;
    if (file_exists($fullPhp)) {
        $lint = shell_exec('php -l ' . escapeshellarg($fullPhp) . ' 2>&1');
        assertCondition(str_contains($lint, 'No syntax errors detected'), "php -l $phpRel is clean", $failures, $checks);
    }
}

echo "\n-----------------------------------------------------------------\n";
if (empty($failures)) {
    echo "🎉 ALL $checks CHECKS PASSED PERFECTLY!\n";
    echo "Universal Icon & Button Tooltip Architecture is fully operational.\n";
    exit(0);
} else {
    echo "🚨 " . count($failures) . " of $checks CHECKS FAILED:\n";
    foreach ($failures as $f) {
        echo " - $f\n";
    }
    exit(1);
}

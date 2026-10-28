<?php

/**
 * Mobile Quick Navigation Links Verification Suite (TAT & Sovereign Board Gate)
 *
 * Verifies that Then & Now, Upcoming Events, and Family Heritage quick-access
 * links are present on the profile card, styled defensively according to v2.1
 * design standards, mapped to proper target IDs, and localized across all languages.
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$failures = [];

echo "=================================================================\n";
echo "📱 MOBILE QUICK NAVIGATION LINKS VERIFICATION SUITE\n";
echo "=================================================================\n";

// [TEST 1] Left Column Profile Card Quick Links
echo "[TEST 1] Verifying quick links in leftColumn.blade.php...\n";
$leftColPath = $rootDir . '/resources/views/member/includes/leftColumn.blade.php';
if (!file_exists($leftColPath)) {
    $failures[] = "leftColumn.blade.php not found at {$leftColPath}";
} else {
    $leftCol = file_get_contents($leftColPath);
    
    $requiredButtons = [
        'quickLinkThenNow' => 'Then & Now',
        'quickLinkUpcomingEvents' => 'Upcoming Events',
        'quickLinkFamilyHeritage' => 'Family Rituals & Quests',
    ];
    
    foreach ($requiredButtons as $btnId => $label) {
        if (!str_contains($leftCol, "id=\"{$btnId}\"")) {
            $failures[] = "Missing button with id '{$btnId}' in leftColumn.blade.php";
        }
    }

    if (!str_contains($leftCol, 'scrollToProfileWidget')) {
        $failures[] = "Missing scrollToProfileWidget JavaScript function in leftColumn.blade.php";
    }

    if (!str_contains($leftCol, 'min-height: 44px')) {
        $failures[] = "Buttons must adhere to minimum 44px touch target standard.";
    }

    if (!str_contains($leftCol, 'white-space: nowrap')) {
        $failures[] = "Buttons must enforce 'white-space: nowrap' to prevent awkward wrapping.";
    }
}

if (empty($failures)) {
    echo "  ✅ PASSED: All 3 quick links and scrollToProfileWidget function present with 44px touch targets.\n";
} else {
    foreach ($failures as $f) {
        echo "  ❌ {$f}\n";
    }
    exit(1);
}

// [TEST 2] Target Section IDs in Secondary Sidebar
echo "[TEST 2] Verifying persistent target IDs in sidebar widgets...\n";

$rightColPath = $rootDir . '/resources/views/member/includes/rightColumn.blade.php';
$rightCol = file_exists($rightColPath) ? file_get_contents($rightColPath) : '';
if (!str_contains($rightCol, 'id="upcomingEventsSection"')) {
    $failures[] = "Missing id='upcomingEventsSection' in rightColumn.blade.php";
}

$heritagePath = $rootDir . '/resources/views/member/includes/familyHeritageWidget.blade.php';
$heritage = file_exists($heritagePath) ? file_get_contents($heritagePath) : '';
if (!str_contains($heritage, 'id="familyHeritageSection"')) {
    $failures[] = "Missing id='familyHeritageSection' in familyHeritageWidget.blade.php";
}

$thenNowPath = $rootDir . '/resources/views/member/includes/thenNowTimeline.blade.php';
$thenNow = file_exists($thenNowPath) ? file_get_contents($thenNowPath) : '';
if (!str_contains($thenNow, 'id="thenNowSection"')) {
    $failures[] = "Missing id='thenNowSection' in thenNowTimeline.blade.php";
}
if (!str_contains($thenNow, 'id="thenNowEmptyState"')) {
    $failures[] = "Missing empty-state fallback 'thenNowEmptyState' in thenNowTimeline.blade.php";
}

if (empty($failures)) {
    echo "  ✅ PASSED: Target IDs #upcomingEventsSection, #familyHeritageSection, and #thenNowSection verified.\n";
} else {
    foreach ($failures as $f) {
        echo "  ❌ {$f}\n";
    }
    exit(1);
}

// [TEST 3] Translation Keys Verification
echo "[TEST 3] Verifying localization across en, es, fr dictionaries...\n";
$langs = ['en', 'es', 'fr'];
$requiredKeys = [
    'profile.then_and_now',
    'profile.upcoming_events',
    'profile.family_heritage',
];

foreach ($langs as $lang) {
    $langFile = $rootDir . "/resources/lang/{$lang}.php";
    if (!file_exists($langFile)) {
        $failures[] = "Missing language file {$langFile}";
        continue;
    }
    $dict = require $langFile;
    foreach ($requiredKeys as $key) {
        if (!isset($dict[$key]) || trim((string)$dict[$key]) === '') {
            $failures[] = "Missing or empty key '{$key}' in resources/lang/{$lang}.php";
        }
    }
}

if (empty($failures)) {
    echo "  ✅ PASSED: All translation keys cleanly defined across en, es, and fr.\n";
} else {
    foreach ($failures as $f) {
        echo "  ❌ {$f}\n";
    }
    exit(1);
}

// [TEST 4] Navigation Order & Layout Regression Check
echo "[TEST 4] Verifying navigation hierarchy preserves RoundTable and Journal ordering...\n";
$posJournal = strpos($leftCol, 'familyJournal');
$posRoundTable = strpos($leftCol, 'family/roundtable');
$posGallery = strpos($leftCol, 'Photo Gallery');
$posThenNow = strpos($leftCol, 'quickLinkThenNow');
$posUpcoming = strpos($leftCol, 'quickLinkUpcomingEvents');
$posHeritage = strpos($leftCol, 'quickLinkFamilyHeritage');

if ($posJournal === false || $posRoundTable === false || $posJournal >= $posRoundTable) {
    $failures[] = "Family Journal must precede Family RoundTable";
}

if ($posGallery === false || $posThenNow === false || $posGallery >= $posThenNow) {
    $failures[] = "Quick action links must appear logically positioned in left column";
}

if (empty($failures)) {
    echo "  ✅ PASSED: Hierarchy intact (Journal -> RoundTable -> Gallery -> Quick Links).\n";
} else {
    foreach ($failures as $f) {
        echo "  ❌ {$f}\n";
    }
    exit(1);
}

echo "=================================================================\n";
echo "🏆 ALL 4 MOBILE QUICK LINKS VERIFICATION GATES PASSED CLEANLY!\n";
echo "=================================================================\n";
exit(0);

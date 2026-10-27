<?php
declare(strict_types=1);

/**
 * 🛡️ KINSHIP RADAR & DETAILS MODAL VERIFICATION SUITE
 * Verifies:
 *  1. Zero space when empty/null
 *  2. High-contrast text & robust name fallback (no missing or white-on-white names)
 *  3. Clickable card with data-kin payload
 *  4. Dark mode styling integrity
 *  5. Kinship details modal presence & structure
 *  6. JS event isolation (stopPropagation on connect & dismiss)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use eftec\bladeone\BladeOne;

$viewDir = dirname(__DIR__) . '/resources/views';
$cacheDir = dirname(__DIR__) . '/bootstrap/cache';
$blade = new BladeOne($viewDir, $cacheDir, BladeOne::MODE_DEBUG);

$failures = [];

echo "=================================================================\n";
echo "🛡️  RUNNING KINSHIP RADAR & DETAILS MODAL TEST SUITE\n";
echo "=================================================================\n\n";

// TEST 1: Zero Space Invariant
echo "[TEST 1] Testing zero space invariant when suggestions are empty or null...\n";
$emptyHtml = trim($blade->run('member.includes.kinshipSuggestions', [
    'suggestedKin' => [],
    'kinshipWidgetId' => 'kinshipRadarWidget'
]));
if ($emptyHtml !== '') {
    $failures[] = "Zero space failed: Expected empty string, got: " . substr($emptyHtml, 0, 100);
} else {
    echo "  ✅ PASS: Empty array produces exactly 0 bytes.\n";
}

$nullHtml = trim($blade->run('member.includes.kinshipSuggestions', [
    'suggestedKin' => null,
    'kinshipWidgetId' => 'kinshipRadarWidget'
]));
if ($nullHtml !== '') {
    $failures[] = "Zero space failed: Expected empty string on null, got: " . substr($nullHtml, 0, 100);
} else {
    echo "  ✅ PASS: Null parameter produces exactly 0 bytes.\n";
}

// TEST 2: Rendering with Name Fallback
echo "\n[TEST 2] Testing name fallback when firstName & lastName are missing...\n";
$suggestionsNoName = [
    [
        'user_id' => 'u999',
        'firstName' => '',
        'lastName' => '',
        'famCode' => 'OLAO60446',
        'profilePics' => '',
        'kinship_type' => 'Paternal Clan',
        'primary_reason' => 'Shared Family Surname (OLAOGUN)',
        'confidence_score' => 55
    ]
];
$renderedNoName = $blade->run('member.includes.kinshipSuggestions', [
    'suggestedKin' => $suggestionsNoName,
    'kinshipWidgetId' => 'kinshipRadarWidget'
]);

if (!str_contains($renderedNoName, 'Family Relative')) {
    $failures[] = "Name fallback failed: 'Family Relative' not found in rendered markup.";
} else {
    echo "  ✅ PASS: Fallback 'Family Relative' correctly rendered when names are blank.\n";
}

// TEST 3: Card is clickable and contains data-kin JSON payload
echo "\n[TEST 3] Testing card clickability and data-kin payload...\n";
$suggestionsNormal = [
    [
        'user_id' => 'u100',
        'firstName' => 'Femi',
        'lastName' => 'Olaogun',
        'famCode' => 'OLAO60446',
        'familyCode2' => 'ADE123',
        'maiden_name' => 'BALOGUN',
        'profilePics' => 'avatar1.jpg',
        'kinship_type' => 'Paternal Clan',
        'primary_reason' => 'Shared Family Surname (OLAOGUN)',
        'reasons' => ['Shared Family Surname (OLAOGUN)', 'Connected to family branch ADE123'],
        'confidence_score' => 75
    ]
];
$renderedNormal = $blade->run('member.includes.kinshipSuggestions', [
    'suggestedKin' => $suggestionsNormal,
    'kinshipWidgetId' => 'kinshipRadarWidget'
]);

if (!str_contains($renderedNormal, 'Femi Olaogun')) {
    $failures[] = "Normal kin name 'Femi Olaogun' not found.";
} else {
    echo "  ✅ PASS: Kin name 'Femi Olaogun' present.\n";
}

if (!str_contains($renderedNormal, 'data-kin=')) {
    $failures[] = "Card missing data-kin attribute for details modal.";
} else {
    echo "  ✅ PASS: data-kin attribute present with JSON payload.\n";
}

if (!str_contains($renderedNormal, 'kin-member-name')) {
    $failures[] = "kin-member-name class missing from card.";
} else {
    echo "  ✅ PASS: Semantic kin-member-name class verified.\n";
}

// TEST 4: Modal view existence and profilePage include
echo "\n[TEST 4] Testing modal template and integration in profilePage.blade.php...\n";
$modalPath = $viewDir . '/member/modals/kinshipDetailsModal.blade.php';
$profilePagePath = $viewDir . '/member/profilePage.blade.php';

if (!file_exists($modalPath)) {
    $failures[] = "Modal file does not exist: $modalPath";
} else {
    $modalContent = file_get_contents($modalPath);
    if (!str_contains($modalContent, 'id="kinshipDetailsModal"') ||
        !str_contains($modalContent, 'id="kinModalName"') ||
        !str_contains($modalContent, 'id="kinModalConnectBtn"')) {
        $failures[] = "Modal template missing expected DOM IDs.";
    } else {
        echo "  ✅ PASS: Modal template contains all required DOM structures.\n";
    }
}

$profileContent = file_get_contents($profilePagePath);
if (!str_contains($profileContent, "member.modals.kinshipDetailsModal")) {
    $failures[] = "profilePage.blade.php does not include member.modals.kinshipDetailsModal.";
} else {
    echo "  ✅ PASS: profilePage.blade.php includes member.modals.kinshipDetailsModal.\n";
}

// TEST 5: CSS Dark Mode contrast integrity
echo "\n[TEST 5] Testing CSS rules for dark mode contrast...\n";
$cssPath = dirname(__DIR__) . '/public/css/profilepage.css';
$cssContent = file_get_contents($cssPath);

if (!str_contains($cssContent, 'body.dark-mode .kinship-item-card') ||
    !str_contains($cssContent, 'body.dark-mode .kinship-item-card .kin-member-name') ||
    !str_contains($cssContent, 'color: #f8fafc !important')) {
    $failures[] = "Dark mode contrast styles missing for .kinship-item-card in profilepage.css.";
} else {
    echo "  ✅ PASS: Dark mode overrides for kinship cards and member names confirmed.\n";
}

// TEST 6: JS event isolation & modal triggering
echo "\n[TEST 6] Testing JavaScript event handlers in kinshipRadar.js...\n";
$jsPath = dirname(__DIR__) . '/resources/asset/js/components/kinship/kinshipRadar.js';
$jsContent = file_get_contents($jsPath);

if (!str_contains($jsContent, 'openKinshipDetailsModal') ||
    !str_contains($jsContent, 'initKinshipDetailsModal') ||
    !str_contains($jsContent, 'e.stopPropagation()')) {
    $failures[] = "kinshipRadar.js missing modal opener or event isolation.";
} else {
    echo "  ✅ PASS: JS includes openKinshipDetailsModal, initKinshipDetailsModal and e.stopPropagation().\n";
}

// SUMMARY
echo "\n-----------------------------------------------------------------\n";
if (!empty($failures)) {
    echo "❌ SUITE FAILED with " . count($failures) . " errors:\n";
    foreach ($failures as $f) {
        echo "   - $f\n";
    }
    exit(1);
} else {
    echo "🎉 ALL 6 VERIFICATION CHECKS PASSED WITH 0 ERRORS!\n";
    echo "=================================================================\n";
    exit(0);
}

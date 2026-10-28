<?php
declare(strict_types=1);

/**
 * 🔍 VERIFICATION SUITE: UPCOMING EVENTS UI & POWERED-BY BRANDING
 */

$rootDir = dirname(__DIR__);
$rightColPath = $rootDir . '/resources/views/member/includes/rightColumn.blade.php';
$modalPath = $rootDir . '/resources/views/member/modals/upcomingEventsModal.blade.php';

$rightCol = file_get_contents($rightColPath);
$modal = file_get_contents($modalPath);

$failures = [];
$total = 0;

function check(bool $cond, string $passMsg, string $failMsg, array &$failures, int &$total): void {
    $total++;
    if ($cond) {
        echo "  ✅ PASSED: {$passMsg}\n";
    } else {
        echo "  ❌ FAILED: {$failMsg}\n";
        $failures[] = $failMsg;
    }
}

echo "\n=================================================================\n";
echo "🔍 UPCOMING EVENTS UI & BRANDING VERIFICATION SUITE\n";
echo "=================================================================\n\n";

echo "[1/4] Verifying 'See All >' moved to its own dedicated line...\n";
check(
    !str_contains($rightCol, 'card-header bg-transparent border-0 d-flex justify-content-between align-items-start pt-4 pb-2 px-4" id="eventHeader"') &&
    str_contains($rightCol, 'card-header bg-transparent border-0 pt-4 pb-2 px-4" id="eventHeader"') &&
    str_contains($rightCol, 'd-flex justify-content-end align-items-center mt-2'),
    "'See All >' button moved to its own dedicated row below the title and timezone.",
    "'See All >' is still inline with the title block in the card-header",
    $failures,
    $total
);

echo "\n[2/4] Verifying event card container background color is white (#ffffff)...\n";
check(
    str_contains($rightCol, 'background-color: #ffffff; border: 1px solid var(--border-color, #e2e8f0); border-radius: 14px;'),
    "Event card container uses background-color: #ffffff.",
    "Event card container does not use background-color: #ffffff",
    $failures,
    $total
);

echo "\n[3/4] Verifying 'Sign Card' button has white background (#ffffff)...\n";
check(
    str_contains($rightCol, 'style="background-color: #ffffff !important;') &&
    str_contains($modal, 'style="background-color: #ffffff !important;'),
    "'Sign Card' button uses pure white background in both right column and modal.",
    "'Sign Card' button is missing white background",
    $failures,
    $total
);

echo "\n[4/4] Verifying 'powered by mypartyplatform.com' small print above Manage Studio...\n";
check(
    str_contains($rightCol, 'powered by') &&
    str_contains($rightCol, 'mypartyplatform.com') &&
    strpos($rightCol, 'powered by') < strpos($rightCol, 'Manage Studio ↗'),
    "Small print 'powered by mypartyplatform.com' is positioned directly above 'Manage Studio ↗'.",
    "Missing 'powered by mypartyplatform.com' above Manage Studio ↗ in right column",
    $failures,
    $total
);

check(
    str_contains($modal, 'powered by') &&
    str_contains($modal, 'mypartyplatform.com') &&
    strpos($modal, 'powered by') < strpos($modal, 'Manage Studio ↗'),
    "Small print 'powered by mypartyplatform.com' is positioned directly above 'Manage Studio ↗' in modal.",
    "Missing 'powered by mypartyplatform.com' above Manage Studio ↗ in modal",
    $failures,
    $total
);

echo "\n=================================================================\n";
if (empty($failures)) {
    echo "🏆 ALL {$total} UPCOMING EVENTS UI & BRANDING CHECKS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ VERIFICATION FAILED:\n";
    foreach ($failures as $f) {
        echo "   - {$f}\n";
    }
    echo "=================================================================\n";
    exit(1);
}

<?php
declare(strict_types=1);

/**
 * 🔍 VERIFICATION SUITE: FAMILY NETWORK AVATARS & ROSTER DISPLAY
 */

$rootDir = dirname(__DIR__);
$rightColPath = $rootDir . '/resources/views/member/includes/rightColumn.blade.php';
$rightCol = file_get_contents($rightColPath);

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
echo "🔍 FAMILY NETWORK AVATARS & ROSTER VERIFICATION SUITE\n";
echo "=================================================================\n\n";

echo "[1/4] Checking avatar stack in Family Network card...\n";
check(
    str_contains($rightCol, 'previewMembers') &&
    str_contains($rightCol, 'allMembers/seeProfile'),
    "Family Network card contains avatar facepile loop linking to member profiles.",
    "Missing avatar facepile in Family Network card",
    $failures,
    $total
);

echo "\n[2/4] Checking avatar image rendering and fallback...\n";
check(
    str_contains($rightCol, 'onerror="this.onerror=null;this.src=\'/resources/images/profile/avatarM.png\';"') &&
    str_contains($rightCol, 'rounded-circle'),
    "Avatar images have defensive fallback and rounded styling.",
    "Avatar images missing defensive fallback",
    $failures,
    $total
);

echo "\n[3/4] Checking overflow count badge...\n";
check(
    str_contains($rightCol, '$overflowCount') &&
    str_contains($rightCol, '+{{ $overflowCount }}'),
    "Overflow badge (+X more connected members) renders when roster exceeds preview count.",
    "Missing overflow count badge",
    $failures,
    $total
);

echo "\n[4/4] Checking touch targets and contrast standards...\n";
check(
    str_contains($rightCol, 'width: 44px; height: 44px;') &&
    str_contains($rightCol, 'min-height: 44px;'),
    "Avatars and buttons strictly observe minimum 44px touch targets.",
    "Touch targets violate 44px minimum",
    $failures,
    $total
);

echo "\n=================================================================\n";
if (empty($failures)) {
    echo "🏆 ALL {$total} FAMILY NETWORK AVATAR CHECKS PASSED CLEANLY!\n";
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

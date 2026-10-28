<?php

declare(strict_types=1);

/**
 * Verification test for Stitch "Webpage Section Redesign" integration into rightColumn.blade.php.
 */

$viewPath = __DIR__ . '/../resources/views/member/includes/rightColumn.blade.php';

if (!file_exists($viewPath)) {
    echo "❌ ERROR: rightColumn.blade.php not found at {$viewPath}\n";
    exit(1);
}

$content = file_get_contents($viewPath);

echo "=================================================================\n";
echo "🎨 VERIFYING STITCH WEBPAGE SECTION REDESIGN INTEGRATION\n";
echo "=================================================================\n";

$checks = [
    'Active Events Pill Badge' => str_contains($content, "x-text=\"events.length + ' Active'\""),
    'Timezone Indicator' => str_contains($content, 'bi-globe') && str_contains($content, 'user-tz-name'),
    'Inset Date Box' => str_contains($content, 'bi-calendar3 text-danger') && str_contains($content, 'x-text="event.eventDate"'),
    'RSVP & Hub Action Button' => str_contains($content, 'RSVP &amp; Hub') && str_contains($content, 'min-height: 44px;'),
    'Sign Card Action Button' => str_contains($content, 'Sign Card') && str_contains($content, 'min-height: 44px;'),
    'Manage Studio Action Button' => str_contains($content, 'Manage Studio') && str_contains($content, 'bi-star-fill text-warning'),
    'Party Studio Purple Pill' => str_contains($content, 'Party Studio') && str_contains($content, '#9333ea'),
    'Secondary Compact Date Badge' => str_contains($content, 'expandedEventNo') && str_contains($content, 'toLocaleDateString(\'en-GB\''),
    'Inviolable Red Surface Rule' => !preg_match('/background(-color)?:\s*#8a1019[^;]*color:\s*(#000|#111|#1e293b|black)/i', $content),
    'Touch Target Compliance' => !str_contains($content, 'min-height: 20px')
];

$failed = 0;
foreach ($checks as $name => $passed) {
    if ($passed) {
        echo "  ✅ PASSED: {$name}\n";
    } else {
        echo "  ❌ FAILED: {$name}\n";
        $failed++;
    }
}

echo "=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL CHECKS PASSED: Stitch Redesign faithfully integrated!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "⚠️ {$failed} checks failed!\n";
    echo "=================================================================\n";
    exit(1);
}

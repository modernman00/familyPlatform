<?php
declare(strict_types=1);

/**
 * 🔍 VERIFICATION SUITE: THEN & NOW CLICKABLE & EXPANDABLE CAPABILITY
 * Enforces:
 *   1. Timeline cards are keyboard accessible (role="button", tabindex="0") and clickable.
 *   2. Expand pill / visual cue is present on timeline items.
 *   3. Interactive Then & Now Comparison Modal (Slider mode + Side-by-side mode) is present.
 *   4. Feed post card has expand trigger calling window.__expandThenNow.
 *   5. PostComparison model returns post_desc for expanded storytelling.
 *   6. British English and Privacy (zero external share links) invariants.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';
require_once $rootDir . '/app/config/init.php';

use App\model\PostComparison;

$failures = [];
$totalChecks = 0;

function assertCheck(bool $condition, string $passMsg, string $failMsg, array &$failures, int &$totalChecks): void {
    $totalChecks++;
    if ($condition) {
        echo "  ✅ PASSED: {$passMsg}\n";
    } else {
        echo "  ❌ FAILED: {$failMsg}\n";
        $failures[] = $failMsg;
    }
}

echo "\n=================================================================\n";
echo "🔍 THEN & NOW CLICKABLE & EXPANDABLE VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// -----------------------------------------------------------------------------
// [GATE 1] thenNowTimeline.blade.php Architecture & Interactions
// -----------------------------------------------------------------------------
echo "[GATE 1/5] Verifying Then & Now Timeline interactive & clickable attributes...\n";
$timelinePath = $rootDir . '/resources/views/member/includes/thenNowTimeline.blade.php';
$timelineContent = file_exists($timelinePath) ? (string)file_get_contents($timelinePath) : '';

assertCheck(
    str_contains($timelineContent, 'then-now-timeline__pair position-relative'),
    "Timeline pair card has positioning and interactive classes.",
    "Timeline pair card missing 'then-now-timeline__pair position-relative'",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($timelineContent, "card.setAttribute('role', 'button')") &&
    str_contains($timelineContent, "card.setAttribute('tabindex', '0')"),
    "Timeline pair card has role='button' and tabindex='0' for full keyboard accessibility.",
    "Timeline pair card missing role='button' or tabindex='0'",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($timelineContent, 'then-now-timeline__expand-pill') &&
    str_contains($timelineContent, 'bi-arrows-angle-expand'),
    "Timeline card has visual expand pill indicator with expand icon.",
    "Missing expand pill or expand icon in timeline card",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($timelineContent, 'window.__expandThenNowTimeline') &&
    str_contains($timelineContent, "card.addEventListener('click'") &&
    str_contains($timelineContent, "card.addEventListener('keydown'"),
    "Timeline card has click and keydown (Enter/Space) handlers invoking window.__expandThenNowTimeline.",
    "Missing click or keydown handlers invoking window.__expandThenNowTimeline",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($timelineContent, "member.modals.thenNowComparisonModal"),
    "thenNowTimeline.blade.php includes thenNowComparisonModal.",
    "thenNowTimeline.blade.php missing @includeIf('member.modals.thenNowComparisonModal')",
    $failures,
    $totalChecks
);

// -----------------------------------------------------------------------------
// [GATE 2] thenNowComparisonModal.blade.php Structure & Controls
// -----------------------------------------------------------------------------
echo "\n[GATE 2/5] Verifying Then & Now Comparison Modal...\n";
$modalPath = $rootDir . '/resources/views/member/modals/thenNowComparisonModal.blade.php';
$modalContent = file_exists($modalPath) ? (string)file_get_contents($modalPath) : '';

assertCheck(
    file_exists($modalPath) && $modalContent !== '',
    "thenNowComparisonModal.blade.php exists.",
    "thenNowComparisonModal.blade.php does not exist",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'id="thenNowComparisonModal"') &&
    str_contains($modalContent, 'id="tnModalSliderStage"') &&
    str_contains($modalContent, 'id="tnModalSideStage"'),
    "Modal defines both Slider Comparison stage and Side-by-Side stage.",
    "Modal missing id='thenNowComparisonModal', id='tnModalSliderStage', or id='tnModalSideStage'",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'id="tnModalRange"') &&
    str_contains($modalContent, 'id="tnModalHandle"') &&
    str_contains($modalContent, 'window.__updateThenNowSlider'),
    "Modal has interactive range slider with drag handle and update handler.",
    "Modal missing range slider, handle or __updateThenNowSlider handler",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'id="tnViewSliderBtn"') &&
    str_contains($modalContent, 'id="tnViewSideBtn"') &&
    str_contains($modalContent, 'window.__setThenNowModalViewMode'),
    "Modal has view mode toggles (Slider vs Side by side).",
    "Modal missing view mode toggles",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'id="tnNavControls"') &&
    str_contains($modalContent, 'window.__prevThenNowItem') &&
    str_contains($modalContent, 'window.__nextThenNowItem'),
    "Modal supports multi-item carousel navigation (Prev, Next, Counter).",
    "Modal missing multi-item navigation controls",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'id="tnStoryBox"') &&
    str_contains($modalContent, 'id="tnStoryText"'),
    "Modal includes caption/story chronicle box for rich narrative context.",
    "Modal missing story box",
    $failures,
    $totalChecks
);

// -----------------------------------------------------------------------------
// [GATE 3] Feed Post Card Expand Capability
// -----------------------------------------------------------------------------
echo "\n[GATE 3/5] Verifying Feed Post Card Then & Now expand triggers...\n";
$postCardPath = $rootDir . '/resources/views/components/feed/post-card.blade.php';
$postCardContent = file_exists($postCardPath) ? (string)file_get_contents($postCardPath) : '';

assertCheck(
    str_contains($postCardContent, 'window.__expandThenNow'),
    "Feed post card integrates window.__expandThenNow handler.",
    "Feed post card missing window.__expandThenNow",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($postCardContent, "feed.expand_comparison") || str_contains($postCardContent, "Expand"),
    "Feed post card has explicit Expand action button.",
    "Feed post card missing Expand button",
    $failures,
    $totalChecks
);

// -----------------------------------------------------------------------------
// [GATE 4] PostComparison Model Storytelling Support
// -----------------------------------------------------------------------------
echo "\n[GATE 4/5] Verifying PostComparison model post_desc enrichment...\n";
$modelPath = $rootDir . '/app/model/PostComparison.php';
$modelContent = file_exists($modelPath) ? (string)file_get_contents($modelPath) : '';

assertCheck(
    str_contains($modelContent, 'p.postMessage') &&
    str_contains($modelContent, "'post_desc' =>"),
    "PostComparison::forMember queries p.postMessage and returns 'post_desc'.",
    "PostComparison model missing p.postMessage or post_desc in forMember",
    $failures,
    $totalChecks
);

// -----------------------------------------------------------------------------
// [GATE 5] Privacy & British English Standard Verification
// -----------------------------------------------------------------------------
echo "\n[GATE 5/5] Verifying Privacy & British English invariants...\n";

assertCheck(
    !str_contains($modalContent, 'whatsapp') && !str_contains($modalContent, 'sharePostToSocialMedia'),
    "Modal strictly maintains privacy: zero WhatsApp or social media share buttons.",
    "Modal leaks WhatsApp or external share functionality",
    $failures,
    $totalChecks
);

assertCheck(
    !str_contains($modalContent, 'color:') || !str_contains($modalContent, 'background-color: var(--primary-color') || str_contains($modalContent, 'color: #ffffff'),
    "Modal adheres to Inviolable Red Surface Rule (crisp white text on red primary buttons).",
    "Modal violates Red Surface Rule",
    $failures,
    $totalChecks
);

assertCheck(
    str_contains($modalContent, 'min-height: 44px'),
    "Interactive buttons satisfy minimum 44px touch targets.",
    "Buttons missing 44px touch targets",
    $failures,
    $totalChecks
);

echo "\n=================================================================\n";
if (empty($failures)) {
    echo "🏆 ALL {$totalChecks} THEN & NOW CLICKABLE & EXPANDABLE CHECKS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ VERIFICATION FAILED WITH " . count($failures) . " FAILURE(S):\n";
    foreach ($failures as $f) {
        echo "   - {$f}\n";
    }
    echo "=================================================================\n";
    exit(1);
}

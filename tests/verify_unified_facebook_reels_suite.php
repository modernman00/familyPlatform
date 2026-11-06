<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "====================================================================\n";
echo "🧪 Running Unified Always-On Facebook Reels Format Test Suite\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function it(string $description, callable $fn): void {
    global $passCount, $failCount;
    try {
        $fn();
        echo "  ✅ PASS: {$description}\n";
        $passCount++;
    } catch (\Throwable $e) {
        echo "  ❌ FAIL: {$description} - " . $e->getMessage() . "\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// Test 1: Template Architecture (reelsTray.blade.php)
// -------------------------------------------------------------
it("reelsTray.blade.php does not contain mobile-only Instagram circles tray", function() {
    $blade = file_get_contents(__DIR__ . '/../resources/views/member/includes/reelsTray.blade.php');
    if ($blade === false) {
        throw new \Exception("Could not read reelsTray.blade.php");
    }

    if (str_contains($blade, 'id="igMobileStoriesTray"') || str_contains($blade, 'class="ig-stories-tray')) {
        throw new \Exception("reelsTray.blade.php must not contain igMobileStoriesTray; unified Facebook format required");
    }
});

it("reelsTray.blade.php does not hide Facebook cards behind d-none d-md-block", function() {
    $blade = file_get_contents(__DIR__ . '/../resources/views/member/includes/reelsTray.blade.php');

    if (preg_match('/<div\s+class="d-none\s+d-md-block"[^>]*>[\s\S]*reel-fb-card/i', $blade)) {
        throw new \Exception("Facebook reel cards must not be hidden behind d-none d-md-block");
    }
});

it("reelsTray.blade.php renders Facebook Create Reel card and Reel video cards at root", function() {
    $blade = file_get_contents(__DIR__ . '/../resources/views/member/includes/reelsTray.blade.php');

    if (!str_contains($blade, 'class="reel-fb-create-card"')) {
        throw new \Exception("reelsTray.blade.php missing reel-fb-create-card");
    }
    if (!str_contains($blade, 'class="reel-fb-card')) {
        throw new \Exception("reelsTray.blade.php missing reel-fb-card");
    }
    if (!str_contains($blade, 'reel-tray-filter-bar')) {
        throw new \Exception("reelsTray.blade.php missing reel-tray-filter-bar");
    }
    if (!str_contains($blade, 'reelsTrayScroll')) {
        throw new \Exception("reelsTray.blade.php missing reelsTrayScroll container");
    }
});

// -------------------------------------------------------------
// Test 2: Fitts's Law Touch Targets & British English Invariant
// -------------------------------------------------------------
it("reelsTray.blade.php enforces minimum 44px hit targets on interactive controls", function() {
    $blade = file_get_contents(__DIR__ . '/../resources/views/member/includes/reelsTray.blade.php');

    if (!preg_match('/class="[^"]*reel-tray-new-btn[^"]*"[^>]*style="[^"]*min-height:\s*44px/i', $blade)) {
        throw new \Exception("New button missing min-height: 44px hit target");
    }
    if (!preg_match('/class="[^"]*reel-tray-watch-link[^"]*"[^>]*style="[^"]*min-height:\s*44px/i', $blade)) {
        throw new \Exception("Watch All link missing min-height: 44px hit target");
    }
});

it("reelsTray.blade.php adheres to British English (en-GB)", function() {
    $blade = file_get_contents(__DIR__ . '/../resources/views/member/includes/reelsTray.blade.php');

    // Strip HTML tags and inline CSS style attributes before checking visible text
    $visibleText = preg_replace('/style="[^"]*"/i', '', $blade);
    $visibleText = strip_tags($visibleText);

    $usEnglishWords = ['color', 'favorite', 'behavior', 'center', 'organize', 'customize'];
    foreach ($usEnglishWords as $word) {
        if (preg_match('/\b' . $word . '\b/i', $visibleText)) {
            throw new \Exception("Found US English spelling in visible text: '{$word}'");
        }
    }
});

// -------------------------------------------------------------
// Test 3: CSS Responsive Scale & Theming
// -------------------------------------------------------------
it("standards-v21.css styles .reels-tray-wrapper as a surface card on mobile", function() {
    $css = file_get_contents(__DIR__ . '/../public/css/standards-v21.css');

    if (!str_contains($css, '.reels-tray-wrapper') || !str_contains($css, 'border-radius: 18px !important;')) {
        throw new \Exception("standards-v21.css must style .reels-tray-wrapper with 18px border-radius on mobile");
    }
});

it("reels.css defines dark-mode surface theming and mobile card scaling", function() {
    $css = file_get_contents(__DIR__ . '/../public/css/reels.css');

    if (!str_contains($css, 'body.dark-mode .reels-tray-wrapper') && !str_contains($css, '[data-bs-theme="dark"] .reels-tray-wrapper')) {
        throw new \Exception("reels.css missing dark-mode rules for .reels-tray-wrapper");
    }
    if (!str_contains($css, 'body.dark-mode .reel-fb-create-card')) {
        throw new \Exception("reels.css missing dark-mode rules for .reel-fb-create-card");
    }
    if (!str_contains($css, '.reel-fb-card:active')) {
        throw new \Exception("reels.css missing tactile :active state for cards");
    }
});

// -------------------------------------------------------------
// Test 4: JavaScript Always-On Video Preview (IntersectionObserver)
// -------------------------------------------------------------
it("reelsPlayer.js configures IntersectionObserver for always-on video preview", function() {
    $js = file_get_contents(__DIR__ . '/../resources/asset/js/components/reels/reelsPlayer.js');

    if (!str_contains($js, "IntersectionObserver") || !str_contains($js, "previewObserver.observe")) {
        throw new \Exception("reelsPlayer.js must configure IntersectionObserver for always-on preview");
    }
    if (!str_contains($js, "video.play().catch") || !str_contains($js, "video.pause()")) {
        throw new \Exception("reelsPlayer.js must play on intersection and pause when scrolled out");
    }
});

// -------------------------------------------------------------
// Summary
// -------------------------------------------------------------
echo "\n====================================================================\n";
echo "📊 Results: {$passCount} Passed, {$failCount} Failed\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);

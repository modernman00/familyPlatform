<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\model\Reel;

echo "====================================================================\n";
echo "🧪 Running Instagram-Superior Grouped Reels & Mobile Scale Test Suite\n";
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
// Test 1: Creator Grouping (groupReelsByCreator)
// -------------------------------------------------------------
it("groupReelsByCreator groups multi-moment reels by user_id", function() {
    $mockReels = [
        [
            'id' => 101,
            'user_id' => 'user_sarah',
            'firstName' => 'Sarah',
            'lastName' => 'Jenkins',
            'profilePics' => 'sarah.jpg',
            'caption' => 'Baby first steps!',
            'category' => 'first_steps',
            'created_at' => '2026-10-30 18:00:00',
        ],
        [
            'id' => 102,
            'user_id' => 'user_david',
            'firstName' => 'David',
            'lastName' => 'Jenkins',
            'profilePics' => 'david.jpg',
            'caption' => 'BBQ Saturday',
            'category' => 'weekend_out',
            'created_at' => '2026-10-30 17:30:00',
        ],
        [
            'id' => 103,
            'user_id' => 'user_sarah',
            'firstName' => 'Sarah',
            'lastName' => 'Jenkins',
            'profilePics' => 'sarah.jpg',
            'caption' => 'Birthday cake ceremony',
            'category' => 'birthday',
            'created_at' => '2026-10-30 17:00:00',
        ],
        [
            'id' => 104,
            'user_id' => 'user_sarah',
            'firstName' => 'Sarah',
            'lastName' => 'Jenkins',
            'profilePics' => 'sarah.jpg',
            'caption' => 'Family reunion group photo',
            'category' => 'reunion',
            'created_at' => '2026-10-30 16:00:00',
        ],
    ];

    $grouped = Reel::groupReelsByCreator($mockReels);

    if (count($grouped) !== 2) {
        throw new \Exception("Expected exactly 2 creator groups, found " . count($grouped));
    }

    $sarahGroup = $grouped[0];
    if ($sarahGroup['user_id'] !== 'user_sarah') {
        throw new \Exception("Expected first group to be user_sarah, got " . $sarahGroup['user_id']);
    }
    if ($sarahGroup['reels_count'] !== 3) {
        throw new \Exception("Expected Sarah to have 3 reels, found " . $sarahGroup['reels_count']);
    }
    if ($sarahGroup['is_birthday'] !== true) {
        throw new \Exception("Expected Sarah group to have is_birthday = true");
    }
    if ((int)$sarahGroup['latest_reel']['id'] !== 101) {
        throw new \Exception("Expected latest reel to be 101, got " . $sarahGroup['latest_reel']['id']);
    }

    $davidGroup = $grouped[1];
    if ($davidGroup['user_id'] !== 'user_david') {
        throw new \Exception("Expected second group to be user_david, got " . $davidGroup['user_id']);
    }
    if ($davidGroup['reels_count'] !== 1) {
        throw new \Exception("Expected David to have 1 reel, found " . $davidGroup['reels_count']);
    }
    if ($davidGroup['is_birthday'] !== false) {
        throw new \Exception("Expected David group to have is_birthday = false");
    }
});

// -------------------------------------------------------------
// Test 2: Creator Clustering & Consecutive Chaining (clusterReelsByCreator)
// -------------------------------------------------------------
it("clusterReelsByCreator chains creator reels consecutively", function() {
    $mockReels = [
        ['id' => 1, 'user_id' => 'user_1', 'created_at' => '2026-10-30 10:00:00'],
        ['id' => 2, 'user_id' => 'user_2', 'created_at' => '2026-10-30 09:00:00'],
        ['id' => 3, 'user_id' => 'user_1', 'created_at' => '2026-10-30 08:00:00'],
        ['id' => 4, 'user_id' => 'user_2', 'created_at' => '2026-10-30 07:00:00'],
    ];

    $clustered = Reel::clusterReelsByCreator($mockReels);

    if (count($clustered) !== 4) {
        throw new \Exception("Expected 4 clustered reels, found " . count($clustered));
    }

    // First two reels should belong to user_1
    if ($clustered[0]['user_id'] !== 'user_1' || $clustered[1]['user_id'] !== 'user_1') {
        throw new \Exception("Expected first two reels to be grouped under user_1");
    }
    // Last two reels should belong to user_2
    if ($clustered[2]['user_id'] !== 'user_2' || $clustered[3]['user_id'] !== 'user_2') {
        throw new \Exception("Expected last two reels to be grouped under user_2");
    }
});

it("clusterReelsByCreator prioritizes requested reel and its creator cluster", function() {
    $mockReels = [
        ['id' => 10, 'user_id' => 'user_a'],
        ['id' => 20, 'user_id' => 'user_b'],
        ['id' => 21, 'user_id' => 'user_b'],
        ['id' => 11, 'user_id' => 'user_a'],
    ];

    // Priority requested reel 21 (belonging to user_b)
    $clustered = Reel::clusterReelsByCreator($mockReels, 21);

    if ((int)$clustered[0]['id'] !== 21) {
        throw new \Exception("Expected first reel to be priority reel 21, got " . $clustered[0]['id']);
    }
    if ($clustered[0]['user_id'] !== 'user_b' || $clustered[1]['user_id'] !== 'user_b') {
        throw new \Exception("Expected user_b cluster to be at head of feed");
    }
});

// -------------------------------------------------------------
// Test 3: CSS Scale & Mobile Ergonomics Verification
// -------------------------------------------------------------
it("standards-v21.css enforces mobile bubble expansion (>= 78px wrap, >= 70px avatar)", function() {
    $css = file_get_contents(__DIR__ . '/../public/css/standards-v21.css');

    if (!preg_match('/\.ig-story-avatar-wrap\s*\{[^}]*width:\s*(7[8-9]|8[0-9]|9[0-9])px/i', $css)) {
        throw new \Exception("Expected .ig-story-avatar-wrap width >= 78px");
    }
    if (!preg_match('/\.ig-story-avatar\s*\{[^}]*width:\s*(7[0-9]|8[0-9])px/i', $css)) {
        throw new \Exception("Expected .ig-story-avatar width >= 70px");
    }
    if (!str_contains($css, '.ig-story-count-badge')) {
        throw new \Exception("Expected .ig-story-count-badge definition in standards-v21.css");
    }
});

it("reels.css defines multi-segment progress bars and full-bleed mobile viewport", function() {
    $css = file_get_contents(__DIR__ . '/../public/css/reels.css');

    if (!str_contains($css, '.reel-progress-segments-container')) {
        throw new \Exception("Expected .reel-progress-segments-container in reels.css");
    }
    if (!str_contains($css, '100dvh')) {
        throw new \Exception("Expected 100dvh viewport support in reels.css");
    }
});

// -------------------------------------------------------------
// Test 4: Continuous Autoplay Till End Verification
// -------------------------------------------------------------
it("reelsPlayer.js configures continuous autoplay till the end of the feed", function() {
    $js = file_get_contents(__DIR__ . '/../resources/asset/js/components/reels/reelsPlayer.js');

    // Video tag must not have loop attribute so ended events fire
    if (preg_match('/<video[^>]*\bid="reelActiveVideo"[^>]*\bloop\b/i', $js)) {
        throw new \Exception("Expected reelActiveVideo element to omit loop attribute for autoplay advancement");
    }
    // Must contain ended event listener that advances to nextReel
    if (!str_contains($js, "video.addEventListener('ended'") || !str_contains($js, "this.nextReel()")) {
        throw new \Exception("Expected reelsPlayer.js to handle ended event and trigger nextReel()");
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

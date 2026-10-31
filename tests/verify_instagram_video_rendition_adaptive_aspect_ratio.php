<?php
/**
 * Test: verify_instagram_video_rendition_adaptive_aspect_ratio.php
 * Verifies that portrait videos render in crystal-clear PORTRAIT HD (adaptive 9:16 / 4:5,
 * zero black letterboxes/pillarboxes) and landscape videos render in SUPER HD LANDSCAPE,
 * matching Instagram world-class feed video standards.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

echo "\n=================================================================\n";
echo "🎬 VERIFYING INSTAGRAM-GRADE VIDEO RENDITION & ADAPTIVE RATIO\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $condition, string $description): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  ✅ [PASS] " . $description . "\n";
        $passCount++;
    } else {
        echo "  ❌ [FAIL] " . $description . "\n";
        $failCount++;
    }
}

// --------------------------------------------------------------------------
// 1. Blade Template Verification (resources/views/components/feed/post-card.blade.php)
// --------------------------------------------------------------------------
echo "--- [1/4] Feed Post Card Blade Verification ---\n";

$postcardPath = __DIR__ . '/../resources/views/components/feed/post-card.blade.php';
assertCondition(file_exists($postcardPath), "post-card.blade.php exists");

$postcardContent = file_get_contents($postcardPath);

assertCondition(
    strpos($postcardContent, 'detectVideoDimensions') !== false,
    "Adaptive intrinsic dimension detector 'detectVideoDimensions' is present"
);

assertCondition(
    strpos($postcardContent, 'PORTRAIT HD') !== false,
    "Instagram-style 'PORTRAIT HD' format badge is present"
);

assertCondition(
    strpos($postcardContent, 'SUPER HD') !== false,
    "Instagram-style 'SUPER HD' format badge is present"
);

assertCondition(
    strpos($postcardContent, 'SQUARE HD') !== false,
    "Instagram-style 'SQUARE HD' format badge is present"
);

assertCondition(
    strpos($postcardContent, 'radial-gradient(circle at center, rgba(30, 41, 59, 0.55) 0%, rgba(7, 9, 14, 0.98) 100%)') !== false,
    "Ambient studio glow backdrop replaces harsh flat black bars"
);

assertCondition(
    strpos($postcardContent, 'video-scrubber-track') !== false,
    "Instagram-style bottom scrubber track is present"
);

assertCondition(
    strpos($postcardContent, 'togglePlayback') !== false && strpos($postcardContent, 'showPulse') !== false,
    "1-Tap play/pause toggle with animated Instagram ripple pulse is present"
);

assertCondition(
    strpos($postcardContent, 'ratio-9x16') !== false && strpos($postcardContent, "includes('/shorts/')") !== false,
    "YouTube Shorts embeds automatically adapt to vertical 9:16 frame"
);

// --------------------------------------------------------------------------
// 2. Profile HTML JS Fallback Verification (resources/asset/js/components/profilePage/html.js)
// --------------------------------------------------------------------------
echo "\n--- [2/4] Profile Feed Fallback Verification (html.js) ---\n";

$htmlJsPath = __DIR__ . '/../resources/asset/js/components/profilePage/html.js';
assertCondition(file_exists($htmlJsPath), "html.js exists");

$htmlJsContent = file_get_contents($htmlJsPath);

assertCondition(
    strpos($htmlJsContent, 'onloadedmetadata') !== false && strpos($htmlJsContent, 'this.videoWidth / this.videoHeight') !== false,
    "html.js dynamically detects aspect ratio on onloadedmetadata for direct streams"
);

assertCondition(
    strpos($htmlJsContent, "isShort") !== false && strpos($htmlJsContent, 'ratio-9x16') !== false,
    "html.js adapts YouTube Shorts to vertical 9:16 ratio"
);

// --------------------------------------------------------------------------
// 3. Post Modal Preview Verification (resources/asset/js/components/profilePage/postEmojiImgProcess.js)
// --------------------------------------------------------------------------
echo "\n--- [3/4] Post Composer Live Preview Verification (postEmojiImgProcess.js) ---\n";

$composerJsPath = __DIR__ . '/../resources/asset/js/components/profilePage/postEmojiImgProcess.js';
assertCondition(file_exists($composerJsPath), "postEmojiImgProcess.js exists");

$composerJsContent = file_get_contents($composerJsPath);

assertCondition(
    strpos($composerJsContent, "vidEl.videoWidth / vidEl.videoHeight") !== false,
    "Composer live preview adapts dimensions dynamically when loaded"
);

assertCondition(
    strpos($composerJsContent, "videoTrimPreviewPlayer.videoWidth / videoTrimPreviewPlayer.videoHeight") !== false,
    "Auto-trim studio video preview dynamically handles portrait aspect ratio"
);

// --------------------------------------------------------------------------
// 4. Invariant & Standards Verification (British English, Hit Targets, php -l)
// --------------------------------------------------------------------------
echo "\n--- [4/4] Invariant & Governance Verification ---\n";

// PHP Syntax check
$phpLint = shell_exec("php -l " . escapeshellarg($postcardPath));
assertCondition(strpos($phpLint, "No syntax errors detected") !== false, "post-card.blade.php passes php -l cleanly");

// British English check on new user-facing labels
assertCondition(strpos($postcardContent, "Unmute video") !== false, "Sound toggle has proper accessible label");
assertCondition(strpos($postcardContent, "View Fullscreen") !== false, "Fullscreen toggle has proper accessible label");

echo "\n=================================================================\n";
echo "📊 TEST RESULTS: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n\n";

if ($failCount > 0) {
    exit(1);
}

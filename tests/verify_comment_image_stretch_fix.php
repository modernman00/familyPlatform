<?php
/**
 * Test Suite: verify_comment_image_stretch_fix.php
 * Verifies that comment section images (user avatars and attached media/GIFs)
 * have strict anti-stretching, anti-squishing, and aspect-ratio preservation rules.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$baseDir = dirname(__DIR__);
$postCardPath = $baseDir . '/resources/views/components/feed/post-card.blade.php';
$profileCssPath = $baseDir . '/public/css/profilepage.css';
$standardsCssPath = $baseDir . '/public/css/standards-v21.css';
$mainCssPath = $baseDir . '/public/css/main.css';
$reelsCssPath = $baseDir . '/public/css/reels.css';
$profileScssPath = $baseDir . '/resources/asset/scss/profilepage.scss';

echo "=================================================================\n";
echo "🧪 VERIFYING COMMENT SECTION IMAGE ANTI-STRETCH INTEGRITY SUITE\n";
echo "=================================================================\n\n";

$errors = [];

// 1. Verify post-card.blade.php comment avatar and gif
echo "[1/5] Checking post-card.blade.php template...\n";
if (!file_exists($postCardPath)) {
    $errors[] = "post-card.blade.php not found at $postCardPath";
} else {
    $postCardContent = file_get_contents($postCardPath);

    // Comment avatar must have flex-shrink: 0, 32px dimensions, aspect-ratio 1/1, and feed-comment-avatar class
    if (!str_contains($postCardContent, 'feed-comment-avatar')) {
        $errors[] = "post-card.blade.php: comment avatar missing 'feed-comment-avatar' class";
    }
    if (!str_contains($postCardContent, 'flex-shrink-0') || !str_contains($postCardContent, 'flex-shrink: 0')) {
        $errors[] = "post-card.blade.php: comment avatar missing flex-shrink: 0";
    }
    if (!str_contains($postCardContent, 'aspect-ratio: 1 / 1')) {
        $errors[] = "post-card.blade.php: comment avatar missing aspect-ratio: 1 / 1";
    }

    // Comment GIF must have width:auto, height:auto, object-fit:contain
    if (!str_contains($postCardContent, 'comment-gif')) {
        $errors[] = "post-card.blade.php: missing comment-gif class";
    }
    if (!str_contains($postCardContent, 'width:auto') && !str_contains($postCardContent, 'width: auto')) {
        $errors[] = "post-card.blade.php: comment-gif missing width: auto";
    }
    if (!str_contains($postCardContent, 'height:auto') && !str_contains($postCardContent, 'height: auto')) {
        $errors[] = "post-card.blade.php: comment-gif missing height: auto";
    }
    if (!str_contains($postCardContent, 'object-fit:contain') && !str_contains($postCardContent, 'object-fit: contain')) {
        $errors[] = "post-card.blade.php: comment-gif missing object-fit: contain";
    }
}

// 2. Verify public/css/profilepage.css
echo "[2/5] Checking public/css/profilepage.css...\n";
if (!file_exists($profileCssPath)) {
    $errors[] = "profilepage.css not found";
} else {
    $css = file_get_contents($profileCssPath);
    if (!str_contains($css, '.feed-comment-avatar')) {
        $errors[] = "profilepage.css: missing .feed-comment-avatar rule";
    }
    if (!str_contains($css, 'flex-shrink: 0 !important')) {
        $errors[] = "profilepage.css: missing flex-shrink: 0 !important for avatars";
    }
    if (!str_contains($css, '.comment-gif')) {
        $errors[] = "profilepage.css: missing .comment-gif rule";
    }
    if (!str_contains($css, 'max-height: 220px !important') && !str_contains($css, 'max-height: 220px')) {
        $errors[] = "profilepage.css: missing max-height: 220px on .comment-gif";
    }
}

// 3. Verify public/css/standards-v21.css
echo "[3/5] Checking public/css/standards-v21.css...\n";
if (!file_exists($standardsCssPath)) {
    $errors[] = "standards-v21.css not found";
} else {
    $css = file_get_contents($standardsCssPath);
    if (!str_contains($css, '.feed-comment-avatar')) {
        $errors[] = "standards-v21.css: missing .feed-comment-avatar rule";
    }
    if (!str_contains($css, '.comment-gif')) {
        $errors[] = "standards-v21.css: missing .comment-gif rule";
    }
}

// 4. Verify public/css/main.css
echo "[4/5] Checking public/css/main.css...\n";
if (!file_exists($mainCssPath)) {
    $errors[] = "main.css not found";
} else {
    $css = file_get_contents($mainCssPath);
    if (!str_contains($css, 'feed-comment-avatar')) {
        $errors[] = "main.css: missing feed-comment-avatar";
    }
    if (!str_contains($css, 'comment-gif')) {
        $errors[] = "main.css: missing comment-gif";
    }
}

// 5. Verify public/css/reels.css
echo "[5/5] Checking public/css/reels.css...\n";
if (!file_exists($reelsCssPath)) {
    $errors[] = "reels.css not found";
} else {
    $css = file_get_contents($reelsCssPath);
    if (!str_contains($css, '.reel-comment-avatar')) {
        $errors[] = "reels.css: missing .reel-comment-avatar rule";
    }
    if (!str_contains($css, '.reel-comment-gif-img')) {
        $errors[] = "reels.css: missing .reel-comment-gif-img rule";
    }
    if (!str_contains($css, 'aspect-ratio: 1 / 1 !important')) {
        $errors[] = "reels.css: missing aspect-ratio: 1 / 1 !important on avatar";
    }
}

echo "\n-----------------------------------------------------------------\n";
if (empty($errors)) {
    echo "✅ ALL COMMENT IMAGE ANTI-STRETCH CHECKS PASSED (0 ERRORS)\n";
    echo "-----------------------------------------------------------------\n";
    exit(0);
} else {
    echo "❌ VERIFICATION FAILED WITH " . count($errors) . " ERROR(S):\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    echo "-----------------------------------------------------------------\n";
    exit(1);
}

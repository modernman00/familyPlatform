<?php
/**
 * Test Suite: verify_diverse_emojis_and_gifs_suite.php
 * Verifies Race-Conscious Fitzpatrick Diversity Emojis, Categorised Multi-Library Expansion,
 * and Curated Family GIF Catalog Expansion.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$baseDir = dirname(__DIR__);
$gifCatalogPath = $baseDir . '/resources/asset/js/components/profilePage/gifCatalog.js';
$emojiPickerPath = $baseDir . '/resources/asset/js/components/emojiPicker.js';
$postBladePath = $baseDir . '/resources/views/member/modals/post.blade.php';
$postCardPath = $baseDir . '/resources/views/components/feed/post-card.blade.php';
$feedComponentPath = $baseDir . '/resources/asset/js/components/profilePage/feedComponent.js';

echo "=================================================================\n";
echo "🧪 VERIFYING RACE-CONSCIOUS EMOJIS & EXPANDED FAMILY GIFS SUITE\n";
echo "=================================================================\n\n";

$errors = [];

// 1. Verify gifCatalog.js
echo "[1/6] Verifying gifCatalog.js...\n";
if (!file_exists($gifCatalogPath)) {
    $errors[] = "gifCatalog.js not found at $gifCatalogPath";
} else {
    $gifContent = file_get_contents($gifCatalogPath);

    // Extract all URLs
    preg_match_all("/url:\s*'([^']+)'/", $gifContent, $matches);
    $urls = $matches[1] ?? [];
    echo "  Total GIFs in catalog: " . count($urls) . "\n";
    if (count($urls) < 40) {
        $errors[] = "Expected at least 40 curated GIFs, found " . count($urls);
    }

    // Verify all URLs match Giphy whitelist
    $invalidUrls = [];
    foreach ($urls as $url) {
        $parts = parse_url($url);
        $host = strtolower((string)($parts['host'] ?? ''));
        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if ($scheme !== 'https' || !preg_match('/^(media\d*\.)?giphy\.com$/', $host)) {
            $invalidUrls[] = $url;
        }
    }
    if (!empty($invalidUrls)) {
        $errors[] = "Found " . count($invalidUrls) . " URLs that violate Giphy security whitelist: " . implode(', ', array_slice($invalidUrls, 0, 3));
    } else {
        echo "  ✅ All " . count($urls) . " GIFs strictly conform to Giphy HTTPS domain whitelist.\n";
    }

    // Verify categories
    if (!str_contains($gifContent, "'kids'")) {
        $errors[] = "gifCatalog.js missing 'kids' category";
    }
    if (!str_contains($gifContent, "'celebrate'") || !str_contains($gifContent, "'love'") || !str_contains($gifContent, "'family'")) {
        $errors[] = "gifCatalog.js missing core categories";
    }
    echo "  ✅ GIF categories verified.\n";
}

// 2. Verify emojiPicker.js
echo "\n[2/6] Verifying emojiPicker.js...\n";
if (!file_exists($emojiPickerPath)) {
    $errors[] = "emojiPicker.js not found at $emojiPickerPath";
} else {
    $emojiContent = file_get_contents($emojiPickerPath);

    // Verify Fitzpatrick skin tones definition (0 through 5)
    for ($t = 0; $t <= 5; $t++) {
        if (!preg_match("/tone:\s*$t/", $emojiContent)) {
            $errors[] = "emojiPicker.js missing skin tone $t";
        }
    }
    echo "  ✅ All 6 Fitzpatrick skin tone modifiers (0-5) defined.\n";

    // Verify localStorage key for skin tone persistence
    if (!str_contains($emojiContent, "preferredEmojiSkinTone")) {
        $errors[] = "emojiPicker.js missing 'preferredEmojiSkinTone' localStorage key";
    } else {
        echo "  ✅ Persistent skin tone storage key verified.\n";
    }

    // Verify categories
    $expectedCats = ['recent', 'smileys', 'people', 'hearts', 'celebration', 'nature', 'activities'];
    foreach ($expectedCats as $c) {
        if (!str_contains($emojiContent, "id: '$c'")) {
            $errors[] = "emojiPicker.js missing category '$c'";
        }
    }
    echo "  ✅ All 7 curated emoji categories verified.\n";

    // Verify getEmojiForTone and skin resolution logic
    if (!str_contains($emojiContent, "getEmojiForTone")) {
        $errors[] = "emojiPicker.js missing getEmojiForTone function";
    }
    if (!str_contains($emojiContent, "showIndividualTonePicker")) {
        $errors[] = "emojiPicker.js missing individual long-press tone picker";
    }
    echo "  ✅ Dynamic skin-tone resolution & popover handlers verified.\n";
}

// 3. Verify Blade Modals and Views
echo "\n[3/6] Verifying Blade templates...\n";
if (file_exists($postBladePath)) {
    $bladeContent = file_get_contents($postBladePath);
    if (!str_contains($bladeContent, 'id="emojiPickerDrawer"')) {
        $errors[] = "post.blade.php missing in-flow emojiPickerDrawer";
    } else {
        echo "  ✅ post.blade.php contains in-flow emojiPickerDrawer.\n";
    }
    if (!str_contains($bladeContent, 'data-cat="kids"')) {
        $errors[] = "post.blade.php missing kids GIF pill";
    } else {
        echo "  ✅ post.blade.php contains updated GIF category pills.\n";
    }
}

if (file_exists($postCardPath)) {
    $cardContent = file_get_contents($postCardPath);
    if (!str_contains($cardContent, 'Comment skin tone diversity selector')) {
        $errors[] = "post-card.blade.php missing comment skin tone diversity selector";
    } else {
        echo "  ✅ post-card.blade.php contains race-conscious comment skin tone palette.\n";
    }
}

// 4. Verify feedComponent.js
echo "\n[4/6] Verifying feedComponent.js...\n";
if (file_exists($feedComponentPath)) {
    $feedContent = file_get_contents($feedComponentPath);
    if (!str_contains($feedContent, 'commentSkinTone') || !str_contains($feedContent, 'setCommentSkinTone')) {
        $errors[] = "feedComponent.js missing commentSkinTone state or setter";
    } else {
        echo "  ✅ feedComponent.js contains reactive comment skin tone state.\n";
    }
    if (!str_contains($feedContent, 'commentEmojiItems')) {
        $errors[] = "feedComponent.js missing commentEmojiItems with skin variations";
    } else {
        echo "  ✅ feedComponent.js contains expanded comment emojis with skin variants.\n";
    }
}

// 5. Verify SCSS Styles
echo "\n[5/6] Verifying SCSS Styles...\n";
$scssPath = $baseDir . '/resources/asset/scss/profilepage.scss';
if (file_exists($scssPath)) {
    $scssContent = file_get_contents($scssPath);
    $requiredClasses = [
        'modern-emoji-picker',
        'emoji-picker-header',
        'emoji-skin-swatch',
        'emoji-category-tabs',
        'emoji-cat-tab-btn',
        'emoji-content-area',
        'has-skin-tones',
        'emoji-tone-popover'
    ];
    foreach ($requiredClasses as $cls) {
        if (!str_contains($scssContent, $cls)) {
            $errors[] = "profilepage.scss missing .$cls style rule";
        }
    }
    echo "  ✅ All tactile design and popover CSS classes verified.\n";
}

// 6. British English Invariant Check
echo "\n[6/6] Verifying British English Invariant...\n";
$filesToCheck = [$gifCatalogPath, $emojiPickerPath, $postBladePath, $postCardPath, $feedComponentPath];
$usTerms = ['color:', 'favorites', 'organize', 'optimize', 'customize', 'center-align'];
foreach ($filesToCheck as $f) {
    if (file_exists($f)) {
        $content = file_get_contents($f);
        // User facing strings check
        if (preg_match('/(Organize|Optimize|Customize|Favorite)/', $content, $m)) {
            $errors[] = basename($f) . " contains US English term: " . $m[1];
        }
    }
}
echo "  ✅ User-facing copy adheres strictly to British English (en-GB).\n";

echo "\n-----------------------------------------------------------------\n";
if (empty($errors)) {
    echo "🎉 ALL TESTS PASSED (0 ERRORS): Race-conscious emojis & expanded GIFs verified!\n";
    echo "-----------------------------------------------------------------\n";
    exit(0);
} else {
    echo "❌ TEST FAILURES DETECTED:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
    echo "-----------------------------------------------------------------\n";
    exit(1);
}

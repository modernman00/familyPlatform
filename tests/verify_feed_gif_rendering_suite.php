<?php

declare(strict_types=1);

/**
 * Test Suite: verify_feed_gif_rendering_suite.php
 *
 * Verifies that GIF media in feed posts bypasses <picture><source type="image/avif">
 * and renders direct <img> elements with pristine URLs, preventing the 404 alt text fallback
 * ("Family post image") reported by users.
 *
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4) & BRATS
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 */

echo "=================================================================\n";
echo "🧪 VERIFYING FEED POST GIF MEDIA RENDERING SUITE\n";
echo "=================================================================\n\n";

$errors = [];

function assertTest(bool $condition, string $passMsg, string $failMsg, array &$errors): void
{
    if ($condition) {
        echo "  ✅ {$passMsg}\n";
    } else {
        echo "  ❌ {$failMsg}\n";
        $errors[] = $failMsg;
    }
}

// -----------------------------------------------------------------------------
// [Phase 1] Audit imageUrls.js Source Code
// -----------------------------------------------------------------------------
echo "[Phase 1] Auditing imageUrls.js GIF isolation logic...\n";

$imageUrlsFile = __DIR__ . '/../resources/asset/js/components/helper/imageUrls.js';
assertTest(file_exists($imageUrlsFile), "imageUrls.js exists", "imageUrls.js missing", $errors);

$imageUrlsContent = file_get_contents($imageUrlsFile);

assertTest(
    str_contains($imageUrlsContent, 'export function isGifMedia('),
    "isGifMedia function is exported from imageUrls.js",
    "isGifMedia missing from imageUrls.js",
    $errors
);

assertTest(
    str_contains($imageUrlsContent, 'isGifMedia(fallbackUrl) || isGifMedia(img)'),
    "getPostImageVariantUrl skips AVIF/WebP variants for GIF media",
    "getPostImageVariantUrl does not skip AVIF/WebP variants for GIFs",
    $errors
);

assertTest(
    str_contains($imageUrlsContent, "clean.startsWith('/')"),
    "getPostImageUrl preserves absolute root paths without mangling",
    "getPostImageUrl does not preserve absolute root paths",
    $errors
);

// -----------------------------------------------------------------------------
// [Phase 2] Node.js Unit Execution of imageUrls.js Functions
// -----------------------------------------------------------------------------
echo "\n[Phase 2] Executing Node.js unit assertions for imageUrls.js...\n";

$nodeScript = <<<JS
const fs = require('fs');

// Simple ESM to CJS translation for testing
let content = fs.readFileSync('resources/asset/js/components/helper/imageUrls.js', 'utf8');
content = content.replace(/export function (\w+)/g, 'function $1');
content += '\\nexports.isGifMedia = isGifMedia; exports.getPostImageUrl = getPostImageUrl; exports.getPostImageVariantUrl = getPostImageVariantUrl;\\n';
const mod = {};
const moduleWrapper = new Function('exports', content);
moduleWrapper(mod);

const { isGifMedia, getPostImageUrl, getPostImageVariantUrl } = mod;

// Assert 1: isGifMedia detection
if (!isGifMedia('/images/brand/gifs/atg_heritage_tree_bloom.gif')) throw new Error('Failed to detect brand GIF');
if (!isGifMedia('https://media.giphy.com/media/artj92V8o75VPL7AeQ/200.gif')) throw new Error('Failed to detect Giphy GIF');
if (!isGifMedia('/api/atg/gif/view/roots_deep')) throw new Error('Failed to detect ATG API GIF endpoint');
if (isGifMedia('/resources/images/post/photo.jpg')) throw new Error('False positive for JPEG');
if (isGifMedia('/resources/images/post/photo.webp')) throw new Error('False positive for WebP');

// Assert 2: getPostImageUrl preservation
const brandGifUrl = getPostImageUrl('/images/brand/gifs/atg_heritage_tree_bloom.gif');
if (brandGifUrl !== '/images/brand/gifs/atg_heritage_tree_bloom.gif') {
    throw new Error('Mangled brand GIF URL: ' + brandGifUrl);
}

const apiGifUrl = getPostImageUrl('/api/atg/gif/view/roots_deep');
if (apiGifUrl !== '/api/atg/gif/view/roots_deep') {
    throw new Error('Mangled API GIF URL: ' + apiGifUrl);
}

const noSlashBrandUrl = getPostImageUrl('images/brand/gifs/atg_heritage_tree_bloom.gif');
if (noSlashBrandUrl !== '/images/brand/gifs/atg_heritage_tree_bloom.gif') {
    throw new Error('Failed to normalize unslashed brand GIF URL: ' + noSlashBrandUrl);
}

const legacyPhoto = getPostImageUrl('photo123.jpg');
if (legacyPhoto !== '/resources/images/post/photo123.jpg') {
    throw new Error('Mangled legacy photo URL: ' + legacyPhoto);
}

// Assert 3: getPostImageVariantUrl MUST NOT return .avif or .webp for GIFs
const gifAvif = getPostImageVariantUrl('/images/brand/gifs/atg_heritage_tree_bloom.gif', 'avif');
if (gifAvif !== '') {
    throw new Error('GIF should NOT return AVIF variant, got: ' + gifAvif);
}

const gifWebp = getPostImageVariantUrl('/images/brand/gifs/atg_heritage_tree_bloom.gif', 'webp');
if (gifWebp !== '') {
    throw new Error('GIF should NOT return WebP variant, got: ' + gifWebp);
}

const photoAvif = getPostImageVariantUrl('photo123.jpg', 'avif');
if (photoAvif !== '/resources/images/post/photo123.avif') {
    throw new Error('Normal photo should return AVIF variant, got: ' + photoAvif);
}

console.log('NODE_JS_UNIT_OK');
JS;

$tempFile = __DIR__ . '/../storage/temp_test_image_urls.js';
file_put_contents($tempFile, $nodeScript);
$output = shell_exec('node ' . escapeshellarg($tempFile) . ' 2>&1');
@unlink($tempFile);

assertTest(str_contains((string)$output, 'NODE_JS_UNIT_OK'), "Node.js unit tests for imageUrls.js passed successfully", "Node.js unit tests failed: " . trim((string)$output), $errors);

// -----------------------------------------------------------------------------
// [Phase 3] Auditing Blade Template post-card.blade.php
// -----------------------------------------------------------------------------
echo "\n[Phase 3] Auditing post-card.blade.php template structure...\n";

$postCardFile = __DIR__ . '/../resources/views/components/feed/post-card.blade.php';
assertTest(file_exists($postCardFile), "post-card.blade.php exists", "post-card.blade.php missing", $errors);

$postCardContent = file_get_contents($postCardFile);

assertTest(
    str_contains($postCardContent, '<template x-if="isGifMedia(post.images[0])">'),
    "post-card.blade.php contains single-photo GIF condition x-if=\"isGifMedia(post.images[0])\"",
    "Single-photo GIF branch missing from post-card.blade.php",
    $errors
);

assertTest(
    str_contains($postCardContent, '<template x-if="!isGifMedia(post.images[0])">'),
    "post-card.blade.php wraps <picture> inside x-if=\"!isGifMedia(post.images[0])\"",
    "<picture> not isolated from GIF media in post-card.blade.php",
    $errors
);

assertTest(
    str_contains($postCardContent, '<template x-if="isGifMedia(img)">'),
    "post-card.blade.php contains carousel slide GIF condition x-if=\"isGifMedia(img)\"",
    "Carousel slide GIF branch missing from post-card.blade.php",
    $errors
);

// -----------------------------------------------------------------------------
// [Phase 4] Verify Built Webpack Bundle profilePage.js
// -----------------------------------------------------------------------------
echo "\n[Phase 4] Auditing compiled Webpack production bundle...\n";

$indexBundle = __DIR__ . '/../public/js/index.js';
assertTest(file_exists($indexBundle), "public/js/index.js exists", "public/js/index.js missing", $errors);

$indexContent = file_get_contents($indexBundle);
assertTest(
    str_contains($indexContent, 'isGifMedia') || str_contains($indexContent, 'image/gif'),
    "Compiled Webpack bundles contain GIF media handling logic",
    "Compiled Webpack bundles missing GIF media logic",
    $errors
);

echo "\n-----------------------------------------------------------------\n";
if (empty($errors)) {
    echo "🎉 ALL FEED POST GIF RENDERING TESTS PASSED (0 ERRORS)!\n";
    echo "   GIF media displays directly in <img> without broken AVIF/WebP <source> tags.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ " . count($errors) . " TEST(S) FAILED:\n";
    foreach ($errors as $e) {
        echo "   - $e\n";
    }
    echo "=================================================================\n";
    exit(1);
}

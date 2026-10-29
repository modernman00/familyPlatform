<?php
declare(strict_types=1);

/**
 * Verification Test: Feed Image Adaptive Aspect Ratio Suite
 * 
 * Asserts:
 * 1. Single feed post images do NOT force rigid portrait ratio (aspect-ratio: 4 / 5) with object-fit: cover.
 * 2. Single feed post images render with adaptive fluid sizing (width: 100%, height: auto, max-height: 600px, object-fit: contain).
 * 3. Mobile CSS overrides (standards-v21.css) preserve object-fit: contain and height: auto so landscape images render edge-to-edge uncropped.
 * 4. Multi-image carousels protect landscape images from truncation with object-fit: contain.
 */

$postCardPath = __DIR__ . '/../resources/views/components/feed/post-card.blade.php';
$standardsCssPath = __DIR__ . '/../public/css/standards-v21.css';
$globalScssPath = __DIR__ . '/../resources/asset/scss/global.scss';

if (!file_exists($postCardPath)) {
    echo "❌ FAIL: post-card.blade.php not found at $postCardPath\n";
    exit(1);
}

$postCardContent = file_get_contents($postCardPath);
$standardsCssContent = file_get_contents($standardsCssPath);
$globalScssContent = file_get_contents($globalScssPath);

$errors = [];

// Check 1: Single image section does not have aspect-ratio: 4 / 5
if (preg_match('/<!-- Single Post Image.*?<template x-if="post\.images && post\.images\.length === 1.*?aspect-ratio:\s*4\s*\/\s*5/s', $postCardContent)) {
    $errors[] = "Single post image still has aspect-ratio: 4 / 5 forced on its stage or image!";
}

// Check 2: Single image uses height: auto and max-height with object-fit: contain
if (!preg_match('/<!-- Single Post Image.*?width:\s*100%;\s*height:\s*auto;\s*max-height:\s*600px;\s*object-fit:\s*contain;/s', $postCardContent)) {
    $errors[] = "Single post image does not have 'width: 100%; height: auto; max-height: 600px; object-fit: contain;' configured!";
}

// Check 3: Mobile styles in standards-v21.css do not force object-fit: cover on post-media-first-img
if (preg_match('/\.feed-post-card \.post-media-first-img\s*\{[^}]*object-fit:\s*cover/s', $standardsCssContent)) {
    $errors[] = "standards-v21.css still forces object-fit: cover on mobile .feed-post-card .post-media-first-img!";
}

// Check 4: standards-v21.css sets object-fit: contain and height: auto on post-media-first-img
if (!preg_match('/\.feed-post-card \.post-media-first-img\s*\{[^}]*object-fit:\s*contain/s', $standardsCssContent)) {
    $errors[] = "standards-v21.css does not set object-fit: contain on .feed-post-card .post-media-first-img!";
}

// Check 5: Multi-image carousel protects slide images from truncation
if (!preg_match('/<!-- Multi-Image Instagram Swipeable Carousel.*?post-media-first-img[^>]*object-fit:\s*contain/s', $postCardContent)) {
    $errors[] = "Multi-image carousel slide image does not specify object-fit: contain!";
}

// Check 6: Ambient Aura Backdrop is rendered
if (!str_contains($postCardContent, 'feed-media-aura-container') || !str_contains($postCardContent, 'feed-media-aura-backdrop')) {
    $errors[] = "post-card.blade.php does not contain Ambient Aura elements (feed-media-aura-container / feed-media-aura-backdrop)!";
}

// Check 7: Holographic Specular Foil Glint is rendered
if (!str_contains($postCardContent, 'vse-holo-foil-glint')) {
    $errors[] = "post-card.blade.php does not contain Holographic Specular Foil Glint element (vse-holo-foil-glint)!";
}

// Check 8: Generational Particle Celebration Fountain is rendered
if (!str_contains($postCardContent, 'heirloom-sparkle-fountain')) {
    $errors[] = "post-card.blade.php does not contain Generational Particle Celebration Fountain (heirloom-sparkle-fountain)!";
}

// Check 9: CSS classes exist in standards-v21.css
if (!str_contains($standardsCssContent, '.feed-media-aura-backdrop') || !str_contains($standardsCssContent, '.vse-holo-foil-glint')) {
    $errors[] = "standards-v21.css is missing .feed-media-aura-backdrop or .vse-holo-foil-glint classes!";
}

// Check 10: SCSS rules exist in global.scss
if (!str_contains($globalScssContent, 'heirloom-sparkle-fountain') || !str_contains($globalScssContent, 'vse-holo-foil-glint')) {
    $errors[] = "global.scss is missing heirloom-sparkle-fountain or vse-holo-foil-glint styles!";
}

if (!empty($errors)) {
    echo "❌ VERIFICATION FAILED:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
    exit(1);
}

echo "✅ PASS: Feed Image Adaptive Aspect Ratio Verified.\n";
echo "  - 0 rigid 4/5 portrait ratio locks on single images.\n";
echo "  - Adaptive fluid rendering (width: 100%, height: auto, max-height: 600px, object-fit: contain) confirmed.\n";
echo "  - Mobile viewport rules (standards-v21.css) safely uncrop landscape images.\n";
echo "  - Multi-image carousels protected from lateral truncation.\n";
exit(0);

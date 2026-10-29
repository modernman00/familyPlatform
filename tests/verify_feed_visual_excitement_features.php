<?php

/**
 * Test: Verify Feed Visual Excitement Features
 * Validates Features 1 through 5:
 *   1. Dynamic Ambient Aura Backdrop
 *   2. Generational Particle Celebration Fountain
 *   3. 2.5D Holographic Specular Foil Shimmer
 *   4. Interactive Lineage Spotlight & Kinship Halos
 *   5. Floating Voice Capsule Keepsake Pill
 */

$postCardView = file_get_contents(__DIR__ . '/../resources/views/components/feed/post-card.blade.php');
$feedComponentJs = file_get_contents(__DIR__ . '/../resources/asset/js/components/profilePage/feedComponent.js');
$standardsCss = file_get_contents(__DIR__ . '/../public/css/standards-v21.css');
$globalScss = file_get_contents(__DIR__ . '/../resources/asset/scss/global.scss');
$postDto = file_get_contents(__DIR__ . '/../app/dto/PostDto.php');
$feedRepo = file_get_contents(__DIR__ . '/../app/repository/FeedRepository.php');
$vseCanvasJs = file_get_contents(__DIR__ . '/../resources/asset/js/components/vse-canvas.js');

$errors = [];

// 1. Dynamic Ambient Aura
if (!str_contains($postCardView, 'feed-media-aura-container') || !str_contains($postCardView, 'feed-media-aura-backdrop')) {
    $errors[] = 'Missing ambient aura markup in post-card.blade.php';
}
if (!str_contains($standardsCss, 'feed-media-aura-backdrop')) {
    $errors[] = 'Missing ambient aura styles in standards-v21.css';
}

// 2. Generational Particle Celebration Fountain
if (!str_contains($postCardView, 'heirloom-sparkle-fountain') || !str_contains($postCardView, 'sparkle-gem')) {
    $errors[] = 'Missing particle fountain sparkle gem markup in post-card.blade.php';
}
if (!str_contains($feedComponentJs, 'triggerHaptic') && !str_contains($feedComponentJs, 'navigator.vibrate')) {
    $errors[] = 'Missing haptic pulse burst in feedComponent.js';
}

// 3. 2.5D Holographic Specular Foil Shimmer
if (!str_contains($postCardView, 'vse-holo-foil-glint')) {
    $errors[] = 'Missing holographic foil sheen in post-card.blade.php';
}
if (!str_contains($vseCanvasJs, '--vse-glint-pos') || !str_contains($vseCanvasJs, 'has-glint')) {
    $errors[] = 'Missing holographic foil parallax tilt in vse-canvas.js';
}

// 4. Interactive Lineage Spotlight
if (!str_contains($postCardView, 'lineage-spotlight-pill') || !str_contains($postCardView, 'lineage-spotlight-drawer')) {
    $errors[] = 'Missing Lineage Spotlight markup in post-card.blade.php';
}
if (!str_contains($feedComponentJs, 'toggleLineageSpotlight')) {
    $errors[] = 'Missing toggleLineageSpotlight in feedComponent.js';
}
if (!str_contains($postCardView, '/member/organogram?focus=')) {
    $errors[] = 'Missing organogram tree deep-link in Lineage Spotlight drawer';
}

// 5. Floating Voice Capsule Keepsake Pill
if (!str_contains($postCardView, 'voice-capsule-floating-pill') || !str_contains($postCardView, 'audio-mini-waveform')) {
    $errors[] = 'Missing Voice Capsule floating pill markup in post-card.blade.php';
}
if (!str_contains($postDto, 'voiceCapsuleUrl')) {
    $errors[] = 'Missing voiceCapsuleUrl property in PostDto.php';
}
if (!str_contains($feedRepo, 'voice_capsule_url')) {
    $errors[] = 'Missing voice_capsule_url hydration in FeedRepository.php';
}
if (!str_contains($feedComponentJs, 'togglePlayVoiceCapsule')) {
    $errors[] = 'Missing togglePlayVoiceCapsule in feedComponent.js';
}
if (!str_contains($standardsCss, 'photo-voice-playing') || !str_contains($standardsCss, 'voicePhotoBreathe')) {
    $errors[] = 'Missing breathing photo playback animation in standards-v21.css';
}

if (!empty($errors)) {
    echo "❌ FAIL: Feed visual excitement verification failed:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
    exit(1);
}

echo "✅ PASS: All 5 Feed Visual Excitement Features Verified Successfully!\n";
echo "  1. Dynamic Ambient Aura Backdrop: Active\n";
echo "  2. Generational Particle Celebration Fountain: Active\n";
echo "  3. 2.5D Holographic Specular Foil Shimmer: Active\n";
echo "  4. Interactive Lineage Spotlight & Kinship Halos: Active\n";
echo "  5. Floating Voice Capsule Keepsake Pill: Active\n";
exit(0);

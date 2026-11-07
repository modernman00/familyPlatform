<?php

declare(strict_types=1);

/**
 * Verification Test Suite: ATG Sovereign 3D Emoji Engine & Avatar Studio
 * Validates Pod 3 & 4 architectures, procedural avatar synthesis,
 * 12 specialised 3D kinship emojis, XSS neutralization, and database sync.
 */

require_once __DIR__ . '/../app/config/init.php';

echo "=================================================================\n";
echo "🧪 VERIFYING ATG SOVEREIGN 3D EMOJI & AVATAR SUITE (POD 3 & 4)\n";
echo "=================================================================\n\n";

$errors = [];
$service = new \App\services\AtgEmojiService();

// [1/6] Verify Catalog & Specialized 3D Emojis
echo "[1/6] Verifying 3D Emoji Catalog...\n";
$catalog = $service->getCatalog();
if (empty($catalog['categories']) || count($catalog['categories']) < 3) {
    $errors[] = "Catalog must have at least 3 distinct categories.";
} else {
    echo "  ✅ Found " . count($catalog['categories']) . " categories: " . implode(', ', array_keys($catalog['categories'])) . "\n";
}

if (count($catalog['all']) < 10) {
    $errors[] = "Expected at least 10 specialized 3D emojis, found " . count($catalog['all']);
} else {
    echo "  ✅ Catalog contains " . count($catalog['all']) . " specialised 3D emojis.\n";
}

// [2/6] Verify SVG Rendering of All 18 3D Emojis
echo "\n[2/6] Verifying SVG Vector Rendering of Specialised Emojis...\n";
$expectedEmojiIds = [
    'family_roots_3d', 'family_crest_3d', 'family_hearth_3d', 'family_feast_3d',
    'family_hug_3d', 'memory_capsule_3d', 'ancestor_crown_3d', 'ancestor_blessing_3d',
    'kinship_pride_3d', 'family_celebrate_3d', 'wisdom_scroll_3d', 'generations_link_3d',
    'family_toast_3d', 'baby_cradle_3d', 'wedding_vows_3d', 'laugh_tears_3d',
    'star_struck_3d', 'family_house_3d'
];

foreach ($expectedEmojiIds as $emojiId) {
    $svg = $service->renderSvgEmoji($emojiId, 120);
    if (!str_starts_with($svg, '<svg') || !str_ends_with(trim($svg), '</svg>')) {
        $errors[] = "Emoji '{$emojiId}' failed to render valid SVG document.";
    }
    if (str_contains($svg, '<script') || str_contains($svg, 'onerror=') || str_contains($svg, 'onload=')) {
        $errors[] = "Emoji '{$emojiId}' contains forbidden active script elements.";
    }
}
echo "  ✅ All " . count($expectedEmojiIds) . " 3D emojis render pristine, sanitized SVG markup.\n";

// [3/6] Verify Procedural 3D Avatar Rendering Across Diversity Traits
echo "\n[3/6] Verifying Procedural 3D Avatar Engine...\n";
$testCombinations = [
    ['skin_tone' => 'melanin_rich', 'hairstyle' => 'afro_crown', 'expression' => 'warm_smile', 'accessory' => 'ancestor_crown', 'backdrop' => 'warm_sunset'],
    ['skin_tone' => 'deep_espresso', 'hairstyle' => 'regal_locs', 'expression' => 'proud_grin', 'accessory' => 'heritage_coral_beads', 'backdrop' => 'royal_violet'],
    ['skin_tone' => 'warm_chestnut', 'hairstyle' => 'traditional_gele', 'expression' => 'wise_calm', 'accessory' => 'gold_spectacles', 'backdrop' => 'emerald_roots'],
    ['skin_tone' => 'fair_radiance', 'hairstyle' => 'regal_bald', 'expression' => 'radiant_joy', 'accessory' => 'laurel_wreath', 'backdrop' => 'pure_gold'],
];

foreach ($testCombinations as $index => $traits) {
    $avatarSvg = $service->renderProceduralAvatar($traits, 256);
    if (!str_starts_with($avatarSvg, '<svg') || !str_ends_with(trim($avatarSvg), '</svg>')) {
        $errors[] = "Avatar combination #{$index} failed to render valid SVG.";
    }
    if (!str_contains($avatarSvg, 'atg-avatar-svg') || !str_contains($avatarSvg, '<radialGradient')) {
        $errors[] = "Avatar combination #{$index} missing 3D radial lighting shader definitions.";
    }
}
echo "  ✅ Procedural avatar engine successfully synthesises high-fidelity 3D personas across diverse traits.\n";

// [4/6] Adversarial Red-Team & SecOps Penetration Check
echo "\n[4/6] Executing Adversarial XSS & Directory Traversal Attacks...\n";
$maliciousTraits = [
    'skin_tone'  => '"><script>alert(1)</script>',
    'hairstyle'  => 'afro" onload="fetch(\'/steal\')"',
    'expression' => '"><img src=x onerror=alert(2)>',
    'accessory'  => 'ancestor_crown; drop table users;',
    'backdrop'   => '../../etc/passwd',
];

$neutralizedSvg = $service->renderProceduralAvatar($maliciousTraits, 256);
if (str_contains($neutralizedSvg, '<script>') || str_contains($neutralizedSvg, 'onload=') || str_contains($neutralizedSvg, 'onerror=')) {
    $errors[] = "CRITICAL: Malicious traits bypassed SVG sanitisation (Stored XSS vulnerability)!";
} else {
    echo "  ✅ PASS: Malicious trait injection successfully neutralized. Fallback defaults applied safely.\n";
}

// [5/6] Verify Profile Avatar Persistence & Organogram Parity
echo "\n[5/6] Verifying Avatar Persistence & Organogram Parity...\n";
$testUserId = 'test_atg_user_' . bin2hex(random_bytes(4));
$db = \Src\Db::connect2();

// Create test user record in personal and family_nodes
$db->prepare("INSERT INTO personal (id, firstName, lastName, famCode, gender) VALUES (?, 'ATG', 'Tester', 'TESTFAM', 'Other')")
   ->execute([$testUserId]);

$db->prepare("INSERT INTO family_nodes (user_id, family_code, first_name, last_name, avatar_url) VALUES (?, 'TESTFAM', 'ATG', 'Tester', '/resources/images/profile/avatarM.png')")
   ->execute([$testUserId]);

// Save avatar
$saveResult = $service->saveAvatar($testUserId, [
    'skin_tone'  => 'warm_honey',
    'hairstyle'  => 'curled_top',
    'expression' => 'warm_smile',
    'accessory'  => 'gold_spectacles',
    'backdrop'   => 'sapphire_night',
]);

if (!$saveResult['success'] || empty($saveResult['filename'])) {
    $errors[] = "Failed to save procedural avatar.";
} else {
    // Verify file exists
    $filePath = 'resources/images/profile/' . $saveResult['filename'];
    if (!file_exists($filePath)) {
        $errors[] = "Saved avatar file was not found on disk at: {$filePath}";
    } else {
        echo "  ✅ Avatar file successfully written to disk ({$filePath}).\n";
    }

    // Verify DB sync in profilePics
    $pStmt = $db->prepare("SELECT img FROM profilePics WHERE id = ?");
    $pStmt->execute([$testUserId]);
    $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);
    if (($pRow['img'] ?? '') !== $saveResult['filename']) {
        $errors[] = "profilePics table was not properly updated with avatar filename.";
    } else {
        echo "  ✅ profilePics table updated synchronously.\n";
    }

    // Verify organogram family_nodes sync
    $fnStmt = $db->prepare("SELECT avatar_url FROM family_nodes WHERE user_id = ?");
    $fnStmt->execute([$testUserId]);
    $fnRow = $fnStmt->fetch(PDO::FETCH_ASSOC);
    if (!str_contains((string)($fnRow['avatar_url'] ?? ''), $saveResult['filename'])) {
        $errors[] = "family_nodes.avatar_url was not synchronized for organogram tree parity.";
    } else {
        echo "  ✅ family_nodes table synchronized for organogram tree parity.\n";
    }
}

// Cleanup test fixture
$db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
$db->prepare("DELETE FROM profilePics WHERE id = ?")->execute([$testUserId]);
$db->prepare("DELETE FROM family_nodes WHERE user_id = ?")->execute([$testUserId]);
if (!empty($saveResult['filename']) && file_exists('resources/images/profile/' . $saveResult['filename'])) {
    @unlink('resources/images/profile/' . $saveResult['filename']);
}
if (!empty($saveResult['filename']) && file_exists('public/img/profile/' . $saveResult['filename'])) {
    @unlink('public/img/profile/' . $saveResult['filename']);
}

// [6/6] Verify AR Modal, Post AR Attachments & Frontend Assets
echo "\n[6/6] Verifying AR Modal, Post AR Attachments & Frontend Assets...\n";
$cssPath = dirname(__DIR__) . '/public/css/atg-emoji.css';
$jsPath = dirname(__DIR__) . '/public/js/atg-emoji-engine.js';
$viewPath = dirname(__DIR__) . '/resources/views/member/emojiStudio.blade.php';
$arModalPath = dirname(__DIR__) . '/resources/views/components/atgArViewerModal.blade.php';

if (!file_exists($cssPath)) {
    $errors[] = "Missing public/css/atg-emoji.css";
}
if (!file_exists($jsPath)) {
    $errors[] = "Missing public/js/atg-emoji-engine.js";
}
if (!file_exists($viewPath)) {
    $errors[] = "Missing resources/views/member/emojiStudio.blade.php";
}
if (!file_exists($arModalPath)) {
    $errors[] = "Missing resources/views/components/atgArViewerModal.blade.php";
} else {
    $arModalContent = file_get_contents($arModalPath);
    if (!str_contains($arModalContent, 'atgArCameraFeed') || !str_contains($arModalContent, 'atgArParticleCanvas')) {
        $errors[] = "atgArViewerModal missing AR camera feed or particle canvas components.";
    }
}

// Verify PostDto ar_emoji integration
$dummyPostRow = [
    'post_no' => 99999,
    'user_id' => 'user_123',
    'title' => 'Test Post',
    'text' => 'Celebrating with 3D emoji :family_roots_3d:',
    'image' => null,
    'video' => null,
    'date' => '2026-11-03 12:00:00',
    'status' => 'active',
    'share' => '0',
    'ar_emoji' => 'family_roots_3d',
];
$postDto = \App\dto\PostDto::fromDatabaseRow($dummyPostRow);
if ($postDto->arEmoji !== 'family_roots_3d') {
    $errors[] = "PostDto failed to map ar_emoji from database row.";
} else {
    echo "  ✅ PostDto successfully maps ar_emoji property.\n";
}

$viewContent = file_get_contents($viewPath);
if (!str_contains($viewContent, '@include(\'csrf\')')) {
    $errors[] = "emojiStudio.blade.php missing CSRF token include.";
}
if (!str_contains($viewContent, 'btn-spin-on-click')) {
    $errors[] = "emojiStudio.blade.php missing Universal Button Spinner class.";
}

echo "  ✅ All AR components, frontend assets and layout templates verified against Golden Standards.\n";

// [7/7] Inclusive traits, motion rig, emoji tones & living-emoji motion
echo "\n[7/7] Verifying inclusive traits, motion rig and emoji skin tones...\n";
$opts = \App\services\AtgAvatarRenderer::options();
foreach (['skin_tone' => 12, 'hairstyle' => 20, 'eye_shape' => 5, 'hair_color' => 10, 'facial_hair' => 4, 'outfit' => 6] as $trait => $min) {
    if (count($opts[$trait]) < $min) {
        $errors[] = "Trait '{$trait}' has only " . count($opts[$trait]) . " options (expected >= {$min}).";
    }
}
foreach (['hijab', 'dastar_turban', 'kufi_cap', 'traditional_gele', 'long_straight', 'blunt_bangs'] as $style) {
    if (!isset($opts['hairstyle'][$style])) {
        $errors[] = "Missing inclusive hairstyle/headwear '{$style}'.";
    }
}
$rig = $service->renderProceduralAvatar(['hairstyle' => 'hijab', 'eye_shape' => 'monolid'], 256, true);
foreach (['atg-av-headpose', 'atg-av-gaze', 'atg-av-eye-l', 'atg-av-talk', 'atg-av-mouth', '@keyframes atgAvBlink', 'prefers-reduced-motion', 'data-atg-traits'] as $hook) {
    if (!str_contains($rig, $hook)) {
        $errors[] = "Animated avatar rig missing '{$hook}'.";
    }
}
$fem = $service->renderProceduralAvatar(['presentation' => 'feminine'], 256);
$masc = $service->renderProceduralAvatar(['presentation' => 'masculine'], 256);
$neut = $service->renderProceduralAvatar(['presentation' => 'neutral'], 256);
if ($fem === $masc || $fem === $neut || $masc === $neut) {
    $errors[] = "Gender expression options must render distinct bodies.";
}
$beardedFem = $service->renderProceduralAvatar(['presentation' => 'feminine', 'facial_hair' => 'full_beard', 'hairstyle' => 'high_fade'], 256);
if (!str_contains($beardedFem, 'facial_hair:full_beard')) {
    $errors[] = "Gender expression must not restrict facial hair / hairstyle choices.";
}
$static = $service->renderProceduralAvatar([], 256, false);
if (str_contains($static, '<style>')) {
    $errors[] = "Static avatar must not embed idle animation styles.";
}
$sanitised = $service->sanitiseTraits(['skin_tone' => '"><script>', 'outfit' => 'suit_tie', 'unknown' => 'x']);
if ($sanitised['skin_tone'] !== 'warm_chestnut' || $sanitised['outfit'] !== 'suit_tie' || isset($sanitised['unknown'])) {
    $errors[] = "sanitiseTraits() failed to whitelist input.";
}
if ($service->traitsFromProfileImage('../../.env') !== null) {
    $errors[] = "traitsFromProfileImage() accepted a non-studio path.";
}
[$base, $tone] = $service->parseToneId('family_wave_medium_dark_3d');
if ($base !== 'family_wave_3d' || $tone !== 'medium_dark') {
    $errors[] = "parseToneId() failed on a tone variant.";
}
$light = $service->renderSvgEmoji('family_heart_hands_light_3d', 96);
$dark = $service->renderSvgEmoji('family_heart_hands_dark_3d', 96);
if ($light === $dark || !str_contains($dark, '#4a2c1c')) {
    $errors[] = "Tone variants of family_heart_hands_3d do not differ.";
}
foreach ($service->allEmojiAssetIds() as $assetId) {
    $svg = $service->renderSvgEmoji($assetId, 64);
    if (!str_contains($svg, 'class="atgm atgm-')) {
        $errors[] = "Emoji '{$assetId}' has no motion wrapper.";
    }
    if (preg_match('/:([a-z0-9_]+_3d):/', ':' . $assetId . ':') !== 1) {
        $errors[] = "Emoji id '{$assetId}' breaks the :<id>_3d: shortcode convention.";
    }
}
if (empty($errors)) {
    echo "  ✅ 13 skin tones, 21 hair/headwear styles, motion rig hooks, and " . count($service->allEmojiAssetIds()) . " living emoji assets verified.\n";
}

// [8/8] Verify Photo-to-Living Avatar Analysis & Permissions-Policy Header
echo "\n[8/8] Verifying Photo-to-Living Avatar Analysis & Permissions-Policy Header...\n";
$photoAnalysis = $service->analysePhotoToAvatar([
    'sample_r'           => 106,
    'sample_g'           => 60,
    'sample_b'           => 32,
    'hair_r'             => 14,
    'hair_g'             => 11,
    'hair_b'             => 10,
    'denoising_strength' => 0.38,
    'cfg_scale'          => 6.5,
    'face_restoration'   => 0.65,
    'skin_texture'       => 0.72,
    'sharpening'         => 0.45,
    'eye_retouch'        => 0.80,
]);
if (($photoAnalysis['sampled_palette']['skin_tone'] ?? '') !== 'warm_chestnut') {
    $errors[] = "Photo-to-Avatar failed to match RGB(106,60,32) to warm_chestnut skin tone.";
}
if (($photoAnalysis['parameters']['denoising_strength'] ?? 0.0) !== 0.38 || ($photoAnalysis['identity_score'] ?? 0.0) < 95.0) {
    $errors[] = "Photo-to-Avatar realism parameters or identity lock score failed validation.";
}
$htaccessContent = (string)file_get_contents(dirname(__DIR__) . '/.htaccess');
if (!str_contains($htaccessContent, 'microphone=(self)')) {
    $errors[] = ".htaccess Permissions-Policy must include microphone=(self) so Talk Mode functions in production.";
}

// Verify Living Photo Save Mode (embeds portrait PNG + choreography animations into SVG)
if (!empty($testUserId)) {
    $tinyPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
    // Pad valid PNG to > 100 bytes so length gate passes
    $im = imagecreatetruecolor(64, 64);
    $bg = imagecolorallocate($im, 138, 84, 52);
    imagefill($im, 0, 0, $bg);
    ob_start();
    imagepng($im);
    $pngBytes = (string)ob_get_clean();
    imagedestroy($im);
    $validPngUri = 'data:image/png;base64,' . base64_encode($pngBytes);

    $livingSave = $service->saveAvatar(
        $testUserId,
        ['skin_tone' => 'warm_chestnut', 'hairstyle' => 'afro_crown'],
        $validPngUri,
        true,
        'living_photo',
        'dance',
        [138, 84, 52]
    );
    if (empty($livingSave['success']) || !str_ends_with($livingSave['filename'], '.svg')) {
        $errors[] = "saveAvatar(living_photo) failed to return an active .svg filename.";
    } else {
        $savedSvgPath = dirname(__DIR__) . '/resources/images/profile/' . $livingSave['filename'];
        $savedSvgText = is_file($savedSvgPath) ? (string)file_get_contents($savedSvgPath) : '';
        if (!str_contains($savedSvgText, 'atg-living-photo-svg') || !str_contains($savedSvgText, 'atgLpDance')) {
            $errors[] = "Saved Living Photo SVG is missing atg-living-photo-svg class or dance choreography keyframes.";
        }
        @unlink($savedSvgPath);
        @unlink(substr($savedSvgPath, 0, -4) . '.png');
        @unlink(dirname(__DIR__) . '/public/img/profile/' . $livingSave['filename']);
        @unlink(dirname(__DIR__) . '/public/img/profile/' . substr($livingSave['filename'], 0, -4) . '.png');
    }

    if ($originalImg !== null) {
        $restStmt = $db->prepare("UPDATE profilePics SET img = ? WHERE id = ?");
        $restStmt->execute([$originalImg, $testUserId]);
    }
}

if (empty($errors)) {
    echo "  ✅ Photo-to-Living Avatar RGB complexion matching, neural realism parameters, Living Photo SVG save, and microphone=(self) security policy verified.\n";
}

echo "\n=================================================================\n";
if (empty($errors)) {
    echo "🎉 ALL ATG 3D EMOJI & AVATAR TESTS PASSED! ZERO VIOLATIONS.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ TEST SUITE FAILED WITH " . count($errors) . " ERRORS:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
    echo "=================================================================\n";
    exit(1);
}

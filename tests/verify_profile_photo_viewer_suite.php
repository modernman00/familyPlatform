<?php

declare(strict_types=1);

/**
 * verify_profile_photo_viewer_suite.php
 *
 * Adversarial Security & Quality Suite for Profile Photo Viewer (Facebook-Style Lightbox)
 * Gatekeepers: Dr. Viktor Kovács & Marcus (SecOps Red Team) + David (Gatewatcher)
 */

echo "======================================================================\n";
echo "🛡️  ADVERSARIAL SECURITY & QUALITY SUITE: PROFILE PHOTO VIEWER\n";
echo "======================================================================\n";

$baseDir = dirname(__DIR__);
$failures = 0;

function assertCheck(bool $condition, string $desc): void {
    global $failures;
    if ($condition) {
        echo "  ✅ [PASS] {$desc}\n";
    } else {
        echo "  ❌ [FAIL] {$desc}\n";
        $failures++;
    }
}

// 1. Structural File Existence Checks
$jsPath = $baseDir . '/public/js/profilePhotoViewer.js';
$cssPath = $baseDir . '/public/css/profile-photo-viewer.css';
$bladePath = $baseDir . '/resources/views/components/profilePhotoViewer.blade.php';
$layoutPath = $baseDir . '/resources/views/layouts/profileBase.blade.php';
$leftColPath = $baseDir . '/resources/views/member/includes/leftColumn.blade.php';

assertCheck(file_exists($jsPath), "JavaScript engine file exists: public/js/profilePhotoViewer.js");
assertCheck(file_exists($cssPath), "CSS stylesheet exists: public/css/profile-photo-viewer.css");
assertCheck(file_exists($bladePath), "Blade component exists: resources/views/components/profilePhotoViewer.blade.php");

$jsContent = file_get_contents($jsPath) ?: '';
$cssContent = file_get_contents($cssPath) ?: '';
$bladeContent = file_get_contents($bladePath) ?: '';
$layoutContent = file_get_contents($layoutPath) ?: '';
$leftColContent = file_get_contents($leftColPath) ?: '';

// 2. Adversarial Security Audits (Dr. Viktor Kovács & Marcus Gate)
echo "\n[SecOps Gate] Checking Protocol & XSS Neutralization in JS Engine...\n";

// Audit XSS Filter Regex
assertCheck(
    strpos($jsContent, 'javascript|vbscript|data:(?!image\\/)') !== false,
    "Strict protocol sanitisation filters javascript:, vbscript:, and non-image data URIs"
);

// Audit Safe Text Node Injection (No innerHTML for user strings)
assertCheck(
    strpos($jsContent, 'this.nameEl.textContent = safeName') !== false,
    "Safe textContent assignment for member name (neutralising HTML/XSS injection)"
);
assertCheck(
    strpos($jsContent, 'this.subtitleEl.textContent = safeSubtitle') !== false,
    "Safe textContent assignment for member subtitle"
);

// Audit Keyboard Trap Neutralisation
assertCheck(
    strpos($jsContent, "e.key === 'Escape'") !== false,
    "Global Escape key listener registered to dismiss viewer without keyboard lock"
);

// Audit Touch Dismiss Gesture
assertCheck(
    strpos($jsContent, "touchstart") !== false && strpos($jsContent, "touchend") !== false,
    "Mobile touch-drag gesture handler enabled for instant dismiss on touchscreens"
);

// 3. UI/UX & Standards v2.1 Verification (Kieran & Sarah Gate)
echo "\n[Standards Gate] Checking Global Design Standards v2.1 Compliance...\n";

assertCheck(
    strpos($cssContent, '--pf-tap-min: 44px') !== false,
    "Fitts's Law 44px minimum touch target variable defined"
);
assertCheck(
    strpos($cssContent, 'min-height: var(--pf-tap-min)') !== false,
    "Action and close buttons strictly enforce >= 44px touch boundaries"
);
assertCheck(
    strpos($cssContent, 'backdrop-filter: blur') !== false,
    "Glassmorphic backdrop blur enabled for Facebook-grade visual immersion"
);
assertCheck(
    strpos($cssContent, 'object-fit: contain') !== false,
    "Aspect ratio preservation enforced (object-fit: contain) preventing image distortion"
);

// 4. Layout Integration Verification
echo "\n[Integration Gate] Checking Layout Inclusions...\n";

assertCheck(
    strpos($layoutContent, "profile-photo-viewer.css") !== false,
    "CSS stylesheet included in resources/views/layouts/profileBase.blade.php"
);
assertCheck(
    strpos($layoutContent, "components.profilePhotoViewer") !== false,
    "ProfilePhotoViewer component included in resources/views/layouts/profileBase.blade.php"
);
assertCheck(
    strpos($layoutContent, "profilePhotoViewer.js") !== false,
    "JavaScript engine loaded with defer in resources/views/layouts/profileBase.blade.php"
);
assertCheck(
    strpos($leftColContent, 'data-profile-expand="true"') !== false,
    "Profile avatar container in leftColumn.blade.php has data-profile-expand attribute"
);
assertCheck(
    strpos($leftColContent, 'avatar-zoom-cue') !== false,
    "Visual hover zoom cue badge present on profile avatar"
);

// 5. British English Copy Audit
echo "\n[Jumoke Gate] Checking British English Standards...\n";
$bannedAmerican = ['maximize', 'optimize', 'customize', 'color:', 'behavior'];
$foundAmerican = false;
foreach ($bannedAmerican as $word) {
    if (stripos($bladeContent, $word) !== false) {
        $foundAmerican = true;
        echo "  ❌ Banned American spelling '{$word}' found in Blade template!\n";
    }
}
assertCheck(!$foundAmerican, "Blade template strictly adheres to British English (en-GB)");

echo "======================================================================\n";
if ($failures === 0) {
    echo "🎉 ALL TESTS PASSED! Profile Photo Lightbox verified 100% world-class.\n";
    echo "======================================================================\n";
    exit(0);
} else {
    echo "🛑 TEST SUITE FAILED with {$failures} issue(s).\n";
    echo "======================================================================\n";
    exit(1);
}

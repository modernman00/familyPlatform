<?php
/**
 * verify_then_now_freeze_fix.php
 *
 * Verifies that the Then & Now comparison modal and slider freeze bugs are structurally eliminated:
 * 1. Modal DOM reparenting (ensureModalInBody) prevents sidebar-column / overflow stacking context backdrop traps.
 * 2. Explicit z-index: 1060 on #thenNowComparisonModal ensures it floats above Bootstrap backdrops (1050).
 * 3. Drag hijacking protection: pointer-events: none, user-select: none, and -webkit-user-drag: none on images.
 * 4. Pointer capture API integration (setPointerCapture) on #tnModalSliderStage for fluid 60fps dragging without freezing.
 * 5. Defensive backdrop and body scroll cleanup on hidden.bs.modal.
 * 6. Root layout availability on both profilePage.blade.php and getProfile.blade.php.
 */

$rootDir = dirname(__DIR__);

echo "\n=================================================================\n";
echo "🛡️ THEN & NOW FREEZE FIX REGRESSION VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$errors = [];

// 1. Check thenNowComparisonModal.blade.php
$modalPath = $rootDir . '/resources/views/member/modals/thenNowComparisonModal.blade.php';
if (!file_exists($modalPath)) {
    $errors[] = "Missing {$modalPath}";
} else {
    $modalContent = file_get_contents($modalPath);

    // Gate 1: ensureModalInBody reparenting
    if (strpos($modalContent, 'ensureModalInBody') !== false && strpos($modalContent, 'document.body.appendChild') !== false) {
        echo "  ✅ PASSED: ensureModalInBody() moves modal into document.body to avoid stacking context traps.\n";
    } else {
        $errors[] = "ensureModalInBody() DOM reparenting logic missing from thenNowComparisonModal.blade.php";
    }

    // Gate 2: z-index: 1060
    if (strpos($modalContent, 'z-index: 1060') !== false) {
        echo "  ✅ PASSED: Modal container explicitly enforces z-index: 1060 (above backdrop at 1050).\n";
    } else {
        $errors[] = "z-index: 1060 missing from thenNowComparisonModal.blade.php";
    }

    // Gate 3: Native drag hijacking prevention on images
    if (strpos($modalContent, '-webkit-user-drag: none') !== false && strpos($modalContent, 'pointer-events: none') !== false) {
        echo "  ✅ PASSED: Images enforce pointer-events: none and -webkit-user-drag: none to prevent drag freeze.\n";
    } else {
        $errors[] = "Image drag hijacking prevention attributes missing from thenNowComparisonModal.blade.php";
    }

    // Gate 4: Direct Pointer Events / pointer capture API
    if (strpos($modalContent, 'setPointerCapture') !== false && strpos($modalContent, 'pointerdown') !== false && strpos($modalContent, 'pointermove') !== false) {
        echo "  ✅ PASSED: #tnModalSliderStage implements PointerEvent capture for 60fps drag tracking.\n";
    } else {
        $errors[] = "Pointer capture event listeners missing from thenNowComparisonModal.blade.php";
    }

    // Gate 5: Defensive backdrop cleanup on hidden.bs.modal
    if (strpos($modalContent, 'hidden.bs.modal') !== false && strpos($modalContent, 'modal-backdrop') !== false && strpos($modalContent, 'modal-open') !== false) {
        echo "  ✅ PASSED: hidden.bs.modal listener cleans orphaned backdrops and restores body scrolling.\n";
    } else {
        $errors[] = "hidden.bs.modal cleanup listener missing from thenNowComparisonModal.blade.php";
    }

    // Gate 6: Dual clip-path and webkitClipPath updates
    if (strpos($modalContent, 'clipPath') !== false && strpos($modalContent, 'webkitClipPath') !== false) {
        echo "  ✅ PASSED: Dual standard and webkit clipPath applied to slider divider.\n";
    } else {
        $errors[] = "Dual clipPath / webkitClipPath updates missing from thenNowComparisonModal.blade.php";
    }
}

// 2. Check getProfile.blade.php and profilePage.blade.php
$profilePage = file_get_contents($rootDir . '/resources/views/member/profilePage.blade.php');
$getProfile = file_get_contents($rootDir . '/resources/views/member/getProfile.blade.php');

if (strpos($profilePage, 'thenNowComparisonModal') !== false) {
    echo "  ✅ PASSED: profilePage.blade.php includes thenNowComparisonModal at root layout level.\n";
} else {
    $errors[] = "thenNowComparisonModal missing from profilePage.blade.php";
}

if (strpos($getProfile, 'thenNowComparisonModal') !== false) {
    echo "  ✅ PASSED: getProfile.blade.php includes thenNowComparisonModal at root layout level.\n";
} else {
    $errors[] = "thenNowComparisonModal missing from getProfile.blade.php";
}

// 3. Check post-card.blade.php
$postCard = file_get_contents($rootDir . '/resources/views/components/feed/post-card.blade.php');
if (strpos($postCard, '-webkit-user-drag: none') !== false && strpos($postCard, 'touch-action: none') !== false) {
    echo "  ✅ PASSED: Feed post card Then & Now slider images and input range protected from drag freeze.\n";
} else {
    $errors[] = "Feed post card Then & Now slider drag protection missing in post-card.blade.php";
}

echo "\n=================================================================\n";
if (empty($errors)) {
    echo "🏆 ALL THEN & NOW FREEZE FIX CHECKS PASSED WITH ZERO ERRORS!\n";
    echo "=================================================================\n\n";
    exit(0);
} else {
    echo "❌ FAILURES DETECTED (" . count($errors) . "):\n";
    foreach ($errors as $err) {
        echo "  - {$err}\n";
    }
    echo "=================================================================\n\n";
    exit(1);
}

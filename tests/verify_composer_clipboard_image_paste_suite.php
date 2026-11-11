<?php
/**
 * 🧪 Verification Suite: Composer Clipboard Image & Screenshot Paste
 * Application: FamilyPlatform
 * Charter: Master Engineering, Board & Security Governance Charter (v3.0 Executive Edition)
 */

declare(strict_types=1);

$failures = 0;
$passes = 0;

function assertCondition(bool $cond, string $msg): void {
    global $failures, $passes;
    if ($cond) {
        echo "  ✅ PASSED: {$msg}\n";
        $passes++;
    } else {
        echo "  ❌ FAILED: {$msg}\n";
        $failures++;
    }
}

echo "=================================================================\n";
echo "📋 COMPOSER CLIPBOARD IMAGE PASTE VERIFICATION SUITE\n";
echo "=================================================================\n";

$bladePath = __DIR__ . '/../resources/views/member/modals/post.blade.php';
$bladeContent = file_get_contents($bladePath);

echo "\n[TEST 1] Verifying Blade Template Clipboard Image Extraction & Attachment Helpers...\n";
assertCondition(
    str_contains($bladeContent, 'extractClipboardImages'),
    "post.blade.php defines extractClipboardImages() helper"
);
assertCondition(
    str_contains($bladeContent, 'attachPastedImagesToComposer'),
    "post.blade.php defines attachPastedImagesToComposer() helper"
);
assertCondition(
    str_contains($bladeContent, 'new DataTransfer()'),
    "post.blade.php leverages native DataTransfer API to assign pasted files into #imageUpload"
);

echo "\n[TEST 2] Verifying Editor Paste Event Handler Checks for Images Before Plain Text Fallback...\n";
assertCondition(
    str_contains($bladeContent, 'const pastedImages = extractClipboardImages(clipboard);'),
    "editor paste event extracts image files from clipboard before falling back to plain text"
);
assertCondition(
    str_contains($bladeContent, 'e.__fpImagePasteHandled = true;'),
    "editor paste sets __fpImagePasteHandled flag to prevent duplicate event processing"
);

echo "\n[TEST 3] Verifying Modal-Wide Paste Listener on #postModal...\n";
assertCondition(
    str_contains($bladeContent, "composerModalEl = document.getElementById('postModal')") ||
    str_contains($bladeContent, "document.getElementById('postModal')"),
    "Modal-level paste listener registered on #postModal for full-dialog pasting"
);
assertCondition(
    str_contains($bladeContent, "attachPastedImagesToComposer(pastedImages)"),
    "Modal-level paste listener routes pasted images to #imageUpload"
);

echo "\n[TEST 4] Verifying PostEmojiImgProcess JS Targets #postMessageEditor...\n";
$jsPath = __DIR__ . '/../resources/asset/js/components/profilePage/postEmojiImgProcess.js';
$jsContent = file_get_contents($jsPath);

assertCondition(
    str_contains($jsContent, "document.getElementById('postMessageEditor')"),
    "postEmojiImgProcess.js prioritises #postMessageEditor instead of hidden #postMessage"
);
assertCondition(
    str_contains($jsContent, "clipboard.files && clipboard.files.length"),
    "postEmojiImgProcess.js handles clipboard.files for operating-system file copies"
);
assertCondition(
    str_contains($jsContent, "clipboard.items && clipboard.items.length"),
    "postEmojiImgProcess.js handles clipboard.items for screenshots and browser copied images"
);

echo "\n[TEST 5] Verifying Production JS Bundle Contains Updated Paste Logic...\n";
$bundlePath = __DIR__ . '/../public/js/profilePage.js';
$bundleContent = file_get_contents($bundlePath);

assertCondition(
    str_contains($bundleContent, "postMessageEditor"),
    "Compiled public/js/profilePage.js includes #postMessageEditor target"
);
assertCondition(
    str_contains($bundleContent, "imageUpload"),
    "Compiled public/js/profilePage.js targets #imageUpload for clipboard file transfer"
);

echo "\n=================================================================\n";
echo "SUMMARY: {$passes} Passed, {$failures} Failed\n";
echo "=================================================================\n";

if ($failures > 0) {
    exit(1);
}

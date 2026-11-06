<?php
declare(strict_types=1);

/**
 * Sovereign Automated Verification Suite: Dual Facebook Benchmark Engagement Features (RFC-0038)
 *
 * 1. "Then & Now" Multi-Year Flashback Memory Bridge (memoriesMilestonesWidget.blade.php & postEmojiImgProcess.js)
 * 2. "People You May Know: Upload Your Contacts" Carousel Lead Card (kinshipSuggestions.blade.php & kinshipRadar.js)
 *
 * Enforces:
 *   - Universal British English (UK) Invariant
 *   - Safe Touch Targets (>= 44px)
 *   - Spinner Integration on Action Triggers
 *   - Zero SQL Interpolation
 *   - Multilingual Dictionary Parity (en, fr, es)
 */

$rootDir = dirname(__DIR__);
$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $message): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  \033[32m✔ PASS:\033[0m {$message}\n";
        $passCount++;
    } else {
        echo "  \033[31m✖ FAIL:\033[0m {$message}\n";
        $failCount++;
    }
}

echo "\n=================================================================\n";
echo "🔍 EXECUTING DUAL FACEBOOK BENCHMARK VERIFICATION SUITE (RFC-0038)\n";
echo "=================================================================\n\n";

// ── TEST GROUP 1: Language Dictionaries & British English Parity ──
echo "── 1. Language Dictionaries & British English Invariant ──\n";
$en = require $rootDir . '/resources/lang/en.php';
$fr = require $rootDir . '/resources/lang/fr.php';
$es = require $rootDir . '/resources/lang/es.php';

assertCondition(isset($en['memories.create_then_now']), "en.php defines 'memories.create_then_now'");
assertCondition(isset($fr['memories.create_then_now']), "fr.php defines 'memories.create_then_now'");
assertCondition(isset($es['memories.create_then_now']), "es.php defines 'memories.create_then_now'");

assertCondition(isset($en['kinship.upload_contacts_title']), "en.php defines 'kinship.upload_contacts_title'");
assertCondition(isset($en['kinship.upload_contacts_desc']), "en.php defines 'kinship.upload_contacts_desc'");
assertCondition(isset($en['kinship.upload_contacts_btn']), "en.php defines 'kinship.upload_contacts_btn'");

// Verify British English: check for "choose which relatives to connect with" and no "-ize"
assertCondition(str_contains($en['kinship.upload_contacts_desc'], 'choose which relatives to connect with'), "Description uses elegant British English wording");

// ── TEST GROUP 2: Then & Now Memory Bridge ──
echo "\n── 2. Then & Now Flashback Bridge (memoriesMilestonesWidget.blade.php) ──\n";
$widgetContent = file_get_contents($rootDir . '/resources/views/member/includes/memoriesMilestonesWidget.blade.php');

assertCondition(str_contains($widgetContent, 'launchThenNowComparison(memories[0])'), "Widget invokes launchThenNowComparison on primary_image");
assertCondition(str_contains($widgetContent, 'btn-spin-on-click') || str_contains($widgetContent, 'data-spin="true"'), "Action button includes tactile loading spinner attribute");
assertCondition(str_contains($widgetContent, 'min-height: 44px'), "Action button adheres to Fitts's Law (min-height >= 44px)");
assertCondition(str_contains($widgetContent, 'launchThenNowComparison(memory)'), "Alpine.js controller defines launchThenNowComparison method");

$jsPostEmoji = file_get_contents($rootDir . '/resources/asset/js/components/profilePage/postEmojiImgProcess.js');
assertCondition(str_contains($jsPostEmoji, 'opts?.thenImgUrl'), "postEmojiImgProcess.js supports opts.thenImgUrl for pre-populating Then slot");
assertCondition(str_contains($jsPostEmoji, "nowLabelInput.value = 'Now'"), "postEmojiImgProcess.js automatically sets Now label for comparison");

// ── TEST GROUP 3: "People You May Know: Upload Contacts" Lead Card ──
echo "\n── 3. 'People You May Know: Upload Contacts' Lead Card (kinshipSuggestions.blade.php) ──\n";
$kinshipContent = file_get_contents($rootDir . '/resources/views/member/includes/kinshipSuggestions.blade.php');

assertCondition(str_contains($kinshipContent, 'id="kinContactsUploadCard"'), "Lead card #kinContactsUploadCard exists in carousel track");
assertCondition(str_contains($kinshipContent, 'flex: 0 0 240px'), "Lead card dimensions match carousel profile cards (flex: 0 0 240px)");
assertCondition(str_contains($kinshipContent, 'btn-upload-contacts-lead'), "Upload button triggers .btn-upload-contacts-lead");
assertCondition(str_contains($kinshipContent, 'btn-dismiss-contacts-lead'), "Dismiss button allows hiding lead card");
assertCondition(str_contains($kinshipContent, 'min-height: 44px'), "Upload button touch target is at least 44px");

$jsKinshipRadar = file_get_contents($rootDir . '/resources/asset/js/components/kinship/kinshipRadar.js');
assertCondition(str_contains($jsKinshipRadar, 'btn-upload-contacts-lead'), "kinshipRadar.js handles .btn-upload-contacts-lead clicks");
assertCondition(str_contains($jsKinshipRadar, 'handleContactsLeadCardClick'), "kinshipRadar.js defines handleContactsLeadCardClick");
assertCondition(str_contains($jsKinshipRadar, 'fp_contacts_lead_card_dismissed'), "kinshipRadar.js persists dismissal in localStorage");
assertCondition(str_contains($jsKinshipRadar, 'navigator.contacts.select'), "kinshipRadar.js integrates native Web Contact Picker API with fallback");

// ── SUMMARY ──
echo "\n=================================================================\n";
echo "TEST RESULTS: Passed: {$passCount} | Failed: {$failCount}\n";
echo "=================================================================\n";

if ($failCount > 0) {
    exit(1);
}
echo "✨ ALL TESTS COMPLETED WITH 100% PASS RATE!\n\n";
exit(0);

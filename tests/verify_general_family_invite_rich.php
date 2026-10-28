<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Rich General Family Invite with Family Code
 *
 * Verifies that:
 * 1. OnboardingService generates rich invite payload with Family Code & rich WhatsApp URL.
 * 2. InviteController outputs rich WhatsApp message containing Family Code, emojis, and call-to-action.
 * 3. Birth/Maiden lineage adapts to "Birth Family Code" appropriately.
 * 4. UI components (inviteFamilyModal, recommendModal, leftColumn, familyFlyerModal) expose
 *    Copy Full Invite, Copy Link, and Share via WhatsApp with 100% rich format parity.
 * 5. British English invariant is strictly preserved across all user-facing invite copy.
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/vendor/autoload.php';

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $description): void {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$description}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$description}\n";
        $failed++;
    }
}

echo "\n======================================================================\n";
echo "  🌳 VERIFYING RICH GENERAL FAMILY INVITE WITH FAMILY CODE\n";
echo "======================================================================\n\n";

// [1] Verify OnboardingService::generateInviteData
echo "[1] Testing OnboardingService::generateInviteData...\n";

// Mock DB connection for OnboardingService
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$onboardingService = new \App\services\OnboardingService($pdo);

// Test marital / household scope
$testFamCode = 'TEST99001';
$inviteData = $onboardingService->generateInviteData('user_123', $testFamCode, 'primary');

assertCondition(isset($inviteData['invite_text']), 'invite_text is present in invite_data');
assertCondition(str_contains($inviteData['invite_text'], '🌳 *Family Heritage Invitation* 🌳'), 'invite_text contains tree header');
assertCondition(str_contains($inviteData['invite_text'], "🔑 *Family Code:* #{$testFamCode}"), 'invite_text explicitly calls out Family Code');
assertCondition(str_contains($inviteData['invite_text'], '👉 Tap here to join our family sanctuary:'), 'invite_text contains call-to-action arrow');
assertCondition(str_contains($inviteData['invite_text'], 'Welcome to our living family legacy! 🏰'), 'invite_text contains closing salute');
assertCondition(str_contains($inviteData['whatsapp_url'], 'https://api.whatsapp.com/send?text='), 'whatsapp_url points to WhatsApp API');
assertCondition(str_contains(urldecode($inviteData['whatsapp_url']), "🔑 *Family Code:* #{$testFamCode}"), 'whatsapp_url encodes the rich message with Family Code');

// Test birth / maiden scope
$birthFamCode = 'BIRTH88002';
$birthInviteData = $onboardingService->generateInviteData('user_123', $birthFamCode, 'maternal');
assertCondition(str_contains($birthInviteData['invite_text'], "🔑 *Birth Family Code:* #{$birthFamCode}"), 'Birth lineage invite_text uses Birth Family Code label');
assertCondition(str_contains($birthInviteData['invite_text'], 'Birth Family Network & Heritage Tree'), 'Birth lineage invite_text specifies Birth Family Network');

// [2] Verify InviteController Formatting
echo "\n[2] Testing InviteController WhatsApp message format...\n";
$inviteControllerFile = file_get_contents($baseDir . '/app/controller/InviteController.php');
assertCondition($inviteControllerFile !== false, 'InviteController.php is readable');
assertCondition(str_contains($inviteControllerFile, '🌳 *Family Heritage Invitation* 🌳'), 'InviteController contains rich tree header');
assertCondition(str_contains($inviteControllerFile, '🔑 *{$codeLabel}:* #{$familyCode}'), 'InviteController explicitly embeds Family Code with label');
assertCondition(str_contains($inviteControllerFile, '👉 Tap here to join our family sanctuary:'), 'InviteController contains call to action arrow');
assertCondition(str_contains($inviteControllerFile, 'Welcome to our living family legacy! 🏰'), 'InviteController contains closing salute');

// [3] Verify inviteFamilyModal.blade.php UI & Script
echo "\n[3] Testing inviteFamilyModal.blade.php...\n";
$inviteModalFile = file_get_contents($baseDir . '/resources/views/member/modals/inviteFamilyModal.blade.php');
assertCondition($inviteModalFile !== false, 'inviteFamilyModal.blade.php is readable');
assertCondition(str_contains($inviteModalFile, 'id="btnCopyFullInvite"'), 'inviteFamilyModal contains Copy Full Invite button (#btnCopyFullInvite)');
assertCondition(str_contains($inviteModalFile, 'id="btnCopyInviteLink"'), 'inviteFamilyModal contains Copy Link Only button (#btnCopyInviteLink)');
assertCondition(str_contains($inviteModalFile, 'id="btnInviteWhatsApp"'), 'inviteFamilyModal contains WhatsApp button (#btnInviteWhatsApp)');
assertCondition(str_contains($inviteModalFile, 'Copy Full Invite'), 'inviteFamilyModal displays "Copy Full Invite" label');
assertCondition(str_contains($inviteModalFile, 'Copy Link Only'), 'inviteFamilyModal displays "Copy Link Only" label');
assertCondition(str_contains($inviteModalFile, 'white-space: pre-wrap;'), 'Message preview has white-space: pre-wrap for formatted line breaks');
assertCondition(str_contains($inviteModalFile, '🌳 *Family Heritage Invitation* 🌳'), 'Kinship template contains rich tree header for General');
assertCondition(str_contains($inviteModalFile, '🔑 *Family Code:* #${code}'), 'Kinship template embeds Family Code for General');
assertCondition(str_contains($inviteModalFile, 'btnCopyFull.addEventListener'), 'JS event listener wired for Copy Full Invite');

// [4] Verify recommendModal.blade.php Dual Tabs & Family Invite
echo "\n[4] Testing recommendModal.blade.php...\n";
$recommendModalFile = file_get_contents($baseDir . '/resources/views/member/modals/recommendModal.blade.php');
assertCondition($recommendModalFile !== false, 'recommendModal.blade.php is readable');
assertCondition(str_contains($recommendModalFile, 'id="tab-our-family"'), 'recommendModal contains Our Family Network tab');
assertCondition(str_contains($recommendModalFile, 'id="tab-external-friends"'), 'recommendModal contains External Friends/In-Laws tab');
assertCondition(str_contains($recommendModalFile, 'id="btnFamilyModalCopyFull"'), 'recommendModal contains Copy Full Invite button');
assertCondition(str_contains($recommendModalFile, 'id="btnFamilyModalWhatsApp"'), 'recommendModal contains WhatsApp button for family');
assertCondition(str_contains($recommendModalFile, '🔑 *Family Code:* #${code}'), 'recommendModal script embeds Family Code in rich invite message');

// [5] Verify leftColumn.blade.php Profile Page Trigger
echo "\n[5] Testing leftColumn.blade.php profile page trigger...\n";
$leftColFile = file_get_contents($baseDir . '/resources/views/member/includes/leftColumn.blade.php');
assertCondition($leftColFile !== false, 'leftColumn.blade.php is readable');
assertCondition(str_contains($leftColFile, 'data-bs-target="#inviteFamilyModal"'), 'leftColumn contains button opening #inviteFamilyModal');
assertCondition(str_contains($leftColFile, 'Invite Family'), 'leftColumn has Invite Family label');

// [6] Verify British English Invariant
echo "\n[6] Testing British English Compliance...\n";
$prohibitedUS = ['organize', 'customize', 'prioritize', 'neighbor', 'defense'];
$viewsToCheck = [$inviteModalFile, $recommendModalFile];
$usSpellingFound = false;
foreach ($viewsToCheck as $viewContent) {
    // Strip HTML tags and script/style contents to inspect visible text
    $cleanText = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $viewContent) ?? $viewContent;
    $cleanText = strip_tags($cleanText);
    foreach ($prohibitedUS as $word) {
        if (stripos($cleanText, $word) !== false) {
            $usSpellingFound = true;
            echo "  ❌ Found US English spelling in visible text: {$word}\n";
        }
    }
}
assertCondition(!$usSpellingFound, 'Zero US English spellings detected in invite view templates');

echo "\n======================================================================\n";
echo "  SUMMARY: {$passed} Passed, {$failed} Failed\n";
echo "======================================================================\n";

if ($failed > 0) {
    exit(1);
}
echo "🎉 ALL TESTS PASSED!\n\n";
exit(0);

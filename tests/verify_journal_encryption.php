<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\model\FamilyJournal;
use App\services\FamilyTimeCapsuleService;
use App\services\EncryptionService;
use Src\Db;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

echo "======================================================================\n";
echo "🛡️  FAMILY VAULT END-TO-END CRYPTOGRAPHIC INTEGRATION VERIFICATION\n";
echo "======================================================================\n";

$familyCode = "FAM_CRYPTO_VERIFY_" . bin2hex(random_bytes(4));
$authorId = "user_test_vault_" . bin2hex(random_bytes(4));
$db = Db::connect2();

try {
    // -------------------------------------------------------------
    // Part 1: Family Journal Encrypted Preservation & Retrieval
    // -------------------------------------------------------------
    echo "\n[1/3] Testing FamilyJournal Storage & Transparent Decryption...\n";
    $plainTitle = "Grandma's 90th Milestone & Legacy Advice";
    $plainContent = "<h2>Cherished Family Memories</h2><p>Always stay united, honour your elders, and keep our traditions alive.</p>";
    $plainSummary = "Wisdom shared by Grandma on her 90th birthday.";

    $journalId = FamilyJournal::createJournal([
        'family_code' => $familyCode,
        'author_id'   => $authorId,
        'title'       => $plainTitle,
        'slug'        => 'grandmas-90th-milestone-' . substr(bin2hex(random_bytes(2)), 0, 4),
        'summary'     => $plainSummary,
        'content'     => $plainContent,
        'category'    => 'Milestone',
        'mood'        => 'heartwarming',
        'status'      => 'published'
    ]);

    if ($journalId <= 0) {
        throw new RuntimeException("Failed to insert test journal entry.");
    }
    echo "  ✅ Chronicle created with ID: {$journalId}\n";

    // Direct Raw SQL Audit: Confirm ciphertext at rest in MySQL
    $rawStmt = $db->prepare("SELECT content, summary FROM family_journals WHERE id = ?");
    $rawStmt->execute([$journalId]);
    $rawRow = $rawStmt->fetch(PDO::FETCH_ASSOC);

    if (empty($rawRow)) {
        throw new RuntimeException("Raw DB query failed to find inserted journal.");
    }

    $rawContent = (string)$rawRow['content'];
    $rawSummary = (string)$rawRow['summary'];

    echo "  🔎 Raw DB Content preview: " . substr($rawContent, 0, 30) . "...\n";
    if (!EncryptionService::isEncrypted($rawContent)) {
        throw new RuntimeException("FAIL: MySQL `content` column is NOT stored as vault ciphertext!");
    }
    if (!EncryptionService::isEncrypted($rawSummary)) {
        throw new RuntimeException("FAIL: MySQL `summary` column is NOT stored as vault ciphertext!");
    }
    echo "  ✅ PASS: Raw MySQL rows verified to be stored as AES-256-GCM ciphertext (vault:v1: prefix).\n";

    // Model Read Audit: Confirm transparent decryption
    $fetched = FamilyJournal::getJournalById($journalId, $familyCode);
    if ($fetched === null) {
        throw new RuntimeException("FAIL: FamilyJournal::getJournalById returned null!");
    }

    if ($fetched['content'] !== $plainContent) {
        throw new RuntimeException("FAIL: Decrypted content does not match original plaintext!");
    }
    if ($fetched['summary'] !== $plainSummary) {
        throw new RuntimeException("FAIL: Decrypted summary does not match original plaintext!");
    }
    echo "  ✅ PASS: FamilyJournal::getJournalById transparently decrypted content and summary.\n";

    // -------------------------------------------------------------
    // Part 2: Time Capsule Encrypted Storage & Retrieval
    // -------------------------------------------------------------
    echo "\n[2/3] Testing FamilyTimeCapsuleService Storage & Decryption...\n";
    $plainCapsuleMsg = "Open this letter when you graduate from university in 2035.";
    $capsuleId = FamilyTimeCapsuleService::createCapsule($familyCode, $authorId, [
        'recipient_name'    => 'Little David',
        'recipient_user_id' => null,
        'title'             => 'A Letter to Graduate David',
        'message'           => $plainCapsuleMsg,
        'unlock_date'       => date('Y-m-d', strtotime('-1 day')), // Unlocked for retrieval
        'unlock_type'       => 'date',
    ]);

    $rawCapStmt = $db->prepare("SELECT message FROM family_time_capsules WHERE id = ?");
    $rawCapStmt->execute([$capsuleId]);
    $rawCapMsg = (string)$rawCapStmt->fetchColumn();

    echo "  🔎 Raw DB Capsule preview: " . substr($rawCapMsg, 0, 30) . "...\n";
    if (!EncryptionService::isEncrypted($rawCapMsg)) {
        throw new RuntimeException("FAIL: MySQL time capsule message is NOT stored as vault ciphertext!");
    }
    echo "  ✅ PASS: Time capsule message verified encrypted at rest.\n";

    $capsules = FamilyTimeCapsuleService::getFamilyCapsules($familyCode, $authorId);
    $foundCapsule = null;
    foreach ($capsules as $c) {
        if ($c['id'] === $capsuleId) {
            $foundCapsule = $c;
            break;
        }
    }

    if ($foundCapsule === null) {
        throw new RuntimeException("FAIL: Created capsule was not found in getFamilyCapsules list.");
    }

    if ($foundCapsule['message'] !== $plainCapsuleMsg) {
        throw new RuntimeException("FAIL: Decrypted capsule message does not match original plaintext!");
    }
    echo "  ✅ PASS: FamilyTimeCapsuleService transparently decrypted unlocked capsule message.\n";

    // -------------------------------------------------------------
    // Part 3: Teardown & Clean Up
    // -------------------------------------------------------------
    echo "\n[3/3] Cleaning Up Ephemeral Test Data...\n";
    $db->prepare("DELETE FROM family_journals WHERE id = ?")->execute([$journalId]);
    $db->prepare("DELETE FROM family_time_capsules WHERE id = ?")->execute([$capsuleId]);
    echo "  ✅ PASS: Test data purged from database.\n";

    echo "\n🎉 ALL INTEGRATION TESTS PASSED: 100% End-to-End Cryptographic Vault Verified!\n";
} catch (\Throwable $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

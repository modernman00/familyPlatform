<?php
declare(strict_types=1);

/**
 * Regression Test: Sovereign Family Tree Migration & Grafting Verification
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable) {}

use Src\Db;
use App\services\FamilyMigrationService;

echo "=================================================================\n";
echo "🌳 RUNNING FAMILY TREE MIGRATION & GRAFTING REGRESSION SUITE\n";
echo "=================================================================\n";

$db = Db::connect2();

$testUserId = 'TEST_USER_GRAFT_' . bin2hex(random_bytes(4));
$oldCode = 'OLD' . mt_rand(1000, 9999);
$newCode = 'NEW' . mt_rand(1000, 9999);

try {
    // 1. Setup mock user in personal and code_mgt under $oldCode
    $stmtP = $db->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, gender)
        VALUES (?, 'Olusola', 'Olaogun', ?, 'Male')
    ");
    $stmtP->execute([$testUserId, $oldCode]);

    $stmtCm = $db->prepare("INSERT INTO code_mgt (id, code) VALUES (?, ?)");
    $stmtCm->execute([$testUserId, $oldCode]);

    // 2. Setup mock tree in $oldCode: Root, Partner, Child, and Union
    $stmtN1 = $db->prepare("
        INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, bio)
        VALUES (?, ?, 'Olusola', 'Olaogun', 'Male', 0, 'Original Bio')
    ");
    $stmtN1->execute([$oldCode, $testUserId]);
    $oldRootId = (int)$db->lastInsertId();

    $stmtN2 = $db->prepare("
        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
        VALUES (?, 'Spouse', 'Olaogun', 'Female', 0)
    ");
    $stmtN2->execute([$oldCode]);
    $spouseNodeId = (int)$db->lastInsertId();

    $stmtN3 = $db->prepare("
        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
        VALUES (?, 'ChildOne', 'Olaogun', 'Female', 1)
    ");
    $stmtN3->execute([$oldCode]);
    $childNodeId = (int)$db->lastInsertId();

    // Union linking oldRoot and spouse
    $stmtU = $db->prepare("
        INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type)
        VALUES (?, ?, ?, 'married')
    ");
    $stmtU->execute([$oldCode, $oldRootId, $spouseNodeId]);
    $unionId = (int)$db->lastInsertId();

    // Child linking
    $stmtC = $db->prepare("
        INSERT INTO family_node_children (union_id, parent_node_id, child_id)
        VALUES (?, ?, ?)
    ");
    $stmtC->execute([$unionId, $oldRootId, $childNodeId]);

    echo "✅ [Setup Complete] Mock user {$testUserId} provisioned with 3 nodes & 1 union in #{$oldCode}.\n";

    // 3. Execute Atomic Migration from $oldCode to $newCode
    $migrationService = new FamilyMigrationService($db);
    $migrated = $migrationService->executeAtomicMigration($testUserId, $oldCode, $newCode, $testUserId);

    if (!$migrated) {
        throw new RuntimeException("Migration failed unexpectedly!");
    }
    echo "✅ [Atomic Migration Complete] Migrated to #{$newCode}.\n";

    // 4. Assertions:
    // A) personal.famCode updated
    $stmtCheckP = $db->prepare("SELECT famCode FROM personal WHERE id = ?");
    $stmtCheckP->execute([$testUserId]);
    $updatedFamCode = $stmtCheckP->fetchColumn();
    if ($updatedFamCode !== $newCode) {
        throw new RuntimeException("Assertion failed: famCode is {$updatedFamCode}, expected {$newCode}");
    }
    echo "  [PASS] personal.famCode updated to #{$newCode}.\n";

    // B) user_families retains approved status for both old and new code
    $stmtUf = $db->prepare("SELECT family_code, status FROM user_families WHERE user_id = ?");
    $stmtUf->execute([$testUserId]);
    $ufs = $stmtUf->fetchAll(PDO::FETCH_KEY_PAIR);

    if (($ufs[$newCode] ?? '') !== 'approved') {
        throw new RuntimeException("Assertion failed: new code {$newCode} not approved in user_families!");
    }
    if (($ufs[$oldCode] ?? '') !== 'approved') {
        throw new RuntimeException("Assertion failed: old code {$oldCode} lost approved status in user_families!");
    }
    echo "  [PASS] user_families retains approved status for both #{$oldCode} and #{$newCode}.\n";

    // C) Check grafted nodes in newCode
    $stmtGraftedNodes = $db->prepare("SELECT id, first_name, user_id, bio FROM family_nodes WHERE family_code = ?");
    $stmtGraftedNodes->execute([$newCode]);
    $graftedNodes = $stmtGraftedNodes->fetchAll(PDO::FETCH_ASSOC);

    echo "  Grafted nodes count in #{$newCode}: " . count($graftedNodes) . "\n";
    $nodeNames = array_column($graftedNodes, 'first_name');

    if (!in_array('Spouse', $nodeNames, true)) {
        throw new RuntimeException("Assertion failed: Spouse node was not grafted into #{$newCode}!");
    }
    if (!in_array('ChildOne', $nodeNames, true)) {
        throw new RuntimeException("Assertion failed: Child node was not grafted into #{$newCode}!");
    }
    echo "  [PASS] Spouse and Child nodes successfully grafted into #{$newCode}.\n";

    // D) Verify Unions were re-pointed to the new root node
    $stmtNewRoot = $db->prepare("SELECT id, bio FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
    $stmtNewRoot->execute([$newCode, $testUserId]);
    $newRoot = $stmtNewRoot->fetch(PDO::FETCH_ASSOC);

    if (!$newRoot) {
        throw new RuntimeException("Assertion failed: No active root node in #{$newCode}!");
    }
    $newRootId = (int)$newRoot['id'];

    $stmtCheckU = $db->prepare("SELECT * FROM family_unions WHERE family_code = ?");
    $stmtCheckU->execute([$newCode]);
    $graftedUnions = $stmtCheckU->fetchAll(PDO::FETCH_ASSOC);

    if (empty($graftedUnions)) {
        throw new RuntimeException("Assertion failed: Union not grafted into #{$newCode}!");
    }

    $gU = $graftedUnions[0];
    if ((int)$gU['partner_1_id'] !== $newRootId && (int)$gU['partner_2_id'] !== $newRootId) {
        throw new RuntimeException("Assertion failed: Union partners not re-pointed to new root node #{$newRootId}!");
    }
    echo "  [PASS] Union partners successfully re-pointed to new root node #{$newRootId}.\n";

    // E) Verify biographical sync
    if ($newRoot['bio'] !== 'Original Bio') {
        throw new RuntimeException("Assertion failed: Biographical data was not preserved on new root node!");
    }
    echo "  [PASS] Biographical data ('Original Bio') successfully preserved.\n";

    echo "=================================================================\n";
    echo "🏆 ALL REGRESSION TESTS PASSED! ZERO DATA LOSS ON MIGRATION.\n";
    echo "=================================================================\n";
} finally {
    // Clean up test data
    $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code IN (?, ?))")->execute([$oldCode, $newCode]);
    $db->prepare("DELETE FROM family_unions WHERE family_code IN (?, ?)")->execute([$oldCode, $newCode]);
    $db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?)")->execute([$oldCode, $newCode]);
    $db->prepare("DELETE FROM user_families WHERE user_id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM code_mgt WHERE id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
}

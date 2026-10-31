<?php
declare(strict_types=1);

/**
 * FamilyPlatform Production Tree Recovery & Grafting Tool
 *
 * Safely inspects, restores, and grafts family trees for users whose tree information
 * was left in a previous family code following an account migration.
 *
 * Usage:
 *   php scripts/recover_migrated_family_tree.php --search="Olusola Olaogun"
 *   php scripts/recover_migrated_family_tree.php --user="USER_ID" --action=inspect
 *   php scripts/recover_migrated_family_tree.php --user="USER_ID" --action=restore_access
 *   php scripts/recover_migrated_family_tree.php --user="USER_ID" --action=graft_tree [--dry-run]
 */

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
} catch (\Throwable) {}

use Src\Db;

$options = getopt('', ['search:', 'user:', 'action:', 'old-code:', 'new-code:', 'dry-run', 'help']);

if (isset($options['help']) || (empty($options['search']) && empty($options['user']) && empty($options['action']))) {
    echo "=================================================================\n";
    echo "  🌳 FAMILYPLATFORM PRODUCTION FAMILY TREE RECOVERY & GRAFT TOOL\n";
    echo "=================================================================\n";
    echo "Options:\n";
    echo "  --search=\"Name or Email\"     Search for a user by first name, last name, or email.\n";
    echo "  --user=\"USER_ID\"             Specify target user ID directly.\n";
    echo "  --action=inspect             (Default) Inspect family codes, nodes, and migration records.\n";
    echo "  --action=restore_access      Restore approved multi-lineage access in user_families.\n";
    echo "  --action=graft_tree          Graft nodes and unions from old code into new family code.\n";
    echo "  --old-code=\"CODE\"            Explicitly specify the previous family code.\n";
    echo "  --new-code=\"CODE\"            Explicitly specify the new family code.\n";
    echo "  --dry-run                    Preview changes without modifying the database.\n";
    echo "=================================================================\n";
    exit(0);
}

try {
    $db = Db::connect2();
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$search = isset($options['search']) ? trim((string)$options['search']) : '';
$userId = isset($options['user']) ? trim((string)$options['user']) : '';
$action = isset($options['action']) ? trim((string)$options['action']) : 'inspect';
$dryRun = isset($options['dry-run']);
$forcedOldCode = isset($options['old-code']) ? strtoupper(trim(str_replace('#', '', (string)$options['old-code']))) : '';
$forcedNewCode = isset($options['new-code']) ? strtoupper(trim(str_replace('#', '', (string)$options['new-code']))) : '';

// 1. Search Mode
if ($search !== '' && $userId === '') {
    echo "🔍 Searching for members matching '{$search}'...\n\n";
    $searchPattern = '%' . $search . '%';
    $stmt = $db->prepare("
        SELECT p.id, p.firstName, p.lastName, p.famCode, acc.email, cm.code as code_mgt_code
        FROM personal p
        LEFT JOIN account acc ON acc.id = p.id
        LEFT JOIN code_mgt cm ON cm.id = p.id
        WHERE p.firstName LIKE ? OR p.lastName LIKE ? OR acc.email LIKE ? OR p.id = ?
        ORDER BY p.no DESC
        LIMIT 20
    ");
    $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $search]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo "⚠️  No matching members found. Please check spelling or try searching by User ID.\n";
        exit(0);
    }

    echo "Found " . count($users) . " candidate member(s):\n";
    echo str_repeat('-', 90) . "\n";
    printf("%-18s | %-20s | %-12s | %-30s\n", "User ID", "Name", "Primary Code", "Email");
    echo str_repeat('-', 90) . "\n";
    foreach ($users as $u) {
        $fullName = trim(($u['firstName'] ?? '') . ' ' . ($u['lastName'] ?? ''));
        printf("%-18s | %-20s | %-12s | %-30s\n", $u['id'], $fullName, $u['famCode'] ?? 'NONE', $u['email'] ?? 'N/A');
    }
    echo str_repeat('-', 90) . "\n";

    if (count($users) === 1) {
        $userId = (string)$users[0]['id'];
        echo "\n👉 Exactly one member matched. Proceeding with user: {$userId}\n\n";
    } else {
        echo "\nPlease re-run specifying the exact user ID with --user=\"<ID>\".\n";
        exit(0);
    }
}

if ($userId === '') {
    echo "❌ Please specify a user ID using --user=\"USER_ID\".\n";
    exit(1);
}

// 2. Fetch User Dossier
$stmt = $db->prepare("
    SELECT p.id, p.firstName, p.lastName, p.famCode, acc.email, cm.code as code_mgt_code
    FROM personal p
    LEFT JOIN account acc ON acc.id = p.id
    LEFT JOIN code_mgt cm ON cm.id = p.id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ Member with ID '{$userId}' was not found in personal table.\n";
    exit(1);
}

$currentCode = strtoupper(trim(str_replace('#', '', (string)($user['famCode'] ?? ''))));
$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));

echo "=================================================================\n";
echo "  MEMBER DOSSIER: {$fullName} (ID: {$userId})\n";
echo "=================================================================\n";
echo "  Primary Family Code (personal):   #{$currentCode}\n";
echo "  Code Mgt Code:                    #" . ($user['code_mgt_code'] ?? 'NONE') . "\n";
echo "  Email Address:                    " . ($user['email'] ?? 'NONE') . "\n\n";

// 3. Inspect user_families and family_migration_requests
$stmtUf = $db->prepare("SELECT family_code, status, role, created_at FROM user_families WHERE user_id = ? ORDER BY id DESC");
$stmtUf->execute([$userId]);
$userFamilies = $stmtUf->fetchAll(PDO::FETCH_ASSOC);

echo "📋 Family Associations in user_families:\n";
foreach ($userFamilies as $uf) {
    $fCode = strtoupper(trim(str_replace('#', '', (string)$uf['family_code'])));
    echo "   - Code: #{$fCode} | Status: {$uf['status']} | Role: {$uf['role']}\n";
}
echo "\n";

$stmtMig = $db->prepare("
    SELECT id, requester_id, target_user_id, old_family_code, new_family_code, type, status, created_at, approved_at
    FROM family_migration_requests
    WHERE target_user_id = ? OR requester_id = ?
    ORDER BY id DESC
");
$stmtMig->execute([$userId, $userId]);
$migrations = $stmtMig->fetchAll(PDO::FETCH_ASSOC);

$detectedOldCodes = [];
if (!empty($migrations)) {
    echo "🔄 Migration History in family_migration_requests:\n";
    foreach ($migrations as $m) {
        $oldC = strtoupper(trim(str_replace('#', '', (string)$m['old_family_code'])));
        $newC = strtoupper(trim(str_replace('#', '', (string)$m['new_family_code'])));
        echo "   - Migration #{$m['id']} [{$m['type']}]: #{$oldC} -> #{$newC} | Status: {$m['status']} | Approved: " . ($m['approved_at'] ?? 'Pending') . "\n";
        if ($oldC !== '' && $oldC !== $currentCode) {
            $detectedOldCodes[] = $oldC;
        }
    }
    echo "\n";
}

// Also check any code in user_families that differs from currentCode
foreach ($userFamilies as $uf) {
    $fCode = strtoupper(trim(str_replace('#', '', (string)$uf['family_code'])));
    if ($fCode !== '' && $fCode !== $currentCode && !in_array($fCode, $detectedOldCodes, true)) {
        $detectedOldCodes[] = $fCode;
    }
}

// 4. Inspect Family Tree Nodes & Unions
echo "🌳 Family Tree Graph Breakdown Across Codes:\n";
$allCodesToCheck = array_unique(array_filter(array_merge([$currentCode], $detectedOldCodes, [$forcedOldCode, $forcedNewCode])));

foreach ($allCodesToCheck as $chkCode) {
    $stmtNodes = $db->prepare("SELECT COUNT(*) FROM family_nodes WHERE family_code = ?");
    $stmtNodes->execute([$chkCode]);
    $nodeCount = (int)$stmtNodes->fetchColumn();

    $stmtUnions = $db->prepare("SELECT COUNT(*) FROM family_unions WHERE family_code = ?");
    $stmtUnions->execute([$chkCode]);
    $unionCount = (int)$stmtUnions->fetchColumn();

    $stmtUserNode = $db->prepare("SELECT id, first_name, last_name, generation_level FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
    $stmtUserNode->execute([$chkCode, $userId]);
    $uNode = $stmtUserNode->fetch(PDO::FETCH_ASSOC);

    $isCurrent = ($chkCode === $currentCode) ? ' [ACTIVE PRIMARY]' : '';
    echo "   • Family Code #{$chkCode}{$isCurrent}:\n";
    echo "       Nodes count:  {$nodeCount}\n";
    echo "       Unions count: {$unionCount}\n";
    if ($uNode) {
        echo "       Member Root:  Node #{$uNode['id']} ({$uNode['first_name']} {$uNode['last_name']}, Gen {$uNode['generation_level']})\n";
    } else {
        echo "       Member Root:  None (Unlinked in this code)\n";
    }
}
echo "\n";

// Determine Old and New Codes for Action
$targetOldCode = $forcedOldCode !== '' ? $forcedOldCode : (!empty($detectedOldCodes) ? $detectedOldCodes[0] : '');
$targetNewCode = $forcedNewCode !== '' ? $forcedNewCode : $currentCode;

if ($action === 'inspect') {
    echo "💡 Recommendations:\n";
    if ($targetOldCode !== '' && $targetOldCode !== $targetNewCode) {
        echo "  1. Multi-Lineage Restore: To allow the member to toggle and view their previous tree in the Organogram:\n";
        echo "     php scripts/recover_migrated_family_tree.php --user=\"{$userId}\" --action=restore_access\n\n";
        echo "  2. Tree Grafting: To merge all nodes & unions from #{$targetOldCode} into #{$targetNewCode}:\n";
        echo "     php scripts/recover_migrated_family_tree.php --user=\"{$userId}\" --old-code=\"{$targetOldCode}\" --new-code=\"{$targetNewCode}\" --action=graft_tree\n\n";
        echo "  3. Manual SQL Quick Fix (for Production Database Admin):\n";
        echo "     -- Option A: Enable dual tree viewing in Organogram\n";
        echo "     UPDATE user_families SET status = 'approved' WHERE user_id = '{$userId}' AND family_code = '{$targetOldCode}';\n\n";
        echo "     -- Option B: Graft nodes to new family code\n";
        echo "     UPDATE family_nodes SET family_code = '{$targetNewCode}' WHERE family_code = '{$targetOldCode}' AND user_id != '{$userId}';\n";
        echo "     UPDATE family_unions SET family_code = '{$targetNewCode}' WHERE family_code = '{$targetOldCode}';\n";
    } else {
        echo "  No distinct previous family code detected automatically. Specify with --old-code=\"PREV_CODE\" and --new-code=\"NEW_CODE\".\n";
    }
    exit(0);
}

// ACTION: Restore Access
if ($action === 'restore_access') {
    if ($targetOldCode === '') {
        echo "❌ Cannot restore access: No previous family code found. Specify with --old-code=\"CODE\".\n";
        exit(1);
    }

    echo "⚙️  Restoring approved access to family code #{$targetOldCode} in user_families...\n";
    if ($dryRun) {
        echo "   [DRY-RUN] Would execute: INSERT INTO user_families (user_id, family_code, status, role) VALUES ('{$userId}', '{$targetOldCode}', 'approved', 'member') ON DUPLICATE KEY UPDATE status = 'approved'\n";
    } else {
        $stmtIns = $db->prepare("
            INSERT INTO user_families (user_id, family_code, status, role)
            VALUES (?, ?, 'approved', 'member')
            ON DUPLICATE KEY UPDATE status = 'approved'
        ");
        $stmtIns->execute([$userId, $targetOldCode]);
        echo "   ✅ Successfully restored! Member {$userId} now has approved access to #{$targetOldCode} in their Organogram lineage selector.\n";
    }
    exit(0);
}

// ACTION: Graft Tree
if ($action === 'graft_tree') {
    if ($targetOldCode === '' || $targetNewCode === '' || $targetOldCode === $targetNewCode) {
        echo "❌ Tree grafting requires distinct --old-code and --new-code.\n";
        exit(1);
    }

    echo "🌳 Initiating tree grafting from #{$targetOldCode} into #{$targetNewCode}...\n";

    // Find old root node
    $stmtOldR = $db->prepare("SELECT * FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
    $stmtOldR->execute([$targetOldCode, $userId]);
    $oldRoot = $stmtOldR->fetch(PDO::FETCH_ASSOC);

    // Find new root node
    $stmtNewR = $db->prepare("SELECT * FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
    $stmtNewR->execute([$targetNewCode, $userId]);
    $newRoot = $stmtNewR->fetch(PDO::FETCH_ASSOC);

    if (!$oldRoot) {
        echo "⚠️  No root node found with user_id = '{$userId}' under #{$targetOldCode}. Checking if any nodes exist...\n";
    }

    $oldRootId = $oldRoot ? (int)$oldRoot['id'] : 0;
    $newRootId = $newRoot ? (int)$newRoot['id'] : 0;

    echo "   Old Root Node: " . ($oldRootId > 0 ? "Node #{$oldRootId}" : "None") . "\n";
    echo "   New Root Node: " . ($newRootId > 0 ? "Node #{$newRootId}" : "None") . "\n";

    if ($dryRun) {
        echo "\n[DRY-RUN MODE] The following atomic database updates would be applied:\n";
        echo "   1. Re-point unions where partner_1_id = {$oldRootId} to {$newRootId}.\n";
        echo "   2. Re-point unions where partner_2_id = {$oldRootId} to {$newRootId}.\n";
        echo "   3. Re-point children where parent_node_id = {$oldRootId} to {$newRootId}.\n";
        echo "   4. Re-point children where child_id = {$oldRootId} to {$newRootId}.\n";
        echo "   5. Merge biographical fields from Node #{$oldRootId} to Node #{$newRootId}.\n";
        echo "   6. Delete redundant old root Node #{$oldRootId}.\n";
        echo "   7. Update family_code = '{$targetNewCode}' for remaining nodes from #{$targetOldCode}.\n";
        echo "   8. Update family_code = '{$targetNewCode}' for unions from #{$targetOldCode}.\n";
        echo "   9. Ensure user_families keeps 'approved' status for both codes.\n";
        echo "\nRun without --dry-run to commit these updates.\n";
        exit(0);
    }

    $db->beginTransaction();
    try {
        if ($oldRootId > 0 && $newRootId > 0 && $oldRootId !== $newRootId) {
            // Re-point unions
            $stmtU1 = $db->prepare("UPDATE family_unions SET partner_1_id = ? WHERE partner_1_id = ?");
            $stmtU1->execute([$newRootId, $oldRootId]);

            $stmtU2 = $db->prepare("UPDATE family_unions SET partner_2_id = ? WHERE partner_2_id = ?");
            $stmtU2->execute([$newRootId, $oldRootId]);

            // Re-point children
            $stmtC1 = $db->prepare("UPDATE family_node_children SET parent_node_id = ? WHERE parent_node_id = ?");
            $stmtC1->execute([$newRootId, $oldRootId]);

            $stmtC2 = $db->prepare("UPDATE family_node_children SET child_id = ? WHERE child_id = ?");
            $stmtC2->execute([$newRootId, $oldRootId]);

            // Copy bio/data to new root if new root is blank
            $stmtSync = $db->prepare("
                UPDATE family_nodes n_new, family_nodes n_old
                SET n_new.bio = IF(n_new.bio IS NULL OR n_new.bio = '' OR n_new.bio = 'Primary Member', COALESCE(n_old.bio, n_new.bio), n_new.bio),
                    n_new.birth_date = COALESCE(n_new.birth_date, n_old.birth_date),
                    n_new.birth_year = COALESCE(n_new.birth_year, n_old.birth_year),
                    n_new.birth_place = COALESCE(NULLIF(n_new.birth_place, ''), n_old.birth_place),
                    n_new.occupation = COALESCE(NULLIF(n_new.occupation, ''), n_old.occupation),
                    n_new.location = COALESCE(NULLIF(n_new.location, ''), n_old.location),
                    n_new.voice_capsule_url = COALESCE(NULLIF(n_new.voice_capsule_url, ''), n_old.voice_capsule_url),
                    n_new.avatar_url = COALESCE(NULLIF(n_new.avatar_url, ''), n_old.avatar_url)
                WHERE n_new.id = ? AND n_old.id = ?
            ");
            $stmtSync->execute([$newRootId, $oldRootId]);

            // Delete redundant old root
            $stmtDel = $db->prepare("DELETE FROM family_nodes WHERE id = ?");
            $stmtDel->execute([$oldRootId]);
        }

        // Migrate all remaining nodes from old family code to new family code
        $stmtNodes = $db->prepare("UPDATE family_nodes SET family_code = ? WHERE family_code = ?");
        $stmtNodes->execute([$targetNewCode, $targetOldCode]);
        $migratedNodes = $stmtNodes->rowCount();

        // Migrate all unions from old family code to new family code
        $stmtUnions = $db->prepare("UPDATE family_unions SET family_code = ? WHERE family_code = ?");
        $stmtUnions->execute([$targetNewCode, $targetOldCode]);
        $migratedUnions = $stmtUnions->rowCount();

        // Ensure user_families has approved entries
        $stmtUf1 = $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member') ON DUPLICATE KEY UPDATE status = 'approved'");
        $stmtUf1->execute([$userId, $targetNewCode]);

        $stmtUf2 = $db->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member') ON DUPLICATE KEY UPDATE status = 'approved'");
        $stmtUf2->execute([$userId, $targetOldCode]);

        $db->commit();

        echo "🎉 Grafting complete!\n";
        echo "   - Migrated Nodes:  {$migratedNodes}\n";
        echo "   - Migrated Unions: {$migratedUnions}\n";
        echo "   - New Family Code: #{$targetNewCode}\n";
        echo "   - Member's tree is now fully visible and connected in their active Organogram.\n";
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        echo "❌ Grafting failed: " . $e->getMessage() . "\n";
        exit(1);
    }
    exit(0);
}

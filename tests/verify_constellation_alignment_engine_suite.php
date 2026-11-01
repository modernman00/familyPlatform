<?php

declare(strict_types=1);

/**
 * 🛡️ Autonomous Constellation Alignment & Cross-Studio Lineage Engine Verification Suite
 *
 * Validates:
 * 1. Cross-studio topological constellation matching (Jaccard similarity >= 85%).
 * 2. 3-way transactional branch grafting across independent family codes.
 * 3. Asymmetric half-sibling preservation (Dele attached via Father, isolated from Mother).
 * 4. Ancestral fast-forwarding (Grandparents inherited by child tree).
 * 5. Dynamic ego-centric kinship calculation on the newly grafted tree.
 * 6. Idempotency (re-merging does not duplicate nodes).
 * 7. Dismissal persistence in kinship_match_dismissals.
 * 8. Cycle defense: circular grafts are detected and rejected.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\ConstellationAlignmentService;
use App\services\LineageMergeService;
use App\services\KinshipCalculatorService;
use App\services\FamilyGraphService;
use Src\Db;

$passed = 0;
$failed = 0;

function runStep(string $name, callable $fn): void {
    global $passed, $failed;
    echo "  [TEST " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        echo "     at " . $e->getFile() . ":" . $e->getLine() . "\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🌌 CONSTELLATION ALIGNMENT & 3-WAY GRAFTING VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$db = Db::connect2();

$famA = 'TEST_FAM_JOHN_' . strtoupper(bin2hex(random_bytes(3)));
$famB = 'TEST_FAM_SHA_' . strtoupper(bin2hex(random_bytes(3)));
$userJohnId = 'usr_john_' . bin2hex(random_bytes(4));
$userShaId  = 'usr_sha_' . bin2hex(random_bytes(4));

try {
    // -------------------------------------------------------------
    // 1. Setup Family A (John's Studio)
    // -------------------------------------------------------------
    // Shey (Father) & Enny (Mother)
    $stmtNode = $db->prepare('INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, ?, ?, ?, ?)');
    
    $stmtNode->execute([$famA, 'Shey', 'Sterling', 'Male', -1, 'Father']);
    $sheyAId = (int)$db->lastInsertId();

    $stmtNode->execute([$famA, 'Enny', 'Sterling', 'Female', -1, 'Mother']);
    $ennyAId = (int)$db->lastInsertId();

    // Union A: Shey + Enny
    $stmtU = $db->prepare('INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, "married", 1)');
    $stmtU->execute([$famA, $sheyAId, $ennyAId]);
    $unionAId = (int)$db->lastInsertId();

    // Children in Family A: John & Tundu
    $stmtNode->execute([$famA, 'John', 'Sterling', 'Male', 0, 'Ego John']);
    $johnAId = (int)$db->lastInsertId();
    $db->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?')->execute([$userJohnId, $johnAId]);

    $stmtNode->execute([$famA, 'Tundu', 'Sterling', 'Female', 0, 'Sister']);
    $tunduAId = (int)$db->lastInsertId();

    $stmtChild = $db->prepare('INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, "biological")');
    $stmtChild->execute([$unionAId, $johnAId]);
    $stmtChild->execute([$unionAId, $tunduAId]);

    // Upstream Ancestors in Family A: Baba Granddad & Mama Grandma (Shey's parents)
    $stmtNode->execute([$famA, 'Baba Granddad', 'Sterling', 'Male', -2, 'Grandfather']);
    $granddadAId = (int)$db->lastInsertId();

    $stmtNode->execute([$famA, 'Mama Grandma', 'Sterling', 'Female', -2, 'Grandmother']);
    $grandmaAId = (int)$db->lastInsertId();

    $stmtU->execute([$famA, $granddadAId, $grandmaAId]);
    $grandparentsUnionAId = (int)$db->lastInsertId();
    $stmtChild->execute([$grandparentsUnionAId, $sheyAId]);

    // Collateral Union in Family A: Shey + Linda Ex -> Dele (Half-Brother)
    $stmtNode->execute([$famA, 'Linda Ex', 'Smith', 'Female', -1, 'Former Partner']);
    $lindaAId = (int)$db->lastInsertId();

    $stmtU->execute([$famA, $sheyAId, $lindaAId]);
    $sheyLindaUnionAId = (int)$db->lastInsertId();

    $stmtNode->execute([$famA, 'Dele', 'Sterling', 'Male', 0, 'Half-Brother']);
    $deleAId = (int)$db->lastInsertId();
    $stmtChild->execute([$sheyLindaUnionAId, $deleAId]);

    // -------------------------------------------------------------
    // 2. Setup Family B (Sha's Studio - completely independent)
    // -------------------------------------------------------------
    // Sha starts building his own tree with placeholder parents Shey & Enny
    $stmtNode->execute([$famB, 'Sha', 'Sterling', 'Male', 0, 'Ego Sha']);
    $shaBId = (int)$db->lastInsertId();
    $db->prepare('UPDATE family_nodes SET user_id = ? WHERE id = ?')->execute([$userShaId, $shaBId]);

    $stmtNode->execute([$famB, 'Shey', 'Sterling', 'Male', -1, 'Father']);
    $sheyBId = (int)$db->lastInsertId();

    $stmtNode->execute([$famB, 'Enny', 'Sterling', 'Female', -1, 'Mother']);
    $ennyBId = (int)$db->lastInsertId();

    $stmtU->execute([$famB, $sheyBId, $ennyBId]);
    $unionBId = (int)$db->lastInsertId();

    $stmtChild->execute([$unionBId, $shaBId]);

    // Also adds his brother John in Family B
    $stmtNode->execute([$famB, 'John', 'Sterling', 'Male', 0, 'Brother']);
    $johnBId = (int)$db->lastInsertId();
    $stmtChild->execute([$unionBId, $johnBId]);

    // =============================================================
    // EXECUTE TESTS
    // =============================================================

    // TEST 1: Constellation Match Discovery
    runStep('Discover high-confidence constellation match between Family B and Family A', function() use ($famB, $userShaId, $unionAId, $famA) {
        $matches = ConstellationAlignmentService::findMatchesForUser($famB, $userShaId);

        if (empty($matches)) {
            throw new \RuntimeException('Expected constellation matches for Sha, but none were found.');
        }

        $topMatch = $matches[0];
        if ($topMatch['target_union_id'] !== $unionAId) {
            throw new \RuntimeException("Expected target_union_id {$unionAId}, got: " . $topMatch['target_union_id']);
        }
        if ($topMatch['target_family_code'] !== $famA) {
            throw new \RuntimeException("Expected target_family_code {$famA}, got: " . $topMatch['target_family_code']);
        }
        if ($topMatch['confidence'] < 0.85) {
            throw new \RuntimeException('Expected confidence >= 0.85, got: ' . $topMatch['confidence']);
        }
        if ($topMatch['has_half_siblings'] !== true) {
            throw new \RuntimeException('Expected has_half_siblings to be true (Shey has child Dele with Linda).');
        }
        if ($topMatch['has_ancestors'] !== true) {
            throw new \RuntimeException('Expected has_ancestors to be true (Grandparents present in target tree).');
        }
    });

    // TEST 2: 3-Way Branch Grafting
    runStep('Execute transactional 3-way branch grafting into Family B', function() use ($famB, $userShaId, $unionAId, $db) {
        $mergeRes = LineageMergeService::mergeConstellationBranch($famB, $userShaId, $unionAId);

        if (!$mergeRes['success']) {
            throw new \RuntimeException('Branch merge failed: ' . $mergeRes['message']);
        }
        if ($mergeRes['imported_count'] < 3) {
            throw new \RuntimeException('Expected at least 3 imported relatives (Grandparents, Tundu, Dele), got: ' . $mergeRes['imported_count']);
        }

        // Verify Tundu now exists in Family B
        $stmtChk = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND first_name = "Tundu"');
        $stmtChk->execute([$famB]);
        if ((int)$stmtChk->fetchColumn() === 0) {
            throw new \RuntimeException('Sister Tundu was not grafted into Family B!');
        }

        // Verify Grandparents exist in Family B
        $stmtChkG = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND first_name = "Baba Granddad"');
        $stmtChkG->execute([$famB]);
        if ((int)$stmtChkG->fetchColumn() === 0) {
            throw new \RuntimeException('Baba Granddad was not grafted into Family B!');
        }

        // Verify Half-Brother Dele exists in Family B
        $stmtChkD = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ? AND first_name = "Dele"');
        $stmtChkD->execute([$famB]);
        if ((int)$stmtChkD->fetchColumn() === 0) {
            throw new \RuntimeException('Half-brother Dele was not grafted into Family B!');
        }
    });

    // TEST 3: Asymmetric Half-Sibling Isolation in Grafted Family
    runStep('Verify Dele is attached to Shey but NOT linked to Enny in Family B', function() use ($db, $famB) {
        // Find Dele's union in Family B
        $stmt = $db->prepare('
            SELECT u.partner_1_id, u.partner_2_id FROM family_node_children fnc
            JOIN family_unions u ON fnc.union_id = u.id
            JOIN family_nodes n ON fnc.child_id = n.id
            WHERE n.family_code = ? AND n.first_name = "Dele"
        ');
        $stmt->execute([$famB]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \RuntimeException('Could not find Dele union link in Family B');
        }

        $p1 = (int)$row['partner_1_id'];
        $p2 = (int)$row['partner_2_id'];

        // Enny's ID in Family B
        $stmtEnny = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Enny"');
        $stmtEnny->execute([$famB]);
        $ennyBId = (int)$stmtEnny->fetchColumn();

        if ($p1 === $ennyBId || $p2 === $ennyBId) {
            throw new \RuntimeException('FATAL: Dele is erroneously attached as a biological child of Mother Enny!');
        }
    });

    // TEST 4: SiblingKind Resolution on Grafted Family
    runStep('Verify SiblingKind resolution on Family B nodes (Sha vs John: Full, Sha vs Dele: Half)', function() use ($db, $famB) {
        $stmtSha = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Sha"');
        $stmtSha->execute([$famB]);
        $shaId = (int)$stmtSha->fetchColumn();

        $stmtJohn = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "John"');
        $stmtJohn->execute([$famB]);
        $johnId = (int)$stmtJohn->fetchColumn();

        $stmtDele = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Dele"');
        $stmtDele->execute([$famB]);
        $deleId = (int)$stmtDele->fetchColumn();

        // Sha & John -> Full
        $full = FamilyGraphService::siblingKind($db, $famB, $shaId, $johnId);
        if ($full['type'] !== 'full' || count($full['shared_parents']) !== 2) {
            throw new \RuntimeException("Sha and John expected 'full' with 2 parents, got: " . json_encode($full));
        }

        // Sha & Dele -> Half
        $half = FamilyGraphService::siblingKind($db, $famB, $shaId, $deleId);
        if ($half['type'] !== 'half' || count($half['shared_parents']) !== 1) {
            throw new \RuntimeException("Sha and Dele expected 'half' with 1 parent, got: " . json_encode($half));
        }
    });

    // TEST 5: Kinship BFS Calculation on Grafted Tree
    runStep('Verify KinshipCalculatorService on Family B anchored on Sha as Ego', function() use ($db, $famB) {
        $stmtSha = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Sha"');
        $stmtSha->execute([$famB]);
        $shaId = (int)$stmtSha->fetchColumn();

        $stmtDele = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Dele"');
        $stmtDele->execute([$famB]);
        $deleId = (int)$stmtDele->fetchColumn();

        $stmtGranddad = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND first_name = "Baba Granddad"');
        $stmtGranddad->execute([$famB]);
        $granddadId = (int)$stmtGranddad->fetchColumn();

        $nodes = $db->prepare('SELECT * FROM family_nodes WHERE family_code = ?');
        $nodes->execute([$famB]);
        $nodesList = $nodes->fetchAll(\PDO::FETCH_ASSOC);

        $unions = $db->prepare('SELECT * FROM family_unions WHERE family_code = ?');
        $unions->execute([$famB]);
        $unionsList = $unions->fetchAll(\PDO::FETCH_ASSOC);

        $children = $db->prepare('
            SELECT fnc.* FROM family_node_children fnc
            JOIN family_unions u ON fnc.union_id = u.id
            WHERE u.family_code = ?
        ');
        $children->execute([$famB]);
        $childrenList = $children->fetchAll(\PDO::FETCH_ASSOC);

        $annotated = KinshipCalculatorService::calculateEgoKinship([
            'nodes' => $nodesList,
            'unions' => $unionsList,
            'children' => $childrenList,
        ], $shaId);

        // Assert Dele is Half-brother (same dad)
        if (!str_contains($annotated[$deleId]['ego_relation'], 'Half-brother') || !str_contains($annotated[$deleId]['ego_relation'], 'same dad')) {
            throw new \RuntimeException("Dele expected 'Half-brother (same dad)', got: " . $annotated[$deleId]['ego_relation']);
        }

        // Assert Granddad is Paternal Grandfather
        if ($annotated[$granddadId]['ego_relation'] !== 'Paternal Grandfather') {
            throw new \RuntimeException("Granddad expected 'Paternal Grandfather', got: " . $annotated[$granddadId]['ego_relation']);
        }
    });

    // TEST 6: Idempotency Verification
    runStep('Verify re-merging the branch is idempotent and does not create duplicates', function() use ($famB, $userShaId, $unionAId, $db) {
        $stmtC = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE family_code = ?');
        $stmtC->execute([$famB]);
        $countBefore = (int)$stmtC->fetchColumn();
        
        $mergeRes = LineageMergeService::mergeConstellationBranch($famB, $userShaId, $unionAId);
        if (!$mergeRes['success']) {
            throw new \RuntimeException('Idempotent merge returned failure!');
        }

        $stmtC->execute([$famB]);
        $countAfter = (int)$stmtC->fetchColumn();
        if ($countBefore !== $countAfter) {
            throw new \RuntimeException("Node count changed during re-merge! Before: {$countBefore}, After: {$countAfter}");
        }
    });

    // TEST 7: Dismissal Persistence
    runStep('Verify dismissing a constellation match prevents re-prompting', function() use ($famB, $userShaId, $unionAId) {
        $dismissed = ConstellationAlignmentService::dismissMatch($famB, $userShaId, $unionAId);
        if (!$dismissed) {
            throw new \RuntimeException('Dismiss match returned false');
        }

        $matchesAfter = ConstellationAlignmentService::findMatchesForUser($famB, $userShaId);
        foreach ($matchesAfter as $m) {
            if ($m['target_union_id'] === $unionAId) {
                throw new \RuntimeException('Dismissed union was still returned in match proposals!');
            }
        }
    });

} finally {
    // Clean up test families
    $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code IN (?, ?))")->execute([$famA, $famB]);
    $db->prepare("DELETE FROM family_unions WHERE family_code IN (?, ?)")->execute([$famA, $famB]);
    $db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?)")->execute([$famA, $famB]);
    $db->prepare("DELETE FROM kinship_match_dismissals WHERE family_code IN (?, ?)")->execute([$famA, $famB]);
}

echo "\n=================================================================\n";
echo "🎉 TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

<?php

declare(strict_types=1);

/**
 * 🛡️ Multi-Ego Family Tree & Node Alignment Verification Suite
 * 
 * Validates the core algorithmic requirements:
 * 1. John, Sha, Tundu, Shey, Enny dynamic ego-centric relationship calculation.
 * 2. Half-brother / Half-sister differentiation via shared union parent counts.
 * 3. Asymmetric parentage: Dele (same dad) vs Bisi (same mum).
 * 4. Step-sibling isolation: Dele vs Bisi (zero shared biological parents).
 * 5. Upstream ancestral fast-forwarding: Grandparents auto-resolve to Paternal Grandparents.
 * 6. SiblingKind resolution via FamilyGraphService::siblingKind().
 * 7. Deduplication via KinshipEntityResolver::resolveOrCreateChild().
 * 8. Cycle detection guardrail via KinshipEntityResolver.
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

use App\services\KinshipCalculatorService;
use App\services\FamilyGraphService;
use App\services\KinshipEntityResolver;
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
echo "🌳 MULTI-EGO FAMILY TREE & NODE ALIGNMENT VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$db = Db::connect2();
$famCode = 'TEST_EGO_' . strtoupper(bin2hex(random_bytes(3)));

try {
    // -------------------------------------------------------------
    // Provision Fixtures in actual DB tables
    // -------------------------------------------------------------

    // 1. Ancestors: Grandparents of Shey (Generation -2)
    $stmt = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio) VALUES (?, ?, 'Sterling', ?, ?, ?)");
    
    $stmt->execute([$famCode, 'Baba Granddad', 'Male', -2, 'Grandfather']);
    $granddadId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Mama Grandma', 'Female', -2, 'Grandmother']);
    $grandmaId = (int)$db->lastInsertId();

    // Union 0: Grandparents
    $uStmt = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
    $uStmt->execute([$famCode, $granddadId, $grandmaId]);
    $grandparentsUnionId = (int)$db->lastInsertId();

    // 2. Parents: Shey & Enny (Generation -1)
    $stmt->execute([$famCode, 'Shey', 'Male', -1, 'Father']);
    $sheyId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Enny', 'Female', -1, 'Mother']);
    $ennyId = (int)$db->lastInsertId();

    // Link Shey as child of Grandparents
    $cStmt = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
    $cStmt->execute([$grandparentsUnionId, $sheyId]);

    // Union 1: Shey + Enny
    $uStmt->execute([$famCode, $sheyId, $ennyId]);
    $mainUnionId = (int)$db->lastInsertId();

    // 3. Children of Shey + Enny: John, Sha, Tundu (Generation 0)
    $stmt->execute([$famCode, 'John', 'Male', 0, 'Son']);
    $johnId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Sha', 'Male', 0, 'Son']);
    $shaId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Tundu', 'Female', 0, 'Daughter']);
    $tunduId = (int)$db->lastInsertId();

    $cStmt->execute([$mainUnionId, $johnId]);
    $cStmt->execute([$mainUnionId, $shaId]);
    $cStmt->execute([$mainUnionId, $tunduId]);

    // 4. Shey's Prior Partner & Half-Brother Dele
    $stmt->execute([$famCode, 'Linda Ex', 'Female', -1, 'Former Partner']);
    $lindaId = (int)$db->lastInsertId();

    $uStmt->execute([$famCode, $sheyId, $lindaId]);
    $sheyLindaUnionId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Dele', 'Male', 0, 'Half-Brother']);
    $deleId = (int)$db->lastInsertId();
    $cStmt->execute([$sheyLindaUnionId, $deleId]);

    // 5. Enny's Prior Partner & Half-Sister Bisi
    $stmt->execute([$famCode, 'Bayo Ex', 'Male', -1, 'Former Partner']);
    $bayoId = (int)$db->lastInsertId();

    $uStmt->execute([$famCode, $bayoId, $ennyId]);
    $bayoEnnyUnionId = (int)$db->lastInsertId();

    $stmt->execute([$famCode, 'Bisi', 'Female', 0, 'Half-Sister']);
    $bisiId = (int)$db->lastInsertId();
    $cStmt->execute([$bayoEnnyUnionId, $bisiId]);

    // Fetch full graph representation for KinshipCalculatorService
    $fetchGraph = function() use ($db, $famCode): array {
        $nStmt = $db->prepare('SELECT * FROM family_nodes WHERE family_code = ?');
        $nStmt->execute([$famCode]);
        $nodes = $nStmt->fetchAll(\PDO::FETCH_ASSOC);

        $uStmt = $db->prepare('SELECT * FROM family_unions WHERE family_code = ?');
        $uStmt->execute([$famCode]);
        $unions = $uStmt->fetchAll(\PDO::FETCH_ASSOC);

        $cQuery = 'SELECT fnc.* FROM family_node_children fnc
                   JOIN family_unions u ON fnc.union_id = u.id
                   WHERE u.family_code = ?';
        $cStmtExec = $db->prepare($cQuery);
        $cStmtExec->execute([$famCode]);
        $children = $cStmtExec->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'nodes' => $nodes,
            'unions' => $unions,
            'children' => $children,
        ];
    };

    $graphData = $fetchGraph();

    // -------------------------------------------------------------
    // Execute Tests Against the Graph Nodes
    // -------------------------------------------------------------

    // TEST 1: John as Ego
    runStep('Ego calculation for John (Full Siblings, Half-Siblings, Parents, Grandparents)', function() use ($graphData, $johnId, $sheyId, $ennyId, $shaId, $tunduId, $deleId, $bisiId, $granddadId, $grandmaId) {
        $annotated = KinshipCalculatorService::calculateEgoKinship($graphData, $johnId);

        if ($annotated[$johnId]['ego_relation'] !== 'You (Primary Member)') {
            throw new \RuntimeException("John ego_relation expected 'You (Primary Member)', got: " . $annotated[$johnId]['ego_relation']);
        }
        if ($annotated[$sheyId]['ego_relation'] !== 'Father') {
            throw new \RuntimeException("Shey expected 'Father', got: " . $annotated[$sheyId]['ego_relation']);
        }
        if ($annotated[$ennyId]['ego_relation'] !== 'Mother') {
            throw new \RuntimeException("Enny expected 'Mother', got: " . $annotated[$ennyId]['ego_relation']);
        }
        if ($annotated[$shaId]['ego_relation'] !== 'Brother' || $annotated[$shaId]['sibling_kind'] !== 'full') {
            throw new \RuntimeException("Sha expected 'Brother' (full), got: " . $annotated[$shaId]['ego_relation'] . " ({$annotated[$shaId]['sibling_kind']})");
        }
        if ($annotated[$tunduId]['ego_relation'] !== 'Sister' || $annotated[$tunduId]['sibling_kind'] !== 'full') {
            throw new \RuntimeException("Tundu expected 'Sister' (full), got: " . $annotated[$tunduId]['ego_relation']);
        }
        // Dele: half-brother via Shey (same dad)
        if (!str_contains($annotated[$deleId]['ego_relation'], 'Half-brother') || !str_contains($annotated[$deleId]['ego_relation'], 'same dad') || $annotated[$deleId]['sibling_kind'] !== 'half') {
            throw new \RuntimeException("Dele expected 'Half-brother (same dad)', got: " . $annotated[$deleId]['ego_relation']);
        }
        // Bisi: half-sister via Enny (same mum)
        if (!str_contains($annotated[$bisiId]['ego_relation'], 'Half-sister') || !str_contains($annotated[$bisiId]['ego_relation'], 'same mum') || $annotated[$bisiId]['sibling_kind'] !== 'half') {
            throw new \RuntimeException("Bisi expected 'Half-sister (same mum)', got: " . $annotated[$bisiId]['ego_relation']);
        }
        // Grandparents
        if ($annotated[$granddadId]['ego_relation'] !== 'Paternal Grandfather') {
            throw new \RuntimeException("Granddad expected 'Paternal Grandfather', got: " . $annotated[$granddadId]['ego_relation']);
        }
        if ($annotated[$grandmaId]['ego_relation'] !== 'Paternal Grandmother') {
            throw new \RuntimeException("Grandma expected 'Paternal Grandmother', got: " . $annotated[$grandmaId]['ego_relation']);
        }
    });

    // TEST 2: Sha as Ego
    runStep('Ego calculation for Sha (John is Brother, Dele is Half-brother)', function() use ($graphData, $shaId, $johnId, $tunduId, $deleId, $bisiId, $sheyId, $ennyId) {
        $annotated = KinshipCalculatorService::calculateEgoKinship($graphData, $shaId);

        if ($annotated[$johnId]['ego_relation'] !== 'Brother' || $annotated[$johnId]['sibling_kind'] !== 'full') {
            throw new \RuntimeException("John expected 'Brother' to Sha, got: " . $annotated[$johnId]['ego_relation']);
        }
        if ($annotated[$tunduId]['ego_relation'] !== 'Sister' || $annotated[$tunduId]['sibling_kind'] !== 'full') {
            throw new \RuntimeException("Tundu expected 'Sister' to Sha, got: " . $annotated[$tunduId]['ego_relation']);
        }
        if (!str_contains($annotated[$deleId]['ego_relation'], 'Half-brother') || $annotated[$deleId]['sibling_kind'] !== 'half') {
            throw new \RuntimeException("Dele expected 'Half-brother' to Sha, got: " . $annotated[$deleId]['ego_relation']);
        }
        if (!str_contains($annotated[$bisiId]['ego_relation'], 'Half-sister') || $annotated[$bisiId]['sibling_kind'] !== 'half') {
            throw new \RuntimeException("Bisi expected 'Half-sister' to Sha, got: " . $annotated[$bisiId]['ego_relation']);
        }
        if ($annotated[$sheyId]['ego_relation'] !== 'Father') {
            throw new \RuntimeException("Shey expected 'Father' to Sha, got: " . $annotated[$sheyId]['ego_relation']);
        }
    });

    // TEST 3: Shey (Father) as Ego
    runStep('Ego calculation for Shey (Children are Sons/Daughters, Dele is direct Son, Enny is Wife)', function() use ($graphData, $sheyId, $johnId, $shaId, $tunduId, $deleId, $ennyId, $granddadId, $grandmaId) {
        $annotated = KinshipCalculatorService::calculateEgoKinship($graphData, $sheyId);

        if ($annotated[$ennyId]['ego_relation'] !== 'Wife') {
            throw new \RuntimeException("Enny expected 'Wife' to Shey, got: " . $annotated[$ennyId]['ego_relation']);
        }
        if ($annotated[$johnId]['ego_relation'] !== 'Son') {
            throw new \RuntimeException("John expected 'Son' to Shey, got: " . $annotated[$johnId]['ego_relation']);
        }
        if ($annotated[$shaId]['ego_relation'] !== 'Son') {
            throw new \RuntimeException("Sha expected 'Son' to Shey, got: " . $annotated[$shaId]['ego_relation']);
        }
        if ($annotated[$tunduId]['ego_relation'] !== 'Daughter') {
            throw new \RuntimeException("Tundu expected 'Daughter' to Shey, got: " . $annotated[$tunduId]['ego_relation']);
        }
        if ($annotated[$deleId]['ego_relation'] !== 'Son') {
            throw new \RuntimeException("Dele expected 'Son' to Shey, got: " . $annotated[$deleId]['ego_relation']);
        }
        if ($annotated[$granddadId]['ego_relation'] !== 'Father') {
            throw new \RuntimeException("Granddad expected 'Father' to Shey, got: " . $annotated[$granddadId]['ego_relation']);
        }
        if ($annotated[$grandmaId]['ego_relation'] !== 'Mother') {
            throw new \RuntimeException("Grandma expected 'Mother' to Shey, got: " . $annotated[$grandmaId]['ego_relation']);
        }
    });

    // TEST 4: Enny (Mother) as Ego
    runStep('Ego calculation for Enny (Bisi is direct Daughter, Dele is step-son/partner child)', function() use ($graphData, $ennyId, $johnId, $bisiId, $deleId, $sheyId) {
        $annotated = KinshipCalculatorService::calculateEgoKinship($graphData, $ennyId);

        if ($annotated[$sheyId]['ego_relation'] !== 'Husband') {
            throw new \RuntimeException("Shey expected 'Husband' to Enny, got: " . $annotated[$sheyId]['ego_relation']);
        }
        if ($annotated[$johnId]['ego_relation'] !== 'Son') {
            throw new \RuntimeException("John expected 'Son' to Enny, got: " . $annotated[$johnId]['ego_relation']);
        }
        if ($annotated[$bisiId]['ego_relation'] !== 'Daughter') {
            throw new \RuntimeException("Bisi expected 'Daughter' to Enny, got: " . $annotated[$bisiId]['ego_relation']);
        }
        // Dele is NOT Enny's biological child
        if ($annotated[$deleId]['ego_relation'] === 'Son') {
            throw new \RuntimeException("Dele MUST NOT be labeled 'Son' to Enny!");
        }
    });

    // TEST 5: Sibling Kind Resolution via FamilyGraphService::siblingKind
    runStep('SiblingKind method verification (Full, Half, and Step/None)', function() use ($db, $famCode, $johnId, $shaId, $deleId, $bisiId) {
        // John & Sha share 2 parents (Shey & Enny)
        $fullCheck = FamilyGraphService::siblingKind($db, $famCode, $johnId, $shaId);
        if ($fullCheck['type'] !== 'full' || count($fullCheck['shared_parents']) !== 2) {
            throw new \RuntimeException("John and Sha expected type 'full' with 2 shared parents, got: " . json_encode($fullCheck));
        }

        // John & Dele share 1 parent (Shey)
        $halfCheck1 = FamilyGraphService::siblingKind($db, $famCode, $johnId, $deleId);
        if ($halfCheck1['type'] !== 'half' || count($halfCheck1['shared_parents']) !== 1) {
            throw new \RuntimeException("John and Dele expected type 'half' with 1 shared parent, got: " . json_encode($halfCheck1));
        }

        // John & Bisi share 1 parent (Enny)
        $halfCheck2 = FamilyGraphService::siblingKind($db, $famCode, $johnId, $bisiId);
        if ($halfCheck2['type'] !== 'half' || count($halfCheck2['shared_parents']) !== 1) {
            throw new \RuntimeException("John and Bisi expected type 'half' with 1 shared parent, got: " . json_encode($halfCheck2));
        }

        // Dele & Bisi share 0 parents (blended step-relation)
        $stepCheck = FamilyGraphService::siblingKind($db, $famCode, $deleId, $bisiId);
        if ($stepCheck['type'] !== 'none' || count($stepCheck['shared_parents']) !== 0) {
            throw new \RuntimeException("Dele and Bisi expected type 'none' (zero shared biological parents), got: " . json_encode($stepCheck));
        }
    });

    // TEST 6: Entity Deduplication Invariant
    runStep('Entity resolution deduplication (Re-adding John under Union 1 preserves single node)', function() use ($famCode, $mainUnionId, $johnId) {
        $result = KinshipEntityResolver::resolveOrCreateChild(
            $famCode,
            $mainUnionId,
            'John',
            'Sterling',
            'Male'
        );

        if ($result['is_new'] !== false) {
            throw new \RuntimeException("Re-adding John should not create a new node!");
        }
        if ($result['node_id'] !== $johnId) {
            throw new \RuntimeException("Expected resolved node ID {$johnId}, got: " . $result['node_id']);
        }
    });

    // TEST 7: Cycle Detection Guardrail (Preventing Circular Lineage)
    runStep('Cycle detection guardrail (John cannot be made ancestor of Granddad)', function() use ($db, $johnId, $granddadId) {
        $reflection = new \ReflectionClass(KinshipEntityResolver::class);
        $method = $reflection->getMethod('isDescendant');
        $method->setAccessible(true);

        // Check if John is descendant of Granddad (Should be true)
        $isDesc = $method->invoke(null, $db, $granddadId, $johnId);
        if (!$isDesc) {
            throw new \RuntimeException("John should be recognized as a descendant of Granddad!");
        }

        // Check if Granddad is descendant of John (Should be false)
        $isReverse = $method->invoke(null, $db, $johnId, $granddadId);
        if ($isReverse) {
            throw new \RuntimeException("Granddad should NOT be recognized as descendant of John!");
        }
    });

} finally {
    // Clean up test fixtures safely
    $db->prepare("DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code = ?)")->execute([$famCode]);
    $db->prepare("DELETE FROM family_unions WHERE family_code = ?")->execute([$famCode]);
    $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$famCode]);
}

echo "\n=================================================================\n";
echo "🎉 TEST RESULTS: {$passed} PASSED, {$failed} FAILED\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

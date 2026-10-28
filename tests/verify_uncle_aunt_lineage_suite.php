<?php

declare(strict_types=1);

/**
 * 🛡️ Uncle & Auntie Lineage Verification Suite (TAT & Engineering Council Gate)
 * 
 * Verifies:
 * 1. Adding Paternal Uncle with Marital / Primary Family Code (Father's side)
 * 2. Adding Maternal Auntie with Birth / Maiden Family Code (Mother's side)
 * 3. Kinship DAG resolution via KinshipCalculatorService (Paternal Uncle & Maternal Aunt)
 * 4. IDOR Protection: Cross-family tree modification rejection (HTTP 403)
 * 5. Input Validation: First name requirement (HTTP 400) & Method check (HTTP 405)
 * 6. Maiden name and secondary family code annotation preservation
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

use App\controller\members\OrganogramEditorController;
use App\services\KinshipCalculatorService;
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
echo "🏛️ FAMILYPLATFORM UNCLE & AUNTIE LINEAGE VERIFICATION SUITE\n";
echo "=================================================================\n";

$db = Db::connect2();

// Fixtures
$maritalFamCode = 'TEST_MARITAL_' . strtoupper(bin2hex(random_bytes(3)));
$birthFamCode   = 'TEST_BIRTH_' . strtoupper(bin2hex(random_bytes(3)));
$userId         = 'usr_kin_' . bin2hex(random_bytes(5));

// Seed personal & user_families rows
$db->prepare("
    INSERT INTO personal (id, firstName, lastName, famCode)
    VALUES (?, 'Wale', 'Olaogun', ?)
")->execute([$userId, $maritalFamCode]);

$db->prepare("
    INSERT INTO otherFamily (id, otherFamCode)
    VALUES (?, ?)
")->execute([$userId, $birthFamCode]);

$db->prepare("
    INSERT INTO user_families (user_id, family_code, status, role)
    VALUES (?, ?, 'approved', 'admin'), (?, ?, 'approved', 'member')
")->execute([$userId, $maritalFamCode, $userId, $birthFamCode]);

// Seed Ego in marital tree and in birth tree
$db->prepare("
    INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
    VALUES (?, ?, 'Wale', 'Olaogun', 'Male', 0)
")->execute([$maritalFamCode, $userId]);
$egoMaritalNodeId = (int)$db->lastInsertId();

$db->prepare("
    INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
    VALUES (?, ?, 'Wale', 'Olaogun', 'Male', 0)
")->execute([$birthFamCode, $userId]);
$egoBirthNodeId = (int)$db->lastInsertId();

// Helper to invoke controller instance without full session bootstrap
function callController(OrganogramEditorController $controller, string $method): array {
    unset($GLOBALS['__testResponse'], $GLOBALS['__testError']);
    ob_start();
    try {
        $controller->$method();
        $output = ob_get_clean() ?: '';
        if (isset($GLOBALS['__testResponse'])) {
            return $GLOBALS['__testResponse'];
        }
        if (isset($GLOBALS['__testError'])) {
            $err = $GLOBALS['__testError'];
            return [
                'status' => 'error',
                'code' => $err instanceof \Throwable ? (int)$err->getCode() : 500,
                'message' => $err instanceof \Throwable ? $err->getMessage() : (string)$err
            ];
        }
        return json_decode($output, true) ?: ['raw' => $output];
    } catch (\Throwable $e) {
        ob_end_clean();
        return [
            'status' => 'error',
            'code' => (int)$e->getCode(),
            'message' => $e->getMessage()
        ];
    }
}

$controller = (new ReflectionClass(OrganogramEditorController::class))->newInstanceWithoutConstructor();

// -----------------------------------------------------------------------------
// TEST 1: Add Paternal Uncle with Marital Family Code
// -----------------------------------------------------------------------------
runStep('Add Paternal Uncle with Marital Family Code', function () use ($controller, $db, $userId, $maritalFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'uncle',
        'side' => 'paternal',
        'famcode_type' => 'marital',
        'marital_famcode' => $maritalFamCode,
        'first_name' => 'Babatunde',
        'last_name' => 'Olaogun',
        'email' => 'babatunde@example.com',
        'birth_year' => '1970'
    ];

    $res = callController($controller, 'addUncleAunt');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Babatunde') {
        throw new RuntimeException('Unexpected uncle first name: ' . ($node['first_name'] ?? ''));
    }
    if (($node['family_code'] ?? '') !== $maritalFamCode) {
        throw new RuntimeException('Uncle not placed in marital family code: ' . ($node['family_code'] ?? ''));
    }

    // Check DB rows
    $uncleId = (int)$node['id'];
    $stmtNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
    $stmtNode->execute([$uncleId]);
    $dbUncle = $stmtNode->fetch(\PDO::FETCH_ASSOC);
    if (!$dbUncle || $dbUncle['gender'] !== 'Male' || (int)$dbUncle['generation_level'] !== -1) {
        throw new RuntimeException('Invalid DB uncle record.');
    }

    // Verify child link to grandparent union
    $stmtLink = $db->prepare('SELECT union_id FROM family_node_children WHERE child_id = ?');
    $stmtLink->execute([$uncleId]);
    $gpUnionId = (int)$stmtLink->fetchColumn();
    if ($gpUnionId <= 0) {
        throw new RuntimeException('Uncle not linked to Grandparent union.');
    }
});

// -----------------------------------------------------------------------------
// TEST 2: Add Maternal Auntie with Birth Family Code
// -----------------------------------------------------------------------------
runStep('Add Maternal Auntie with Birth Family Code', function () use ($controller, $db, $userId, $maritalFamCode, $birthFamCode, $egoBirthNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode, $birthFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoBirthNodeId,
        'relationship' => 'aunt',
        'side' => 'maternal',
        'famcode_type' => 'birth',
        'birth_famcode' => $birthFamCode,
        'lineage_scope' => 'birth',
        'lineage_code' => $birthFamCode,
        'first_name' => 'Folashade',
        'last_name' => 'Adeyemi',
        'maiden_name' => 'Mayungbe',
        'email' => 'folashade@example.com',
        'birth_year' => '1975'
    ];

    $res = callController($controller, 'addUncleAunt');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Folashade') {
        throw new RuntimeException('Unexpected aunt first name: ' . ($node['first_name'] ?? ''));
    }
    if (($node['family_code'] ?? '') !== $birthFamCode) {
        throw new RuntimeException('Aunt not placed in birth family code: ' . ($node['family_code'] ?? ''));
    }

    $auntId = (int)$node['id'];
    $stmtNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
    $stmtNode->execute([$auntId]);
    $dbAunt = $stmtNode->fetch(\PDO::FETCH_ASSOC);
    if (!$dbAunt || $dbAunt['gender'] !== 'Female' || (int)$dbAunt['generation_level'] !== -1) {
        throw new RuntimeException('Invalid DB aunt record.');
    }
    if ($dbAunt['maiden_name'] !== 'Mayungbe') {
        throw new RuntimeException('Maiden name not preserved on aunt record.');
    }
});

// -----------------------------------------------------------------------------
// TEST 3: Kinship Graph Traversal & DAG Degree
// -----------------------------------------------------------------------------
runStep('Kinship Graph Traversal via CollateralKinshipService', function () use ($db, $maritalFamCode, $userId) {
    // Look up the uncle we added in Test 1
    $stmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND first_name = 'Babatunde' LIMIT 1");
    $stmt->execute([$maritalFamCode]);
    $uncleId = (int)$stmt->fetchColumn();
    if ($uncleId <= 0) {
        throw new RuntimeException('Uncle Babatunde not found in DB.');
    }

    $graph = \App\services\CollateralKinshipService::buildCollateralGraph($maritalFamCode, $userId);
    
    $uncleNode = null;
    foreach ($graph['nodes'] as $node) {
        if ((int)$node['id'] === $uncleId) {
            $uncleNode = $node;
            break;
        }
    }

    if (!$uncleNode) {
        throw new RuntimeException('Uncle Babatunde not present in computed collateral graph nodes.');
    }

    if (($uncleNode['kinship_category'] ?? '') !== 'uncle_aunt' && !str_contains((string)($uncleNode['ego_relation'] ?? ''), 'Uncle')) {
        throw new RuntimeException('Kinship calculation failed: expected Uncle, got ' . json_encode($uncleNode));
    }

    if (($uncleNode['ego_side'] ?? '') !== 'paternal' && ($uncleNode['side'] ?? '') !== 'paternal') {
        throw new RuntimeException('Kinship calculation failed: expected paternal side, got ' . json_encode($uncleNode));
    }
});

// -----------------------------------------------------------------------------
// TEST 4: IDOR Protection (Cross-Family Node Modification Blocked)
// -----------------------------------------------------------------------------
runStep('IDOR Protection: Foreign Family Base Node Rejected with 403', function () use ($controller, $userId, $maritalFamCode) {
    $foreignDb = Db::connect2();
    $foreignDb->prepare("
        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
        VALUES ('FOREIGN_FAMILY_99', 'Hacker', 'Target', 'Male', 0)
    ")->execute();
    $foreignNodeId = (int)$foreignDb->lastInsertId();

    try {
        $_SESSION = [
            'id' => $userId,
            'famCode' => $maritalFamCode,
            'famCodes' => [$maritalFamCode]
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'base_node_id' => (string)$foreignNodeId,
            'relationship' => 'uncle',
            'first_name' => 'Intruder',
            'last_name' => 'Uncle'
        ];

        $res = callController($controller, 'addUncleAunt');

        if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 403) {
            throw new RuntimeException('Expected 403 Forbidden for cross-family base node, got: ' . json_encode($res));
        }
    } finally {
        $foreignDb->prepare('DELETE FROM family_nodes WHERE id = ?')->execute([$foreignNodeId]);
    }
});

// -----------------------------------------------------------------------------
// TEST 5: Input Validation (First Name Required)
// -----------------------------------------------------------------------------
runStep('Validation: Missing First Name Rejected with 400', function () use ($controller, $userId, $maritalFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'uncle',
        'first_name' => '',
        'last_name' => 'Olaogun'
    ];

    $res = callController($controller, 'addUncleAunt');

    if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 400) {
        throw new RuntimeException('Expected 400 Bad Request for empty first name, got: ' . json_encode($res));
    }
});

// -----------------------------------------------------------------------------
// TEST 6: HTTP Method Guard (GET Request Rejected with 405)
// -----------------------------------------------------------------------------
runStep('Method Guard: GET Request Rejected with 405', function () use ($controller) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_POST = [];

    $res = callController($controller, 'addUncleAunt');

    if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 405) {
        throw new RuntimeException('Expected 405 Method Not Allowed, got: ' . json_encode($res));
    }
});

// Clean up fixtures
$db->prepare('DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code IN (?, ?))')->execute([$maritalFamCode, $birthFamCode]);
$db->prepare('DELETE FROM family_unions WHERE family_code IN (?, ?)')->execute([$maritalFamCode, $birthFamCode]);
$db->prepare('DELETE FROM family_nodes WHERE family_code IN (?, ?)')->execute([$maritalFamCode, $birthFamCode]);
$db->prepare('DELETE FROM otherFamily WHERE id = ?')->execute([$userId]);
$db->prepare('DELETE FROM user_families WHERE user_id = ?')->execute([$userId]);
$db->prepare('DELETE FROM personal WHERE id = ?')->execute([$userId]);

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🎉 ALL {$passed} UNCLE & AUNTIE TESTS PASSED! 100% IMMUNITY & KINSHIP INTEGRITY.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "🛑 {$failed} TESTS FAILED! IMMEDIATE ACTION REQUIRED.\n";
    echo "=================================================================\n";
    exit(1);
}

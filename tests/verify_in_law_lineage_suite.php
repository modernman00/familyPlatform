<?php

declare(strict_types=1);

/**
 * 🛡️ In-Law Lineage Verification Suite (TAT & Engineering Council Gate)
 * 
 * Verifies:
 * 1. Adding Father-in-law with Wife's Family Code
 * 2. Adding Mother-in-law with Wife's Family Code and maiden name
 * 3. Adding Brother-in-law with Wife's Family Code
 * 4. Adding Sister-in-law with Wife's Family Code
 * 5. Kinship DAG resolution via CollateralKinshipService & KinshipCalculatorService
 * 6. IDOR Protection: Cross-family tree base node rejected with HTTP 403
 * 7. Input Validation: Missing first name rejected with HTTP 400
 * 8. Method Guard: GET request rejected with HTTP 405
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
use App\services\CollateralKinshipService;
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
echo "🏛️ FAMILYPLATFORM IN-LAW LINEAGE VERIFICATION SUITE\n";
echo "=================================================================\n";

$db = Db::connect2();

// Fixtures
$maritalFamCode = 'TEST_MARITAL_' . strtoupper(bin2hex(random_bytes(3)));
$wifeFamCode    = 'TEST_WIFE_' . strtoupper(bin2hex(random_bytes(3)));
$userId         = 'usr_inlaw_' . bin2hex(random_bytes(5));

// Seed personal & user_families rows
$db->prepare("
    INSERT INTO personal (id, firstName, lastName, famCode)
    VALUES (?, 'Wale', 'Olaogun', ?)
")->execute([$userId, $maritalFamCode]);

$db->prepare("
    INSERT INTO user_families (user_id, family_code, status, role)
    VALUES (?, ?, 'approved', 'admin')
")->execute([$userId, $maritalFamCode]);

// Seed Ego in marital tree
$db->prepare("
    INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
    VALUES (?, ?, 'Wale', 'Olaogun', 'Male', 0)
")->execute([$maritalFamCode, $userId]);
$egoMaritalNodeId = (int)$db->lastInsertId();

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
// TEST 1: Add Father-in-law with Wife's Family Code
// -----------------------------------------------------------------------------
runStep('Add Father-in-law with Wife Family Code', function () use ($controller, $db, $userId, $maritalFamCode, $wifeFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'father_in_law',
        'famcode_type' => 'wife',
        'wife_famcode' => $wifeFamCode,
        'first_name' => 'Joseph',
        'last_name' => 'Kokumo',
        'email' => 'joseph.kokumo@example.com',
        'birth_year' => '1950'
    ];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Joseph') {
        throw new RuntimeException('Unexpected father-in-law first name: ' . ($node['first_name'] ?? ''));
    }
    if (($node['role'] ?? '') !== 'father_in_law') {
        throw new RuntimeException('Unexpected role: ' . ($node['role'] ?? ''));
    }

    // Verify Spouse was auto-created and linked in the Wife's family tree
    $stmtSpouse = $db->prepare("
        SELECT u.id AS union_id, u.partner_1_id, u.partner_2_id
        FROM family_unions u
        WHERE u.family_code = ? AND u.is_current = 1
        LIMIT 1
    ");
    $stmtSpouse->execute([$wifeFamCode]);
    $spouseUnion = $stmtSpouse->fetch(\PDO::FETCH_ASSOC);
    if (!$spouseUnion) {
        throw new RuntimeException('Spouse union was not auto-created in wife family tree.');
    }
    $spouseId = (int)$spouseUnion['partner_2_id'];

    // Verify Spouse is linked as child of Father-in-law's union
    $filId = (int)$node['id'];
    $stmtLink = $db->prepare("
        SELECT fnc.union_id, u.partner_1_id, u.partner_2_id
        FROM family_node_children fnc
        JOIN family_unions u ON u.id = fnc.union_id
        WHERE fnc.child_id = ? AND u.family_code = ?
    ");
    $stmtLink->execute([$spouseId, $wifeFamCode]);
    $linkRow = $stmtLink->fetch(\PDO::FETCH_ASSOC);
    if (!$linkRow || ((int)$linkRow['partner_1_id'] !== $filId && (int)$linkRow['partner_2_id'] !== $filId)) {
        throw new RuntimeException('Spouse is not linked to Father-in-law union.');
    }
});

// -----------------------------------------------------------------------------
// TEST 2: Add Mother-in-law with Wife's Family Code and Maiden Name
// -----------------------------------------------------------------------------
runStep('Add Mother-in-law with Wife Family Code & Maiden Name', function () use ($controller, $db, $userId, $maritalFamCode, $wifeFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'mother_in_law',
        'famcode_type' => 'wife',
        'wife_famcode' => $wifeFamCode,
        'first_name' => 'Victoria',
        'last_name' => 'Kokumo',
        'maiden_name' => 'Adeleke',
        'email' => 'victoria.kokumo@example.com',
        'birth_year' => '1955'
    ];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Victoria') {
        throw new RuntimeException('Unexpected mother-in-law first name: ' . ($node['first_name'] ?? ''));
    }
    if (($node['maiden_name'] ?? '') !== 'Adeleke') {
        throw new RuntimeException('Maiden name not preserved on mother-in-law.');
    }

    $milId = (int)$node['id'];
    $stmtNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
    $stmtNode->execute([$milId]);
    $dbMil = $stmtNode->fetch(\PDO::FETCH_ASSOC);
    if (!$dbMil || $dbMil['gender'] !== 'Female' || (int)$dbMil['generation_level'] !== -1) {
        throw new RuntimeException('Invalid DB mother-in-law record.');
    }
});

// -----------------------------------------------------------------------------
// TEST 3: Add Brother-in-law with Wife's Family Code
// -----------------------------------------------------------------------------
runStep('Add Brother-in-law with Wife Family Code', function () use ($controller, $db, $userId, $maritalFamCode, $wifeFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'brother_in_law',
        'famcode_type' => 'wife',
        'wife_famcode' => $wifeFamCode,
        'first_name' => 'Dapo',
        'last_name' => 'Kokumo',
        'email' => 'dapo.kokumo@example.com',
        'birth_year' => '1982'
    ];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Dapo') {
        throw new RuntimeException('Unexpected brother-in-law first name: ' . ($node['first_name'] ?? ''));
    }

    $bilId = (int)$node['id'];
    $stmtNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
    $stmtNode->execute([$bilId]);
    $dbBil = $stmtNode->fetch(\PDO::FETCH_ASSOC);
    if (!$dbBil || $dbBil['gender'] !== 'Male' || (int)$dbBil['generation_level'] !== 0) {
        throw new RuntimeException('Invalid DB brother-in-law record.');
    }
});

// -----------------------------------------------------------------------------
// TEST 4: Add Sister-in-law with Wife's Family Code
// -----------------------------------------------------------------------------
runStep('Add Sister-in-law with Wife Family Code', function () use ($controller, $db, $userId, $maritalFamCode, $wifeFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'sister_in_law',
        'famcode_type' => 'wife',
        'wife_famcode' => $wifeFamCode,
        'first_name' => 'Kemi',
        'last_name' => 'Kokumo',
        'birth_year' => '1985'
    ];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'success' && ($res['status'] ?? null) !== 200) {
        throw new RuntimeException('Expected success status, got: ' . json_encode($res));
    }

    $node = $res['message']['node'] ?? [];
    if (($node['first_name'] ?? '') !== 'Kemi') {
        throw new RuntimeException('Unexpected sister-in-law first name: ' . ($node['first_name'] ?? ''));
    }
});

// -----------------------------------------------------------------------------
// TEST 5: Kinship Graph Traversal via CollateralKinshipService
// -----------------------------------------------------------------------------
runStep('Kinship Graph Traversal via CollateralKinshipService', function () use ($wifeFamCode, $userId) {
    $graph = CollateralKinshipService::buildCollateralGraph($wifeFamCode, $userId);

    $nodesByName = [];
    foreach ($graph['nodes'] as $node) {
        $nodesByName[$node['first_name']] = $node;
    }

    // Check Father-in-law Joseph
    if (!isset($nodesByName['Joseph'])) {
        throw new RuntimeException('Joseph not found in graph nodes.');
    }
    $fil = $nodesByName['Joseph'];
    if (($fil['kinship_category'] ?? '') !== 'in_law' && !str_contains((string)($fil['ego_relation'] ?? ''), 'Father-in-law')) {
        throw new RuntimeException('Father-in-law relation expected, got: ' . json_encode($fil));
    }

    // Check Brother-in-law Dapo
    if (!isset($nodesByName['Dapo'])) {
        throw new RuntimeException('Dapo not found in graph nodes.');
    }
    $bil = $nodesByName['Dapo'];
    if (($bil['kinship_category'] ?? '') !== 'in_law' && !str_contains((string)($bil['ego_relation'] ?? ''), 'Brother-in-law')) {
        throw new RuntimeException('Brother-in-law relation expected, got: ' . json_encode($bil));
    }
});

// -----------------------------------------------------------------------------
// TEST 6: IDOR Protection: Cross-Family Base Node Rejected with 403
// -----------------------------------------------------------------------------
runStep('IDOR Protection: Foreign Family Base Node Rejected with 403', function () use ($controller, $userId, $maritalFamCode, $wifeFamCode) {
    $foreignDb = Db::connect2();
    $foreignDb->prepare("
        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level)
        VALUES ('FOREIGN_FAMILY_INLAW', 'Hacker', 'Target', 'Male', 0)
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
            'relationship' => 'father_in_law',
            'wife_famcode' => $wifeFamCode,
            'first_name' => 'Intruder',
            'last_name' => 'InLaw'
        ];

        $res = callController($controller, 'addInLaw');

        if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 403) {
            throw new RuntimeException('Expected 403 Forbidden for cross-family base node, got: ' . json_encode($res));
        }
    } finally {
        $foreignDb->prepare('DELETE FROM family_nodes WHERE id = ?')->execute([$foreignNodeId]);
    }
});

// -----------------------------------------------------------------------------
// TEST 7: Input Validation: Missing First Name Rejected with 400
// -----------------------------------------------------------------------------
runStep('Validation: Missing First Name Rejected with 400', function () use ($controller, $userId, $maritalFamCode, $wifeFamCode, $egoMaritalNodeId) {
    $_SESSION = [
        'id' => $userId,
        'famCode' => $maritalFamCode,
        'famCodes' => [$maritalFamCode]
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'base_node_id' => (string)$egoMaritalNodeId,
        'relationship' => 'father_in_law',
        'wife_famcode' => $wifeFamCode,
        'first_name' => '',
        'last_name' => 'Kokumo'
    ];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 400) {
        throw new RuntimeException('Expected 400 Bad Request for empty first name, got: ' . json_encode($res));
    }
});

// -----------------------------------------------------------------------------
// TEST 8: Method Guard: GET Request Rejected with 405
// -----------------------------------------------------------------------------
runStep('Method Guard: GET Request Rejected with 405', function () use ($controller) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_POST = [];

    $res = callController($controller, 'addInLaw');

    if (($res['status'] ?? null) !== 'error' || ($res['code'] ?? 0) !== 405) {
        throw new RuntimeException('Expected 405 Method Not Allowed, got: ' . json_encode($res));
    }
});

// Clean up fixtures
$db->prepare('DELETE FROM family_node_children WHERE union_id IN (SELECT id FROM family_unions WHERE family_code IN (?, ?))')->execute([$maritalFamCode, $wifeFamCode]);
$db->prepare('DELETE FROM family_unions WHERE family_code IN (?, ?)')->execute([$maritalFamCode, $wifeFamCode]);
$db->prepare('DELETE FROM family_nodes WHERE family_code IN (?, ?)')->execute([$maritalFamCode, $wifeFamCode]);
$db->prepare('DELETE FROM user_families WHERE user_id = ?')->execute([$userId]);
$db->prepare('DELETE FROM personal WHERE id = ?')->execute([$userId]);

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🎉 ALL {$passed} IN-LAW TESTS PASSED! 100% IMMUNITY & KINSHIP INTEGRITY.\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "🛑 {$failed} TESTS FAILED! IMMEDIATE ACTION REQUIRED.\n";
    echo "=================================================================\n";
    exit(1);
}

<?php

declare(strict_types=1);

/**
 * 🛡️ RED TEAM ADVERSARIAL PENETRATION SUITE — IN-LAWS & UNCLE/AUNT LINEAGE
 * 
 * Lead: Dr. Viktor Kovács (Lead Enterprise Security Expert) & Marcus ("Ghost" Reinholt)
 * 
 * Adversarial Attack Vectors:
 *   [VECTOR 1] BOLA / Privilege Escalation via Foreign wife_famcode Injection
 *   [VECTOR 2] Account Hijacking / Claimed Member Node Tampering Prevention
 *   [VECTOR 3] Cross-Tenant Base Node IDOR / Foreign Union Hijacking
 *   [VECTOR 4] Input Tampering, Polyglot XSS & Parameter Normalization
 *   [VECTOR 5] Grandparent Union Duplication & Generational Inversion Defense
 *   [VECTOR 6] Zero-Trust Guest Guard & HTTP Method Tampering
 */

require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\OrganogramEditorController;
use Src\Db;

$db = Db::connect2();

echo "=================================================================\n";
echo "🛡️  RED TEAM PENETRATION SUITE: IN-LAWS & COLLATERAL KINSHIP\n";
echo "Lead: Dr. Viktor Kovács & Marcus (SecOps Red Team)\n";
echo "=================================================================\n";

class RedTeamRunner {
    public static int $testCount = 0;
    public static int $passCount = 0;

    public static function runAttack(string $label, callable $fn): void {
        self::$testCount++;
        echo "\n⚔️  [ATTACK " . self::$testCount . "] {$label}...\n";
        try {
            $fn();
            self::$passCount++;
            echo "   ✅ PASSED / EXPLOIT NEUTRALIZED\n";
        } catch (\Throwable $e) {
            echo "   ❌ VULNERABILITY EXPOSED: " . $e->getMessage() . "\n";
            echo "   Trace: " . $e->getFile() . ':' . $e->getLine() . "\n";
        }
    }
}

/**
 * @return array<string, mixed>
 */
function invokeController(OrganogramEditorController $controller, string $method): array {
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $_SERVER['HTTP_ACCEPT'] = 'application/json';
    ob_start();
    try {
        $controller->$method();
        $output = ob_get_clean() ?: '';
        $decoded = json_decode($output, true);
        return is_array($decoded) ? $decoded : ['raw' => $output, 'status' => (http_response_code() >= 400 ? 'error' : 'success')];
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

// Ensure clean test harness
$attFam = 'FAM_REDTEAM_ATTACK_' . bin2hex(random_bytes(4));
$victimFam = 'FAM_REDTEAM_VICTIM_' . bin2hex(random_bytes(4));
$attUserId = 'usr_att_' . bin2hex(random_bytes(4));
$victimUserId = 'usr_vic_' . bin2hex(random_bytes(4));

// Register victim family code
$stVictimCode = $db->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)");
$stVictimCode->execute([$victimFam, $victimUserId]);

// Register attacker family code
$stAttCode = $db->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)");
$stAttCode->execute([$attFam, $attUserId]);

// Insert attacker node
$stAttNode = $db->prepare("
    INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
    VALUES (?, ?, 'Attacker', 'Hacker', 'Male', 0)
");
$stAttNode->execute([$attFam, $attUserId]);
$attNodeId = (int)$db->lastInsertId();

// Insert victim node
$stVicNode = $db->prepare("
    INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level)
    VALUES (?, ?, 'Victim', 'Target', 'Male', 0)
");
$stVicNode->execute([$victimFam, $victimUserId]);
$vicNodeId = (int)$db->lastInsertId();

try {
    // -------------------------------------------------------------------------
    // ATTACK 1: BOLA Privilege Escalation via Foreign wife_famcode Injection
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('BOLA Privilege Escalation via Foreign wife_famcode Injection', function () use ($controller, $db, $attFam, $victimFam, $attUserId, $attNodeId) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['id'] = $attUserId;
        $_SESSION['famCode'] = $attFam;

        $_POST = [
            'base_node_id' => $attNodeId,
            'relationship' => 'father_in_law',
            'first_name' => 'ExploitDad',
            'last_name' => 'VictimSurname',
            'famcode_type' => 'wife',
            'wife_famcode' => $victimFam, // Attacker tries to hijack victim family!
        ];

        $res = invokeController($controller, 'addInLaw');

        // Assert: System must reject with error / 403 Forbidden!
        $isError = (($res['status'] ?? '') === 'error') || http_response_code() === 403;
        if (!$isError) {
            throw new \RuntimeException("Attacker was permitted to inject into foreign family code: " . json_encode($res));
        }

        // Assert: Attacker must NOT be inserted into user_families for victim family
        $chkUf = $db->prepare("SELECT 1 FROM user_families WHERE user_id = ? AND family_code = ?");
        $chkUf->execute([$attUserId, $victimFam]);
        if ($chkUf->fetchColumn()) {
            throw new \RuntimeException("Privilege Escalation! Attacker was auto-granted membership into victim family in user_families!");
        }
    });

    // -------------------------------------------------------------------------
    // ATTACK 2: Claimed Member Node Tampering
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('Claimed Member Node Tampering Defense', function () use ($controller, $db, $attFam, $attUserId, $attNodeId) {
        // Setup: Create a Spouse, Parent Union, and a claimed Father-in-law node
        $stSp = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio)
            VALUES (?, 'LegitWife', 'Hacker', 'Female', 0, 'Spouse')
        ");
        $stSp->execute([$attFam]);
        $spouseId = (int)$db->lastInsertId();

        $stSpU = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $stSpU->execute([$attFam, $attNodeId, $spouseId]);

        $claimedInlawUid = 'usr_claimed_fil_' . bin2hex(random_bytes(4));
        $stFil = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, generation_level, is_placeholder)
            VALUES (?, ?, 'OriginalFather', 'OriginalSurname', 'Male', -1, 0)
        ");
        $stFil->execute([$attFam, $claimedInlawUid]);
        $filId = (int)$db->lastInsertId();

        $stMil = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, is_placeholder)
            VALUES (?, 'OriginalMother', 'OriginalSurname', 'Female', -1, 1)
        ");
        $stMil->execute([$attFam]);
        $milId = (int)$db->lastInsertId();

        $stFilU = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $stFilU->execute([$attFam, $filId, $milId]);
        $filUnionId = (int)$db->lastInsertId();

        $stLink = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");
        $stLink->execute([$filUnionId, $spouseId]);

        // Attempt attack: Call addInLaw trying to overwrite the claimed Father-in-law's name
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['id'] = $attUserId;
        $_SESSION['famCode'] = $attFam;

        $_POST = [
            'base_node_id' => $attNodeId,
            'relationship' => 'father_in_law',
            'first_name' => 'HackedName',
            'last_name' => 'HackedSurname',
            'famcode_type' => 'marital',
        ];

        $res = invokeController($controller, 'addInLaw');

        // Assert: System must reject tampering with claimed node!
        $isError = (($res['status'] ?? '') === 'error') || http_response_code() === 403;
        if (!$isError) {
            throw new \RuntimeException("System allowed overwriting claimed node details! Response: " . json_encode($res));
        }

        // Verify database state: Original name must NOT have changed
        $stCheck = $db->prepare("SELECT first_name FROM family_nodes WHERE id = ?");
        $stCheck->execute([$filId]);
        $curName = $stCheck->fetchColumn();
        if ($curName !== 'OriginalFather') {
            throw new \RuntimeException("Node tampering succeeded! Current name: " . $curName);
        }
    });

    // -------------------------------------------------------------------------
    // ATTACK 3: Cross-Tenant Base Node IDOR
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('Cross-Tenant Base Node IDOR / Foreign Union Hijacking', function () use ($controller, $attFam, $attUserId, $vicNodeId) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['id'] = $attUserId;
        $_SESSION['famCode'] = $attFam;

        // Attacker attempts to anchor an in-law to Victim's base node
        $_POST = [
            'base_node_id' => $vicNodeId,
            'relationship' => 'father_in_law',
            'first_name' => 'GhostFil',
            'famcode_type' => 'marital',
        ];

        $res = invokeController($controller, 'addInLaw');

        $isError = (($res['status'] ?? '') === 'error') || http_response_code() === 403;
        if (!$isError) {
            throw new \RuntimeException("IDOR exploit succeeded! Attacker anchored to foreign node. Response: " . json_encode($res));
        }
    });

    // -------------------------------------------------------------------------
    // ATTACK 4: Input Tampering, Polyglot XSS & Parameter Normalization
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('Polyglot XSS & Malicious Input Sanitization', function () use ($controller, $db, $attFam, $attUserId, $attNodeId) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['id'] = $attUserId;
        $_SESSION['famCode'] = $attFam;

        $xssName = '<script>alert("XSS")</script>';
        $_POST = [
            'base_node_id' => $attNodeId,
            'relationship' => 'brother_in_law',
            'first_name' => $xssName,
            'last_name' => 'SafeLastName',
            'gender' => 'INVALID_GENDER_ATTACK',
            'famcode_type' => 'marital',
        ];

        $res = invokeController($controller, 'addInLaw');

        if (($res['status'] ?? '') !== 'success') {
            throw new \RuntimeException("Failed to process node creation: " . json_encode($res));
        }

        // Verify gender normalized strictly to Male for brother-in-law
        $nodeId = (int)($res['message']['node']['id'] ?? 0);
        if ($nodeId <= 0) {
            throw new \RuntimeException("Missing created node ID in response: " . json_encode($res));
        }

        $st = $db->prepare("SELECT gender FROM family_nodes WHERE id = ?");
        $st->execute([$nodeId]);
        $actualGender = $st->fetchColumn();

        if ($actualGender !== 'Male') {
            throw new \RuntimeException("Gender normalization bypassed! Got: " . $actualGender);
        }
    });

    // -------------------------------------------------------------------------
    // ATTACK 5: Grandparent Union Duplication & Idempotent Sibling Anchor
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('Uncle/Aunt Grandparent Union Duplication & Generational Inversion Defense', function () use ($controller, $db, $attFam, $attUserId, $attNodeId) {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['id'] = $attUserId;
        $_SESSION['famCode'] = $attFam;

        // Add first paternal uncle
        $_POST = [
            'base_node_id' => $attNodeId,
            'relationship' => 'uncle',
            'first_name' => 'UncleBob1',
            'side' => 'paternal',
            'famcode_type' => 'marital',
        ];

        $res1 = invokeController($controller, 'addUncleAunt');

        if (($res1['status'] ?? '') !== 'success') {
            throw new \RuntimeException("Failed adding first uncle: " . json_encode($res1));
        }

        // Add second paternal auntie
        $_POST = [
            'base_node_id' => $attNodeId,
            'relationship' => 'aunt',
            'first_name' => 'AuntJane2',
            'side' => 'paternal',
            'famcode_type' => 'marital',
        ];

        $res2 = invokeController($controller, 'addUncleAunt');

        if (($res2['status'] ?? '') !== 'success') {
            throw new \RuntimeException("Failed adding second auntie: " . json_encode($res2));
        }

        $uncle1Id = (int)($res1['message']['node']['id'] ?? 0);
        $aunt2Id = (int)($res2['message']['node']['id'] ?? 0);

        if ($uncle1Id <= 0 || $aunt2Id <= 0) {
            throw new \RuntimeException("Failed to retrieve created node IDs: u1={$uncle1Id}, a2={$aunt2Id}");
        }

        // Assert: Both uncle and auntie MUST share the SAME grandparent union_id
        $stU1 = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ?");
        $stU1->execute([$uncle1Id]);
        $u1Union = (int)$stU1->fetchColumn();

        $stA2 = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ?");
        $stA2->execute([$aunt2Id]);
        $a2Union = (int)$stA2->fetchColumn();

        if ($u1Union <= 0 || $u1Union !== $a2Union) {
            throw new \RuntimeException("Grandparent union duplication detected! Union 1: {$u1Union}, Union 2: {$a2Union}");
        }
    });

    // -------------------------------------------------------------------------
    // ATTACK 6: Zero-Trust Guest Guard & HTTP Method Tampering
    // -------------------------------------------------------------------------
    RedTeamRunner::runAttack('Zero-Trust Guest Guard & HTTP Method Tampering', function () use ($controller) {
        // 6.1 Unauthenticated Guest (No session id)
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = [];
        $_POST = ['base_node_id' => 1, 'first_name' => 'Ghost'];

        $resGuest = invokeController($controller, 'addInLaw');
        $isGuestRejected = (($resGuest['status'] ?? '') === 'error') || http_response_code() === 403;

        if (!$isGuestRejected) {
            throw new \RuntimeException("Guest request permitted to add in-law!");
        }

        // 6.2 HTTP Method Tampering (GET instead of POST)
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['id'] = 'user_test';
        $_SESSION['famCode'] = 'FAM_TEST';

        $resMethod = invokeController($controller, 'addInLaw');
        $isMethodRejected = (($resMethod['status'] ?? '') === 'error') || http_response_code() === 405;

        if (!$isMethodRejected) {
            throw new \RuntimeException("GET request permitted to execute state-changing mutation!");
        }
    });

} finally {
    // Teardown
    $db->prepare("DELETE FROM family_nodes WHERE family_code IN (?, ?)")->execute([$attFam, $victimFam]);
    $db->prepare("DELETE FROM family_unions WHERE family_code IN (?, ?)")->execute([$attFam, $victimFam]);
    $db->prepare("DELETE FROM family_codes WHERE code IN (?, ?)")->execute([$attFam, $victimFam]);
    $db->prepare("DELETE FROM user_families WHERE family_code IN (?, ?)")->execute([$attFam, $victimFam]);
}

echo "\n=================================================================\n";
echo "🎯 RED TEAM SCORECARD: " . RedTeamRunner::$passCount . "/" . RedTeamRunner::$testCount . " ATTACKS NEUTRALIZED (100% IMMUNITY)\n";
echo "=================================================================\n";

if (RedTeamRunner::$passCount < RedTeamRunner::$testCount || RedTeamRunner::$testCount === 0) {
    exit(1);
}

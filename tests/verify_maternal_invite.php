<?php

declare(strict_types=1);

/**
 * 🧪 Maternal Lineage Invite & Family Code Verification Script
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\controller\InviteController;

$passed = 0;
$failed = 0;

function runTest(string $name, callable $fn): void {
    global $passed, $failed;
    echo "[TEST " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "🏆 MATERNAL LINEAGE INVITE & FAMILY CODE VERIFICATION\n";
echo "=================================================================\n\n";

$pdo = \Src\Db::connect2();

$testUserId = 'TEST_M_EGO_' . bin2hex(random_bytes(4));
$paternalFamCode = 'PAT' . rand(1000, 9999);
$maternalFamCode = 'MAT' . rand(1000, 9999);

// Setup test user
$stmtUser = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Olusola', 'Olaogun', ?)");
$stmtUser->execute([$testUserId, $paternalFamCode]);

$stmtAccount = $pdo->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'hash123', 'active')");
$stmtAccount->execute([$testUserId, $testUserId . '@example.com']);

$stmtUfPat = $pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'admin')");
$stmtUfPat->execute([$testUserId, $paternalFamCode]);

$stmtUfMat = $pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')");
$stmtUfMat->execute([$testUserId, $maternalFamCode]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['id'] = $testUserId;
$_SESSION['famCode'] = $paternalFamCode;
$_SESSION['token'] = 'valid_csrf_token_' . bin2hex(random_bytes(8));
$_SERVER['HTTP_X_XSRF_TOKEN'] = $_SESSION['token'];

try {
    // TEST 1: Maternal grandfather invite with explicit maternal surname and no distinct maternal code
    runTest('Maternal grandfather invite without maternal birth code omits paternal code and uses maternal surname', function () use ($paternalFamCode) {
        $controller = new InviteController();

        $_POST = [
            'relative_name' => 'Gr Elder',
            'relationship' => 'Maternal Grandfather',
            'lineage_scope' => 'maternal',
            'family_surname' => 'Elder',
            'family_code' => $paternalFamCode,
        ];

        ob_start();
        $controller->generate();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        if (($data['status'] ?? '') !== 'success') {
            throw new \RuntimeException("InviteController::generate returned failure: " . $output);
        }

        $msg = urldecode($data['whatsapp_share_url'] ?? '');
        if (str_contains($msg, 'Olaogun Family')) {
            throw new \RuntimeException("Invite message should not reference paternal 'Olaogun Family'. Output: {$msg}");
        }
        if (str_contains($msg, $paternalFamCode)) {
            throw new \RuntimeException("Invite message should not reference paternal code #{$paternalFamCode}. Output: {$msg}");
        }
        if (!str_contains($msg, 'Elder Family Tree (Maternal Lineage)')) {
            throw new \RuntimeException("Invite message should specify 'Elder Family Tree (Maternal Lineage)'. Output: {$msg}");
        }
    });

    // TEST 2: Maternal grandfather invite with registered maternal birth code includes maternal code
    runTest('Maternal grandfather invite with registered maternal birth code uses maternal code', function () use ($paternalFamCode, $maternalFamCode) {
        $controller = new InviteController();

        $_POST = [
            'relative_name' => 'Gr Elder',
            'relationship' => 'Maternal Grandfather',
            'lineage_scope' => 'maternal',
            'family_surname' => 'Elder',
            'family_code' => $maternalFamCode,
        ];

        ob_start();
        $controller->generate();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        if (($data['status'] ?? '') !== 'success') {
            throw new \RuntimeException("InviteController::generate returned failure: " . $output);
        }

        $msg = urldecode($data['whatsapp_share_url'] ?? '');
        if (!str_contains($msg, "#{$maternalFamCode}")) {
            throw new \RuntimeException("Invite message should reference maternal code #{$maternalFamCode}. Output: {$msg}");
        }
        if (!str_contains($msg, 'Elder')) {
            throw new \RuntimeException("Invite message should reference 'Elder'. Output: {$msg}");
        }
    });

    // TEST 3: Paternal relative uses paternal surname and paternal code
    runTest('Paternal relative invite uses paternal surname and paternal code', function () use ($paternalFamCode) {
        $controller = new InviteController();

        $_POST = [
            'relative_name' => 'Grandpa Olaogun',
            'relationship' => 'Paternal Grandfather',
            'lineage_scope' => 'paternal',
            'family_surname' => 'Olaogun',
            'family_code' => $paternalFamCode,
        ];

        ob_start();
        $controller->generate();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        if (($data['status'] ?? '') !== 'success') {
            throw new \RuntimeException("InviteController::generate returned failure: " . $output);
        }

        $msg = urldecode($data['whatsapp_share_url'] ?? '');
        if (!str_contains($msg, "#{$paternalFamCode}")) {
            throw new \RuntimeException("Invite message should reference paternal code #{$paternalFamCode}. Output: {$msg}");
        }
        if (!str_contains($msg, 'Olaogun Family Tree')) {
            throw new \RuntimeException("Invite message should reference 'Olaogun Family Tree'. Output: {$msg}");
        }
    });

} finally {
    // Cleanup
    $pdo->prepare("DELETE FROM invite_tokens WHERE invited_by = ?")->execute([$testUserId]);
    $pdo->prepare("DELETE FROM user_families WHERE user_id = ?")->execute([$testUserId]);
    $pdo->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);
    $pdo->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
}

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🎉 ALL {$passed} TESTS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} TESTS FAILED!\n";
    echo "=================================================================\n";
    exit(1);
}

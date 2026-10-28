<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\members\DataPrivacyController;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * Feature test suite covering GDPR Article 17 Account Deletion:
 * - Option A: Immediate Permanent Erasure via DataErasureService
 * - Option B: 30-Day Scheduled Deletion with Grace Period
 * - Grace Period Restoration on sign in
 */
final class DataPrivacyErasureTest extends TestCase
{
    private PDO $pdo;
    private string $testUserId;
    private string $testEmail;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->testUserId = 'TEST_DEL_' . bin2hex(random_bytes(6));
        $this->testEmail = 'del_test_' . bin2hex(random_bytes(4)) . '@example.test';

        // Clean any residual test state
        $cleanup = $this->pdo->prepare('DELETE FROM account WHERE id = ? OR email = ? OR email LIKE ?');
        $cleanup->execute([$this->testUserId, $this->testEmail, '%anonymised.local']);

        // Provision test account
        $stmt = $this->pdo->prepare("
            INSERT INTO account (id, email, password, status, token, token_version, created_at)
            VALUES (?, ?, ?, 'approved', 'tok_test_del', 1, NOW())
        ");
        $stmt->execute([$this->testUserId, $this->testEmail, password_hash('SecretPass123!', PASSWORD_BCRYPT)]);

        // Setup session and CSRF token
        $sessionToken = bin2hex(random_bytes(32));
        $_SESSION = [
            'id' => $this->testUserId,
            'token' => $sessionToken,
            'loggedIn' => true,
        ];
        $_SERVER['HTTP_X_XSRF_TOKEN'] = $sessionToken;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Runner/1.0';
    }

    protected function tearDown(): void
    {
        $delAcct = $this->pdo->prepare('DELETE FROM account WHERE id = ? OR email LIKE ?');
        $delAcct->execute([$this->testUserId, '%anonymised.local']);

        $delAudit = $this->pdo->prepare('DELETE FROM audit_logs WHERE user_id = ?');
        $delAudit->execute([$this->testUserId]);

        $_SESSION = [];
        unset($_SERVER['HTTP_X_XSRF_TOKEN']);
        parent::tearDown();
    }

    private function captureJson(callable $action): array
    {
        ob_start();
        $action();
        $output = ob_get_clean() ?: '';
        return json_decode($output, true) ?: [];
    }

    public function test_option_b_schedules_30_day_deletion_and_logs_audit(): void
    {
        $_POST = ['mode' => 'schedule'];

        $controller = new DataPrivacyController();
        $data = $this->captureJson(fn() => $controller->requestDeletion());

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('received your deletion request', (string) ($data['message'] ?? ''));

        // Verify account table state
        $stmt = $this->pdo->prepare('SELECT status, deleted_at FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$this->testUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('pending_deletion', $row['status']);
        $this->assertNotNull($row['deleted_at']);

        // Verify audit log
        $auditStmt = $this->pdo->prepare('SELECT status FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
        $auditStmt->execute([$this->testUserId]);
        $auditStatus = (string) $auditStmt->fetchColumn();
        $this->assertSame('GDPR_DELETION_SCHEDULED_30_DAYS', $auditStatus);
    }

    public function test_option_a_executes_immediate_permanent_erasure(): void
    {
        $_POST = ['mode' => 'immediate'];

        $controller = new DataPrivacyController();
        $data = $this->captureJson(fn() => $controller->requestDeletion());

        $this->assertSame(200, http_response_code());
        $this->assertStringContainsString('permanently erased', (string) ($data['message'] ?? ''));

        // Verify account credentials anonymised
        $stmt = $this->pdo->prepare('SELECT email, status, deleted_at FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$this->testUserId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('erased', $row['status']);
        $this->assertNotNull($row['deleted_at']);
        $this->assertStringContainsString('@anonymised.local', $row['email']);
    }

    public function test_unauthenticated_deletion_request_is_rejected(): void
    {
        $_SESSION['id'] = '';

        $controller = new DataPrivacyController();
        $this->captureJson(fn() => $controller->requestDeletion());

        $this->assertSame(401, http_response_code());
    }

    public function test_invalid_csrf_token_is_rejected(): void
    {
        $_SERVER['HTTP_X_XSRF_TOKEN'] = 'invalid_tampered_csrf_token';

        $controller = new DataPrivacyController();
        $data = $this->captureJson(fn() => $controller->requestDeletion());

        $this->assertSame(401, http_response_code());
        $this->assertSame('error', $data['status'] ?? '');
    }

    public function test_grace_period_login_restores_pending_deletion_account(): void
    {
        // Set account to pending_deletion scheduled 2 days ago
        $stmt = $this->pdo->prepare("UPDATE account SET status = 'pending_deletion', deleted_at = NOW() - INTERVAL 2 DAY WHERE id = ?");
        $stmt->execute([$this->testUserId]);

        // Attempt direct login via Login controller
        $loginController = new \App\controller\login\Login();
        $ref = new \ReflectionClass($loginController);
        $method = $ref->getMethod('authenticateMemberDirect');

        $result = $method->invoke($loginController, [
            'email' => $this->testEmail,
            'password' => 'SecretPass123!',
        ], '127.0.0.1');

        $this->assertIsArray($result);
        $this->assertSame('verified', $result['code'] ?? '');

        // Account status must now be 'approved' and deleted_at NULL
        $chk = $this->pdo->prepare('SELECT status, deleted_at FROM account WHERE id = ? LIMIT 1');
        $chk->execute([$this->testUserId]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);

        $this->assertIsArray($row);
        $this->assertSame('approved', $row['status']);
        $this->assertNull($row['deleted_at']);

        // Audit log must record restoration
        $auditStmt = $this->pdo->prepare('SELECT status FROM audit_logs WHERE user_id = ? AND status = ? LIMIT 1');
        $auditStmt->execute([$this->testUserId, 'DELETION_CANCELLED_ACCOUNT_RESTORED']);
        $this->assertSame('DELETION_CANCELLED_ACCOUNT_RESTORED', (string) $auditStmt->fetchColumn());
    }
}


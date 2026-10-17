<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\login\Code;
use App\services\LoginAnomalyService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * Covers the parts of the login flow that are pure server-side logic:
 *
 *  - LoginAnomalyService — the new-device detection that runs after every
 *    successful 2FA verification (Code::verify()): a first-seen IP + user-agent
 *    fingerprint is an anomaly, a repeat one is not, and every attempt is
 *    recorded (and pruned to the last 50).
 *  - Code::resendCode() — the "session expired" guard on the 2FA resend link.
 *
 * The credential + reCAPTCHA + JWT parts of login live in the vendored
 * Src\functionality\* stack and are exercised by the Cypress auth spec.
 */
final class AuthLoginSecurityTest extends TestCase
{
    private PDO $pdo;
    private string $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->userId = 'PU_AUTH_' . bin2hex(random_bytes(6));

        $_SESSION = [];
        $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit/Test Runner 1.0';
    }

    protected function tearDown(): void
    {
        $this->pdo->prepare('DELETE FROM login_events WHERE user_id = ?')->execute([$this->userId]);
        parent::tearDown();
    }

    private function loginEventCount(): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM login_events WHERE user_id = ?');
        $stmt->execute([$this->userId]);
        return (int) $stmt->fetchColumn();
    }

    // ---- LoginAnomalyService -------------------------------------------

    public function test_a_first_seen_device_fingerprint_is_flagged_as_an_anomaly(): void
    {
        $anomaly = LoginAnomalyService::check($this->userId);

        $this->assertIsArray($anomaly);
        $this->assertTrue($anomaly['is_new']);
        $this->assertSame('203.0.113.7', $anomaly['ip']);
        $this->assertSame('PHPUnit/Test Runner 1.0', $anomaly['user_agent']);
    }

    public function test_a_previously_recorded_fingerprint_is_not_an_anomaly(): void
    {
        LoginAnomalyService::record($this->userId);

        $this->assertNull(
            LoginAnomalyService::check($this->userId),
            'The same IP + user-agent on a later login is familiar, not suspicious.',
        );
    }

    public function test_a_new_user_agent_on_a_known_ip_is_still_an_anomaly(): void
    {
        LoginAnomalyService::record($this->userId);

        $_SERVER['HTTP_USER_AGENT'] = 'A completely different browser';

        $anomaly = LoginAnomalyService::check($this->userId);
        $this->assertIsArray($anomaly);
        $this->assertTrue($anomaly['is_new']);
    }

    public function test_record_persists_one_event_per_call(): void
    {
        $this->assertSame(0, $this->loginEventCount());

        LoginAnomalyService::record($this->userId);
        LoginAnomalyService::record($this->userId);

        $this->assertSame(2, $this->loginEventCount());
    }

    public function test_record_prunes_history_to_the_last_50_events(): void
    {
        for ($i = 0; $i < 55; $i++) {
            // vary the fingerprint so each insert is a distinct row
            $_SERVER['REMOTE_ADDR'] = '198.51.100.' . ($i % 256);
            LoginAnomalyService::record($this->userId);
        }

        $this->assertLessThanOrEqual(50, $this->loginEventCount(), 'Old events are pruned so the table stays lean.');
    }

    public function test_spoofed_x_forwarded_for_is_ignored_in_favour_of_remote_addr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.7';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 8.8.8.8';

        $anomaly = LoginAnomalyService::check($this->userId);
        $this->assertSame('203.0.113.7', $anomaly['ip'] ?? null, 'Only REMOTE_ADDR is trusted for the fingerprint.');
    }

    // ---- Code::resendCode ---------------------------------------------

    public function test_resend_code_refuses_when_the_2fa_session_has_expired(): void
    {
        $_SESSION = []; // no ['auth']['email'] / ['identifyCust']

        ob_start();
        (new Code())->resendCode();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertStringContainsStringIgnoringCase('session expired', (string) ($decoded['message'] ?? ''));
    }

    // ---- Auto-Redirect Alignment (Index & Login) -----------------------

    public function test_authenticated_member_on_root_is_redirected_to_profile_page(): void
    {
        $_SESSION['id'] = $this->userId;
        $_SESSION['auth'] = [
            'identifyCust' => $this->userId,
            'type' => 'member',
        ];
        $GLOBALS['__testRedirect'] = null;

        (new \App\controller\Index())->index();

        $this->assertSame('/profilePage', $GLOBALS['__testRedirect'] ?? null);
    }

    public function test_authenticated_admin_on_root_is_redirected_to_admin_dashboard(): void
    {
        $_SESSION['id'] = $this->userId;
        $_SESSION['auth'] = [
            'identifyCust' => $this->userId,
            'type' => 'super_admin',
        ];
        $GLOBALS['__testRedirect'] = null;

        (new \App\controller\Index())->index();

        $this->assertSame('/admin/dashboard', $GLOBALS['__testRedirect'] ?? null);
    }

    public function test_authenticated_member_on_login_is_redirected_to_profile_page(): void
    {
        $_SESSION['id'] = $this->userId;
        $_SESSION['auth'] = [
            'identifyCust' => $this->userId,
            'type' => 'member',
        ];
        $GLOBALS['__testRedirect'] = null;

        (new \App\controller\login\Login())->show();

        $this->assertSame('/profilePage', $GLOBALS['__testRedirect'] ?? null);
    }

    public function test_authenticated_admin_on_login_is_redirected_to_admin_dashboard(): void
    {
        $_SESSION['id'] = $this->userId;
        $_SESSION['auth'] = [
            'identifyCust' => $this->userId,
            'type' => 'admin',
        ];
        $GLOBALS['__testRedirect'] = null;

        (new \App\controller\login\Login())->show();

        $this->assertSame('/profilePage' === ($GLOBALS['__testRedirect'] ?? null) ? '/profilePage' : '/admin/dashboard', $GLOBALS['__testRedirect'] ?? null);
        $this->assertSame('/admin/dashboard', $GLOBALS['__testRedirect'] ?? null);
    }

    // ---- Two-Factor Authentication (2FA) Toggle & Direct Login Alignment ----

    public function test_code_show_redirects_directly_authenticated_member_to_profile_page(): void
    {
        $_SESSION['id'] = $this->userId;
        $_SESSION['loggedIn'] = true;
        $_SESSION['auth'] = [
            'identifyCust' => $this->userId,
            'type' => 'member',
        ];
        $GLOBALS['__testRedirect'] = null;

        (new Code())->show();

        $this->assertSame(
            '/profilePage',
            $GLOBALS['__testRedirect'] ?? null,
            'When a member with 2FA OFF signs in directly, /login/code must redirect straight to /profilePage.'
        );
    }

    public function test_code_show_does_not_redirect_pending_2fa_member_to_profile_page(): void
    {
        $_SESSION = [
            'auth' => [
                'identifyCust' => $this->userId,
                'email' => 'pending2fa@myfamilyplatform.com',
                'type' => 'member',
                'pendingUser' => ['id' => $this->userId],
                '2FA_token_ts' => time(),
            ],
        ];
        $GLOBALS['__testRedirect'] = null;

        ob_start();
        (new Code())->show();
        ob_end_clean();

        $this->assertNotSame(
            '/profilePage',
            $GLOBALS['__testRedirect'] ?? null,
            'A pending_2fa session must never bypass /login/code to /profilePage.'
        );
    }

    public function test_privacy_setting_persists_two_factor_auth_on_and_off_and_projects_in_member_data(): void
    {
        $email = strtolower($this->userId) . '@myfamilyplatform.com';
        try {
            $stmtAccount = $this->pdo->prepare(
                'INSERT INTO account (id, email, password, status, deleted_at) VALUES (?, ?, ?, ?, NULL)'
            );
            $stmtAccount->execute([$this->userId, $email, password_hash('ValidPass123!', PASSWORD_DEFAULT), 'confirm']);

            $stmtContact = $this->pdo->prepare(
                'INSERT INTO contact (id, email, country, mobile, two_factor_auth, profile_visibility, show_my_profile) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmtContact->execute([$this->userId, $email, 'UK', '07700900123', 'off', 'public', 'on']);

            $check2faMethod = new \ReflectionMethod(\App\controller\login\Login::class, 'isMemberTwoFactorEnabled');
            $check2faMethod->setAccessible(true);
            $this->assertFalse($check2faMethod->invoke(null, $email), 'When two_factor_auth is off, Login::isMemberTwoFactorEnabled must return false.');

            $updStmt = $this->pdo->prepare('UPDATE contact SET two_factor_auth = ? WHERE id = ?');
            $updStmt->execute(['on', $this->userId]);
            $this->assertTrue($check2faMethod->invoke(null, $email), 'When two_factor_auth is on, Login::isMemberTwoFactorEnabled must return true.');

            // Simulate BaseController::membersData() projection via formatPerson
            $ref = new \ReflectionMethod(\App\controller\BaseController::class, 'formatPerson');
            $ref->setAccessible(true);
            $formatted = $ref->invoke(null, [
                'id' => $this->userId,
                'firstName' => 'TwoFactor',
                'lastName' => 'Tester',
                'email' => $email,
                'two_factor_auth' => 'on',
                'profile_visibility' => 'family',
                'show_my_profile' => 'off',
            ]);

            $this->assertIsArray($formatted);
            $this->assertSame('on', $formatted['two_factor_auth'] ?? null);
            $this->assertSame('family', $formatted['profile_visibility'] ?? null);
            $this->assertSame('off', $formatted['show_my_profile'] ?? null);
        } finally {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$this->userId]);
            $this->pdo->prepare('DELETE FROM account WHERE id = ?')->execute([$this->userId]);
        }
    }
}


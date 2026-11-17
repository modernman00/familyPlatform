<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\auth\FamilyCodeApprovalController;
use App\controller\BaseController;
use App\controller\members\ChildAccountController;
use PHPUnit\Framework\TestCase;
use Src\JwtHandler;

final class AuthAuditHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_COOKIE = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['HTTP_SEC_PURPOSE'], $_SERVER['HTTP_PURPOSE'], $_SERVER['HTTP_X_MOZ'], $_SERVER['HTTP_USER_AGENT']);
    }

    public function test_rehydrate_session_rejects_password_recovery_jwt(): void
    {
        $jwt = JwtHandler::jwtEncodeData([
            'id' => '999999',
            'email' => 'recovery@example.com',
            'role' => 'password_recovery',
            'token_version' => 1,
        ], 900);

        $cookieName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $_COOKIE[$cookieName] = $jwt;

        BaseController::rehydrateSession();

        $this->assertArrayNotHasKey('id', $_SESSION);
        $this->assertArrayNotHasKey('loggedIn', $_SESSION);
    }

    public function test_rehydrate_session_rejects_jwt_containing_password_hash(): void
    {
        $jwt = JwtHandler::jwtEncodeData([
            'id' => '999999',
            'email' => 'recovery@example.com',
            'password' => '$argon2id$v=19$m=65536,t=4,p=1$fakehash',
            'role' => 'users',
            'token_version' => 1,
        ], 86400);

        $cookieName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $_COOKIE[$cookieName] = $jwt;

        BaseController::rehydrateSession();

        $this->assertArrayNotHasKey('id', $_SESSION);
        $this->assertArrayNotHasKey('loggedIn', $_SESSION);
    }

    public function test_validate_csrf_accepts_underscore_token_when_token_is_reset_token(): void
    {
        $csrf = bin2hex(random_bytes(32));
        $_SESSION['token'] = $csrf;
        $_POST = [
            '_token' => $csrf,
            'token' => 'password_reset_token_value',
        ];

        BaseController::validateCsrf($_POST);
        $this->assertTrue(true);
    }

    public function test_child_account_controller_allows_unauthenticated_login_method(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $csrf = bin2hex(random_bytes(32));
        $_SESSION['token'] = $csrf;
        $_POST = [
            'token' => $csrf,
            'family_code' => '',
            'username' => '',
            'pin' => '',
        ];

        ob_start();
        (new ChildAccountController())->login();
        $out = (string) ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertStringContainsString('required', (string) ($decoded['message'] ?? ''));
    }

    public function test_family_approval_blocks_prefetch_scanner_get_requests(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_SEC_PURPOSE'] = 'prefetch';

        ob_start();
        (new FamilyCodeApprovalController())->approveRequest(123456);
        $out = (string) ob_get_clean();

        $this->assertStringContainsString('Please open this link in your browser to confirm.', $out);
    }

    public function test_complete_registration_rejects_unauthenticated_or_missing_csrf(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['token'] = bin2hex(random_bytes(32));

        ob_start();
        (new FamilyCodeApprovalController())->completeRegistration();
        $out = (string) ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('error', $decoded);
    }

    public function test_code_verify_rejects_missing_csrf_token(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['auth'] = [
            'identifyCust' => '999999',
            '2FA_token_ts' => time(),
            'flow' => 'login',
        ];
        $_POST = [
            'code' => 'ABCDEF',
        ];

        ob_start();
        (new \App\controller\login\Code())->verify();
        $out = (string) ob_get_clean();

        $decoded = json_decode($out, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertArrayNotHasKey('loggedIn', $_SESSION);
    }
}

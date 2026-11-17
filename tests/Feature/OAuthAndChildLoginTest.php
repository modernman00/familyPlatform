<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\controller\login\OAuthController;
use App\controller\members\ChildAccountController;
use App\services\ChildAccountService;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class OAuthAndChildLoginTest extends TestCase
{
    private PDO $pdo;
    private string $famCode;
    private string $parentId;
    private string $userId;
    private ?int $childNodeId = null;
    private ?string $childUserId = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->famCode = 'PUOAUTH_' . strtoupper(bin2hex(random_bytes(3)));
        $this->parentId = 'PU_PAR_' . bin2hex(random_bytes(4));
        $this->userId = 'PU_OAU_' . bin2hex(random_bytes(4));

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REMOTE_ADDR'] = '127.0.' . random_int(1, 250) . '.' . random_int(1, 250);
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit/OAuthChildTest';
        $GLOBALS['__testRedirect'] = null;
    }

    protected function tearDown(): void
    {
        $ids = array_values(array_filter([$this->parentId, $this->userId, $this->childUserId]));
        if ($ids !== []) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->pdo->prepare("DELETE FROM code_mgt WHERE id IN ({$placeholders})")->execute($ids);
            $this->pdo->prepare("DELETE FROM contact WHERE id IN ({$placeholders})")->execute($ids);
            $this->pdo->prepare("DELETE FROM personal WHERE id IN ({$placeholders})")->execute($ids);
            $this->pdo->prepare("DELETE FROM account WHERE id IN ({$placeholders})")->execute($ids);
        }
        if ($this->childNodeId !== null) {
            $this->pdo->prepare('DELETE FROM family_nodes WHERE id = ?')->execute([$this->childNodeId]);
        }
        $this->pdo->prepare('DELETE FROM family_nodes WHERE family_code = ?')->execute([$this->famCode]);

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        parent::tearDown();
    }

    private function seedMember(string $userId, string $email, string $status = 'approved', string $twoFactor = 'off', ?string $deletedAt = null): void
    {
        $this->pdo->prepare(
            'INSERT INTO account (id, email, password, status, type, deleted_at) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$userId, $email, password_hash('ValidPass123!', PASSWORD_DEFAULT), $status, 'member', $deletedAt]);

        $this->pdo->prepare(
            'INSERT INTO personal (id, firstName, lastName, famCode, day, month, year, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([$userId, 'Test', 'Member', $this->famCode, 10, 'May', 1990, 'Female']);

        $this->pdo->prepare(
            'INSERT INTO contact (id, email, country, mobile, two_factor_auth, profile_visibility, show_my_profile) VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([$userId, $email, 'United Kingdom', '+447700900111', $twoFactor, 'public', 'on']);
    }

    private function provisionChildFixture(string $username = 'kid_pu', string $pin = '4321'): void
    {
        $this->seedMember($this->parentId, strtolower($this->parentId) . '@example.com');

        $stmtNode = $this->pdo->prepare(
            "INSERT INTO family_nodes (family_code, first_name, last_name, gender, birth_date, birth_year) VALUES (?, 'Tobi', 'Test', 'Male', '2016-05-10', 2016)"
        );
        $stmtNode->execute([$this->famCode]);
        $this->childNodeId = (int) $this->pdo->lastInsertId();

        $res = ChildAccountService::provisionChildAccount(
            parentUserId: $this->parentId,
            childNodeId: $this->childNodeId,
            username: $username,
            pin: $pin,
            dobYear: '2016',
            dobMonth: '05',
            dobDay: '10',
            clientIp: '127.0.0.1'
        );

        $this->assertSame('success', $res['status'] ?? null);
        $this->childUserId = (string) ($res['child']['user_id'] ?? '');
        $this->assertNotSame('', $this->childUserId);
    }

    // ---- POST /login/child ---------------------------------------------

    public function test_child_login_succeeds_with_provisioned_db_child_record(): void
    {
        $this->provisionChildFixture('tobi_pu', '2468');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = ['token' => 'child-csrf-token'];
        $_POST = [
            'token' => 'child-csrf-token',
            'family_code' => $this->famCode,
            'username' => 'tobi_pu',
            'pin' => '2468',
        ];

        ob_start();
        (new ChildAccountController())->login();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded, 'Expected JSON response, got: ' . $output);
        $this->assertSame('success', $decoded['status'] ?? null);
        $this->assertSame('/member/profilePage', $decoded['redirect'] ?? null);
        $this->assertTrue((bool) ($_SESSION['loggedIn'] ?? false));
        $this->assertTrue((bool) ($_SESSION['is_child_account'] ?? false));
        $this->assertSame($this->childUserId, $_SESSION['id'] ?? null);
    }

    public function test_child_login_rejects_wrong_pin_with_401_error(): void
    {
        $this->provisionChildFixture('tobi_pu2', '2468');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = ['token' => 'child-csrf-token'];
        $_POST = [
            'token' => 'child-csrf-token',
            'family_code' => $this->famCode,
            'username' => 'tobi_pu2',
            'pin' => '9999',
        ];

        ob_start();
        (new ChildAccountController())->login();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertStringContainsStringIgnoringCase('invalid', (string) ($decoded['message'] ?? ''));
        $this->assertFalse($_SESSION['loggedIn'] ?? false);
    }

    public function test_child_login_rejects_missing_fields_with_400_error(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION = ['token' => 'child-csrf-token'];
        $_POST = [
            'token' => 'child-csrf-token',
            'family_code' => $this->famCode,
            'username' => '',
            'pin' => '',
        ];

        ob_start();
        (new ChildAccountController())->login();
        $output = (string) ob_get_clean();

        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['status'] ?? null);
        $this->assertStringContainsStringIgnoringCase('required', (string) ($decoded['message'] ?? ''));
    }

    // ---- OAuthController State & Callback Flow -------------------------

    public function test_oauth_signed_state_round_trip_restores_invite_and_claim_node(): void
    {
        $controller = new OAuthController();
        $buildState = new \ReflectionMethod(OAuthController::class, 'buildSignedState');
        $buildState->setAccessible(true);
        $unpackState = new \ReflectionMethod(OAuthController::class, 'assertAndUnpackState');
        $unpackState->setAccessible(true);

        $_GET = [
            'invite' => 'inv_tok_123',
            'claim_node' => '42',
        ];
        /** @var string $signedState */
        $signedState = $buildState->invoke($controller);
        $this->assertStringContainsString('.', $signedState);

        $_SESSION = [];
        $_GET = ['state' => $signedState];
        /** @var array{invite_token: ?string, ref_token: ?string, claim_node: ?int} $context */
        $context = $unpackState->invoke($controller);

        $this->assertSame('inv_tok_123', $context['invite_token']);
        $this->assertSame(42, $context['claim_node']);
        $this->assertSame('inv_tok_123', $_SESSION['invite_token'] ?? null);
    }

    public function test_oauth_existing_approved_user_binds_provider_id_and_logs_in(): void
    {
        $email = strtolower($this->userId) . '@example.com';
        $this->seedMember($this->userId, $email, 'approved', 'off');

        $controller = new OAuthController();
        $handleSocial = new \ReflectionMethod(OAuthController::class, 'handleSocialLogin');
        $handleSocial->setAccessible(true);

        $handleSocial->invoke($controller, $email, 'Test', 'Member', 'google-sub-999', 'google', true, []);

        $this->assertSame('/profilePage', $GLOBALS['__testRedirect'] ?? null);
        $this->assertTrue($_SESSION['loggedIn'] ?? false);
        $this->assertSame($this->userId, $_SESSION['id'] ?? null);

        $stmt = $this->pdo->prepare('SELECT google_id FROM account WHERE id = ?');
        $stmt->execute([$this->userId]);
        $this->assertSame('google-sub-999', $stmt->fetchColumn());
    }

    public function test_oauth_existing_user_with_2fa_enabled_redirects_to_login_code(): void
    {
        $email = strtolower($this->userId) . '@example.com';
        $this->seedMember($this->userId, $email, 'approved', 'on');

        $controller = new OAuthController();
        $handleSocial = new \ReflectionMethod(OAuthController::class, 'handleSocialLogin');
        $handleSocial->setAccessible(true);

        $handleSocial->invoke($controller, $email, 'Test', 'Member', 'fb-sub-777', 'facebook', true, []);

        $this->assertSame('/login/code', $GLOBALS['__testRedirect'] ?? null);
        $this->assertSame($this->userId, $_SESSION['auth']['pendingUser']['id'] ?? null);
        $this->assertFalse($_SESSION['loggedIn'] ?? false);

        $stmt = $this->pdo->prepare('SELECT facebook_id FROM account WHERE id = ?');
        $stmt->execute([$this->userId]);
        $this->assertSame('fb-sub-777', $stmt->fetchColumn());
    }

    public function test_oauth_pending_deletion_user_within_grace_period_is_restored_and_logged_in(): void
    {
        $email = strtolower($this->userId) . '@example.com';
        $recentDeletedAt = date('Y-m-d H:i:s', time() - 86400);
        $this->seedMember($this->userId, $email, 'pending_deletion', 'off', $recentDeletedAt);

        $controller = new OAuthController();
        $handleSocial = new \ReflectionMethod(OAuthController::class, 'handleSocialLogin');
        $handleSocial->setAccessible(true);

        $handleSocial->invoke($controller, $email, 'Test', 'Member', 'apple-sub-555', 'apple', true, []);

        $this->assertSame('/profilePage', $GLOBALS['__testRedirect'] ?? null);
        $this->assertTrue($_SESSION['loggedIn'] ?? false);

        $stmt = $this->pdo->prepare('SELECT status, deleted_at, apple_id FROM account WHERE id = ?');
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('approved', $row['status'] ?? null);
        $this->assertArrayHasKey('deleted_at', $row);
        $this->assertNull($row['deleted_at']);
        $this->assertSame('apple-sub-555', $row['apple_id'] ?? null);
    }

    public function test_oauth_new_user_sets_pending_session_and_redirects_to_register_with_context(): void
    {
        $email = 'brandnew_' . strtolower($this->userId) . '@example.com';

        $controller = new OAuthController();
        $handleSocial = new \ReflectionMethod(OAuthController::class, 'handleSocialLogin');
        $handleSocial->setAccessible(true);

        $handleSocial->invoke(
            $controller,
            $email,
            'Grace',
            'Hopper',
            'google-new-123',
            'google',
            true,
            ['invite_token' => 'inv_abc', 'claim_node' => 77]
        );

        $this->assertSame('/register?oauth=1&invite=inv_abc&claim_node=77', $GLOBALS['__testRedirect'] ?? null);
        $this->assertSame($email, $_SESSION['oauth_pending']['email'] ?? null);
        $this->assertSame('google', $_SESSION['oauth_pending']['provider'] ?? null);
        $this->assertSame('google-new-123', $_SESSION['oauth_pending']['providerId'] ?? null);
    }

    public function test_oauth_verify_and_parse_apple_id_token_with_signed_rs256_jwt(): void
    {
        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($res);
        $details = openssl_pkey_get_details($res);
        $this->assertIsArray($details);
        $privateKey = '';
        $this->assertTrue(openssl_pkey_export($res, $privateKey));

        $b64url = static function (string $bin): string {
            return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
        };

        $kid = 'pu-apple-kid-' . bin2hex(random_bytes(4));
        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => $kid,
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n'   => $b64url((string) $details['rsa']['n']),
                    'e'   => $b64url((string) $details['rsa']['e']),
                ],
            ],
        ];

        $cacheDir = dirname(__DIR__, 2) . '/bootstrap/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }
        $cacheFile = $cacheDir . '/apple_jwks.json';
        $hadExisting = file_exists($cacheFile);
        $backup = $hadExisting ? file_get_contents($cacheFile) : null;

        try {
            file_put_contents($cacheFile, json_encode($jwks));
            touch($cacheFile, time());

            $expectedAud = (string) ($_ENV['APPLE_CLIENT_ID'] ?? getenv('APPLE_CLIENT_ID') ?: 'com.myfamilyplatform.web');
            $now = time();
            $jwt = \Firebase\JWT\JWT::encode([
                'iss'            => 'https://appleid.apple.com',
                'aud'            => $expectedAud,
                'exp'            => $now + 300,
                'iat'            => $now,
                'sub'            => 'apple-sub-verified-001',
                'email'          => 'apple.verified@example.com',
                'email_verified' => true,
            ], $privateKey, 'RS256', $kid);

            $controller = new OAuthController();
            $verifyMethod = new \ReflectionMethod(OAuthController::class, 'verifyAndParseAppleIdToken');
            $verifyMethod->setAccessible(true);

            /** @var array{sub: string, email: ?string, email_verified: bool} $claims */
            $claims = $verifyMethod->invoke($controller, $jwt);

            $this->assertSame('apple-sub-verified-001', $claims['sub']);
            $this->assertSame('apple.verified@example.com', $claims['email']);
            $this->assertTrue($claims['email_verified']);
        } finally {
            if ($hadExisting && is_string($backup)) {
                file_put_contents($cacheFile, $backup);
            } elseif (file_exists($cacheFile)) {
                @unlink($cacheFile);
            }
        }
    }

    public function test_oauth_handle_social_login_rejects_unverified_email_with_409_and_suspended_with_403(): void
    {
        $approvedEmail = strtolower($this->userId) . '@example.com';
        $this->seedMember($this->userId, $approvedEmail, 'approved', 'off');

        $suspendedId = 'PU_SUS_' . bin2hex(random_bytes(4));
        $suspendedEmail = strtolower($suspendedId) . '@example.com';
        $this->seedMember($suspendedId, $suspendedEmail, 'suspended', 'off');

        try {
            $runCase = static function (string $email, string $providerId, bool $verified): string {
                $emailCode = var_export($email, true);
                $pidCode = var_export($providerId, true);
                $verifiedCode = $verified ? 'true' : 'false';
                $script = <<<PHP
require 'tests/bootstrap.php';
\$c = new App\controller\login\OAuthController();
\$m = new ReflectionMethod(\$c, 'handleSocialLogin');
\$m->setAccessible(true);
\$m->invoke(\$c, {$emailCode}, 'Test', 'Member', {$pidCode}, 'facebook', {$verifiedCode}, []);
PHP;
                $proc = proc_open([PHP_BINARY, '-r', $script], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__, 2));
                $out = is_resource($proc) ? (string) stream_get_contents($pipes[1]) : '';
                $err = is_resource($proc) ? (string) stream_get_contents($pipes[2]) : '';
                if (is_resource($proc)) {
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                }
                return $out . ($err !== '' ? "\nSTDERR:" . $err : '');
            };

            $unverifiedOut = $runCase($approvedEmail, 'fb-unverified-1', false);
            $this->assertStringContainsString('An account already exists for this email', $unverifiedOut);

            $suspendedOut = $runCase($suspendedEmail, 'fb-suspended-1', true);
            $this->assertStringContainsString('Your account is currently suspended and cannot sign in.', $suspendedOut);
        } finally {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$suspendedId]);
            $this->pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$suspendedId]);
            $this->pdo->prepare('DELETE FROM account WHERE id = ?')->execute([$suspendedId]);
        }
    }
}

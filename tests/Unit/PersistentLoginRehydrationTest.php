<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\controller\BaseController;
use Firebase\JWT\JWT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Src\Db;

class PersistentLoginRehydrationTest extends TestCase
{
    private string $originalCookieToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalCookieToken = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($_COOKIE[$this->originalCookieToken]);
        parent::tearDown();
    }

    #[Test]
    public function rehydrateSessionSucceedsWithValidCookie(): void
    {
        $stmt = Db::connect2()->query("SELECT * FROM account WHERE email = 'cypress_test@myfamilyplatform.com' LIMIT 1");
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertIsArray($user, 'Test account cypress_test@myfamilyplatform.com must exist');

        unset($user['password']);

        $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $jwtKey = (string) ($_ENV['JWT_KEY'] ?? $_ENV['JWT_TOKEN'] ?? '');

        $jwt = JWT::encode([
            'iss' => $_ENV['APP_URL'] ?? 'http://localhost',
            'aud' => $_ENV['APP_URL'] ?? 'http://localhost',
            'iat' => time(),
            'exp' => time() + 2592000,
            'data' => $user,
            'sub' => (string) $user['id'],
            'role' => 'users',
            'token_version' => (int) ($user['token_version'] ?? 1),
        ], $jwtKey, 'HS256');

        $_COOKIE[$tokenName] = $jwt;
        $_SESSION = [];

        BaseController::rehydrateSession();

        $this->assertSame((string) $user['id'], $_SESSION['id'] ?? null);
        $this->assertSame((string) $user['id'], $_SESSION['auth']['identifyCust'] ?? null);
        $this->assertTrue($_SESSION['loggedIn'] ?? false);
        $this->assertNotEmpty($_SESSION['famCode'] ?? null);
    }

    #[Test]
    public function rehydrateSessionRejectsForgedSignature(): void
    {
        $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';

        $forgedJwt = JWT::encode([
            'iss' => 'http://localhost',
            'aud' => 'http://localhost',
            'iat' => time(),
            'exp' => time() + 2592000,
            'data' => ['id' => 'FORGED_USER', 'email' => 'hacker@example.com'],
            'sub' => 'FORGED_USER',
            'token_version' => 1,
        ], 'invalid_secret_key_12345678901234567890', 'HS256');

        $_COOKIE[$tokenName] = $forgedJwt;
        $_SESSION = [];

        BaseController::rehydrateSession();

        $this->assertEmpty($_SESSION['id'] ?? null);
        $this->assertEmpty($_SESSION['auth'] ?? null);
    }

    #[Test]
    public function rehydrateSessionRejectsRevokedTokenVersion(): void
    {
        $stmt = Db::connect2()->query("SELECT * FROM account WHERE email = 'cypress_test@myfamilyplatform.com' LIMIT 1");
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertIsArray($user);

        unset($user['password']);
        $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        $jwtKey = (string) ($_ENV['JWT_KEY'] ?? $_ENV['JWT_TOKEN'] ?? '');

        // Token has outdated token_version
        $user['token_version'] = 0;
        $jwt = JWT::encode([
            'iss' => 'http://localhost',
            'aud' => 'http://localhost',
            'iat' => time(),
            'exp' => time() + 2592000,
            'data' => $user,
            'sub' => (string) $user['id'],
            'token_version' => 0,
        ], $jwtKey, 'HS256');

        $_COOKIE[$tokenName] = $jwt;
        $_SESSION = [];

        BaseController::rehydrateSession();

        $this->assertEmpty($_SESSION['id'] ?? null);
    }

    #[Test]
    public function baseControllerConstructTrustsActiveSessionWithoutCookie(): void
    {
        $tokenName = $_ENV['COOKIE_TOKEN_LOGIN'] ?? 'familyCookie';
        unset($_COOKIE[$tokenName]);

        // Pre-populate active session
        $_SESSION['id'] = '397755OLUSOLA';
        $_SESSION['famCode'] = 'SHO';

        // Instantiate controller directly - must NOT throw NotFoundException or UnauthorisedException
        $controller = new class extends BaseController {
            public function test(): string {
                return 'ok';
            }
        };

        $this->assertSame('ok', $controller->test());
        $this->assertSame('397755OLUSOLA', $_SESSION['id']);
        $this->assertSame('SHO', $_SESSION['famCode']);
    }
}


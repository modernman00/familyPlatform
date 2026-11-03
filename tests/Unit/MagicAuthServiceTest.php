<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\services\MagicAuthService;
use PHPUnit\Framework\TestCase;
use Src\Db;

final class MagicAuthServiceTest extends TestCase
{
    private string $testUserId = 'test_magic_user_99';
    private string $testAdminId = 'test_magic_admin_99';

    protected function setUp(): void
    {
        parent::setUp();
        $db = Db::connect2();

        // Clean up test rows using strictly parameterized PDO statements
        $delToken = $db->prepare('DELETE FROM magic_login_tokens WHERE user_id IN (?, ?)');
        $delToken->execute([$this->testUserId, $this->testAdminId]);

        $delPersonal = $db->prepare('DELETE FROM personal WHERE id IN (?, ?)');
        $delPersonal->execute([$this->testUserId, $this->testAdminId]);

        $delAccount = $db->prepare('DELETE FROM account WHERE id IN (?, ?)');
        $delAccount->execute([$this->testUserId, $this->testAdminId]);

        // Insert test member account
        $insAcc = $db->prepare('
            INSERT INTO account (id, email, password, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $insAcc->execute([$this->testUserId, 'test_magic@myfamilyplatform.com', 'hashed_pw', 'member', 'approved']);

        // Insert test admin account
        $insAdmin = $db->prepare('
            INSERT INTO account (id, email, password, type, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $insAdmin->execute([$this->testAdminId, 'test_admin@myfamilyplatform.com', 'hashed_pw', 'admin', 'approved']);

        // Insert test personal record
        $insPers = $db->prepare('
            INSERT INTO personal (id, firstName, lastName, famCode, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ');
        $insPers->execute([$this->testUserId, 'MagicTester', 'Family', 'MAGICTEST']);
    }

    protected function tearDown(): void
    {
        $db = Db::connect2();
        $delToken = $db->prepare('DELETE FROM magic_login_tokens WHERE user_id IN (?, ?)');
        $delToken->execute([$this->testUserId, $this->testAdminId]);

        $delPersonal = $db->prepare('DELETE FROM personal WHERE id IN (?, ?)');
        $delPersonal->execute([$this->testUserId, $this->testAdminId]);

        $delAccount = $db->prepare('DELETE FROM account WHERE id IN (?, ?)');
        $delAccount->execute([$this->testUserId, $this->testAdminId]);

        parent::tearDown();
    }

    public function testGenerateAndConsumeValidMagicToken(): void
    {
        $token = MagicAuthService::generateToken($this->testUserId, '/organogram?action=welcome', 72);
        $this->assertSame(64, strlen($token));
        $this->assertTrue(ctype_xdigit($token));

        $result = MagicAuthService::consume($token);
        $this->assertTrue($result['success']);
        $this->assertSame($this->testUserId, $result['user_id']);
        $this->assertSame('test_magic@myfamilyplatform.com', $result['email']);
        $this->assertSame('/organogram?action=welcome', $result['destination']);
    }

    public function testTokenReplayAttackFails(): void
    {
        $token = MagicAuthService::generateToken($this->testUserId, '/profilePage', 72);

        // First consumption succeeds
        $res1 = MagicAuthService::consume($token);
        $this->assertTrue($res1['success']);

        // Second consumption must fail
        $res2 = MagicAuthService::consume($token);
        $this->assertFalse($res2['success']);
        $this->assertSame('token_already_consumed', $res2['error']);
    }

    public function testInvalidFormatTokenFails(): void
    {
        $res = MagicAuthService::consume('short_invalid_token');
        $this->assertFalse($res['success']);
        $this->assertSame('invalid_token_format', $res['error']);
    }

    public function testNonExistentTokenFails(): void
    {
        $fakeToken = str_repeat('a', 64);
        $res = MagicAuthService::consume($fakeToken);
        $this->assertFalse($res['success']);
        $this->assertSame('token_not_found', $res['error']);
    }

    public function testOpenRedirectNeutralisation(): void
    {
        // Malicious absolute URL
        $clean1 = MagicAuthService::sanitizeDestination('https://evil-phishing.com/steal', '/profilePage');
        $this->assertSame('/profilePage', $clean1);

        // Malicious protocol-relative URL
        $clean2 = MagicAuthService::sanitizeDestination('//evil-phishing.com', '/profilePage');
        $this->assertSame('/profilePage', $clean2);

        // Malicious javascript URI
        $clean3 = MagicAuthService::sanitizeDestination('javascript:alert(1)', '/profilePage');
        $this->assertSame('/profilePage', $clean3);

        // Valid relative paths
        $clean4 = MagicAuthService::sanitizeDestination('/organogram?action=add_first', '/profilePage');
        $this->assertSame('/organogram?action=add_first', $clean4);

        $clean5 = MagicAuthService::sanitizeDestination('/roundtable', '/profilePage');
        $this->assertSame('/roundtable', $clean5);
    }

    public function testAdminPrivilegeEscalationIsPrevented(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Administrative accounts are strictly prohibited from magic link authentication');

        MagicAuthService::generateToken($this->testAdminId, '/admin/dashboard');
    }
}

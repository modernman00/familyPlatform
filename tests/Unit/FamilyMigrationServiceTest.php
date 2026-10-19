<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\services\FamilyMigrationService;
use PHPUnit\Framework\TestCase;
use Src\Db;
use PDO;

final class FamilyMigrationServiceTest extends TestCase
{
    private PDO $pdo;
    private FamilyMigrationService $service;

    /** @var array<int, string> */
    private array $cleanUserIds = [];

    /** @var array<int, string> */
    private array $cleanFamilyCodes = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Db::connect2();
        $this->service = new FamilyMigrationService($this->pdo);
    }

    protected function tearDown(): void
    {
        // Clean up test data cleanly to avoid database pollution
        if (!empty($this->cleanUserIds)) {
            $inUser = implode(',', array_fill(0, count($this->cleanUserIds), '?'));
            $this->pdo->prepare("DELETE FROM family_migration_requests WHERE requester_id IN ($inUser) OR target_user_id IN ($inUser)")->execute(array_merge($this->cleanUserIds, $this->cleanUserIds));
            $this->pdo->prepare("DELETE FROM personal WHERE id IN ($inUser)")->execute($this->cleanUserIds);
            $this->pdo->prepare("DELETE FROM account WHERE id IN ($inUser)")->execute($this->cleanUserIds);
            $this->pdo->prepare("DELETE FROM code_mgt WHERE id IN ($inUser)")->execute($this->cleanUserIds);
            $this->pdo->prepare("DELETE FROM user_families WHERE user_id IN ($inUser)")->execute($this->cleanUserIds);
            $this->pdo->prepare("DELETE FROM family_nodes WHERE user_id IN ($inUser)")->execute($this->cleanUserIds);
        }

        if (!empty($this->cleanFamilyCodes)) {
            $inFam = implode(',', array_fill(0, count($this->cleanFamilyCodes), '?'));
            $this->pdo->prepare("DELETE FROM user_families WHERE family_code IN ($inFam)")->execute($this->cleanFamilyCodes);
            $this->pdo->prepare("DELETE FROM family_nodes WHERE family_code IN ($inFam)")->execute($this->cleanFamilyCodes);
        }

        parent::tearDown();
    }

    private function provisionUser(string $userId, string $famCode, string $firstName, string $lastName, string $email): void
    {
        $this->cleanUserIds[] = $userId;
        $this->cleanFamilyCodes[] = $famCode;

        // Account
        $stmtAcc = $this->pdo->prepare('INSERT INTO account (id, email, password, status, created_at) VALUES (?, ?, "hash123", "active", NOW()) ON DUPLICATE KEY UPDATE email = VALUES(email)');
        $stmtAcc->execute([$userId, $email]);

        // Personal
        $stmtP = $this->pdo->prepare('INSERT INTO personal (id, famCode, firstName, lastName, created_at) VALUES (?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE famCode = VALUES(famCode), firstName = VALUES(firstName), lastName = VALUES(lastName)');
        $stmtP->execute([$userId, $famCode, $firstName, $lastName]);

        // Code mgt
        $stmtC = $this->pdo->prepare('INSERT INTO code_mgt (id, code) VALUES (?, ?) ON DUPLICATE KEY UPDATE code = VALUES(code)');
        $stmtC->execute([$userId, $famCode]);

        // User families
        $stmtUf = $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member") ON DUPLICATE KEY UPDATE status = "approved"');
        $stmtUf->execute([$userId, $famCode]);
    }

    public function test_nudge_creates_pending_request_and_prevents_duplicate(): void
    {
        $famA = 'MIG_A_' . strtoupper(bin2hex(random_bytes(2)));
        $famB = 'MIG_B_' . strtoupper(bin2hex(random_bytes(2)));
        $userA = 'user_miga_' . bin2hex(random_bytes(4));
        $userB = 'user_migb_' . bin2hex(random_bytes(4));

        $this->provisionUser($userA, $famA, 'Abiola', 'Olaogun', "abiola_{$userA}@test.com");
        $this->provisionUser($userB, $famB, 'Tunde', 'Olaogun', "tunde_{$userB}@test.com");

        // 1. Send nudge from userA to userB
        $res = $this->service->createNudge($userA, $userB, 'Hey cousin! Join our family tree.');
        $this->assertTrue($res['success'], $res['message']);
        $this->assertArrayHasKey('migration_id', $res);
        $this->assertArrayHasKey('token', $res);

        $migrationId = (int)$res['migration_id'];
        $req = $this->service->getMigrationRequest($migrationId);
        $this->assertNotNull($req);
        $this->assertSame('nudge', $req['type']);
        $this->assertSame('pending', $req['status']);
        $this->assertSame($userA, $req['requester_id']);
        $this->assertSame($userB, $req['target_user_id']);
        $this->assertSame($famA, $req['new_family_code']);
        $this->assertSame($famB, $req['old_family_code']);

        // 2. Prevent duplicate pending nudge
        $dup = $this->service->createNudge($userA, $userB, 'Duplicate nudge');
        $this->assertFalse($dup['success']);
        $this->assertStringContainsString('already', $dup['message']);

        // 3. Prevent self-nudge
        $self = $this->service->createNudge($userA, $userA);
        $this->assertFalse($self['success']);
    }

    public function test_accept_nudge_executes_atomic_migration(): void
    {
        $famA = 'MIG_X_' . strtoupper(bin2hex(random_bytes(2)));
        $famB = 'MIG_Y_' . strtoupper(bin2hex(random_bytes(2)));
        $userA = 'user_migx_' . bin2hex(random_bytes(4));
        $userB = 'user_migy_' . bin2hex(random_bytes(4));

        $this->provisionUser($userA, $famA, 'Sarah', 'Kola', "sarah_{$userA}@test.com");
        $this->provisionUser($userB, $famB, 'David', 'Kola', "david_{$userB}@test.com");

        $res = $this->service->createNudge($userA, $userB);
        $migrationId = (int)$res['migration_id'];
        $token = (string)$res['token'];

        // IDOR attack attempt: userA tries to accept on behalf of userB
        $idor = $this->service->acceptNudge($migrationId, $userA, $token);
        $this->assertFalse($idor['success']);

        // Tampered token attack
        $tampered = $this->service->acceptNudge($migrationId, $userB, 'invalid_token_123');
        $this->assertFalse($tampered['success']);

        // Legitimate target relative accepts
        $accept = $this->service->acceptNudge($migrationId, $userB, $token);
        $this->assertTrue($accept['success'], $accept['message']);
        $this->assertSame($famA, $accept['family_code']);

        // Verify database state: personal, code_mgt, user_families updated to famA
        $stmtP = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
        $stmtP->execute([$userB]);
        $this->assertSame($famA, $stmtP->fetchColumn());

        $stmtC = $this->pdo->prepare('SELECT code FROM code_mgt WHERE id = ?');
        $stmtC->execute([$userB]);
        $this->assertSame($famA, $stmtC->fetchColumn());

        $stmtUf = $this->pdo->prepare('SELECT status FROM user_families WHERE user_id = ? AND family_code = ?');
        $stmtUf->execute([$userB, $famA]);
        $this->assertSame('approved', $stmtUf->fetchColumn());

        // Verify request record is now approved
        $req = $this->service->getMigrationRequest($migrationId);
        $this->assertSame('approved', $req['status']);
        $this->assertNotNull($req['approved_at']);
    }

    public function test_self_initiated_request_and_approval_workflow(): void
    {
        $famA = 'MIG_M_' . strtoupper(bin2hex(random_bytes(2)));
        $famB = 'MIG_N_' . strtoupper(bin2hex(random_bytes(2)));
        $userA = 'user_miga2_' . bin2hex(random_bytes(4));
        $userB = 'user_migb2_' . bin2hex(random_bytes(4));

        $this->provisionUser($userA, $famA, 'Amina', 'Sterling', "amina_{$userA}@test.com");
        $this->provisionUser($userB, $famB, 'Kwame', 'Sterling', "kwame_{$userB}@test.com");

        // User B discovers family A and requests to migrate into famA
        $reqRes = $this->service->createRequest($userB, $famA, $userA, 'Hello, I want to join our family tree!');
        $this->assertTrue($reqRes['success'], $reqRes['message']);
        $migrationId = (int)$reqRes['migration_id'];
        $token = (string)$reqRes['token'];

        // Verify pending migrations query returns this request for family A members
        $pending = $this->service->getPendingMigrationsForUser($userA, $famA);
        $this->assertNotEmpty($pending['incomingRequests']);
        $this->assertSame($migrationId, (int)$pending['incomingRequests'][0]['id']);

        // Unauthorized user attempts approval (BOLA test)
        $unauthRes = $this->service->approveRequest($migrationId, $userB, $token);
        $this->assertFalse($unauthRes['success']);

        // Authentic family A member approves
        $apprRes = $this->service->approveRequest($migrationId, $userA, $token);
        $this->assertTrue($apprRes['success'], $apprRes['message']);

        // Check User B is now migrated to famA
        $stmtP = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
        $stmtP->execute([$userB]);
        $this->assertSame($famA, $stmtP->fetchColumn());
    }

    public function test_decline_and_rejection(): void
    {
        $famA = 'MIG_D1_' . strtoupper(bin2hex(random_bytes(2)));
        $famB = 'MIG_D2_' . strtoupper(bin2hex(random_bytes(2)));
        $userA = 'user_migd1_' . bin2hex(random_bytes(4));
        $userB = 'user_migd2_' . bin2hex(random_bytes(4));

        $this->provisionUser($userA, $famA, 'Eleanor', 'Cole', "eleanor_{$userA}@test.com");
        $this->provisionUser($userB, $famB, 'Miranda', 'Cole', "miranda_{$userB}@test.com");

        $nudge = $this->service->createNudge($userA, $userB);
        $migrationId = (int)$nudge['migration_id'];
        $token = (string)$nudge['token'];

        // User B declines
        $decRes = $this->service->declineOrReject($migrationId, $userB, $token, 'Not my family branch');
        $this->assertTrue($decRes['success']);

        $req = $this->service->getMigrationRequest($migrationId);
        $this->assertSame('rejected', $req['status']);

        // Subsequent accept fails because it's rejected
        $accept = $this->service->acceptNudge($migrationId, $userB, $token);
        $this->assertFalse($accept['success']);
    }
}

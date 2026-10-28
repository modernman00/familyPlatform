<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\service\FamilyCodeApprovalService;
use App\services\BirthFamilyCodeService;
use DomainException;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * RFC-0004 — create / link a birth (maiden) family code.
 * Runs against the real dev DB (see the Feature suite convention); every row is tracked and removed in tearDown().
 */
final class BirthFamilyCodeTest extends TestCase
{
    private PDO $pdo;
    private BirthFamilyCodeService $service;
    private FamilyCodeApprovalService $approvals;

    /** @var list<string> */
    private array $userIds = [];
    /** @var list<string> */
    private array $codes = [];

    protected function setUp(): void
    {
        $this->pdo = Db::connect2();
        $this->approvals = new FamilyCodeApprovalService($this->pdo);
        $this->service = new BirthFamilyCodeService($this->pdo, $this->approvals);
    }

    protected function tearDown(): void
    {
        foreach ($this->userIds as $id) {
            $this->pdo->prepare('DELETE FROM contact WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM personal WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM account WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM otherFamily WHERE id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM user_families WHERE user_id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM family_nodes WHERE user_id = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM family_codes WHERE created_by = ?')->execute([$id]);
            $this->pdo->prepare('DELETE FROM family_approval_requests WHERE id = ?')->execute([$id]);
        }
        foreach ($this->codes as $code) {
            $this->pdo->prepare('DELETE FROM family_nodes WHERE family_code = ?')->execute([$code]);
            $this->pdo->prepare('DELETE FROM user_families WHERE family_code = ?')->execute([$code]);
            $this->pdo->prepare('DELETE FROM family_codes WHERE code = ?')->execute([$code]);
            $this->pdo->prepare('DELETE FROM family_approval_requests WHERE family_code = ?')->execute([$code]);
        }
        $this->pdo->exec("DELETE FROM family_approval_requests WHERE id LIKE 'test-bf-%'");
        $_SESSION = [];
    }

    /** @return array{id: string, famCode: string, email: string} */
    private function makeUser(string $first, string $last, ?string $famCode = null): array
    {
        $id = 'test-bf-' . bin2hex(random_bytes(5));
        $famCode ??= 'TBF' . random_int(1000, 9999);
        $email = $id . '@test.com';

        $this->pdo->prepare('INSERT INTO account (id, email, password, status) VALUES (?, ?, ?, "active")')
            ->execute([$id, $email, password_hash('x', PASSWORD_BCRYPT)]);
        $this->pdo->prepare('INSERT INTO contact (id, email, mobile, country) VALUES (?, ?, ?, "UK")')
            ->execute([$id, $email, '+4477' . random_int(10000000, 99999999)]);
        $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)')
            ->execute([$id, $first, $last, $famCode]);

        $this->userIds[] = $id;
        $this->codes[] = $famCode;
        return ['id' => $id, 'famCode' => $famCode, 'email' => $email];
    }

    private function storedCode(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT otherFamCode FROM otherFamily WHERE id = ?');
        $stmt->execute([$userId]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    private function expectDomainError(int $status, callable $fn): void
    {
        try {
            $fn();
            $this->fail("Expected DomainException {$status}");
        } catch (DomainException $e) {
            $this->assertSame($status, $e->getCode(), $e->getMessage());
        }
    }

    public function testCreateMintsVerifiedCodeAndLeavesPrimaryUntouched(): void
    {
        $wife = $this->makeUser('Bisi', 'Okafor');

        $result = $this->service->create($wife['id'], 'Adeyemi');
        $code = $result['code'];
        $this->codes[] = $code;

        $this->assertMatchesRegularExpression('/^ADE\d{3}$/', $code);
        $this->assertSame('created', $result['origin']);
        $this->assertSame($code, $this->storedCode($wife['id']));
        $this->assertTrue($this->service->userHoldsCode($wife['id'], $code));

        // Primary household code is never modified.
        $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
        $stmt->execute([$wife['id']]);
        $this->assertSame($wife['famCode'], $stmt->fetchColumn());

        // The code exists platform-wide even though nobody holds it as famCode yet (so her mother can use it).
        $this->assertTrue($this->approvals->familyCodeExists($code));

        $reg = $this->pdo->prepare('SELECT kind, created_by FROM family_codes WHERE code = ?');
        $reg->execute([$code]);
        $this->assertSame(['kind' => 'birth', 'created_by' => $wife['id']], $reg->fetch(PDO::FETCH_ASSOC));

        $role = $this->pdo->prepare('SELECT role, status FROM user_families WHERE user_id = ? AND family_code = ?');
        $role->execute([$wife['id'], $code]);
        $this->assertSame(['role' => 'admin', 'status' => 'approved'], $role->fetch(PDO::FETCH_ASSOC));

        $node = $this->pdo->prepare('SELECT last_name FROM family_nodes WHERE family_code = ? AND user_id = ?');
        $node->execute([$code, $wife['id']]);
        $this->assertSame('Adeyemi', $node->fetchColumn(), 'root node carries the maiden surname');

        $status = $this->service->status($wife['id']);
        $this->assertSame('active', $status['state']);
        $this->assertSame('created', $status['origin']);
    }

    public function testSecondCreateIsRejectedAndCreatedCodeCannotBeCleared(): void
    {
        $wife = $this->makeUser('Bisi', 'Okafor');
        $code = $this->service->create($wife['id'], 'Adeyemi')['code'];
        $this->codes[] = $code;

        $this->expectDomainError(409, fn() => $this->service->create($wife['id'], 'Bakare'));
        $this->expectDomainError(409, fn() => $this->service->clear($wife['id']));
        $this->assertSame($code, $this->storedCode($wife['id']), 'locked code survives a clear attempt');
    }

    /** @return array<string, array{string}> */
    public static function badMaidenNames(): array
    {
        return [
            'empty' => [''],
            'one letter' => ['A'],
            'markup' => ['<script>alert(1)</script>'],
            'digits' => ['Smith99'],
            'too long' => [str_repeat('A', 61)],
        ];
    }

    #[DataProvider('badMaidenNames')]
    public function testInvalidMaidenNameIsRejectedWithoutSideEffects(string $name): void
    {
        $wife = $this->makeUser('Bisi', 'Okafor');

        $this->expectDomainError(422, fn() => $this->service->create($wife['id'], $name));

        $this->assertSame('', $this->storedCode($wife['id']));
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM family_codes WHERE created_by = ?');
        $count->execute([$wife['id']]);
        $this->assertSame(0, (int)$count->fetchColumn());
    }

    public function testFreeTextCodeOfAnotherFamilyCannotBeSelected(): void
    {
        $victim = $this->makeUser('Grace', 'Nwosu');
        $attacker = $this->makeUser('Mallory', 'Evil');

        // PoC: claim someone else's family as "my maiden code".
        $this->expectDomainError(422, fn() => $this->service->select($attacker['id'], $victim['famCode']));
        $this->assertSame('', $this->storedCode($attacker['id']));
        $this->assertFalse($this->service->userHoldsCode($attacker['id'], $victim['famCode']));
    }

    public function testLegacyUnverifiedCodeIsNotTrusted(): void
    {
        $victim = $this->makeUser('Grace', 'Nwosu');
        $legacy = $this->makeUser('Old', 'Row');
        $seed = $this->pdo->prepare('INSERT INTO otherFamily (id, otherFamCode) VALUES (:id, :code)');
        $seed->execute(['id' => $legacy['id'], 'code' => $victim['famCode']]);

        $status = $this->service->status($legacy['id']);
        $this->assertSame('unverified', $status['state']);
        $this->assertFalse($this->service->userHoldsCode($legacy['id'], $victim['famCode']));

        // ...but a legacy user can still create a proper birth family, replacing the unverified value.
        $created = $this->service->create($legacy['id'], 'Nwosu');
        $this->codes[] = $created['code'];
        $this->assertSame($created['code'], $this->storedCode($legacy['id']));
    }

    public function testLinkRequestApprovalAddsMembershipWithoutMovingPrimaryCode(): void
    {
        $mother = $this->makeUser('Grace', 'Adeyemi');
        $wife = $this->makeUser('Bisi', 'Okafor');

        $link = $this->service->requestLink($wife['id'], '#' . strtolower($mother['famCode']), 'Grace', 'Adeyemi', $mother['email']);
        $this->assertSame($mother['famCode'], $link['code']);
        $this->assertSame($mother['id'], (string)$link['inviter']['id']);

        $pending = $this->service->status($wife['id']);
        $this->assertSame('pending', $pending['state']);
        $this->assertSame('', $this->storedCode($wife['id']), 'nothing is stored until a family member approves');

        $this->assertTrue($this->approvals->approveRequest((int)$link['request']['request_id']));

        $status = $this->service->status($wife['id']);
        $this->assertSame('active', $status['state']);
        $this->assertSame('linked', $status['origin']);
        $this->assertSame($mother['famCode'], $this->storedCode($wife['id']));

        $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
        $stmt->execute([$wife['id']]);
        $this->assertSame($wife['famCode'], $stmt->fetchColumn(), 'primary household code is unchanged by a birth link');

        // Unlinking removes the link and membership only.
        $this->service->clear($wife['id']);
        $this->assertSame('none', $this->service->status($wife['id'])['state']);
        $this->assertFalse($this->service->userHoldsCode($wife['id'], $mother['famCode']));
        $this->assertTrue($this->approvals->familyCodeExists($mother['famCode']), "the mother's family is untouched");
    }

    public function testLinkRequestRejectsBadInputsAndWrongInviter(): void
    {
        $mother = $this->makeUser('Grace', 'Adeyemi');
        $wife = $this->makeUser('Bisi', 'Okafor');

        $this->expectDomainError(422, fn() => $this->service->requestLink($wife['id'], 'x', 'Grace', 'Adeyemi', $mother['email']));
        $this->expectDomainError(422, fn() => $this->service->requestLink($wife['id'], $mother['famCode'], '', '', ''));
        $this->expectDomainError(404, fn() => $this->service->requestLink($wife['id'], 'ZZZ9999', 'Grace', 'Adeyemi', $mother['email']));
        $this->expectDomainError(422, fn() => $this->service->requestLink($wife['id'], $wife['famCode'], 'Bisi', 'Okafor', $wife['email']));
        // Right family, but the named person's contact details do not match anyone in it.
        $this->expectDomainError(422, fn() => $this->service->requestLink($wife['id'], $mother['famCode'], 'Grace', 'Adeyemi', 'stranger@test.com'));

        $this->assertSame('none', $this->service->status($wife['id'])['state']);
    }

    public function testFamilyWithNoActiveAccountSaysSoInsteadOfBlamingTheDetails(): void
    {
        $closed = $this->makeUser('Swindon', 'Olaogun');
        $closedArgs = [$closed['id']];
        $this->pdo->prepare("UPDATE account SET status = 'pending_deletion' WHERE id = ?")->execute($closedArgs);
        $wife = $this->makeUser('Bisi', 'Okafor');

        try {
            $this->service->requestLink($wife['id'], $closed['famCode'], 'Swindon', 'Olaogun', $closed['email']);
            $this->fail('expected a 422');
        } catch (DomainException $e) {
            $this->assertSame(422, $e->getCode());
            $this->assertStringContainsString('active account', $e->getMessage());
        }
        $this->assertSame('none', $this->service->status($wife['id'])['state']);
    }

    /**
     * account.deleted_at has `ON UPDATE CURRENT_TIMESTAMP`, so any later UPDATE of an account row stamps it.
     * It is not a delete marker: only status erased / pending_deletion is. Regression for the "Swindon" case.
     */
    public function testStampedDeletedAtDoesNotStopAnActiveMemberApproving(): void
    {
        $mother = $this->makeUser('Swindon', 'Olaogun');
        $motherArgs = [$mother['id']];
        $this->pdo->prepare("UPDATE account SET status = 'approved', deleted_at = '2025-04-25 21:15:56' WHERE id = ?")->execute($motherArgs);
        $wife = $this->makeUser('Bisi', 'Okafor');

        $link = $this->service->requestLink($wife['id'], $mother['famCode'], 'Swindon', 'Olaogun', $mother['email']);

        $this->assertSame($mother['id'], (string)$link['inviter']['id']);
        $this->assertSame('pending', $this->service->status($wife['id'])['state']);
    }

    public function testOnlyOnePendingRequestAtATime(): void
    {
        $mother = $this->makeUser('Grace', 'Adeyemi');
        $wife = $this->makeUser('Bisi', 'Okafor');

        $this->service->requestLink($wife['id'], $mother['famCode'], 'Grace', 'Adeyemi', $mother['email']);
        $this->expectDomainError(409, fn() => $this->service->requestLink($wife['id'], $mother['famCode'], 'Grace', 'Adeyemi', $mother['email']));
    }

    public function testCreateForUnknownUserFailsCleanly(): void
    {
        $this->expectDomainError(404, fn() => $this->service->create('test-bf-ghost-' . bin2hex(random_bytes(4)), 'Adeyemi'));
    }

    /** Chaos: a database failure mid-transaction must leave no registry, membership or stored-code rows. */
    public function testMidTransactionFailureRollsBackEverything(): void
    {
        $wife = $this->makeUser('Bisi', 'Okafor');

        $failing = new class(
            'mysql:host=' . ($_ENV['DB_HOST'] ?? 'localhost') . ';dbname=' . ($_ENV['DB_NAME'] ?? 'family'),
            (string)($_ENV['DB_USERNAME'] ?? ''),
            (string)($_ENV['DB_PASSWORD'] ?? '')
        ) extends PDO {
            public function prepare(string $query, array $options = []): \PDOStatement|false
            {
                if (str_contains($query, 'INSERT INTO user_families')) {
                    throw new PDOException('simulated lock wait timeout', 1205);
                }
                return parent::prepare($query, $options);
            }
        };
        $failing->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $service = new BirthFamilyCodeService($failing, new FamilyCodeApprovalService($failing));

        try {
            $service->create($wife['id'], 'Adeyemi');
            $this->fail('expected the simulated failure to propagate');
        } catch (PDOException $e) {
            $this->assertStringContainsString('simulated lock wait timeout', $e->getMessage());
        }

        $this->assertFalse($failing->inTransaction());
        $this->assertSame('', $this->storedCode($wife['id']));
        $reg = $this->pdo->prepare('SELECT COUNT(*) FROM family_codes WHERE created_by = ?');
        $reg->execute([$wife['id']]);
        $this->assertSame(0, (int)$reg->fetchColumn(), 'registry insert was rolled back');
        $uf = $this->pdo->prepare('SELECT COUNT(*) FROM user_families WHERE user_id = ?');
        $uf->execute([$wife['id']]);
        $this->assertSame(0, (int)$uf->fetchColumn());
    }
}

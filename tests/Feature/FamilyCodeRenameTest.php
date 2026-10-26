<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\services\FamilyCodeRenameService;
use DomainException;
use PDO;
use PHPUnit\Framework\TestCase;
use Src\Db;

/**
 * RFC-0005 — creator proposes a new family code; a family poll must pass before it changes for everyone.
 * Runs against the real dev DB (Feature suite convention); every row is tracked and removed in tearDown().
 */
final class FamilyCodeRenameTest extends TestCase
{
    private PDO $pdo;
    private FamilyCodeRenameService $service;

    /** @var list<string> */
    private array $userIds = [];
    /** @var list<string> */
    private array $codes = [];

    protected function setUp(): void
    {
        $this->pdo = Db::connect2();
        $this->service = new FamilyCodeRenameService($this->pdo);
    }

    protected function tearDown(): void
    {
        $codeMarks = $this->codes === [] ? "''" : implode(',', array_fill(0, count($this->codes), '?'));

        $posts = $this->pdo->prepare("SELECT post_no FROM post WHERE postFamCode IN ($codeMarks)");
        $posts->execute($this->codes);
        foreach ($posts->fetchAll(PDO::FETCH_COLUMN) as $postNo) {
            $this->pdo->prepare('DELETE v FROM post_poll_votes v JOIN post_poll_options o ON o.option_id = v.option_id JOIN post_polls p ON p.poll_id = o.poll_id WHERE p.post_no = ?')->execute([$postNo]);
            $this->pdo->prepare('DELETE o FROM post_poll_options o JOIN post_polls p ON p.poll_id = o.poll_id WHERE p.post_no = ?')->execute([$postNo]);
            $this->pdo->prepare('DELETE FROM post_polls WHERE post_no = ?')->execute([$postNo]);
            $this->pdo->prepare('DELETE FROM post WHERE post_no = ?')->execute([$postNo]);
        }
        $this->pdo->prepare("DELETE FROM family_code_rename_requests WHERE old_code IN ($codeMarks) OR new_code IN ($codeMarks)")->execute(array_merge($this->codes, $this->codes));
        $this->pdo->prepare("DELETE FROM family_code_aliases WHERE old_code IN ($codeMarks) OR new_code IN ($codeMarks)")->execute(array_merge($this->codes, $this->codes));
        $this->pdo->prepare("DELETE FROM family_codes WHERE code IN ($codeMarks)")->execute($this->codes);
        $this->pdo->prepare("DELETE FROM family_nodes WHERE family_code IN ($codeMarks)")->execute($this->codes);

        foreach ($this->userIds as $id) {
            foreach (['contact' => 'id', 'personal' => 'id', 'account' => 'id', 'otherFamily' => 'id', 'code_mgt' => 'id', 'user_families' => 'user_id', 'family_nodes' => 'user_id'] as $table => $col) {
                $this->pdo->prepare("DELETE FROM {$table} WHERE {$col} = ?")->execute([$id]);
            }
            $this->pdo->prepare("DELETE FROM notification WHERE receiver_id = ? AND sender_id = 'system'")->execute([$id]);
            $this->pdo->prepare('DELETE FROM post_poll_votes WHERE user_id = ?')->execute([$id]);
        }
        $_SESSION = [];
    }

    private function newCode(): string
    {
        $code = 'TRN' . random_int(1000, 9999);
        $this->codes[] = $code;
        return $code;
    }

    /** @return array{id: string} */
    private function makeUser(string $code, ?string $storedAs = null): array
    {
        $id = 'test-rn-' . bin2hex(random_bytes(5));
        $this->pdo->prepare('INSERT INTO account (id, email, password, status) VALUES (?, ?, ?, "active")')
            ->execute([$id, $id . '@test.com', password_hash('x', PASSWORD_BCRYPT)]);
        $this->pdo->prepare('INSERT INTO contact (id, email, mobile, country) VALUES (?, ?, ?, "UK")')
            ->execute([$id, $id . '@test.com', '+4477' . random_int(10000000, 99999999)]);
        $this->pdo->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)')
            ->execute([$id, 'Rn', 'Tester', $storedAs ?? $code]);
        $this->userIds[] = $id;
        return ['id' => $id];
    }

    /**
     * A family of $size members; the first is the creator (admin + registry creator).
     *
     * @return array{code: string, ids: list<string>}
     */
    private function makeFamily(int $size): array
    {
        $code = $this->newCode();
        $ids = [];
        for ($i = 0; $i < $size; $i++) {
            $ids[] = $this->makeUser($code)['id'];
        }
        $this->pdo->prepare('INSERT INTO family_codes (code, kind, created_by) VALUES (?, "primary", ?)')->execute([$code, $ids[0]]);
        $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "admin")')->execute([$ids[0], $code]);
        for ($i = 1; $i < $size; $i++) {
            $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")')->execute([$ids[$i], $code]);
        }
        return ['code' => $code, 'ids' => $ids];
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

    /** @param list<string> $params */
    private function countRows(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function famCodeOf(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
        $stmt->execute([$userId]);
        return (string)$stmt->fetchColumn();
    }

    /** @return array<string, mixed> */
    private function request(int $id): array
    {
        return (array)$this->service->getRequest($id);
    }

    public function testOnlyTheCreatorCanPropose(): void
    {
        $family = $this->makeFamily(3);

        $this->expectDomainError(403, fn () => $this->service->propose($family['ids'][1], $this->newCode()));
        $this->assertSame($family['code'], $this->famCodeOf($family['ids'][1]));
    }

    public function testLegacyBackfilledCodeResolvesEarliestFounderAndSelfHeals(): void
    {
        $code = $this->newCode();
        $founder = $this->makeUser($code)['id'];
        $second = $this->makeUser($code)['id'];

        // Simulate pre-RFC-0005 registration & backfill: created_by is NULL and both rows have role='member'
        $this->pdo->prepare('INSERT INTO family_codes (code, kind, created_by) VALUES (?, "primary", NULL)')->execute([$code]);
        $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")')->execute([$founder, $code]);
        $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")')->execute([$second, $code]);

        // Second member is still rejected with 403
        $this->expectDomainError(403, fn () => $this->service->propose($second, $this->newCode()));

        // Founder is recognised and self-healed into family_codes.created_by and user_families.role='admin'
        $status = $this->service->statusFor($founder);
        $this->assertTrue($status['is_creator']);
        $this->assertTrue($status['can_propose']);
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM family_codes WHERE code = ? AND created_by = ?', [$code, $founder]));
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM user_families WHERE user_id = ? AND family_code = ? AND role = "admin"', [$founder, $code]));
    }

    public function testRegistrationAndSoloBranchOutRecordCreatorRole(): void
    {
        $founderTables = \App\model\RegisterTableData::createRegisterTable([
            'id' => 'TEST_FOUNDER',
            'firstName' => 'Ada',
            'lastName' => 'Lovelace',
            'famCode' => 'LOVE1234',
            'familyStatus' => 'approved',
        ]);
        $this->assertSame('admin', $founderTables['user_families']['role'] ?? null);

        $inviteeTables = \App\model\RegisterTableData::createRegisterTable([
            'id' => 'TEST_INVITEE',
            'firstName' => 'Charles',
            'lastName' => 'Babbage',
            'famCode' => 'LOVE1234',
            'familyStatus' => 'pending',
        ]);
        $this->assertSame('member', $inviteeTables['user_families']['role'] ?? null);

        // Solo branch-out records created_by in family_codes
        $soloUser = $this->makeUser('TEMP123')['id'];
        $approvalSvc = new \App\service\FamilyCodeApprovalService($this->pdo);
        $assignedCode = $approvalSvc->switchUserToSoloFamily($soloUser, 'Babbage', ['firstName' => 'Charles', 'lastName' => 'Babbage']);
        $this->codes[] = $assignedCode;

        $this->assertSame($soloUser, $this->service->creatorOf($assignedCode));
        $this->assertTrue($this->service->statusFor($soloUser)['can_propose']);
    }

    public function testProposalOpensAPollAndChangesNothingYet(): void
    {
        $family = $this->makeFamily(3);
        $new = $this->newCode();

        $res = $this->service->propose($family['ids'][0], $new);

        $this->assertSame('pending', $res['status']);
        $this->assertSame(3, $res['eligible']);
        foreach ($family['ids'] as $id) {
            $this->assertSame($family['code'], $this->famCodeOf($id), 'code must not change before the vote passes');
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM post_polls WHERE post_no = ?');
        $stmt->execute([$res['post_no']]);
        $this->assertSame(1, (int)$stmt->fetchColumn(), 'the vote is a real feed poll');

        $t = $this->service->tally($this->request($res['request_id']));
        $this->assertSame(['eligible' => 3, 'yes' => 1, 'no' => 0], $t, "creator's proposal counts as their yes");
    }

    public function testMajorityYesAppliesTheNewCodeEverywhereAndNotifiesEveryone(): void
    {
        $family = $this->makeFamily(3);
        [$creator, $second, $third] = $family['ids'];
        $old = $family['code'];
        $new = $this->newCode();

        // Messy legacy storage must be rewritten too: lowercase with a '#'.
        $this->pdo->prepare('UPDATE personal SET famCode = ? WHERE id = ?')->execute(['#' . strtolower($old), $third]);
        $this->pdo->prepare("INSERT INTO family_nodes (family_code, user_id, first_name, last_name) VALUES (?, ?, 'Rn', 'Tester')")->execute([$old, $creator]);

        $res = $this->service->propose($creator, $new);
        $req = $this->request($res['request_id']);

        $this->assertSame('pending', $this->service->castConsentVote($second, (int)$req['no_option_id']));
        // second flips to yes (single choice: the No is replaced, not added)
        $this->assertSame('applied', $this->service->castConsentVote($second, (int)$req['yes_option_id']));

        foreach ($family['ids'] as $id) {
            $this->assertSame($new, $this->famCodeOf($id), 'every member moves to the new code');
        }
        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM user_families WHERE family_code = ?', [$old]));
        $this->assertSame(3, $this->countRows('SELECT COUNT(*) FROM user_families WHERE family_code = ?', [$new]));
        $this->assertSame(1, $this->countRows('SELECT COUNT(*) FROM family_nodes WHERE family_code = ?', [$new]));
        $registryArgs = [$new, $creator];
        $kept = $this->countRows('SELECT COUNT(*) FROM family_codes WHERE code = ? AND created_by = ?', $registryArgs);
        $this->assertSame(1, $kept, 'registry keeps the creator');
        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM family_codes WHERE code = ?', [$old]));

        $post = $this->pdo->prepare('SELECT postFamCode FROM post WHERE post_no = ?');
        $post->execute([$res['post_no']]);
        $this->assertSame($new, $post->fetchColumn(), 'the poll post moves with the family');

        $this->assertSame($new, FamilyCodeRenameService::resolveAlias($this->pdo, $old), 'old links and sessions resolve to the new code');
        $this->assertSame($new, FamilyCodeRenameService::resolveAlias($this->pdo, '#' . strtolower($old)));

        foreach ([$second, $third, $creator] as $uid) {
            $n = $this->pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = 'system' AND notification_name = 'Family code changed'");
            $n->execute([$uid]);
            $this->assertSame(1, (int)$n->fetchColumn(), "member {$uid} is notified");
        }
    }

    public function testSoloFamilyStillGoesThroughThePollButPassesOnTheCreatorsVote(): void
    {
        $family = $this->makeFamily(1);
        $new = $this->newCode();

        $res = $this->service->propose($family['ids'][0], $new);

        $this->assertSame('applied', $res['status']);
        $this->assertSame($new, $this->famCodeOf($family['ids'][0]));
        $this->assertGreaterThan(0, $res['post_no'], 'a poll was still created');
    }

    public function testMajorityNoRejectsAndNothingChanges(): void
    {
        $family = $this->makeFamily(4);
        $res = $this->service->propose($family['ids'][0], $this->newCode());
        $req = $this->request($res['request_id']);

        $this->service->castConsentVote($family['ids'][1], (int)$req['no_option_id']);
        $status = $this->service->castConsentVote($family['ids'][2], (int)$req['no_option_id']);

        $this->assertSame('rejected', $status);
        foreach ($family['ids'] as $id) {
            $this->assertSame($family['code'], $this->famCodeOf($id));
        }
        $this->expectDomainError(409, fn () => $this->service->castConsentVote($family['ids'][3], (int)$req['yes_option_id']));
    }

    public function testOutsidersCannotVote(): void
    {
        $family = $this->makeFamily(3);
        $outsider = $this->makeUser($this->newCode());
        $res = $this->service->propose($family['ids'][0], $this->newCode());
        $req = $this->request($res['request_id']);

        $this->expectDomainError(403, fn () => $this->service->castConsentVote($outsider['id'], (int)$req['yes_option_id']));
        $this->assertSame(['eligible' => 3, 'yes' => 1, 'no' => 0], $this->service->tally($req));
    }

    public function testValidationAndSingleOpenVote(): void
    {
        $family = $this->makeFamily(3);
        $creator = $family['ids'][0];
        $other = $this->makeFamily(1);

        $this->expectDomainError(422, fn () => $this->service->propose($creator, 'ab'));
        $this->expectDomainError(422, fn () => $this->service->propose($creator, 'bad code!'));
        $this->expectDomainError(422, fn () => $this->service->propose($creator, $family['code']));
        $this->expectDomainError(409, fn () => $this->service->propose($creator, $other['code']));

        $this->service->propose($creator, $this->newCode());
        $this->expectDomainError(409, fn () => $this->service->propose($creator, $this->newCode()));
    }

    public function testReservedPlaceholderCodeCannotBeRenamed(): void
    {
        $user = $this->makeUser('TEMP123');
        $params = [$user['id']];
        $this->pdo->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, "TEMP123", "approved", "admin")')->execute($params);

        $this->expectDomainError(422, fn () => $this->service->propose($user['id'], $this->newCode()));
        $this->pdo->prepare('DELETE FROM user_families WHERE user_id = ?')->execute($params);
    }

    public function testCooldownBlocksAnImmediateSecondRename(): void
    {
        $family = $this->makeFamily(1);
        $first = $this->newCode();
        $this->service->propose($family['ids'][0], $first);

        $this->expectDomainError(429, fn () => $this->service->propose($family['ids'][0], $this->newCode()));
    }

    public function testTieAtClosingKeepsTheCode(): void
    {
        $family = $this->makeFamily(4);
        $res = $this->service->propose($family['ids'][0], $this->newCode());
        $req = $this->request($res['request_id']);
        $this->service->castConsentVote($family['ids'][1], (int)$req['no_option_id']);
        $params = [$res['request_id']];
        $this->pdo->prepare('UPDATE family_code_rename_requests SET expires_at = NOW() - INTERVAL 1 DAY WHERE id = ?')->execute($params);

        $this->assertGreaterThanOrEqual(1, $this->service->expireDue());
        $this->assertSame('expired', $this->request($res['request_id'])['status']);
        $this->assertSame($family['code'], $this->famCodeOf($family['ids'][2]));
        $this->expectDomainError(409, fn () => $this->service->castConsentVote($family['ids'][2], (int)$req['yes_option_id']));
    }

    /** Inactive members must not hold the change back: 2 yes, 0 no, 3 silent -> passes when the vote closes. */
    public function testMajorityOfThoseWhoVotedAppliesAtClosingDespiteSilentMembers(): void
    {
        $family = $this->makeFamily(5);
        $new = $this->newCode();
        $res = $this->service->propose($family['ids'][0], $new);
        $req = $this->request($res['request_id']);

        $this->assertSame('pending', $this->service->evaluate($res['request_id']), 'still open: 1 of 5 is not a majority of the family');
        $this->assertSame('pending', $this->service->castConsentVote($family['ids'][1], (int)$req['yes_option_id']), '2 of 5 is not over half either');
        $this->assertSame($family['code'], $this->famCodeOf($family['ids'][4]));

        $params = [$res['request_id']];
        $this->pdo->prepare('UPDATE family_code_rename_requests SET expires_at = NOW() - INTERVAL 1 DAY WHERE id = ?')->execute($params);
        $this->assertSame(1, $this->service->expireDue());

        $this->assertSame('applied', $this->request($res['request_id'])['status']);
        foreach ($family['ids'] as $id) {
            $this->assertSame($new, $this->famCodeOf($id));
        }
    }

    /** The creator's own Yes is not consent from the family. */
    public function testLoneCreatorYesDoesNotCarryTheVoteAtClosing(): void
    {
        $family = $this->makeFamily(5);
        $res = $this->service->propose($family['ids'][0], $this->newCode());
        $params = [$res['request_id']];
        $this->pdo->prepare('UPDATE family_code_rename_requests SET expires_at = NOW() - INTERVAL 1 DAY WHERE id = ?')->execute($params);

        $this->service->expireDue();

        $this->assertSame('expired', $this->request($res['request_id'])['status']);
        foreach ($family['ids'] as $id) {
            $this->assertSame($family['code'], $this->famCodeOf($id));
        }
    }

    /** Applying must also move the voter's live session (Login stores famCodes as a string). */
    public function testApplyUpdatesTheCurrentSession(): void
    {
        $family = $this->makeFamily(3);
        $new = $this->newCode();
        $res = $this->service->propose($family['ids'][0], $new);
        $req = $this->request($res['request_id']);

        $_SESSION = ['id' => $family['ids'][1], 'famCode' => $family['code'], 'famCodes' => $family['code']];
        $this->service->castConsentVote($family['ids'][1], (int)$req['yes_option_id']);

        $this->assertSame($new, $_SESSION['famCode']);
        $this->assertSame($new, $_SESSION['famCodes']);
    }

    /** A stale session on another device is healed by the alias on its next request. */
    public function testAliasLetsStaleCodesResolve(): void
    {
        $family = $this->makeFamily(1);
        $new = $this->newCode();
        $this->service->propose($family['ids'][0], $new);

        $this->assertSame($new, FamilyCodeRenameService::resolveAlias($this->pdo, $family['code']));
        $this->assertSame($new, FamilyCodeRenameService::resolveAlias($this->pdo, $new));
    }

    public function testNoLeadAtClosingKeepsTheCode(): void
    {
        $family = $this->makeFamily(5);
        $res = $this->service->propose($family['ids'][0], $this->newCode());
        $req = $this->request($res['request_id']);
        $this->service->castConsentVote($family['ids'][1], (int)$req['no_option_id']);
        $this->service->castConsentVote($family['ids'][2], (int)$req['no_option_id']);
        // 1 yes vs 2 no of 5: not yet half, so still open; the creator flips nothing, window closes
        $params = [$res['request_id']];
        $this->pdo->prepare('UPDATE family_code_rename_requests SET expires_at = NOW() - INTERVAL 1 DAY WHERE id = ?')->execute($params);
        $this->service->expireDue();

        $this->assertSame('expired', $this->request($res['request_id'])['status']);
        $this->assertSame($family['code'], $this->famCodeOf($family['ids'][3]));
    }

    public function testCreatorCanCancelAnOpenVote(): void
    {
        $family = $this->makeFamily(3);
        $res = $this->service->propose($family['ids'][0], $this->newCode());

        $this->expectDomainError(403, fn () => $this->service->cancel($family['ids'][1]));
        $this->service->cancel($family['ids'][0]);

        $this->assertSame('cancelled', $this->request($res['request_id'])['status']);
        $this->assertTrue($this->service->statusFor($family['ids'][0])['can_propose']);
    }

    /** Chaos: the new code is taken between the proposal and the final vote. Nothing may change. */
    public function testCodeTakenMidVoteFailsSafelyAndLeavesTheFamilyIntact(): void
    {
        $family = $this->makeFamily(3);
        $new = $this->newCode();
        $res = $this->service->propose($family['ids'][0], $new);
        $req = $this->request($res['request_id']);

        $this->pdo->prepare('INSERT INTO family_codes (code, kind, created_by) VALUES (?, "primary", "someone-else")')->execute([$new]);

        $status = $this->service->castConsentVote($family['ids'][1], (int)$req['yes_option_id']);

        $this->assertSame('failed', $status);
        foreach ($family['ids'] as $id) {
            $this->assertSame($family['code'], $this->famCodeOf($id), 'rolled back: old code intact');
        }
        $aliasArgs = [$family['code']];
        $this->assertSame(0, $this->countRows('SELECT COUNT(*) FROM family_code_aliases WHERE old_code = ?', $aliasArgs));
    }

    public function testStatusForNonCreatorHidesProposeButShowsOpenVote(): void
    {
        $family = $this->makeFamily(3);
        $this->service->propose($family['ids'][0], $this->newCode());

        $member = $this->service->statusFor($family['ids'][1]);
        $this->assertFalse($member['is_creator']);
        $this->assertFalse($member['can_propose']);
        $this->assertNotNull($member['open']);
        $this->assertSame(1, $member['open']['yes']);
    }

    /** End to end through the real poll endpoint: a member's Yes vote tips the majority and renames the family. */
    public function testPollVoteEndpointRoutesRenamePollsThroughConsentRules(): void
    {
        $family = $this->makeFamily(3);
        $new = $this->newCode();
        $res = $this->service->propose($family['ids'][0], $new);
        $req = $this->request($res['request_id']);
        $outsider = $this->makeUser($this->newCode());

        $vote = function (string $userId, int $optionId): array {
            $_SESSION = ['id' => $userId, 'token' => 'tok-' . $userId, 'famCode' => $this->famCodeOf($userId)];
            $_POST = ['option_id' => (string)$optionId, 'token' => 'tok-' . $userId];
            $_SERVER['HTTP_X_CSRF_TOKEN'] = 'tok-' . $userId;
            ob_start();
            (new \App\controller\members\EngagementController())->vote();
            $out = (string)ob_get_clean();
            return ['code' => http_response_code(), 'body' => (array)json_decode($out, true)];
        };

        $denied = $vote($outsider['id'], (int)$req['yes_option_id']);
        $this->assertContains($denied['code'], [403, 400], 'outsider is refused');
        $this->assertSame('error', $denied['body']['status'] ?? null);
        $this->assertSame($family['code'], $this->famCodeOf($family['ids'][1]));

        $ok = $vote($family['ids'][1], (int)$req['yes_option_id']);
        $this->assertSame('success', $ok['body']['status'] ?? null, json_encode($ok));
        $this->assertSame($new, $this->famCodeOf($family['ids'][2]), 'majority reached through the endpoint renames everyone');

        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        $_POST = [];
    }
}

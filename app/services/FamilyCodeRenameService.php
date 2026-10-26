<?php
declare(strict_types=1);

namespace App\services;

use App\classes\PushNotificationClass;
use DomainException;
use PDO;

/**
 * RFC-0005: the creator of a primary family code can rename it, but only through a
 * family poll. The poll is an ordinary feed post (post_polls) linked to a
 * `family_code_rename_requests` row; when more members vote Yes than No (decided when
 * the vote closes, or at once when over half of the whole family says Yes), the new code is applied to every member in one transaction and
 * everyone is notified. Without the poll nothing can start, and the apply step is only
 * reachable from evaluate().
 *
 * Failures are thrown as DomainException whose code is the HTTP status to return.
 */
final class FamilyCodeRenameService
{
    public const VOTING_DAYS = 7;
    public const COOLDOWN_DAYS = 30;
    public const YES_TEXT = 'Yes, change it';
    public const NO_TEXT = 'No, keep it';
    /** Shared placeholder codes that must never be renamed. */
    public const RESERVED_CODES = ['TEMP123'];

    /**
     * Every column that stores a family code as a plain value (matched case-insensitively
     * and ignoring a leading '#', because historic rows are not normalised).
     * user_families, family_codes and api_keys need special handling in apply().
     *
     * @var array<int, array{0: string, 1: string}>
     */
    private const CODE_COLUMNS = [
        ['personal', 'famCode'],
        ['personal', 'familyCode2'],
        ['code_mgt', 'code'],
        ['otherFamily', 'otherFamCode'],
        ['post', 'postFamCode'],
        ['events', 'eventCode'],
        ['family_announcements', 'family_code'],
        ['family_journals', 'family_code'],
        ['family_nodes', 'family_code'],
        ['family_unions', 'family_code'],
        ['family_time_capsules', 'family_code'],
        ['family_hashtags', 'family_code'],
        ['family_hashtag_links', 'family_code'],
        ['family_relationship_requests', 'family_code'],
        ['family_approval_requests', 'family_code'],
        ['family_reels', 'famCode'],
        ['sibling', 'famCode'],
        ['invite_tokens', 'family_code'],
        ['kinship_match_dismissals', 'family_code'],
        ['notification_orchestration', 'family_code'],
        ['family_migration_requests', 'old_family_code'],
        ['family_migration_requests', 'new_family_code'],
        ['platform_recommendations', 'new_family_code'],
    ];

    private PDO $pdo;
    /** @var array<string, bool> */
    private array $columnCache = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureSchema();
    }

    public static function normalise(string $code): string
    {
        return strtoupper(preg_replace('/[\s#]+/', '', $code) ?? '');
    }

    /**
     * Follow old -> new aliases (bounded) so stale sessions and shared links resolve to the live code.
     */
    public static function resolveAlias(PDO $pdo, string $code): string
    {
        $current = self::normalise($code);
        if ($current === '') {
            return $current;
        }
        try {
            $stmt = $pdo->prepare('SELECT new_code FROM family_code_aliases WHERE old_code = ? LIMIT 1');
            for ($hops = 0; $hops < 5; $hops++) {
                $stmt->execute([$current]);
                $next = $stmt->fetchColumn();
                if (!is_string($next) || $next === '' || $next === $current) {
                    break;
                }
                $current = $next;
            }
        } catch (\Throwable) {
            // alias table not migrated yet: nothing was ever renamed
        }
        return $current;
    }

    /** The user who created the code: registry creator, else earliest approved admin, else earliest active primary member. */
    public function creatorOf(string $code): string
    {
        $clean = self::normalise($code);
        if ($clean === '') {
            return '';
        }
        $stmt = $this->pdo->prepare('SELECT created_by FROM family_codes WHERE code = ? LIMIT 1');
        $stmt->execute([$clean]);
        $creator = $stmt->fetchColumn();
        if (is_string($creator) && $creator !== '') {
            return $creator;
        }

        $stmt = $this->pdo->prepare(
            "SELECT user_id FROM user_families
             WHERE UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? AND role = 'admin' AND status = 'approved'
             ORDER BY id ASC LIMIT 1"
        );
        $stmt->execute([$clean]);
        $first = $stmt->fetchColumn();
        if (is_string($first) && $first !== '') {
            $this->healCreatorRecord($clean, $first);
            return $first;
        }

        // Legacy/backfilled primary codes where created_by was NULL and all user_families rows defaulted to 'member':
        // resolve the earliest live registrant whose primary family code is $clean.
        $stmtFounder = $this->pdo->prepare(
            "SELECT p.id
             FROM personal p
             JOIN account a ON a.id = p.id AND a.deleted_at IS NULL
             WHERE UPPER(TRIM(REPLACE(p.famCode, '#', ''))) = ?
             ORDER BY p.no ASC LIMIT 1"
        );
        $stmtFounder->execute([$clean]);
        $founder = $stmtFounder->fetchColumn();
        if (is_string($founder) && $founder !== '') {
            $this->healCreatorRecord($clean, $founder);
            return $founder;
        }

        return '';
    }

    /** Persist resolved founder into family_codes and user_families so future lookups are O(1). */
    private function healCreatorRecord(string $cleanCode, string $userId): void
    {
        if (in_array($cleanCode, self::RESERVED_CODES, true)) {
            return;
        }
        try {
            $this->pdo->prepare(
                "INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)
                 ON DUPLICATE KEY UPDATE created_by = IF(created_by IS NULL OR created_by = '', VALUES(created_by), created_by)"
            )->execute([$cleanCode, $userId]);
            $this->pdo->prepare(
                "UPDATE user_families SET role = 'admin'
                 WHERE user_id = ? AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? AND status = 'approved'"
            )->execute([$userId, $cleanCode]);
        } catch (\Throwable) {
            // Best-effort self-heal; never block read paths
        }
    }

    /**
     * Ids of the members whose consent counts: approved members of the code (or whose
     * primary code it is) with a live account.
     *
     * @return array<int, string>
     */
    public function eligibleMembers(string $code): array
    {
        $clean = self::normalise($code);
        if ($clean === '') {
            return [];
        }
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT p.id
             FROM personal p
             JOIN account a ON a.id = p.id AND a.deleted_at IS NULL
             LEFT JOIN user_families uf ON uf.user_id = p.id AND uf.status = 'approved'
               AND UPPER(TRIM(REPLACE(uf.family_code, '#', ''))) = ?
             WHERE UPPER(TRIM(REPLACE(p.famCode, '#', ''))) = ? OR uf.id IS NOT NULL"
        );
        $stmt->execute([$clean, $clean]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    /** True when no family, request or alias already uses (or has ever used) the code. */
    public function isCodeAvailable(string $code): bool
    {
        $clean = self::normalise($code);
        if ($clean === '') {
            return false;
        }
        $queries = [
            "SELECT 1 FROM family_codes WHERE code = ?",
            "SELECT 1 FROM personal WHERE UPPER(TRIM(REPLACE(famCode, '#', ''))) = ? OR UPPER(TRIM(REPLACE(familyCode2, '#', ''))) = ?",
            "SELECT 1 FROM user_families WHERE UPPER(TRIM(REPLACE(family_code, '#', ''))) = ?",
            "SELECT 1 FROM otherFamily WHERE UPPER(TRIM(REPLACE(otherFamCode, '#', ''))) = ?",
            "SELECT 1 FROM family_code_aliases WHERE old_code = ? OR new_code = ?",
            "SELECT 1 FROM family_code_rename_requests WHERE (new_code = ? OR old_code = ?) AND status = 'pending'",
        ];
        foreach ($queries as $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_fill(0, substr_count($sql, '?'), $clean));
            if ($stmt->fetchColumn() !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Start a rename: validates, then opens the consent poll in the family feed.
     *
     * @return array{request_id: int, post_no: int, old_code: string, new_code: string, expires_at: string, eligible: int, status: string}
     */
    public function propose(string $userId, string $newCodeRaw): array
    {
        $this->expireDue();

        $old = $this->primaryCode($userId);
        if ($old === '') {
            throw new DomainException('You do not have a family code to rename.', 422);
        }
        if (in_array($old, self::RESERVED_CODES, true)) {
            throw new DomainException('This is a shared starter code. Branch out to your own family code first.', 422);
        }
        $kind = $this->pdo->prepare('SELECT kind FROM family_codes WHERE code = ? LIMIT 1');
        $kind->execute([$old]);
        if ($kind->fetchColumn() === 'birth') {
            throw new DomainException('Birth family codes cannot be renamed.', 422);
        }
        if ($this->creatorOf($old) !== $userId) {
            $this->log('code_rename.rejected', $userId, ['code' => $old, 'reason' => 'not_creator']);
            throw new DomainException('Only the person who created this family code can propose changing it.', 403);
        }

        $new = self::normalise($newCodeRaw);
        if (preg_match(BirthFamilyCodeService::CODE_PATTERN, $new) !== 1) {
            throw new DomainException('A family code is 4 to 12 letters and numbers.', 422);
        }
        if ($new === $old) {
            throw new DomainException('That is already your family code.', 422);
        }
        if (in_array($new, self::RESERVED_CODES, true) || !$this->isCodeAvailable($new)) {
            throw new DomainException('That code is already taken. Please try another.', 409);
        }
        // Serialise concurrent proposals for this family (double-tap, two tabs): check + insert under one lock.
        $lockName = 'family_code_rename:' . $old;
        $lock = $this->pdo->prepare('SELECT GET_LOCK(?, 5)');
        $lock->execute([$lockName]);
        if ((int)$lock->fetchColumn() !== 1) {
            throw new DomainException('Another change is being started. Please try again in a moment.', 409);
        }
        try {
            return $this->proposeLocked($userId, $old, $new);
        } finally {
            $this->pdo->prepare('SELECT RELEASE_LOCK(?)')->execute([$lockName]);
        }
    }

    /**
     * @return array{request_id: int, post_no: int, old_code: string, new_code: string, expires_at: string, eligible: int, status: string}
     */
    private function proposeLocked(string $userId, string $old, string $new): array
    {
        if ($this->openRequest($old) !== null) {
            throw new DomainException('A family vote on changing the code is already open.', 409);
        }
        if ($this->inCooldown($old)) {
            throw new DomainException('The family code was changed recently. Please wait ' . self::COOLDOWN_DAYS . ' days between changes.', 429);
        }

        $name = $this->displayName($userId);
        $this->pdo->beginTransaction();
        try {
            // expires_at uses the database clock, the same one expireDue() compares against.
            $ins = $this->pdo->prepare(
                'INSERT INTO family_code_rename_requests (old_code, new_code, requested_by, status, expires_at)
                 VALUES (?, ?, ?, "pending", NOW() + INTERVAL ' . self::VOTING_DAYS . ' DAY)'
            );
            $ins->execute([$old, $new, $userId]);
            $requestId = (int)$this->pdo->lastInsertId();
            $exp = $this->pdo->prepare('SELECT expires_at FROM family_code_rename_requests WHERE id = ?');
            $exp->execute([$requestId]);
            $expires = (string)$exp->fetchColumn();

            $message = $this->pendingMessage($name, $old, $new, $expires);
            $postIns = $this->pdo->prepare(
                'INSERT INTO post (id, fullName, postMessage, postFamCode, post_time, post_status, date_created)
                 VALUES (?, ?, ?, ?, ?, "published", NOW())'
            );
            $postIns->execute([$userId, $name, $message, $old, (string)(int)(microtime(true) * 1000)]);
            $postNo = (int)$this->pdo->lastInsertId();

            $this->pdo->prepare('INSERT INTO post_polls (post_no, question, is_active) VALUES (?, ?, 1)')
                ->execute([$postNo, substr("Change our family code from #{$old} to #{$new}?", 0, 255)]);
            $pollId = (int)$this->pdo->lastInsertId();

            $optIns = $this->pdo->prepare('INSERT INTO post_poll_options (poll_id, option_text) VALUES (?, ?)');
            $optIns->execute([$pollId, self::YES_TEXT]);
            $yesId = (int)$this->pdo->lastInsertId();
            $optIns->execute([$pollId, self::NO_TEXT]);
            $noId = (int)$this->pdo->lastInsertId();

            // The creator's proposal is their Yes vote.
            $this->pdo->prepare('INSERT INTO post_poll_votes (option_id, user_id) VALUES (?, ?)')->execute([$yesId, $userId]);

            $this->pdo->prepare(
                'UPDATE family_code_rename_requests SET post_no = ?, poll_id = ?, yes_option_id = ?, no_option_id = ? WHERE id = ?'
            )->execute([$postNo, $pollId, $yesId, $noId, $requestId]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log('code_rename.propose_failed', $userId, ['code' => $old, 'error' => $e->getMessage()]);
            throw new DomainException('Could not start the family vote. Please try again.', 500);
        }

        $this->log('code_rename.proposed', $userId, ['code' => $old, 'new_code' => $new, 'request_id' => $requestId]);

        $status = $this->evaluate($requestId);
        if ($status === 'pending') {
            $this->notifyMembers(
                $this->eligibleMembers($old),
                $userId,
                'Family code vote',
                "{$name} wants to change our family code to #{$new}. Cast your vote - it changes for everyone if more of us vote yes than no.",
                '/profilePage?post_no=' . $postNo . '#post' . $postNo,
                "code-rename-vote-{$requestId}"
            );
        }

        return [
            'request_id' => $requestId,
            'post_no' => $postNo,
            'old_code' => $old,
            'new_code' => $new,
            'expires_at' => $expires,
            'eligible' => count($this->eligibleMembers($status === 'applied' ? $new : $old)),
            'status' => $status,
        ];
    }

    /**
     * The request (any status) a poll option belongs to, or null for an ordinary poll.
     *
     * @return array<string, mixed>|null
     */
    public function requestForOption(int $optionId): ?array
    {
        if ($optionId <= 0) {
            return null;
        }
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_code_rename_requests WHERE yes_option_id = ? OR no_option_id = ? LIMIT 1'
        );
        $stmt->execute([$optionId, $optionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * Cast (or toggle off) a consent vote, enforcing eligibility and a single choice, then
     * re-evaluate. Returns the request status after the vote.
     */
    public function castConsentVote(string $userId, int $optionId): string
    {
        $req = $this->requestForOption($optionId);
        if ($req === null) {
            throw new DomainException('Not a family code vote.', 404);
        }
        $requestId = (int)$req['id'];
        $this->expireDue();
        $req = $this->getRequest($requestId) ?? $req;

        if (($req['status'] ?? '') !== 'pending') {
            throw new DomainException('This family vote is closed.', 409);
        }
        if (!in_array($userId, $this->eligibleMembers((string)$req['old_code']), true)) {
            $this->log('code_rename.rejected', $userId, ['request_id' => $requestId, 'reason' => 'not_eligible']);
            throw new DomainException('Only members of this family can vote.', 403);
        }

        $this->pdo->beginTransaction();
        try {
            $existing = $this->pdo->prepare(
                'SELECT option_id FROM post_poll_votes WHERE user_id = ? AND option_id IN (?, ?)'
            );
            $existing->execute([$userId, (int)$req['yes_option_id'], (int)$req['no_option_id']]);
            $previous = array_map('intval', $existing->fetchAll(PDO::FETCH_COLUMN) ?: []);

            $this->pdo->prepare('DELETE FROM post_poll_votes WHERE user_id = ? AND option_id IN (?, ?)')
                ->execute([$userId, (int)$req['yes_option_id'], (int)$req['no_option_id']]);

            // Re-selecting the same option clears the vote; otherwise this is the user's single choice.
            if (!in_array($optionId, $previous, true)) {
                $this->pdo->prepare('INSERT INTO post_poll_votes (option_id, user_id) VALUES (?, ?)')->execute([$optionId, $userId]);
            }
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log('code_rename.vote_failed', $userId, ['request_id' => $requestId, 'error' => $e->getMessage()]);
            throw new DomainException('Could not record your vote. Please try again.', 500);
        }

        return $this->evaluate($requestId);
    }

    /**
     * @param array<string, mixed> $req
     * @return array{eligible: int, yes: int, no: int}
     */
    public function tally(array $req): array
    {
        $eligible = $this->eligibleMembers((string)$req['old_code']);
        if ($eligible === []) {
            return ['eligible' => 0, 'yes' => 0, 'no' => 0];
        }
        $marks = implode(',', array_fill(0, count($eligible), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT option_id, COUNT(DISTINCT user_id) FROM post_poll_votes
             WHERE option_id IN (?, ?) AND user_id IN ($marks) GROUP BY option_id"
        );
        $stmt->execute(array_merge([(int)$req['yes_option_id'], (int)$req['no_option_id']], $eligible));
        $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        return [
            'eligible' => count($eligible),
            'yes' => (int)($counts[(int)$req['yes_option_id']] ?? 0),
            'no' => (int)($counts[(int)$req['no_option_id']] ?? 0),
        ];
    }

    /**
     * Decide a pending request. The rule is "majority of those who voted", decided when the vote
     * closes; two outcomes cannot be overturned by later votes so they close early:
     *  - more than half of ALL eligible members said yes  -> applied now
     *  - no >= half of all eligible members                -> rejected now (yes can never beat no)
     * Returns the resulting status.
     */
    public function evaluate(int $requestId): string
    {
        $this->expireDue();
        $req = $this->getRequest($requestId);
        if ($req === null) {
            return 'missing';
        }
        if ($req['status'] !== 'pending') {
            return (string)$req['status'];
        }

        $t = $this->tally($req);
        if ($t['eligible'] > 0 && $t['yes'] * 2 > $t['eligible']) {
            return $this->apply($requestId) ? 'applied' : 'failed';
        }
        if ($t['eligible'] === 0 || $t['no'] * 2 >= $t['eligible']) {
            $this->close($req, 'rejected', 'The family voted to keep the current code.');
            return 'rejected';
        }
        return 'pending';
    }

    /**
     * Settle every vote whose window has closed: more yes than no among those who voted applies
     * the change (inactive members do not count against it), provided at least one member besides the
     * creator voted; a tie, a No lead or a lone creator keeps the code.
     * Run lazily on every vote/propose/settings load and by scripts/close_family_code_votes.php.
     */
    public function expireDue(): int
    {
        $stmt = $this->pdo->query("SELECT * FROM family_code_rename_requests WHERE status = 'pending' AND expires_at < NOW()");
        $rows = $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
        $settled = 0;
        foreach ($rows as $row) {
            try {
                $t = $this->tally($row);
                // The creator's own Yes is never enough: at least one other member must have voted
                // (unless the creator is the whole family).
                $enoughVoters = $t['eligible'] === 1 || ($t['yes'] + $t['no']) >= 2;
                if ($enoughVoters && $t['yes'] > $t['no']) {
                    $this->apply((int)$row['id']);
                } else {
                    $this->close($row, 'expired', 'The vote ended without enough yes votes.');
                }
                $settled++;
            } catch (\Throwable $e) {
                $this->log('code_rename.settle_failed', (string)($row['requested_by'] ?? ''), ['request_id' => (int)$row['id'], 'error' => $e->getMessage()]);
            }
        }
        return $settled;
    }

    /** Creator withdraws an open proposal. */
    public function cancel(string $userId): void
    {
        $this->expireDue();
        $old = $this->primaryCode($userId);
        $req = $old !== '' ? $this->openRequest($old) : null;
        if ($req === null) {
            throw new DomainException('There is no open family vote to cancel.', 404);
        }
        if ((string)$req['requested_by'] !== $userId) {
            throw new DomainException('Only the person who proposed the change can cancel it.', 403);
        }
        $this->close($req, 'cancelled', 'The proposal was withdrawn.');
        $this->log('code_rename.cancelled', $userId, ['request_id' => (int)$req['id']]);
    }

    /**
     * Current state for the settings screen.
     *
     * @return array{is_creator: bool, can_propose: bool, code: string, open: ?array<string, mixed>, cooldown: bool}
     */
    public function statusFor(string $userId): array
    {
        $this->expireDue();
        $code = $this->primaryCode($userId);
        $open = $code !== '' ? $this->openRequest($code) : null;
        $isCreator = $code !== '' && !in_array($code, self::RESERVED_CODES, true) && $this->creatorOf($code) === $userId;
        $cooldown = $code !== '' && $this->inCooldown($code);

        if ($open !== null) {
            $open += $this->tally($open);
        }
        return [
            'is_creator' => $isCreator,
            'can_propose' => $isCreator && $open === null && !$cooldown,
            'code' => $code,
            'open' => $open,
            'cooldown' => $cooldown,
        ];
    }

    /** @return array<string, mixed>|null */
    public function getRequest(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_code_rename_requests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * Rewrite the code everywhere in one transaction, then notify every member. Only evaluate()
     * calls this, after the poll has passed.
     */
    private function apply(int $requestId): bool
    {
        $old = '';
        $new = '';
        $members = [];
        $req = null;

        $this->pdo->beginTransaction();
        try {
            $lock = $this->pdo->prepare("SELECT * FROM family_code_rename_requests WHERE id = ? AND status = 'pending' FOR UPDATE");
            $lock->execute([$requestId]);
            $req = $lock->fetch(PDO::FETCH_ASSOC);
            if (!is_array($req)) {
                $this->pdo->rollBack();
                return false;
            }
            $old = (string)$req['old_code'];
            $new = (string)$req['new_code'];

            // The code may have been taken since the proposal; the unique registry key is the final arbiter.
            $taken = $this->pdo->prepare(
                "SELECT 1 FROM family_codes WHERE code = ?
                 UNION SELECT 1 FROM family_code_aliases WHERE old_code = ? OR new_code = ?
                 UNION SELECT 1 FROM personal WHERE UPPER(TRIM(REPLACE(famCode, '#', ''))) = ?"
            );
            $taken->execute([$new, $new, $new, $new]);
            if ($taken->fetchColumn() !== false) {
                $this->pdo->rollBack();
                $this->close($req, 'failed', 'The new code was taken before the change could be applied.');
                return false;
            }

            $members = $this->eligibleMembers($old);

            foreach (self::CODE_COLUMNS as [$table, $column]) {
                if (!$this->columnExists($table, $column)) {
                    continue;
                }
                // Identifiers come from the constant whitelist above, never from input. Every code column
                // uses a case-insensitive collation, so IN (code, #code) is sargable (index range lock, not a
                // full-table scan) and still catches historic '#CODE' / 'code' rows.
                $sql = "UPDATE `{$table}` SET `{$column}` = ? WHERE `{$column}` IN (?, ?)";
                $this->pdo->prepare($sql)->execute([$new, $old, '#' . $old]);
            }
            $this->assertNoStragglers($old);

            // user_families has a unique (user_id, family_code) key: IGNORE then drop any leftover duplicate variant.
            $this->pdo->prepare('UPDATE IGNORE user_families SET family_code = ? WHERE family_code IN (?, ?)')
                ->execute([$new, $old, '#' . $old]);
            $this->pdo->prepare('DELETE FROM user_families WHERE family_code IN (?, ?)')
                ->execute([$old, '#' . $old]);

            // Registry: keep the creator and kind, swap the key.
            $registry = $this->pdo->prepare('UPDATE family_codes SET code = ? WHERE code = ?');
            $registry->execute([$new, $old]);
            if ($registry->rowCount() === 0) {
                $this->pdo->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'primary', ?)")
                    ->execute([$new, (string)$req['requested_by']]);
            }

            $this->renameApiKeyScopes($old, $new);

            $this->pdo->prepare('INSERT INTO family_code_aliases (old_code, new_code, request_id) VALUES (?, ?, ?)')
                ->execute([$old, $new, $requestId]);
            $this->pdo->prepare(
                "UPDATE family_code_rename_requests SET status = 'applied', resolved_at = NOW(), outcome_note = ? WHERE id = ?"
            )->execute(['Family code changed.', $requestId]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log('code_rename.apply_failed', (string)($req['requested_by'] ?? ''), ['request_id' => $requestId, 'error' => $e->getMessage()]);
            if (is_array($req)) {
                $this->close($req, 'failed', 'The change could not be applied and nothing was changed.');
            }
            return false;
        }

        $this->log('code_rename.applied', (string)$req['requested_by'], [
            'request_id' => $requestId, 'code' => $old, 'new_code' => $new, 'members' => count($members),
        ]);

        // Post-commit, best effort: a notification or feed-text failure never undoes the change.
        $this->setPostMessage((int)$req['post_no'], "✅ Our family code is now #{$new}. More of us voted yes than no, so it has been updated for everyone.");
        $this->refreshSession($old, $new);
        $this->notifyMembers(
            $members,
            '',
            'Family code changed',
            "Our family code is now #{$new}. Everything - your tree, feed and invites - has moved over automatically.",
            '/profilePage',
            "code-rename-done-{$requestId}"
        );

        return true;
    }

    /**
     * Safety net for the sargable rewrite: a plain (non-locking) read that fails the whole transaction
     * if any member still carries the old code in a spelling the fast path missed.
     */
    private function assertNoStragglers(string $old): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM personal WHERE UPPER(TRIM(REPLACE(famCode, '#', ''))) = ?"
        );
        $stmt->execute([$old]);
        if ((int)$stmt->fetchColumn() > 0) {
            throw new \RuntimeException('Some members still carry the old code after the rewrite.');
        }
    }

    /** @param array<string, mixed> $req */
    private function close(array $req, string $status, string $note): void
    {
        $upd = $this->pdo->prepare(
            "UPDATE family_code_rename_requests SET status = ?, resolved_at = NOW(), outcome_note = ? WHERE id = ? AND status = 'pending'"
        );
        $upd->execute([$status, $note, (int)$req['id']]);
        if ($upd->rowCount() === 0) {
            return;
        }

        $this->log('code_rename.' . $status, (string)$req['requested_by'], ['request_id' => (int)$req['id'], 'code' => (string)$req['old_code']]);
        $icon = $status === 'failed' ? '⚠️' : '🗳️';
        $this->setPostMessage((int)($req['post_no'] ?? 0), "{$icon} The vote to change our family code to #{$req['new_code']} is closed. {$note}");

        if ($status !== 'cancelled') {
            $this->notifyMembers(
                [(string)$req['requested_by']],
                '',
                'Family code vote closed',
                "The vote to change your family code to #{$req['new_code']} is closed. {$note}",
                '/accountSetting',
                'code-rename-closed-' . (int)$req['id']
            );
        }
    }

    private function setPostMessage(int $postNo, string $message): void
    {
        if ($postNo <= 0) {
            return;
        }
        try {
            $this->pdo->prepare('UPDATE post SET postMessage = ? WHERE post_no = ?')->execute([$message, $postNo]);
        } catch (\Throwable $e) {
            error_log('[FamilyCodeRenameService] post text update failed: ' . $e->getMessage());
        }
    }

    private function renameApiKeyScopes(string $old, string $new): void
    {
        if (!$this->columnExists('api_keys', 'family_codes')) {
            return;
        }
        $stmt = $this->pdo->prepare('SELECT id, family_codes FROM api_keys WHERE family_codes LIKE ?');
        $stmt->execute(['%' . $old . '%']);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $codes = json_decode((string)$row['family_codes'], true);
            if (!is_array($codes)) {
                continue;
            }
            $mapped = array_map(
                static fn($c) => is_string($c) && self::normalise($c) === $old ? $new : $c,
                $codes
            );
            $this->pdo->prepare('UPDATE api_keys SET family_codes = ? WHERE id = ?')
                ->execute([json_encode($mapped), $row['id']]);
        }
    }

    /** Keep the request that applied the rename seeing the new code straight away. */
    private function refreshSession(string $old, string $new): void
    {
        if (!isset($_SESSION)) {
            return;
        }
        if (self::normalise((string)($_SESSION['famCode'] ?? '')) === $old) {
            $_SESSION['famCode'] = $new;
        }
        // Login stores famCodes as a plain string; migrations store an array. Handle both.
        $codes = $_SESSION['famCodes'] ?? null;
        if (is_string($codes) && self::normalise($codes) === $old) {
            $_SESSION['famCodes'] = $new;
        } elseif (is_array($codes)) {
            $_SESSION['famCodes'] = array_values(array_unique(array_map(
                static fn($c) => is_string($c) && self::normalise($c) === $old ? $new : $c,
                $codes
            )));
        }
    }

    /** @param array<int, string> $userIds */
    private function notifyMembers(array $userIds, string $exceptUserId, string $title, string $body, string $url, string $tag): void
    {
        foreach (array_unique($userIds) as $uid) {
            if ($uid === '' || $uid === $exceptUserId) {
                continue;
            }
            try {
                $this->pdo->prepare(
                    "INSERT INTO notification
                     (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                     VALUES ('system', ?, 'Family Platform', ?, 'Family Request', ?, ?, 'new', NOW())"
                )->execute([$uid, $title, $body, $url]);
            } catch (\Throwable $e) {
                error_log('[FamilyCodeRenameService] in-app notification failed: ' . $e->getMessage());
            }
            try {
                PushNotificationClass::sendPushNotification(userId: $uid, message: $body, url: $url, title: $title, tag: $tag);
            } catch (\Throwable $e) {
                error_log('[FamilyCodeRenameService] push failed: ' . $e->getMessage());
            }
        }
    }

    /** @return array<string, mixed>|null */
    private function openRequest(string $oldCode): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM family_code_rename_requests WHERE old_code = ? AND status = 'pending' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$oldCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function inCooldown(string $code): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM family_code_aliases WHERE new_code = ? AND renamed_at > (NOW() - INTERVAL " . self::COOLDOWN_DAYS . " DAY) LIMIT 1"
        );
        $stmt->execute([$code]);
        return $stmt->fetchColumn() !== false;
    }

    private function primaryCode(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return self::normalise((string)($stmt->fetchColumn() ?: ''));
    }

    private function displayName(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = is_array($row) ? trim(((string)$row['firstName']) . ' ' . ((string)$row['lastName'])) : '';
        return $name !== '' ? $name : 'Your family creator';
    }

    private function pendingMessage(string $name, string $old, string $new, string $expires): string
    {
        return "🗳️ {$name} wants to change our family code from #{$old} to #{$new}.\n\n"
            . "If more members vote yes than no, the code updates automatically for everyone and nothing is lost - your tree, posts and invites move over. "
            . 'Vote below. Voting closes ' . date('j M Y', (int)strtotime($expires)) . '.';
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (!isset($this->columnCache[$key])) {
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1'
            );
            $stmt->execute([$table, $column]);
            $this->columnCache[$key] = $stmt->fetchColumn() !== false;
        }
        return $this->columnCache[$key];
    }

    private function ensureSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        try {
            // Only run DDL (which implicitly commits) when a table is genuinely missing.
            $have = $this->pdo->query(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()
                 AND table_name IN ('family_code_rename_requests', 'family_code_aliases')"
            );
            if ($have && (int)$have->fetchColumn() === 2) {
                $checked = true;
                return;
            }
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS `family_code_rename_requests` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                  `old_code` VARCHAR(50) NOT NULL,
                  `new_code` VARCHAR(50) NOT NULL,
                  `requested_by` VARCHAR(255) NOT NULL,
                  `post_no` INT NULL,
                  `poll_id` INT NULL,
                  `yes_option_id` INT NULL,
                  `no_option_id` INT NULL,
                  `status` ENUM('pending','applied','rejected','expired','cancelled','failed') NOT NULL DEFAULT 'pending',
                  `outcome_note` VARCHAR(255) NULL,
                  `expires_at` DATETIME NOT NULL,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `resolved_at` DATETIME NULL,
                  KEY `idx_rename_old_status` (`old_code`, `status`),
                  KEY `idx_rename_new` (`new_code`),
                  KEY `idx_rename_poll` (`poll_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS `family_code_aliases` (
                  `old_code` VARCHAR(50) NOT NULL PRIMARY KEY,
                  `new_code` VARCHAR(50) NOT NULL,
                  `request_id` INT UNSIGNED NULL,
                  `renamed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  KEY `idx_alias_new` (`new_code`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            $checked = true;
        } catch (\Throwable $e) {
            error_log('[FamilyCodeRenameService] ensureSchema: ' . $e->getMessage());
        }
    }

    /** @param array<string, mixed> $ctx */
    private function log(string $event, string $userId, array $ctx = []): void
    {
        error_log((string)json_encode(['event' => $event, 'trace_id' => bin2hex(random_bytes(8)), 'user_id' => $userId] + $ctx));
    }
}

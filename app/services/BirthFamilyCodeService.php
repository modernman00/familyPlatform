<?php
declare(strict_types=1);

namespace App\services;

use App\service\FamilyCodeApprovalService;
use DomainException;
use PDO;

/**
 * RFC-0004: create or link a member's birth / maiden family code.
 *
 * `otherFamily.otherFamCode` is only ever set here (or through
 * FamilyCodeApprovalService::linkBirthFamily on approval), and only for a code the
 * user verifiably holds via an approved `user_families` row. The primary
 * `personal.famCode` is never modified.
 *
 * Failures are thrown as DomainException whose code is the HTTP status to return.
 */
final class BirthFamilyCodeService
{
    public const CODE_PATTERN = '/^[A-Z0-9]{4,12}$/';
    private const MAX_CODE_ATTEMPTS = 5;

    private PDO $pdo;
    private FamilyCodeApprovalService $approvals;

    public function __construct(PDO $pdo, ?FamilyCodeApprovalService $approvals = null)
    {
        $this->pdo = $pdo;
        $this->approvals = $approvals ?? new FamilyCodeApprovalService($pdo);
    }

    public static function normalise(string $code): string
    {
        return strtoupper(preg_replace('/[\s#]+/', '', $code) ?? '');
    }

    /** True when the user has an approved membership of $code (or it is their primary family code). */
    public function userHoldsCode(string $userId, string $code): bool
    {
        $clean = self::normalise($code);
        if ($userId === '' || $clean === '') {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT 1 FROM user_families
                 WHERE user_id = ? AND status = 'approved'
                   AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? LIMIT 1"
            );
            $stmt->execute([$userId, $clean]);
            if ($stmt->fetchColumn() !== false) {
                return true;
            }

            return $this->primaryCode($userId) === $clean;
        } catch (\Throwable $e) {
            $this->log('birth_code.hold_check_failed', $userId, ['error' => $e->getMessage()]);
            return false;
        }
    }

    /** The stored (normalised) birth/maiden code, held or not. */
    public function storedCode(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT otherFamCode FROM otherFamily WHERE id = ? ORDER BY no ASC LIMIT 1');
        $stmt->execute([$userId]);
        return self::normalise((string)($stmt->fetchColumn() ?: ''));
    }

    /**
     * @return array{state: string, code: string, origin: ?string, pending: ?array<string, mixed>}
     *   state: none | pending | active | unverified
     */
    public function status(string $userId): array
    {
        $code = $this->storedCode($userId);

        if ($code !== '') {
            $role = $this->memberRole($userId, $code);
            if ($role !== null) {
                return ['state' => 'active', 'code' => $code, 'origin' => $role === 'admin' ? 'created' : 'linked', 'pending' => null];
            }
        }

        $pending = $this->pendingLink($userId);
        if ($pending !== null) {
            return ['state' => 'pending', 'code' => (string)$pending['family_code'], 'origin' => null, 'pending' => $pending];
        }

        // A stored code nobody verified (legacy free-text value): never trusted.
        return ['state' => $code !== '' ? 'unverified' : 'none', 'code' => $code, 'origin' => null, 'pending' => null];
    }

    /**
     * Path A: mint a brand-new birth family code from the maiden name.
     *
     * @param array<string, mixed> $memberData personal row used to seed the root tree node
     * @return array{code: string, origin: string}
     */
    public function create(string $userId, string $maidenName, array $memberData = []): array
    {
        $maiden = trim(preg_replace('/\s+/u', ' ', $maidenName) ?? '');
        if (preg_match("/^[\\p{L}\\p{M}' \\-]{2,60}$/u", $maiden) !== 1) {
            $this->reject('invalid_maiden_name', $userId);
            throw new DomainException('Enter your maiden (birth family) name using letters only, 2 to 60 characters.', 422);
        }

        $status = $this->status($userId);
        if (in_array($status['state'], ['active', 'pending'], true)) {
            $this->reject('already_has_birth_family', $userId, ['state' => $status['state']]);
            throw new DomainException('You already have a birth family code or a pending link request.', 409);
        }

        $stmt = $this->pdo->prepare('SELECT * FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $person = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($person)) {
            throw new DomainException('Your profile could not be found.', 404);
        }
        $primary = self::normalise((string)($person['famCode'] ?? ''));

        $code = '';
        for ($attempt = 1; $attempt <= self::MAX_CODE_ATTEMPTS && $code === ''; $attempt++) {
            $candidate = self::normalise($this->approvals->generateUniqueSoloFamilyCode($maiden));
            if ($candidate === '' || $candidate === $primary || preg_match(self::CODE_PATTERN, $candidate) !== 1) {
                continue;
            }

            $this->pdo->beginTransaction();
            try {
                $this->pdo->prepare("INSERT INTO family_codes (code, kind, created_by) VALUES (?, 'birth', ?)")
                    ->execute([$candidate, $userId]);

                $this->pdo->prepare(
                    "INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'admin')
                     ON DUPLICATE KEY UPDATE status = 'approved', role = 'admin'"
                )->execute([$userId, $candidate]);

                $this->saveStoredCode($userId, $candidate, $maiden);

                $this->pdo->commit();
                $code = $candidate;
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                // 23000 = duplicate registry code: retry with a fresh candidate. Anything else is a real failure.
                if (!($e instanceof \PDOException) || (string)$e->getCode() !== '23000') {
                    $this->log('birth_code.create_failed', $userId, ['error' => $e->getMessage()]);
                    throw $e;
                }
            }
        }

        if ($code === '') {
            throw new DomainException('We could not reserve a unique code right now. Please try again.', 503);
        }

        $this->addSessionCode($userId, $code);

        // Root node in the new tree, carrying the maiden surname. Non-fatal: it auto-claims on first use.
        try {
            $seed = ['lastName' => $maiden] + $memberData + $person;
            FamilyClaimService::claimOrInitializeNode($code, $userId, $seed);
        } catch (\Throwable $e) {
            $this->log('birth_code.node_init_failed', $userId, ['code' => $code, 'error' => $e->getMessage()]);
        }

        $this->log('birth_code.created', $userId, ['code' => $code]);
        return ['code' => $code, 'origin' => 'created'];
    }

    /**
     * Path B: ask a verified member of an existing family to link this user to it.
     *
     * @return array{request: array<string, mixed>, inviter: array<string, mixed>, code: string}
     */
    public function requestLink(string $userId, string $rawCode, string $inviterFirst, string $inviterLast, string $inviterContact): array
    {
        $code = self::normalise($rawCode);
        if (preg_match(self::CODE_PATTERN, $code) !== 1 || trim($inviterFirst) === '' || trim($inviterLast) === '' || trim($inviterContact) === '') {
            $this->reject('invalid_link_input', $userId);
            throw new DomainException('Enter the family code and the name and email or mobile of a family member who can confirm you.', 422);
        }

        if ($this->status($userId)['state'] === 'active') {
            throw new DomainException('You already have a birth family code. Clear it first if you want to link a different one.', 409);
        }
        if ($this->approvals->getPendingRequestForUser($userId) !== null) {
            throw new DomainException('You already have a pending family request. Cancel it before sending another.', 409);
        }
        if ($this->primaryCode($userId) === $code) {
            throw new DomainException("#{$code} is already your primary family code.", 422);
        }
        if (!$this->approvals->familyCodeExists($code)) {
            $this->reject('unknown_code', $userId, ['code' => $code]);
            throw new DomainException("Family code #{$code} was not found. Check it with your family.", 404);
        }

        $inviter = $this->approvals->findMatchingInviter($code, $inviterFirst, $inviterLast, $inviterContact);
        if (!$inviter || (string)$inviter['id'] === $userId) {
            // Distinguish "wrong details" from "nobody there can approve" (every member's account is closed or never existed).
            $hasActiveMember = $this->approvals->getFamilyMembersForCode($code) !== [];
            $this->reject('inviter_not_matched', $userId, ['code' => $code, 'active_members' => $hasActiveMember]);
            throw new DomainException(
                $hasActiveMember
                    ? "We couldn't match that family member in #{$code}. Check their name and contact details."
                    : "No one in #{$code} has an active account that can approve you yet. Ask a family member to sign up or invite you, or create your birth family code instead.",
                422
            );
        }

        $request = $this->approvals->createApprovalRequest($userId, $code, trim($inviterFirst), trim($inviterLast), trim($inviterContact), 'birth_link');
        $this->approvals->setApproverId((int)$request['request_id'], (string)$inviter['id']);

        $this->log('birth_code.link_requested', $userId, ['code' => $code, 'request_id' => $request['request_id']]);
        return ['request' => $request, 'inviter' => $inviter, 'code' => $code];
    }

    /** Re-select a family the user already holds as their birth/maiden family (also repairs legacy rows). */
    public function select(string $userId, string $rawCode): string
    {
        $code = self::normalise($rawCode);

        if (preg_match(self::CODE_PATTERN, $code) !== 1 || $code === $this->primaryCode($userId) || !$this->userHoldsCode($userId, $code)) {
            $this->reject('code_not_held', $userId, ['code' => $code]);
            throw new DomainException('You can only use a family you created or have been approved into. Use "Create" or "Link" below.', 422);
        }

        $this->saveStoredCode($userId, $code, null);
        $this->addSessionCode($userId, $code);
        return $code;
    }

    /** Removes the link only; the family tree and its members are untouched. */
    public function clear(string $userId): void
    {
        $code = $this->storedCode($userId);
        if ($code === '') {
            return;
        }

        if ($this->memberRole($userId, $code) === 'admin') {
            $this->reject('clear_locked', $userId, ['code' => $code]);
            throw new DomainException('A birth family code you created is permanent. Contact support if it was made by mistake.', 409);
        }

        $isPrimary = $this->primaryCode($userId) === $code;

        $this->pdo->beginTransaction();
        try {
            if (!$isPrimary) {
                $this->pdo->prepare(
                    "DELETE FROM user_families
                     WHERE user_id = ? AND role = 'member' AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ?"
                )->execute([$userId, $code]);
            }
            $this->pdo->prepare('UPDATE otherFamily SET otherFamCode = NULL, updated_at = NOW() WHERE id = ?')
                ->execute([$userId]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->log('birth_code.clear_failed', $userId, ['error' => $e->getMessage()]);
            throw $e;
        }

        if (!$isPrimary && isset($_SESSION['id']) && (string)$_SESSION['id'] === $userId && is_array($_SESSION['famCodes'] ?? null)) {
            $_SESSION['famCodes'] = array_values(array_filter(
                $_SESSION['famCodes'],
                static fn($c): bool => !is_string($c) || self::normalise($c) !== $code
            ));
        }
        $this->log('birth_code.cleared', $userId, ['code' => $code]);
    }

    private function primaryCode(string $userId): string
    {
        $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        return self::normalise((string)($stmt->fetchColumn() ?: ''));
    }

    /** 'admin' | 'member' for an approved membership, null when the user is not an approved member. */
    private function memberRole(string $userId, string $code): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT role FROM user_families
             WHERE user_id = ? AND status = 'approved' AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ?
             ORDER BY role = 'admin' DESC LIMIT 1"
        );
        $stmt->execute([$userId, $code]);
        $role = $stmt->fetchColumn();
        return is_string($role) ? $role : null;
    }

    /** @return array<string, mixed>|null */
    private function pendingLink(string $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM family_approval_requests
                 WHERE id = ? AND status = 'pending' AND purpose = 'birth_link'
                   AND (request_expires_at IS NULL OR request_expires_at > NOW())
                 ORDER BY created_at DESC LIMIT 1"
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? $row : null;
        } catch (\Throwable) {
            return null; // `purpose` column unavailable: no birth-link requests can exist yet
        }
    }

    private function saveStoredCode(string $userId, string $code, ?string $maiden): void
    {
        $chk = $this->pdo->prepare('SELECT no, maiden_name FROM otherFamily WHERE id = ? ORDER BY no ASC LIMIT 1');
        $chk->execute([$userId]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);

        if (is_array($row)) {
            $keepMaiden = trim((string)($row['maiden_name'] ?? '')) !== '' ? (string)$row['maiden_name'] : ($maiden ?? '');
            $this->pdo->prepare("UPDATE otherFamily SET otherFamCode = ?, maiden_name = NULLIF(?, ''), updated_at = NOW() WHERE no = ?")
                ->execute([$code, $keepMaiden, $row['no']]);
        } else {
            $this->pdo->prepare("INSERT INTO otherFamily (id, otherFamCode, maiden_name) VALUES (?, ?, NULLIF(?, ''))")
                ->execute([$userId, $code, $maiden ?? '']);
        }
    }

    private function addSessionCode(string $userId, string $code): void
    {
        if (!isset($_SESSION['id']) || (string)$_SESSION['id'] !== $userId) {
            return;
        }
        $codes = $_SESSION['famCodes'] ?? [];
        $codes = is_array($codes) ? $codes : [$codes];
        if (!in_array($code, $codes, true)) {
            $codes[] = $code;
        }
        $_SESSION['famCodes'] = $codes;
    }

    /** @param array<string, mixed> $ctx */
    private function reject(string $reason, string $userId, array $ctx = []): void
    {
        $this->log('birth_code.rejected', $userId, ['reason' => $reason] + $ctx);
    }

    /** @param array<string, mixed> $ctx */
    private function log(string $event, string $userId, array $ctx = []): void
    {
        error_log((string)json_encode(['event' => $event, 'trace_id' => bin2hex(random_bytes(8)), 'user_id' => $userId] + $ctx));
    }
}

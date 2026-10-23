<?php
declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Src\functionality\SendEmailFunctionality;
use App\classes\PushNotificationClass;
use App\classes\Pusher;
use App\service\FamilyCodeApprovalService;

final class FamilyMigrationService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        } else {
            try {
                $this->pdo = Db::connect2();
            } catch (\Throwable) {
                $this->pdo = (new Db())->connect();
            }
        }
    }

    /**
     * Generate HMAC-SHA256 signature token to secure migration actions and links against enumeration.
     */
    public function generateToken(int $id, string $targetUserId): string
    {
        $secret = (string)($_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: 'family-migration-secret-key-2026');
        return hash_hmac('sha256', "migration:{$id}:{$targetUserId}", $secret);
    }

    /**
     * Verify HMAC token and ensure request has not expired.
     */
    public function verifyToken(int $id, string $targetUserId, string $token): bool
    {
        $expected = $this->generateToken($id, $targetUserId);
        if (!hash_equals($expected, $token)) {
            return false;
        }

        $request = $this->getMigrationRequest($id);
        if (!$request) {
            return false;
        }

        if (!empty($request['request_expires_at'])) {
            $expiresAt = strtotime((string)$request['request_expires_at']);
            if ($expiresAt !== false && $expiresAt < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch a migration request by primary key.
     *
     * @return array<string, mixed>|null
     */
    public function getMigrationRequest(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM family_migration_requests WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * Flow A: An existing family member (requester) discovers a relative (target) with a different code
     * and sends them a nudge to migrate to requester's family network.
     *
     * @return array{success: bool, message: string, migration_id?: int, token?: string}
     */
    public function createNudge(string $requesterId, string $targetUserId, ?string $note = null): array
    {
        $requesterId = trim($requesterId);
        $targetUserId = trim($targetUserId);

        if ($requesterId === '' || $targetUserId === '') {
            return ['success' => false, 'message' => 'Both requester and target relative are required.'];
        }

        if ($requesterId === $targetUserId) {
            return ['success' => false, 'message' => 'You cannot send a migration nudge to yourself.'];
        }

        // Fetch requester info and primary family code
        $stmtReq = $this->pdo->prepare('SELECT p.id, p.firstName, p.lastName, p.famCode, acc.email FROM personal p LEFT JOIN account acc ON acc.id = p.id WHERE p.id = ? LIMIT 1');
        $stmtReq->execute([$requesterId]);
        $requester = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if (!$requester || empty($requester['famCode'])) {
            return ['success' => false, 'message' => 'You must have an assigned family code to send a migration nudge.'];
        }

        $newFamilyCode = strtoupper(trim(str_replace('#', '', (string)$requester['famCode'])));

        // Fetch target relative info
        $stmtTarget = $this->pdo->prepare('SELECT p.id, p.firstName, p.lastName, p.famCode, acc.email FROM personal p LEFT JOIN account acc ON acc.id = p.id WHERE p.id = ? LIMIT 1');
        $stmtTarget->execute([$targetUserId]);
        $target = $stmtTarget->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            return ['success' => false, 'message' => 'Target member profile was not found in the directory.'];
        }

        $oldFamilyCode = strtoupper(trim(str_replace('#', '', (string)($target['famCode'] ?? ''))));

        if ($oldFamilyCode === $newFamilyCode) {
            return ['success' => false, 'message' => 'This relative is already registered under your family code #' . $newFamilyCode . '.'];
        }

        // Prevent duplicate active pending migration nudges/requests
        $stmtCheck = $this->pdo->prepare(
            "SELECT id FROM family_migration_requests 
             WHERE target_user_id = ? AND new_family_code = ? AND status = 'pending' AND (request_expires_at IS NULL OR request_expires_at > NOW())
             LIMIT 1"
        );
        $stmtCheck->execute([$targetUserId, $newFamilyCode]);
        if ($stmtCheck->fetchColumn()) {
            return ['success' => false, 'message' => 'A pending migration invitation is already awaiting response for this relative.'];
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $tempToken = bin2hex(random_bytes(16));

        $cleanNote = $note !== null ? trim($note) : null;
        if ($cleanNote === '') {
            $cleanNote = null;
        }

        $stmtInsert = $this->pdo->prepare(
            "INSERT INTO family_migration_requests 
             (requester_id, target_user_id, old_family_code, new_family_code, approver_id, type, status, message, token, request_expires_at)
             VALUES (?, ?, ?, ?, ?, 'nudge', 'pending', ?, ?, ?)"
        );
        $stmtInsert->execute([
            $requesterId,
            $targetUserId,
            $oldFamilyCode,
            $newFamilyCode,
            $requesterId, // Requester is the pre-approving family member
            $cleanNote,
            $tempToken,
            $expiresAt
        ]);

        $migrationId = (int)$this->pdo->lastInsertId();
        $token = $this->generateToken($migrationId, $targetUserId);

        // Update token to official HMAC token
        $upd = $this->pdo->prepare('UPDATE family_migration_requests SET token = ? WHERE id = ?');
        $upd->execute([$token, $migrationId]);

        // Send multi-channel non-blocking notifications
        $this->notifyTargetAboutNudge($migrationId, $requester, $target, $newFamilyCode, $token, $cleanNote);

        return [
            'success' => true,
            'message' => 'Migration nudge sent successfully. Your relative can now accept to join your family network.',
            'migration_id' => $migrationId,
            'token' => $token
        ];
    }

    /**
     * Flow B: A member (requester) discovers an existing family code and requests to migrate into it.
     * Subject to approval by an approved member of that family.
     *
     * @return array{success: bool, message: string, migration_id?: int, token?: string}
     */
    public function createRequest(string $requesterId, string $targetFamilyCode, ?string $targetUserId = null, ?string $note = null): array
    {
        $requesterId = trim($requesterId);
        $cleanTargetCode = strtoupper(trim(str_replace('#', '', $targetFamilyCode)));

        if ($requesterId === '' || $cleanTargetCode === '') {
            return ['success' => false, 'message' => 'Please provide a valid family code to join.'];
        }

        $approvalService = new FamilyCodeApprovalService($this->pdo);
        if (!$approvalService->familyCodeExists($cleanTargetCode)) {
            return ['success' => false, 'message' => "Family code '#{$cleanTargetCode}' does not exist on the platform."];
        }

        // Fetch requester details
        $stmtReq = $this->pdo->prepare('SELECT p.id, p.firstName, p.lastName, p.famCode, acc.email FROM personal p LEFT JOIN account acc ON acc.id = p.id WHERE p.id = ? LIMIT 1');
        $stmtReq->execute([$requesterId]);
        $requester = $stmtReq->fetch(PDO::FETCH_ASSOC);

        if (!$requester) {
            return ['success' => false, 'message' => 'Requester profile could not be loaded.'];
        }

        $oldFamilyCode = strtoupper(trim(str_replace('#', '', (string)($requester['famCode'] ?? ''))));
        if ($oldFamilyCode === $cleanTargetCode) {
            return ['success' => false, 'message' => "You are already a registered member of family code #{$cleanTargetCode}."];
        }

        // Prevent duplicate active pending migration requests
        $stmtCheck = $this->pdo->prepare(
            "SELECT id FROM family_migration_requests 
             WHERE requester_id = ? AND new_family_code = ? AND status = 'pending' AND (request_expires_at IS NULL OR request_expires_at > NOW())
             LIMIT 1"
        );
        $stmtCheck->execute([$requesterId, $cleanTargetCode]);
        if ($stmtCheck->fetchColumn()) {
            return ['success' => false, 'message' => "You already have an active pending migration request for family code #{$cleanTargetCode}."];
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $tempToken = bin2hex(random_bytes(16));
        $cleanTargetUser = ($targetUserId !== null && trim($targetUserId) !== '') ? trim($targetUserId) : null;

        $cleanNote = $note !== null ? trim($note) : null;
        if ($cleanNote === '') {
            $cleanNote = null;
        }

        $stmtInsert = $this->pdo->prepare(
            "INSERT INTO family_migration_requests 
             (requester_id, target_user_id, old_family_code, new_family_code, approver_id, type, status, message, token, request_expires_at)
             VALUES (?, ?, ?, ?, ?, 'request', 'pending', ?, ?, ?)"
        );
        $stmtInsert->execute([
            $requesterId,
            $requesterId, // Target of migration is requester themselves
            $oldFamilyCode,
            $cleanTargetCode,
            $cleanTargetUser, // Optional specific family member contacted
            $cleanNote,
            $tempToken,
            $expiresAt
        ]);

        $migrationId = (int)$this->pdo->lastInsertId();
        $token = $this->generateToken($migrationId, $requesterId);

        $upd = $this->pdo->prepare('UPDATE family_migration_requests SET token = ? WHERE id = ?');
        $upd->execute([$token, $migrationId]);

        // Send multi-channel non-blocking notifications to target family members
        $this->notifyFamilyAboutRequest($migrationId, $requester, $cleanTargetCode, $cleanTargetUser, $token, $cleanNote);

        return [
            'success' => true,
            'message' => "Your migration request to join family code #{$cleanTargetCode} has been sent and is awaiting family approval.",
            'migration_id' => $migrationId,
            'token' => $token
        ];
    }

    /**
     * Target relative accepts the nudge. Because the sender was an approved member of that family,
     * acceptance immediately executes the atomic migration.
     *
     * @return array{success: bool, message: string, family_code?: string}
     */
    public function acceptNudge(int $migrationId, string $targetUserId, string $token): array
    {
        $targetUserId = trim($targetUserId);

        if (!$this->verifyToken($migrationId, $targetUserId, $token)) {
            return ['success' => false, 'message' => 'Invalid or expired migration authorization token.'];
        }

        $request = $this->getMigrationRequest($migrationId);
        if (!$request) {
            return ['success' => false, 'message' => 'Migration request not found.'];
        }

        if ((string)$request['target_user_id'] !== $targetUserId) {
            return ['success' => false, 'message' => 'You are not authorized to accept this migration.'];
        }

        if ($request['type'] !== 'nudge') {
            return ['success' => false, 'message' => 'This record is not an incoming migration nudge.'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'This migration invitation has already been ' . $request['status'] . '.'];
        }

        $newFamilyCode = (string)$request['new_family_code'];
        $oldFamilyCode = (string)$request['old_family_code'];
        $actorId = (string)($request['requester_id'] ?? $targetUserId);

        // Execute atomic multi-table migration transaction
        $ok = $this->executeAtomicMigration($targetUserId, $oldFamilyCode, $newFamilyCode, $actorId);
        if (!$ok) {
            return ['success' => false, 'message' => 'Failed to complete family migration in database transaction. Please try again.'];
        }

        // Mark record approved
        $upd = $this->pdo->prepare('UPDATE family_migration_requests SET status = "approved", approved_at = NOW() WHERE id = ?');
        $upd->execute([$migrationId]);

        // Send confirmation notifications to both parties
        $this->notifyMigrationCompleted($migrationId, $targetUserId, $newFamilyCode, (string)$request['requester_id']);

        return [
            'success' => true,
            'message' => "Welcome to the family! Your account has been migrated to family code #{$newFamilyCode}.",
            'family_code' => $newFamilyCode
        ];
    }

    /**
     * An approved member of the family approves a user's self-initiated migration request.
     *
     * @return array{success: bool, message: string, family_code?: string}
     */
    public function approveRequest(int $migrationId, string $approverUserId, string $token): array
    {
        $approverUserId = trim($approverUserId);
        $request = $this->getMigrationRequest($migrationId);

        if (!$request) {
            return ['success' => false, 'message' => 'Migration request not found.'];
        }

        $targetUserId = (string)$request['target_user_id'];
        if (!$this->verifyToken($migrationId, $targetUserId, $token)) {
            return ['success' => false, 'message' => 'Invalid or expired migration authorization token.'];
        }

        if ($request['type'] !== 'request') {
            return ['success' => false, 'message' => 'This record is not a join request.'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'This migration request has already been ' . $request['status'] . '.'];
        }

        $newFamilyCode = (string)$request['new_family_code'];
        $oldFamilyCode = (string)$request['old_family_code'];

        // Authorization check: approver must belong to the new family code
        if (!$this->userBelongsToFamily($approverUserId, $newFamilyCode)) {
            return ['success' => false, 'message' => 'Only verified members of family code #' . $newFamilyCode . ' can approve this request.'];
        }

        // Execute atomic migration
        $ok = $this->executeAtomicMigration($targetUserId, $oldFamilyCode, $newFamilyCode, $approverUserId);
        if (!$ok) {
            return ['success' => false, 'message' => 'Database transaction failed during migration approval.'];
        }

        // Mark record approved
        $upd = $this->pdo->prepare('UPDATE family_migration_requests SET status = "approved", approver_id = ?, approved_at = NOW() WHERE id = ?');
        $upd->execute([$approverUserId, $migrationId]);

        // Send confirmation notifications
        $this->notifyMigrationCompleted($migrationId, $targetUserId, $newFamilyCode, $approverUserId);

        return [
            'success' => true,
            'message' => "Successfully approved! Member has been welcomed into family code #{$newFamilyCode}.",
            'family_code' => $newFamilyCode
        ];
    }

    /**
     * Decline a nudge or deny a migration request.
     *
     * @return array{success: bool, message: string}
     */
    public function declineOrReject(int $migrationId, string $actorId, string $token, string $reason = ''): array
    {
        $actorId = trim($actorId);
        $request = $this->getMigrationRequest($migrationId);

        if (!$request) {
            return ['success' => false, 'message' => 'Migration request not found.'];
        }

        $targetUserId = (string)$request['target_user_id'];
        if (!$this->verifyToken($migrationId, $targetUserId, $token)) {
            return ['success' => false, 'message' => 'Invalid or expired authorization token.'];
        }

        if ($request['status'] !== 'pending') {
            return ['success' => false, 'message' => 'This request has already been ' . $request['status'] . '.'];
        }

        // Verify actor is authorized (either target user declining a nudge, or family member denying a request)
        $isAuthorized = false;
        if ($actorId === $targetUserId || $actorId === (string)$request['requester_id']) {
            $isAuthorized = true;
        } elseif ($this->userBelongsToFamily($actorId, (string)$request['new_family_code'])) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            return ['success' => false, 'message' => 'You are not authorized to decline this request.'];
        }

        $upd = $this->pdo->prepare('UPDATE family_migration_requests SET status = "rejected", updated_at = NOW() WHERE id = ?');
        $upd->execute([$migrationId]);

        return [
            'success' => true,
            'message' => 'The migration request has been declined.'
        ];
    }

    /**
     * Core atomic ACID transaction executing the complete user migration across all platform tables.
     */
    public function executeAtomicMigration(string $targetUserId, string $oldFamilyCode, string $newFamilyCode, string $actorId): bool
    {
        $targetUserId = trim($targetUserId);
        $cleanNewCode = strtoupper(trim(str_replace('#', '', $newFamilyCode)));
        $cleanOldCode = strtoupper(trim(str_replace('#', '', $oldFamilyCode)));

        if ($targetUserId === '' || $cleanNewCode === '') {
            return false;
        }

        $wasInTransaction = $this->pdo->inTransaction();
        if (!$wasInTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            // 1. Update personal table (the authoritative source of truth for famCode)
            $stmtPersonal = $this->pdo->prepare('UPDATE personal SET famCode = ? WHERE id = ?');
            $stmtPersonal->execute([$cleanNewCode, $targetUserId]);

            // 2. Update or insert code_mgt table
            $stmtChkCode = $this->pdo->prepare('SELECT no FROM code_mgt WHERE id = ? LIMIT 1');
            $stmtChkCode->execute([$targetUserId]);
            if ($stmtChkCode->fetch()) {
                $stmtCode = $this->pdo->prepare('UPDATE code_mgt SET code = ?, updated_at = NOW() WHERE id = ?');
                $stmtCode->execute([$cleanNewCode, $targetUserId]);
            } else {
                $stmtCode = $this->pdo->prepare('INSERT INTO code_mgt (id, code) VALUES (?, ?)');
                $stmtCode->execute([$targetUserId, $cleanNewCode]);
            }

            // 3. Ensure otherFamily record exists
            $stmtOther = $this->pdo->prepare('INSERT INTO otherFamily (id) VALUES (?) ON DUPLICATE KEY UPDATE id = id');
            $stmtOther->execute([$targetUserId]);

            // 4. Update user_families: set new family code as approved member
            $stmtFam = $this->pdo->prepare(
                'INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")
                 ON DUPLICATE KEY UPDATE status = "approved"'
            );
            $stmtFam->execute([$targetUserId, $cleanNewCode]);

            // 5. Update old family code association if solo or clean up obsolete pending records
            if ($cleanOldCode !== '' && $cleanOldCode !== $cleanNewCode) {
                // If old code was only used by this user, mark it as migrated or remove from active codes
                $checkOtherUsers = $this->pdo->prepare('SELECT COUNT(*) FROM personal WHERE famCode = ? AND id != ?');
                $checkOtherUsers->execute([$cleanOldCode, $targetUserId]);
                $otherUserCount = (int)$checkOtherUsers->fetchColumn();

                if ($otherUserCount === 0) {
                    $updOldFam = $this->pdo->prepare('UPDATE user_families SET status = "pending" WHERE user_id = ? AND family_code = ?');
                    $updOldFam->execute([$targetUserId, $cleanOldCode]);
                }
            }

            // 6. Migrate user's existing posts so they appear in new family feed
            if ($cleanOldCode !== '') {
                $stmtPost = $this->pdo->prepare('UPDATE post SET postFamCode = ? WHERE id = ? AND postFamCode = ?');
                $stmtPost->execute([$cleanNewCode, $targetUserId, $cleanOldCode]);

                $stmtEvents = $this->pdo->prepare('UPDATE events SET eventCode = ? WHERE id = ? AND eventCode = ?');
                $stmtEvents->execute([$cleanNewCode, $targetUserId, $cleanOldCode]);
            }

            // 7. Auto-claim or initialize family node in the new family tree
            try {
                $stmtMember = $this->pdo->prepare('SELECT * FROM personal WHERE id = ?');
                $stmtMember->execute([$targetUserId]);
                $pRow = $stmtMember->fetch(PDO::FETCH_ASSOC);
                $memberData = is_array($pRow) ? $pRow : [];

                FamilyClaimService::claimOrInitializeNode($cleanNewCode, $targetUserId, $memberData);
            } catch (\Throwable $nodeEx) {
                error_log('[FamilyMigrationService] Node claim warning: ' . $nodeEx->getMessage());
            }

            // 8. Commit database transaction
            if (!$wasInTransaction) {
                $this->pdo->commit();
            }

            // 9. Update live PHP session if the migrated user is the currently logged-in user
            if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['id']) && (string)$_SESSION['id'] === $targetUserId) {
                $_SESSION['famCode'] = $cleanNewCode;
                $currentCodes = $_SESSION['famCodes'] ?? [];
                if (!is_array($currentCodes)) {
                    $currentCodes = [$cleanNewCode];
                } elseif (!in_array($cleanNewCode, $currentCodes, true)) {
                    $currentCodes[] = $cleanNewCode;
                }
                $_SESSION['famCodes'] = $currentCodes;
            }

            return true;
        } catch (\Throwable $e) {
            if (!$wasInTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[FamilyMigrationService] executeAtomicMigration failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve pending incoming nudges and requests for a user/family to display on Profile Page.
     *
     * @return array{
     *   incomingNudges: array<int, array<string, mixed>>,
     *   incomingRequests: array<int, array<string, mixed>>,
     *   totalPendingCount: int
     * }
     */
    public function getPendingMigrationsForUser(string $userId, string $famCode): array
    {
        $userId = trim($userId);
        $cleanCode = strtoupper(trim(str_replace('#', '', $famCode)));

        $incomingNudges = [];
        $incomingRequests = [];

        if ($userId === '') {
            return [
                'incomingNudges' => [],
                'incomingRequests' => [],
                'totalPendingCount' => 0
            ];
        }

        try {
            // 1. Incoming Nudges: Invitations sent TO this user asking them to migrate to another family code
            $stmtNudges = $this->pdo->prepare(
                "SELECT fmr.*, 
                        p.firstName AS reqFirstName, p.lastName AS reqLastName, pp.img AS reqImg,
                        target_p.firstName AS targetFirstName, target_p.lastName AS targetLastName
                 FROM family_migration_requests fmr
                 LEFT JOIN personal p ON p.id = fmr.requester_id
                 LEFT JOIN profilePics pp ON pp.id = fmr.requester_id
                 LEFT JOIN personal target_p ON target_p.id = fmr.target_user_id
                 WHERE fmr.target_user_id = ? 
                   AND fmr.type = 'nudge' 
                   AND fmr.status = 'pending'
                   AND (fmr.request_expires_at IS NULL OR fmr.request_expires_at > NOW())
                 ORDER BY fmr.created_at DESC"
            );
            $stmtNudges->execute([$userId]);
            $nudgeRows = $stmtNudges->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($nudgeRows as $row) {
                $row['requesterName'] = trim(($row['reqFirstName'] ?? '') . ' ' . ($row['reqLastName'] ?? '')) ?: 'Family Relative';
                $row['img'] = $row['reqImg'] ?? 'avatarM.png';
                $row['token'] = $this->generateToken((int)$row['id'], (string)$row['target_user_id']);
                $incomingNudges[] = $row;
            }

            // 2. Incoming Requests: Requests from other users wanting to migrate INTO this family code
            if ($cleanCode !== '') {
                $stmtReqs = $this->pdo->prepare(
                    "SELECT fmr.*, 
                            p.firstName AS reqFirstName, p.lastName AS reqLastName, pp.img AS reqImg, c.mobile AS reqMobile, c.email AS reqEmail
                     FROM family_migration_requests fmr
                     LEFT JOIN personal p ON p.id = fmr.requester_id
                     LEFT JOIN profilePics pp ON pp.id = fmr.requester_id
                     LEFT JOIN contact c ON c.id = fmr.requester_id
                     WHERE UPPER(TRIM(REPLACE(fmr.new_family_code, '#', ''))) = ?
                       AND fmr.type = 'request'
                       AND fmr.status = 'pending'
                       AND fmr.requester_id != ?
                       AND (fmr.request_expires_at IS NULL OR fmr.request_expires_at > NOW())
                     ORDER BY fmr.created_at DESC"
                );
                $stmtReqs->execute([$cleanCode, $userId]);
                $reqRows = $stmtReqs->fetchAll(PDO::FETCH_ASSOC) ?: [];

                foreach ($reqRows as $row) {
                    $row['requesterName'] = trim(($row['reqFirstName'] ?? '') . ' ' . ($row['reqLastName'] ?? '')) ?: 'Relative';
                    $row['img'] = $row['reqImg'] ?? 'avatarM.png';
                    $row['token'] = $this->generateToken((int)$row['id'], (string)$row['target_user_id']);
                    $incomingRequests[] = $row;
                }
            }
        } catch (\Throwable $e) {
            error_log('[FamilyMigrationService] getPendingMigrationsForUser failed: ' . $e->getMessage());
        }

        return [
            'incomingNudges' => $incomingNudges,
            'incomingRequests' => $incomingRequests,
            'totalPendingCount' => count($incomingNudges) + count($incomingRequests)
        ];
    }

    /**
     * Check if a specific user belongs to a family code.
     */
    public function userBelongsToFamily(string $userId, string $familyCode): bool
    {
        $cleanCode = strtoupper(trim(str_replace('#', '', $familyCode)));
        if ($userId === '' || $cleanCode === '') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM personal WHERE id = ? AND UPPER(TRIM(REPLACE(famCode, '#', ''))) = ?
             UNION
             SELECT 1 FROM user_families WHERE user_id = ? AND UPPER(TRIM(REPLACE(family_code, '#', ''))) = ? AND status = 'approved'
             LIMIT 1"
        );
        $stmt->execute([$userId, $cleanCode, $userId, $cleanCode]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Send multi-channel notifications to the target relative when nudged.
     *
     * @param array<string, mixed> $requester
     * @param array<string, mixed> $target
     */
    private function notifyTargetAboutNudge(int $migrationId, array $requester, array $target, string $newFamilyCode, string $token, ?string $note): void
    {
        $requesterName = trim(($requester['firstName'] ?? '') . ' ' . ($requester['lastName'] ?? '')) ?: 'A family member';
        $targetId = (string)($target['id'] ?? '');
        $targetEmail = (string)($target['email'] ?? '');

        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

        // 1. In-App durable notification
        try {
            $stmtNotif = $this->pdo->prepare(
                "INSERT INTO notification 
                 (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                 VALUES (?, ?, ?, 'Family Migration Nudge', 'Family Request', ?, ?, 'new', NOW())"
            );
            $msgContent = "{$requesterName} invited you to migrate your account to the #{$newFamilyCode} family network.";
            if ($note) {
                $msgContent .= " Note: \"{$note}\"";
            }
            $stmtNotif->execute([
                (string)($requester['id'] ?? 'system'),
                $targetId,
                $requesterName,
                $msgContent,
                "/profilePage#migrationNudgeCard-{$migrationId}"
            ]);
        } catch (\Throwable $e) {
            error_log('[FamilyMigrationService] Notification table write failed: ' . $e->getMessage());
        }

        // 2. Web Push Notification
        try {
            PushNotificationClass::sendPushNotification(
                userId: $targetId,
                message: "{$requesterName} invited you to migrate to the #{$newFamilyCode} family tree. Tap to review.",
                url: '/profilePage',
                title: 'Family Migration Invitation 🌟',
                tag: "family-nudge-{$migrationId}"
            );
        } catch (\Throwable $e) {
            error_log('[FamilyMigrationService] Push notification failed: ' . $e->getMessage());
        }

        // 3. Email Notification
        if ($targetEmail !== '') {
            try {
                $emailData = [
                    'id' => $targetId,
                    'firstName' => $target['firstName'] ?? 'Relative',
                    'requesterName' => $requesterName,
                    'newFamilyCode' => $newFamilyCode,
                    'note' => $note,
                    'profileUrl' => $baseUrl . '/profilePage',
                    'acceptUrl' => $baseUrl . "/api/family-migration/accept-nudge?id={$migrationId}&token={$token}"
                ];
                SendEmailFunctionality::email(
                    'msg/familyMigrationNudge',
                    "{$requesterName} invited you to join the #{$newFamilyCode} Family Tree",
                    $emailData,
                    'member'
                );
            } catch (\Throwable $e) {
                error_log('[FamilyMigrationService] Nudge email failed: ' . $e->getMessage());
            }
        }

        // 4. Realtime Pusher notification
        try {
            if (class_exists(Pusher::class)) {
                Pusher::broadcastToFamily($newFamilyCode, 'family-migration-nudge', [
                    'migrationId' => $migrationId,
                    'requesterName' => $requesterName,
                    'targetUserId' => $targetId,
                    'newFamilyCode' => $newFamilyCode
                ]);
            }
        } catch (\Throwable) {
            // Non-blocking
        }
    }

    /**
     * Send notifications to the family when an external user requests to migrate into their family code.
     *
     * @param array<string, mixed> $requester
     */
    private function notifyFamilyAboutRequest(int $migrationId, array $requester, string $targetFamilyCode, ?string $targetUserId, string $token, ?string $note): void
    {
        $requesterName = trim(($requester['firstName'] ?? '') . ' ' . ($requester['lastName'] ?? '')) ?: 'A relative';
        $requesterId = (string)($requester['id'] ?? '');

        $approvalService = new FamilyCodeApprovalService($this->pdo);
        $familyMembers = $approvalService->getFamilyMembersForCode($targetFamilyCode);

        $recipients = [];
        if ($targetUserId !== null && $targetUserId !== '') {
            $recipients[] = $targetUserId;
        }
        foreach ($familyMembers as $m) {
            if (!empty($m['id'])) {
                $recipients[] = (string)$m['id'];
            }
        }
        $recipients = array_values(array_unique(array_filter($recipients)));

        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

        // 1. In-App Notification
        try {
            $stmtNotif = $this->pdo->prepare(
                "INSERT INTO notification 
                 (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                 VALUES (?, ?, ?, 'Migration Join Request', 'Family Request', ?, ?, 'new', NOW())"
            );
            $msgContent = "{$requesterName} requested to migrate into your #{$targetFamilyCode} family network.";
            if ($note) {
                $msgContent .= " Note: \"{$note}\"";
            }

            foreach ($recipients as $recId) {
                $stmtNotif->execute([
                    $requesterId,
                    $recId,
                    $requesterName,
                    $msgContent,
                    "/profilePage#migrationReqCard-{$migrationId}"
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[FamilyMigrationService] Request notification table write failed: ' . $e->getMessage());
        }

        // 2. Web Push Notification to family members
        foreach ($recipients as $recId) {
            try {
                PushNotificationClass::sendPushNotification(
                    userId: $recId,
                    message: "{$requesterName} requested to migrate to your #{$targetFamilyCode} family network.",
                    url: '/profilePage',
                    title: 'New Family Migration Request 👋',
                    tag: "family-migration-req-{$migrationId}"
                );
            } catch (\Throwable) {
                // Non-blocking
            }
        }

        // 3. Email Notification to first family member / approver
        if (!empty($familyMembers[0]['email'])) {
            try {
                $emailData = [
                    'id' => (string)$familyMembers[0]['id'],
                    'firstName' => $familyMembers[0]['firstName'] ?? 'Family Member',
                    'requesterName' => $requesterName,
                    'familyCode' => $targetFamilyCode,
                    'note' => $note,
                    'profileUrl' => $baseUrl . '/profilePage',
                    'approveUrl' => $baseUrl . "/api/family-migration/approve-request?id={$migrationId}&token={$token}"
                ];
                SendEmailFunctionality::email(
                    'msg/familyMigrationRequest',
                    "{$requesterName} requested to join the #{$targetFamilyCode} Family Tree",
                    $emailData,
                    'member'
                );
            } catch (\Throwable $e) {
                error_log('[FamilyMigrationService] Request email failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Notify both parties when migration is finalized.
     */
    private function notifyMigrationCompleted(int $migrationId, string $targetUserId, string $newFamilyCode, string $approverId): void
    {
        // 1. Notify the newly migrated user
        try {
            $stmtNotif = $this->pdo->prepare(
                "INSERT INTO notification 
                 (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, notification_status, notification_date)
                 VALUES ('system', ?, 'Family Platform', 'Migration Completed', 'Family Request', ?, 'new', NOW())"
            );
            $stmtNotif->execute([
                $targetUserId,
                "🎉 Congratulations! Your account has successfully migrated to family code #{$newFamilyCode}."
            ]);

            PushNotificationClass::sendPushNotification(
                userId: $targetUserId,
                message: "Congratulations! You have joined the #{$newFamilyCode} family network. Visit your profile to explore.",
                url: '/profilePage',
                title: 'Family Migration Completed 🎉',
                tag: "migration-confirmed-{$migrationId}"
            );
        } catch (\Throwable $e) {
            error_log('[FamilyMigrationService] Completion push to target failed: ' . $e->getMessage());
        }

        // 2. Notify the approver/sender
        if ($approverId !== '' && $approverId !== $targetUserId) {
            try {
                $stmtTarget = $this->pdo->prepare('SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1');
                $stmtTarget->execute([$targetUserId]);
                $targetRow = $stmtTarget->fetch(PDO::FETCH_ASSOC);
                $targetName = trim(($targetRow['firstName'] ?? '') . ' ' . ($targetRow['lastName'] ?? '')) ?: 'Your relative';

                $stmtNotif = $this->pdo->prepare(
                    "INSERT INTO notification 
                     (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, notification_status, notification_date)
                     VALUES ('system', ?, 'Family Platform', 'Relative Migrated', 'Family Request', ?, 'new', NOW())"
                );
                $stmtNotif->execute([
                    $approverId,
                    "🎉 {$targetName} has joined your #{$newFamilyCode} family network!"
                ]);

                PushNotificationClass::sendPushNotification(
                    userId: $approverId,
                    message: "{$targetName} has joined your #{$newFamilyCode} family network!",
                    url: '/profilePage',
                    title: 'New Relative Joined Family 🌳',
                    tag: "migration-welcomed-{$migrationId}"
                );
            } catch (\Throwable $e) {
                error_log('[FamilyMigrationService] Completion push to approver failed: ' . $e->getMessage());
            }
        }
    }
}

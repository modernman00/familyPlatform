<?php

namespace App\service;

use PDO;

class FamilyCodeApprovalService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureSchema();
    }

    /**
     * Ensure the family_approval_requests table exists
     */
    private function ensureSchema(): void
    {
        try {
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS `family_approval_requests` (
                  `no` int NOT NULL AUTO_INCREMENT,
                  `id` varchar(255) NOT NULL,
                  `family_code` varchar(50) NOT NULL,
                  `inviter_first_name` varchar(100) NOT NULL,
                  `inviter_last_name` varchar(100) NOT NULL,
                  `inviter_email_or_mobile` varchar(100) NOT NULL,
                  `approver_id` varchar(255) NULL,
                  `temporary_code` varchar(50) NOT NULL,
                  `status` enum('pending','approved','denied','expired') DEFAULT 'pending',
                  `request_expires_at` timestamp NULL,
                  `reminder_sent_at` timestamp NULL,
                  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                  `approved_at` timestamp NULL,
                  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `deleted_at` timestamp NULL,
                  PRIMARY KEY (`no`),
                  KEY `family_code_idx` (`family_code`),
                  KEY `status_expires_idx` (`status`, `request_expires_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
            );
        } catch (\Throwable $e) {
            // Non-blocking in case of restricted permissions
        }
    }

    /**
     * Check if a family code exists and is valid (resilient to #, case, and O vs 0 typos)
     */
    public function familyCodeExists(string $code): bool
    {
        $clean = strtoupper(trim(str_replace('#', '', $code)));
        if ($clean === '') {
            return false;
        }

        $variations = array_values(array_unique([
            $clean,
            $code,
            (strlen($clean) >= 4) ? substr($clean, 0, 3) . 'O' . substr($clean, 4) : $clean,
            (strlen($clean) >= 4) ? substr($clean, 0, 3) . '0' . substr($clean, 4) : $clean,
            str_replace('0', 'O', $clean),
            str_replace('O', '0', $clean)
        ]));

        $placeholders = implode(',', array_fill(0, count($variations), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM personal 
             WHERE UPPER(TRIM(REPLACE(famCode, '#', ''))) IN ($placeholders) 
                OR UPPER(TRIM(famCode)) IN ($placeholders)"
        );
        $params = array_merge($variations, $variations);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Get family members for a code (to find who to send approval notification to)
     * @return array<int, array<string, mixed>>
     */
    public function getFamilyMembersForCode(string $code): array
    {
        $clean = strtoupper(trim(str_replace('#', '', $code)));
        if ($clean === '') {
            return [];
        }

        $variations = array_values(array_unique([
            $clean,
            $code,
            (strlen($clean) >= 4) ? substr($clean, 0, 3) . 'O' . substr($clean, 4) : $clean,
            (strlen($clean) >= 4) ? substr($clean, 0, 3) . '0' . substr($clean, 4) : $clean,
            str_replace('0', 'O', $clean),
            str_replace('O', '0', $clean)
        ]));

        $placeholders = implode(',', array_fill(0, count($variations), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT a.id, p.firstName, p.lastName, c.email, c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE (UPPER(TRIM(REPLACE(p.famCode, '#', ''))) IN ($placeholders) OR UPPER(TRIM(p.famCode)) IN ($placeholders))
               AND a.deleted_at IS NULL"
        );
        $params = array_merge($variations, $variations);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Generate a temporary family code
     */
    public function generateTemporaryCode(): string
    {
        do {
            $tempCode = 'TEMP_' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->familyCodeExists($tempCode));

        return $tempCode;
    }

    /**
     * Create a pending approval request with signed token
     * @return array<string, mixed>
     */
    public function createApprovalRequest(
        string $userId,
        string $familyCode,
        string $inviterFirstName,
        string $inviterLastName,
        string $inviterEmailOrMobile
    ): array {
        $cleanCode = trim(str_replace('#', '', $familyCode));
        $tempCode = $this->generateTemporaryCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $this->pdo->prepare(
            'INSERT INTO family_approval_requests
             (id, family_code, inviter_first_name, inviter_last_name, inviter_email_or_mobile, temporary_code, status, request_expires_at)
             VALUES (?, ?, ?, ?, ?, ?, "pending", ?)'
        );

        $stmt->execute([
            $userId,
            $cleanCode,
            $inviterFirstName,
            $inviterLastName,
            $inviterEmailOrMobile,
            $tempCode,
            $expiresAt
        ]);

        $requestId = (int)$this->pdo->lastInsertId();
        $approvalToken = $this->generateApprovalToken($requestId);

        return [
            'request_id' => $requestId,
            'temporary_code' => $tempCode,
            'expires_at' => $expiresAt,
            'approval_token' => $approvalToken
        ];
    }

    /**
     * Generate HMAC-signed token for approval link (prevents request ID enumeration)
     */
    public function generateApprovalToken(int $requestId): string
    {
        $secret = $_ENV['APP_KEY'] ?? 'default-secret-key';
        return hash_hmac('sha256', (string)$requestId, $secret);
    }

    /**
     * Verify approval token matches request ID and check request expiration
     */
    public function verifyApprovalToken(int $requestId, string $token): bool
    {
        $expectedToken = $this->generateApprovalToken($requestId);
        if (!hash_equals($expectedToken, $token)) {
            return false;
        }

        $request = $this->getApprovalRequest($requestId);
        if (!$request) {
            return false;
        }

        // Enforce 7-day expiration check
        if (!empty($request['request_expires_at'])) {
            $expiresAt = strtotime((string)$request['request_expires_at']);
            if ($expiresAt !== false && $expiresAt < time()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find matching family member for inviter details
     * Matches by name and email/mobile
     * @return array<string, mixed>|null
     */
    public function findMatchingInviter(
        string $familyCode,
        string $firstName,
        string $lastName,
        string $emailOrMobile
    ): ?array {
        $cleanCode = trim(str_replace('#', '', $familyCode));
        $cleanContact = trim($emailOrMobile);
        $cleanPhoneDigits = preg_replace('/[^0-9]/', '', $cleanContact);

        // Match by email/mobile in the family
        $stmt = $this->pdo->prepare(
            'SELECT a.id, p.firstName, p.lastName, c.email, c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE (LOWER(TRIM(REPLACE(p.famCode, "#", ""))) = LOWER(?) OR LOWER(TRIM(p.famCode)) = LOWER(?))
             AND (
                 (c.email IS NOT NULL AND LOWER(TRIM(c.email)) = LOWER(?))
                 OR (a.email IS NOT NULL AND LOWER(TRIM(a.email)) = LOWER(?))
                 OR (c.mobile IS NOT NULL AND LOWER(TRIM(c.mobile)) = LOWER(?))
                 OR (c.mobile IS NOT NULL AND REPLACE(REPLACE(REPLACE(REPLACE(c.mobile, " ", ""), "-", ""), "+", ""), "(", "") LIKE ?)
             )
             AND a.deleted_at IS NULL
             LIMIT 1'
        );

        $phonePattern = '%' . ($cleanPhoneDigits !== '' ? $cleanPhoneDigits : $cleanContact) . '%';
        $stmt->execute([$cleanCode, $familyCode, $cleanContact, $cleanContact, $cleanContact, $phonePattern]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Verify names match (fuzzy match to handle typos)
            if ($this->namesMatch($firstName, $lastName, (string)($result['firstName'] ?? ''), (string)($result['lastName'] ?? ''))) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Simple fuzzy name matching (case-insensitive, ignore minor typos)
     * If database names are empty, consider it a match (user may have incomplete profile)
     */
    private function namesMatch(string $fname1, string $lname1, string $fname2, string $lname2): bool
    {
        // Null or empty database names - skip name validation (email already matched)
        if (empty($fname2) && empty($lname2)) {
            return true;
        }

        $fname1Lower = strtolower(trim($fname1));
        $lname1Lower = strtolower(trim($lname1));
        $fname2Lower = strtolower(trim($fname2));
        $lname2Lower = strtolower(trim($lname2));

        // Exact match
        if ($fname1Lower === $fname2Lower && $lname1Lower === $lname2Lower) {
            return true;
        }

        // Levenshtein distance for minor typos (within 2 characters)
        $fnameDistance = levenshtein($fname1Lower, $fname2Lower);
        $lnameDistance = levenshtein($lname1Lower, $lname2Lower);

        return $fnameDistance <= 2 && $lnameDistance <= 2;
    }

    /**
     * Check if user already has a pending approval from the same inviter
     */
    public function hasPendingApprovalFromInviter(
        string $userId,
        string $approverId
    ): bool {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM family_approval_requests
             WHERE id = ? AND approver_id = ? AND status = "pending"'
        );
        $stmt->execute([$userId, $approverId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get approval requests for a user (for the inviter to approve)
     * @return array<int, array<string, mixed>>
     */
    public function getPendingApprovalsForUser(string $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_approval_requests
             WHERE approver_id = ? AND status = "pending"
             ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get approval request by ID
     * @return array<string, mixed>|null
     */
    public function getApprovalRequest(int $requestId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_approval_requests WHERE no = ?'
        );
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Approve an approval request (link user to family code and grant access atomically)
     */
    public function approveRequest(int $requestId, ?string $approverId = null): bool
    {
        $request = $this->getApprovalRequest($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }

        // Resolve approver_id if not provided
        $effectiveApproverId = $approverId ?: ($request['approver_id'] ?? null);
        if (!$effectiveApproverId && !empty($request['family_code'])) {
            $inviter = $this->findMatchingInviter(
                (string)$request['family_code'],
                (string)($request['inviter_first_name'] ?? ''),
                (string)($request['inviter_last_name'] ?? ''),
                (string)($request['inviter_email_or_mobile'] ?? '')
            );
            if ($inviter) {
                $effectiveApproverId = (string)$inviter['id'];
            }
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Update family_approval_requests
            $stmt = $this->pdo->prepare(
                'UPDATE family_approval_requests
                 SET status = "approved", approver_id = ?, approved_at = NOW()
                 WHERE no = ? AND status = "pending"'
            );
            $stmt->execute([$effectiveApproverId, $requestId]);

            // 2. Link user in code_mgt
            $userId = (string)$request['id'];
            $familyCode = (string)$request['family_code'];
            $stmtCode = $this->pdo->prepare(
                'INSERT INTO code_mgt (id, code) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE code = VALUES(code), updated_at = NOW()'
            );
            $stmtCode->execute([$userId, $familyCode]);

            // 3. Update user_families status to approved
            $stmtUserFam = $this->pdo->prepare(
                "UPDATE user_families SET status = 'approved' WHERE user_id = ? AND family_code = ?"
            );
            $stmtUserFam->execute([$userId, $familyCode]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[FamilyCodeApprovalService] approveRequest transaction failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Deny an approval request atomically
     */
    public function denyRequest(int $requestId): bool
    {
        $request = $this->getApprovalRequest($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'UPDATE family_approval_requests
                 SET status = "denied", approved_at = NOW()
                 WHERE no = ? AND status = "pending"'
            );
            $stmt->execute([$requestId]);

            // Remove pending record from user_families if present
            $stmtUserFam = $this->pdo->prepare(
                "DELETE FROM user_families WHERE user_id = ? AND family_code = ? AND status = 'pending'"
            );
            $stmtUserFam->execute([(string)$request['id'], (string)$request['family_code']]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[FamilyCodeApprovalService] denyRequest transaction failed: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Get unapproved requests that need reminders (2 days old)
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsNeedingReminders(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_approval_requests
             WHERE status = "pending"
             AND reminder_sent_at IS NULL
             AND created_at <= DATE_SUB(NOW(), INTERVAL 2 DAY)
             ORDER BY created_at ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Mark reminder as sent
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function markReminderSent(int $requestId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE family_approval_requests
             SET reminder_sent_at = NOW()
             WHERE no = ?'
        );

        return $stmt->execute([$requestId]);
    }

    /**
     * Expire old pending requests
     */
    public function expireOldRequests(): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE family_approval_requests
             SET status = "expired"
             WHERE status = "pending" AND request_expires_at < NOW()'
        );

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Link approved user to family code (replace temp code with real code across all tables)
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    public function linkUserToFamily(string $userId, string $familyCode): bool
    {
        $cleanCode = trim(str_replace('#', '', $familyCode));

        // 1. Update code_mgt
        $stmt = $this->pdo->prepare(
            'INSERT INTO code_mgt (id, code) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE code = VALUES(code), updated_at = NOW()'
        );
        $stmt->execute([$userId, $cleanCode]);

        // 2. Update personal table
        $stmtPersonal = $this->pdo->prepare('UPDATE personal SET famCode = ? WHERE id = ?');
        $stmtPersonal->execute([$cleanCode, $userId]);

        // 3. Ensure otherFamily record exists without overwriting maternal/maiden otherFamCode
        $stmtOther = $this->pdo->prepare('INSERT INTO otherFamily (id) VALUES (?) ON DUPLICATE KEY UPDATE id = id');
        $stmtOther->execute([$userId]);

        // 4. Update or insert into user_families
        $stmtFam = $this->pdo->prepare(
            'INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "member")
             ON DUPLICATE KEY UPDATE status = "approved"'
        );
        $stmtFam->execute([$userId, $cleanCode]);

        return true;
    }

    /**
     * Generate a unique solo family code based on the surname (exactly 6 characters: 3 letters + 3 digits, e.g. OLA345)
     */
    public function generateUniqueSoloFamilyCode(string $surname): string
    {
        $cleanedSurname = preg_replace('/[^A-Za-z]/', '', $surname);
        if (empty($cleanedSurname)) {
            $cleanedSurname = 'FAM';
        }
        $prefix = strtoupper(substr($cleanedSurname, 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }

        // Generate and verify uniqueness with bounded retry (e.g. OLA345)
        $attempts = 0;
        do {
            $uniqueNumber = mt_rand(100, 999);
            $candidateCode = $prefix . $uniqueNumber;
            $attempts++;
        } while ($this->familyCodeExists($candidateCode) && $attempts < 10);

        if ($this->familyCodeExists($candidateCode)) {
            $candidateCode = $prefix . mt_rand(100, 999);
        }

        return $candidateCode;
    }

    /**
     * Switch user to a newly generated solo family code in an ACID transaction
     * @param string $userId
     * @param string $surname
     * @param array<string, mixed> $memberData
     * @return string The newly generated family code
     */
    public function switchUserToSoloFamily(string $userId, string $surname, array $memberData = []): string
    {
        $newCode = $this->generateUniqueSoloFamilyCode($surname);

        $wasInTransaction = $this->pdo->inTransaction();
        if (!$wasInTransaction) {
            $this->pdo->beginTransaction();
        }

        try {
            // 1. Update personal table
            $stmtPersonal = $this->pdo->prepare('UPDATE personal SET famCode = ? WHERE id = ?');
            $stmtPersonal->execute([$newCode, $userId]);

            // 2. Update code_mgt table
            $stmtCodeMgt = $this->pdo->prepare(
                'INSERT INTO code_mgt (id, code) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE code = VALUES(code), updated_at = NOW()'
            );
            $stmtCodeMgt->execute([$userId, $newCode]);

            // 3. Ensure otherFamily record exists without overwriting maternal/maiden otherFamCode
            $stmtOther = $this->pdo->prepare('INSERT INTO otherFamily (id) VALUES (?) ON DUPLICATE KEY UPDATE id = id');
            $stmtOther->execute([$userId]);

            // 4. Update user_families
            $stmtFam = $this->pdo->prepare(
                'INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", "admin")
                 ON DUPLICATE KEY UPDATE status = "approved", role = "admin"'
            );
            $stmtFam->execute([$userId, $newCode]);

            // 5. Initialize fresh tree root node in family_nodes for the new code
            try {
                \App\services\FamilyClaimService::claimOrInitializeNode($newCode, $userId, $memberData);
            } catch (\Throwable $nodeEx) {
                error_log('[FamilyCodeApprovalService] Node init warning: ' . $nodeEx->getMessage());
            }

            if (!$wasInTransaction) {
                $this->pdo->commit();
            }

            return $newCode;
        } catch (\Throwable $e) {
            if (!$wasInTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get active pending request for a user
     * @return array<string, mixed>|null
     */
    public function getPendingRequestForUser(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM family_approval_requests
             WHERE id = ? AND status = "pending" AND (request_expires_at IS NULL OR request_expires_at > NOW())
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /**
     * Cancel an active pending request for a user
     */
    public function cancelPendingRequest(string $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE family_approval_requests
             SET status = "denied", updated_at = NOW()
             WHERE id = ? AND status = "pending"'
        );
        return $stmt->execute([$userId]);
    }
}

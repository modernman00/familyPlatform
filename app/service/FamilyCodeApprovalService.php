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
     * Check if a family code exists and is valid (resilient to # and case)
     */
    public function familyCodeExists(string $code): bool
    {
        $clean = trim(str_replace('#', '', $code));
        if ($clean === '') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM personal 
             WHERE LOWER(TRIM(REPLACE(famCode, "#", ""))) = LOWER(?) 
                OR LOWER(TRIM(famCode)) = LOWER(?)'
        );
        $stmt->execute([$clean, $code]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Get family members for a code (to find who to send approval notification to)
     */
    public function getFamilyMembersForCode(string $code): array
    {
        $clean = trim(str_replace('#', '', $code));
        if ($clean === '') {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT a.id, p.firstName, p.lastName, c.email, c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE (LOWER(TRIM(REPLACE(p.famCode, "#", ""))) = LOWER(?) OR LOWER(TRIM(p.famCode)) = LOWER(?))
               AND a.deleted_at IS NULL'
        );
        $stmt->execute([$clean, $code]);
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
     * Verify approval token matches request ID
     */
    public function verifyApprovalToken(int $requestId, string $token): bool
    {
        $expectedToken = $this->generateApprovalToken($requestId);
        return hash_equals($expectedToken, $token);
    }

    /**
     * Find matching family member for inviter details
     * Matches by name and email/mobile
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
     * Approve an approval request (link user to family code)
     */
    public function approveRequest(int $requestId): bool
    {
        $request = $this->getApprovalRequest($requestId);
        if (!$request || $request['status'] !== 'pending') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE family_approval_requests
             SET status = "approved", approver_id = ?, approved_at = NOW()
             WHERE no = ?'
        );

        return $stmt->execute([$request['approver_id'], $requestId]);
    }

    /**
     * Deny an approval request
     */
    public function denyRequest(int $requestId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE family_approval_requests
             SET status = "denied", approved_at = NOW()
             WHERE no = ?'
        );

        return $stmt->execute([$requestId]);
    }

    /**
     * Get unapproved requests that need reminders (2 days old)
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
     * Link approved user to family code (replace temp code with real code)
     */
    public function linkUserToFamily(string $userId, string $familyCode): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO code_mgt (id, code) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE code = VALUES(code), updated_at = NOW()'
        );

        return $stmt->execute([$userId, $familyCode]);
    }
}

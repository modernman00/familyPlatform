<?php

namespace App\service;

use PDO;

class FamilyCodeApprovalService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Check if a family code exists and is valid
     */
    public function familyCodeExists(string $code): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM personal WHERE famCode = ?');
        $stmt->execute([$code]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get family members for a code (to find who to send approval notification to)
     */
    public function getFamilyMembersForCode(string $code): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT a.id, p.firstName, p.lastName, c.email, c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             LEFT JOIN contact c ON c.id = p.id
             WHERE p.famCode = ? AND a.deleted_at IS NULL'
        );
        $stmt->execute([$code]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Generate a temporary family code
     */
    public function generateTemporaryCode(): string
    {
        do {
            $tempCode = 'TEMP_' . bin2hex(random_bytes(6));
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
        $tempCode = $this->generateTemporaryCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $this->pdo->prepare(
            'INSERT INTO family_approval_requests
             (id, family_code, inviter_first_name, inviter_last_name, inviter_email_or_mobile, temporary_code, status, request_expires_at)
             VALUES (?, ?, ?, ?, ?, ?, "pending", ?)'
        );

        $stmt->execute([
            $userId,
            $familyCode,
            $inviterFirstName,
            $inviterLastName,
            $inviterEmailOrMobile,
            $tempCode,
            $expiresAt
        ]);

        $requestId = $this->pdo->lastInsertId();
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
        // First try to match by email/mobile in the family
        $stmt = $this->pdo->prepare(
            'SELECT a.id, p.firstName, p.lastName, c.email, c.mobile
             FROM personal p
             JOIN account a ON a.id = p.id
             JOIN contact c ON c.id = p.id
             WHERE p.famCode = ?
             AND (LOWER(c.email) = LOWER(?) OR LOWER(c.mobile) = LOWER(?))
             AND a.deleted_at IS NULL
             LIMIT 1'
        );

        $stmt->execute([$familyCode, $emailOrMobile, $emailOrMobile]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Verify names match (fuzzy match to handle typos)
            if ($this->namesMatch($firstName, $lastName, $result['firstName'] ?? '', $result['lastName'] ?? '')) {
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

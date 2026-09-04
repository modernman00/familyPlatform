<?php

namespace App\service;

use PDO;

/**
 * Wrapper service for sending approval-related notifications
 * Integrates with PushNotificationClass for in-app + email
 */
class NotificationService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Send approval request notification to inviter
     */
    public function sendFamilyApprovalNotification(
        string $approverId,
        ?array $newUserInfo,
        int $requestId,
        string $familyCode,
        string $approvalToken = ''
    ): void {
        if (!$newUserInfo) {
            return;
        }

        $approverEmail = $this->getEmailForUser($approverId);
        if (!$approverEmail) {
            return;
        }

        // TODO: Integrate with PushNotificationClass & Mail service
        // For now, just log that notification was sent
        error_log("Approval notification sent to $approverEmail for request $requestId with token $approvalToken");
    }

    /**
     * Send confirmation notification to new user
     */
    public function sendApprovalConfirmationNotification(
        string $userId,
        string $familyCode
    ): void {
        $email = $this->getEmailForUser($userId);
        if (!$email) {
            return;
        }

        // TODO: Integrate with PushNotificationClass
        error_log("Approval confirmation sent to $email for family code $familyCode");
    }

    /**
     * Send 2-day reminder to requester
     */
    public function sendApprovalReminderToRequester(
        string $userId,
        string $inviterName,
        string $expiresAt
    ): void {
        $email = $this->getEmailForUser($userId);
        if (!$email) {
            return;
        }

        error_log("Reminder sent to $email about pending approval from $inviterName");
    }

    /**
     * Send 2-day reminder to approver
     */
    public function sendApprovalReminderToApprover(
        string $approverId,
        string $requesterName,
        int $requestId,
        string $expiresAt
    ): void {
        $email = $this->getEmailForUser($approverId);
        if (!$email) {
            return;
        }

        error_log("Reminder sent to $email about pending approval for $requesterName");
    }

    /**
     * Get email for user ID
     */
    private function getEmailForUser(string $userId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT email FROM account WHERE id = ?');
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['email'] ?? null;
    }
}

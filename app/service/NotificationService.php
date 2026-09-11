<?php

namespace App\service;

use PDO;
use Src\functionality\SendEmailFunctionality;

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
     * Send approval request notification to inviter (email + in-app push)
     * @param array<string, mixed>|null $newUserInfo
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

        $approverData = $this->getApproverInfo($approverId);
        if (!$approverData || empty($approverData['email'])) {
            return;
        }

        $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ''), '/');
        $approveUrl = $baseUrl . "/api/family-code/approve/{$requestId}?token={$approvalToken}";
        $denyUrl    = $baseUrl . "/api/family-code/deny/{$requestId}?token={$approvalToken}";

        $emailData = [
            'id'            => $approverId,
            'firstName'     => $approverData['firstName'] ?? 'Family Member',
            'lastName'      => $approverData['lastName'] ?? '',
            'email'         => $approverData['email'],
            // New member details shown in the email
            'requesterName' => trim(($newUserInfo['firstName'] ?? '') . ' ' . ($newUserInfo['lastName'] ?? '')),
            'requesterEmail'=> $newUserInfo['email'] ?? '',
            'familyCode'    => $familyCode,
            'approveUrl'    => $approveUrl,
            'denyUrl'       => $denyUrl,
        ];

        try {
            SendEmailFunctionality::email(
                'msg/familyApprovalRequest',
                "Someone wants to join your family network",
                $emailData,
                'member'
            );
        } catch (\Throwable $e) {
            error_log('[NotificationService] Email send failed: ' . $e->getMessage());
        }

        // In-app push notification to the approver
        try {
            \App\classes\PushNotificationClass::sendPushNotification(
                userId: $approverId,
                message: ($emailData['requesterName'] ?: 'Someone') . " wants to join your {$familyCode} family network. Tap to approve or deny.",
                url: "/profilePage",
                title: "New Family Join Request",
                tag: "family-approval-request-{$requestId}"
            );
        } catch (\Throwable $e) {
            error_log('[NotificationService] Push notification failed: ' . $e->getMessage());
        }
    }


    /**
     * Send confirmation notification to the new member (requester) once approved.
     * Fires both an email and an in-app push notification.
     */
    public function sendApprovalConfirmationNotification(
        string $userId,
        string $familyCode
    ): void {
        $userData = $this->getApproverInfo($userId); // reuses the same query shape
        if (!$userData || empty($userData['email'])) {
            return;
        }

        $baseUrl     = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ''), '/');
        $profileUrl  = $baseUrl . '/profilePage';

        $emailData = [
            'id'         => $userId,
            'firstName'  => $userData['firstName'] ?? 'Member',
            'lastName'   => $userData['lastName'] ?? '',
            'email'      => $userData['email'],
            'familyCode' => $familyCode,
            'profileUrl' => $profileUrl,
        ];

        try {
            SendEmailFunctionality::email(
                'msg/familyApprovalConfirmed',
                "You've been approved to join your family network!",
                $emailData,
                'member'
            );
        } catch (\Throwable $e) {
            error_log('[NotificationService] Confirmation email failed: ' . $e->getMessage());
        }

        // In-app push
        try {
            \App\classes\PushNotificationClass::sendPushNotification(
                userId: $userId,
                message: "Great news! You've been approved to join the {$familyCode} family network. Visit your profile to get started.",
                url: '/profilePage',
                title: 'Family Membership Approved 🎉',
                tag: "family-code-confirmed-{$familyCode}"
            );
        } catch (\Throwable $e) {
            error_log('[NotificationService] Confirmation push failed: ' . $e->getMessage());
        }
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

        error_log("Reminder sent to $email about pending approval from $inviterName (expires: $expiresAt)");
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

        error_log("Reminder sent to $email about pending approval for $requesterName (request $requestId, expires: $expiresAt)");
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

    /**
     * Get name + email for an approver
     * @return array<string, string>|null
     */
    private function getApproverInfo(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.email, p.firstName, p.lastName
             FROM account a
             LEFT JOIN personal p ON p.id = a.id
             WHERE a.id = ?
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

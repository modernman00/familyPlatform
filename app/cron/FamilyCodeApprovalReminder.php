<?php

namespace App\cron;

use App\service\FamilyCodeApprovalService;
use App\service\NotificationService;
use PDO;

class FamilyCodeApprovalReminder
{
    private FamilyCodeApprovalService $approvalService;
    private NotificationService $notificationService;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->approvalService = new FamilyCodeApprovalService($pdo);
        $this->notificationService = new NotificationService($pdo);
    }

    /**
     * Send 2-day reminder to approvers and requesters
     * Should run daily
     */
    public function sendPendingReminders(): int
    {
        $requests = $this->approvalService->getRequestsNeedingReminders();
        $reminderCount = 0;

        foreach ($requests as $request) {
            try {
                // Get requester info
                $requesterInfo = $this->getUserInfo($request['id']);

                if ($requesterInfo) {
                    // Send reminder to requester
                    $this->notificationService->sendApprovalReminderToRequester(
                        $request['id'],
                        $request['inviter_first_name'] . ' ' . $request['inviter_last_name'],
                        $request['request_expires_at']
                    );
                }

                // Find approver (the original inviter)
                $inviter = $this->approvalService->findMatchingInviter(
                    $request['family_code'],
                    $request['inviter_first_name'],
                    $request['inviter_last_name'],
                    $request['inviter_email_or_mobile']
                );

                if ($inviter && $requesterInfo) {
                    // Send reminder to approver
                    $this->notificationService->sendApprovalReminderToApprover(
                        $inviter['id'],
                        $requesterInfo['firstName'] . ' ' . $requesterInfo['lastName'],
                        $request['no'],
                        $request['request_expires_at']
                    );
                }

                // Mark reminder as sent
                $this->approvalService->markReminderSent($request['no']);
                $reminderCount++;

            } catch (\Exception $e) {
                error_log('Error sending approval reminder for request ' . $request['no'] . ': ' . $e->getMessage());
                continue;
            }
        }

        return $reminderCount;
    }

    /**
     * Expire old pending requests (after 7 days)
     * Should run daily
     */
    public function expirePendingRequests(): int
    {
        $expiredCount = $this->approvalService->expireOldRequests();

        if ($expiredCount > 0) {
            error_log("Family code approval: Expired $expiredCount old pending requests");
        }

        return $expiredCount;
    }

    /**
     * Run all reminder tasks
     */
    public function runAll(): array
    {
        return [
            'reminders_sent' => $this->sendPendingReminders(),
            'requests_expired' => $this->expirePendingRequests()
        ];
    }

    /**
     * Get user info by ID
     */
    private function getUserInfo(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.email, c.firstName, c.lastName, c.mobile
             FROM account a
             LEFT JOIN contact c ON c.id = a.id
             WHERE a.id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

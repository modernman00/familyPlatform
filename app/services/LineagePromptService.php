<?php

declare(strict_types=1);

namespace App\services;

use App\controller\BaseController;
use Src\Db;
use Src\functionality\SendEmailFunctionality;

/**
 * LineagePromptService
 *
 * Handles prompt and reminder dispatches between family members to build
 * their interactive family tree and connect relatives in the Family Studio.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class LineagePromptService
{
    /**
     * Send a warm prompt / reminder to a relative to build their family tree.
     *
     * @param string $senderId Logged in member initiating the prompt.
     * @param string $targetUserId Relative being prompted.
     * @param string|null $note Optional personal note from sender.
     * @return array{success: bool, message: string}
     */
    public function sendTreeBuildPrompt(string $senderId, string $targetUserId, ?string $note = null): array
    {
        $senderId = trim($senderId);
        $targetUserId = trim($targetUserId);

        if ($senderId === '' || $targetUserId === '') {
            return [
                'success' => false,
                'message' => 'Both sender and recipient IDs are required.'
            ];
        }

        // 1. Self-prompt prevention
        if ($senderId === $targetUserId) {
            return [
                'success' => false,
                'message' => 'You cannot prompt yourself to build a family tree.'
            ];
        }

        // 2. Resolve sender and target records
        $sender = BaseController::findMemberById($senderId);
        if (empty($sender) || empty($sender['id'])) {
            return [
                'success' => false,
                'message' => 'Unable to verify your account session. Please refresh and try again.'
            ];
        }

        $target = BaseController::findMemberById($targetUserId);
        if (empty($target) || empty($target['id'])) {
            return [
                'success' => false,
                'message' => 'The selected family member could not be found.'
            ];
        }

        $famCode = (string)($target['famCode'] ?? ($sender['famCode'] ?? ''));

        // 3. Authorization / Kinship boundary check
        if (!BaseController::sessionCanViewMember($targetUserId, $famCode)) {
            return [
                'success' => false,
                'message' => 'You can only prompt members within your own family network or approved connections.'
            ];
        }

        $targetFirstName = trim((string)($target['firstName'] ?? 'Relative'));
        if ($targetFirstName === '') {
            $targetFirstName = 'Relative';
        }

        $senderName = trim(($sender['firstName'] ?? '') . ' ' . ($sender['lastName'] ?? ''));
        if ($senderName === '') {
            $senderName = 'A family member';
        }

        $sanitizedNote = null;
        if ($note !== null && trim($note) !== '') {
            $sanitizedNote = mb_substr(trim(strip_tags($note)), 0, 300);
        }

        // 4. Rate-Limiting / Anti-Spam Guard (24-hour debounce per sender -> target)
        try {
            $db = Db::connect2();
            $checkStmt = $db->prepare("
                SELECT created_at 
                FROM notification 
                WHERE sender_id = ? 
                  AND receiver_id = ? 
                  AND notification_type = 'Lineage'
                  AND (LOWER(notification_name) LIKE '%family tree%' OR LOWER(notification_content) LIKE '%family tree%')
                  AND created_at >= (NOW() - INTERVAL 24 HOUR)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $checkStmt->execute([$senderId, $targetUserId]);
            if ($checkStmt->fetch()) {
                return [
                    'success' => false,
                    'message' => "You have already sent a reminder to {$targetFirstName} recently. Please allow them time to respond."
                ];
            }
        } catch (\Throwable $e) {
            error_log('[LineagePromptService] Anti-spam query failed: ' . $e->getMessage());
        }

        // 5. Construct notification payload
        $title = "{$senderName} wants to see your family tree! 🌱";
        $body = !empty($sanitizedNote)
            ? "{$senderName}: \"{$sanitizedNote}\" — Click to build your family tree in the Family Studio."
            : "{$senderName} visited your family tree and would love to see your relatives connected. Click to add your family in the Family Studio.";

        // 6. Multi-channel dispatch (In-App + WebPush)
        try {
            NotificationOrchestrator::dispatch(
                userId: $targetUserId,
                category: 'Lineage',
                priority: 'high',
                title: $title,
                body: $body,
                actionUrl: '/familyStudio',
                tag: "lineage-prompt-{$senderId}-{$targetUserId}",
                familyCode: $famCode,
                metadata: [
                    'sender_id' => $senderId,
                    'sender_name' => $senderName,
                    'note' => $sanitizedNote,
                    'type' => 'tree_build_prompt'
                ]
            );
        } catch (\Throwable $e) {
            error_log('[LineagePromptService] NotificationOrchestrator dispatch failed: ' . $e->getMessage());
        }

        // 7. Email Notification Dispatch
        $targetEmail = trim((string)($target['email'] ?? ''));
        if ($targetEmail !== '') {
            try {
                $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
                $emailData = [
                    'id' => $targetUserId,
                    'firstName' => $targetFirstName,
                    'requesterName' => $senderName,
                    'note' => $sanitizedNote,
                    'studioUrl' => $baseUrl . '/familyStudio',
                    'familyCode' => $famCode
                ];
                SendEmailFunctionality::email(
                    'msg/promptBuildFamilyTree',
                    "{$senderName} prompted you to build your family tree",
                    $emailData,
                    'member'
                );
            } catch (\Throwable $e) {
                error_log('[LineagePromptService] Email delivery failed: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'message' => "We have sent a friendly reminder to {$targetFirstName} to build their family tree!"
        ];
    }
}

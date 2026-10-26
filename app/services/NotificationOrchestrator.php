<?php
declare(strict_types=1);

namespace App\services;

use Src\NotificationOrchestrator as CentralOrchestrator;

/**
 * Unified Multi-Channel Notification Orchestrator Facade (Option A+)
 *
 * Preserves familyCode and existing FamilyPlatform callers while delegating
 * core multi-channel presence arbitration, VAPID push, and socket broadcasts
 * to the centralized \Src\NotificationOrchestrator.
 */
final class NotificationOrchestrator
{
    /**
     * Dispatch a notification through the intelligent multi-channel cascade.
     *
     * @param string $userId
     * @param string $category
     * @param string $priority
     * @param string $title
     * @param string $body
     * @param string $actionUrl
     * @param string $tag
     * @param string|null $familyCode
     * @param array<string, mixed>|null $metadata
     * @param string|null $scheduledAt Optional future release timestamp
     * @return string
     */
    public static function dispatch(
        string $userId,
        string $category,
        string $priority,
        string $title,
        string $body,
        string $actionUrl = '/profilePage',
        string $tag = 'general',
        ?string $familyCode = null,
        ?array $metadata = null,
        ?string $scheduledAt = null
    ): string {
        $effectiveScheduledAt = $scheduledAt ?? (is_array($metadata) && isset($metadata['scheduled_at']) && is_string($metadata['scheduled_at']) ? $metadata['scheduled_at'] : null);
        $isScheduled = false;
        if ($effectiveScheduledAt !== null && trim($effectiveScheduledAt) !== '') {
            $parsed = strtotime($effectiveScheduledAt);
            if ($parsed !== false && $parsed > time()) {
                $isScheduled = true;
            }
        }

        $notifId = CentralOrchestrator::dispatch(
            userId: $userId,
            category: $category,
            priority: $priority,
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            tag: $tag,
            scopeCode: $familyCode,
            metadata: $metadata,
            scheduledAt: $scheduledAt
        );

        // If scheduled for future release, do NOT insert into bell/dropdown feed yet
        if ($isScheduled) {
            return $notifId;
        }

        // Guarantee durable in-app notification row for the bell & dropdown feed with canonical action_url
        try {
            $db = \Src\Db::connect2();
            $senderId = is_scalar($metadata['sender_id'] ?? null) ? (string)$metadata['sender_id'] : 'system';
            $senderName = is_scalar($metadata['sender_name'] ?? null) ? (string)$metadata['sender_name'] : 'Family Platform';
            $resolvedActionUrl = $actionUrl !== '' ? $actionUrl : '/profilePage';

            $notifType = !empty($metadata['type']) && is_scalar($metadata['type']) ? ucfirst((string)$metadata['type']) : ucfirst($category);

            // Check if CentralOrchestrator already inserted a row into notification for this dispatch
            $updateStmt = $db->prepare("
                UPDATE notification 
                SET action_url = ?, sender_name = ?, sender_id = ?, notification_type = ?
                WHERE receiver_id = ? 
                  AND (notification_name = ? OR notification_content = ?)
                  AND created_at >= (NOW() - INTERVAL 10 SECOND)
                ORDER BY no DESC 
                LIMIT 1
            ");
            $updateStmt->execute([
                $resolvedActionUrl,
                $senderName,
                $senderId,
                $notifType,
                $userId,
                strip_tags($title),
                strip_tags($body)
            ]);

            if ($updateStmt->rowCount() === 0) {
                $stmt = $db->prepare("
                    INSERT INTO notification 
                    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'new', NOW())
                ");
                $notifType = !empty($metadata['type']) && is_scalar($metadata['type']) ? ucfirst((string)$metadata['type']) : ucfirst($category);
                $stmt->execute([
                    $senderId,
                    $userId,
                    $senderName,
                    strip_tags($title),
                    $notifType,
                    strip_tags($body),
                    $resolvedActionUrl
                ]);
            }
        } catch (\Throwable $e) {
            // fail-safe logging
            error_log('[NotificationOrchestrator] In-app table sync failed: ' . $e->getMessage());
        }

        // Guarantee OS WebPush dispatch across all registered push endpoints
        try {
            \App\classes\PushNotificationClass::sendPushNotification(
                userId: $userId,
                message: $body,
                url: $actionUrl,
                title: $title,
                tag: $tag,
                targetNotificationId: $notifId !== '' ? $notifId : null
            );
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] WebPush dispatch failed: ' . $e->getMessage());
        }

        // Broadcast real-time notification badge event to user private channel
        try {
            $pusher = \App\classes\Pusher::pusher();
            if ($pusher !== null) {
                $pusher->trigger("private-user-{$userId}", 'new-notification', [
                    'title' => $title,
                    'action_url' => $actionUrl,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] Pusher real-time dispatch warning: ' . $e->getMessage());
        }

        return $notifId;
    }

    public static function markAsRead(string $notificationId, string $userId): bool
    {
        return CentralOrchestrator::markAsRead($notificationId, $userId);
    }

    public static function getUnreadCount(string $userId): int
    {
        return CentralOrchestrator::getUnreadCount($userId);
    }

    public static function isUserOnline(string $userId): bool
    {
        return CentralOrchestrator::isUserOnline($userId);
    }

    public static function updatePresence(string $userId, string $channelName, bool $isActive): void
    {
        CentralOrchestrator::updatePresence($userId, $channelName, $isActive);
    }

    public static function processScheduled(int $limit = 50): int
    {
        return CentralOrchestrator::processScheduled($limit);
    }

    /**
     * @param string $notificationId
     * @param string $event
     * @param array<string, mixed>|null $clientMeta
     * @return bool
     */
    public static function recordTelemetry(string $notificationId, string $event, ?array $clientMeta = null): bool
    {
        return CentralOrchestrator::recordTelemetry($notificationId, $event, $clientMeta);
    }
}

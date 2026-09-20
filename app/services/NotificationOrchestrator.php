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
        ?array $metadata = null
    ): string {
        $notifId = CentralOrchestrator::dispatch(
            userId: $userId,
            category: $category,
            priority: $priority,
            title: $title,
            body: $body,
            actionUrl: $actionUrl,
            tag: $tag,
            scopeCode: $familyCode,
            metadata: $metadata
        );

        // Guarantee durable in-app notification row for the bell & dropdown feed
        try {
            $db = \Src\Db::connect2();
            $stmt = $db->prepare("
                INSERT INTO notification 
                (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, notification_status, notification_date)
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            $senderId = is_scalar($metadata['sender_id'] ?? null) ? (string)$metadata['sender_id'] : 'system';
            $senderName = is_scalar($metadata['sender_name'] ?? null) ? (string)$metadata['sender_name'] : 'Family Platform';
            $stmt->execute([
                $senderId,
                $userId,
                $senderName,
                strip_tags($title),
                ucfirst($category),
                strip_tags($body)
            ]);
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
}

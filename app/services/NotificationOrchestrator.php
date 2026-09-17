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
        return CentralOrchestrator::dispatch(
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

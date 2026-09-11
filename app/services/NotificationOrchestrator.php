<?php
declare(strict_types=1);

namespace App\services;

use App\classes\Pusher;
use App\classes\PushNotificationClass;
use Src\Select;
use Src\Db;
use PDO;

/**
 * Unified Multi-Channel Notification Orchestrator
 *
 * Orchestrates notification delivery across In-App (Pusher WebSockets),
 * Web Push (VAPID / Service Worker), PWA Mobile (iOS/Android Badging),
 * and Delayed Email Fallbacks.
 */
final class NotificationOrchestrator
{
    /**
     * Dispatch a notification through the intelligent multi-channel cascade.
     *
     * @param string $userId
     * @param string $category 'social'|'financial'|'security'|'system'
     * @param string $priority 'low'|'medium'|'high'|'critical'
     * @param string $title
     * @param string $body
     * @param string $actionUrl
     * @param string $tag Coalescing tag (e.g. 'post-94819')
     * @param string|null $familyCode Optional family scope
     * @param array<string, mixed>|null $metadata Optional JSON payload
     * @return string Generated Notification ID
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
        $notificationId = 'notif_' . bin2hex(random_bytes(12));
        $userId = trim($userId);
        if ($userId === '') {
            return '';
        }

        try {
            $pdo = Db::connect2();

            // 1. Insert into notification_orchestration master table
            $stmt = $pdo->prepare("
                INSERT INTO notification_orchestration 
                (id, user_id, family_code, category, priority, title, body, action_url, tag, data_payload, status)
                VALUES (:id, :uid, :fam, :cat, :pri, :title, :body, :url, :tag, :meta, 'pending')
            ");
            $stmt->execute([
                ':id'    => $notificationId,
                ':uid'   => $userId,
                ':fam'   => $familyCode,
                ':cat'   => in_array($category, ['social', 'financial', 'security', 'system'], true) ? $category : 'social',
                ':pri'   => in_array($priority, ['low', 'medium', 'high', 'critical'], true) ? $priority : 'medium',
                ':title' => strip_tags($title),
                ':body'  => strip_tags($body),
                ':url'   => $actionUrl !== '' ? $actionUrl : '/profilePage',
                ':tag'   => preg_replace('/[^A-Za-z0-9_-]/', '', $tag) ?: 'general',
                ':meta'  => $metadata !== null ? json_encode($metadata) : null,
            ]);

            // Also keep legacy `notification` table updated for backward compatibility
            $legacyStmt = $pdo->prepare("
                INSERT INTO notification 
                (sender_id, receiver_id, notification_name, notification_type, notification_content, notification_status, notification_date)
                VALUES (:sender, :receiver, :name, :type, :content, 'new', :notif_date)
            ");
            $legacyStmt->execute([
                ':sender'     => $metadata['sender_id'] ?? 'system',
                ':receiver'   => $userId,
                ':name'       => strip_tags($title),
                ':type'       => $category,
                ':content'    => strip_tags($body),
                ':notif_date' => date('Y-m-d H:i:s'),
            ]);

            $unreadCount = self::getUnreadCount($userId);

            // 2. Check Real-Time User Presence (Cached / Webhook-driven)
            $isUserOnline = self::isUserOnline($userId);

            if ($isUserOnline) {
                // Channel 1: User is active in app -> Broadcast via Pusher socket (Immediate In-App Toast & Bell update)
                $userChannel = Pusher::userChannel($userId);
                Pusher::broadcast($userChannel, 'new-notification', [
                    'id'           => $notificationId,
                    'title'        => $title,
                    'body'         => $body,
                    'action_url'   => $actionUrl,
                    'tag'          => $tag,
                    'unread_count' => $unreadCount,
                    'category'     => $category,
                    'metadata'     => $metadata ?? []
                ]);

                self::logDelivery($notificationId, 'in_app_socket', 'sent', 'Broadcast to active socket channel');

                // If priority is critical (e.g. 2FA/Security), also dispatch silent push to sync background badge
                if ($priority === 'critical') {
                    PushNotificationClass::sendPushNotification(
                        userId: $userId,
                        message: $body,
                        url: $actionUrl,
                        title: $title,
                        tag: $tag,
                        badgeCount: $unreadCount,
                        isSilent: true
                    );
                }
            } else {
                // Channel 2: User is Offline / Backgrounded -> Dispatch WebPush with OS lockscreen banner + Badge
                $pushed = PushNotificationClass::sendPushNotification(
                    userId: $userId,
                    message: $body,
                    url: $actionUrl,
                    title: $title,
                    tag: $tag,
                    badgeCount: $unreadCount,
                    isSilent: false
                );

                self::logDelivery($notificationId, 'web_push', $pushed ? 'sent' : 'failed', 'Dispatched OS WebPush');

                // Channel 3: Queue Delayed Email Fallback for high/critical priority
                if (in_array($priority, ['high', 'critical'], true)) {
                    self::logDelivery($notificationId, 'email', 'queued', 'Queued for 15-minute delayed fallback');
                }
            }

            return $notificationId;
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] Dispatch failed: ' . $e->getMessage());
            return $notificationId;
        }
    }

    /**
     * Mark a notification as read and synchronize across all devices.
     */
    public static function markAsRead(string $notificationId, string $userId): bool
    {
        $notificationId = trim($notificationId);
        $userId = trim($userId);
        if ($notificationId === '' || $userId === '') {
            return false;
        }

        try {
            $pdo = Db::connect2();

            // 1. Update orchestration table
            $stmt = $pdo->prepare("
                UPDATE notification_orchestration 
                SET status = 'read', read_at = NOW() 
                WHERE id = :id AND user_id = :uid
            ");
            $stmt->execute([':id' => $notificationId, ':uid' => $userId]);

            // 2. Also update legacy table if matching record exists
            $legStmt = $pdo->prepare("
                UPDATE notification 
                SET notification_status = 'deleted' 
                WHERE receiver_id = :uid AND (no = :notif_no OR notification_name = :notif_id)
            ");
            $legStmt->execute([
                ':uid'      => $userId,
                ':notif_no' => is_numeric($notificationId) ? (int)$notificationId : 0,
                ':notif_id' => $notificationId,
            ]);

            $unreadCount = self::getUnreadCount($userId);

            // 3. Emit real-time sync event to other open tabs
            $userChannel = Pusher::userChannel($userId);
            Pusher::broadcast($userChannel, 'notification-synced', [
                'action'          => 'READ',
                'notification_id' => $notificationId,
                'unread_count'    => $unreadCount
            ]);

            // 4. Send silent sync WebPush to dismiss OS lockscreen banners and decrement Home Screen badge
            PushNotificationClass::sendPushNotification(
                userId: $userId,
                message: '',
                url: '',
                title: '',
                tag: 'sync-dismiss',
                badgeCount: $unreadCount,
                isSilent: true,
                syncAction: 'CLOSE_NOTIFICATION',
                targetNotificationId: $notificationId
            );

            // 5. Mark pending email fallback as cancelled
            $cancelStmt = $pdo->prepare("
                UPDATE notification_delivery_logs 
                SET status = 'cancelled_already_read' 
                WHERE notification_id = :id AND channel = 'email' AND status = 'queued'
            ");
            $cancelStmt->execute([':id' => $notificationId]);

            return true;
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] markAsRead failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total unread count for a given user.
     */
    public static function getUnreadCount(string $userId): int
    {
        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM notification_orchestration 
                WHERE user_id = :uid AND status = 'pending'
            ");
            $stmt->execute([':uid' => $userId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] getUnreadCount error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if user is actively online via socket presence cache.
     */
    public static function isUserOnline(string $userId): bool
    {
        try {
            $pdo = Db::connect2();
            // Active if heartbeat received within the last 90 seconds
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM user_socket_presence 
                WHERE user_id = :uid 
                  AND is_active = 1 
                  AND last_heartbeat >= DATE_SUB(NOW(), INTERVAL 90 SECOND)
            ");
            $stmt->execute([':uid' => $userId]);
            return ((int)$stmt->fetchColumn()) > 0;
        } catch (\Throwable $e) {
            // Fail open: assume offline if presence table error
            return false;
        }
    }

    /**
     * Update user socket presence (invoked by Pusher webhook or client ping).
     */
    public static function updatePresence(string $userId, string $channelName, bool $isActive): void
    {
        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare("
                INSERT INTO user_socket_presence (user_id, channel_name, is_active, last_heartbeat)
                VALUES (:uid, :channel, :active, NOW())
                ON DUPLICATE KEY UPDATE is_active = :active_update, last_heartbeat = NOW()
            ");
            $stmt->execute([
                ':uid'           => $userId,
                ':channel'       => $channelName,
                ':active'        => $isActive ? 1 : 0,
                ':active_update' => $isActive ? 1 : 0,
            ]);
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] updatePresence error: ' . $e->getMessage());
        }
    }

    /**
     * Log delivery attempts across channels.
     */
    private static function logDelivery(string $notificationId, string $channel, string $status, ?string $details = null): void
    {
        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare("
                INSERT INTO notification_delivery_logs (notification_id, channel, status, details, attempted_at)
                VALUES (:nid, :channel, :status, :details, NOW())
            ");
            $stmt->execute([
                ':nid'     => $notificationId,
                ':channel' => $channel,
                ':status'  => $status,
                ':details' => $details,
            ]);
        } catch (\Throwable $e) {
            error_log('[NotificationOrchestrator] logDelivery error: ' . $e->getMessage());
        }
    }
}

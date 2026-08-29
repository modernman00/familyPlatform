<?php
declare(strict_types=1);

namespace App\classes;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Src\Select;
use Src\Delete;

class PushNotificationClass extends VapidClass
{
    /**
     * Send push notifications to a user or list of users
     *
     * @param string|int|array<int, string|int>|null $userId
     * @param string $message
     * @param string|null $url
     * @param string $title
     * @return bool
     */
    public static function sendPushNotification(
        string|int|array|null $userId,
        string $message,
        ?string $url = null,
        string $title = 'Family Platform'
    ): bool {
        if (empty($userId)) {
            return false;
        }

        $userIds = is_array($userId) ? $userId : [$userId];
        $publicKey = (string) (getenv('VAPID_PUBLIC_KEY') ?: '');
        $privateKey = (string) (getenv('VAPID_PRIVATE_KEY') ?: '');
        $subject = (string) (getenv('VAPID_SUBJECT') ?: 'mailto:support@myfamilyplatform.com');
        $appLogo = (string) (getenv('APP_LOGO') ?: '/public/img/favicon/android-chrome-192x192.png');

        if (empty($publicKey) || empty($privateKey)) {
            error_log('[PushNotification] VAPID keys not configured in environment.');
            return false;
        }

        $auth = [
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        try {
            $webPush = new WebPush($auth);
            $webPush->setReuseVAPIDHeaders(true);

            $payload = json_encode([
                'title' => $title,
                'body' => $message,
                'url' => $url ?: '/profilePage',
                'icon' => $appLogo,
                'badge' => '/public/img/favicon/favicon-32x32.png',
                'tag' => 'family-alert-' . time(),
            ], JSON_UNESCAPED_SLASHES);

            $hasSubscriptions = false;

            foreach ($userIds as $uid) {
                $uidStr = (string) $uid;
                $subscriptions = self::getUserPushSubscriptions($uidStr);

                foreach ($subscriptions as $sub) {
                    if (empty($sub['endpoint']) || empty($sub['p256dhKey']) || empty($sub['authKey'])) {
                        continue;
                    }

                    $subscriptionObject = Subscription::create([
                        'endpoint' => $sub['endpoint'],
                        'keys' => [
                            'p256dh' => $sub['p256dhKey'],
                            'auth' => $sub['authKey'],
                        ],
                    ]);

                    $webPush->queueNotification($subscriptionObject, $payload ?: null);
                    $hasSubscriptions = true;
                }
            }

            if (!$hasSubscriptions) {
                return false;
            }

            // Flush all queued notifications & prune expired endpoints (Gate 2)
            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                if (!$report->isSuccess()) {
                    $reason = $report->getReason();
                    error_log("[PushNotification] Failed for {$endpoint}: {$reason}");

                    // If subscription has expired (410 Gone / 404 Not Found), delete from DB
                    if ($report->isSubscriptionExpired()) {
                        try {
                            $del = new Delete('pushNotification');
                            $del->deleteItem(['endpoint' => $endpoint]);
                        } catch (\Throwable $e) {
                            error_log("[PushNotification] Prune error: " . $e->getMessage());
                        }
                    }
                }
            }

            return true;
        } catch (\Throwable $e) {
            error_log('[PushNotification] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch user's active push subscriptions from database
     *
     * @param string $userId
     * @return array<int, array<string, mixed>>
     */
    private static function getUserPushSubscriptions(string $userId): array
    {
        try {
            $rows = Select::selectFn2(
                'SELECT * FROM pushNotification WHERE id = ?',
                [$userId]
            );
            if (!empty($rows) && is_array($rows)) {
                return isset($rows[0]) && is_array($rows[0]) ? $rows : [$rows];
            }
            return [];
        } catch (\Throwable $e) {
            error_log('[PushNotification] DB Fetch error: ' . $e->getMessage());
            return [];
        }
    }
}
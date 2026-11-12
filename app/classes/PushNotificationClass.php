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
     * Red Team Allowlist for Push Service Endpoints (SSRF Mitigation)
     */
    public static function isAllowedPushEndpoint(string $endpoint): bool
    {
        $parsed = parse_url($endpoint);
        if (!$parsed || empty($parsed['host']) || empty($parsed['scheme']) || $parsed['scheme'] !== 'https') {
            return false;
        }

        $host = strtolower($parsed['host']);
        $allowedSuffixes = [
            'push.apple.com',
            'fcm.googleapis.com',
            'googleapis.com',
            'push.services.mozilla.com',
            'notify.windows.com',
            'push.amazon.com',
        ];

        foreach ($allowedSuffixes as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.' . $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send push notifications to a user or list of users
     *
     * @param string|int|array<int, string|int>|null $userId
     * @param string $message
     * @param string|null $url
     * @param string $title
     * @param string $tag
     * @param int|null $badgeCount
     * @param bool $isSilent
     * @param string|null $syncAction
     * @param string|null $targetNotificationId
     * @return bool
     */
    public static function sendPushNotification(
        string|int|array|null $userId,
        string $message,
        ?string $url = null,
        string $title = 'Family Platform',
        string $tag = 'general',
        ?int $badgeCount = null,
        bool $isSilent = false,
        ?string $syncAction = null,
        ?string $targetNotificationId = null,
        bool $forceOutbound = false
    ): bool {
        if (empty($userId)) {
            return false;
        }

        // Emoji / Me-sticker tokens become glyphs (OS notifications are plain text).
        $message = \App\services\EmojiPlainText::humanize($message);
        $title = \App\services\EmojiPlainText::humanize($title);

        // Never push to real devices during automated test suites.
        // For development/local environments, allow push if forceOutbound is true OR ALLOW_OUTBOUND_NOTIFICATIONS/ALLOW_OUTBOUND_PUSH is set.
        $allowPush = strtolower(trim((string) ($_ENV['ALLOW_OUTBOUND_PUSH'] ?? $_ENV['ALLOW_OUTBOUND_NOTIFICATIONS'] ?? (getenv('ALLOW_OUTBOUND_PUSH') ?: (getenv('ALLOW_OUTBOUND_NOTIFICATIONS') ?: ''))), "'\""));
        if (defined('TESTING_ENV') || (!$forceOutbound && $allowPush !== 'true' && $allowPush !== '1' && Pusher::outboundBlocked())) {
            return false;
        }

        $userIds = is_array($userId) ? $userId : [$userId];
        $publicKey = trim((string) ($_ENV['VAPID_PUBLIC_KEY'] ?? $_SERVER['VAPID_PUBLIC_KEY'] ?? (getenv('VAPID_PUBLIC_KEY') ?: ($_ENV['MIX_VAPID_PUBLIC_KEY'] ?? ''))), "'\"");
        $privateKey = trim((string) ($_ENV['VAPID_PRIVATE_KEY'] ?? $_SERVER['VAPID_PRIVATE_KEY'] ?? (getenv('VAPID_PRIVATE_KEY') ?: ($_ENV['MIX_VAPID_PRIVATE_KEY'] ?? ''))), "'\"");
        $subject = trim((string) ($_ENV['VAPID_SUBJECT'] ?? $_SERVER['VAPID_SUBJECT'] ?? (getenv('VAPID_SUBJECT') ?: 'mailto:support@myfamilyplatform.com')), "'\"");
        $appLogo = trim((string) ($_ENV['APP_LOGO'] ?? $_SERVER['APP_LOGO'] ?? (getenv('APP_LOGO') ?: '/public/img/favicon/android-chrome-192x192.png')), "'\"");

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
            $defaultOptions = [
                'TTL' => $isSilent ? 300 : 86400,
                'urgency' => $isSilent ? 'low' : 'high',
                'topic' => preg_replace('/[^A-Za-z0-9_-]/', '', $tag) ?: 'general',
            ];
            $timeout = 3; // Bounded 3-second timeout (Gate 4)
            $clientOptions = [
                'timeout' => 3.0,
                'connect_timeout' => 2.0,
                'allow_redirects' => false,
                'http_errors' => false,
            ];
            $webPush = new WebPush($auth, $defaultOptions, $timeout, $clientOptions);
            $webPush->setReuseVAPIDHeaders(true);

            $payloadArray = [
                'title'                => $title,
                'body'                 => $message,
                'url'                  => $url ?: '/profilePage',
                'icon'                 => $appLogo,
                'badge'                => '/public/img/favicon/favicon-32x32.png',
                'tag'                  => $tag,
                'badgeCount'           => $badgeCount,
                'isSilent'             => $isSilent,
                'syncAction'           => $syncAction,
                'targetNotificationId' => $targetNotificationId,
                'timestamp'            => time() * 1000,
            ];

            $payload = json_encode($payloadArray, JSON_UNESCAPED_SLASHES);
            $hasSubscriptions = false;

            foreach ($userIds as $uid) {
                $uidStr = (string) $uid;
                $subscriptions = self::getUserPushSubscriptions($uidStr);

                foreach ($subscriptions as $sub) {
                    $endpoint = (string)($sub['endpoint'] ?? '');
                    $p256dh = (string)($sub['p256dhKey'] ?? $sub['p256dh'] ?? '');
                    $auth = (string)($sub['authKey'] ?? $sub['auth'] ?? '');

                    if ($endpoint === '' || $p256dh === '' || $auth === '') {
                        continue;
                    }

                    // Red Team SSRF Gatekeeper
                    if (!self::isAllowedPushEndpoint($endpoint)) {
                        error_log("[PushNotification] SSRF Blocked: Invalid push endpoint {$endpoint}");
                        continue;
                    }

                    $subscriptionObject = Subscription::create([
                        'endpoint' => $endpoint,
                        'keys' => [
                            'p256dh' => $p256dh,
                            'auth' => $auth,
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
                            $delQuery = Delete::formAndMatchQuery(
                                selection: 'DELETE_ONE',
                                table: 'pushNotification',
                                identifier1: 'endpoint',
                            );
                            Delete::deleteFn($delQuery, [$endpoint]);
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
        $merged = [];
        $seenEndpoints = [];

        try {
            $rows = Select::selectFn2(
                'SELECT endpoint, p256dhKey, authKey FROM pushNotification WHERE id = ?',
                [$userId]
            );
            $list = !empty($rows) ? (isset($rows[0]) && is_array($rows[0]) ? $rows : [$rows]) : [];
            foreach ($list as $row) {
                $ep = (string)($row['endpoint'] ?? '');
                if ($ep !== '' && !isset($seenEndpoints[$ep])) {
                    $seenEndpoints[$ep] = true;
                    $merged[] = $row;
                }
            }
        } catch (\Throwable $e) {
            error_log('[PushNotification] DB Fetch error (pushNotification): ' . $e->getMessage());
        }

        // Fallback/supplement with centralized user_push_subscriptions table
        try {
            $db = \Src\Db::connect2();
            $stmt = $db->prepare('SELECT endpoint, p256dh AS p256dhKey, auth_token AS authKey FROM user_push_subscriptions WHERE user_id = ?');
            $stmt->execute([$userId]);
            $extraRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($extraRows as $row) {
                $ep = (string)($row['endpoint'] ?? '');
                if ($ep !== '' && !isset($seenEndpoints[$ep])) {
                    $seenEndpoints[$ep] = true;
                    $merged[] = $row;
                }
            }
        } catch (\Throwable) {
            // Optional table; ignore if absent
        }

        return $merged;
    }
}
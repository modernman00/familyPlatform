<?php
namespace App\controller;

use Src\Select;
use App\classes\Insert;
use Src\CheckToken;
use Src\Exceptions\NotFoundException;
use Src\Update;


final class NotificationController extends Select
{
    // get all the notifications and show on the profile page
    // TODO - Only show notification that are not already clicked on and associated with family member and code 
    /**
     * @return array|null
     */
    public static function index()
    {
        return self::notificationById();
    }

    /**
     * Returns the caller's own notification feed.
     *
     * `notification.receiver_id` holds either a user id or a family code, so the
     * allow-list is built from what the *session* holds — never from the URL
     * segments, which a member could otherwise swap for another family's id/code
     * to read their notifications (IDOR, SEC-1).
     *
     * @return array<int, mixed>|null
     */
    public static function notificationById($id = null, $famCode = null)
    {
        try {
            $userId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($userId === '') {
                msgException(401, 'Unauthorized');
                return null;
            }

            $approvals = self::getPendingApprovalNotifications($userId);
            $receivers = self::sessionReceiverIds($userId);

            // Trigger idempotent daily check for memories & milestones notifications (at most once per day per session)
            $todayKey = 'memories_notif_checked_' . date('Y-m-d');
            if (empty($_SESSION[$todayKey])) {
                $_SESSION[$todayKey] = true;
                try {
                    $memoryService = new \App\services\MemoryMilestoneService();
                    $primaryFam = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
                    $codes = $_SESSION['famCodes'] ?? ($primaryFam !== '' ? [$primaryFam] : []);
                    $memoryService->dispatchDailyMemoriesAndMilestonesForUser($userId, (array)$codes);
                } catch (\Throwable) {}
            }

            // Read-only: drop the session lock so this does not block the profile
            // page's other concurrent AJAX calls sharing the same session file.
            \releaseSessionLock();

            $placeholders = implode(',', array_fill(0, count($receivers), '?'));
            $query = "SELECT * FROM notification
                WHERE receiver_id IN ($placeholders)
                AND (sender_id IS NULL OR sender_id != ?)
                AND notification_status = ?
                ORDER BY created_at DESC, no DESC";
            $result = Select::selectFn2($query, [...$receivers, $userId, 'new']);
            $enriched = self::enrichWithActionUrls(is_array($result) ? $result : [], $userId);
            $merged = self::sortNotificationsNewestFirst(array_merge($approvals, $enriched));

            msgSuccess(200, $merged);

            return $merged;
        } catch (\Throwable $e) {
            showError($e);
            return null;
        }
    }

    /**
     * Unread notification feed for PwaManager badging sync and in-app bell counters.
     * Returns total unread count along with list of unread items.
     */
    public static function unread(): void
    {
        try {
            $userId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($userId === '') {
                msgSuccess(200, ['status' => 'success', 'unread_count' => 0, 'count' => 0, 'message' => []]);
                return;
            }

            $approvals = self::getPendingApprovalNotifications($userId);
            $receivers = self::sessionReceiverIds($userId);

            // Trigger idempotent daily check for memories & milestones notifications (at most once per day per session)
            $todayKey = 'memories_notif_checked_' . date('Y-m-d');
            if (empty($_SESSION[$todayKey])) {
                $_SESSION[$todayKey] = true;
                try {
                    $memoryService = new \App\services\MemoryMilestoneService();
                    $primaryFam = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
                    $codes = $_SESSION['famCodes'] ?? ($primaryFam !== '' ? [$primaryFam] : []);
                    $memoryService->dispatchDailyMemoriesAndMilestonesForUser($userId, (array)$codes);
                } catch (\Throwable) {}
            }

            \releaseSessionLock();

            $placeholders = implode(',', array_fill(0, count($receivers), '?'));
            $query = "SELECT * FROM notification
                WHERE receiver_id IN ($placeholders)
                AND (sender_id IS NULL OR sender_id != ?)
                AND notification_status = ?
                ORDER BY created_at DESC, no DESC";
            $result = Select::selectFn2($query, [...$receivers, $userId, 'new']);
            $enriched = self::enrichWithActionUrls(is_array($result) ? $result : [], $userId);
            $merged = self::sortNotificationsNewestFirst(array_merge($approvals, $enriched));
            $count = count($merged);

            msgSuccess(200, [
                'status' => 'success',
                'unread_count' => $count,
                'count' => $count,
                'notifications' => $merged,
                'message' => $merged
            ]);
        } catch (\Throwable $e) {
            msgSuccess(200, ['status' => 'success', 'unread_count' => 0, 'count' => 0, 'message' => []]);
        }
    }

    /**
     * Sort notifications from newest to oldest by created_at (fallback notification_date) and no DESC.
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private static function sortNotificationsNewestFirst(array $items): array
    {
        usort($items, static function (array $a, array $b): int {
            $timeA = strtotime((string)($a['created_at'] ?? $a['notification_date'] ?? '')) ?: 0;
            $timeB = strtotime((string)($b['created_at'] ?? $b['notification_date'] ?? '')) ?: 0;
            if ($timeA !== $timeB) {
                return $timeB <=> $timeA;
            }
            $noA = is_numeric($a['no'] ?? null) ? (int)$a['no'] : 0;
            $noB = is_numeric($b['no'] ?? null) ? (int)$b['no'] : 0;
            return $noB <=> $noA;
        });

        return $items;
    }

    /**
     * Enriches notification items with canonical, direct action URLs pointing to the exact items.
     *
     * @param array<int, array<string, mixed>> $items
     * @param string $userId
     * @return array<int, array<string, mixed>>
     */
    public static function enrichWithActionUrls(array $items, string $userId = ''): array
    {
        $enriched = [];
        foreach ($items as $item) {
            $item['action_url'] = self::resolveNotificationActionUrl($item, $userId);
            $enriched[] = $item;
        }
        return $enriched;
    }

    /**
     * Resolves the canonical action URL for a notification item so that
     * clicking the notification takes the user directly to the item itself.
     *
     * @param array<string, mixed> $notif
     * @param string $userId
     * @return string
     */
    public static function resolveNotificationActionUrl(array $notif, string $userId = ''): string
    {
        // 1. If action_url is already present and non-empty, normalize and return
        if (!empty($notif['action_url']) && is_string($notif['action_url'])) {
            $raw = trim($notif['action_url']);
            if (stripos($raw, 'familyStudio') !== false) {
                return '/organogram/' . ($userId !== '' ? $userId : '') . '?mode=canvas';
            }
            $parsed = parse_url($raw);
            $path = $parsed['path'] ?? '/profilePage';
            $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#' . $parsed['fragment'] : '';
            return ($path !== '' ? $path : '/profilePage') . $query . $fragment;
        }

        $type = (string)($notif['notification_type'] ?? '');
        $name = (string)($notif['notification_name'] ?? '');
        $content = (string)($notif['notification_content'] ?? '');
        $senderId = (string)($notif['sender_id'] ?? '');

        // 2. Check notification_orchestration for pre-computed action_url or data_payload
        try {
            $db = \Src\Db::connect2();
            $stmt = $db->prepare("
                SELECT action_url, tag, data_payload 
                FROM notification_orchestration 
                WHERE (user_id = ? OR family_code = ?)
                  AND (title = ? OR body = ?)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $receiverId = (string)($notif['receiver_id'] ?? $userId);
            $stmt->execute([$receiverId, $receiverId, $name, $content]);
            $orch = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($orch) {
                if (!empty($orch['action_url']) && is_string($orch['action_url'])) {
                    $url = trim($orch['action_url']);
                    if (stripos($url, 'familyStudio') !== false) {
                        return '/organogram/' . ($userId !== '' ? $userId : '') . '?mode=canvas';
                    }
                    if ($url !== '/' && $url !== '') {
                        return $url;
                    }
                }
                if (!empty($orch['data_payload'])) {
                    $payload = json_decode((string)$orch['data_payload'], true);
                    if (is_array($payload)) {
                        if (!empty($payload['event_no'])) {
                            return '/profilePage#linkNotification' . $payload['event_no'];
                        }
                        if (!empty($payload['post_no'])) {
                            $pNo = (int)$payload['post_no'];
                            if (!empty($payload['comment_no'])) {
                                $cNo = (int)$payload['comment_no'];
                                return "/profilePage?post_no={$pNo}&comment_no={$cNo}#comment{$cNo}";
                            }
                            return "/profilePage?post_no={$pNo}#post{$pNo}";
                        }
                    }
                }
            }
        } catch (\Throwable) {
            // Non-blocking fallback
        }

        // 3. Memories & Milestones ("On This Day" flashbacks & family milestone celebrations)
        if (strcasecmp($type, 'Memories') === 0 || strcasecmp($type, 'Milestones') === 0
            || stripos($name, 'Flashback') !== false || stripos($name, 'Memory') !== false
            || (stripos($name, 'Milestone') !== false && stripos($name, 'Event') === false)
            || stripos($content, 'flashback') !== false || stripos($content, 'cherished family memory') !== false || stripos($content, 'memory from') !== false) {
            return '/profilePage#memoriesCard';
        }

        // 4. Events: Party, Birthday, Wedding, Anniversary, House Warming, Meeting, Reunion, etc.
        $eventTypes = [
            'Party', 'Birthday', 'Wedding', 'Anniversary', 'House Warming', 'House_warming',
            'Remembrance', 'Barbecue', 'Education', 'Naming', 'Meeting', 'Reunion',
            'Holidays', 'Others', 'Event'
        ];
        $isEvent = in_array($type, $eventTypes, true)
            || stripos($name, 'Event') !== false
            || stripos($content, 'added \'') !== false;

        if ($isEvent) {
            try {
                $cleanName = preg_replace('/^🎉\s*New\s+Event:\s*/i', '', $name);
                $cleanName = trim((string)$cleanName);
                if ($cleanName !== '') {
                    $db = \Src\Db::connect2();
                    $stmt = $db->prepare("SELECT no FROM events WHERE eventName = ? ORDER BY no DESC LIMIT 1");
                    $stmt->execute([$cleanName]);
                    $eventNo = $stmt->fetchColumn();
                    if ($eventNo) {
                        return '/profilePage#linkNotification' . $eventNo;
                    }
                }
            } catch (\Throwable) {}

            return '/profilePage#eventHeader';
        }

        // 5. Reels
        if (strcasecmp($type, 'Reel') === 0 || stripos($content, 'reel') !== false || stripos($name, 'reel') !== false || stripos($content, 'video') !== false) {
            if (!empty($notif['action_url']) && is_string($notif['action_url']) && str_contains($notif['action_url'], '/reels')) {
                return $notif['action_url'];
            }
            return '/reels';
        }

        // 6. Family Announcements
        if (strcasecmp($type, 'Announcement') === 0 || stripos($name, 'announcement') !== false || stripos($content, 'announcement') !== false) {
            return '/profilePage#familyAnnouncementsCard';
        }

        // 7. Family Journal / Chronicles
        if (strcasecmp($type, 'Journal') === 0 || strcasecmp($type, 'Chronicle') === 0 || stripos($content, 'chronicle') !== false || stripos($name, 'chronicle') !== false || stripos($content, 'journal') !== false) {
            if (!empty($notif['action_url']) && is_string($notif['action_url']) && str_contains($notif['action_url'], '/familyJournal')) {
                return $notif['action_url'];
            }
            return '/familyJournal';
        }

        // 8. Time Capsules
        if (strcasecmp($type, 'Capsule') === 0 || stripos($name, 'Time Capsule') !== false || stripos($content, 'time capsule') !== false) {
            return '/profilePage#timeCapsuleCard';
        }

        // 9. Posts, Comments and Post Tags
        if (strcasecmp($type, 'Post') === 0 || strcasecmp($type, 'Post Tag') === 0 || strcasecmp($type, 'Comment') === 0 || stripos($name, 'update') !== false || stripos($content, 'tagged you') !== false || stripos($content, 'comment') !== false || stripos($content, 'replied') !== false) {
            try {
                $db = \Src\Db::connect2();
                if ($senderId !== '' && $userId !== '') {
                    $stmt = $db->prepare("SELECT post_no FROM post_tags WHERE user_id = ? AND tagged_by = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$userId, $senderId]);
                    $postNo = $stmt->fetchColumn();
                    if ($postNo) {
                        return '/profilePage?post_no=' . (int)$postNo . '#post' . (int)$postNo;
                    }

                    $stmt = $db->prepare("SELECT post_no, comment_no FROM comment WHERE id = ? ORDER BY comment_no DESC LIMIT 1");
                    $stmt->execute([$senderId]);
                    $commentRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                    if ($commentRow && !empty($commentRow['post_no'])) {
                        $pNo = (int)$commentRow['post_no'];
                        $cNo = (int)($commentRow['comment_no'] ?? 0);
                        return $cNo > 0 
                            ? "/profilePage?post_no={$pNo}&comment_no={$cNo}#comment{$cNo}" 
                            : "/profilePage?post_no={$pNo}#post{$pNo}";
                    }

                    $stmt = $db->prepare("SELECT post_no FROM post WHERE id = ? ORDER BY post_no DESC LIMIT 1");
                    $stmt->execute([$senderId]);
                    $postNo = $stmt->fetchColumn();
                    if ($postNo) {
                        return '/profilePage?post_no=' . (int)$postNo . '#post' . (int)$postNo;
                    }
                }
            } catch (\Throwable) {}

            return '/profilePage#middleColumn';
        }

        // 5. Family Join Requests and Approvals
        if (stripos($name, 'Family Join Request') !== false || stripos($content, 'wants to join your') !== false) {
            return '/profilePage#familyJoinRequests';
        }

        // 6. Migration Nudges & Requests
        if (stripos($name, 'Migration Join Request') !== false || stripos($content, 'requested to migrate') !== false) {
            return '/profilePage#migrationRequests';
        }
        if (stripos($name, 'Family Migration Nudge') !== false || stripos($content, 'invited you to migrate') !== false) {
            return '/profilePage#migrationNudges';
        }

        // 7. Friend Requests
        if (strcasecmp($type, 'Friend Request') === 0 || stripos($name, 'Friend Request') !== false) {
            return '/profilePage#friendRequestsCard';
        }

        // 8. Lineage / Family Tree / Dynasty Milestones / Onboarding
        if (strcasecmp($type, 'onboarding') === 0 || strcasecmp($type, 'Lineage') === 0 || stripos($content, 'family tree') !== false || stripos($content, 'family branch') !== false || stripos($content, 'dynasty') !== false) {
            return '/organogram/' . ($userId !== '' ? $userId : '') . '?mode=canvas';
        }

        return '/profilePage';
    }

    /**
     * Synthesize notification items for pending family approval requests so
     * that navbar bell, dropdown, and PWA counters seamlessly reflect them.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPendingApprovalNotifications(string $userId): array
    {
        try {
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if ($famCode === '' && !empty($_SESSION['famCodes']) && is_array($_SESSION['famCodes'])) {
                $firstCode = reset($_SESSION['famCodes']);
                if (is_string($firstCode)) {
                    $famCode = $firstCode;
                }
            }

            $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());
            $requests = $approvalService->getPendingApprovalsWithDetails($userId, $famCode !== '' ? $famCode : null);

            $dismissed = isset($_SESSION['dismissed_far']) && is_array($_SESSION['dismissed_far']) 
                ? $_SESSION['dismissed_far'] 
                : [];
            $notifications = [];

            foreach ($requests as $req) {
                $senderId = (string)($req['id'] ?? 'system');
                if ($senderId !== '' && $senderId === $userId) {
                    continue;
                }

                $farNo = 'far_' . ($req['no'] ?? '');
                if (in_array($farNo, $dismissed, true)) {
                    continue;
                }

                $requesterName = (string)($req['requesterName'] ?? 'New Member');
                $code = (string)($req['family_code'] ?? '');

                $notifications[] = [
                    'no' => $farNo,
                    'sender_id' => $senderId,
                    'sender_name' => $requesterName,
                    'notification_name' => 'Family Join Request',
                    'notification_type' => 'Family Request',
                    'notification_content' => "{$requesterName} wants to join your {$code} family network.",
                    'notification_status' => 'new',
                    'notification_date' => (string)($req['created_at'] ?? date('Y-m-d H:i:s')),
                    'created_at' => (string)($req['created_at'] ?? date('Y-m-d H:i:s')),
                    'action_url' => '/profilePage#familyJoinReq-' . ($req['no'] ?? ''),
                ];
            }

            return $notifications;
        } catch (\Throwable $e) {
            error_log('[NotificationController] Failed to get pending approval notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * The set of receiver_id values the current session is entitled to read:
     * the user's own id plus every approved family code on the session.
     *
     * @return array<int, string>
     */
    private static function sessionReceiverIds(string $userId): array
    {
        $receivers = [$userId];

        $famCodes = $_SESSION['famCodes'] ?? [];
        if (is_string($famCodes)) {
            $famCodes = [$famCodes];
        }
        if (is_array($famCodes)) {
            foreach ($famCodes as $fc) {
                if (is_string($fc) && $fc !== '') {
                    $receivers[] = $fc;
                }
            }
        }

        if (!empty($_SESSION['famCode']) && is_string($_SESSION['famCode'])) {
            $receivers[] = $_SESSION['famCode'];
        }

        try {
            $db = \Src\Db::connect2();
            $stmt = $db->prepare("SELECT family_code FROM user_families WHERE user_id = ? AND status = 'approved'");
            $stmt->execute([$userId]);
            $dbCodes = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            if ($dbCodes) {
                foreach ($dbCodes as $dbc) {
                    if (is_string($dbc) && $dbc !== '') {
                        $receivers[] = $dbc;
                    }
                }
            }
        } catch (\Throwable $e) {
            // fallback gracefully to session cache
        }

        return array_values(array_unique($receivers));
    }

    // make notification as read 
    /**
     * @return void
     */
    public static function notificationRead($no)
    {
        try {
            $no = checkInput($no) ?? null;

            if (!is_string($no)) {
                throw new NotFoundException('Notification No not found');
            }


            $userId = isset($_SESSION['id']) ? (string) $_SESSION['id'] : '';
            if ($userId === '') throw new NotFoundException('Unauthorized');

            if (str_starts_with($no, 'far_')) {
                if (!isset($_SESSION['dismissed_far']) || !is_array($_SESSION['dismissed_far'])) {
                    $_SESSION['dismissed_far'] = [];
                }
                $_SESSION['dismissed_far'][] = $no;
                msgSuccess(200, 'Notification marked as read');
                return;
            }

            // Only dismiss a row the session is actually a recipient of — by
            // user id or by one of its approved family codes.
            $receivers = self::sessionReceiverIds($userId);
            $placeholders = implode(',', array_fill(0, count($receivers), '?'));
            $db = \Src\Db::connect2();
            $stmt = $db->prepare(
                "UPDATE notification SET notification_status = 'deleted'
                 WHERE no = ? AND receiver_id IN ($placeholders)"
            );
            $stmt->execute([$no, ...$receivers]);


            // Call msgSuccess after returning the result
            msgSuccess(200, 'Notification marked as read');

            // return $result;
        } catch (\Exception $e) {
            // Handle errors or log them
            showError($e);
        }
    }


    /**
     * Posts subscriber data to the server.
     *
     * This function takes the request body as a JSON object and expects the following
     * properties: 'id', 'subscription' with properties 'endpoint', 'keys' with properties
     * 'p256dh', and 'auth'.
     *
     * It validates the input data and inserts a new subscription or updates an existing
     * one in the 'pushNotification' table.
     *
     * @throws \Exception
     */
    public static function postSubscriberData(): void
    {
        try {
            $rawInput = (string)file_get_contents("php://input");
            $inputData = json_decode($rawInput !== '' ? $rawInput : '', true);
            if (!is_array($inputData)) {
                $inputData = [];
            }

            // Robust CSRF verification across headers and JSON payload
            $sessionToken = $_SESSION['token'] ?? '';
            $requestToken = $_SERVER['HTTP_X_XSRF_TOKEN'] 
                ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                ?? $_SERVER['HTTP_CSRF_TOKEN'] 
                ?? ($inputData['token'] ?? ($_POST['token'] ?? ''));

            if (empty($sessionToken) || !is_string($requestToken) || !hash_equals($sessionToken, $requestToken)) {
                msgException(401, 'Unauthorized CSRF Token');
                return;
            }

            // Validate subscription payload
            if (!isset(
                $inputData['endpoint'],
                $inputData['keys']['p256dh'],
                $inputData['keys']['auth']
            ) || empty($inputData['endpoint'])) {
                msgException(422, 'Invalid subscription data');
                return;
            }

            $userId = !empty($inputData['id']) ? cleanSession((string)$inputData['id']) : (isset($_SESSION['id']) ? cleanSession((string)$_SESSION['id']) : '');
            if (empty($userId)) {
                msgException(401, 'User authentication required for push subscription');
                return;
            }

            $endpoint = (string)$inputData['endpoint'];
            $p256dhKey = (string)$inputData['keys']['p256dh'];
            $authKey = (string)$inputData['keys']['auth'];

            // Red Team SSRF Safeguard
            if (!\App\classes\PushNotificationClass::isAllowedPushEndpoint($endpoint)) {
                msgException(422, 'Invalid or untrusted push endpoint host');
                return;
            }

            $data = [
                'id' => $userId,
                'endpoint' => $endpoint,
                'p256dhKey' => $p256dhKey,
                'authKey' => $authKey
            ];

            // Check if subscription already exists for this endpoint
            $existingSubscription = Select::selectFn2('SELECT * FROM pushNotification WHERE endpoint = ?', [$endpoint]);

            if (!empty($existingSubscription)) {
                $update = new Update('pushNotification');
                $update->makeUpdate($data, 'endpoint');
            } else {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare('INSERT INTO pushNotification (id, endpoint, p256dhKey, authKey) VALUES (?, ?, ?, ?)');
                $stmt->execute([$userId, $endpoint, $p256dhKey, $authKey]);
            }

            msgSuccess(200, 'Subscription saved successfully');
        } catch (\Src\Exceptions\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            error_log('[Push] Subscription registration failed: ' . $e->getMessage());
            msgException(500, 'Failed to save push subscription');
        }
    }

    /**
     * Removes a push subscription for the current user (PUSH-1 — "turn off
     * browser notifications" in Settings, or an unsubscribe from the client).
     * Scoped to the session user so one member can't delete another's rows.
     */
    public static function deleteSubscriberData(): void
    {
        try {
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false ? $rawInput : '', true);
            \App\controller\BaseController::validateCsrf(is_array($input) ? $input : null);

            $userId = isset($_SESSION['id']) ? cleanSession((string) $_SESSION['id']) : '';
            if ($userId === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            $endpoint = is_array($input) && !empty($input['endpoint']) ? (string) $input['endpoint'] : '';

            $db = \Src\Db::connect2();
            if ($endpoint !== '') {
                $stmt = $db->prepare('DELETE FROM pushNotification WHERE id = ? AND endpoint = ?');
                $stmt->execute([$userId, $endpoint]);
            } else {
                // No endpoint given — drop every subscription this user has.
                $stmt = $db->prepare('DELETE FROM pushNotification WHERE id = ?');
                $stmt->execute([$userId]);
            }

            msgSuccess(200, 'Push notifications turned off');
        } catch (\Throwable $e) {
            showError($e);
        }
    }

    /**
     * Synchronized cross-device mark-as-read API.
     * Marks notification read, emits Pusher sync event, and triggers silent push dismiss.
     */
    public static function markAsReadSync(?string $notifId = null): void
    {
        try {
            $userId = isset($_SESSION['id']) ? cleanSession((string) $_SESSION['id']) : '';
            if (!is_string($userId) || $userId === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            if (empty($notifId)) {
                $rawInput = file_get_contents('php://input');
                $input = json_decode($rawInput !== false ? $rawInput : '', true);
                $notifId = is_array($input) && !empty($input['id']) ? (string)$input['id'] : '';
            }

            if (empty($notifId)) {
                msgException(422, 'Notification ID required');
                return;
            }

            $success = \App\services\NotificationOrchestrator::markAsRead($notifId, $userId);

            if ($success) {
                msgSuccess(200, 'Notification marked as read across all devices');
            } else {
                msgException(400, 'Could not update notification state');
            }
        } catch (\Throwable $e) {
            error_log('[NotificationController] markAsReadSync error: ' . $e->getMessage());
            msgException(500, 'Server error syncing notification');
        }
    }

    /**
     * Lightweight client presence ping when PWA tab is active.
     */
    public static function recordClientPresence(): void
    {
        try {
            $userId = isset($_SESSION['id']) ? cleanSession((string) $_SESSION['id']) : '';
            if (!is_string($userId) || $userId === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            $userChannel = \App\classes\Pusher::userChannel($userId);
            \App\services\NotificationOrchestrator::updatePresence($userId, $userChannel, true);

            msgSuccess(200, 'Presence updated');
        } catch (\Throwable $e) {
            error_log('[NotificationController] presence error: ' . $e->getMessage());
            msgException(500, 'Failed to update presence');
        }
    }

    /**
     * Pusher webhook receiver for channel_occupied / channel_vacated events.
     */
    public static function handlePusherWebhook(): void
    {
        try {
            $rawBody = (string)file_get_contents('php://input');
            $pusherSecret = trim((string)($_ENV['PUSHER_APP_SECRET'] ?? getenv('PUSHER_APP_SECRET') ?: ''));
            if ($pusherSecret !== '') {
                $signature = trim((string)($_SERVER['HTTP_X_PUSHER_SIGNATURE'] ?? ''));
                $expectedSig = hash_hmac('sha256', $rawBody, $pusherSecret);
                if ($signature === '' || !hash_equals($expectedSig, $signature)) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid webhook signature']);
                    return;
                }
            }

            $data = json_decode($rawBody, true);

            if (!is_array($data) || empty($data['events']) || !is_array($data['events'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid webhook payload']);
                return;
            }

            foreach ($data['events'] as $event) {
                $channel = $event['channel'] ?? '';
                $name = $event['name'] ?? ''; // 'channel_occupied' or 'channel_vacated'

                if (preg_match('/^private-user-(.+)$/', $channel, $matches)) {
                    $userId = $matches[1];
                    $isActive = ($name === 'channel_occupied');
                    \App\services\NotificationOrchestrator::updatePresence($userId, $channel, $isActive);
                }
            }

            http_response_code(200);
            echo json_encode(['status' => 'ok']);
        } catch (\Throwable $e) {
            error_log('[NotificationController] Webhook error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Webhook processing failed']);
        }
    }
}

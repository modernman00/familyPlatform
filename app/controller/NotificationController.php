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
        try {
            $query = Select::formAndMatchQuery(selection: 'SELECT_ALL', table: 'notification', orderBy: 'ORDER BY created_at ASC', limit: 'LIMIT 15');
            $result = Select::selectFn2($query);

            // Call msgSuccess after returning the result
            msgSuccess(200, $result);

            return $result;
        } catch (\Exception $e) {
            // Handle errors or log them
            showError($e);
            return null;
        }
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

            $receivers = self::sessionReceiverIds($userId);

            $placeholders = implode(',', array_fill(0, count($receivers), '?'));
            $query = "SELECT * FROM notification
                WHERE receiver_id IN ($placeholders)
                AND notification_status = ?
                ORDER BY created_at ASC";
            $result = Select::selectFn2($query, [...$receivers, 'new']);

            msgSuccess(200, $result);

            return $result;
        } catch (\Throwable $e) {
            showError($e);
            return null;
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
            CheckToken::tokenCheck();

            $rawInput = file_get_contents("php://input");
            $inputData = json_decode($rawInput !== false ? $rawInput : '', true);
            // Validate the input data
            if (!isset(
                $inputData['endpoint'],
                $inputData['keys']['p256dh'],
                $inputData['keys']['auth']
            )) {
                msgException(300, 'Invalid subscription data');
            }

            $userId = !empty($inputData['id']) ? cleanSession((string)$inputData['id']) : (isset($_SESSION['id']) ? cleanSession((string)$_SESSION['id']) : '');
            if (empty($userId)) {
                msgException(300, 'User authentication required for push subscription');
                return;
            }

            $endpoint = (string)$inputData['endpoint'];
            $p256dhKey = (string)$inputData['keys']['p256dh'];
            $authKey = (string)$inputData['keys']['auth'];
            // Prepare the data to insert
            $data = [
                'id' => $userId,
                'endpoint' => $endpoint,
                'p256dhKey' => $p256dhKey,
                'authKey' => $authKey
            ];

            // Check if the subscription already exists for this user and endpoint

            $existingSubscription = Select::selectFn2('SELECT * FROM pushNotification WHERE id = ? AND endpoint = ?', [$userId, $endpoint]);

            if ($existingSubscription) {

                $update = new Update('pushNotification');
                $update->updateMultiplePOST($data, 'id');
                // If subscription exists, update the keys

            } else {
                // If not, insert a new subscription
                Insert::submitFormDynamicLastId('pushNotification', $data, 'id');
            }

            msgSuccess(200, 'Subscription saved successfully');
        } catch (\Exception $e) {
            msgException(300, $e);
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
            CheckToken::tokenCheck();

            $userId = isset($_SESSION['id']) ? cleanSession((string) $_SESSION['id']) : '';
            if ($userId === '') {
                msgException(401, 'Unauthorized');
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput !== false ? $rawInput : '', true);
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
}

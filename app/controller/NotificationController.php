<?php

namespace App\controller;

use App\classes\{Select, Insert, Update};


class NotificationController extends Select
{
    // get all the notifications and show on the profile page
    // TODO - Only show notification that are not already clicked on and associated with family member and code 
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
        }
    }

    /**
     * Retrieves notifications based on the provided notification ID and family code.
     *
     * This function queries the 'notification' table for entries where the receiver ID
     * matches either the notification ID or family code. The results are ordered by
     * creation date in ascending order. Upon successful retrieval, it returns the data and
     * sends a success message. In case of an exception, it handles the error appropriately.
     */
    public static function notificationById($id, $famCode)
    {
        try {
            $notificationId = checkInput($id) ?? null;
            $famCode = checkInput($famCode) ?? null;

            if ($notificationId === null && $famCode === null) {
                msgException(400, 'Either notification ID or family code must be provided');
            }

            $query = "SELECT * FROM notification 
                WHERE (receiver_id = ? OR receiver_id = ?) 
                AND notification_status = ? 
                ORDER BY created_at ASC";
            $result = Select::selectFn2($query, [$notificationId, $famCode, "new"]);

            // Call msgSuccess after returning the result
            msgSuccess(200, $result);

            return $result;
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
    public static function postSubscriberData()
    {
        try {

            $inputData = json_decode(file_get_contents("php://input"), true);
            // Validate the input data
            if (!isset($inputData['id'], $inputData['subscription']['endpoint'], $inputData['subscription']['keys']['p256dh'], $inputData['subscription']['keys']['auth'])) {
                msgException(300, 'Invalid subscription data');
            }

            $userId = $inputData['id'];
            $endpoint = $inputData['subscription']['endpoint'];
            $p256dhKey = $inputData['subscription']['keys']['p256dh'];
            $authKey = $inputData['subscription']['keys']['auth'];
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
                Insert::submitFormDynamicLastId('notification', $data, 'id');
            }

            msgSuccess(200, 'Subscription saved successfully');
        } catch (\Exception $e) {
            msgException(300, $e);
        }
    }


    // TODO Auto-generated Not used yet
    public static function getUnreadNotifications(int $userId): void
    {
        try {
            $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'notification', identifier1: 'status', orderBy: 'ORDER BY created_at ASC');
            $famCode = $_GET['famCode'] ?? null;

            $query = "SELECT * FROM notification WHERE (receiver_id = ? OR receiver_id = ?) AND status = ?";
            $result = Select::selectFn2($query, [$userId, $famCode, 'unread']);
            msgSuccess(200, $result);
        } catch (\Exception $e) {
            showError($e);
        }
    }


    // TODO Auto-generated Not used yet
    public static function markAsRead($youId, $famCode, $senderId): bool
    {
        $youId = checkInput($youId);
        $famCode = checkInput($famCode);
        $senderId = checkInput($senderId);

        if (!$youId || !$famCode || !$senderId) {
            msgException(400, 'Invalid parameters');
            return false;
        }

        try {
            $query = "UPDATE notification SET notification_status = 'clicked' WHERE (receiver_id = ? OR receiver_id = ?) AND sender_id = ?";
            $statement = parent::connect2()->prepare($query);
            $result = $statement->execute([$youId, $famCode, $senderId]);

            if ($result) {
                msgSuccess(200, "success");
                return true;
            } else {
                msgException(500, "Database update failed");
                return false;
            }
        } catch (\Exception $e) {
            showError($e);
            return false;
        }
    }
}

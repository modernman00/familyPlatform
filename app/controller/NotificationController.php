<?php

namespace App\controller;

use App\classes\{Select, Insert, Update};


class NotificationController extends Select
{
    // get all the notifications and show on the profile page

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

    public static function notificationById()
    {
        try {
            $notificationId = $_GET['notificationId'] ?? null;
            $famCode = $_GET['famCode'] ?? null;
            $query = Select::formAndMatchQuery(selection: 'SELECT_OR', table: 'notification', identifier1: 'receiver_id', identifier2: 'receiver_id', orderBy: 'ORDER BY created_at ASC');
            $result = Select::selectFn2($query, [$notificationId, $famCode]);

            // Call msgSuccess after returning the result
            msgSuccess(200, $result);

            return $result;
        } catch (\Exception $e) {
            // Handle errors or log them
            showError($e);
        }
    }


    public static function postSubscriberData()
    {
        try{

             $inputData = json_decode(file_get_contents("php://input"), true);
        // Validate the input data
        if (!isset($inputData['user_id'], $inputData['subscription']['endpoint'], $inputData['subscription']['keys']['p256dh'], $inputData['subscription']['keys']['auth'])) {
            msgException(300, 'Invalid subscription data');
        }

        $userId = $inputData['user_id'];
        $endpoint = $inputData['subscription']['endpoint'];
        $p256dhKey = $inputData['subscription']['keys']['p256dh'];
        $authKey = $inputData['subscription']['keys']['auth'];
        // Prepare the data to insert
        $data = [
            'id' => $inputData['id'],
            'endpoint' => $inputData['subscription']['endpoint'],
            'p256dhKey' => $p256dhKey,
            'authKey' => $authKey
        ];

        // Check if the subscription already exists for this user and endpoint

        $existingSubscription = Select::selectFn2('SELECT * FROM push_notifications WHERE id = ? AND endpoint = ?', [$userId, $endpoint]);

        if ($existingSubscription) {

            $update = new Update('push_notification');
            $update->updateMultiplePOST($data, 'id');
            // If subscription exists, update the keys

        } else{
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
    public static function markAsRead(int $notificationId): void
    {
        try {
            Update::table('notifications')
                ->where('id', $notificationId)
                ->update(['status' => 'read']);

            echo json_encode(['status' => 'success', 'message' => 'Notification marked as read.']);
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

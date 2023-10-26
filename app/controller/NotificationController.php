<?php

namespace App\controller;

use App\classes\Select;


class NotificationController extends Select {
// get all the notifications and show on the profile page

 public static function index()
{
    try {
        $query = Select::formAndMatchQuery(selection: 'SELECT_ALL', table: 'notification', orderBy: 'ORDER BY created_at ASC', limit: 'LIMIT 15' );
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

}
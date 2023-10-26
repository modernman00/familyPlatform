<?php 


$router->map('POST', '/member/notification/event', 'App\controller\members\Event@PostEventNotificationBar', 'new event notification bar');

$router->map('GET', '/member/notification/event', 'App\controller\members\Event@GetEventNotificationBar', 'new event notification bar2');

$router->map('GET', '/member/notifications', 'App\controller\NotificationController@index', 'all notification bar2');

$router->map('GET', '/member/notifications/id', 'App\controller\NotificationController@notificationById', 'all notification by id');
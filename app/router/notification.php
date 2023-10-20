<?php 


$router->map('POST', '/member/notification/event', 'App\controller\members\Event@PostEventNotificationBar', 'new event notification bar');

$router->map('GET', '/member/notification/event', 'App\controller\members\Event@GetEventNotificationBar', 'new event notification bar2');

$router->map('GET', '/member/notifications', 'App\controller\NotificationController', 'all notification bar2');
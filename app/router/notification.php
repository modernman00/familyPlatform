<?php 


$router->map(
  method: 'POST', 
  route: '/member/notification/event', 
  target: 'App\controller\members\Event@PostEventNotificationBar', 
  name: 'new event notification bar'
);

$router->map(
  method: 'GET', 
  route: '/member/notification/event', 
  target: 'App\controller\members\Event@GetEventNotificationBar', 
  name: 'new event notification bar2');

$router->map(
  method: 'GET', 
  route: '/member/notifications', 
  target: 'App\controller\NotificationController@index', 
  name: 'all notification bar2');

$router->map(
  method: 'GET', 
  route: '/member/notifications/id/[a:id]/[a:famCode]', 
  target: 'App\controller\NotificationController@notificationById',
  name: 'all notification by id'
);

$router->map(
  method: 'PUT', 
  route: '/removeNotification/[i:no]', 
  target: 'App\controller\NotificationController@notificationRead',
  name: 'delete notification by id'
);

$router->map(
  method: 'POST', 
  route: '/pustNotification/subscription', 
  target: 'App\controller\NotificationController@postSubscriberData',
  name: 'post subscriber data'
);


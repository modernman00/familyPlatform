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
  route: '/member/notifications/user', 
  target: 'App\controller\NotificationController@notificationById',
  name: 'session notification by id'
);

$router->map(
  method: 'GET', 
  route: '/api/notifications', 
  target: 'App\controller\NotificationController@notificationById',
  name: 'api notification by id'
);

$router->map(
  method: 'GET', 
  route: '/api/notifications/unread', 
  target: 'App\controller\NotificationController@unread',
  name: 'api notifications unread'
);

$router->map(
  method: 'GET', 
  route: '/member/notifications/id/[*:id]/[*:famCode]', 
  target: 'App\controller\NotificationController@notificationById',
  name: 'all notification by id'
);

$router->map(
  method: 'PUT', 
  route: '/removeNotification/[*:no]', 
  target: 'App\controller\NotificationController@notificationRead',
  name: 'delete notification by id'
);

$router->map(
  method: 'DELETE', 
  route: '/removeNotification/[*:no]', 
  target: 'App\controller\NotificationController@notificationRead',
  name: 'delete notification by id delete'
);

$router->map(
  method: 'PUT', 
  route: '/api/notifications/read-all', 
  target: 'App\controller\NotificationController@markAllReadSync',
  name: 'api notification mark all read'
);

$router->map(
  method: 'POST', 
  route: '/api/notifications/read-all', 
  target: 'App\controller\NotificationController@markAllReadSync',
  name: 'api notification mark all read post'
);

$router->map(
  method: 'PUT', 
  route: '/member/notifications/read-all', 
  target: 'App\controller\NotificationController@markAllReadSync',
  name: 'member notification mark all read'
);

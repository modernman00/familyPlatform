<?php 

$router->map(
  method: 'GET', 
  route: '/start-websocket', 
  target: 'App\controller\WebSocketController@startServer', 
  name: 'startServer'
);

// $router->map(
//   method: 'GET', 
//   route: '/sse', 
//   target: 'App\controller\WebSocketController@sendSSEResponse', 
//   name: 'sse'
// );

// $router->map(
//   method: 'GET', 
//   route: '/getsse', 
//   target: 'App\controller\WebSocketController@getSSEResponse', 
//   name: 'sseget'
// );

$router->map(
  method: 'GET', 
  route: '/getNewPostPusher', 
  target: 'App\controller\members\PostMessage@getNewPostPusher', 
  name: 'Pusher'
);

$router->map(
  method: 'GET', 
  route: '/getNewCommentPusher', 
  target: 'App\controller\members\PostMessage@getNewCommentPusher', 
  name: 'Pusher-comment'
);

$router->map(
  method: 'GET', 
  route: '/getNewLikesPusher', 
  target: 'App\controller\members\PostLikeController@getNewLikesPusher', 
  name: 'post-likes-Pusher'
);

$router->map(
  method: 'PUT', 
  route: '/updateCommentByStatusAsPublished/[a:commentNo]', 
  target: 'App\controller\members\PostMessage@updateCommentAsPublished', 
  name: 'updateCommentByStatus'
);

$router->map(
  method: 'PUT', 
  route: '/updatePostByStatusAsPublished/[a:postNo]', 
  target: 'App\controller\members\PostMessage@updatePostAsPublished', 
  name: 'updatePostByStatus'
);


// Pusher private-channel authorisation. pusher-js POSTs socket_id + channel_name
// here; App\classes\Pusher::authoriseChannel checks the session owns that
// channel before signing the subscription grant.
$router->map(
  method: 'POST',
  route: '/pusher/auth',
  target: 'App\classes\Pusher@authoriseChannel',
  name: 'pusherAuth'
);

// Sovereign Realtime Broadcasting Routes (SSE Stream & Channel Auth)
$router->map(
  method: 'GET',
  route: '/api/broadcasting/events',
  target: 'App\controller\RealtimeBroadcastingController@stream',
  name: 'api_broadcasting_events'
);
$router->map(
  method: 'POST',
  route: '/api/broadcasting/auth',
  target: 'App\controller\RealtimeBroadcastingController@auth',
  name: 'api_broadcasting_auth'
);
$router->map(
  method: 'GET',
  route: '/api/broadcasting/status',
  target: 'App\controller\RealtimeBroadcastingController@status',
  name: 'api_broadcasting_status'
);

// VAPID public key endpoint for browser PushManager subscription (parity with ExecMind)
$router->map(
  method: 'GET',
  route: '/push/vapid-public-key',
  target: 'App\controller\NotificationController@vapidPublicKey',
  name: 'pushVapidPublicKey'
);

$router->map(
  method: 'GET',
  route: '/api/notifications/vapid-public-key',
  target: 'App\controller\NotificationController@vapidPublicKey',
  name: 'apiNotificationsVapidPublicKey'
);

$router->map(
  method: 'POST',
  route: '/pushNotification/subscription',
  target: 'App\controller\NotificationController@postSubscriberData',
  name: 'postSubscriberData'
);

$router->map(
  method: 'POST',
  route: '/api/notifications/subscribe',
  target: 'App\controller\NotificationController@postSubscriberData',
  name: 'apiNotificationsSubscribe'
);

$router->map(
  method: 'POST',
  route: '/api/notifications/test',
  target: 'App\controller\NotificationController@sendTestNotification',
  name: 'apiNotificationsTest'
);

$router->map(
  method: 'POST',
  route: '/pushNotification/unsubscribe',
  target: 'App\controller\NotificationController@deleteSubscriberData',
  name: 'deleteSubscriberData'
);

$router->map(
  method: 'PUT',
  route: '/api/notifications/read',
  target: 'App\controller\NotificationController@markAsReadSync',
  name: 'notificationMarkReadSync'
);

$router->map(
  method: 'PUT',
  route: '/api/notifications/read/[*:id]',
  target: 'App\controller\NotificationController@markAsReadSync',
  name: 'notificationMarkReadSyncId'
);

$router->map(
  method: 'POST',
  route: '/api/notifications/presence',
  target: 'App\controller\NotificationController@recordClientPresence',
  name: 'notificationRecordPresence'
);

$router->map(
  method: 'GET',
  route: '/api/notifications/unread-count',
  target: 'App\controller\NotificationController@getUnreadCountSync',
  name: 'notificationGetUnreadCount'
);

$router->map(
  method: 'POST',
  route: '/api/pusher/webhook',
  target: 'App\controller\NotificationController@handlePusherWebhook',
  name: 'pusherWebhook'
);
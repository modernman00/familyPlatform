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

$router->map(
  method: 'POST',
  route: '/pushNotification/subscription',
  target: 'App\controller\NotificationController@postSubscriberData',
  name: 'postSubscriberData'
);

$router->map(
  method: 'POST',
  route: '/pushNotification/unsubscribe',
  target: 'App\controller\NotificationController@deleteSubscriberData',
  name: 'deleteSubscriberData'
);
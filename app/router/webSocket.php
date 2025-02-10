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
  route: '/getNewPostPolling', 
  target: 'App\controller\members\PostMessage@getNewPostPolling', 
  name: 'polling'
);

$router->map(
  method: 'GET', 
  route: '/getNewCommentPolling', 
  target: 'App\controller\members\PostMessage@getNewCommentPolling', 
  name: 'polling-comment'
);

$router->map(
  method: 'GET', 
  route: '/getNewLikesPolling', 
  target: 'App\controller\members\PostLikeController@getNewLikesPolling', 
  name: 'post-likes-polling'
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


// $router->map(
//   method: 'GET', 
//   route: '/getNewCommentLikesPolling', 
//   target: 'App\controller\members\PostMessage@getNewCommentLikesPolling', 
//   name: 'polling-comment-likes'
// );
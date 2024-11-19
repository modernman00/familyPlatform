<?php 

$router->map(
  method: 'GET', 
  route: '/start-websocket', 
  target: 'App\controller\WebSocketController@startServer', 
  name: 'startServer'
);

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
  route: '/getNewCommentLikesPolling', 
  target: 'App\controller\members\PostMessage@getNewCommentLikesPolling', 
  name: 'polling-comment-likes'
);
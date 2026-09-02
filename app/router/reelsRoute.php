<?php
declare(strict_types=1);

// Immersive Reels Experience View
$router->map('GET', '/reels', 'App\controller\members\ReelsController@index', 'family_reels_view');

// Reels JSON Feed
$router->map('GET', '/api/reels/feed', 'App\controller\members\ReelsController@getFeed', 'family_reels_feed_api');

// Create & Upload Reel
$router->map('POST', '/api/reels/upload', 'App\controller\members\ReelsController@uploadReel', 'family_reels_upload_api');

// Reactions (Heart/Celebrate)
$router->map('POST', '/api/reels/react', 'App\controller\members\ReelsController@toggleReaction', 'family_reels_react_api');

// Comments List
$router->map('GET', '/api/reels/comments', 'App\controller\members\ReelsController@getComments', 'family_reels_get_comments_api');

// Add Comment
$router->map('POST', '/api/reels/comment', 'App\controller\members\ReelsController@addComment', 'family_reels_add_comment_api');

// Delete Reel
$router->map('POST', '/api/reels/delete', 'App\controller\members\ReelsController@deleteReel', 'family_reels_delete_api');

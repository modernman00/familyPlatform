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

// Add Comment (supports 1-level threaded reply & curated GIFs)
$router->map('POST', '/api/reels/comment', 'App\controller\members\ReelsController@addComment', 'family_reels_add_comment_api');

// Update Comment (Author-only)
$router->map('POST', '/api/reels/comment/update', 'App\controller\members\ReelsController@updateComment', 'family_reels_update_comment_api');
$router->map('PUT', '/api/reels/comment/[i:commentId]', 'App\controller\members\ReelsController@updateComment', 'family_reels_update_comment_by_id_api');

// Comment Reaction (Toggle Like, Love, Haha, Wow, Sad, Angry)
$router->map('POST', '/api/reels/comment/react', 'App\controller\members\ReelsController@toggleCommentReaction', 'family_reels_react_comment_api');

// Delete Comment (Author of comment OR account holder of reel)
$router->map('POST', '/api/reels/comment/delete', 'App\controller\members\ReelsController@deleteComment', 'family_reels_delete_comment_api');
$router->map('DELETE', '/api/reels/comment/[i:commentId]', 'App\controller\members\ReelsController@deleteComment', 'family_reels_delete_comment_by_id_api');

// Delete Reel
$router->map('POST', '/api/reels/delete', 'App\controller\members\ReelsController@deleteReel', 'family_reels_delete_api');

// Record Reel View
$router->map('POST', '/api/reels/view', 'App\controller\members\ReelsController@recordView', 'family_reels_view_api');

// Update / Auto-Heal Reel Cover Thumbnail
$router->map('POST', '/api/reels/thumbnail', 'App\controller\members\ReelsController@updateThumbnail', 'family_reels_thumbnail_api');


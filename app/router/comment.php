<?php 

// GET ALL COMMENT  BY NO
$router->map('GET', '/comment/newComment', 'App\controller\members\PostMessage@getNewCommentSSE', 'get_new_comment');



$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');

$router->map('POST', '/api/reactions/add', 'App\controller\members\CommentReactionController@addReaction', 'add_reaction');

// EDIT / DELETE A COMMENT (comment author or post author)
$router->map('PUT', '/comment/[i:commentNo]', 'App\controller\members\PostMessage@updateComment', 'update_comment');

$router->map('DELETE', '/comment/[i:commentNo]', 'App\controller\members\PostMessage@deleteComment', 'delete_comment');

// COMMENT VIEWS TRACKING & AUTHOR VIEWERS INSPECTION
$router->map('POST', '/api/comments/view', 'App\controller\members\PostMessage@recordCommentView', 'api_comment_record_view');
$router->map('POST', '/comment/view', 'App\controller\members\PostMessage@recordCommentView', 'comment_record_view');
$router->map('GET', '/api/comments/[i:commentNo]/viewers', 'App\controller\members\PostMessage@getCommentViewers', 'api_comment_get_viewers');
$router->map('GET', '/comment/[i:commentNo]/viewers', 'App\controller\members\PostMessage@getCommentViewers', 'comment_get_viewers');

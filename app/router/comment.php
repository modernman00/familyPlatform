<?php 

// GET ALL COMMENT  BY NO
$router->map('GET', '/comment/newComment', 'App\controller\members\PostMessage@getNewCommentSSE', 'get_new_comment');



$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');
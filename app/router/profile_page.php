<?php
// POST UPDATES
$router->map('POST', '/member/profilePage/post', 'App\controller\members\ProfilePage@post', 'profile_page_post');

$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');


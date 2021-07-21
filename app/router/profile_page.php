<?php
// POST UPDATES
$router->map('POST', '/member/profilePage/post', 'App\controller\members\ProfilePage@post', 'profile_page_post');

// GET ALL MEMBERS POST

$router->map('GET', '/post/getAllPost', 'App\controller\members\PostMessage@index', 'all_posts');

// GET ALL MEMBERS POST BY NO

$router->map('GET', '/post/getAllPost/byNumber', 'App\controller\members\PostMessage@getPostNo', 'all_posts_by_last_no');

// GET ALL COMMENT 
$router->map('GET', '/member/pp/comment', 'App\controller\members\PostMessage@getComment', 'all_comment');

//commentByNo

// GET ALL COMMENT  BY NO
$router->map('GET', '/member/pp/comment/byNumber', 'App\controller\members\PostMessage@getCommentNo', 'all_comment_by_number');

// GET NEW POST
$router->map('GET', '/post/getAllPost/update', 'App\controller\members\PostMessage@update', 'all_posts_update');

$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');

$router->map('POST', '/member/profilePage/profileImg', 'App\controller\members\ProfilePage@profileImage', 'profile_page_profileImg');

$router->map('POST', '/photos/postImages', 'App\controller\members\ProfilePage@postPics', 'MEMBERS_IMG_POST');

$router->map('GET', '/profilepage/img', 'App\controller\members\ProfilePage@showPics', 'MEMBERS_IMG_POST_SHOW');

// $router->map('POST', '/member/createEvent', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');

$router->map('POST', '/member/profilePage', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');

$router->map('GET', '/member/profilePage/setHeader', 'App\controller\members\PostCard@setHeader', 'HEADER_SET');



$router->map('GET', '/member/sendReminders', 'App\controller\members\Event@sendReminder', 'SEND_REMINDER_EVENT');

$router->map('GET', '/profileCard/getLikes', 'App\controller\members\PostCard@likeFunction', 'likeCounter');

// $router->map('GET', '/member/profileCard/getComments', 'App\model\Post@likeFunction', 'likeCounter');



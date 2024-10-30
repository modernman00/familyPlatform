<?php

// profile page member
$router->map('GET', '/member/ProfilePage', 'App\controller\members\ProfilePage@index', 'opening page member');

// POST UPDATES
$router->map('POST', '/member/profilePage/post', 'App\controller\members\ProfilePage@post', 'profile_page_post');

// GET ALL MEMBERS POST

$router->map('GET', '/post/getAllPostCommentByFamCode', 'App\controller\members\PostMessage@index', 'all_posts');

// GET new post and email

$router->map('GET', '/post/getNewPostAndEmail', 'App\controller\members\PostMessage@getNewPostAndEmail', 'get_new_post_email');

// get new post 
$router->map(
  method: 'GET', 
  route: '/post/getNewPost', 
  target: 'App\controller\members\PostMessage@getNewPost', name: 'get_new_post_FOR_SSE');


// GET ALL COMMENT 
// $router->map('GET', '/member/pp/comment', 'App\controller\members\PostMessage@getComment', 'all_comment');

//commentByNo

// GET ALL COMMENT  BY NO
$router->map('GET', '/comment/newComment', 'App\controller\members\PostMessage@getNewComment', 'get_new_comment');

// GET NEW POST
$router->map('GET', '/post/getAllPost/update', 'App\controller\members\PostMessage@update', 'all_posts_update');

$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');

$router->map('POST', '/member/profilePage/profileImg', 'App\controller\members\ProfilePage@profileImage', 'profile_page_profileImg');

$router->map('POST', '/photos/postImages', 'App\controller\members\ProfilePage@postPics', 'MEMBERS_IMG_POST');

$router->map('GET', '/profilepage/img', 'App\controller\members\ProfilePage@showPics', 'MEMBERS_IMG_POST_SHOW');

// $router->map('POST', '/member/createEvent', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');

$router->map('POST', '/member/profilePage/event', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');

$router->map('GET', '/member/profilePage/setHeader', 'App\controller\members\ProfilePage@setHeader', 'HEADER_SET');



$router->map('GET', '/member/sendReminders', 'App\controller\members\Event@sendReminder', 'SEND_REMINDER_EVENT');

$router->map(
  method: 'GET', 
  route: '/profileCard/getLikes', 
  target: 'App\controller\members\PostLikeController@getLikes', name: 'getLikeCounter');

$router->map('PUT', '/profileCard/postLikes', 'App\controller\members\PostLikeController@postLikes', 'postLikeCounter');

$router->map('GET', '/member/getEventData', 'App\controller\members\Event@getEventData', 'getEventData');

$router->map('GET', '/member/getEventByNo', 'App\controller\members\Event@getEventByNo', 'getEvent_by_no');

// $router->map('GET', '/member/profileCard/getComments', 'App\model\Post@likeFunction', 'likeCounter');



<?php

// profile page member
$router->map('GET', '/member/ProfilePage', 'App\controller\members\ProfilePage@index', 'opening page member');



// GET ALL COMMENT 
// $router->map('GET', '/member/pp/comment', 'App\controller\members\PostMessage@getComment', 'all_comment');

//commentByNo



$router->map('POST', '/member/profilePage/profileImg', 'App\controller\members\ProfilePage@profileImage', 'profile_page_profileImg');

$router->map('POST', '/photos/postImages', 'App\controller\members\ProfilePage@postPics', 'MEMBERS_IMG_POST');

$router->map('GET', '/profilepage/img', 'App\controller\members\ProfilePage@showPics', 'MEMBERS_IMG_POST_SHOW');

// $router->map('POST', '/member/createEvent', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');



$router->map('GET', '/member/profilePage/setHeader', 'App\controller\members\ProfilePage@setHeader', 'HEADER_SET');



$router->map(
  method: 'GET', 
  route: '/profileCard/getLikes', 
  target: 'App\controller\members\PostLikeController@getLikes', name: 'getLikeCounter');

$router->map('PUT', '/profileCard/postLikes', 'App\controller\members\PostLikeController@postLikes', 'postLikeCounter');



// $router->map('GET', '/member/profileCard/getComments', 'App\model\Post@likeFunction', 'likeCounter');



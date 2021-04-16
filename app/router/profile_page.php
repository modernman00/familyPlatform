<?php
// POST UPDATES
$router->map('POST', '/member/profilePage/post', 'App\controller\members\ProfilePage@post', 'profile_page_post');

$router->map('POST', '/postCommentProfile', 'App\controller\members\ProfilePage@postComment', 'profile_page_comment');

$router->map('POST', '/member/profilePage/profileImg', 'App\controller\members\ProfilePage@profileImage', 'profile_page_profileImg');

$router->map('POST', '/photos/postImages', 'App\controller\members\ProfilePage@postPics', 'MEMBERS_IMG_POST');

$router->map('GET', '/profilepage/img', 'App\controller\members\ProfilePage@showPics', 'MEMBERS_IMG_POST_SHOW');

$router->map('POST', '/member/createEvent', 'App\controller\members\ProfilePage@processEvent', 'PROCESS_EVENT');



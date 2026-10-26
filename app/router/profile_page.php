<?php

// profile page member
$router->map('GET', '/member/ProfilePage', 'App\controller\members\ProfilePage@index', 'opening page member');
$router->map('GET', '/profilePage', 'App\controller\members\ProfilePage@index', 'opening page member alias');
$router->map('POST', '/member/profilePage/editProfile', 'App\controller\members\SettingController@post', 'edit_profile_post');



// GET ALL COMMENT 

// $router->map('GET', '/member/pp/comment', 'App\controller\members\PostMessage@getComment', 'all_comment');

//commentByNo



$router->map('POST', '/member/profilePage/profileImg', 'App\controller\members\ProfilePage@profileImage', 'profile_page_profileImg');

$router->map('POST', '/photos/postImages', 'App\controller\members\ProfilePage@postPics', 'MEMBERS_IMG_POST');

$router->map('GET', '/profilepage/gallery', 'App\controller\members\ProfilePage@gallery', 'MEMBERS_IMG_GALLERY');
$router->map('GET', '/profilepage/img', 'App\controller\members\ProfilePage@gallery', 'MEMBERS_IMG_POST_SHOW');
$router->map('POST', '/setProfilePicFromImage', 'App\controller\members\ProfilePage@setProfilePicFromImage', 'SET_PROFILE_PIC_FROM_IMG');
$router->map('POST', '/profilepage/gallery/toggle-visibility', 'App\controller\members\ProfilePage@toggleImageVisibility', 'TOGGLE_IMAGE_VISIBILITY');

// $router->map('POST', '/member/createEvent', 'App\controller\members\Event@submitEvent', 'PROCESS_EVENT');



$router->map('GET', '/member/profilePage/setHeader', 'App\controller\members\ProfilePage@setHeader', 'HEADER_SET');



$router->map(
  method: 'GET', 
  route: '/profileCard/getLikes', 
  target: 'App\controller\members\PostLikeController@getLikes', name: 'getLikeCounter');

$router->map('PUT', '/profileCard/postLikes', 'App\controller\members\PostLikeController@postLikes', 'postLikeCounter');

// AI Family Hub Routes
$router->map('GET', '/family/ai-hub', 'App\controller\members\FamilyAiHubController@index', 'family_ai_hub');
$router->map('POST', '/api/ai/arbitrate-dispute', 'App\controller\members\FamilyAiHubController@arbitrateDispute', 'api_ai_arbitrate_dispute');
$router->map('POST', '/api/ai/inspect-fridge', 'App\controller\members\FamilyAiHubController@inspectFridge', 'api_ai_inspect_fridge');
$router->map('POST', '/api/ai/transcribe-voice', 'App\controller\members\FamilyAiHubController@transcribeVoice', 'api_ai_transcribe_voice');
$router->map('POST', '/api/ai/generate-flyer', 'App\controller\members\FamilyAiHubController@generateFlyer', 'api_ai_generate_flyer');
$router->map('POST', '/api/ai/generate-trivia', 'App\controller\members\FamilyAiHubController@generateTrivia', 'api_ai_generate_trivia');



// $router->map('GET', '/member/profileCard/getComments', 'App\model\Post@likeFunction', 'likeCounter');



<?php

// profile page member
$router->map('GET', '/member/ProfilePage', 'App\controller\members\ProfilePage@index', 'opening page member');
$router->map('GET', '/member/profilePage', 'App\controller\members\ProfilePage@index', 'opening page member lowercase prefix alias');
$router->map('GET', '/member/profilepage', 'App\controller\members\ProfilePage@index', 'opening page member all lowercase alias');
$router->map('GET', '/profilePage', 'App\controller\members\ProfilePage@index', 'opening page member alias');
$router->map('GET', '/profilepage', 'App\controller\members\ProfilePage@index', 'opening page member lowercase alias');
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

// Family RoundTable Routes (Kinship Reconciliation & Council)
$router->map('GET', '/family/roundtable', 'App\controller\members\FamilyRoundTableController@index', 'family_roundtable');
$router->map('POST', '/api/roundtable/file-dispute', 'App\controller\members\FamilyRoundTableController@fileDispute', 'api_roundtable_file_dispute');
$router->map('POST', '/api/roundtable/submit-statement', 'App\controller\members\FamilyRoundTableController@submitStatement', 'api_roundtable_submit_statement');
$router->map('POST', '/api/roundtable/adjudicate', 'App\controller\members\FamilyRoundTableController@adjudicate', 'api_roundtable_adjudicate');
$router->map('GET', '/api/roundtable/dispute/[*:id]', 'App\controller\members\FamilyRoundTableController@getDisputeDetails', 'api_roundtable_get_dispute');
$router->map('POST', '/api/roundtable/arbitrate-instant', 'App\controller\members\FamilyRoundTableController@arbitrateInstant', 'api_roundtable_arbitrate_instant');

// Backward-compatible aliases for tribunal endpoints
$router->map('POST', '/api/fun-place/file-dispute', 'App\controller\members\FamilyRoundTableController@fileDispute', 'alias_fun_place_file_dispute');
$router->map('POST', '/api/fun-place/submit-statement', 'App\controller\members\FamilyRoundTableController@submitStatement', 'alias_fun_place_submit_statement');
$router->map('POST', '/api/fun-place/adjudicate', 'App\controller\members\FamilyRoundTableController@adjudicate', 'alias_fun_place_adjudicate');
$router->map('GET', '/api/fun-place/dispute/[*:id]', 'App\controller\members\FamilyRoundTableController@getDisputeDetails', 'alias_fun_place_get_dispute');

// The Fun Place Routes (Creative Household AI & Games)
$router->map('GET', '/family/fun-place', 'App\controller\members\FamilyAiHubController@index', 'family_fun_place');
$router->map('GET', '/family/ai-hub', 'App\controller\members\FamilyAiHubController@index', 'family_ai_hub');
$router->map('POST', '/api/ai/arbitrate-dispute', 'App\controller\members\FamilyAiHubController@arbitrateDispute', 'api_ai_arbitrate_dispute');
$router->map('POST', '/api/ai/inspect-fridge', 'App\controller\members\FamilyAiHubController@inspectFridge', 'api_ai_inspect_fridge');
$router->map('POST', '/api/ai/transcribe-voice', 'App\controller\members\FamilyAiHubController@transcribeVoice', 'api_ai_transcribe_voice');
$router->map('POST', '/api/ai/generate-flyer', 'App\controller\members\FamilyAiHubController@generateFlyer', 'api_ai_generate_flyer');
$router->map('POST', '/api/ai/generate-trivia', 'App\controller\members\FamilyAiHubController@generateTrivia', 'api_ai_generate_trivia');
$router->map('POST', '/api/ai/weekend-outings', 'App\controller\members\FamilyAiHubController@discoverWeekendOutings', 'api_ai_weekend_outings');
$router->map('POST', '/api/ai/post-outings-poll', 'App\controller\members\FamilyAiHubController@postOutingsPoll', 'api_ai_post_outings_poll');



// $router->map('GET', '/member/profileCard/getComments', 'App\model\Post@likeFunction', 'likeCounter');



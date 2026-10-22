<?php

// POST UPDATES
$router->map(
  method: 'POST', 
  route: '/member/profilePage/post', 
  target: 'App\controller\members\ProfilePage@post', 
  name: 'profile_page_post');

// GET ALL MEMBERS POST

$router->map(
  method:  'GET', 
  route: '/post/getAllPostCommentByFamCode', 
  target: 'App\controller\members\PostMessage@index', 
  name: 'all_posts');

// GET new post and email

$router->map(
  method: 'GET', 
  route: '/post/getNewPostAndEmail', 
  target: 'App\controller\members\PostMessage@getNewPostAndEmail', name: 'get_new_post_email');

// get new post 
$router->map(
  method: 'GET', 
  route: '/post/getNewPost', 
  target: 'App\controller\members\PostMessage@getNewPostSSE', name: 'get_new_post_FOR_SSE');

  // GET NEW POST
$router->map(
  method: 'GET',
  route: '/post/getAllPost/update',
  target: 'App\controller\members\PostMessage@update',
  name: 'all_posts_update'
);

// EDIT / DELETE A POST (author only)
$router->map(
  method: 'PUT',
  route: '/post/[i:postNo]',
  target: 'App\controller\members\PostMessage@updatePost',
  name: 'update_post'
);

$router->map(
  method: 'DELETE',
  route: '/post/[i:postNo]',
  target: 'App\controller\members\PostMessage@deletePost',
  name: 'delete_post'
);

$router->map(
  method: 'POST',
  route: '/api/video/direct-upload',
  target: 'App\controller\members\StreamController@getDirectUploadUrl',
  name: 'direct_video_upload'
);

// FAMILY HASHTAGS & MEMORY PILLS API
$router->map(
  method: 'GET',
  route: '/api/hashtags/trending',
  target: 'App\controller\members\HashtagController@trending',
  name: 'api_hashtags_trending'
);

$router->map(
  method: 'GET',
  route: '/api/hashtags/suggest',
  target: 'App\controller\members\HashtagController@suggest',
  name: 'api_hashtags_suggest'
);

$router->map(
  method: 'GET',
  route: '/api/hashtags/stream',
  target: 'App\controller\members\HashtagController@stream',
  name: 'api_hashtags_stream'
);

// LIVE OPENGRAPH LINK PREVIEW API
$router->map(
  method: 'GET',
  route: '/api/link-preview',
  target: 'App\controller\members\LinkPreviewController@preview',
  name: 'api_link_preview'
);

// POST & MEMBER TAGGING APIS
$router->map(
  method: 'GET',
  route: '/api/members/tag-suggestions',
  target: 'App\controller\members\TagController@searchTagSuggestions',
  name: 'api_members_tag_suggestions'
);

$router->map(
  method: 'POST',
  route: '/api/posts/[i:postNo]/tags',
  target: 'App\controller\members\TagController@addPostTags',
  name: 'api_posts_add_tags'
);

$router->map(
  method: 'DELETE',
  route: '/api/posts/[i:postNo]/tags/[*:userId]',
  target: 'App\controller\members\TagController@removePostTag',
  name: 'api_posts_remove_tag'
);

// Native iOS / Apple Photos Share Sheet Endpoints
$router->map(
  method: 'POST',
  route: '/api/media/native-share',
  target: 'App\controller\members\NativeShareController@share',
  name: 'api_media_native_share'
);

$router->map(
  method: 'GET',
  route: '/api/media/ios-shortcut',
  target: 'App\controller\members\NativeShareController@shortcutConfig',
  name: 'api_media_ios_shortcut'
);

$router->map(
  method: 'GET',
  route: '/media/ios-shortcut',
  target: 'App\controller\members\NativeShareController@shortcutConfig',
  name: 'media_ios_shortcut'
);

// W3C Web Share Target API Level 2 (POST from Native OS Gallery Share Sheet)
$router->map(
  method: 'POST',
  route: '/share-target',
  target: 'App\controller\members\ShareTargetController@receive',
  name: 'pwa_web_share_target'
);

$router->map(
  method: 'GET',
  route: '/api/media/pwa-shared',
  target: 'App\controller\members\ShareTargetController@getPendingShare',
  name: 'api_media_pwa_shared'
);



<?php

// Serve profile images behind JWT authentication via /resources/images/ paths.
// [*:imgName] matches dots and spaces. The controller sanitises with basename().
$router->map('GET', '/resources/images/profile/[*:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img');

// Serve post images behind JWT authentication
$router->map('GET', '/resources/images/post/[*:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img');

// Serve reel videos behind HTTP 206 streaming
$router->map('GET|HEAD', '/resources/videos/reels/[*:videoName]', 'App\controller\ServeImgController@StreamReel', 'serve_reel_video');

// Serve post videos behind HTTP 206 streaming
$router->map('GET|HEAD', '/resources/videos/posts/[*:videoName]', 'App\controller\ServeImgController@StreamPostVideo', 'serve_post_video');

// Serve external linked video streams behind same-origin HTTP 206 streaming proxy
$router->map('GET|HEAD', '/resources/videos/external-stream', 'App\controller\ServeImgController@StreamExternalVideo', 'serve_external_video');

// Serve reel thumbnails
$router->map('GET', '/resources/images/reels/thumbs/[*:thumbName]', 'App\controller\ServeImgController@ReelThumb', 'serve_reel_thumb');

<?php

// Serve profile images behind JWT authentication via /resources/images/ paths.
// [*:imgName] matches dots and spaces. The controller sanitises with basename().
$router->map('GET', '/resources/images/profile/[*:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img');

// Serve post images behind JWT authentication
$router->map('GET', '/resources/images/post/[*:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img');

// Serve reel videos behind HTTP 206 streaming
$router->map('GET', '/resources/videos/reels/[*:videoName]', 'App\controller\ServeImgController@StreamReel', 'serve_reel_video');

// Serve reel thumbnails
$router->map('GET', '/resources/images/reels/thumbs/[*:thumbName]', 'App\controller\ServeImgController@ReelThumb', 'serve_reel_thumb');

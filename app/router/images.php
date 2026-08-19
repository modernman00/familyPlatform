<?php

// Serve profile images that aren't physically in public/img/profile/ (fallback)
// [*:imgName] matches dots and spaces. The controller sanitises with basename().
$router->map('GET', '/public/img/profile/[*:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img');
$router->map('GET', '/resources/images/profile/[*:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img_legacy');

// Serve post images that aren't physically in public/img/post/ (fallback)
$router->map('GET', '/public/img/post/[*:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img');
$router->map('GET', '/resources/images/post/[*:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img_legacy');

<?php

// Serve profile images behind JWT authentication via /resources/images/ paths.
// [*:imgName] matches dots and spaces. The controller sanitises with basename().
$router->map('GET', '/resources/images/profile/[*:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img');

// Serve post images behind JWT authentication
$router->map('GET', '/resources/images/post/[*:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img');

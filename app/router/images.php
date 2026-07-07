<?php

// Serve profile images from resources/images/profile/ URL
$router->map('GET', '/resources/images/profile/[:imgName]', 'App\controller\ServeImgController@ProfileDir', 'serve_profile_img');

// Serve post images from resources/images/post/ URL
$router->map('GET', '/resources/images/post/[:imgName]', 'App\controller\ServeImgController@PostDir', 'serve_post_img');

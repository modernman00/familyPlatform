<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

$router->map('GET', '/aboutus', 'App\controller\About@index', 'About');

$router->map('GET', '/privacy', 'App\controller\Index@privacy', 'privacy');

$router->map('GET', '/terms', 'App\controller\Index@terms', 'terms');

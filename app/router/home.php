<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

$router->map('GET', '/about', 'App\controller\About@index', 'About');

$router->map('GET', '/learn', 'App\controller\About@testing2', 'About2');
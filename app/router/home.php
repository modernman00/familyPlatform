<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

// test for the webpage trialled
$router->map('GET', '/boot', 'App\controller\Index@boot', 'Boot');

$router->map('GET', '/about', 'App\controller\About@index', 'About');
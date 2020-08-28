<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// test for the webpage trialled
$router->map('GET', '/boot', 'App\controller\Index@boot', 'Boot');

$router->map('GET', '/about', 'App\controller\About@index', 'About');
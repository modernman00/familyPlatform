<?php

$router = new AltoRouter;

// homepage
$router->map('GET', '/', 'App\controller\Index@index', 'Home');

$router->map('GET', '/about/us', 'App\controller\About@index', 'About');

$router->map('GET', '/register', 'App\controller\Register@index', 'Register');

$router->map('POST', '/register', 'App\controller\Register@submitForm', 'submit Register');

$router->map('GET', '/createTable', 'App\controller\Create@index', 'create Table');

<?php

$router = new AltoRouter;

// homepage
$router->map('GET', '/', 'App\controller\Index@index', 'Home');

$router->map('GET', '/about', 'App\controller\About@index', 'About');

$router->map('GET', '/createTable', 'App\controller\Create@index', 'create Table');
include __DIR__ . "/register.php";
include __DIR__ . "/login.php";
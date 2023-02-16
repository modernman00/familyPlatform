<?php

$router = new AltoRouter;

include_once __DIR__ ."/home.php";
include_once __DIR__ ."/dbTable.php";
include_once __DIR__ . "/register.php";

include_once __DIR__ . "/login.php";
include_once __DIR__ . "/realtime.php";
include_once __DIR__ . "/admin.php";
include_once __DIR__ . "/profile_page.php";
include_once __DIR__ . "/allMembersRoute.php";
include_once __DIR__ . "/chat.php";
include_once __DIR__ . "/Event.php";

// $router->map('GET', '/php8', 'App\controller\Testphp@index', 'php8');
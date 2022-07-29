
<?php

$router = new AltoRouter;

include __DIR__ ."/home.php";
include __DIR__ ."/dbTable.php";
include __DIR__ . "/register.php";
include __DIR__ . "/login.php";
include __DIR__ . "/realtime.php";
include __DIR__ . "/admin.php";
include __DIR__ . "/profile_page.php";
include __DIR__ . "/allMembersRoute.php";
include __DIR__ . "/chat.php";
include __DIR__ . "/Event.php";

$router->map('GET', '/php8', 'App\controller\Testphp@index', 'php8');
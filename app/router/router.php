<?php
declare(strict_types =1);

$router = new AltoRouter;

include_once __DIR__ ."/home.php";
include_once __DIR__ ."/dbTable.php";
include_once __DIR__ . "/register.php";
include_once __DIR__ . "/post.php";
include_once __DIR__ . "/comment.php";
include_once __DIR__ . "/login.php";
include_once __DIR__ . "/realtime.php";
include_once __DIR__ . "/admin.php";
include_once __DIR__ . "/profile_page.php";
include_once __DIR__ . "/allMembersRoute.php";
include_once __DIR__ . "/chat.php";
include_once __DIR__ . "/Event.php";
include_once __DIR__ . "/notification.php";
include_once __DIR__ . "/friendRequest.php";
include_once __DIR__ . "/pushNotification.php";
include_once __DIR__ . "/organogram.php";
include_once __DIR__ . "/setting.php";
include_once __DIR__ . "/images.php";
include_once __DIR__ . "/approvalRoute.php";
include_once __DIR__ . "/engagementRoute.php";
include_once __DIR__ . "/blog.php";
$router->map('GET', '/checking', 'App\controller\Index@checking', 'checking');


$router->map('GET', '/getEmails', 'App\controller\Index@getEmails', 'GET_EMAILS');



// Temporary Cache Clearing Route
$router->map('GET|POST', '/clearcache/?', 'App\controller\ClearCache@clear', 'clear-cache');
include_once __DIR__ . "/cron.php";

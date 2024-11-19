<?php
declare(strict_types =1);

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
include_once __DIR__ . "/notification.php";
include_once __DIR__ . "/friendRequest.php";
include_once __DIR__ . "/webSocket.php";


$router->map('GET', '/organogram', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');

$router->map('GET', '/testFunction', 'App\controller\Testphp@testFunction', 'php8');
$router->map('GET', '/testOrganogram', 'App\controller\TestClass@organogram', 'TestClass');

$router->map('GET', '/getEmails', 'App\controller\Index@getEmails', 'GET_EMAILS');


// example of new parameter based url
$router->map(
  method: 'GET', 
  route: '/member/test/[a:req]/[a:res]', 
  target: 'App\controller\members\FamilyRequestController@test', 
  name: 'testing new route method'
);

// example of new parameter based url
$router->map(
  method: 'GET', 
  route: '/member/testURL', 
  target: 'App\controller\members\FamilyRequest@testURL', 
  name: 'testing new route method2'
);

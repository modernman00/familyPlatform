<?php

$router->map('GET', '/allMembers', 'App\controller\members\AllMembersController@index', 'SHOW_ALL_MEMBERS');

$router->map('GET', '/allMembers/processApiData', 'App\controller\members\AllMembersController@processApiData', 'SHOW_ALL_MEMBERS3');

$router->map('GET', '/allMembers/setProfile', 'App\controller\members\AllMembersController@setProfile', 'MEMBERS_PROFILE_SET');

$router->map('GET', '/allMembers/getProfile', 'App\controller\members\AllMembersController@getProfile', 'MEMBERS_PROFILE_GET');

$router->map('GET', '/organogram', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');








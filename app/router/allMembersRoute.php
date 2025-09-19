<?php

$router->map('GET', '/allMembers', 'App\controller\members\AllMembersController@index', 'SHOW_ALL_MEMBERS');

$router->map('GET', '/allMembers/processApiData', 'App\controller\members\AllMembersController@processApiData', 'SHOW_ALL_MEMBERS3');

$router->map('GET', '/allMembers/processApiData2', 'App\controller\members\AllMembersController@processApiData2', 'SHOW_ALL_MEMBERS33');

$router->map('GET', '/allMembers/seeProfile/[a:id]', 'App\controller\members\AllMembersController@getProfile', 'MEMBERS_PROFILE_SET');

$router->map('DELETE', '/allMembers/removeProfile/[a:apr]/[a:req]', 'App\controller\members\AllMembersController@removeProfile', 'REMOVE_MEMBERS_PROFILE_SET');

$router->map('GET', '/allMembers/getProfile', 'App\controller\members\AllMembersController@getProfile', 'MEMBERS_PROFILE_GET');













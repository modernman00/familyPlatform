<?php

$router->map('GET', '/allMembers', 'App\controller\members\AllMembersController@index', 'SHOW_ALL_MEMBERS');

$router->map('GET', '/allMembers/processApiData', 'App\controller\members\AllMembersController@processApiData', 'SHOW_ALL_MEMBERS3');

$router->map('GET', '/allMembers/processApiData2', 'App\controller\members\AllMembersController@processApiData2', 'SHOW_ALL_MEMBERS33');

$router->map('GET', '/allMembers/setProfile/[a:id]', 'App\controller\members\AllMembersController@getProfile', 'MEMBERS_PROFILE_SET');

$router->map('DELETE', '/allMembers/removeProfile/[a:apr]/[a:req]', 'App\controller\members\AllMembersController@removeProfile', 'REMOVE_MEMBERS_PROFILE_SET');

$router->map('GET', '/allMembers/getProfile', 'App\controller\members\AllMembersController@getProfile', 'MEMBERS_PROFILE_GET');

$router->map('GET', '/organogram', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');

$router->map('POST', '/members/familyRequestMgt', 'App\controller\members\FamilyRequest@request', 'add to family');

$router->map('GET', '/members/familyRequestMgt/getApprover', 'App\controller\members\FamilyRequest@getApprover', 'approver');

$router->map(
  method: 'GET', 
  route: '/member/request/[a:req]/[a:appr]/[a:dec]/[a:reqCode]/[a:src]', 
  target: 'App\controller\members\FamilyRequest@approveDelete', 
  name: 'approve_request'
);











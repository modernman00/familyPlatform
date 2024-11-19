<?php

$router->map('GET', '/getFriendRequestById', 'App\controller\members\FamilyRequestController@getFriendRequestData', 'get friend request');

$router->map('POST', '/members/familyRequestMgt', 'App\controller\members\FamilyRequestController@request', 'add to family');

$router->map('GET', '/members/familyRequestMgt/getApprover', 'App\controller\members\FamilyRequestController@getApprover', 'approver');

$router->map(
  method: 'GET', 
  route: '/member/request/[a:req]/[a:appr]/[a:dec]/[a:reqCode]/[a:src]', 
  target: 'App\controller\members\FamilyRequestController@approveDelete', 
  name: 'approve_request'
);

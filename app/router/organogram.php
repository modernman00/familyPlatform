<?php

$router->map('GET', '/getSingleMemberData', 'App\model\SingleCustomerData@getCustomerData', 'getSingleMemberData');

$router->map('GET', '/organogram/[a:id]', 'App\controller\members\Organogram@index', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');
$router->map('GET', '/organogram', 'App\controller\members\Organogram@index', 'App\controller\members\Organogram@index_default', 'MEMBERS_ORGANOGRAM_DEFAULT');

// JSON API Endpoints for 6-Gen Interactive Graph & Slide-Out Dossier
$router->map('GET', '/member/organogram/data/[a:id]', 'App\controller\members\Organogram@getGraphData', 'MEMBER_ORGANOGRAM_DATA');
$router->map('GET', '/member/organogram/node/[i:id]', 'App\controller\members\Organogram@getNodeDetails', 'MEMBER_ORGANOGRAM_NODE_DETAILS');
$router->map('POST', '/member/organogram/editor/partner', 'App\controller\members\OrganogramEditorController@addPartner', 'MEMBER_ORGANOGRAM_ADD_PARTNER');
$router->map('POST', '/member/organogram/editor/child', 'App\controller\members\OrganogramEditorController@addChild', 'MEMBER_ORGANOGRAM_ADD_CHILD');

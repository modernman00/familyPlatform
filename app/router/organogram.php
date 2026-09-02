<?php

$router->map('GET', '/getSingleMemberData', 'App\model\SingleCustomerData@getCustomerData', 'getSingleMemberData');

$router->map('GET', '/organogram/[a:id]', 'App\controller\members\Organogram@index', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');
$router->map('GET', '/organogram', 'App\controller\members\Organogram@index', 'App\controller\members\Organogram@index_default', 'MEMBERS_ORGANOGRAM_DEFAULT');

$router->map('GET', '/familyStudio/[a:id]', 'App\controller\members\Organogram@familyStudio', 'App\controller\members\Organogram@familyStudio', 'MEMBERS_FAMILY_STUDIO');
$router->map('GET', '/familyStudio', 'App\controller\members\Organogram@familyStudio', 'App\controller\members\Organogram@familyStudio_default', 'MEMBERS_FAMILY_STUDIO_DEFAULT');

// JSON API Endpoints for 6-Gen Interactive Graph & Slide-Out Dossier
$router->map('GET', '/member/organogram/data/[*:id]', 'App\controller\members\Organogram@getGraphData', 'MEMBER_ORGANOGRAM_DATA');
$router->map('GET', '/member/organogram/node/[*:id]', 'App\controller\members\Organogram@getNodeDetails', 'MEMBER_ORGANOGRAM_NODE_DETAILS');
$router->map('POST', '/member/organogram/editor/partner', 'App\controller\members\OrganogramEditorController@addPartner', 'MEMBER_ORGANOGRAM_ADD_PARTNER');
$router->map('POST', '/member/organogram/editor/child', 'App\controller\members\OrganogramEditorController@addChild', 'MEMBER_ORGANOGRAM_ADD_CHILD');
$router->map('POST', '/member/organogram/editor/parents', 'App\controller\members\OrganogramEditorController@addParents', 'MEMBER_ORGANOGRAM_ADD_PARENTS');
$router->map('POST', '/member/organogram/editor/sibling', 'App\controller\members\OrganogramEditorController@addSibling', 'MEMBER_ORGANOGRAM_ADD_SIBLING');
$router->map('POST', '/member/organogram/editor/update', 'App\controller\members\OrganogramEditorController@updateNode', 'MEMBER_ORGANOGRAM_UPDATE_NODE');
$router->map('POST', '/api/claim-family-node', 'App\controller\members\Organogram@claimNode', 'API_CLAIM_FAMILY_NODE');
$router->map('POST', '/api/dismiss-claim-node', 'App\controller\members\Organogram@dismissClaimNode', 'API_DISMISS_CLAIM_NODE');

<?php

$router->map('GET', '/getSingleMemberData', 'App\model\SingleCustomerData@getCustomerData', 'getSingleMemberData');

$router->map('GET', '/organogram/[a:id]', 'App\controller\members\Organogram@index', 'App\controller\members\Organogram@index', 'MEMBERS_ORGANOGRAM');

<?php

$router->map('GET', '/createTable', 'App\controller\Create@index', 'create Table');

$router->map('GET', '/createRequest', 'App\controller\Create@createFriendRequest', 'createFriendRequest');

$router->map('GET', '/alterTable', 'App\controller\AddCol@addCol', 'alter Table');

$router->map('GET', '/alterTableArr', 'App\controller\AddCol@addColArr', 'alter Table Array');

$router->map('GET', '/createNotification', 'App\controller\Create@notification', 'notification Table');

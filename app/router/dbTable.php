<?php

$router->map('GET', '/createTable', 'App\controller\Create@index', 'create Table');

$router->map('GET', '/alterTable', 'App\controller\AddCol@addCol', 'alter Table');
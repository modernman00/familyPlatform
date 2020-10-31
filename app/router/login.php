<?php

$router->map('GET', '/login', 'App\controller\login\Login@index', 'login');


$router->map('POST', '/login', 'App\controller\login\Login@login', 'Customer Login');

$router->map('GET', '/lasu', 'App\controller\login\Login@showAdmin', 'Admin Login Page');
$router->map('POST', '/lasu', 'App\controller\login\Login@login', 'Login');

$router->map('POST', '/lasu', 'App\controller\login\Login@login', 'Process_Login');


//admin SIGN OUT 
$router->map('GET', '/admin/signout', 'App\controller\login\Login@adminSignOut', 'ADMIN SIGN OUT' );

$router->map('GET', '/signout', 'App\controller\login\Login@adminSignOut', 'signout');

// CODE 


$router->map('GET', '/auth/code', 'App\controller\login\Code@show', 'code');

$router->map('POST', '/auth/code', 'App\controller\login\Code@verify', 'verify cost');

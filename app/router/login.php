<?php

$router->map('GET', '/login', 'App\controller\login\Login@show', 'login');


$router->map('POST', '/login', 'App\controller\login\Login@login', 'Customer Login');

$router->map('GET', '/lasu', 'App\controller\login\Login@showAdmin', 'Admin Login Page');

$router->map('POST', '/lasu', 'App\controller\login\Login@login', 'admin_Login');

// Forgot
$router->map('GET', '/forgot', 'App\controller\login\Forgot@show', 'Forgot');

// FORGOT_POST
$router->map('POST', '/forgot', 'App\controller\login\Forgot@verify', 'ForgotPost');


//admin SIGN OUT 
$router->map('GET', '/admin/signout', 'App\controller\login\Login@adminSignOut', 'ADMIN SIGN OUT' );

// $router->map('GET', '/signout', 'App\controller\login\Login@adminSignOut', 'signout');

// LOG OUT PAGE
$router->map('GET', '/signout/[a:redirect]', 'App\controller\LogoutController@signout', 'signout');

// CODE 


$router->map('GET', '/code', 'App\controller\login\Code@show', 'code');

$router->map('POST', '/code', 'App\controller\login\Code@verify', 'verify cost');
// // forgot
// $router->map('GET', '/login/forgot', 'App\controller\login\Forgot@show', 'forgot_PASSWORD');

// $router->map('POST', '/login/forgot', 'App\controller\login\Forgot@verify', 'forgot_VERIFY');



// profile page member
$router->map('GET', '/admin/dashboard', 'App\controller\admin\Dashboard@index', 'opening page admin');

// change password
$router->map('GET', '/changePW', 'App\controller\login\PassChange@show', 'ChangePW_PASSWORD');

$router->map('POST', '/changePW', 'App\controller\login\PassChange@verify', 'ChangePW_VERIFY');


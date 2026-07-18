<?php

$router->map('GET', '/login', 'App\controller\login\Login@show', 'login');


$router->map('POST', '/login', 'App\controller\login\Login@login', 'Customer Login');

// WebAuthn Routes
$router->map('POST', '/webauthn/register/options', 'Src\functionality\WebAuthnFunctionality@getRegistrationOptions', 'WebAuthn Register Options');
$router->map('POST', '/webauthn/register', 'Src\functionality\WebAuthnFunctionality@registerDevice', 'WebAuthn Register');
$router->map('POST', '/webauthn/login/options', 'Src\functionality\WebAuthnFunctionality@getLoginOptions', 'WebAuthn Login Options');
$router->map('POST', '/webauthn/login', 'Src\functionality\WebAuthnFunctionality@login', 'WebAuthn Login');

$router->map('GET', '/lasu', 'App\controller\login\Login@showAdmin', 'Admin Login Page');

$router->map('POST', '/lasu', 'App\controller\login\Login@login', 'admin_Login');

// Forgot
$router->map('GET', '/login/forgot', 'App\controller\login\Forgot@show', 'Forgot');

// FORGOT_POST
$router->map('POST', '/login/forgot', 'App\controller\login\Forgot@verify', 'ForgotPost');


//admin SIGN OUT 
$router->map('GET', '/admin/signout', 'App\controller\login\Login@adminSignOut', 'ADMIN SIGN OUT');

// $router->map('GET', '/signout', 'App\controller\login\Login@adminSignOut', 'signout');

// LOG OUT PAGE
$router->map('GET', '/signout/[a:redirect]?', 'App\controller\LogoutController@signout', 'signout');

// CODE 


$router->map('GET', '/login/code', 'App\controller\login\Code@show', 'code');

$router->map('POST', '/login/code', 'App\controller\login\Code@verify', 'verify cost');
// // forgot
// $router->map('GET', '/login/forgot', 'App\controller\login\Forgot@show', 'forgot_PASSWORD');

// $router->map('POST', '/login/forgot', 'App\controller\login\Forgot@verify', 'forgot_VERIFY');



// profile page member
$router->map('GET', '/admin/dashboard', 'App\controller\admin\Dashboard@index', 'opening page admin');

// change password
$router->map('GET', '/login/changePW', 'App\controller\login\PassChange@show', 'ChangePW_PASSWORD');

$router->map('POST', '/login/changePW', 'App\controller\login\PassChange@verify', 'ChangePW_VERIFY');


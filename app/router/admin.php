<?php

// ── Zero-Trust Administrative Auth Routes (ADMIN_SECRET_PATH) ──────────────
$adminSecretPath = '/' . trim((string) ($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/');

$router->map('GET',  $adminSecretPath,                            'App\controller\admin\AdminAuthController@showLogin',       'admin_secret_root');
$router->map('GET',  $adminSecretPath . '/login',                 'App\controller\admin\AdminAuthController@showLogin',       'admin_login_show');
$router->map('POST', $adminSecretPath . '/login',                 'App\controller\admin\AdminAuthController@login',           'admin_login_post');
$router->map('GET',  $adminSecretPath . '/2fa',                   'App\controller\admin\AdminAuthController@show2fa',         'admin_2fa_show');
$router->map('POST', $adminSecretPath . '/2fa',                   'App\controller\admin\AdminAuthController@verify2fa',       'admin_2fa_post');
$router->map('GET',  $adminSecretPath . '/2fa/setup',             'App\controller\admin\AdminAuthController@show2faSetup',    'admin_2fa_setup_show');
$router->map('POST', $adminSecretPath . '/2fa/setup',             'App\controller\admin\AdminAuthController@save2faSetup',    'admin_2fa_setup_post');
$router->map('GET',  $adminSecretPath . '/forgot-password',       'App\controller\admin\AdminAuthController@showForgotPassword', 'admin_forgot_show');
$router->map('POST', $adminSecretPath . '/forgot-password',       'App\controller\admin\AdminAuthController@sendResetLink',   'admin_forgot_post');
$router->map('GET',  $adminSecretPath . '/reset-password',        'App\controller\admin\AdminAuthController@showResetPassword', 'admin_reset_show');
$router->map('POST', $adminSecretPath . '/reset-password',        'App\controller\admin\AdminAuthController@updatePassword',  'admin_reset_post');
$router->map('GET',  $adminSecretPath . '/logout',                'App\controller\admin\AdminAuthController@logout',          'admin_logout');

// Enforce Zero-Trust Administrative Security (IP Gating, Rate Limiting, Fingerprint Check)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (str_starts_with($requestUri, '/admin') && \class_exists('\App\middleware\AdminGuardMiddleware')) {
    \App\middleware\AdminGuardMiddleware::enforce();
}


// NEW APPLICATION
$router->map('GET', '/admin/reviewApps', 'App\controller\admin\ReviewApps@get', 'NEW APPLICATION');

$router->map('GET', '/admin/reviewApps/approval', 'App\controller\admin\ReviewApps@approve', 'NEW APPLICATION APPROVED');

$router->map('GET', '/admin/reviewApps/delete', 'App\controller\admin\ReviewApps@delete', 'NEW APPLICATION DELETED');

$router->map('GET', '/admin/reviewApps/decline', 'App\controller\admin\ReviewApps@decline', 'NEW APPLICATION DECLINED');

$router->map('GET', '/admin/reviewApps/cancel', 'App\controller\admin\ReviewApps@cancel', 'NEW APPLICATION CANCEL');

$router->map('GET', '/admin/dashboard', 'App\controller\admin\Dashboard@index', 'dashboard');

// BLOG MANAGEMENT
$router->map('GET', '/admin/blog/create', 'App\controller\admin\AdminBlogController@create', 'admin_blog_create');
$router->map('POST', '/admin/blog/store', 'App\controller\admin\AdminBlogController@store', 'admin_blog_store');

// GDPR DATA ERASURE MANAGEMENT
$router->map('GET', '/admin/erasure', 'App\controller\admin\AdminErasureController@index', 'admin_erasure_index');
$router->map('POST', '/admin/erasure/process', 'App\controller\admin\AdminErasureController@process', 'admin_erasure_process');

// TELEMETRY & RUM FRICTION DASHBOARD
$router->map('GET', '/admin/telemetry', 'App\controller\admin\AdminTelemetryController@index', 'admin_telemetry');

// GOOGLE AUTHENTICATOR (TOTP) 2-FA SETUP
$router->map('GET', '/admin/setup-2fa', 'App\controller\admin\TotpSetupController@show', 'admin_totp_setup');
$router->map('POST', '/admin/setup-2fa', 'App\controller\admin\TotpSetupController@save', 'admin_totp_save');
$router->map('POST', '/admin/disable-2fa', 'App\controller\admin\TotpSetupController@disable', 'admin_totp_disable');
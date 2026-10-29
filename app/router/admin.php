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
if ((str_starts_with($requestUri, '/admin') || str_starts_with($requestUri, $adminSecretPath)) && \class_exists('\App\middleware\AdminGuardMiddleware')) {
    \App\middleware\AdminGuardMiddleware::enforce();
}

// NEW APPLICATION
$router->map('GET', '/admin/reviewApps', 'App\controller\admin\ReviewApps@get', 'NEW APPLICATION');
$router->map('GET', $adminSecretPath . '/reviewApps', 'App\controller\admin\ReviewApps@get', 'NEW APPLICATION SECRET');

$router->map('GET', '/admin/reviewApps/approval', 'App\controller\admin\ReviewApps@approve', 'NEW APPLICATION APPROVED');
$router->map('GET', $adminSecretPath . '/reviewApps/approval', 'App\controller\admin\ReviewApps@approve', 'NEW APPLICATION APPROVED SECRET');

$router->map('GET', '/admin/reviewApps/delete', 'App\controller\admin\ReviewApps@delete', 'NEW APPLICATION DELETED');
$router->map('GET', $adminSecretPath . '/reviewApps/delete', 'App\controller\admin\ReviewApps@delete', 'NEW APPLICATION DELETED SECRET');

$router->map('GET', '/admin/reviewApps/decline', 'App\controller\admin\ReviewApps@decline', 'NEW APPLICATION DECLINED');
$router->map('GET', $adminSecretPath . '/reviewApps/decline', 'App\controller\admin\ReviewApps@decline', 'NEW APPLICATION DECLINED SECRET');

$router->map('GET', '/admin/reviewApps/cancel', 'App\controller\admin\ReviewApps@cancel', 'NEW APPLICATION CANCEL');
$router->map('GET', $adminSecretPath . '/reviewApps/cancel', 'App\controller\admin\ReviewApps@cancel', 'NEW APPLICATION CANCEL SECRET');

// MEMBER MANAGEMENT & PLATFORM DIRECTORY
$router->map('GET', '/admin/new-registrations', 'App\controller\admin\AdminMembersController@newRegistrations', 'admin_new_registrations');
$router->map('GET', $adminSecretPath . '/new-registrations', 'App\controller\admin\AdminMembersController@newRegistrations', 'admin_new_registrations_secret');

$router->map('GET', '/admin/members', 'App\controller\admin\AdminMembersController@registeredUsers', 'admin_registered_users');
$router->map('GET', $adminSecretPath . '/members', 'App\controller\admin\AdminMembersController@registeredUsers', 'admin_registered_users_secret');

$router->map('GET', '/admin/online-users', 'App\controller\admin\AdminMembersController@onlineUsers', 'admin_online_users');
$router->map('GET', $adminSecretPath . '/online-users', 'App\controller\admin\AdminMembersController@onlineUsers', 'admin_online_users_secret');

$router->map('GET', '/admin/dashboard', 'App\controller\admin\AdminController@index', 'dashboard');
$router->map('GET', $adminSecretPath . '/dashboard', 'App\controller\admin\AdminController@index', 'dashboard_secret');
$router->map('GET', '/admin', 'App\controller\admin\AdminController@index', 'admin_root');
$router->map('GET', $adminSecretPath . '/umac', 'App\controller\admin\AdminController@index', 'admin_umac_secret');

$router->map('GET', '/admin/users', 'App\controller\admin\AdminController@users', 'admin_users');
$router->map('GET', $adminSecretPath . '/users', 'App\controller\admin\AdminController@users', 'admin_users_secret');

$router->map('GET', '/admin/campaigns', 'App\controller\admin\AdminController@campaigns', 'admin_campaigns');
$router->map('GET', $adminSecretPath . '/campaigns', 'App\controller\admin\AdminController@campaigns', 'admin_campaigns_secret');

$router->map('GET', '/admin/broadcast', 'App\controller\admin\AdminController@broadcast', 'admin_broadcast');
$router->map('GET', $adminSecretPath . '/broadcast', 'App\controller\admin\AdminController@broadcast', 'admin_broadcast_secret');

$router->map('GET', '/admin/system', 'App\controller\admin\AdminController@system', 'admin_system');
$router->map('GET', $adminSecretPath . '/system', 'App\controller\admin\AdminController@system', 'admin_system_secret');

// OFFICIAL PLATFORM ACCOUNT & UNIVERSAL FEED CONSOLE
$router->map('GET', '/admin/official-posts', 'App\controller\admin\AdminController@officialPosts', 'admin_official_posts');
$router->map('GET', $adminSecretPath . '/official-posts', 'App\controller\admin\AdminController@officialPosts', 'admin_official_posts_secret');
$router->map('POST', '/admin/official-posts/publish', 'App\controller\admin\AdminController@publishOfficialPost', 'admin_official_posts_publish');
$router->map('POST', $adminSecretPath . '/official-posts/publish', 'App\controller\admin\AdminController@publishOfficialPost', 'admin_official_posts_publish_secret');
$router->map('POST', '/admin/official-posts/update', 'App\controller\admin\AdminController@updateOfficialPost', 'admin_official_posts_update');
$router->map('POST', $adminSecretPath . '/official-posts/update', 'App\controller\admin\AdminController@updateOfficialPost', 'admin_official_posts_update_secret');
$router->map('POST', '/admin/official-posts/delete', 'App\controller\admin\AdminController@deleteOfficialPost', 'admin_official_posts_delete');
$router->map('POST', $adminSecretPath . '/official-posts/delete', 'App\controller\admin\AdminController@deleteOfficialPost', 'admin_official_posts_delete_secret');
$router->map('POST', '/admin/official-posts/summarise', 'App\controller\admin\AdminController@summariseFriendlyUpdate', 'admin_official_posts_summarise');
$router->map('POST', $adminSecretPath . '/official-posts/summarise', 'App\controller\admin\AdminController@summariseFriendlyUpdate', 'admin_official_posts_summarise_secret');

// pSEO CMS & URL REGISTRY
$router->map('GET', '/admin/pseo', 'App\controller\admin\AdminController@pseo', 'admin_pseo');
$router->map('GET', $adminSecretPath . '/pseo', 'App\controller\admin\AdminController@pseo', 'admin_pseo_secret');
$router->map('POST', '/admin/pseo/create', 'App\controller\admin\AdminController@createPseoGuide', 'admin_pseo_create');
$router->map('POST', $adminSecretPath . '/pseo/create', 'App\controller\admin\AdminController@createPseoGuide', 'admin_pseo_create_secret');

// Admin APIs
$router->map('GET', '/admin/api/metrics', 'App\controller\admin\AdminController@apiMetrics', 'admin_api_metrics');
$router->map('POST', '/admin/api/users/status', 'App\controller\admin\AdminController@apiUpdateUserStatus', 'admin_api_user_status');
$router->map('POST', '/admin/api/campaigns', 'App\controller\admin\AdminController@apiCreateCampaign', 'admin_api_create_campaign');
$router->map('POST', '/admin/api/broadcast/audience', 'App\controller\admin\AdminController@apiAudienceCount', 'admin_api_audience_count');
$router->map('POST', '/admin/api/broadcast/send', 'App\controller\admin\AdminController@apiSendBroadcast', 'admin_api_send_broadcast');
$router->map('POST', '/admin/api/system/purge-cache', 'App\controller\admin\AdminController@apiPurgeCache', 'admin_api_purge_cache');


$router->map('GET', '/admin/playbook', 'App\controller\PlaybookController@adminPlaybook', 'admin_playbook');
$router->map('GET', $adminSecretPath . '/playbook', 'App\controller\PlaybookController@adminPlaybook', 'admin_playbook_secret');

$router->map('POST', '/admin/create-admin', 'App\controller\admin\AdminAuthController@createSuperAdmin', 'create_admin');
$router->map('POST', $adminSecretPath . '/create-admin', 'App\controller\admin\AdminAuthController@createSuperAdmin', 'create_admin_secret');

// BLOG MANAGEMENT
$router->map('GET', '/admin/blog/create', 'App\controller\admin\AdminBlogController@create', 'admin_blog_create');
$router->map('GET', $adminSecretPath . '/blog/create', 'App\controller\admin\AdminBlogController@create', 'admin_blog_create_secret');
$router->map('POST', '/admin/blog/store', 'App\controller\admin\AdminBlogController@store', 'admin_blog_store');
$router->map('POST', $adminSecretPath . '/blog/store', 'App\controller\admin\AdminBlogController@store', 'admin_blog_store_secret');
$router->map('POST', '/admin/blog/ai-generate', 'App\controller\admin\AdminBlogController@generateAi', 'admin_blog_ai_generate');
$router->map('POST', $adminSecretPath . '/blog/ai-generate', 'App\controller\admin\AdminBlogController@generateAi', 'admin_blog_ai_generate_secret');
$router->map('POST', '/admin/blog/ai-summarize', 'App\controller\admin\AdminBlogController@summarizeAi', 'admin_blog_ai_summarize');
$router->map('POST', $adminSecretPath . '/blog/ai-summarize', 'App\controller\admin\AdminBlogController@summarizeAi', 'admin_blog_ai_summarize_secret');

// GDPR DATA ERASURE MANAGEMENT
$router->map('GET', '/admin/erasure', 'App\controller\admin\AdminErasureController@index', 'admin_erasure_index');
$router->map('GET', $adminSecretPath . '/erasure', 'App\controller\admin\AdminErasureController@index', 'admin_erasure_index_secret');
$router->map('POST', '/admin/erasure/process', 'App\controller\admin\AdminErasureController@process', 'admin_erasure_process');
$router->map('POST', $adminSecretPath . '/erasure/process', 'App\controller\admin\AdminErasureController@process', 'admin_erasure_process_secret');

// TELEMETRY & RUM FRICTION DASHBOARD
$router->map('GET', '/admin/telemetry', 'App\controller\admin\AdminTelemetryController@index', 'admin_telemetry');
$router->map('GET', $adminSecretPath . '/telemetry', 'App\controller\admin\AdminTelemetryController@index', 'admin_telemetry_secret');

// GOOGLE AUTHENTICATOR (TOTP) 2-FA SETUP
$router->map('GET', '/admin/setup-2fa', 'App\controller\admin\TotpSetupController@show', 'admin_totp_setup');
$router->map('GET', $adminSecretPath . '/setup-2fa', 'App\controller\admin\TotpSetupController@show', 'admin_totp_setup_secret');
$router->map('POST', '/admin/setup-2fa', 'App\controller\admin\TotpSetupController@save', 'admin_totp_save');
$router->map('POST', $adminSecretPath . '/setup-2fa', 'App\controller\admin\TotpSetupController@save', 'admin_totp_save_secret');
$router->map('POST', '/admin/disable-2fa', 'App\controller\admin\TotpSetupController@disable', 'admin_totp_disable');
$router->map('POST', $adminSecretPath . '/disable-2fa', 'App\controller\admin\TotpSetupController@disable', 'admin_totp_disable_secret');
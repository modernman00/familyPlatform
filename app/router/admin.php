<?php
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
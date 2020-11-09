<?php
// NEW APPLICATION

$router->map('GET', '/admin/reviewApps', 'App\controller\admin\ReviewApps@get', 'NEW APPLICATION');

$router->map('GET', '/admin/reviewApps/approval', 'App\controller\admin\ReviewApps@approve', 'NEW APPLICATION APPROVED');

$router->map('GET', '/admin/dashboard', 'App\controller\admin\Dashboard@index', 'dashboard');
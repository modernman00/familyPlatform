<?php
declare(strict_types=1);

/** @var \AltoRouter $router */

$router->map('GET', '/blog', 'App\controller\BlogController@index', 'blog_index');
$router->map('GET', '/blog/[a:slug]', 'App\controller\BlogController@show', 'blog_show');

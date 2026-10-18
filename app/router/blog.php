<?php
declare(strict_types=1);

/** @var \AltoRouter $router */

$router->map('GET|HEAD', '/blog', 'App\controller\BlogController@index', 'blog_index');
$router->map('GET|HEAD', '/blogs', 'App\controller\BlogController@index', 'blog_index_plural');
$router->map('GET|HEAD', '/blog/[*:slug]', 'App\controller\BlogController@show', 'blog_show');


<?php
declare(strict_types =1);

$router->map('GET|POST', '/api/cron/social', 'App\controller\CronController@publishSocial', 'cron-social');
$router->map('GET|POST', '/api/cron/ai-blog', 'App\controller\CronController@blog', 'cron-ai-blog');
$router->map('GET|POST', '/cron/ai-blog', 'App\controller\CronController@blog', 'cron-ai-blog-alias');

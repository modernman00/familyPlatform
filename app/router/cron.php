<?php
declare(strict_types =1);

$router->map('GET|POST', '/api/cron/social', 'App\controller\CronController@publishSocial', 'cron-social');

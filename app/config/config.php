<?php

return [
    'paths' => [
        'admin' => '/lasu',
        'login' => '/login',
    ],
    'tables' => [
        'account' => 'account',
        'login_attempts' => 'login_attempts',
    ],
    'env' => [
        'app_url' => getenv('APP_URL') ?: 'http://localhost',
        'coding' => getenv('CODING') ?: '',
        'mix_app_url2' => getenv('MIX_APP_URL2') ?: 'http://localhost',
    ],
    'rate_limit' => [
        'max_attempts' => 5,
        'time_window' => 15 * 60, // 15= 15 minutes
    ],
    'session' => [
        'login' => 'auth',
    ],
];


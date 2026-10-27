<?php

declare(strict_types=1);

/**
 * 🧒 Child Account & Adulthood Graduation Routes
 */

$router->map(
    'POST',
    '/api/family/children/provision',
    'App\controller\members\ChildAccountController@provision',
    'api_child_provision'
);

$router->map(
    'POST',
    '/login/child',
    'App\controller\members\ChildAccountController@login',
    'login_child'
);

$router->map(
    'GET',
    '/api/adulthood/celebration',
    'App\controller\members\ChildAccountController@celebration',
    'api_adulthood_celebration'
);

<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

$router->map('GET', '/aboutus', 'App\controller\About@index', 'About');

$router->map('GET', '/privacy', 'App\controller\Index@privacy', 'privacy');

$router->map('GET', '/terms', 'App\controller\Index@terms', 'terms');

$router->map('GET', '/contact', 'App\controller\Index@contact', 'Contact');

// --- TEST AUTOMATION ROUTE ---
// Clears rate limits to unblock Cypress tests.
$router->map('GET', '/tests/clear-rate-limit', function() {
    $nonProdEnvs = ['local', 'development', 'testing'];
    if (!in_array(($_ENV['APP_ENV'] ?? ''), $nonProdEnvs, true)) {
        http_response_code(403);
        die('Forbidden');
    }
    $db = \Src\Db::connect2();
    $db->exec("TRUNCATE rate_limiter");
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Rate limit cleared']);
});

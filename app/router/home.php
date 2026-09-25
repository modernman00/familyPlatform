<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

$router->map('GET', '/aboutus', 'App\controller\About@index', 'About');

$router->map('GET', '/privacy', 'App\controller\Index@privacy', 'privacy');

$router->map('GET', '/terms', 'App\controller\Index@terms', 'terms');

$router->map('GET', '/contact', 'App\controller\Index@contact', 'Contact');

$router->map('GET|POST', '/email/unsubscribe', 'App\controller\Index@unsubscribe', 'email_unsubscribe');

// --- ROBOTS.TXT ROUTE HANDLER ---
$router->map('GET', '/robots.txt', function() {
    header('Content-Type: text/plain; charset=utf-8');
    $robotsFile = __DIR__ . '/../../public/robots.txt';
    if (file_exists($robotsFile)) {
        readfile($robotsFile);
    } else {
        echo "User-agent: *\nAllow: /\nDisallow: /app/\nDisallow: /api/\nDisallow: /cron/\nDisallow: /storage/\nSitemap: https://familyplatform.app/sitemap.xml\n";
    }
});

// --- AUTOMATED DEPLOYMENT & SRE HEALTH CHECK (Item #17) ---
$router->map('GET', '/api/health', function() {
    header('Content-Type: application/json; charset=utf-8');
    $status = ['status' => 'ok', 'app' => $_ENV['APP_NAME'] ?? 'Family Platform', 'time' => time()];
    try {
        $db = \Src\Db::connect2();
        $db->query("SELECT 1");
        $status['database'] = 'connected';
        http_response_code(200);
    } catch (\Throwable $e) {
        $status['status'] = 'degraded';
        $status['database'] = 'disconnected';
        http_response_code(503);
    }
    echo json_encode($status);
});

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


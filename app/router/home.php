<?php

$router->map('GET', '/', 'App\controller\Index@index', 'Home');

// the launch page
$router->map('GET', '/launch', 'App\controller\Index@launch', 'launch');

$router->map('GET', '/aboutus', 'App\controller\About@index', 'About');
$router->map('GET', '/about', 'App\controller\About@index', 'About_Alias');
$router->map('GET', '/why-familyplatform', 'App\controller\Index@whyFamilyPlatform', 'why_familyplatform');
$router->map('GET', '/playbook', 'App\controller\PlaybookController@index', 'playbook');
$router->map('GET', '/how-to-use', 'App\controller\PlaybookController@howToUse', 'how_to_use');

$router->map('GET', '/privacy', 'App\controller\Index@privacy', 'privacy');

$router->map('GET', '/terms', 'App\controller\Index@terms', 'terms');

$router->map('GET', '/contact', 'App\controller\Index@contact', 'Contact');

// --- PUBLIC ACQUISITION & INTERACTIVE TOOLS ("Engineering as Marketing") ---
$router->map('GET|HEAD', '/tools/family-memory-prompts', 'App\controller\PublicToolsController@memoryPrompts', 'tools_memory_prompts');
$router->map('GET|HEAD', '/memory-prompts', 'App\controller\PublicToolsController@memoryPrompts', 'tools_memory_prompts_short_alias');
$router->map('GET|HEAD', '/prompts', 'App\controller\PublicToolsController@memoryPrompts', 'tools_memory_prompts_alias');
$router->map('GET|HEAD', '/prompts/[*:slug]', 'App\controller\PublicToolsController@promptGuide', 'tools_prompt_guide');
$router->map('GET|HEAD', '/tools/kinship-calculator', 'App\controller\PublicToolsController@kinshipCalculator', 'tools_kinship_calculator');
$router->map('GET|HEAD', '/kinship-calculator', 'App\controller\PublicToolsController@kinshipCalculator', 'tools_kinship_calculator_alias');

$router->map('GET|POST', '/email/unsubscribe', 'App\controller\Index@unsubscribe', 'email_unsubscribe');

// --- ROBOTS.TXT ROUTE HANDLER ---
$router->map('GET|HEAD', '/robots.txt', function() {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    $robotsFile = file_exists(__DIR__ . '/../../public/robots.txt')
        ? __DIR__ . '/../../public/robots.txt'
        : (file_exists(__DIR__ . '/../../robots.txt') ? __DIR__ . '/../../robots.txt' : null);

    if ($robotsFile !== null) {
        readfile($robotsFile);
    } else {
        echo "User-agent: *\nAllow: /\nDisallow: /app/\nDisallow: /api/\nDisallow: /cron/\nDisallow: /storage/\nSitemap: https://myfamilyplatform.com/sitemap.xml\n";
    }
});

// --- SITEMAP.XML ROUTE HANDLER ---
$router->map('GET|HEAD', '/sitemap.xml', function() {
    header('Content-Type: application/xml; charset=utf-8');
    header('Cache-Control: public, max-age=86400');
    $sitemapFile = file_exists(__DIR__ . '/../../public/sitemap.xml')
        ? __DIR__ . '/../../public/sitemap.xml'
        : (file_exists(__DIR__ . '/../../sitemap.xml') ? __DIR__ . '/../../sitemap.xml' : null);

    if ($sitemapFile !== null) {
        readfile($sitemapFile);
        return;
    }

    // Dynamic fallback generation if static file is unavailable
    $generatorScript = __DIR__ . '/../../scripts/generate_sitemap.php';
    if (file_exists($generatorScript)) {
        require_once $generatorScript;
        return;
    }

    http_response_code(404);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Sitemap not found</error>';
});

// --- LLMS.TXT & LLMS-FULL.TXT ROUTE HANDLERS (Generative Engine Optimization) ---
$router->map('GET', '/llms.txt', function() {
    header('Content-Type: text/plain; charset=utf-8');
    $llmsFile = __DIR__ . '/../../public/llms.txt';
    if (file_exists($llmsFile)) {
        readfile($llmsFile);
    } else {
        http_response_code(404);
        echo "# FamilyPlatform\n> The private family social network and living heritage organogram.\n";
    }
});

$router->map('GET', '/llms-full.txt', function() {
    header('Content-Type: text/plain; charset=utf-8');
    $llmsFullFile = __DIR__ . '/../../public/llms-full.txt';
    if (file_exists($llmsFullFile)) {
        readfile($llmsFullFile);
    } else {
        http_response_code(404);
        echo "# FamilyPlatform — Complete Knowledge Base\n";
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


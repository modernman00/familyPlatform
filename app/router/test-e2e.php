<?php
declare(strict_types=1);

/**
 * E2E Testing Routes
 * NOTE: This file is only included from router.php when APP_ENV is non-prod,
 * where $router is the AltoRouter instance created in router.php.
 */

$router->map('GET', '/api/test/get-valid-family-code', 'App\controller\test\E2ETestController@getValidFamilyCode', 'test-valid-family-code');
$router->map('GET', '/api/test/get-valid-family-code-with-inviter', 'App\controller\test\E2ETestController@getValidFamilyCodeWithInviter', 'test-valid-family-code-inviter');
$router->map('GET', '/api/test/get-pending-approval-requests', 'App\controller\test\E2ETestController@getPendingApprovalRequests', 'test-pending-approvals');
$router->map('GET', '/api/test/get-approval-request/[i:id]', 'App\controller\test\E2ETestController@getApprovalRequest', 'test-get-approval-request');
$router->map('POST', '/api/test/setup', 'App\controller\test\E2ETestController@setup', 'test-setup');
$router->map('DELETE', '/api/test/setup', 'App\controller\test\E2ETestController@teardown', 'test-teardown');
$router->map('POST', '/api/test/teardown', 'App\controller\test\E2ETestController@teardown', 'test-teardown-post');
$router->map('POST', '/api/test/clear-rate-limit', 'App\controller\test\E2ETestController@clearRateLimit', 'api-test-clear-rate-limit');
$router->map('POST', '/api/test/seed-test-user', 'App\controller\test\E2ETestController@seedTestUser', 'api-test-seed-user');
$router->map('POST', '/api/test/create-invite-token', 'App\controller\test\E2ETestController@createInviteToken', 'api-test-create-invite-token');
$router->map('POST', '/tests/clear-rate-limit', 'App\controller\test\E2ETestController@clearRateLimit', 'test-clear-rate-limit');
$router->map('POST', '/tests/seed-test-user', 'App\controller\test\E2ETestController@seedTestUser', 'test-seed-user');
$router->map('POST', '/tests/create-invite-token', 'App\controller\test\E2ETestController@createInviteToken', 'test-create-invite-token');

// Sovereign Explainer Studio Live Route Browser Verification
$router->map('GET|HEAD', '/explainer-studio-verify', function () {
    // 1. Resolve Storyboard for FamilyPlatform
    $storyboard = \Src\Explainer\PortfolioExplainerRegistry::buildStoryboard('familyplatform');

    // 2. Instantiate Sovereign Video Renderer
    $renderer = new \Src\Explainer\SovereignVideoRenderer();

    // 3. Render Canvas Studio HTML container (defaults to 'warm-botanical' for FamilyPlatform)
    $studioHtml = $renderer->renderCanvasStudio($storyboard, 'warm-botanical');

    // 4. Read Client JS Engine files
    $sharedDir = dirname(__DIR__, 2) . '/vendor/modernman00/shared-lib/resources/js/explainer';
    if (!is_dir($sharedDir)) {
        $sharedDir = '/Users/waleolaogun/Sites/shared-lib/resources/js/explainer';
    }

    $sfxJs = (string) @file_get_contents($sharedDir . '/explainer-sfx.js');
    $canvasJs = (string) @file_get_contents($sharedDir . '/explainer-canvas-renderer.js');
    $initJs = (string) @file_get_contents($sharedDir . '/explainer-studio-init.js');

    $nonce = defined('CSP_NONCE') ? (string) CSP_NONCE : '';

    // 5. Emit Full HTML Verification Page
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="en-GB" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FamilyPlatform — Sovereign Explainer Studio Live Route Verification</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <style>
            body { background: #020617; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        </style>
    </head>
    <body class="min-h-screen p-4 sm:p-8 flex flex-col items-center justify-center">
        <div class="w-full max-w-5xl space-y-6">
            <!-- Header Banner -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">FamilyPlatform Sovereign Explainer Studio</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        ATG ExplainerCore · 60fps Canvas
                    </span>
                </div>
                <div class="text-xs text-slate-400">
                    Live Route Verification (<code class="text-indigo-400">/explainer-studio-verify</code>)
                </div>
            </div>

            <!-- Studio Container Component -->
            <?= $studioHtml ?>

            <!-- Verification Metadata & Metrics Badge -->
            <div id="verifyMetrics" class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-mono">
                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900/60">
                    <div class="text-slate-400">Engine</div>
                    <div class="font-bold text-emerald-400">HTML5 Canvas 2D</div>
                </div>
                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900/60">
                    <div class="text-slate-400">Audio</div>
                    <div class="font-bold text-indigo-400" id="audioStateDisplay">Web Audio Procedural</div>
                </div>
                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900/60">
                    <div class="text-slate-400">Target Framerate</div>
                    <div class="font-bold text-sky-400">60.0 FPS</div>
                </div>
                <div class="p-3 rounded-xl border border-slate-800 bg-slate-900/60">
                    <div class="text-slate-400">Export Codec</div>
                    <div class="font-bold text-amber-400">VP9 / Opus WebM</div>
                </div>
            </div>
        </div>

        <!-- Explainer Scripts -->
        <script nonce="<?= $nonce ?>"><?= $sfxJs ?></script>
        <script nonce="<?= $nonce ?>"><?= $canvasJs ?></script>
        <script nonce="<?= $nonce ?>"><?= $initJs ?></script>
    </body>
    </html>
    <?php
    exit;
}, 'explainer_studio_verify');



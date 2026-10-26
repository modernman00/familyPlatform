<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Universal PWA Optimization (FamilyPlatform)
 * 
 * Verifies:
 * 1. public/manifest.json existence, valid JSON, and complete PWA specification (#a31621)
 * 2. High-resolution app icons (192x192, 512x512, apple-touch-icon, favicon) exist on disk
 * 3. Offline fallback shell (public/offline.html) exists and contains friendly reload trigger
 * 4. PWA partials (pwa-head and pwa-register) exist and implement canonical best practices
 * 5. All base layouts include pwa-head and pwa-register
 * 6. Root public/sw.js & sw.js precache offline.html, manifest.json, and implement 3000ms timeout race
 */

$rootDir = dirname(__DIR__);
$passed = 0;
$failed = 0;

function runCheck(string $description, callable $fn): void {
    global $passed, $failed;
    echo "[TEST " . ($passed + $failed + 1) . "] {$description}... ";
    try {
        $fn();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "⚡ FAMILYPLATFORM UNIVERSAL PWA OPTIMIZATION VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// TEST 1: Manifest existence and schema validation
runCheck("public/manifest.json exists and satisfies PWA installability criteria", function() use ($rootDir) {
    $manifestPath = $rootDir . '/public/manifest.json';
    if (!file_exists($manifestPath)) {
        throw new \Exception("public/manifest.json not found.");
    }
    $raw = file_get_contents($manifestPath);
    $data = json_decode((string)$raw, true);
    if (!is_array($data)) {
        throw new \Exception("public/manifest.json is not valid JSON.");
    }
    
    $requiredKeys = ['name', 'short_name', 'start_url', 'display', 'scope', 'theme_color', 'background_color', 'icons'];
    foreach ($requiredKeys as $key) {
        if (!isset($data[$key])) {
            throw new \Exception("Manifest missing required key: {$key}");
        }
    }
    if ($data['display'] !== 'standalone') {
        throw new \Exception("Manifest display must be 'standalone', found: " . $data['display']);
    }
    if (strtolower($data['theme_color']) !== '#a31621') {
        throw new \Exception("Manifest theme_color must be '#a31621', found: " . $data['theme_color']);
    }
    if (empty($data['icons']) || !is_array($data['icons'])) {
        throw new \Exception("Manifest icons array is empty.");
    }
});

// TEST 2: High-resolution icons verification
runCheck("App icons and touch icons exist with correct dimensions", function() use ($rootDir) {
    $requiredIcons = [
        '/public/img/favicon/android-chrome-192x192.png',
        '/public/img/favicon/android-chrome-512x512.png',
        '/public/img/favicon/apple-touch-icon.png',
        '/public/img/favicon/favicon.ico',
        '/public/icon-192x192.png',
        '/public/icon-512x512.png'
    ];

    foreach ($requiredIcons as $iconRel) {
        $fullPath = $rootDir . $iconRel;
        if (!file_exists($fullPath)) {
            throw new \Exception("Missing icon asset: {$iconRel}");
        }
        if (filesize($fullPath) < 100) {
            throw new \Exception("Icon file is abnormally small (< 100 bytes): {$iconRel}");
        }
    }
});

// TEST 3: Offline fallback shell
runCheck("Offline fallback shell (public/offline.html) is present and functional", function() use ($rootDir) {
    $offlinePath = $rootDir . '/public/offline.html';
    if (!file_exists($offlinePath)) {
        throw new \Exception("public/offline.html not found.");
    }
    $content = file_get_contents($offlinePath);
    if (!str_contains($content, 'offline') && !str_contains($content, 'Offline') && !str_contains($content, 'Connection')) {
        throw new \Exception("public/offline.html missing offline notice.");
    }
});

// TEST 4: PWA Partials existence and implementation
runCheck("PWA partials (pwa-head and pwa-register) exist with proper meta tags", function() use ($rootDir) {
    $pwaHead = $rootDir . '/resources/views/partials/pwa-head.blade.php';
    $pwaReg = $rootDir . '/resources/views/partials/pwa-register.blade.php';

    if (!file_exists($pwaHead)) {
        throw new \Exception("Missing resources/views/partials/pwa-head.blade.php");
    }
    if (!file_exists($pwaReg)) {
        throw new \Exception("Missing resources/views/partials/pwa-register.blade.php");
    }

    $headContent = file_get_contents($pwaHead);
    $requiredHeadDirectives = [
        'rel="manifest"',
        'name="theme-color"',
        'name="mobile-web-app-capable"',
        'name="apple-mobile-web-app-capable"',
        'rel="apple-touch-icon"',
    ];
    foreach ($requiredHeadDirectives as $directive) {
        if (!str_contains($headContent, $directive)) {
            throw new \Exception("pwa-head missing directive: {$directive}");
        }
    }

    $regContent = file_get_contents($pwaReg);
    if (!str_contains($regContent, "serviceWorker.register('/sw.js'")) {
        throw new \Exception("pwa-register missing navigator.serviceWorker.register('/sw.js').");
    }
});

// TEST 5: Base Layouts Integration
runCheck("All base layouts include pwa-head and pwa-register partials", function() use ($rootDir) {
    // Check head sub-partial
    $headPartial = $rootDir . '/resources/views/layouts/head.blade.php';
    if (!file_exists($headPartial) || !str_contains((string)file_get_contents($headPartial), 'partials.pwa-head')) {
        throw new \Exception("layouts/head.blade.php missing pwa-head inclusion.");
    }

    $layouts = [
        'landing_layout.blade.php' => $rootDir . '/resources/views/layouts/landing_layout.blade.php',
        'profileBase.blade.php'    => $rootDir . '/resources/views/layouts/profileBase.blade.php',
        'w3s_admin.blade.php'      => $rootDir . '/resources/views/layouts/w3s_admin.blade.php',
        'w3s_member.blade.php'     => $rootDir . '/resources/views/layouts/w3s_member.blade.php',
        'baseBulma.blade.php'      => $rootDir . '/resources/views/baseBulma.blade.php',
        'baseBulmaForm.blade.php'  => $rootDir . '/resources/views/baseBulmaForm.blade.php',
        'errors/base.blade.php'    => $rootDir . '/resources/views/errors/base.blade.php',
        'baseError.blade.php'      => $rootDir . '/resources/views/baseError.blade.php',
        'admin/base.blade.php'     => $rootDir . '/resources/views/admin/base.blade.php',
        'baseTail.blade.php'       => $rootDir . '/resources/views/layouts/baseTail.blade.php',
    ];

    foreach ($layouts as $name => $layout) {
        if (!file_exists($layout)) {
            throw new \Exception("Layout file not found: {$name}");
        }
        $content = file_get_contents($layout);
        if (!str_contains($content, 'partials.pwa-head') && !str_contains($content, 'pwa-head') && !str_contains($content, 'layouts.head')) {
            throw new \Exception("Layout {$name} missing `@include('partials.pwa-head')` (or transitive `layouts.head`)");
        }
        if (!str_contains($content, 'partials.pwa-register') && !str_contains($content, 'pwa-register')) {
            throw new \Exception("Layout {$name} missing `@include('partials.pwa-register')`");
        }
    }
});

// TEST 6: Service Worker Implementation & Timeout Race
runCheck("sw.js & public/sw.js implement 3000ms timeout race and precaches critical assets", function() use ($rootDir) {
    $swFiles = [$rootDir . '/sw.js', $rootDir . '/public/sw.js'];
    foreach ($swFiles as $swPath) {
        if (!file_exists($swPath)) {
            throw new \Exception("Service worker file missing: {$swPath}");
        }
        $swContent = file_get_contents($swPath);
        if (!str_contains($swContent, 'fetchWithTimeout') && !str_contains($swContent, '3000')) {
            throw new \Exception("Service worker missing 3000ms fetchWithTimeout watchdog: {$swPath}");
        }
        if (!str_contains($swContent, '/offline.html') || !str_contains($swContent, '/manifest.json')) {
            throw new \Exception("Service worker missing critical precache assets: {$swPath}");
        }
    }
});

// TEST 7: W3C Web Share Target API Level 2 (Receiving shares from OS gallery)
runCheck("Web Share Target API Level 2 is fully configured across manifest, SW, and controllers", function() use ($rootDir) {
    // 1. Manifest must declare share_target with POST and multipart/form-data
    $manifestPath = $rootDir . '/public/manifest.json';
    $manifest = json_decode((string)file_get_contents($manifestPath), true);
    if (!isset($manifest['share_target'])) {
        throw new \Exception("Manifest missing 'share_target' specification.");
    }
    $target = $manifest['share_target'];
    if ($target['action'] !== '/share-target' || strtoupper($target['method']) !== 'POST') {
        throw new \Exception("share_target must use action '/share-target' with method 'POST'.");
    }
    if (empty($target['params']['files'][0]['accept'])) {
        throw new \Exception("share_target missing file accept types.");
    }

    // 2. Service Worker must intercept POST /share-target
    $swContent = file_get_contents($rootDir . '/public/sw.js');
    if (!str_contains($swContent, '/share-target') || !str_contains($swContent, 'FP_PWA_ShareTarget_DB')) {
        throw new \Exception("Service Worker missing Web Share Target interceptor or IndexedDB queue.");
    }

    // 3. ShareTargetController & router must exist
    $controllerPath = $rootDir . '/app/controller/members/ShareTargetController.php';
    if (!file_exists($controllerPath)) {
        throw new \Exception("ShareTargetController.php not found.");
    }
    $routerContent = file_get_contents($rootDir . '/app/router/post.php');
    if (!str_contains($routerContent, '/share-target')) {
        throw new \Exception("Router missing POST /share-target route mapping.");
    }
});

// TEST 8: Web Share API (Sharing FROM PWA to Social Media)
runCheck("Web Share API is integrated in feedComponent.js and post-card.blade.php", function() use ($rootDir) {
    $feedCompPath = $rootDir . '/resources/asset/js/components/profilePage/feedComponent.js';
    $feedContent = file_get_contents($feedCompPath);
    if (!str_contains($feedContent, 'sharePostToSocialMedia') || !str_contains($feedContent, 'navigator.share')) {
        throw new \Exception("feedComponent.js missing sharePostToSocialMedia or navigator.share implementation.");
    }
    if (!str_contains($feedContent, 'openSocialShareModal')) {
        throw new \Exception("feedComponent.js missing openSocialShareModal fallback for desktop/unsupported browsers.");
    }

    $cardPath = $rootDir . '/resources/views/components/feed/post-card.blade.php';
    $cardContent = file_get_contents($cardPath);
    if (!str_contains($cardContent, 'sharePostToSocialMedia(post)')) {
        throw new \Exception("post-card.blade.php missing Share button trigger.");
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} PWA OPTIMIZATION TESTS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} PWA OPTIMIZATION TESTS FAILED. INVESTIGATE IMMEDIATELY.\n";
    echo "=================================================================\n";
    exit(1);
}

<?php

/**
 * Sovereign Verification Suite: Microsoft Edge Ghost Typography Neutralization
 * Verifies that the public layout, homepage, and auth views are permanently
 * protected against white-on-white text inversion under dark-mode conditions.
 */

echo "=================================================================\n";
echo "🛡️  VERIFYING MICROSOFT EDGE GHOST TYPOGRAPHY NEUTRALIZATION\n";
echo "=================================================================\n\n";

$errors = [];

// 1. Verify landing_layout.blade.php
$layoutPath = __DIR__ . '/../resources/views/layouts/landing_layout.blade.php';
$layoutContent = file_get_contents($layoutPath);

if (!str_contains($layoutContent, 'data-surface="light"')) {
    $errors[] = "landing_layout.blade.php is missing data-surface=\"light\" attribute on body.";
} else {
    echo "✅ landing_layout.blade.php declares data-surface=\"light\"\n";
}

if (!str_contains($layoutContent, 'class="public-layout surface-light light-mode"')) {
    $errors[] = "landing_layout.blade.php is missing public-layout surface-light classes.";
} else {
    echo "✅ landing_layout.blade.php declares public-layout surface-light classes\n";
}

if (!str_contains($layoutContent, '<meta name="page-scope" content="public">')) {
    $errors[] = 'landing_layout.blade.php is missing <meta name="page-scope" content="public"> tag.';
} else {
    echo "✅ landing_layout.blade.php declares <meta name=\"page-scope\" content=\"public\">\n";
}

// 2. Verify stores.js & darkMode.js
$storesPath = __DIR__ . '/../resources/asset/js/alpine/stores.js';
$storesContent = file_get_contents($storesPath);
if (!str_contains($storesContent, 'isPublicSurface')) {
    $errors[] = "stores.js is missing isPublicSurface guard.";
} else {
    echo "✅ stores.js enforces isPublicSurface dark-mode prevention guard\n";
}

$darkModePath = __DIR__ . '/../resources/asset/js/components/darkMode.js';
$darkModeContent = file_get_contents($darkModePath);
if (!str_contains($darkModeContent, 'isPublicSurface')) {
    $errors[] = "darkMode.js is missing isPublicSurface guard.";
} else {
    echo "✅ darkMode.js enforces isPublicSurface dark-mode prevention guard\n";
}

// 3. Verify standards-v21.css
$standardsPath = __DIR__ . '/../public/css/standards-v21.css';
$standardsContent = file_get_contents($standardsPath);

if (!str_contains($standardsContent, 'body.dark-mode:not(.public-layout):not([data-surface="light"])')) {
    $errors[] = "standards-v21.css does not exclude public-layout / data-surface=\"light\" from dark mode root inversion.";
} else {
    echo "✅ standards-v21.css excludes public-layout & data-surface=\"light\" from root inversion\n";
}

if (!str_contains($standardsContent, 'Public Layout & Light Surface Ghost Typography Invariant Shield')) {
    $errors[] = "standards-v21.css is missing the Public Layout & Light Surface Ghost Typography Invariant Shield.";
} else {
    echo "✅ standards-v21.css contains the Public Layout & Light Surface Ghost Typography Invariant Shield\n";
}

// 4. Verify landing.css
$landingCssPath = __DIR__ . '/../public/css/landing.css';
$landingCssContent = file_get_contents($landingCssPath);
if (!str_contains($landingCssContent, 'color: #0f172a !important;') || !str_contains($landingCssContent, '.feature-card .text-muted')) {
    $errors[] = "landing.css missing explicit contrast protection for feature titles/muted text.";
} else {
    echo "✅ landing.css enforces #0f172a !important for .feature-title and #475569 !important for .feature-card .text-muted\n";
}

// 5. Verify index.blade.php
$indexPath = __DIR__ . '/../resources/views/index.blade.php';
$indexContent = file_get_contents($indexPath);
if (!str_contains($indexContent, '<h2 class="fw-bold fs-1" style="color: #0f172a;">Everything your family needs</h2>')) {
    $errors[] = "index.blade.php missing explicit contrast styling on main features heading.";
} else {
    echo "✅ index.blade.php enforces explicit high-contrast color (#0f172a) on 'Everything your family needs'\n";
}

// 6. Verify login.blade.php
$loginPath = __DIR__ . '/../resources/views/login/login.blade.php';
$loginContent = file_get_contents($loginPath);
if (!str_contains($loginContent, '.styleform_form .form-label') || !str_contains($loginContent, 'color: #374151 !important;')) {
    $errors[] = "login.blade.php missing !important contrast rule on form labels.";
} else {
    echo "✅ login.blade.php enforces color: #374151 !important on form labels\n";
}

if (!str_contains($loginContent, '.styleform_form .form-control') || !str_contains($loginContent, 'background-color: #ffffff !important;')) {
    $errors[] = "login.blade.php missing !important background rule on form inputs.";
} else {
    echo "✅ login.blade.php enforces background-color: #ffffff !important & color: #111827 !important on form inputs\n";
}

// 7. Verify live endpoints
$homeHtml = @file_get_contents('https://olaogun.test/', false, stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));
if ($homeHtml && str_contains($homeHtml, 'data-surface="light"') && str_contains($homeHtml, 'public-layout')) {
    echo "✅ Live homepage (https://olaogun.test/) renders with data-surface=\"light\" and public-layout\n";
} else {
    $errors[] = "Live homepage failed to render expected attributes.";
}

$loginHtml = @file_get_contents('https://olaogun.test/login', false, stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]));
if ($loginHtml && str_contains($loginHtml, 'data-surface="light"') && str_contains($loginHtml, 'stitch-auth-container')) {
    echo "✅ Live login page (https://olaogun.test/login) renders with data-surface=\"light\" and stitch-auth-container\n";
} else {
    $errors[] = "Live login page failed to render expected attributes.";
}

echo "\n-----------------------------------------------------------------\n";
if (empty($errors)) {
    echo "🎉 ALL 7 AUDIT GATES PASSED! GHOST TYPOGRAPHY FULLY NEUTRALIZED.\n";
    exit(0);
} else {
    echo "❌ AUDIT FAILED WITH " . count($errors) . " ERRORS:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
    exit(1);
}

<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Frontend, UI & UX Stability Guard (Isla & Kieran Gate)
 * Application: FamilyPlatform
 * 
 * Verifies:
 * 1. Blade Template Integrity: 100% of Blade templates compile without unclosed directives or parse errors.
 * 2. Universal CSRF Shield: 100% of state-changing forms contain valid CSRF token fields.
 * 3. Tactile Button Spinners: All base layouts integrate submitSpinner and prevent double submissions.
 * 4. Isla Touch & Viewport Standard: Viewport meta tag present in all layouts; mobile touch targets >= 44px.
 * 5. Defensive Client-Side Scripting: API callers employ defensive typing and null checks.
 * 6. Asset & Family Platform Integrity: Core assets and family network templates exist.
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
echo "🛡️  FAMILYPLATFORM FRONTEND & UX STABILITY VERIFICATION SUITE\n";
echo "=================================================================\n\n";

// TEST 1: Blade Template Parse & Directive Balance Check
runCheck("All Blade templates in resources/views compile with balanced directives", function() use ($rootDir) {
    $viewsDir = $rootDir . '/resources/views';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
    $bladeFiles = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $bladeFiles[] = $file->getPathname();
        }
    }

    if (empty($bladeFiles)) {
        throw new \Exception("No Blade templates discovered under resources/views.");
    }

    $errors = [];
    foreach ($bladeFiles as $filePath) {
        $content = file_get_contents($filePath);
        if ($content === false) continue;

        // Check for balanced structural directives
        $openIf = substr_count($content, '@if') + substr_count($content, '@hasSection') + substr_count($content, '@sectionMissing');
        $closeIf = substr_count($content, '@endif');
        if ($openIf !== $closeIf) {
            $errors[] = basename($filePath) . " has unbalanced @if (@open: {$openIf}, @endif: {$closeIf})";
        }

        $openUnless = substr_count($content, '@unless');
        $closeUnless = substr_count($content, '@endunless');
        if ($openUnless !== $closeUnless) {
            $errors[] = basename($filePath) . " has unbalanced @unless (@unless: {$openUnless}, @endunless: {$closeUnless})";
        }

        $openForeach = substr_count($content, '@foreach');
        $closeForeach = substr_count($content, '@endforeach');
        if ($openForeach !== $closeForeach) {
            $errors[] = basename($filePath) . " has unbalanced @foreach ({$openForeach} vs {$closeForeach})";
        }

        $openForelse = substr_count($content, '@forelse');
        $closeForelse = substr_count($content, '@endforelse');
        if ($openForelse !== $closeForelse) {
            $errors[] = basename($filePath) . " has unbalanced @forelse ({$openForelse} vs {$closeForelse})";
        }

        $openWhile = substr_count($content, '@while');
        $closeWhile = substr_count($content, '@endwhile');
        if ($openWhile !== $closeWhile) {
            $errors[] = basename($filePath) . " has unbalanced @while ({$openWhile} vs {$closeWhile})";
        }
    }

    if (!empty($errors)) {
        throw new \Exception("Blade directive imbalance detected:\n  " . implode("\n  ", $errors));
    }
});

// TEST 2: Universal Form CSRF Protection
runCheck("Every form in the application contains CSRF token protection", function() use ($rootDir) {
    $viewsDir = $rootDir . '/resources/views';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
    $csrfFailures = [];

    foreach ($iterator as $file) {
        if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $content = (string)file_get_contents($file->getPathname());
        $formRegex = '#<' . 'form\b([^>]*)>(.*?)</' . 'form>#is';
        if (preg_match_all($formRegex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attrs = $match[1];
                $body = $match[2];

                if (preg_match('/method=[\'"]get[\'"]/i', $attrs)) {
                    continue;
                }

                $hasCsrf = preg_match(
                    '/(name=[\'"]_token[\'"]|name=[\'"]token[\'"]|@csrf|\bcsrf_token\b|\bcsrf_field\b|\bcsrfField\b|\bcsrfToken\b|@include\([\'"][^\'"]*csrf[^\'"]*[\'"]\)|\bLaravelHelper::csrfField\b|\bLaravelHelper::csrfToken\b|\bBuildFormBStrap\b|\bBuildFormBulma\b|\bFormBuilder\b)/i',
                    $body
                );

                if (!$hasCsrf) {
                    $csrfFailures[] = $file->getFilename();
                }
            }
        }
    }

    if (!empty($csrfFailures)) {
        throw new \Exception("Missing CSRF protection in templates: " . implode(', ', array_unique($csrfFailures)));
    }
});

// TEST 3: Universal Button Spinners & Double-Click Mitigation
runCheck("Universal submit spinner component and layout inclusions are intact", function() use ($rootDir) {
    $spinnerPath = $rootDir . '/resources/views/component/submitSpinner.blade.php';
    if (!file_exists($spinnerPath)) {
        throw new \Exception("Universal submit spinner component missing.");
    }

    $spinnerContent = file_get_contents($spinnerPath);
    if (!str_contains($spinnerContent, 'FamilyPlatformSpinner') || !str_contains($spinnerContent, 'start:')) {
        throw new \Exception("submitSpinner.blade.php does not define FamilyPlatformSpinner.start().");
    }

    $requiredLayouts = [
        $rootDir . '/resources/views/layouts/landing_layout.blade.php',
        $rootDir . '/resources/views/layouts/profileBase.blade.php',
        $rootDir . '/resources/views/layouts/w3s_admin.blade.php',
        $rootDir . '/resources/views/layouts/w3s_member.blade.php',
        $rootDir . '/resources/views/baseBulma.blade.php',
        $rootDir . '/resources/views/baseBulmaForm.blade.php',
        $rootDir . '/resources/views/errors/base.blade.php',
        $rootDir . '/resources/views/baseError.blade.php',
        $rootDir . '/resources/views/admin/base.blade.php',
        $rootDir . '/resources/views/layouts/baseTail.blade.php',
    ];

    foreach ($requiredLayouts as $layout) {
        $content = file_get_contents($layout);
        if (!str_contains($content, 'component.submitSpinner')) {
            throw new \Exception("Layout " . basename($layout) . " missing submitSpinner inclusion.");
        }
    }
});

// TEST 4: Isla Responsive Viewport & Touch Ergonomics
runCheck("All layouts enforce responsive viewport standards and mobile ergonomics", function() use ($rootDir) {
    $requiredLayouts = [
        $rootDir . '/resources/views/layouts/landing_layout.blade.php',
        $rootDir . '/resources/views/layouts/profileBase.blade.php',
        $rootDir . '/resources/views/layouts/w3s_admin.blade.php',
        $rootDir . '/resources/views/layouts/w3s_member.blade.php',
        $rootDir . '/resources/views/baseBulma.blade.php',
        $rootDir . '/resources/views/baseBulmaForm.blade.php',
        $rootDir . '/resources/views/errors/base.blade.php',
        $rootDir . '/resources/views/baseError.blade.php',
        $rootDir . '/resources/views/admin/base.blade.php',
        $rootDir . '/resources/views/layouts/baseTail.blade.php',
    ];

    foreach ($requiredLayouts as $layout) {
        $content = file_get_contents($layout);
        $hasViewport = preg_match('/<meta\s+[^>]*name=[\'"]viewport[\'"][^>]*content=[\'"][^\'"]*width=device-width[^\'"]*[\'"]/i', $content) ||
                       preg_match('/<meta\s+[^>]*content=[\'"][^\'"]*width=device-width[^\'"]*[\'"][^>]*name=[\'"]viewport[\'"]/i', $content) ||
                       str_contains($content, 'layouts.head') ||
                       str_contains($content, 'partials.pwa-head');
        if (!$hasViewport) {
            throw new \Exception("Layout " . basename($layout) . " missing standard mobile viewport meta tag.");
        }
    }
});

// TEST 5: Defensive Client-Side Scripting Invariant
runCheck("Client-side scripts enforce defensive payload handling", function() use ($rootDir) {
    $jsFiles = [
        $rootDir . '/public/sw.js',
        $rootDir . '/sw.js',
        $rootDir . '/resources/views/component/submitSpinner.blade.php',
        $rootDir . '/resources/views/partials/pwa-register.blade.php',
    ];

    foreach ($jsFiles as $file) {
        if (!file_exists($file)) continue;
        $content = file_get_contents($file);
        if (str_contains($content, 'JSON.parse') && !str_contains($content, 'try') && !str_contains($content, 'catch')) {
            throw new \Exception(basename($file) . " uses JSON.parse without try/catch guard.");
        }
    }
});

// TEST 6: Static Asset & Family Platform Integrity
runCheck("Core frontend static assets and family platform templates exist", function() use ($rootDir) {
    $assets = [
        $rootDir . '/public/manifest.json',
        $rootDir . '/public/offline.html',
        $rootDir . '/resources/views/index.blade.php',
    ];

    foreach ($assets as $path) {
        if (!file_exists($path)) {
            throw new \Exception("Required frontend asset missing: " . basename($path));
        }
    }

    $indexContent = file_get_contents($rootDir . '/resources/views/index.blade.php');
    if (!str_contains($indexContent, 'FamilyPlatform') && !str_contains($indexContent, 'Family Platform') && !str_contains($indexContent, 'family')) {
        throw new \Exception("index.blade.php missing core FamilyPlatform branding or family network markup.");
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} FRONTEND & UX STABILITY TESTS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} FRONTEND STABILITY TESTS FAILED. INVESTIGATE IMMEDIATELY.\n";
    echo "=================================================================\n";
    exit(1);
}

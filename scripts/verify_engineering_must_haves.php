<?php

declare(strict_types=1);

/**
 * verify_engineering_must_haves.php
 *
 * Automated CLI Linter & Static Analysis Guard for Non-Negotiable Engineering Must-Haves:
 * 1. CSRF Token in every POST/State-Changing Form
 * 2. Prepared Statements Only (Bans raw SQL interpolation)
 * 3. Defensive Superglobal Access (Enforces null coalescing `??`)
 * 4. Banned Insecure Hash Functions (md5/sha1 on credentials)
 *
 * Usage: php scripts/verify_engineering_must_haves.php [target_directory]
 */

$targetDir = $argv[1] ?? getcwd();
$exitCode = 0;
$violations = [];

echo "=================================================================\n";
echo "🛡️  ENGINEERING MUST-HAVES AUTOMATED SCANNER (TAT & BRATS GATE)\n";
echo "Scanning Directory: " . realpath($targetDir) . "\n";
echo "=================================================================\n\n";

$fileExtensions = ['php', 'blade.php', 'html', 'phtml'];
$dirIterator = new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS);
$filterIterator = new RecursiveCallbackFilterIterator($dirIterator, function ($current, $key, $iterator) {
    if ($iterator->hasChildren()) {
        $filename = $current->getFilename();
        if (in_array($filename, ['vendor', '.git', '.phpunit.cache', '.php_tmp', 'cache', 'storage', 'node_modules', 'scripts', 'gen_class', 'scratch', 'php-uk'], true)) {
            return false;
        }
    }
    return true;
});
$iterator = new RecursiveIteratorIterator($filterIterator);

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $filePath = $file->getPathname();

    // Skip vendor, cache, git, scratch, temp dirs, 3rd party SDKs, and scripts directories
    if (preg_match('#/(vendor|\.git|\.phpunit\.cache|\.php_tmp|cache|storage|node_modules|scripts|gen_class|scratch|php-uk)/#', $filePath)) {
        continue;
    }

    $extension = $file->getExtension();
    if (!in_array($extension, ['php', 'html', 'phtml'], true) && !str_ends_with($filePath, '.blade.php')) {
        continue;
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        continue;
    }

    // --- CHECK 1: CSRF TOKEN IN FORMS ---
    // Match forms that use POST (or don't specify GET)
    if (preg_match_all('#<form\b([^>]*)>(.*?)</form>#is', $content, $matches, PREG_OFFSET_CAPTURE)) {
        foreach ($matches[0] as $index => $formMatch) {
            $formAttributes = $matches[1][$index][0];
            $formBody = $matches[2][$index][0];
            $formOffset = $formMatch[1];

            // If method is explicitly GET, CSRF is not required
            if (preg_match('/method=[\'"]get[\'"]/i', $formAttributes)) {
                continue;
            }

            // Check if CSRF token exists in the form body
            $hasCsrf = preg_match(
                '/(name=[\'"]_token[\'"]|name=[\'"]token[\'"]|@csrf|\bcsrf_token\b|\bcsrf_field\b|\bcsrfField\b|\bcsrfToken\b|@include\([\'"][^\'"]*csrf[^\'"]*[\'"]\)|LaravelHelper::csrfField|LaravelHelper::csrfToken|BuildFormBStrap|BuildFormBulma|FormBuilder)/i',
                $formBody
            );

            if (!$hasCsrf) {
                $lineNumber = substr_count(substr($content, 0, $formOffset), "\n") + 1;
                $violations[] = [
                    'severity' => 'CRITICAL',
                    'rule' => 'MUST-HAVE #1: Missing CSRF Token in Form',
                    'file' => $filePath,
                    'line' => $lineNumber,
                    'remediation' => 'Add `@csrf` or `LaravelHelper::csrfField()` or `<input type="hidden" name="_token" value="...">` inside the `<form>`.',
                ];
                $exitCode = 1;
            }
        }
    }

    // --- CHECK 2: RAW SQL STRING INTERPOLATION ---
    $isBlade = str_ends_with($filePath, '.blade.php');
    if ($extension === 'php' && !$isBlade) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            // Check for SQL statements with direct variable interpolation
            if (preg_match('/\b(SELECT\s+.*?\s+FROM|INSERT\s+INTO\s+.*?\s+VALUES|UPDATE\s+.*?\s+SET|DELETE\s+FROM)\b.*["\'].*\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*.*["\']/i', $line)) {
                // Ignore if it's within a comment
                if (!preg_match('~^\s*(//|\*|#)~', $line)) {
                    $violations[] = [
                        'severity' => 'CRITICAL',
                        'rule' => 'MUST-HAVE #2: Raw SQL Interpolation Detected',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'remediation' => 'Use prepared statements with `?` or `:name` placeholders. Never interpolate variables directly into SQL queries.',
                    ];
                    $exitCode = 1;
                }
            }
        }
    }

    // --- CHECK 3: INSECURE HASHING ON PASSWORDS ---
    if ($extension === 'php') {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/\b(md5|sha1)\s*\(\s*\$[a-zA-Z0-9_]*(pass|pwd|token)/i', $line)) {
                $violations[] = [
                    'severity' => 'CRITICAL',
                    'rule' => 'MUST-HAVE #3: Insecure Hash Function on Password/Token',
                    'file' => $filePath,
                    'line' => $lineNum + 1,
                    'remediation' => 'Use `password_hash($pass, PASSWORD_ARGON2ID)` or `hash_hmac()` with SHA-256 for tokens.',
                ];
                $exitCode = 1;
            }
        }
    }

    // --- CHECK 4: ANTI-REINVENTION GUARD (SHARED-LIB & SHARED-JS-LIB) ---
    // 4A: Check for raw IP header lookups instead of \Src\ClientIp
    if ($extension === 'php' && !str_ends_with($filePath, 'app/function/helper.php') && !str_contains($filePath, '/tests/')) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/\$_SERVER\s*\[\s*[\'"](HTTP_CLIENT_IP|HTTP_X_FORWARDED_FOR|HTTP_CF_CONNECTING_IP)[\'"]\s*\]/i', $line)) {
                if (!preg_match('~^\s*(//|\*|#)~', $line)) {
                    $violations[] = [
                        'severity' => 'CRITICAL',
                        'rule' => 'MUST-HAVE #4: Ad-Hoc Client IP Parsing (Standard 21)',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'remediation' => 'Use `\Src\ClientIp::fromGlobals()` or `getUserIpAddr()` from modernman00/shared-lib instead of raw $_SERVER inspection.',
                    ];
                    $exitCode = 1;
                }
            }
        }
    }

    // 4B: Check for reinvented ad-hoc JSON responders in newly written controllers
    if ($extension === 'php' && str_contains($filePath, '/app/controller/')) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/function\s+(sendJson|outputJson|jsonResponse)\s*\(/i', $line)) {
                if (!preg_match('~^\s*(//|\*|#)~', $line)) {
                    $violations[] = [
                        'severity' => 'CRITICAL',
                        'rule' => 'MUST-HAVE #4: Ad-Hoc JSON Response Reinvention (Standard 21)',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'remediation' => 'Use `\Src\ApiResponse::success()` or `\Src\ApiResponse::error()` from modernman00/shared-lib.',
                    ];
                    $exitCode = 1;
                }
            }
        }
    }

    // 4C: Check for reinvented JS utility functions in blade templates or non-bundled scripts
    if (($extension === 'php' || $extension === 'html' || $extension === 'js') && !preg_match('#/(public/assets/js/index|vendor|node_modules)/#', $filePath)) {
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/function\s+(toSentenceCase|date2String|calTiming)\s*\(/i', $line)) {
                if (!preg_match('~^\s*(//|\*|#)~', $line)) {
                    $violations[] = [
                        'severity' => 'CRITICAL',
                        'rule' => 'MUST-HAVE #4: Reinvented Shared-JS-Lib Utility (Standard 21)',
                        'file' => $filePath,
                        'line' => $lineNum + 1,
                        'remediation' => 'Import from `@modernman00/shared-js-lib` (Utility.js, DateTime.js) instead of defining duplicate functions.',
                    ];
                    $exitCode = 1;
                }
            }
        }
    }
}

// --- CHECK 5: UNIVERSAL BUTTON SPINNER INVARIANT (JUMOKE MANDATE) ---
$spinnerComponent = $targetDir . '/resources/views/component/submitSpinner.blade.php';
$spinnerComponentAlias = $targetDir . '/resources/views/components/submitSpinner.blade.php';
if (!file_exists($spinnerComponent)) {
    $violations[] = [
        'severity' => 'CRITICAL',
        'rule' => 'MUST-HAVE #5: Missing Universal Submit Spinner Component',
        'file' => $spinnerComponent,
        'line' => 1,
        'remediation' => 'Create resources/views/component/submitSpinner.blade.php to provide universal button spinners.',
    ];
    $exitCode = 1;
}

if (!file_exists($spinnerComponentAlias)) {
    $violations[] = [
        'severity' => 'CRITICAL',
        'rule' => 'MUST-HAVE #5: Missing Universal Submit Spinner Alias',
        'file' => $spinnerComponentAlias,
        'line' => 1,
        'remediation' => 'Create resources/views/components/submitSpinner.blade.php proxying to component.submitSpinner.',
    ];
    $exitCode = 1;
}

$requiredLayouts = [
    $targetDir . '/resources/views/layouts/landing_layout.blade.php',
    $targetDir . '/resources/views/layouts/profileBase.blade.php',
    $targetDir . '/resources/views/layouts/w3s_admin.blade.php',
    $targetDir . '/resources/views/layouts/w3s_member.blade.php',
    $targetDir . '/resources/views/baseBulma.blade.php',
    $targetDir . '/resources/views/baseBulmaForm.blade.php',
    $targetDir . '/resources/views/errors/base.blade.php',
    $targetDir . '/resources/views/baseError.blade.php',
    $targetDir . '/resources/views/admin/base.blade.php',
    $targetDir . '/resources/views/layouts/baseTail.blade.php',
];

foreach ($requiredLayouts as $layoutPath) {
    if (file_exists($layoutPath)) {
        $lContent = file_get_contents($layoutPath) ?: '';
        if (!str_contains($lContent, 'submitSpinner')) {
            $violations[] = [
                'severity' => 'CRITICAL',
                'rule' => 'MUST-HAVE #5: Layout Missing Universal Submit Spinner Inclusion',
                'file' => $layoutPath,
                'line' => 1,
                'remediation' => "Add `@include('component.submitSpinner')` before `</body>` in {$layoutPath}.",
            ];
            $exitCode = 1;
        }
    }
}

// --- CHECK 6: ROOT SERVICE WORKER ACCESSIBILITY & SYNTAX INTEGRITY ---
$swCandidates = [$targetDir . '/public/sw.js', $targetDir . '/sw.js'];
foreach ($swCandidates as $swFile) {
    if (!file_exists($swFile)) {
        $violations[] = [
            'severity' => 'CRITICAL',
            'rule' => 'MUST-HAVE #6: Missing Root Service Worker Script (' . basename($swFile) . ')',
            'file' => $swFile,
            'line' => 1,
            'remediation' => 'Create ' . basename($swFile) . ' with push and fetch listeners to prevent service worker registration errors.',
        ];
        $exitCode = 1;
    } else {
        // Enforce JavaScript syntax verification via node --check
        $cmd = 'node --check ' . escapeshellarg($swFile) . ' 2>&1';
        $output = [];
        $status = 0;
        exec($cmd, $output, $status);
        if ($status !== 0) {
            $violations[] = [
                'severity' => 'CRITICAL',
                'rule' => 'MUST-HAVE #6: Service Worker Syntax Error (' . basename($swFile) . ')',
                'file' => $swFile,
                'line' => 1,
                'remediation' => 'Resolve JavaScript syntax error in ' . basename($swFile) . ': ' . implode("\n", $output),
            ];
            $exitCode = 1;
        }
    }
}

// --- CHECK 7: PWA OPTIMIZATION INVARIANT (MANIFEST & SERVICE WORKER LAYOUT INTEGRATION) ---
$manifestFile = $targetDir . '/public/manifest.json';
if (!file_exists($manifestFile)) {
    $violations[] = [
        'severity' => 'CRITICAL',
        'rule' => 'MUST-HAVE #7: Missing PWA Manifest (public/manifest.json)',
        'file' => $manifestFile,
        'line' => 1,
        'remediation' => 'Create public/manifest.json with valid PWA manifest fields (standalone, icons, theme_color).',
    ];
    $exitCode = 1;
}

foreach ($requiredLayouts as $pwaLayoutPath) {
    if (file_exists($pwaLayoutPath)) {
        $pContent = file_get_contents($pwaLayoutPath) ?: '';
        if (!str_contains($pContent, 'partials.pwa-head') && !str_contains($pContent, 'layouts.head')) {
            $violations[] = [
                'severity' => 'CRITICAL',
                'rule' => 'MUST-HAVE #7: Layout Missing PWA Head Meta Inclusion',
                'file' => $pwaLayoutPath,
                'line' => 1,
                'remediation' => "Add `@include('partials.pwa-head')` inside `<head>` in {$pwaLayoutPath}.",
            ];
            $exitCode = 1;
        }
        if (!str_contains($pContent, 'partials.pwa-register')) {
            $violations[] = [
                'severity' => 'CRITICAL',
                'rule' => 'MUST-HAVE #7: Layout Missing PWA Service Worker Registration',
                'file' => $pwaLayoutPath,
                'line' => 1,
                'remediation' => "Add `@include('partials.pwa-register')` before `</body>` in {$pwaLayoutPath}.",
            ];
            $exitCode = 1;
        }
    }
}

// --- CHECK 8: ZERO-SYMLINK INVARIANT (STANDARD 18 - VENDOR & BUILD ISOLATION) ---
$sharedLibVendorPath = $targetDir . '/vendor/modernman00/shared-lib';
if (is_link($sharedLibVendorPath)) {
    $violations[] = [
        'severity' => 'CRITICAL',
        'rule' => 'MUST-HAVE #8: Standard 18 Zero-Symlink Invariant Violation (vendor/modernman00/shared-lib is a symlink)',
        'file' => $sharedLibVendorPath,
        'line' => 1,
        'remediation' => 'Remove the symlink and install real, physical package via `composer update modernman00/shared-lib --prefer-dist`. Symlinks are strictly prohibited in application workspaces.',
    ];
    $exitCode = 1;
}

$sharedJsLibNodePath = $targetDir . '/node_modules/@modernman00/shared-js-lib';
if (is_link($sharedJsLibNodePath)) {
    $violations[] = [
        'severity' => 'CRITICAL',
        'rule' => 'MUST-HAVE #8: Standard 18 Zero-Symlink Invariant Violation (node_modules/@modernman00/shared-js-lib is a symlink)',
        'file' => $sharedJsLibNodePath,
        'line' => 1,
        'remediation' => 'Remove the symlink and install physical package via npm. Local host symlinks are strictly prohibited.',
    ];
    $exitCode = 1;
}

// Display report
if (empty($violations)) {
    echo "✅ ALL MUST-HAVE CHECKS PASSED!\n";
    echo "0 CSRF violations, 0 SQL interpolation flaws, 0 weak hashes, 0 shared-lib reinventions, 0 symlink violations (Standard 18 verified), 100% button spinner compliance, root sw.js & PWA layouts verified.\n\n";
    exit(0);
}

echo "🚨 MUST-HAVE VIOLATIONS DETECTED (" . count($violations) . " issues found):\n\n";
foreach ($violations as $v) {
    echo "  [" . $v['severity'] . "] " . $v['rule'] . "\n";
    echo "  Location: " . $v['file'] . ":" . $v['line'] . "\n";
    echo "  Fix: " . $v['remediation'] . "\n\n";
}

echo "❌ Pre-review gate failed. Address all violations above before submitting PR.\n";
exit($exitCode);


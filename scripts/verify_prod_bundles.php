<?php

declare(strict_types=1);

/**
 * Production-bundle gate: refuses saved (tracked) webpack output that is a dev/test build.
 *
 * Why: a `mix watch` build saved to git (eval-source-map JS, an expanded main.css, an unversioned
 * mix-manifest.json) runs against the production vendor.js and breaks pages (PartyPlatform Guest Builder, 610113c).
 *
 * It is driven by mix-manifest.json, so it needs no per-app file list: every bundle the manifest names is checked.
 *   - webpack.mix.js calls .version() (production only) -> every manifest entry must carry ?id=
 *   - JS must not carry the webpack eval-source-map banner or sourceURL=webpack markers
 *   - a versioned app's CSS bundle must be minified (production), not expanded
 *
 * Usage:
 *   php scripts/verify_prod_bundles.php            check the working tree
 *   php scripts/verify_prod_bundles.php --staged   check what is staged (used by .githooks/pre-commit)
 *
 * Fix: stop `mix watch`, run `npm run production`, then `git add` the rebuilt files.
 */

$root   = dirname(__DIR__);
$staged = in_array('--staged', $argv ?? [], true);
chdir($root);

function tracked(string $path): bool
{
    exec('git ls-files --error-unmatch -- ' . escapeshellarg($path) . ' 2>/dev/null', $o, $code);
    return $code === 0;
}

/** @return string|null contents from the index (--staged) or the working tree; null when absent */
function readBundle(string $path, bool $staged): ?string
{
    if ($staged) {
        $out = [];
        exec('git show ' . escapeshellarg(':' . $path) . ' 2>/dev/null', $out, $code);
        return $code === 0 ? implode("\n", $out) : null;
    }
    return is_file($path) ? (string)file_get_contents($path) : null;
}

$stagedFiles = [];
if ($staged) {
    exec('git diff --cached --name-only --diff-filter=ACMR', $stagedFiles);
}

$mixConfig = is_file('webpack.mix.js') ? (string)file_get_contents('webpack.mix.js') : '';
$versioned = (bool)preg_match('/\.version\s*\(/', $mixConfig);

$manifestPath = null;
foreach (['public/mix-manifest.json', 'mix-manifest.json'] as $candidate) {
    if (is_file($candidate) && tracked($candidate)) {
        $manifestPath = $candidate;
        break;
    }
}
if ($manifestPath === null) {
    echo "✅ No tracked mix-manifest.json, so no saved webpack bundles to check.\n";
    exit(0);
}

$problems = [];
$checked  = 0;
$manifestBody = readBundle($manifestPath, $staged);
$entries      = is_string($manifestBody) ? json_decode($manifestBody, true) : null;
if (!is_array($entries)) {
    fwrite(STDERR, "❌ $manifestPath is missing or not valid JSON.\n");
    exit(1);
}
$manifestDir = dirname($manifestPath) === '.' ? '' : dirname($manifestPath) . '/';

if ($versioned) {
    foreach ($entries as $key => $value) {
        if (is_string($value) && !str_contains($value, '?id=')) {
            $problems[] = "$manifestPath has an unversioned entry ($key), which is what a dev build writes";
            break;
        }
    }
}

foreach (array_keys($entries) as $key) {
    $path = $manifestDir . ltrim((string)preg_replace('/\?.*$/', '', (string)$key), '/');
    if (!tracked($path) || ($staged && !in_array($path, $stagedFiles, true))) {
        continue;
    }
    $body = readBundle($path, $staged);
    if ($body === null) {
        continue;
    }
    $checked++;

    if (str_ends_with($path, '.js')
        && (str_contains($body, 'An "eval-source-map" devtool has been used') || str_contains($body, '//# sourceURL=webpack'))) {
        $problems[] = "$path is a dev build (eval-source-map)";
    }

    if ($versioned && str_ends_with($path, '.css') && substr_count($body, "\n") > 50) {
        $problems[] = "$path is expanded (" . (substr_count($body, "\n") + 1) . ' lines), not the minified production build';
    }
}

if ($problems === []) {
    echo "✅ Production bundles OK ($checked saved bundle files checked" . ($staged ? ', staged' : '') . ").\n";
    exit(0);
}

fwrite(STDERR, "❌ Dev/test build artefacts are saved in git:\n");
foreach ($problems as $problem) {
    fwrite(STDERR, "   - $problem\n");
}
fwrite(STDERR, "\n   Stop `mix watch`, run `npm run production`, then `git add` the rebuilt files.\n");
exit(1);

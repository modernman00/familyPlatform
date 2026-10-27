<?php 

function printMemory (): void
{
    /** Currently used memory */
    $mem_usage = memory_get_usage(true);
    /** Peak memory usage */
    $mem_peak = memory_get_peak_usage(true);

    echo "MEM_USAGE : This script is using : <strong>" .round($mem_usage / 1024) ." kb </strong> of memory". BR;
    echo "PEAK_USAGE : This script is using : <strong>" .round($mem_peak / 1024) ."kb </strong> of memory" . BR;
}

/**
 * Cache-busting query value for a file under /public — its last-modified time,
 * so the asset URL changes only when the file itself changes (LH-1). Using
 * time() here (as templates previously did) made every page load re-download
 * the whole bundle and fails Lighthouse "efficient cache policy".
 *
 * @param string $relativePath e.g. "js/index.js" or "css/main.css"
 */
function assetVersion(string $relativePath): string
{
    static $cache = [];
    $rel = ltrim($relativePath, '/');
    if (isset($cache[$rel])) {
        return $cache[$rel];
    }
    $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
    $mtime = @filemtime($base . '/public/' . $rel);

    return $cache[$rel] = $mtime !== false ? (string) $mtime : '1';
}

if (!function_exists('trans')) {
    /**
     * Global I18n translation helper.
     *
     * @param string $key Translation key
     * @param array<string, string|int|float>|string|null $replaceOrFallback
     * @param string|null $fallback
     * @return string
     */
    function trans(string $key, array|string|null $replaceOrFallback = null, ?string $fallback = null): string
    {
        if (is_array($replaceOrFallback)) {
            return \App\classes\I18n::trans($key, $replaceOrFallback, $fallback);
        }
        return \App\classes\I18n::trans($key, [], is_string($replaceOrFallback) ? $replaceOrFallback : $fallback);
    }
}

if (!function_exists('__')) {
    /**
     * Global I18n translation helper alias.
     *
     * @param string $key Translation key
     * @param array<string, string|int|float>|string|null $replaceOrFallback
     * @param string|null $fallback
     * @return string
     */
    function __(string $key, array|string|null $replaceOrFallback = null, ?string $fallback = null): string
    {
        if (is_array($replaceOrFallback)) {
            return \App\classes\I18n::trans($key, $replaceOrFallback, $fallback);
        }
        return \App\classes\I18n::trans($key, [], is_string($replaceOrFallback) ? $replaceOrFallback : $fallback);
    }
}

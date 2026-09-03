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
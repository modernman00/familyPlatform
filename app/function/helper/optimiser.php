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

/**
 * Resize and cache a local /resources/images/profile/* raster image (cover or avatar)
 * into /resources/images/profile/_thumbs/ as WebP (with JPEG fallback) so mobile/desktop
 * pages never download 300-700 KB originals into 40px avatars or 404px cover slots.
 */
function optimiseLocalImageUrl(string $url, int $maxWidth = 960): string
{
    $trimmed = trim($url);
    if ($trimmed === '') {
        return $trimmed;
    }

    if (preg_match('#^https://lh3\.googleusercontent\.com/.+=[sw]\d+(?:-no)?$#i', $trimmed)) {
        return (string) preg_replace('#=[sw]\d+(?:-no)?$#i', '=w' . max(160, min(1280, $maxWidth)), $trimmed);
    }

    if (!preg_match('#^/?(resources/images/profile/([a-zA-Z0-9_.-]+\.(?:jpe?g|png|webp)))$#i', $trimmed, $m)) {
        return $trimmed;
    }

    $base = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
    $srcPath = $base . '/' . $m[1];
    $fileName = $m[2];
    if (!is_file($srcPath) || !function_exists('getimagesize')) {
        return $trimmed;
    }

    $safeW = max(80, min(1280, $maxWidth));
    $useWebp = function_exists('imagewebp');
    $ext = $useWebp ? 'webp' : 'jpg';
    $thumbName = $safeW . '_' . pathinfo($fileName, PATHINFO_FILENAME) . '.' . $ext;
    $thumbDir = $base . '/resources/images/profile/_thumbs';
    $thumbPath = $thumbDir . '/' . $thumbName;
    $thumbUrl = '/resources/images/profile/_thumbs/' . $thumbName;

    $srcMtime = @filemtime($srcPath) ?: 0;
    if (is_file($thumbPath) && (@filemtime($thumbPath) ?: 0) >= $srcMtime) {
        return $thumbUrl;
    }

    $info = @getimagesize($srcPath);
    if (!is_array($info) || empty($info[0]) || empty($info[1])) {
        return $trimmed;
    }
    $origW = (int) $info[0];
    $origH = (int) $info[1];
    if ($origW <= $safeW && filesize($srcPath) <= 45000) {
        return $trimmed;
    }

    $mime = $info['mime'];
    $srcImg = match ($mime) {
        'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($srcPath) : false,
        'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($srcPath) : false,
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($srcPath) : false,
        default => false,
    };
    if (!$srcImg) {
        return $trimmed;
    }

    if (!is_dir($thumbDir)) {
        @mkdir($thumbDir, 0755, true);
    }

    $targetW = min($origW, $safeW);
    $targetH = max(1, (int) round($origH * ($targetW / $origW)));
    $dstImg = imagecreatetruecolor($targetW, $targetH);
    if (!$dstImg) {
        imagedestroy($srcImg);
        return $trimmed;
    }

    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

    $ok = $useWebp ? @imagewebp($dstImg, $thumbPath, 82) : @imagejpeg($dstImg, $thumbPath, 82);
    imagedestroy($dstImg);
    imagedestroy($srcImg);

    return $ok ? $thumbUrl : $trimmed;
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

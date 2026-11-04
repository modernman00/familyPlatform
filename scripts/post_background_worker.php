<?php
declare(strict_types=1);

/**
 * Detached Background Post Pipeline Worker (ATG + Gatewatcher Approved)
 *
 * Runs outside the Apache mpm_prefork HTTP request lifecycle so that publishing
 * posts (with 3D family emojis, uploaded photos/videos, or Google Photos links)
 * returns to the browser in ~150ms without blocking on:
 *   1. Pusher real-time broadcast
 *   2. Visual Supremacy Engine (VSE) WebP/AVIF generation & External Video SSD caching
 *   3. Outbound SMTP email notifications to family members
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$postNo = isset($argv[1]) ? (int)$argv[1] : 0;
if ($postNo <= 0) {
    exit(0);
}

$baseDir = dirname(__DIR__);
require_once $baseDir . '/app/config/init.php';

$tStart = microtime(true);

// Step 1: Pusher real-time broadcast to active family members
$tPusherStart = microtime(true);
try {
    \App\controller\members\PostMessage::getNewPostPusher($postNo);
} catch (\Throwable $th) {
    error_log('[post_background_worker Pusher] ' . $th->getMessage());
}
$pusherDuration = round((microtime(true) - $tPusherStart) * 1000, 2);

// Step 2: Visual Supremacy Engine & External Video SSD Cache
$tVseStart = microtime(true);
try {
    $postBaseDir = realpath($baseDir . '/resources/images/post');
    $postRecord = \App\model\Post::postByNo($postNo);
    if (is_array($postRecord)) {
        for ($i = 0; $i < 6; $i++) {
            $imgCol = 'post_img' . $i;
            $imgVal = trim((string)($postRecord[$imgCol] ?? ''));
            if ($imgVal === '') {
                continue;
            }
            if (!str_starts_with($imgVal, 'http://') && !str_starts_with($imgVal, 'https://')) {
                if ($postBaseDir && is_dir($postBaseDir) && !str_starts_with($imgVal, '/resources/videos/')) {
                    $localImagePath = rtrim($postBaseDir, '/\\') . DIRECTORY_SEPARATOR . basename($imgVal);
                    if (file_exists($localImagePath) && is_file($localImagePath)) {
                        \App\services\VisualSupremacyService::processInPlace($localImagePath);
                    }
                }
            } elseif (str_contains($imgVal, 'googleusercontent.com') && preg_match('/=(?:m18|m22|m37|dv)/i', $imgVal)) {
                \App\services\LinkPreviewService::cacheExternalVideoLocally($imgVal);
            }
        }
    }
} catch (\Throwable $e) {
    error_log('[post_background_worker VSE] ' . $e->getMessage());
}
$vseDuration = round((microtime(true) - $tVseStart) * 1000, 2);

// Step 3: Outbound Email Fan-out (SMTP loop to family members)
$tEmailStart = microtime(true);
try {
    \App\controller\members\PostMessage::getNewPostAndEmail($postNo);
} catch (\Throwable $th) {
    error_log('[post_background_worker Email] ' . $th->getMessage());
}
$emailDuration = round((microtime(true) - $tEmailStart) * 1000, 2);

$totalBgDuration = round((microtime(true) - $tStart) * 1000, 2);
error_log(sprintf(
    '[post_background_worker] Post #%d completed detached pipeline: Pusher %0.1fms, VSE %0.1fms, Email %0.1fms (Total: %0.1fms)',
    $postNo,
    $pusherDuration,
    $vseDuration,
    $emailDuration,
    $totalBgDuration
));

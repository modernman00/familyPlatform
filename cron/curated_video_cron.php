<?php

declare(strict_types=1);

/**
 * CLI Cron Runner: Curated Video Content Engine
 *
 * Fetches family-safe funny & family YouTube videos from active sources and
 * publishes one to everyone's feed when auto-publish is enabled and due.
 *
 * Usage:
 *   php cron/curated_video_cron.php [--force]
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env') && class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
}

$force = in_array('--force', $argv ?? [], true);

$lockPath = sys_get_temp_dir() . '/familyplatform-content-engine-cron.lock';
$lockHandle = fopen($lockPath, 'c');
if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    if (is_resource($lockHandle)) {
        fclose($lockHandle);
    }
    fwrite(STDERR, "[CuratedVideoCron] Another instance is already running.\n");
    exit(0);
}

try {
    $engine = new \App\services\CuratedVideoEngineService();
    $result = $engine->runScheduledAutoPublish($force);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (\Throwable $e) {
    fwrite(STDERR, "[CuratedVideoCron] Error: " . $e->getMessage() . "\n");
    exit(1);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

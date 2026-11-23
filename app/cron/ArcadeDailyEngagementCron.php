<?php

declare(strict_types=1);

namespace App\cron;

require_once __DIR__ . '/../../vendor/autoload.php';

if (file_exists(__DIR__ . '/../../.env')) {
    \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..')->safeLoad();
}

use App\services\ArcadeDailyEngagementService;
use Src\Db;

$isCli = PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__;

if ($isCli) {
    $args = $_SERVER['argv'] ?? [];
    $dryRun = in_array('--dry-run', $args, true);

    $lockPath = sys_get_temp_dir() . '/familyplatform-arcade-daily-cron.lock';
    $lockHandle = fopen($lockPath, 'c');
    if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
        if (is_resource($lockHandle)) {
            fclose($lockHandle);
        }
        fwrite(STDERR, "Arcade daily engagement cron is already running.\n");
        exit(1);
    }

    try {
        $service = new ArcadeDailyEngagementService(Db::connect2());
        $stats = $service->dispatchDailyNudges($dryRun);
        echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        exit(0);
    } catch (\Throwable $e) {
        fwrite(STDERR, 'Arcade daily engagement cron failed: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    } finally {
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }
}

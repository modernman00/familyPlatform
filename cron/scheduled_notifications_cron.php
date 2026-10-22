<?php

declare(strict_types=1);

/**
 * Scheduled Notifications Dispatcher - Cron Script
 *
 * Runs per-minute to process and release scheduled notifications across
 * Pusher WebSockets, WebPush, and Email fallback.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/scheduled_notifications_cron.php
 */

if (PHP_SAPI !== 'cli') {
    $cronSecret = (string)($_ENV['CRON_SECRET'] ?? getenv('CRON_SECRET') ?: '');
    $providedSecret = (string)($_GET['secret'] ?? $_SERVER['HTTP_X_CRON_SECRET'] ?? '');
    if (empty($cronSecret) || !hash_equals($cronSecret, $providedSecret)) {
        http_response_code(403);
        echo "Forbidden: CLI or valid cron secret required.\n";
        exit;
    }
}

require __DIR__ . '/../app/config/init.php';

use Src\NotificationOrchestrator;

try {
    $dispatched = NotificationOrchestrator::processScheduled(50);
    if ($dispatched > 0) {
        echo date('[Y-m-d H:i:s] ') . "Dispatched {$dispatched} scheduled notification(s).\n";
    }
} catch (\Throwable $th) {
    echo "Error processing scheduled notifications: " . $th->getMessage() . "\n";
    error_log("[scheduled_notifications_cron.php] " . $th->getMessage());
}

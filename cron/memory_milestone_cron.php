<?php

declare(strict_types=1);

/**
 * Daily Memory & Milestone Prompt Runner - Cron Script
 *
 * Scans for nostalgia flashback memories ("On this day X years ago..."),
 * upcoming birthdays, and wedding anniversaries, dispatching native WebPush
 * notifications to subscribed family members.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/memory_milestone_cron.php
 */

// If invoked over HTTP, restrict with CRON_SECRET token
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

use App\services\MemoryMilestoneService;

echo "Starting Daily Memory & Milestone Notification Engine (" . date('Y-m-d H:i:s') . ")...\n";

try {
    $service = new MemoryMilestoneService();
    $result = $service->dispatchDailyMilestonePushNotifications();

    echo "Completed.\n";
    echo "Families Scanned: " . (int)$result['processed_families'] . "\n";
    echo "Push Notifications Sent: " . (int)$result['notifications_sent'] . "\n";
} catch (\Throwable $th) {
    echo "Error executing memory milestone cron: " . $th->getMessage() . "\n";
    error_log("[memory_milestone_cron.php] " . $th->getMessage());
}

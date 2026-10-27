<?php

declare(strict_types=1);

/**
 * 🎓 Scheduled Cron: Adulthood Transition & 18th Birthday Watchdog
 * 
 * Runs daily to detect children who turn 18, triggers family feed announcements,
 * unseals "Family Vault / Time Capsules", and transitions accounts to sovereign adult status.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

echo "=================================================================\n";
echo "🎓 RUNNING ADULTHOOD TRANSITION & 18TH BIRTHDAY WATCHDOG\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================================\n";

try {
    $count = \App\services\AdulthoodTransitionService::processAllPendingGraduations();
    echo "✅ Successfully processed {$count} adulthood graduation(s).\n";
    exit(0);
} catch (\Throwable $e) {
    echo "❌ Watchdog Error: " . $e->getMessage() . "\n";
    exit(1);
}

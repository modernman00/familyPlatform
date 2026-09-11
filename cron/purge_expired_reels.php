<?php

declare(strict_types=1);

/**
 * Family Reels Expiration & Purge Runner - Cron Script
 *
 * Scans for reels older than REELS_EXPIRATION_DAYS (default 7 days) and
 * safely purges their records and physical video/thumbnail files.
 *
 * To be executed via CLI or Cron:
 * /usr/local/bin/php /path/to/familyPlatform/cron/purge_expired_reels.php
 */

// If invoked over HTTP, restrict to non-prod or require CRON_SECRET token
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

use App\model\Reel;

$days = Reel::getExpirationDays();

echo "Starting Family Reels Expiration Purge (Lifespan: {$days} days)...\n";

try {
    $purged = Reel::purgeExpiredReels();
    echo "Completed. Purged {$purged} expired reel(s).\n";
} catch (\Throwable $th) {
    echo "Error during reels purge: " . $th->getMessage() . "\n";
    error_log("[purge_expired_reels.php] " . $th->getMessage());
}

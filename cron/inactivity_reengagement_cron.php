<?php

declare(strict_types=1);

/**
 * Autonomous Inactivity & Tiered Dormancy Re-Engagement - Cron Script
 *
 * Runs on a scheduled cadence (e.g. Wednesday and Sunday at 10:30 AM) to evaluate
 * registered accounts across 4 inactivity tiers (Ghost Pioneer, Stalled Explorer,
 * Sunday Banter, and Deep Hibernation) and dispatches 1-click magic access prompts.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/inactivity_reengagement_cron.php [--dry-run] [--user-id=USER_ID]
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
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

use App\services\OnboardingFollowupService;

$dryRun = in_array('--dry-run', $argv ?? [], true) || (isset($_GET['dry_run']) && $_GET['dry_run'] === '1');

// Optional force user flag: --user-id=USER_ID or ?user_id=USER_ID
$forceUserId = null;
if (isset($_GET['user_id']) && is_string($_GET['user_id'])) {
    $forceUserId = trim($_GET['user_id']);
} else {
    foreach ($argv ?? [] as $arg) {
        if (str_starts_with($arg, '--user-id=')) {
            $forceUserId = trim(substr($arg, strlen('--user-id=')));
            break;
        }
    }
}

echo "Starting Autonomous Inactivity & Tiered Dormancy Engine (" . date('Y-m-d H:i:s') . ") [DryRun: " . ($dryRun ? 'YES' : 'NO') . "]...\n";

try {
    $result = OnboardingFollowupService::runAllInactivityTouchpoints($dryRun, $forceUserId);

    echo "Completed successfully.\n";
    foreach ($result as $stage => $count) {
        if ($stage !== 'dry_run') {
            echo " - {$stage}: {$count}\n";
        }
    }
} catch (\Throwable $th) {
    echo "Error executing inactivity re-engagement cron: " . $th->getMessage() . "\n";
    error_log("[inactivity_reengagement_cron.php] " . $th->getMessage());
}

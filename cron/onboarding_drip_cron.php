<?php

declare(strict_types=1);

/**
 * 60-Day Onboarding & Heritage Engagement Drip - Cron Script
 *
 * Runs daily (e.g. 10:00 AM) to evaluate eligible member cohorts and dispatch
 * personalized heritage prompts across Days 1, 3, 7, 14, 21, 28, 35, 42, 49, and 60.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/onboarding_drip_cron.php [--dry-run] [--user-id=USER_ID]
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

echo "Starting 60-Day Onboarding & Heritage Engagement Drip Engine (" . date('Y-m-d H:i:s') . ") [DryRun: " . ($dryRun ? 'YES' : 'NO') . "]...\n";

try {
    $result = OnboardingFollowupService::runAllTouchpoints($dryRun, $forceUserId);

    echo "Completed successfully.\n";
    foreach ($result as $stage => $count) {
        if ($stage !== 'dry_run') {
            echo " - {$stage}: {$count}\n";
        }
    }
} catch (\Throwable $th) {
    echo "Error executing onboarding drip cron: " . $th->getMessage() . "\n";
    error_log("[onboarding_drip_cron.php] " . $th->getMessage());
}

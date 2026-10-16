<?php

declare(strict_types=1);

/**
 * Sunday Family Sanctuary Digest - Cron Script
 *
 * Runs on Sunday evenings (18:00) to dispatch the personalized family digest
 * (Sunday Trivia, upcoming birthdays/anniversaries in the next 7 days, and lineage quests)
 * to keep family members actively engaged.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/sunday_digest_cron.php [--dry-run]
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

use App\services\FamilyDigestService;

$dryRun = in_array('--dry-run', $argv ?? [], true) || (isset($_GET['dry_run']) && $_GET['dry_run'] === '1');

echo "Starting Sunday Family Digest Dispatch Engine (" . date('Y-m-d H:i:s') . ") [DryRun: " . ($dryRun ? 'YES' : 'NO') . "]...\n";

try {
    $result = FamilyDigestService::dispatchSundayDigest($dryRun);

    echo "Completed.\n";
    echo "Families Scanned: " . (int)$result['families_scanned'] . "\n";
    echo "Members Notified: " . (int)$result['members_notified'] . "\n";
} catch (\Throwable $th) {
    echo "Error executing Sunday digest cron: " . $th->getMessage() . "\n";
    error_log("[sunday_digest_cron.php] " . $th->getMessage());
}

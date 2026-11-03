<?php

declare(strict_types=1);

/**
 * Weekly Activity Reel Curation - Cron Script
 *
 * Runs automatically every Sunday morning (03:00 UTC) to curate
 * the week's photos, milestones, and high-sentiment moments across
 * active family circles and prepare the 30-60 second draft video reels
 * pinned to each user's feed for 1-tap approval.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/weekly_activity_curation_cron.php [--dry-run]
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

use App\services\WeeklyActivityVideoCuratorService;
use Src\Db;

$dryRun = in_array('--dry-run', $argv ?? [], true) || (isset($_GET['dry_run']) && $_GET['dry_run'] === '1');

echo "Starting Weekly Activity Reel Curation Engine (" . date('Y-m-d H:i:s') . ") [DryRun: " . ($dryRun ? 'YES' : 'NO') . "]...\n";

try {
    $db = Db::connect2();

    // Fetch active family members with registered family codes
    $stmt = $db->query(
        'SELECT DISTINCT a.id AS user_id, p.famCode
         FROM account a
         INNER JOIN personal p ON a.id = p.id
         WHERE p.famCode IS NOT NULL 
           AND p.famCode != ""
           AND a.status = "approved"
         LIMIT 200'
    );
    /** @var array<int, array<string, mixed>> $members */
    $members = ($stmt instanceof \PDOStatement) ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

    $curatedCount = 0;
    $skippedCount = 0;

    foreach ($members as $member) {
        $userId = (string)($member['user_id'] ?? '');
        $famCode = (string)($member['famCode'] ?? '');

        if ($userId === '' || $famCode === '') {
            continue;
        }

        // Check if pending draft already exists for this week
        $existing = WeeklyActivityVideoCuratorService::getPendingReel($userId, $famCode);
        if ($existing !== null) {
            $skippedCount++;
            continue;
        }

        if (!$dryRun) {
            WeeklyActivityVideoCuratorService::curateWeeklyActivity($userId, $famCode, false);
        }
        $curatedCount++;
    }

    echo "Completed.\n";
    echo "Members Processed: " . count($members) . "\n";
    echo "Draft Reels Curated: {$curatedCount}\n";
    echo "Existing Drafts Skipped: {$skippedCount}\n";

} catch (\Throwable $th) {
    echo "Error executing weekly activity curation cron: " . $th->getMessage() . "\n";
    error_log("[weekly_activity_curation_cron.php] " . $th->getMessage());
}

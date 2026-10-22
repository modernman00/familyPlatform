<?php

declare(strict_types=1);

/**
 * Family Code Approval & Expiry Runner - Cron Script
 *
 * Sends 2-day approval reminder notifications to approvers and requesters,
 * and automatically expires pending requests older than 7 days.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/family_code_approval_cron.php
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

use App\cron\FamilyCodeApprovalReminder;
use Src\Db;

echo "Starting Family Code Approval & Expiry Engine (" . date('Y-m-d H:i:s') . ")...\n";

try {
    $pdo = Db::connect2();
    $reminder = new FamilyCodeApprovalReminder($pdo);
    $result = $reminder->runAll();

    echo "Completed.\n";
    echo "Approval Reminders Sent: " . (int)$result['reminders_sent'] . "\n";
    echo "Pending Requests Expired: " . (int)$result['requests_expired'] . "\n";
} catch (\Throwable $th) {
    echo "Error executing family code approval cron: " . $th->getMessage() . "\n";
    error_log("[family_code_approval_cron.php] " . $th->getMessage());
}

<?php

declare(strict_types=1);

/**
 * Family Celebrations - Cron Script (RFC-0008)
 *
 * Tells each family about birthdays and wedding anniversaries the day before
 * and on the day, and sends the celebrant a greeting from Family Platform.
 * Safe to run more than once a day: each notice is claimed before it is sent.
 *
 * Execution (daily, e.g. 08:00):
 * 0 8 * * * php /path/to/familyPlatform/cron/celebration_cron.php
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

use App\services\CelebrationService;

$traceId = bin2hex(random_bytes(8));

try {
    $result = CelebrationService::runDaily();
    echo json_encode([
        'event' => 'celebrations.run',
        'trace_id' => $traceId,
        'at' => date('c'),
        'occasions' => $result['occasions'],
        'notices' => $result['notices'],
        'greetings' => $result['greetings'],
        'skipped' => $result['skipped'],
        'failed' => $result['failed'],
    ], JSON_UNESCAPED_SLASHES) . "\n";
    exit($result['failed'] > 0 ? 1 : 0);
} catch (\Throwable $th) {
    echo "Error sending celebration notices: " . $th->getMessage() . "\n";
    error_log("[celebration_cron.php] trace_id={$traceId} " . $th->getMessage());
    exit(1);
}

<?php

declare(strict_types=1);

/**
 * Event Reminder Dispatcher - Cron Script (RFC-0007)
 *
 * Sends the 7-day, 1-day and on-the-day reminders for family events
 * (push, in-app notification and email). Safe to run more than once a day:
 * each reminder is claimed in event_reminder_log before it is sent.
 *
 * Execution (daily, e.g. 08:05):
 * 5 8 * * * php /path/to/familyPlatform/cron/event_reminder_cron.php
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

use App\controller\members\Event;

$traceId = bin2hex(random_bytes(8));

try {
    $result = Event::runReminders();
    echo json_encode([
        'event' => 'event_reminders.run',
        'trace_id' => $traceId,
        'at' => date('c'),
        'events' => $result['events'],
        'sent' => $result['sent'],
        'skipped' => $result['skipped'],
        'failed' => $result['failed'],
    ], JSON_UNESCAPED_SLASHES) . "\n";
    exit($result['failed'] > 0 ? 1 : 0);
} catch (\Throwable $th) {
    echo "Error sending event reminders: " . $th->getMessage() . "\n";
    error_log("[event_reminder_cron.php] trace_id={$traceId} " . $th->getMessage());
    exit(1);
}

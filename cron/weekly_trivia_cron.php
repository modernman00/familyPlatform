<?php

declare(strict_types=1);

/**
 * Sunday Family Trivia & Guess Who Generator - Cron Script
 *
 * Scans active families and automatically generates a weekly Sunday heritage
 * trivia challenge from ground-truth family tree records if one has not yet
 * been published for the current 7-day cycle.
 *
 * Execution:
 * php /path/to/familyPlatform/cron/weekly_trivia_cron.php
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

use App\services\FamilyTriviaService;
use Src\Db;

echo "Starting Sunday Family Trivia Generator Engine (" . date('Y-m-d H:i:s') . ")...\n";

try {
    $db = Db::connect2();
    $stmt = $db->query('SELECT DISTINCT family_code FROM family_nodes WHERE family_code IS NOT NULL AND family_code != ""');
    /** @var array<int, array<string, mixed>> $families */
    $families = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $generatedCount = 0;
    $skippedCount = 0;

    foreach ($families as $row) {
        $famCode = (string)($row['family_code'] ?? '');
        if ($famCode === '') {
            continue;
        }

        if (FamilyTriviaService::hasActiveWeeklyTrivia($famCode)) {
            $skippedCount++;
            continue;
        }

        $result = FamilyTriviaService::generateWeeklyHeritageTrivia($famCode);
        if ($result !== null) {
            $generatedCount++;
            echo "  [+] Generated trivia for family {$famCode}: \"{$result['question']}\"\n";
        }
    }

    echo "Completed.\n";
    echo "Families Scanned: " . count($families) . "\n";
    echo "New Trivia Challenges Generated: " . $generatedCount . "\n";
    echo "Families Already Active: " . $skippedCount . "\n";
} catch (\Throwable $th) {
    echo "Error executing weekly trivia cron: " . $th->getMessage() . "\n";
    error_log("[weekly_trivia_cron.php] " . $th->getMessage());
}

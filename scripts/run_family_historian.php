<?php

declare(strict_types=1);

/**
 * Family Historian — "Do You Know?" Automated Timeline Posts Cron Runner
 *
 * Usage:
 *   php scripts/run_family_historian.php
 *   php scripts/run_family_historian.php --dry-run
 *   php scripts/run_family_historian.php --famCode=OLAWALE --date=2026-11-01
 *   php scripts/run_family_historian.php --famCode=OLAWALE --force-pillar=occupation_skills
 *
 * Suggested Crontab (daily at 08:00 UTC):
 *   0 8 * * * php /Users/waleolaogun/Sites/familyPlatform/scripts/run_family_historian.php >> /var/log/family_historian.log 2>&1
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\services\FamilyHistorianService;
use Src\Db;

$options = getopt('', ['dry-run', 'famCode::', 'date::', 'force-pillar::']);
$dryRun = array_key_exists('dry-run', $options);
$famCode = isset($options['famCode']) ? trim((string)$options['famCode']) : '';
$dateStr = isset($options['date']) ? trim((string)$options['date']) : '';
$forcePillar = isset($options['force-pillar']) ? trim((string)$options['force-pillar']) : null;

$now = null;
if ($dateStr !== '') {
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $dateStr);
    if ($parsed === false) {
        fwrite(STDERR, "[FamilyHistorian] Invalid --date format. Expected YYYY-MM-DD.\n");
        exit(1);
    }
    $now = $parsed->setTime(8, 0, 0);
}

try {
    $db = Db::connect2();
    if ($famCode !== '') {
        $results = [FamilyHistorianService::runForFamily($famCode, $db, $now, $dryRun, $forcePillar)];
    } else {
        $results = FamilyHistorianService::runForAllFamilies($db, $now, $dryRun);
    }

    $published = 0;
    $skipped = 0;
    $errors = 0;
    foreach ($results as $famRes) {
        foreach ($famRes['actions'] ?? [] as $action) {
            $status = (string)($action['status'] ?? '');
            if ($status === 'published') {
                $published++;
                echo sprintf(
                    "[FamilyHistorian] PUBLISHED %s (%s) for #%s -> post_no=%d, img=%s\n",
                    (string)$action['kind'],
                    (string)$action['pillar'],
                    (string)$famRes['family_code'],
                    (int)($action['post_no'] ?? 0),
                    (string)($action['image_filename'] ?? 'none')
                );
            } elseif ($status === 'error') {
                $errors++;
                fwrite(STDERR, sprintf(
                    "[FamilyHistorian] ERROR %s for #%s: %s\n",
                    (string)$action['kind'],
                    (string)$famRes['family_code'],
                    (string)($action['error'] ?? 'unknown')
                ));
            } else {
                $skipped++;
            }
        }
    }

    echo sprintf(
        "[FamilyHistorian] Completed (%s): families=%d, published=%d, skipped=%d, errors=%d\n",
        $dryRun ? 'DRY-RUN' : 'LIVE',
        count($results),
        $published,
        $skipped,
        $errors
    );
    exit($errors > 0 ? 1 : 0);
} catch (\Throwable $e) {
    fwrite(STDERR, "[FamilyHistorian] Fatal error: " . $e->getMessage() . "\n");
    exit(1);
}

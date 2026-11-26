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

$options = getopt('', ['dry-run', 'regenerate', 'famCode::', 'date::', 'force-pillar::']);
$dryRun = array_key_exists('dry-run', $options);
$regenerate = array_key_exists('regenerate', $options);
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
    if ($regenerate) {
        $sql = "SELECT post_no, postFamCode, postMessage, post_img0, date_created FROM post WHERE post_img0 LIKE ?";
        $params = ['historian_%'];
        if ($famCode !== '') {
            $sql .= " AND postFamCode = ?";
            $params[] = strtoupper(ltrim($famCode, '#'));
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $msSvc = new \App\services\MemoryMilestoneService($db);
        $rebuilt = 0;

        foreach ($rows as $row) {
            $img = (string)($row['post_img0'] ?? '');
            $fam = (string)($row['postFamCode'] ?? 'FAMILY');
            $msg = (string)($row['postMessage'] ?? '');
            if ($img === '') {
                continue;
            }
            if (str_starts_with($img, 'historian_milestone_spotlight_')) {
                $days = 7;
                if (preg_match('/in\s+(\d+)\s+Days/i', $msg, $m)) {
                    $days = (int)$m[1];
                } elseif (stripos($msg, 'Tomorrow') !== false) {
                    $days = 1;
                } elseif (stripos($msg, 'Today') !== false) {
                    $days = 0;
                }
                $dateLabel = '';
                if (preg_match('/\(([A-Za-z]+\s+\d+,\s+\d{4})\)/', $msg, $m)) {
                    $dateLabel = $m[1];
                }
                $rawTitle = 'Family Milestone';
                if (preg_match('/(?:celebrate|honour|Tomorrow:|:\s*)([^\(!\n]+)/i', $msg, $m)) {
                    $rawTitle = trim($m[1]);
                }
                $subtitle = stripos($msg, 'Anniversary') !== false ? 'Wedding Anniversary' : 'Birthday Celebration';
                $ms = ['type' => 'event', 'title' => $rawTitle, 'subtitle' => $subtitle, 'target_date' => $dateLabel];
                $avatar = $msSvc->resolveMemberAvatar($ms, $fam);
                $fmtRef = new ReflectionMethod(\App\services\MemoryMilestoneService::class, 'formatMilestoneCardTitle');
                $fmtRef->setAccessible(true);
                $cleanTitle = (string)$fmtRef->invoke($msSvc, $ms, $fam);
                $data = [
                    'title' => $cleanTitle,
                    'subtitle' => $subtitle,
                    'days_remaining' => $days,
                    'target_date' => $dateLabel !== '' ? $dateLabel : date('M j, Y'),
                    'avatar' => $avatar,
                    'meaningful_message' => 'Another wonderful year of laughter, growth, and shared memories that enrich our family legacy.',
                ];
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('milestone_spotlight', $data, $fam, $img)) {
                    $rebuilt++;
                }
            } elseif (str_starts_with($img, 'historian_occupation_skills_')) {
                $occData = FamilyHistorianService::getOccupationSkillsData($db, $fam);
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('occupation_skills', $occData, $fam, $img)) {
                    $rebuilt++;
                }
            } elseif (str_starts_with($img, 'historian_activity_milestone_')) {
                $actData = FamilyHistorianService::getActivityMilestoneData($db, $fam, new DateTimeImmutable((string)($row['date_created'] ?? 'now')));
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('activity_milestone', $actData, $fam, $img)) {
                    $rebuilt++;
                }
            } elseif (str_starts_with($img, 'historian_monthly_birthdays_')) {
                $dt = new DateTimeImmutable((string)($row['date_created'] ?? 'now'));
                $bData = FamilyHistorianService::getMonthlyBirthdaysData($db, $fam, (int)$dt->format('n'), $dt->format('F'));
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('monthly_birthdays', $bData, $fam, $img)) {
                    $rebuilt++;
                }
            } elseif (str_starts_with($img, 'historian_family_tree_roots_')) {
                $tData = FamilyHistorianService::getFamilyTreeRootsData($db, $fam);
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('family_tree_roots', $tData, $fam, $img)) {
                    $rebuilt++;
                }
            } elseif (str_starts_with($img, 'historian_union_milestones_')) {
                $dt = new DateTimeImmutable((string)($row['date_created'] ?? 'now'));
                $uData = FamilyHistorianService::getUnionMilestonesData($db, $fam, (int)$dt->format('n'), (int)$dt->format('Y'));
                if (\App\services\FamilyHistorianCardRenderer::renderToFilename('union_milestones', $uData, $fam, $img)) {
                    $rebuilt++;
                }
            }
        }
        echo sprintf("[FamilyHistorian] Regenerated %d card image(s) in place.\n", $rebuilt);
        exit(0);
    }

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

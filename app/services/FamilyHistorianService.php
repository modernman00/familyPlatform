<?php

declare(strict_types=1);

namespace App\services;

use App\dto\PostDto;
use DateTimeImmutable;
use PDO;
use Src\Db;

/**
 * FamilyHistorianService
 *
 * "Do You Know?" Automated Timeline Posts Engine (Option 3).
 *
 * Cadence:
 *  - 1st of every month: "Do You Know? Birthdays This Month" (Day & Month only, never year/age).
 *  - Weekly rotating digest cycling through 4 pillars:
 *      1. occupation_skills  (Occupation & Skills Breakdown)
 *      2. family_tree_roots  (Family Tree & Roots)
 *      3. union_milestones   (Union & Anniversary Milestones — married/partner only)
 *      4. activity_milestone (Activity & Memory Milestone)
 *
 * Enforces smart minimum thresholds, strict per-family isolation, and transactional
 * idempotency via `family_historian_runs`.
 */
final class FamilyHistorianService
{
    public const HISTORIAN_ID = 'FAMILY_HISTORIAN';
    public const HISTORIAN_NAME = 'Family Historian';
    public const HISTORIAN_AVATAR = '/resources/images/profile/family_historian_avatar.svg';

    /** @var array<int, string> */
    public const WEEKLY_PILLARS = [
        'occupation_skills',
        'family_tree_roots',
        'union_milestones',
        'activity_milestone',
    ];

    /**
     * Ensure idempotency tracking table exists.
     */
    public static function ensureTablesExist(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS family_historian_runs (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                family_code VARCHAR(64) NOT NULL,
                post_kind ENUM('monthly_birthdays', 'weekly_digest') NOT NULL,
                pillar_key VARCHAR(64) NOT NULL,
                period_key VARCHAR(32) NOT NULL,
                post_no INT NULL,
                image_filename VARCHAR(255) NULL,
                payload_hash CHAR(64) NULL,
                created_at DATETIME NOT NULL,
                UNIQUE KEY uniq_fam_kind_period (family_code, post_kind, period_key),
                INDEX idx_fh_fam_created (family_code, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function normaliseFamilyCode(string $famCode): string
    {
        return strtoupper(ltrim(trim($famCode), '#'));
    }

    /**
     * Normalise mixed month representations ('11', '06', 'Nov', 'AUGUST', 'September') to 1..12.
     */
    public static function normaliseMonthNumber(mixed $raw): ?int
    {
        if (is_int($raw)) {
            return ($raw >= 1 && $raw <= 12) ? $raw : null;
        }
        $str = strtolower(trim((string)$raw));
        if ($str === '') {
            return null;
        }
        if (ctype_digit($str)) {
            $num = (int)$str;
            return ($num >= 1 && $num <= 12) ? $num : null;
        }

        $map = [
            'jan' => 1, 'january' => 1,
            'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3,
            'apr' => 4, 'april' => 4,
            'may' => 5,
            'jun' => 6, 'june' => 6,
            'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8,
            'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10,
            'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        ];

        return $map[$str] ?? null;
    }

    /**
     * Filter placeholder/junk occupations and normalise common titles.
     */
    public static function normaliseOccupation(string $raw): ?string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($raw)) ?? '');
        if ($clean === '' || mb_strlen($clean) < 2) {
            return null;
        }

        $lower = strtolower($clean);
        $ignored = [
            'not specified', 'unspecified', 'none', 'n/a', 'na', 'unknown', 'null',
            'no job', 'nil', 'nothing', '-', '--', '---', 'other', 'prefer not to say',
            'sdsd', 'test', 'asdf', 'none specified',
        ];
        if (in_array($lower, $ignored, true)) {
            return null;
        }

        // Must contain at least two letters
        if (!preg_match('/[a-zA-Z]{2,}/', $clean)) {
            return null;
        }

        $synonyms = [
            'account' => 'Accountant',
            'accountant' => 'Accountant',
            'chartered accountant' => 'Accountant',
            'accounting' => 'Accounting & Finance',
            'finance' => 'Accounting & Finance',
            'software engineer' => 'Software & Tech',
            'software developer' => 'Software & Tech',
            'web developer' => 'Software & Tech',
            'developer' => 'Software & Tech',
            'programmer' => 'Software & Tech',
            'tech' => 'Software & Tech',
            'it' => 'Software & Tech',
            'doctor' => 'Doctor',
            'physician' => 'Doctor',
            'gp' => 'Doctor',
            'medical doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'registered nurse' => 'Nurse',
            'teacher' => 'Teacher & Educator',
            'educator' => 'Teacher & Educator',
            'lecturer' => 'Teacher & Educator',
            'carer' => 'Care & Support',
            'caregiver' => 'Care & Support',
            'care worker' => 'Care & Support',
            'business man' => 'Business & Entrepreneurship',
            'businessman' => 'Business & Entrepreneurship',
            'business woman' => 'Business & Entrepreneurship',
            'businesswoman' => 'Business & Entrepreneurship',
            'entrepreneur' => 'Business & Entrepreneurship',
            'business owner' => 'Business & Entrepreneurship',
            'supply chain professional' => 'Supply Chain & Logistics',
            'student' => 'Student',
            'retired' => 'Retired',
            'homemaker' => 'Homemaker',
            'housewife' => 'Homemaker',
        ];

        if (isset($synonyms[$lower])) {
            return $synonyms[$lower];
        }

        return mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Normalise country names for Family Tree & Roots.
     */
    public static function normaliseCountry(string $raw): ?string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($raw)) ?? '');
        if ($clean === '' || mb_strlen($clean) < 2) {
            return null;
        }
        $lower = strtolower($clean);
        $ignored = ['not specified', 'unspecified', 'none', 'n/a', 'na', 'unknown', 'null', '-', '--'];
        if (in_array($lower, $ignored, true)) {
            return null;
        }

        $synonyms = [
            'uk' => 'United Kingdom',
            'u.k.' => 'United Kingdom',
            'gb' => 'United Kingdom',
            'great britain' => 'United Kingdom',
            'united kingdom' => 'United Kingdom',
            'england' => 'United Kingdom',
            'scotland' => 'United Kingdom',
            'wales' => 'United Kingdom',
            'northern ireland' => 'United Kingdom',
            'us' => 'United States',
            'usa' => 'United States',
            'u.s.a.' => 'United States',
            'united states' => 'United States',
            'united states of america' => 'United States',
        ];

        if (isset($synonyms[$lower])) {
            return $synonyms[$lower];
        }

        return mb_convert_case($clean, MB_CASE_TITLE, 'UTF-8');
    }

    private static function monthName(int $monthNumber): string
    {
        $dt = DateTimeImmutable::createFromFormat('!m', (string)max(1, min(12, $monthNumber)));
        return $dt !== false ? $dt->format('F') : date('F');
    }

    /**
     * Collect registered family members' birthdays for a given month (Day & Month ONLY).
     *
     * @return array<string, mixed>
     */
    public static function getMonthlyBirthdaysData(PDO $db, string $famCode, int $monthNumber): array
    {
        $normCode = self::normaliseFamilyCode($famCode);
        $hashCode = '#' . $normCode;
        $monthName = self::monthName($monthNumber);

        $stmt = $db->prepare(
            "SELECT DISTINCT p.id, p.firstName, p.lastName, p.day, p.month,
                (SELECT pp.img FROM profilePics pp WHERE pp.id = p.id AND pp.date_deleted IS NULL ORDER BY pp.no DESC LIMIT 1) AS avatar
             FROM personal p
             LEFT JOIN account a ON a.id = p.id
             WHERE p.deleted_at IS NULL
               AND (a.id IS NULL OR a.deleted_at IS NULL)
               AND p.id NOT IN ('FAMILY_PLATFORM_OFFICIAL', 'FAMILY_HISTORIAN')
               AND (
                   UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                   OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                   OR EXISTS (
                       SELECT 1 FROM user_families uf
                       WHERE uf.user_id = p.id
                         AND uf.status = 'approved'
                         AND UPPER(REPLACE(uf.family_code, '#', '')) = ?
                   )
               )
               AND p.day IS NOT NULL AND p.day BETWEEN 1 AND 31
               AND p.month IS NOT NULL AND p.month != ''"
        );
        $stmt->execute([$normCode, $normCode, $normCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $members = [];
        foreach ($rows as $r) {
            $mNum = self::normaliseMonthNumber($r['month'] ?? null);
            if ($mNum !== $monthNumber) {
                continue;
            }
            $day = (int)($r['day'] ?? 0);
            if ($day < 1 || $day > 31) {
                continue;
            }
            $first = trim((string)($r['firstName'] ?? ''));
            $last = trim((string)($r['lastName'] ?? ''));
            $fullName = trim($first . ' ' . $last);
            if ($fullName === '') {
                $fullName = 'Family Member';
            }
            $members[] = [
                'user_id' => (string)$r['id'],
                'first_name' => $first,
                'last_name' => $last,
                'full_name' => $fullName,
                'day' => $day,
                'month_name' => $monthName,
                'day_month_label' => $day . ' ' . $monthName,
                'avatar' => (string)($r['avatar'] ?? ''),
            ];
        }

        usort($members, static function (array $a, array $b): int {
            $cmp = $a['day'] <=> $b['day'];
            return $cmp !== 0 ? $cmp : strcmp((string)$a['full_name'], (string)$b['full_name']);
        });

        return [
            'eligible' => count($members) >= 1,
            'month_number' => $monthNumber,
            'month_name' => $monthName,
            'count' => count($members),
            'members' => $members,
            'fam_code_hash' => $hashCode,
        ];
    }

    /**
     * Pillar 1: Occupation & Skills Breakdown (aggregate counts, deduplicated per member).
     *
     * @return array<string, mixed>
     */
    public static function getOccupationSkillsData(PDO $db, string $famCode): array
    {
        $normCode = self::normaliseFamilyCode($famCode);

        $stmt = $db->prepare(
            "SELECT DISTINCT p.id,
                    NULLIF(TRIM(COALESCE(w.occupation, '')), '') AS work_occ,
                    NULLIF(TRIM(COALESCE(c.occupation, '')), '') AS contact_occ
             FROM personal p
             LEFT JOIN account a ON a.id = p.id
             LEFT JOIN work w ON w.id = p.id AND w.deleted_at IS NULL
             LEFT JOIN contact c ON c.id = p.id AND c.deleted_at IS NULL
             WHERE p.deleted_at IS NULL
               AND (a.id IS NULL OR a.deleted_at IS NULL)
               AND p.id NOT IN ('FAMILY_PLATFORM_OFFICIAL', 'FAMILY_HISTORIAN')
               AND (
                   UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                   OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                   OR EXISTS (
                       SELECT 1 FROM user_families uf
                       WHERE uf.user_id = p.id
                         AND uf.status = 'approved'
                         AND UPPER(REPLACE(uf.family_code, '#', '')) = ?
                   )
               )"
        );
        $stmt->execute([$normCode, $normCode, $normCode]);
        $memberRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $memberOccById = [];
        foreach ($memberRows as $r) {
            $uid = (string)$r['id'];
            $occ = self::normaliseOccupation((string)($r['work_occ'] ?? ''))
                ?? self::normaliseOccupation((string)($r['contact_occ'] ?? ''));
            if ($occ !== null) {
                $memberOccById[$uid] = $occ;
            }
        }

        // Also include family_nodes occupations for tree members not already counted via user_id
        $nodeStmt = $db->prepare(
            "SELECT id, user_id, occupation
             FROM family_nodes
             WHERE UPPER(REPLACE(COALESCE(family_code, ''), '#', '')) = ?
               AND is_erased = 0
               AND occupation IS NOT NULL AND occupation != ''"
        );
        $nodeStmt->execute([$normCode]);
        foreach ($nodeStmt->fetchAll(PDO::FETCH_ASSOC) as $nr) {
            $linkedUid = trim((string)($nr['user_id'] ?? ''));
            if ($linkedUid !== '' && isset($memberOccById[$linkedUid])) {
                continue;
            }
            $occ = self::normaliseOccupation((string)($nr['occupation'] ?? ''));
            if ($occ !== null) {
                $key = $linkedUid !== '' ? $linkedUid : ('node_' . (string)$nr['id']);
                $memberOccById[$key] = $occ;
            }
        }

        $counts = [];
        foreach ($memberOccById as $occ) {
            $counts[$occ] = ($counts[$occ] ?? 0) + 1;
        }
        arsort($counts);

        $total = count($memberOccById);
        $items = [];
        foreach ($counts as $label => $cnt) {
            $items[] = [
                'label' => $label,
                'count' => $cnt,
                'pct' => $total > 0 ? (int)round(($cnt / $total) * 100) : 0,
            ];
        }

        return [
            'eligible' => $total >= 2,
            'total_members' => $total,
            'distinct_count' => count($counts),
            'items' => array_slice($items, 0, 6),
        ];
    }

    /**
     * Pillar 2: Family Tree & Roots (generation depth, total branches, countries represented).
     *
     * @return array<string, mixed>
     */
    public static function getFamilyTreeRootsData(PDO $db, string $famCode): array
    {
        $normCode = self::normaliseFamilyCode($famCode);

        $stmt = $db->prepare(
            "SELECT COUNT(*) AS total_branches,
                    COUNT(DISTINCT generation_level) AS distinct_gens,
                    MIN(generation_level) AS min_gen,
                    MAX(generation_level) AS max_gen
             FROM family_nodes
             WHERE UPPER(REPLACE(COALESCE(family_code, ''), '#', '')) = ?
               AND is_erased = 0"
        );
        $stmt->execute([$normCode]);
        /** @var array<string, mixed>|false $stats */
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalBranches = (int)($stats['total_branches'] ?? 0);
        $distinctGens = (int)($stats['distinct_gens'] ?? 0);
        $spanGens = ($stats !== false && $stats['min_gen'] !== null && $stats['max_gen'] !== null)
            ? ((int)$stats['max_gen'] - (int)$stats['min_gen'] + 1)
            : 0;
        $generationDepth = max($distinctGens, $spanGens, $totalBranches > 0 ? 1 : 0);

        // Countries represented from contact.country of registered members
        $countryStmt = $db->prepare(
            "SELECT DISTINCT c.country
             FROM contact c
             INNER JOIN personal p ON p.id = c.id
             WHERE p.deleted_at IS NULL
               AND c.deleted_at IS NULL
               AND c.country IS NOT NULL AND c.country != ''
               AND (
                   UPPER(REPLACE(COALESCE(p.famCode, ''), '#', '')) = ?
                   OR UPPER(REPLACE(COALESCE(p.familyCode2, ''), '#', '')) = ?
                   OR EXISTS (
                       SELECT 1 FROM user_families uf
                       WHERE uf.user_id = p.id
                         AND uf.status = 'approved'
                         AND UPPER(REPLACE(uf.family_code, '#', '')) = ?
                   )
               )"
        );
        $countryStmt->execute([$normCode, $normCode, $normCode]);
        $countries = [];
        foreach ($countryStmt->fetchAll(PDO::FETCH_COLUMN) as $rawCountry) {
            $norm = self::normaliseCountry((string)$rawCountry);
            if ($norm !== null && !in_array($norm, $countries, true)) {
                $countries[] = $norm;
            }
        }
        sort($countries);

        return [
            'eligible' => ($totalBranches >= 3) || ($totalBranches >= 2 && $generationDepth >= 2),
            'total_branches' => $totalBranches,
            'generation_depth' => $generationDepth,
            'countries' => $countries,
        ];
    }

    /**
     * Pillar 3: Union & Anniversary Milestones (strictly married/partner current unions).
     * NEVER exposes single, divorced, separated, widowed, or annulled statuses.
     *
     * @return array<string, mixed>
     */
    public static function getUnionMilestonesData(PDO $db, string $famCode, int $monthNumber, ?int $currentYear = null): array
    {
        $normCode = self::normaliseFamilyCode($famCode);
        $yearNow = $currentYear ?? (int)date('Y');
        $monthName = self::monthName($monthNumber);

        $stmt = $db->prepare(
            "SELECT u.id, u.marriage_date, u.marriage_year,
                    p1.first_name AS p1_first, p1.last_name AS p1_last,
                    p2.first_name AS p2_first, p2.last_name AS p2_last
             FROM family_unions u
             INNER JOIN family_nodes p1 ON p1.id = u.partner_1_id AND p1.is_erased = 0
             INNER JOIN family_nodes p2 ON p2.id = u.partner_2_id AND p2.is_erased = 0
             WHERE UPPER(REPLACE(u.family_code, '#', '')) = ?
               AND u.union_type IN ('married', 'partner')
               AND u.is_current = 1
             ORDER BY u.id ASC"
        );
        $stmt->execute([$normCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unions = [];
        foreach ($rows as $r) {
            $name1 = trim((string)($r['p1_first'] ?? '') . ' ' . (string)($r['p1_last'] ?? ''));
            $name2 = trim((string)($r['p2_first'] ?? '') . ' ' . (string)($r['p2_last'] ?? ''));
            if ($name1 === '' || $name2 === '') {
                continue;
            }

            $mDate = trim((string)($r['marriage_date'] ?? ''));
            $mYear = !empty($r['marriage_year']) ? (int)$r['marriage_year'] : null;
            if ($mYear === null && $mDate !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $mDate, $dm)) {
                $mYear = (int)$dm[1];
            }

            $isAnniversaryMonth = false;
            if ($mDate !== '' && preg_match('/^\d{4}-(\d{2})-(\d{2})/', $mDate, $dm2)) {
                $isAnniversaryMonth = ((int)$dm2[1] === $monthNumber);
            }

            $years = ($mYear !== null && $mYear > 1900 && $mYear <= $yearNow) ? ($yearNow - $mYear) : null;
            if ($isAnniversaryMonth && $years !== null && $years > 0) {
                $milestone = "{$years} yrs • {$monthName} Anniversary";
            } elseif ($isAnniversaryMonth) {
                $milestone = "{$monthName} Anniversary";
            } elseif ($years !== null && $years > 0) {
                $milestone = "{$years} " . ($years === 1 ? 'Year' : 'Years') . ' Together';
            } else {
                $milestone = 'Cherished Union';
            }

            $unions[] = [
                'couple_label' => "{$name1} & {$name2}",
                'milestone_label' => $milestone,
                'is_anniversary_month' => $isAnniversaryMonth,
                'years' => $years ?? 0,
            ];
        }

        usort($unions, static function (array $a, array $b): int {
            $annivCmp = ($b['is_anniversary_month'] ? 1 : 0) <=> ($a['is_anniversary_month'] ? 1 : 0);
            if ($annivCmp !== 0) {
                return $annivCmp;
            }
            return ($b['years'] <=> $a['years']);
        });

        return [
            'eligible' => count($unions) >= 1,
            'total_unions' => count($unions),
            'unions' => array_slice($unions, 0, 4),
        ];
    }

    /**
     * Pillar 4: Activity & Memory Milestone (most-loved recent post & peak posting day).
     *
     * @return array<string, mixed>
     */
    public static function getActivityMilestoneData(PDO $db, string $famCode, DateTimeImmutable $now): array
    {
        $normCode = self::normaliseFamilyCode($famCode);
        $since = $now->modify('-60 days')->format('Y-m-d H:i:s');

        $stmt = $db->prepare(
            "SELECT p.post_no, p.fullName, p.postMessage, p.post_likes, p.date_created,
                    (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_no = p.post_no) AS reaction_count
             FROM post p
             WHERE p.post_status = 'published'
               AND p.date_deleted IS NULL
               AND p.id NOT IN ('FAMILY_PLATFORM_OFFICIAL', 'FAMILY_HISTORIAN')
               AND p.date_created >= ?
               AND (
                   UPPER(REPLACE(COALESCE(p.postFamCode, ''), '#', '')) = ?
                   OR EXISTS (
                       SELECT 1 FROM post_family_targets pft
                       WHERE pft.post_no = p.post_no
                         AND UPPER(REPLACE(pft.family_code, '#', '')) = ?
                   )
               )
             ORDER BY (COALESCE(p.post_likes, 0) + (SELECT COUNT(*) FROM post_reactions pr2 WHERE pr2.post_no = p.post_no)) DESC,
                      p.date_created DESC
             LIMIT 100"
        );
        $stmt->execute([$since, $normCode, $normCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPosts = count($rows);
        if ($totalPosts === 0) {
            return ['eligible' => false, 'total_posts' => 0];
        }

        $dayCounts = [];
        foreach ($rows as $r) {
            $ts = strtotime((string)($r['date_created'] ?? ''));
            if ($ts !== false) {
                $dayName = date('l', $ts);
                $dayCounts[$dayName] = ($dayCounts[$dayName] ?? 0) + 1;
            }
        }
        arsort($dayCounts);
        $peakDay = (string)(array_key_first($dayCounts) ?? 'Sunday');

        $top = $rows[0];
        $topScore = (int)($top['post_likes'] ?? 0) + (int)($top['reaction_count'] ?? 0);
        $rawMsg = PostDto::stripStyleToken((string)($top['postMessage'] ?? ''));
        $rawMsg = (string)preg_replace('#https?://[^\s]+#i', '', $rawMsg);
        $rawMsg = (string)preg_replace('#/resources/(?:videos|images|audio)/[^\s]+#i', '', $rawMsg);
        $rawMsg = trim((string)preg_replace('/\s+/', ' ', strip_tags($rawMsg)));
        if ($rawMsg === '') {
            $rawMsg = 'A cherished family moment shared on our timeline.';
        }

        return [
            'eligible' => ($totalPosts >= 3) || ($topScore >= 1),
            'total_posts' => $totalPosts,
            'peak_day' => $peakDay,
            'top_post_no' => (int)$top['post_no'],
            'top_author' => trim((string)($top['fullName'] ?? 'Family Member')) ?: 'Family Member',
            'top_score' => $topScore,
            'top_excerpt' => $rawMsg,
        ];
    }

    /**
     * Run the "Do You Know?" engine for a single family with strict idempotency.
     *
     * @return array<string, mixed>
     */
    public static function runForFamily(
        string $famCode,
        ?PDO $dbOverride = null,
        ?DateTimeImmutable $nowOverride = null,
        bool $dryRun = false,
        ?string $forcePillar = null
    ): array {
        $db = $dbOverride ?? Db::connect2();
        self::ensureTablesExist($db);

        $normCode = self::normaliseFamilyCode($famCode);
        if ($normCode === '') {
            return ['family_code' => $famCode, 'actions' => []];
        }

        $now = $nowOverride ?? new DateTimeImmutable('now');
        $actions = [];

        // 1. Monthly Birthdays on the 1st of the month (or when forced)
        if ($now->format('j') === '1' || $forcePillar === 'monthly_birthdays') {
            $monthPeriod = $now->format('Y-m');
            $monthNum = (int)$now->format('n');
            $actions[] = self::processMonthlyBirthdays($db, $normCode, $monthPeriod, $monthNum, $dryRun);
        }

        // 2. Weekly Rotating Digest (unless specifically forcing monthly_birthdays)
        if ($forcePillar !== 'monthly_birthdays') {
            $weekPeriod = $now->format('o-\WW');
            $actions[] = self::processWeeklyDigest($db, $normCode, $weekPeriod, $now, $dryRun, $forcePillar);
        }

        return [
            'family_code' => $normCode,
            'actions' => $actions,
        ];
    }

    /**
     * Run across all active families in `family_codes`.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function runForAllFamilies(
        ?PDO $dbOverride = null,
        ?DateTimeImmutable $nowOverride = null,
        bool $dryRun = false
    ): array {
        $db = $dbOverride ?? Db::connect2();
        self::ensureTablesExist($db);

        $stmt = $db->query("SELECT DISTINCT code FROM family_codes WHERE code IS NOT NULL AND code != '' ORDER BY code ASC");
        $codes = $stmt !== false ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];

        $results = [];
        foreach ($codes as $code) {
            $results[] = self::runForFamily((string)$code, $db, $nowOverride, $dryRun);
        }

        return $results;
    }

    /**
     * Opportunistic feed hook: ensures the current week's "Do You Know?" fun-fact card
     * (and 1st-of-month birthday card) has been evaluated/published for a family without
     * requiring an external crontab daemon. Uses a fast temp-file stamp per family + ISO week.
     *
     * @return array<string, mixed>|null
     */
    public static function ensureWeeklyDigestForFamily(string $famCode, ?PDO $dbOverride = null): ?array
    {
        $normCode = self::normaliseFamilyCode($famCode);
        if ($normCode === '' || str_starts_with($normCode, 'PHPUNIT') || str_starts_with($normCode, 'TEST_')) {
            return null;
        }

        try {
            $now = new DateTimeImmutable('now');
            $periodStamp = $now->format('o-\WW') . ($now->format('j') === '1' ? '_m1' : '');
            $safeCode = preg_replace('/[^A-Z0-9_]/', '', $normCode) ?: 'FAM';
            $stampFile = sys_get_temp_dir() . '/fp_historian_' . $safeCode . '_' . $periodStamp . '.stamp';
            if (is_file($stampFile)) {
                return null;
            }
            @touch($stampFile);

            return self::runForFamily($normCode, $dbOverride, $now, false);
        } catch (\Throwable $e) {
            error_log('[FamilyHistorianService::ensureWeeklyDigestForFamily] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function processMonthlyBirthdays(
        PDO $db,
        string $normCode,
        string $periodKey,
        int $monthNum,
        bool $dryRun
    ): array {
        if (self::hasRun($db, $normCode, 'monthly_birthdays', $periodKey)) {
            return ['kind' => 'monthly_birthdays', 'pillar' => 'monthly_birthdays', 'status' => 'skipped_already_posted'];
        }

        $data = self::getMonthlyBirthdaysData($db, $normCode, $monthNum);
        if (empty($data['eligible'])) {
            return ['kind' => 'monthly_birthdays', 'pillar' => 'monthly_birthdays', 'status' => 'skipped_below_threshold'];
        }

        $count = (int)$data['count'];
        $monthName = (string)$data['month_name'];
        $names = array_map(static fn(array $m): string => $m['full_name'] . ' (' . $m['day_month_label'] . ')', array_slice($data['members'], 0, 5));
        $extra = $count > 5 ? ' and ' . ($count - 5) . ' more' : '';
        $message = "🎂 Do You Know? Birthdays This Month ({$monthName})\n\n"
            . "We are celebrating {$count} " . ($count === 1 ? 'birthday' : 'birthdays') . " in {$monthName}: "
            . implode(', ', $names) . $extra . ".\n\n"
            . "Leave a warm wish in the comments below!";

        if ($dryRun) {
            return ['kind' => 'monthly_birthdays', 'pillar' => 'monthly_birthdays', 'status' => 'dry_run_eligible', 'count' => $count];
        }

        return self::publishPost($db, $normCode, 'monthly_birthdays', 'monthly_birthdays', $periodKey, $data, $message);
    }

    /**
     * @return array<string, mixed>
     */
    private static function processWeeklyDigest(
        PDO $db,
        string $normCode,
        string $periodKey,
        DateTimeImmutable $now,
        bool $dryRun,
        ?string $forcePillar
    ): array {
        if (self::hasRun($db, $normCode, 'weekly_digest', $periodKey)) {
            return ['kind' => 'weekly_digest', 'pillar' => 'none', 'status' => 'skipped_already_posted'];
        }

        $pillars = self::WEEKLY_PILLARS;
        $orderedPillars = [];
        if ($forcePillar !== null && in_array($forcePillar, $pillars, true)) {
            $orderedPillars = [$forcePillar];
        } else {
            $startIdx = max(0, ((int)$now->format('W') - 1) % count($pillars));
            for ($i = 0; $i < count($pillars); $i++) {
                $orderedPillars[] = $pillars[($startIdx + $i) % count($pillars)];
            }
        }

        foreach ($orderedPillars as $pillar) {
            $data = match ($pillar) {
                'occupation_skills' => self::getOccupationSkillsData($db, $normCode),
                'family_tree_roots' => self::getFamilyTreeRootsData($db, $normCode),
                'union_milestones' => self::getUnionMilestonesData($db, $normCode, (int)$now->format('n'), (int)$now->format('Y')),
                'activity_milestone' => self::getActivityMilestoneData($db, $normCode, $now),
            };

            if (empty($data['eligible'])) {
                continue;
            }

            $message = self::buildWeeklyMessage($pillar, $data);
            if ($dryRun) {
                return ['kind' => 'weekly_digest', 'pillar' => $pillar, 'status' => 'dry_run_eligible'];
            }

            return self::publishPost($db, $normCode, 'weekly_digest', $pillar, $periodKey, $data, $message);
        }

        return ['kind' => 'weekly_digest', 'pillar' => 'none', 'status' => 'skipped_below_threshold'];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function buildWeeklyMessage(string $pillar, array $data): string
    {
        return match ($pillar) {
            'occupation_skills' => sprintf(
                "💼 Do You Know? Occupations & Skills\n\n%d family members are represented across %d professions & skills. Celebrating the talents that shape our family!",
                (int)($data['total_members'] ?? 0),
                (int)($data['distinct_count'] ?? 0)
            ),
            'family_tree_roots' => sprintf(
                "🌳 Do You Know? Family Tree & Roots\n\nOur living family tree spans %d %s and %d %s%s. Explore your heritage in the Family Tree!",
                (int)($data['generation_depth'] ?? 1),
                ((int)($data['generation_depth'] ?? 1) === 1 ? 'generation' : 'generations'),
                (int)($data['total_branches'] ?? 0),
                ((int)($data['total_branches'] ?? 0) === 1 ? 'branch' : 'branches'),
                !empty($data['countries']) ? (' across ' . count((array)$data['countries']) . ' ' . (count((array)$data['countries']) === 1 ? 'country' : 'countries')) : ''
            ),
            'union_milestones' => sprintf(
                "💍 Do You Know? Unions & Anniversaries\n\nHonouring %d cherished %s in our family tree. Share your favourite memories and warm wishes below!",
                (int)($data['total_unions'] ?? 0),
                ((int)($data['total_unions'] ?? 0) === 1 ? 'marriage & partnership' : 'marriages & partnerships')
            ),
            default => sprintf(
                "✨ Do You Know? Memory Spotlight\n\nOur family shared %d stories recently, with %s as our peak day! Favourite recent moment from %s (%d reactions).",
                (int)($data['total_posts'] ?? 0),
                (string)($data['peak_day'] ?? 'Sunday'),
                (string)($data['top_author'] ?? 'a family member'),
                (int)($data['top_score'] ?? 0)
            ),
        };
    }

    private static function hasRun(PDO $db, string $normCode, string $postKind, string $periodKey): bool
    {
        $stmt = $db->prepare(
            "SELECT 1 FROM family_historian_runs WHERE family_code = ? AND post_kind = ? AND period_key = ? LIMIT 1"
        );
        $stmt->execute([$normCode, $postKind, $periodKey]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function publishPost(
        PDO $db,
        string $normCode,
        string $postKind,
        string $pillar,
        string $periodKey,
        array $data,
        string $message
    ): array {
        $imageFilename = FamilyHistorianCardRenderer::renderAndSave($pillar, $data, $normCode, $periodKey);
        $payloadHash = hash('sha256', (string)json_encode($data));

        try {
            $db->beginTransaction();

            $chk = $db->prepare(
                "SELECT id FROM family_historian_runs WHERE family_code = ? AND post_kind = ? AND period_key = ? LIMIT 1 FOR UPDATE"
            );
            $chk->execute([$normCode, $postKind, $periodKey]);
            if ($chk->fetchColumn() !== false) {
                $db->rollBack();
                return ['kind' => $postKind, 'pillar' => $pillar, 'status' => 'skipped_already_posted'];
            }

            $postTimeMs = (string)(int)(microtime(true) * 1000);
            $stmtPost = $db->prepare(
                "INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, target_scope, post_time, post_status, post_img0, date_created)
                 VALUES (?, ?, ?, ?, ?, 'all', ?, 'published', ?, NOW())"
            );
            $stmtPost->execute([
                self::HISTORIAN_ID,
                self::HISTORIAN_NAME,
                self::HISTORIAN_AVATAR,
                $message,
                $normCode,
                $postTimeMs,
                $imageFilename ?? '',
            ]);
            $postNo = (int)$db->lastInsertId();

            $stmtRun = $db->prepare(
                "INSERT INTO family_historian_runs (family_code, post_kind, pillar_key, period_key, post_no, image_filename, payload_hash, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmtRun->execute([
                $normCode,
                $postKind,
                $pillar,
                $periodKey,
                $postNo,
                $imageFilename,
                $payloadHash,
            ]);

            $db->commit();

            return [
                'kind' => $postKind,
                'pillar' => $pillar,
                'status' => 'published',
                'post_no' => $postNo,
                'image_filename' => $imageFilename,
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[FamilyHistorianService::publishPost] ' . $e->getMessage());
            return [
                'kind' => $postKind,
                'pillar' => $pillar,
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }
}

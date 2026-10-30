<?php
declare(strict_types=1);

namespace App\model;

use PDO;
use Src\Db;

/**
 * "Then & Now" before/after pairs attached to a normal post (RFC-0002).
 * Image names are always read back from the post row, never from the client.
 */
final class PostComparison extends Db
{
    public const MAX_LABEL = 40;
    private static bool $columnsVerified = false;

    /**
     * Idempotent zero-lock check ensuring Living Then & Now 2.0 columns exist.
     */
    public static function ensureColumns(): void
    {
        if (self::$columnsVerified) {
            return;
        }
        self::$columnsVerified = true;
        try {
            $pdo = self::connect2();
            $cols = $pdo->query('SHOW COLUMNS FROM post_comparisons')->fetchAll(PDO::FETCH_COLUMN);
            if (is_array($cols)) {
                if (!in_array('comparison_mode', $cols, true)) {
                    $pdo->exec("ALTER TABLE post_comparisons ADD COLUMN comparison_mode VARCHAR(24) NOT NULL DEFAULT 'classic'");
                }
                if (!in_array('mystery_year', $cols, true)) {
                    $pdo->exec('ALTER TABLE post_comparisons ADD COLUMN mystery_year TINYINT(1) NOT NULL DEFAULT 0');
                }
            }
        } catch (\Throwable $e) {
            error_log('[PostComparison::ensureColumns] ' . $e->getMessage());
        }
    }

    public static function cleanLabel(mixed $raw): ?string
    {
        $label = trim(strip_tags((string)$raw));
        $label = mb_substr($label, 0, self::MAX_LABEL);
        return $label === '' ? null : $label;
    }

    public static function cleanMode(mixed $raw): string
    {
        $mode = strtolower(trim((string)$raw));
        return in_array($mode, ['classic', 'echo'], true) ? $mode : 'classic';
    }

    /**
     * Extract 4-digit years (1900..2100) from before/after labels and compute the absolute delta.
     */
    public static function computeYearsDelta(?string $beforeLabel, ?string $afterLabel): ?int
    {
        if ($beforeLabel === null || $afterLabel === null) {
            return null;
        }
        if (!preg_match('/\b(19\d{2}|20\d{2})\b/', $beforeLabel, $m1)) {
            return null;
        }
        if (!preg_match('/\b(19\d{2}|20\d{2})\b/', $afterLabel, $m2)) {
            return null;
        }
        $y1 = (int)$m1[1];
        $y2 = (int)$m2[1];
        return abs($y2 - $y1);
    }

    public static function isComparison(int $postNo): bool
    {
        $stmt = self::connect2()->prepare('SELECT 1 FROM post_comparisons WHERE post_no = ? LIMIT 1');
        $stmt->execute([$postNo]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Link the first two stored photos of $postNo (Then, then Now) as a comparison.
     * The upload pipeline fills post_img1..N in upload order, so read columns in order
     * and skip empty ones. Returns false when the post does not carry two stored images.
     */
    public static function createForPost(
        int $postNo,
        ?string $beforeLabel,
        ?string $afterLabel,
        ?int $parentPostNo = null,
        string $comparisonMode = 'classic',
        bool $mysteryYear = false
    ): bool {
        self::ensureColumns();
        $post = Post::postByNo($postNo);
        $stored = [];
        for ($i = 0; $i < 6; $i++) {
            $name = trim((string)($post['post_img' . $i] ?? ''));
            if ($name !== '') {
                $stored[] = $name;
            }
        }
        if (count($stored) < 2) {
            return false;
        }
        [$before, $after] = $stored;

        // Ensure Next-Gen Visual Supremacy Engine (AVIF/WebP/Display P3) variants exist for comparison pair
        try {
            $postImgDir = realpath(__DIR__ . '/../../resources/images/post');
            if ($postImgDir && is_dir($postImgDir) && class_exists(\App\services\VisualSupremacyService::class)) {
                foreach ([$before, $after] as $imgFile) {
                    $base = basename($imgFile);
                    $fullPath = rtrim($postImgDir, '/\\') . DIRECTORY_SEPARATOR . $base;
                    $stem = pathinfo($fullPath, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($fullPath, PATHINFO_FILENAME);
                    if (is_file($fullPath) && (!is_file($stem . '.avif') || !is_file($stem . '.webp'))) {
                        \App\services\VisualSupremacyService::processInPlace($fullPath);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[PostComparison::createForPost VSE] ' . $e->getMessage());
        }

        if ($parentPostNo !== null && !self::isComparison($parentPostNo)) {
            $parentPostNo = null;
        }

        $cleanMode = self::cleanMode($comparisonMode);
        $mysteryFlag = $mysteryYear ? 1 : 0;

        $stmt = self::connect2()->prepare(
            'INSERT INTO post_comparisons (post_no, before_img, after_img, before_label, after_label, parent_post_no, comparison_mode, mystery_year)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([$postNo, $before, $after, $beforeLabel, $afterLabel, $parentPostNo, $cleanMode, $mysteryFlag]);
    }

    /**
     * Batch fetch (one query, no N+1) keyed by post_no, with answer counts and chain facepile previews.
     *
     * @param array<int> $postNos
     * @return array<int, array<string, mixed>>
     */
    public static function getForPosts(array $postNos): array
    {
        if ($postNos === []) {
            return [];
        }
        self::ensureColumns();
        $cleanNos = array_values(array_map('intval', $postNos));
        $in = implode(',', array_fill(0, count($cleanNos), '?'));
        $sql = sprintf(
            'SELECT c.post_no, c.before_img, c.after_img, c.before_label, c.after_label, c.parent_post_no,
                    c.comparison_mode, c.mystery_year,
                    (SELECT COUNT(*) FROM post_comparisons r
                       JOIN post rp ON rp.post_no = r.post_no AND rp.date_deleted IS NULL
                      WHERE r.parent_post_no = c.post_no) AS answer_count
               FROM post_comparisons c
              WHERE c.post_no IN (%s)',
            $in
        );
        $stmt = self::connect2()->prepare($sql);
        $stmt->execute($cleanNos);

        $out = [];
        $parentCandidates = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pNo = (int)$row['post_no'];
            $bLabel = $row['before_label'] !== null ? (string)$row['before_label'] : null;
            $aLabel = $row['after_label'] !== null ? (string)$row['after_label'] : null;
            $ansCount = (int)$row['answer_count'];
            if ($ansCount > 0) {
                $parentCandidates[] = $pNo;
            }
            $out[$pNo] = [
                'before_img' => (string)$row['before_img'],
                'after_img' => (string)$row['after_img'],
                'before_label' => $bLabel,
                'after_label' => $aLabel,
                'parent_post_no' => $row['parent_post_no'] !== null ? (int)$row['parent_post_no'] : null,
                'answer_count' => $ansCount,
                'comparison_mode' => self::cleanMode($row['comparison_mode'] ?? 'classic'),
                'mystery_year' => !empty($row['mystery_year']),
                'years_delta' => self::computeYearsDelta($bLabel, $aLabel),
                'chain_avatars' => [],
            ];
        }

        // Batch-hydrate up to 3 respondent avatars per parent post in a single query (O(1) queries)
        if ($parentCandidates !== []) {
            $parentIn = implode(',', array_fill(0, count($parentCandidates), '?'));
            $replySql = sprintf(
                'SELECT r.parent_post_no, r.post_no, rp.id AS user_id, rp.fullName, rp.profileImg
                   FROM post_comparisons r
                   JOIN post rp ON rp.post_no = r.post_no AND rp.date_deleted IS NULL AND rp.post_status = \'published\'
                  WHERE r.parent_post_no IN (%s)
                  ORDER BY r.post_no DESC',
                $parentIn
            );
            $replyStmt = self::connect2()->prepare($replySql);
            $replyStmt->execute($parentCandidates);
            foreach ($replyStmt->fetchAll(PDO::FETCH_ASSOC) as $rRow) {
                $pId = (int)$rRow['parent_post_no'];
                if (isset($out[$pId]) && count($out[$pId]['chain_avatars']) < 3) {
                    $out[$pId]['chain_avatars'][] = [
                        'post_no' => (int)$rRow['post_no'],
                        'user_id' => (string)($rRow['user_id'] ?? ''),
                        'fullName' => (string)($rRow['fullName'] ?? 'Family Member'),
                        'profileImg' => (string)($rRow['profileImg'] ?? ''),
                    ];
                }
            }
        }

        return $out;
    }

    /**
     * Fetch a complete "Family Time-Warp Chain" (root comparison + all family responses),
     * strictly gated to the viewer's family codes or own posts to prevent cross-family IDOR.
     *
     * @param array<string> $viewerFamCodes
     * @return array<int, array<string, mixed>>
     */
    public static function forChain(int $postNo, string $viewerId, array $viewerFamCodes, int $limit = 24): array
    {
        if ($postNo <= 0 || $viewerId === '') {
            return [];
        }
        self::ensureColumns();
        $codes = array_values(array_filter(array_map('strval', $viewerFamCodes), fn(string $c) => $c !== ''));
        $limit = max(1, min(48, $limit));

        // Resolve root parent_post_no if viewer clicked a child reply in the chain
        $rootStmt = self::connect2()->prepare('SELECT post_no, parent_post_no FROM post_comparisons WHERE post_no = ? LIMIT 1');
        $rootStmt->execute([$postNo]);
        $targetRow = $rootStmt->fetch(PDO::FETCH_ASSOC);
        if (!$targetRow) {
            return [];
        }
        $rootNo = !empty($targetRow['parent_post_no']) ? (int)$targetRow['parent_post_no'] : (int)$targetRow['post_no'];

        $visibilitySql = 'p.id = ?';
        $params = [$rootNo, $rootNo, $viewerId];
        if ($codes !== []) {
            $codePlaceholders = implode(',', array_fill(0, count($codes), '?'));
            $visibilitySql = sprintf('p.postFamCode IN (%s) OR p.id = ?', $codePlaceholders);
            $params = array_merge([$rootNo, $rootNo], $codes, [$viewerId]);
        }

        $sql = sprintf(
            "SELECT c.post_no, c.before_img, c.after_img, c.before_label, c.after_label,
                    c.parent_post_no, c.comparison_mode, c.mystery_year,
                    p.id AS user_id, p.fullName, p.profileImg, p.date_created, p.postMessage, p.voice_capsule_url
               FROM post_comparisons c
               JOIN post p ON p.post_no = c.post_no
              WHERE p.post_status = 'published'
                AND p.date_deleted IS NULL
                AND (c.post_no = ? OR c.parent_post_no = ?)
                AND (%s)
              ORDER BY CASE WHEN c.post_no = %d THEN 0 ELSE 1 END ASC, c.post_no ASC
              LIMIT %d",
            $visibilitySql,
            $rootNo,
            $limit
        );

        $stmt = self::connect2()->prepare($sql);
        $stmt->execute($params);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bLabel = $row['before_label'] !== null ? (string)$row['before_label'] : null;
            $aLabel = $row['after_label'] !== null ? (string)$row['after_label'] : null;
            $out[] = [
                'post_no' => (int)$row['post_no'],
                'before_img' => (string)$row['before_img'],
                'after_img' => (string)$row['after_img'],
                'before_label' => $bLabel,
                'after_label' => $aLabel,
                'parent_post_no' => $row['parent_post_no'] !== null ? (int)$row['parent_post_no'] : null,
                'comparison_mode' => self::cleanMode($row['comparison_mode'] ?? 'classic'),
                'mystery_year' => !empty($row['mystery_year']),
                'years_delta' => self::computeYearsDelta($bLabel, $aLabel),
                'user_id' => (string)($row['user_id'] ?? ''),
                'fullName' => (string)$row['fullName'],
                'profileImg' => (string)($row['profileImg'] ?? ''),
                'date_created' => (string)$row['date_created'],
                'post_desc' => (string)($row['postMessage'] ?? ''),
                'voice_capsule_url' => !empty($row['voice_capsule_url']) ? (string)$row['voice_capsule_url'] : null,
            ];
        }
        return $out;
    }

    /**
     * Fetch a member's existing family archive photos (authored or tagged) so they can pick a "Then"
     * throwback directly from the FamilyPlatform vault without hunting through local files.
     *
     * @param array<string> $viewerFamCodes
     * @return array<int, array<string, mixed>>
     */
    public static function archivePhotosForMember(string $memberId, string $viewerId, array $viewerFamCodes, int $limit = 20): array
    {
        $codes = array_values(array_filter(array_map('strval', $viewerFamCodes), fn(string $c) => $c !== ''));
        if ($memberId === '' || $viewerId === '') {
            return [];
        }
        $limit = max(1, min(40, $limit));

        $visibilitySql = 'p.id = ?';
        $params = [$memberId, $memberId, $viewerId];
        if ($codes !== []) {
            $codePlaceholders = implode(',', array_fill(0, count($codes), '?'));
            $visibilitySql = sprintf('p.postFamCode IN (%s) OR p.id = ?', $codePlaceholders);
            $params = array_merge([$memberId, $memberId], $codes, [$viewerId]);
        }

        $sql = sprintf(
            "SELECT p.post_no, p.post_img0, p.post_img1, p.post_img2, p.post_img3, p.post_img4, p.post_img5,
                    p.date_created, p.postMessage
               FROM post p
              WHERE p.post_status = 'published'
                AND p.date_deleted IS NULL
                AND (p.id = ? OR EXISTS (SELECT 1 FROM post_tags t WHERE t.post_no = p.post_no AND t.user_id = ?))
                AND (%s)
              ORDER BY p.post_no DESC
              LIMIT %d",
            $visibilitySql,
            $limit
        );

        $stmt = self::connect2()->prepare($sql);
        $stmt->execute($params);

        $photos = [];
        $seen = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dateStr = (string)($row['date_created'] ?? '');
            $year = null;
            if (preg_match('/\b(19\d{2}|20\d{2})\b/', $dateStr, $ym)) {
                $year = (int)$ym[1];
            }
            for ($i = 0; $i <= 5; $i++) {
                $img = trim((string)($row['post_img' . $i] ?? ''));
                if ($img === '' || $img === 'null' || $img === 'none' || str_contains($img, 'no_image')) {
                    continue;
                }
                if (preg_match('/\.(mp4|webm|mov|ogg)(\?|#|$)/i', $img)) {
                    continue;
                }
                if (isset($seen[$img])) {
                    continue;
                }
                $seen[$img] = true;
                $photos[] = [
                    'post_no' => (int)$row['post_no'],
                    'img' => $img,
                    'year' => $year,
                    'caption' => mb_substr(trim(strip_tags((string)($row['postMessage'] ?? ''))), 0, 80),
                ];
                if (count($photos) >= $limit) {
                    break 2;
                }
            }
        }

        return $photos;
    }

    /**
     * A member's Then & Now timeline: comparisons they posted or are tagged in,
     * limited to posts the viewer may already see (the viewer's family codes, or the
     * viewer's own posts). One query, newest first.
     *
     * @param array<string> $viewerFamCodes
     * @return array<int, array<string, mixed>>
     */
    public static function forMember(string $memberId, string $viewerId, array $viewerFamCodes, int $limit = 12): array
    {
        $codes = array_values(array_filter(array_map('strval', $viewerFamCodes), fn(string $c) => $c !== ''));
        if ($memberId === '' || $viewerId === '') {
            return [];
        }
        self::ensureColumns();
        $limit = max(1, min(24, $limit));

        $visibility = 'p.id = ?';
        $params = [$memberId, $memberId, $viewerId];
        if ($codes !== []) {
            $visibility = 'p.postFamCode IN (' . implode(',', array_fill(0, count($codes), '?')) . ') OR p.id = ?';
            $params = array_merge([$memberId, $memberId], $codes, [$viewerId]);
        }

        $sql = sprintf(
            "SELECT c.post_no, c.before_img, c.after_img, c.before_label, c.after_label,
                    c.comparison_mode, c.mystery_year,
                    p.fullName, p.date_created, p.postMessage, p.voice_capsule_url
               FROM post_comparisons c
               JOIN post p ON p.post_no = c.post_no
              WHERE p.post_status = 'published'
                AND p.date_deleted IS NULL
                AND (p.id = ? OR EXISTS (SELECT 1 FROM post_tags t WHERE t.post_no = c.post_no AND t.user_id = ?))
                AND (%s)
              ORDER BY c.post_no DESC
              LIMIT %d",
            $visibility,
            $limit
        );
        $stmt = self::connect2()->prepare($sql);
        $stmt->execute($params);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bLabel = $row['before_label'] !== null ? (string)$row['before_label'] : null;
            $aLabel = $row['after_label'] !== null ? (string)$row['after_label'] : null;
            $out[] = [
                'post_no' => (int)$row['post_no'],
                'before_img' => (string)$row['before_img'],
                'after_img' => (string)$row['after_img'],
                'before_label' => $bLabel,
                'after_label' => $aLabel,
                'comparison_mode' => self::cleanMode($row['comparison_mode'] ?? 'classic'),
                'mystery_year' => !empty($row['mystery_year']),
                'years_delta' => self::computeYearsDelta($bLabel, $aLabel),
                'fullName' => (string)$row['fullName'],
                'date_created' => (string)$row['date_created'],
                'post_desc' => (string)($row['postMessage'] ?? ''),
                'voice_capsule_url' => !empty($row['voice_capsule_url']) ? (string)$row['voice_capsule_url'] : null,
            ];
        }
        return $out;
    }
}

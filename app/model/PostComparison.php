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

    public static function cleanLabel(mixed $raw): ?string
    {
        $label = trim(strip_tags((string)$raw));
        $label = mb_substr($label, 0, self::MAX_LABEL);
        return $label === '' ? null : $label;
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
    public static function createForPost(int $postNo, ?string $beforeLabel, ?string $afterLabel, ?int $parentPostNo = null): bool
    {
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

        $stmt = self::connect2()->prepare(
            'INSERT INTO post_comparisons (post_no, before_img, after_img, before_label, after_label, parent_post_no)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([$postNo, $before, $after, $beforeLabel, $afterLabel, $parentPostNo]);
    }

    /**
     * Batch fetch (one query, no N+1) keyed by post_no, with answer counts.
     *
     * @param array<int> $postNos
     * @return array<int, array<string, mixed>>
     */
    public static function getForPosts(array $postNos): array
    {
        if ($postNos === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($postNos), '?'));
        $stmt = self::connect2()->prepare(
            "SELECT c.post_no, c.before_img, c.after_img, c.before_label, c.after_label, c.parent_post_no,
                    (SELECT COUNT(*) FROM post_comparisons r
                       JOIN post rp ON rp.post_no = r.post_no AND rp.date_deleted IS NULL
                      WHERE r.parent_post_no = c.post_no) AS answer_count
               FROM post_comparisons c
              WHERE c.post_no IN ($in)"
        );
        $stmt->execute(array_values($postNos));

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[(int)$row['post_no']] = [
                'before_img' => (string)$row['before_img'],
                'after_img' => (string)$row['after_img'],
                'before_label' => $row['before_label'] !== null ? (string)$row['before_label'] : null,
                'after_label' => $row['after_label'] !== null ? (string)$row['after_label'] : null,
                'parent_post_no' => $row['parent_post_no'] !== null ? (int)$row['parent_post_no'] : null,
                'answer_count' => (int)$row['answer_count'],
            ];
        }
        return $out;
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
        $limit = max(1, min(24, $limit));

        $visibility = 'p.id = ?';
        $params = [$memberId, $memberId, $viewerId];
        if ($codes !== []) {
            $visibility = 'p.postFamCode IN (' . implode(',', array_fill(0, count($codes), '?')) . ') OR p.id = ?';
            $params = array_merge([$memberId, $memberId], $codes, [$viewerId]);
        }

        $stmt = self::connect2()->prepare(
            "SELECT c.post_no, c.before_img, c.after_img, c.before_label, c.after_label, p.fullName, p.date_created, p.postMessage
               FROM post_comparisons c
               JOIN post p ON p.post_no = c.post_no
              WHERE p.post_status = 'published'
                AND p.date_deleted IS NULL
                AND (p.id = ? OR EXISTS (SELECT 1 FROM post_tags t WHERE t.post_no = c.post_no AND t.user_id = ?))
                AND ($visibility)
              ORDER BY c.post_no DESC
              LIMIT $limit"
        );
        $stmt->execute($params);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $out[] = [
                'post_no' => (int)$row['post_no'],
                'before_img' => (string)$row['before_img'],
                'after_img' => (string)$row['after_img'],
                'before_label' => $row['before_label'] !== null ? (string)$row['before_label'] : null,
                'after_label' => $row['after_label'] !== null ? (string)$row['after_label'] : null,
                'fullName' => (string)$row['fullName'],
                'date_created' => (string)$row['date_created'],
                'post_desc' => (string)($row['postMessage'] ?? ''),
            ];
        }
        return $out;
    }
}

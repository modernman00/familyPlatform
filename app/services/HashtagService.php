<?php
declare(strict_types=1);

namespace App\services;

use Src\Db;
use PDO;
use Throwable;

/**
 * Living Heritage & Private Family Hashtags Service
 *
 * Governs topical tagging, cross-generational indexing, and memory pills
 * strictly within multi-tenant family boundaries.
 */
final class HashtagService extends Db
{
    /**
     * Extracts and normalises hashtags from text content.
     *
     * @param string $text
     * @param int $maxTags Maximum tags to extract (default 10)
     * @return array<int, string>
     */
    public static function extractHashtags(string $text, int $maxTags = 10): array
    {
        if (trim($text) === '') {
            return [];
        }

        // Match #tag starting with letter/digit/underscore, 2-40 chars long
        if (!preg_match_all('/#([a-zA-Z0-9_]{2,40})/u', $text, $matches)) {
            return [];
        }

        $rawTags = $matches[1];
        $cleanTags = [];

        foreach ($rawTags as $tag) {
            $norm = strtolower(trim((string)$tag));
            if ($norm !== '' && !in_array($norm, $cleanTags, true)) {
                $cleanTags[] = $norm;
            }
            if (count($cleanTags) >= $maxTags) {
                break;
            }
        }

        return $cleanTags;
    }

    /**
     * Synchronises hashtags for a given target (post, reel, journal, or image).
     *
     * @param string $familyCode
     * @param string $targetType 'post'|'reel'|'journal'|'image'
     * @param string|int $targetId
     * @param string $authorId
     * @param string $text
     * @return array<int, string> The list of active tags
     */
    public static function syncHashtags(
        string $familyCode,
        string $targetType,
        string|int $targetId,
        string $authorId,
        string $text
    ): array {
        $familyCode = trim($familyCode);
        $targetId = (string)$targetId;
        $authorId = trim($authorId);

        if ($familyCode === '' || $targetId === '') {
            return [];
        }

        $tags = self::extractHashtags($text);

        try {
            $pdo = self::connect2();
            $pdo->beginTransaction();

            // 1. Fetch existing hashtag IDs currently linked to this target
            $fetchStmt = $pdo->prepare(
                "SELECT hl.id, hl.hashtag_id, h.tag 
                 FROM family_hashtag_links hl 
                 INNER JOIN family_hashtags h ON hl.hashtag_id = h.id 
                 WHERE hl.target_type = ? AND hl.target_id = ?"
            );
            $fetchStmt->execute([$targetType, $targetId]);
            $existingRows = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);

            $existingTagsMap = [];
            foreach ($existingRows as $row) {
                $existingTagsMap[(string)$row['tag']] = (int)$row['hashtag_id'];
            }

            // 2. Identify tags to remove
            $tagsToRemove = [];
            foreach ($existingTagsMap as $eTag => $eId) {
                if (!in_array($eTag, $tags, true)) {
                    $tagsToRemove[$eTag] = $eId;
                }
            }

            // Delete links for removed tags and decrement usage count
            if (!empty($tagsToRemove)) {
                $delLinkStmt = $pdo->prepare(
                    "DELETE FROM family_hashtag_links 
                     WHERE target_type = ? AND target_id = ? AND hashtag_id = ?"
                );
                $decUsageStmt = $pdo->prepare(
                    "UPDATE family_hashtags 
                     SET usage_count = GREATEST(0, usage_count - 1) 
                     WHERE id = ?"
                );

                foreach ($tagsToRemove as $rTag => $rId) {
                    $delLinkStmt->execute([$targetType, $targetId, $rId]);
                    $decUsageStmt->execute([$rId]);
                }
            }

            // 3. Insert or increment new tags
            $upsertTagStmt = $pdo->prepare(
                "INSERT INTO family_hashtags (family_code, tag, usage_count) 
                 VALUES (?, ?, 1) 
                 ON DUPLICATE KEY UPDATE usage_count = usage_count + 1"
            );
            $getTagIdStmt = $pdo->prepare(
                "SELECT id FROM family_hashtags WHERE family_code = ? AND tag = ? LIMIT 1"
            );
            $insertLinkStmt = $pdo->prepare(
                "INSERT IGNORE INTO family_hashtag_links (hashtag_id, family_code, target_type, target_id, author_id) 
                 VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($tags as $tag) {
                if (isset($existingTagsMap[$tag])) {
                    // Tag link already exists; no duplicate increment needed
                    continue;
                }

                $upsertTagStmt->execute([$familyCode, $tag]);
                $getTagIdStmt->execute([$familyCode, $tag]);
                $hashtagId = (int)$getTagIdStmt->fetchColumn();

                if ($hashtagId > 0) {
                    $insertLinkStmt->execute([$hashtagId, $familyCode, $targetType, $targetId, $authorId]);
                }
            }

            $pdo->commit();
            return $tags;
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HashtagService::syncHashtags] Failed: ' . $e->getMessage());
            return $tags;
        }
    }

    /**
     * Returns trending or popular hashtags for a family.
     *
     * @param string $familyCode
     * @param int $limit
     * @return array<int, array{tag: string, count: int}>
     */
    public static function getTrendingHashtags(string $familyCode, int $limit = 12): array
    {
        $familyCode = trim($familyCode);
        if ($familyCode === '') {
            return [];
        }

        try {
            $pdo = self::connect2();
            $stmt = $pdo->prepare(
                "SELECT tag, usage_count AS count 
                 FROM family_hashtags 
                 WHERE family_code = ? AND usage_count > 0 
                 ORDER BY usage_count DESC, updated_at DESC 
                 LIMIT ?"
            );
            $stmt->bindValue(1, $familyCode, PDO::PARAM_STR);
            $stmt->bindValue(2, max(1, min($limit, 50)), PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[HashtagService::getTrendingHashtags] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Autocomplete suggestions for hashtags as the user types '#' in the composer.
     *
     * @param string $familyCode
     * @param string $query
     * @param int $limit
     * @return array<int, array{tag: string, count: int}>
     */
    public static function suggestHashtags(string $familyCode, string $query, int $limit = 8): array
    {
        $familyCode = trim($familyCode);
        $cleanQuery = strtolower(ltrim(trim($query), '#'));

        if ($familyCode === '') {
            return [];
        }

        try {
            $pdo = self::connect2();
            if ($cleanQuery === '') {
                return self::getTrendingHashtags($familyCode, $limit);
            }

            $stmt = $pdo->prepare(
                "SELECT tag, usage_count AS count 
                 FROM family_hashtags 
                 WHERE family_code = ? AND tag LIKE ? AND usage_count > 0 
                 ORDER BY usage_count DESC, tag ASC 
                 LIMIT ?"
            );
            $likePattern = $cleanQuery . '%';
            $stmt->bindValue(1, $familyCode, PDO::PARAM_STR);
            $stmt->bindValue(2, $likePattern, PDO::PARAM_STR);
            $stmt->bindValue(3, max(1, min($limit, 20)), PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[HashtagService::suggestHashtags] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetches feed items (posts and reels) matching a specific hashtag within the family.
     *
     * @param string $familyCode
     * @param string $tag
     * @param int $limit
     * @param int $offset
     * @return array{tag: string, count: int, items: array<int, array<string, mixed>>}
     */
    public static function getTagStream(string $familyCode, string $tag, int $limit = 30, int $offset = 0): array
    {
        $familyCode = trim($familyCode);
        $cleanTag = strtolower(ltrim(trim($tag), '#'));

        if ($familyCode === '' || $cleanTag === '') {
            return ['tag' => $cleanTag, 'count' => 0, 'items' => []];
        }

        try {
            $pdo = self::connect2();

            // 1. Verify hashtag existence for this family
            $countStmt = $pdo->prepare(
                "SELECT id, usage_count FROM family_hashtags WHERE family_code = ? AND tag = ? LIMIT 1"
            );
            $countStmt->execute([$familyCode, $cleanTag]);
            $hashtagRow = $countStmt->fetch(PDO::FETCH_ASSOC);

            if (!$hashtagRow) {
                return ['tag' => $cleanTag, 'count' => 0, 'items' => []];
            }

            $hashtagId = (int)$hashtagRow['id'];
            $usageCount = (int)$hashtagRow['usage_count'];

            // 2. Fetch linked items ordered by creation time
            $sql = "SELECT hl.target_type, hl.target_id, hl.created_at AS tagged_at,
                           p.post_no, p.id AS author_id, p.fullName, p.postMessage, p.postFamCode,
                           p.post_img0, p.post_img1, p.post_img2, p.post_img3, p.post_img4, p.post_img5,
                           p.post_likes, p.date_created,
                           pp.img AS profilePics
                    FROM family_hashtag_links hl
                    INNER JOIN post p ON (hl.target_type = 'post' AND hl.target_id = CAST(p.post_no AS CHAR))
                    LEFT JOIN profilePics pp ON p.id = pp.id
                    WHERE hl.hashtag_id = ? AND hl.family_code = ? AND p.post_status = 'published'
                    ORDER BY hl.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $hashtagId, PDO::PARAM_INT);
            $stmt->bindValue(2, $familyCode, PDO::PARAM_STR);
            $stmt->bindValue(3, max(1, min($limit, 50)), PDO::PARAM_INT);
            $stmt->bindValue(4, max(0, $offset), PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $formattedItems = [];

            foreach ($rows as $row) {
                // Collect images
                $images = [];
                for ($i = 0; $i <= 5; $i++) {
                    $k = 'post_img' . $i;
                    if (!empty($row[$k])) {
                        $images[] = $row[$k];
                    }
                }

                $formattedItems[] = [
                    'target_type' => $row['target_type'],
                    'post_no' => (int)$row['post_no'],
                    'author_id' => $row['author_id'],
                    'fullName' => $row['fullName'] ?? 'Family Member',
                    'postMessage' => $row['postMessage'] ?? '',
                    'postFamCode' => $row['postFamCode'] ?? '',
                    'images' => $images,
                    'profilePics' => !empty($row['profilePics']) ? '/resources/images/profile/' . $row['profilePics'] : '/public/avatar/avatarF.png',
                    'likes' => (int)($row['post_likes'] ?? 0),
                    'date_created' => $row['date_created'] ?? $row['tagged_at'],
                ];
            }

            return [
                'tag' => $cleanTag,
                'count' => $usageCount,
                'items' => $formattedItems
            ];
        } catch (Throwable $e) {
            error_log('[HashtagService::getTagStream] ' . $e->getMessage());
            return ['tag' => $cleanTag, 'count' => 0, 'items' => []];
        }
    }

    /**
     * Removes all hashtag linkages for a deleted target item.
     *
     * @param string $targetType
     * @param string|int $targetId
     * @return void
     */
    public static function deleteLinksForTarget(string $targetType, string|int $targetId): void
    {
        $targetId = (string)$targetId;
        if ($targetId === '') {
            return;
        }

        try {
            $pdo = self::connect2();
            $pdo->beginTransaction();

            $fetchStmt = $pdo->prepare(
                "SELECT hashtag_id FROM family_hashtag_links WHERE target_type = ? AND target_id = ?"
            );
            $fetchStmt->execute([$targetType, $targetId]);
            $hashtagIds = $fetchStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

            if (!empty($hashtagIds)) {
                $delStmt = $pdo->prepare(
                    "DELETE FROM family_hashtag_links WHERE target_type = ? AND target_id = ?"
                );
                $delStmt->execute([$targetType, $targetId]);

                $decStmt = $pdo->prepare(
                    "UPDATE family_hashtags SET usage_count = GREATEST(0, usage_count - 1) WHERE id = ?"
                );
                foreach ($hashtagIds as $hid) {
                    $decStmt->execute([(int)$hid]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[HashtagService::deleteLinksForTarget] ' . $e->getMessage());
        }
    }
}

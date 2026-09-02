<?php
declare(strict_types=1);

namespace App\model;

use Src\Select;
use PDO;
use Exception;

final class Reel extends Select
{
    /**
     * Fetch paginated Family Reels feed for the current user and their family circle
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getReelsFeed(string|int $userId, string $famCode, int $limit = 30, int $offset = 0): array
    {
        try {
            $pdo = self::connect2();
            
            // Join reels with personal profile and reactions
            $sql = "SELECT r.id, r.user_id, r.famCode, r.caption, r.video_url, r.thumbnail_url, 
                           r.aspect_ratio, r.category, r.music_title, r.views_count, r.created_at,
                           p.firstName, p.lastName, pp.img AS profilePics,
                           (SELECT COUNT(*) FROM family_reel_reactions frr WHERE frr.reel_id = r.id) AS likes_count,
                           (SELECT COUNT(*) FROM family_reel_comments frc WHERE frc.reel_id = r.id) AS comments_count,
                           (SELECT frr2.reaction_type FROM family_reel_reactions frr2 WHERE frr2.reel_id = r.id AND frr2.user_id = :currUser LIMIT 1) AS user_reaction
                    FROM family_reels AS r
                    INNER JOIN personal AS p ON r.user_id = p.id
                    LEFT JOIN profilePics AS pp ON r.user_id = pp.id
                    WHERE r.famCode = :famCode
                       OR r.user_id = :currUser2
                       OR r.user_id IN (
                           SELECT rm.approver_id FROM requestMgt rm WHERE rm.requester_id = :currUser3 AND LOWER(rm.status) IN ('approved', 'accepted')
                           UNION
                           SELECT rm2.requester_id FROM requestMgt rm2 WHERE rm2.approver_id = :currUser4 AND LOWER(rm2.status) IN ('approved', 'accepted')
                       )
                    ORDER BY r.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currUser', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':famCode', (string)$famCode, PDO::PARAM_STR);
            $stmt->bindValue(':currUser2', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':currUser3', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':currUser4', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("[Reel::getReelsFeed] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new Family Reel
     *
     * @param array<string, mixed> $data
     */
    public static function createReel(array $data): int
    {
        $pdo = self::connect2();
        $sql = "INSERT INTO family_reels (user_id, famCode, caption, video_url, thumbnail_url, aspect_ratio, category, music_title)
                VALUES (:user_id, :famCode, :caption, :video_url, :thumbnail_url, :aspect_ratio, :category, :music_title)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => (string)$data['user_id'],
            ':famCode' => (string)$data['famCode'],
            ':caption' => $data['caption'] ?? '',
            ':video_url' => (string)$data['video_url'],
            ':thumbnail_url' => $data['thumbnail_url'] ?? null,
            ':aspect_ratio' => $data['aspect_ratio'] ?? '9:16',
            ':category' => $data['category'] ?? 'milestone',
            ':music_title' => $data['music_title'] ?? 'Original Audio'
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Get a single reel by ID
     *
     * @return array<string, mixed>|null
     */
    public static function getReelById(int $reelId, int|string $currentUserId = ''): ?array
    {
        try {
            $pdo = self::connect2();
            $sql = "SELECT r.*, p.firstName, p.lastName, pp.img AS profilePics,
                           (SELECT COUNT(*) FROM family_reel_reactions frr WHERE frr.reel_id = r.id) AS likes_count,
                           (SELECT COUNT(*) FROM family_reel_comments frc WHERE frc.reel_id = r.id) AS comments_count,
                           (SELECT frr2.reaction_type FROM family_reel_reactions frr2 WHERE frr2.reel_id = r.id AND frr2.user_id = :currUser LIMIT 1) AS user_reaction
                    FROM family_reels AS r
                    INNER JOIN personal AS p ON r.user_id = p.id
                    LEFT JOIN profilePics AS pp ON r.user_id = pp.id
                    WHERE r.id = :reelId LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':reelId', $reelId, PDO::PARAM_INT);
            $stmt->bindValue(':currUser', (string)$currentUserId, PDO::PARAM_STR);
            $stmt->execute();

            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Toggle reaction on a Reel (like, love, celebrate, etc.)
     *
     * @return array{status: string, action: string, reaction: string, count: int}
     */
    public static function toggleReaction(int $reelId, string|int $userId, string $type = 'like'): array
    {
        $pdo = self::connect2();
        $strUserId = (string)$userId;

        // Check if reaction exists
        $checkStmt = $pdo->prepare("SELECT reaction_type FROM family_reel_reactions WHERE reel_id = :reelId AND user_id = :userId LIMIT 1");
        $checkStmt->execute([':reelId' => $reelId, ':userId' => $strUserId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['reaction_type'] === $type) {
                // Remove reaction (unlike)
                $delStmt = $pdo->prepare("DELETE FROM family_reel_reactions WHERE reel_id = :reelId AND user_id = :userId");
                $delStmt->execute([':reelId' => $reelId, ':userId' => $strUserId]);
                $action = 'removed';
                $userReaction = null;
            } else {
                // Update to new reaction
                $upStmt = $pdo->prepare("UPDATE family_reel_reactions SET reaction_type = :type WHERE reel_id = :reelId AND user_id = :userId");
                $upStmt->execute([':type' => $type, ':reelId' => $reelId, ':userId' => $strUserId]);
                $action = 'updated';
                $userReaction = $type;
            }
        } else {
            // Insert reaction
            $inStmt = $pdo->prepare("INSERT INTO family_reel_reactions (reel_id, user_id, reaction_type) VALUES (:reelId, :userId, :type)");
            $inStmt->execute([':reelId' => $reelId, ':userId' => $strUserId, ':type' => $type]);
            $action = 'added';
            $userReaction = $type;
        }

        // Get total count
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM family_reel_reactions WHERE reel_id = :reelId");
        $cntStmt->execute([':reelId' => $reelId]);
        $totalLikes = (int)$cntStmt->fetchColumn();

        return [
            'status' => 'success',
            'action' => $action,
            'reaction' => $userReaction ?? '',
            'count' => $totalLikes
        ];
    }

    /**
     * Get comments for a Reel
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getComments(int $reelId): array
    {
        try {
            $pdo = self::connect2();
            $sql = "SELECT c.id AS comment_id, c.reel_id, c.user_id, c.comment, c.created_at,
                           p.firstName, p.lastName, pp.img AS profilePics
                    FROM family_reel_comments AS c
                    INNER JOIN personal AS p ON c.user_id = p.id
                    LEFT JOIN profilePics AS pp ON c.user_id = pp.id
                    WHERE c.reel_id = :reelId
                    ORDER BY c.created_at ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':reelId' => $reelId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Add comment to a Reel
     *
     * @return array<string, mixed>
     */
    public static function addComment(int $reelId, string|int $userId, string $comment): array
    {
        $pdo = self::connect2();
        $strUserId = (string)$userId;

        $sql = "INSERT INTO family_reel_comments (reel_id, user_id, comment) VALUES (:reelId, :userId, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reelId' => $reelId,
            ':userId' => $strUserId,
            ':comment' => $comment
        ]);

        $commentId = (int)$pdo->lastInsertId();

        // Return the full comment object for instant optimistic DOM insertion
        $fetchStmt = $pdo->prepare("SELECT c.id AS comment_id, c.reel_id, c.user_id, c.comment, c.created_at,
                                           p.firstName, p.lastName, pp.img AS profilePics
                                    FROM family_reel_comments AS c
                                    INNER JOIN personal AS p ON c.user_id = p.id
                                    LEFT JOIN profilePics AS pp ON c.user_id = pp.id
                                    WHERE c.id = :id LIMIT 1");
        $fetchStmt->execute([':id' => $commentId]);
        return $fetchStmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Increment view counter
     */
    public static function incrementViews(int $reelId): void
    {
        try {
            $pdo = self::connect2();
            $stmt = $pdo->prepare("UPDATE family_reels SET views_count = views_count + 1 WHERE id = :id");
            $stmt->execute([':id' => $reelId]);
        } catch (\Throwable $e) {
            // Silently ignore view counter increments
        }
    }

    /**
     * Delete a Reel (Author or Manager only)
     */
    public static function deleteReel(int $reelId, string|int $userId): bool
    {
        $pdo = self::connect2();
        $stmt = $pdo->prepare("DELETE FROM family_reels WHERE id = :id AND user_id = :userId");
        $stmt->execute([':id' => $reelId, ':userId' => (string)$userId]);
        return $stmt->rowCount() > 0;
    }
}

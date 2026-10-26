<?php
declare(strict_types=1);

namespace App\model;

use Src\Select;
use PDO;
use Exception;

final class Reel extends Select
{
    /**
     * Get the configured reel expiration lifespan in days.
     * 0 = no expiration (permanent retention). Default: 7 days.
     */
    public static function getExpirationDays(): int
    {
        $envVal = $_ENV['REELS_EXPIRATION_DAYS'] ?? null;
        if ($envVal === null) {
            $fromGetEnv = getenv('REELS_EXPIRATION_DAYS');
            if ($fromGetEnv !== false) {
                $envVal = $fromGetEnv;
            }
        }

        if ($envVal === null || $envVal === '') {
            return 7;
        }

        return max(0, (int)$envVal);
    }

    /**
     * Fetch paginated Family Reels feed for the current user and their family circle
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getReelsFeed(string|int $userId, string $famCode, int $limit = 30, int $offset = 0): array
    {
        try {
            $pdo = self::connect2();
            $expiryDays = self::getExpirationDays();
            $expiryClause = $expiryDays > 0 ? " AND r.created_at >= DATE_SUB(NOW(), INTERVAL :expiryDays DAY)" : "";
            
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
                    WHERE (r.famCode = :famCode
                       OR r.user_id = :currUser2
                       OR r.user_id IN (
                           SELECT rm.approver_id FROM requestMgt rm WHERE rm.requester_id = :currUser3 AND LOWER(rm.status) IN ('approved', 'accepted')
                           UNION
                           SELECT rm2.requester_id FROM requestMgt rm2 WHERE rm2.approver_id = :currUser4 AND LOWER(rm2.status) IN ('approved', 'accepted')
                       ))" . $expiryClause . "
                    ORDER BY r.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':currUser', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':famCode', (string)$famCode, PDO::PARAM_STR);
            $stmt->bindValue(':currUser2', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':currUser3', (string)$userId, PDO::PARAM_STR);
            $stmt->bindValue(':currUser4', (string)$userId, PDO::PARAM_STR);
            if ($expiryDays > 0) {
                $stmt->bindValue(':expiryDays', $expiryDays, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            /** @var array<int, array<string, mixed>> $rows */
            return array_map([self::class, 'normalizeMediaUrls'], $rows);
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
            $expiryDays = self::getExpirationDays();
            $expiryClause = $expiryDays > 0 ? " AND r.created_at >= DATE_SUB(NOW(), INTERVAL :expiryDays DAY)" : "";

            $sql = "SELECT r.*, p.firstName, p.lastName, pp.img AS profilePics,
                           (SELECT COUNT(*) FROM family_reel_reactions frr WHERE frr.reel_id = r.id) AS likes_count,
                           (SELECT COUNT(*) FROM family_reel_comments frc WHERE frc.reel_id = r.id) AS comments_count,
                           (SELECT frr2.reaction_type FROM family_reel_reactions frr2 WHERE frr2.reel_id = r.id AND frr2.user_id = :currUser LIMIT 1) AS user_reaction
                    FROM family_reels AS r
                    INNER JOIN personal AS p ON r.user_id = p.id
                    LEFT JOIN profilePics AS pp ON r.user_id = pp.id
                    WHERE r.id = :reelId" . $expiryClause . " LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':reelId', $reelId, PDO::PARAM_INT);
            $stmt->bindValue(':currUser', (string)$currentUserId, PDO::PARAM_STR);
            if ($expiryDays > 0) {
                $stmt->bindValue(':expiryDays', $expiryDays, PDO::PARAM_INT);
            }
            $stmt->execute();

            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($res) ? self::normalizeMediaUrls($res) : null;
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
     * Sanitize and validate a GIF URL against the Giphy allowlist.
     */
    public static function sanitizeCommentGifUrl(mixed $raw): ?string
    {
        if (!is_string($raw)) {
            return null;
        }
        $url = trim($raw);
        if ($url === '' || strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user']) || isset($parts['pass'])
            || !preg_match('/^(media\d*\.)?giphy\.com$/', $host)
        ) {
            return null;
        }
        return $url;
    }

    /**
     * Get comments for a Reel (with reaction summary and threaded parent linkage)
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getComments(int $reelId, string|int $currentUserId = ''): array
    {
        try {
            $pdo = self::connect2();
            $strUserId = (string)$currentUserId;

            $sql = "SELECT c.id AS comment_id, c.reel_id, c.user_id, c.parent_comment_no, c.comment, c.gif_url, c.created_at, c.updated_at,
                           p.firstName, p.lastName, pp.img AS profilePics
                    FROM family_reel_comments AS c
                    INNER JOIN personal AS p ON c.user_id = p.id
                    LEFT JOIN profilePics AS pp ON c.user_id = pp.id
                    WHERE c.reel_id = :reelId
                    ORDER BY c.created_at ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':reelId' => $reelId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($rows)) {
                return [];
            }

            // Extract comment IDs
            $commentIds = array_map(fn($r) => (int)$r['comment_id'], $rows);
            $placeholders = implode(',', array_fill(0, count($commentIds), '?'));

            // Batch fetch reactions summary
            $reactionsSql = sprintf(
                'SELECT comment_id, reaction_type, COUNT(*) AS cnt FROM family_reel_comment_reactions WHERE comment_id IN (%s) GROUP BY comment_id, reaction_type',
                $placeholders
            );
            $reactStmt = $pdo->prepare($reactionsSql);
            $reactStmt->execute($commentIds);
            $reactionRows = $reactStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $reactionMap = [];
            foreach ($reactionRows as $rr) {
                $cId = (int)$rr['comment_id'];
                $rType = (string)$rr['reaction_type'];
                $cnt = (int)$rr['cnt'];
                if (!isset($reactionMap[$cId])) {
                    $reactionMap[$cId] = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0, 'total' => 0];
                }
                $reactionMap[$cId][$rType] = $cnt;
                $reactionMap[$cId]['total'] += $cnt;
            }

            // Batch fetch user active reaction
            $userReactions = [];
            if ($strUserId !== '') {
                $userReactSql = sprintf(
                    'SELECT comment_id, reaction_type FROM family_reel_comment_reactions WHERE comment_id IN (%s) AND user_id = ?',
                    $placeholders
                );
                $userStmt = $pdo->prepare($userReactSql);
                $execParams = array_merge($commentIds, [$strUserId]);
                $userStmt->execute($execParams);
                $uRows = $userStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                foreach ($uRows as $ur) {
                    $userReactions[(int)$ur['comment_id']] = (string)$ur['reaction_type'];
                }
            }

            // Merge reactions and normalize media URLs
            foreach ($rows as &$comment) {
                $cId = (int)$comment['comment_id'];
                $comment['reactions'] = $reactionMap[$cId] ?? ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0, 'total' => 0];
                $comment['total_reactions'] = $comment['reactions']['total'] ?? 0;
                $comment['user_reaction'] = $userReactions[$cId] ?? null;
            }
            unset($comment);

            return array_map([self::class, 'normalizeMediaUrls'], $rows);
        } catch (\Throwable $e) {
            error_log('[Reel::getComments] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Add comment to a Reel (supports 1-level threaded reply and curated GIFs)
     *
     * @return array<string, mixed>
     */
    public static function addComment(
        int $reelId,
        string|int $userId,
        string $comment,
        ?int $parentCommentNo = null,
        ?string $gifUrl = null
    ): array {
        $pdo = self::connect2();
        $strUserId = (string)$userId;
        $sanitizedGif = self::sanitizeCommentGifUrl($gifUrl);
        $cleanComment = trim($comment);

        if ($cleanComment === '' && $sanitizedGif === null) {
            throw new \InvalidArgumentException('Write a comment or pick a GIF.');
        }

        $resolvedParentNo = null;
        if ($parentCommentNo !== null && $parentCommentNo > 0) {
            $pStmt = $pdo->prepare("SELECT id, reel_id, parent_comment_no FROM family_reel_comments WHERE id = :pid LIMIT 1");
            $pStmt->execute([':pid' => $parentCommentNo]);
            $parentRow = $pStmt->fetch(PDO::FETCH_ASSOC);

            if (!$parentRow || (int)$parentRow['reel_id'] !== $reelId) {
                throw new \InvalidArgumentException('The comment you are replying to is no longer available on this reel.');
            }

            // 1-level thread invariant: reply to a reply attaches to root parent
            $resolvedParentNo = !empty($parentRow['parent_comment_no']) ? (int)$parentRow['parent_comment_no'] : (int)$parentRow['id'];
        }

        $sql = "INSERT INTO family_reel_comments (reel_id, user_id, parent_comment_no, comment, gif_url) 
                VALUES (:reelId, :userId, :parentCommentNo, :comment, :gifUrl)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':reelId' => $reelId,
            ':userId' => $strUserId,
            ':parentCommentNo' => $resolvedParentNo,
            ':comment' => $cleanComment,
            ':gifUrl' => $sanitizedGif
        ]);

        $commentId = (int)$pdo->lastInsertId();

        // Return the full comment object for instant optimistic DOM insertion
        $fetchStmt = $pdo->prepare("SELECT c.id AS comment_id, c.reel_id, c.user_id, c.parent_comment_no, c.comment, c.gif_url, c.created_at, c.updated_at,
                                           p.firstName, p.lastName, pp.img AS profilePics
                                    FROM family_reel_comments AS c
                                    INNER JOIN personal AS p ON c.user_id = p.id
                                    LEFT JOIN profilePics AS pp ON c.user_id = pp.id
                                    WHERE c.id = :id LIMIT 1");
        $fetchStmt->execute([':id' => $commentId]);
        $res = $fetchStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($res)) {
            $res = self::normalizeMediaUrls($res);
            $res['reactions'] = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0, 'total' => 0];
            $res['total_reactions'] = 0;
            $res['user_reaction'] = null;
        }
        return $res;
    }

    /**
     * Update an existing Reel comment (Author-only, IDOR protected).
     *
     * @return array{success: bool, code: int, message: string, data?: array<string, mixed>}
     */
    public static function updateComment(int $commentId, string|int $userId, string $newComment): array
    {
        try {
            $pdo = self::connect2();
            $strUserId = (string)$userId;
            $cleanText = trim($newComment);

            if ($cleanText === '') {
                return ['success' => false, 'code' => 400, 'message' => 'Comment text cannot be empty.'];
            }

            $stmt = $pdo->prepare("SELECT id, user_id, reel_id FROM family_reel_comments WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $commentId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment) {
                return ['success' => false, 'code' => 404, 'message' => 'Comment not found.'];
            }

            if ((string)$comment['user_id'] !== $strUserId) {
                return ['success' => false, 'code' => 403, 'message' => 'You can only edit your own comments.'];
            }

            $upd = $pdo->prepare("UPDATE family_reel_comments SET comment = :comment WHERE id = :id");
            $upd->execute([':comment' => $cleanText, ':id' => $commentId]);

            $fetchStmt = $pdo->prepare(
                "SELECT c.id AS comment_id, c.reel_id, c.user_id, c.parent_comment_no, c.comment, c.gif_url, c.created_at, c.updated_at,
                        p.firstName, p.lastName, pp.img AS profilePics
                 FROM family_reel_comments AS c
                 INNER JOIN personal AS p ON c.user_id = p.id
                 LEFT JOIN profilePics AS pp ON c.user_id = pp.id
                 WHERE c.id = :id LIMIT 1"
            );
            $fetchStmt->execute([':id' => $commentId]);
            $updated = $fetchStmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Comment updated successfully.',
                'data' => self::normalizeMediaUrls($updated)
            ];
        } catch (\Throwable $e) {
            error_log('[Reel::updateComment] ' . $e->getMessage());
            return ['success' => false, 'code' => 500, 'message' => 'Internal server error.'];
        }
    }

    /**
     * Toggle reaction on a Reel comment (like, love, haha, wow, sad, angry).
     *
     * @return array{status: string, action: string, reaction: string, count: int, reactions: array<string, int>, commentAuthorId: string, reelId: int}
     */
    public static function toggleCommentReaction(int $commentId, string|int $userId, string $type = 'like'): array
    {
        $pdo = self::connect2();
        $strUserId = (string)$userId;
        $allowed = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
        $reactionType = in_array($type, $allowed, true) ? $type : 'like';

        // Check if comment exists
        $checkComm = $pdo->prepare("SELECT id, reel_id, user_id FROM family_reel_comments WHERE id = :cid LIMIT 1");
        $checkComm->execute([':cid' => $commentId]);
        $comm = $checkComm->fetch(PDO::FETCH_ASSOC);
        if (!$comm) {
            throw new \InvalidArgumentException('Comment not found.');
        }

        // Check if user already reacted
        $checkStmt = $pdo->prepare("SELECT reaction_type FROM family_reel_comment_reactions WHERE comment_id = :cid AND user_id = :uid LIMIT 1");
        $checkStmt->execute([':cid' => $commentId, ':uid' => $strUserId]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

        $action = 'added';
        $userReaction = $reactionType;

        if ($existing) {
            if ($existing['reaction_type'] === $reactionType) {
                // Same reaction clicked -> remove (toggle off)
                $delStmt = $pdo->prepare("DELETE FROM family_reel_comment_reactions WHERE comment_id = :cid AND user_id = :uid");
                $delStmt->execute([':cid' => $commentId, ':uid' => $strUserId]);
                $action = 'removed';
                $userReaction = '';
            } else {
                // Different reaction -> update
                $updStmt = $pdo->prepare("UPDATE family_reel_comment_reactions SET reaction_type = :type WHERE comment_id = :cid AND user_id = :uid");
                $updStmt->execute([':type' => $reactionType, ':cid' => $commentId, ':uid' => $strUserId]);
                $action = 'updated';
            }
        } else {
            // New reaction -> insert
            $insStmt = $pdo->prepare("INSERT INTO family_reel_comment_reactions (comment_id, user_id, reaction_type) VALUES (:cid, :uid, :type)");
            $insStmt->execute([':cid' => $commentId, ':uid' => $strUserId, ':type' => $reactionType]);
            $action = 'added';
        }

        // Fetch refreshed reaction counts
        $countStmt = $pdo->prepare("SELECT reaction_type, COUNT(*) AS cnt FROM family_reel_comment_reactions WHERE comment_id = :cid GROUP BY reaction_type");
        $countStmt->execute([':cid' => $commentId]);
        $cRows = $countStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $counts = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
        $total = 0;
        foreach ($cRows as $r) {
            $t = (string)$r['reaction_type'];
            $c = (int)$r['cnt'];
            if (isset($counts[$t])) {
                $counts[$t] = $c;
            }
            $total += $c;
        }

        return [
            'status' => 'success',
            'action' => $action,
            'reaction' => $userReaction,
            'count' => $total,
            'reactions' => $counts,
            'commentAuthorId' => (string)($comm['user_id'] ?? ''),
            'reelId' => (int)($comm['reel_id'] ?? 0)
        ];
    }

    /**
     * Delete a Reel comment (IDOR protected: author of comment OR account holder of reel)
     * Cascades deletion of all child replies and reactions.
     *
     * @param int $commentId
     * @param string|int $userId
     * @return array{success: bool, code: int, message: string}
     */
    public static function deleteComment(int $commentId, string|int $userId): array
    {
        try {
            $pdo = self::connect2();
            $strUserId = (string)$userId;

            $stmt = $pdo->prepare(
                "SELECT c.id, c.reel_id, c.user_id AS comment_user_id, r.user_id AS reel_user_id
                 FROM family_reel_comments c
                 INNER JOIN family_reels r ON c.reel_id = r.id
                 WHERE c.id = :commentId LIMIT 1"
            );
            $stmt->execute([':commentId' => $commentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return ['success' => false, 'code' => 404, 'message' => 'Comment not found or already deleted.'];
            }

            $commentUserId = (string)($row['comment_user_id'] ?? '');
            $reelUserId = (string)($row['reel_user_id'] ?? '');

            if ($strUserId !== $commentUserId && $strUserId !== $reelUserId) {
                return ['success' => false, 'code' => 403, 'message' => 'You are not authorised to delete this comment.'];
            }

            // Cascade delete reactions for child replies and the comment itself
            $delChildReactions = $pdo->prepare(
                "DELETE cr FROM family_reel_comment_reactions cr 
                 INNER JOIN family_reel_comments c ON cr.comment_id = c.id 
                 WHERE c.parent_comment_no = :pid"
            );
            $delChildReactions->execute([':pid' => $commentId]);

            $delReactions = $pdo->prepare("DELETE FROM family_reel_comment_reactions WHERE comment_id = :commentId");
            $delReactions->execute([':commentId' => $commentId]);

            // Cascade delete child replies
            $delReplies = $pdo->prepare("DELETE FROM family_reel_comments WHERE parent_comment_no = :pid");
            $delReplies->execute([':pid' => $commentId]);

            // Delete the comment
            $delStmt = $pdo->prepare("DELETE FROM family_reel_comments WHERE id = :commentId");
            $delStmt->execute([':commentId' => $commentId]);

            return ['success' => true, 'code' => 200, 'message' => 'Comment deleted successfully.'];
        } catch (\Throwable $e) {
            error_log('[Reel::deleteComment] ' . $e->getMessage());
            return ['success' => false, 'code' => 500, 'message' => 'Internal server error.'];
        }
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
     * Update a reel's thumbnail URL and detected aspect ratio (Owner-only, IDOR guarded).
     */
    public static function updateThumbnailAndAspect(int $reelId, string|int $userId, string $thumbnailUrl, string $aspectRatio = '9:16'): bool
    {
        try {
            $pdo = self::connect2();
            $allowedAspects = ['9:16', '16:9', '1:1', '4:5', 'cinema'];
            $safeAspect = in_array($aspectRatio, $allowedAspects, true) ? $aspectRatio : '9:16';

            $stmt = $pdo->prepare("UPDATE family_reels SET thumbnail_url = :thumb, aspect_ratio = :aspect WHERE id = :id AND user_id = :userId");
            $stmt->execute([
                ':thumb' => $thumbnailUrl,
                ':aspect' => $safeAspect,
                ':id' => $reelId,
                ':userId' => (string)$userId,
            ]);

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log("[Reel::updateThumbnailAndAspect] " . $e->getMessage());
            return false;
        }
    }


    /**
     * Delete a Reel (Author or Manager only)
     */
    public static function deleteReel(int $reelId, string|int $userId): bool
    {
        try {
            $pdo = self::connect2();

            // Fetch reel data first to locate files on disk and verify ownership
            $fetchStmt = $pdo->prepare("SELECT id, video_url, thumbnail_url FROM family_reels WHERE id = :id AND user_id = :userId LIMIT 1");
            $fetchStmt->execute([':id' => $reelId, ':userId' => (string)$userId]);
            $reel = $fetchStmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($reel)) {
                return false;
            }

            $basePath = defined('BASE_PATH') ? (string)BASE_PATH : (realpath(__DIR__ . '/../../') ?: '');
            if ($basePath !== '') {
                self::deleteMediaFiles((string)($reel['video_url'] ?? ''), (string)($reel['thumbnail_url'] ?? ''), $basePath);
            }

            // Delete child reactions, comment reactions, and comments
            $delReact = $pdo->prepare("DELETE FROM family_reel_reactions WHERE reel_id = :id");
            $delReact->execute([':id' => $reelId]);

            $delCommReact = $pdo->prepare("DELETE cr FROM family_reel_comment_reactions cr INNER JOIN family_reel_comments c ON cr.comment_id = c.id WHERE c.reel_id = :id");
            $delCommReact->execute([':id' => $reelId]);

            $delComm = $pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id = :id");
            $delComm->execute([':id' => $reelId]);

            // Delete the reel
            $stmt = $pdo->prepare("DELETE FROM family_reels WHERE id = :id AND user_id = :userId");
            $stmt->execute([':id' => $reelId, ':userId' => (string)$userId]);

            return $stmt->rowCount() > 0;
        } catch (\Throwable $e) {
            error_log("[Reel::deleteReel] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Purge expired reels from the database and delete associated physical video/thumbnail files.
     *
     * @param int|null $overrideDays Optional custom lifespan in days. If null, uses getExpirationDays().
     * @return int Number of purged reels.
     */
    public static function purgeExpiredReels(?int $overrideDays = null): int
    {
        $days = $overrideDays !== null ? max(0, $overrideDays) : self::getExpirationDays();
        if ($days <= 0) {
            return 0; // Expiration disabled (permanent retention)
        }

        try {
            $pdo = self::connect2();

            // Find all expired reels to clean up media files
            $findStmt = $pdo->prepare("SELECT id, video_url, thumbnail_url FROM family_reels WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
            $findStmt->bindValue(':days', $days, PDO::PARAM_INT);
            $findStmt->execute();
            $expiredReels = $findStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($expiredReels)) {
                return 0;
            }

            $purgedCount = 0;
            $basePath = defined('BASE_PATH') ? (string)BASE_PATH : (realpath(__DIR__ . '/../../') ?: '');

            foreach ($expiredReels as $reel) {
                if (!is_array($reel)) {
                    continue;
                }
                $reelId = (int)$reel['id'];

                // Safely delete associated physical video and thumbnail files
                if ($basePath !== '') {
                    self::deleteMediaFiles((string)($reel['video_url'] ?? ''), (string)($reel['thumbnail_url'] ?? ''), $basePath);
                }

                // Delete reactions
                $delReact = $pdo->prepare("DELETE FROM family_reel_reactions WHERE reel_id = :id");
                $delReact->execute([':id' => $reelId]);

                $delCommReact = $pdo->prepare("DELETE cr FROM family_reel_comment_reactions cr INNER JOIN family_reel_comments c ON cr.comment_id = c.id WHERE c.reel_id = :id");
                $delCommReact->execute([':id' => $reelId]);

                // Delete comments
                $delComm = $pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id = :id");
                $delComm->execute([':id' => $reelId]);

                // Delete reel record
                $delReel = $pdo->prepare("DELETE FROM family_reels WHERE id = :id");
                $delReel->execute([':id' => $reelId]);

                if ($delReel->rowCount() > 0) {
                    $purgedCount++;
                }
            }

            return $purgedCount;
        } catch (\Throwable $e) {
            error_log("[Reel::purgeExpiredReels] " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Safely delete physical video and thumbnail files on local storage.
     */
    private static function deleteMediaFiles(string $videoUrl, string $thumbnailUrl, string $basePath): void
    {
        $allowedPrefixes = [
            '/resources/videos/reels/',
            '/resources/images/reels/thumbs/',
            '/public/resources/videos/reels/',
            '/public/resources/images/reels/thumbs/'
        ];

        $canonicalBase = realpath($basePath);
        if ($canonicalBase === false) {
            return;
        }

        foreach ([$videoUrl, $thumbnailUrl] as $url) {
            if ($url === '') {
                continue;
            }

            // Only delete local relative paths, never external URLs
            $isLocal = false;
            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($url, $prefix)) {
                    $isLocal = true;
                    break;
                }
            }

            if (!$isLocal) {
                continue;
            }

            // Clean path and prevent directory traversal
            $relPath = ltrim((string)(parse_url($url, PHP_URL_PATH) ?: $url), '/');
            $fullPath = rtrim($canonicalBase, '/') . '/' . $relPath;
            $realPath = realpath($fullPath);

            if ($realPath !== false && is_file($realPath)) {
                // Ensure real path is inside canonicalBase
                if (str_starts_with($realPath, $canonicalBase)) {
                    // nosemgrep: php.lang.security.unlink-use.unlink-use
                    @unlink($realPath);
                }
            }
        }
    }

    /**
     * Normalize legacy /public/ path prefixes for video and thumbnail URLs.
     *
     * @param array<string, mixed> $reel
     * @return array<string, mixed>
     */
    private static function normalizeMediaUrls(array $reel): array
    {
        if (isset($reel['video_url']) && is_string($reel['video_url'])) {
            $reel['video_url'] = str_replace('/public/resources/videos/reels/', '/resources/videos/reels/', $reel['video_url']);
        }
        if (isset($reel['thumbnail_url']) && is_string($reel['thumbnail_url'])) {
            $reel['thumbnail_url'] = str_replace('/public/resources/images/reels/thumbs/', '/resources/images/reels/thumbs/', $reel['thumbnail_url']);
        }
        return $reel;
    }
}

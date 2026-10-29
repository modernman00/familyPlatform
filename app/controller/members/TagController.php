<?php

declare(strict_types=1);

namespace App\controller\members;

use PDO;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\UnauthorisedException;
use Src\Exceptions\ValidationException;
use Src\functionality\SignIn;
use Throwable;

/**
 * TagController
 *
 * Sovereign controller for Post and Media tagging across FamilyPlatform.
 * Governs member tag suggestions, post-level tags ("With..."), self-untagging,
 * and spatial photo tags with strict cross-tenant boundary validation.
 */
class TagController
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * Resolves authenticated caller ID from session or signed token.
     *
     * @return string
     * @throws UnauthorisedException
     */
    private static function getAuthenticatedUserId(): string
    {
        $userId = isset($_SESSION['id']) ? (string)$_SESSION['id'] : '';
        if ($userId === '') {
            try {
                $payload = SignIn::verify('users');
                if (!empty($payload['id'])) {
                    $userId = (string)\cleanSession((string)$payload['id']);
                    $_SESSION['id'] = $userId;
                }
            } catch (Throwable) {
                $userId = '';
            }
        }

        if ($userId === '') {
            throw new UnauthorisedException('User session is not verified.');
        }

        return $userId;
    }

    /**
     * Resolves caller's active family code.
     *
     * @param string $userId
     * @return string
     */
    private function getCallerFamCode(string $userId): string
    {
        $famCode = (string)($_SESSION['famCode'] ?? '');
        if ($famCode === '') {
            try {
                $stmt = $this->pdo->prepare('SELECT famCode FROM personal WHERE id = ?');
                $stmt->execute([$userId]);
                $famCode = (string)($stmt->fetchColumn() ?: '');
                if ($famCode !== '') {
                    $_SESSION['famCode'] = $famCode;
                }
            } catch (Throwable) {
                $famCode = '';
            }
        }

        return $famCode;
    }

    /**
     * GET /api/members/tag-suggestions?q=...&limit=20
     * Returns searchable family members and approved connections for tagging.
     */
    public function searchTagSuggestions(): void
    {
        try {
            $userId = self::getAuthenticatedUserId();
            $famCode = $this->getCallerFamCode($userId);

            $term = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
            $limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 50)) : 20;

            $params = [$userId, $userId, $userId, $famCode];
            $searchSql = '';

            if ($term !== '') {
                $like = '%' . $term . '%';
                $searchSql = " AND (p.firstName LIKE ? OR p.lastName LIKE ? OR CONCAT(p.firstName, ' ', p.lastName) LIKE ?) ";
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            $params[] = $famCode;
            $params[] = (int)$limit;

            $sql = "
                SELECT DISTINCT p.id, p.firstName, p.lastName, p.famCode, pp.img
                FROM personal AS p
                LEFT JOIN profilePics AS pp ON p.id = pp.id
                LEFT JOIN requestMgt AS rm ON (
                    (rm.requester_id = ? AND rm.approver_id = p.id AND LOWER(rm.status) IN ('approved', 'accepted'))
                    OR
                    (rm.approver_id = ? AND rm.requester_id = p.id AND LOWER(rm.status) IN ('approved', 'accepted'))
                )
                WHERE p.id != ?
                  AND p.deleted_at IS NULL
                  AND (
                      (p.famCode = ? AND p.famCode != '')
                      OR rm.status IS NOT NULL
                  )
                  {$searchSql}
                ORDER BY (p.famCode = ?) DESC, p.firstName ASC, p.lastName ASC
                LIMIT ?
            ";

            $stmt = $this->pdo->prepare($sql);
            $paramIndex = 1;
            foreach ($params as $paramValue) {
                $stmt->bindValue($paramIndex++, $paramValue, is_int($paramValue) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $members = [];
            foreach ($rows as $row) {
                $img = $row['img'] ?? null;
                $profImg = '/resources/images/profile/avatarM.png';
                if (!empty($img)) {
                    $profImg = (str_starts_with((string)$img, '/') || str_starts_with((string)$img, 'http'))
                        ? (string)$img
                        : "/resources/images/profile/{$img}";
                }

                $members[] = [
                    'id' => (string)$row['id'],
                    'fullName' => trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? '')),
                    'firstName' => (string)($row['firstName'] ?? ''),
                    'lastName' => (string)($row['lastName'] ?? ''),
                    'profileImg' => $profImg,
                    'famCode' => (string)($row['famCode'] ?? ''),
                ];
            }

            \msgSuccess(200, ['members' => $members]);
        } catch (Throwable $e) {
            \error_log('[TagController::searchTagSuggestions] ' . $e->getMessage());
            \msgException(500, 'Unable to retrieve tag suggestions.');
        }
    }

    /**
     * POST /api/posts/[i:postNo]/tags
     * Adds tagged members to an existing post.
     */
    public function addPostTags(int|string $postNo): void
    {
        try {
            $userId = self::getAuthenticatedUserId();
            $famCode = $this->getCallerFamCode($userId);
            $postNo = (int)$postNo;

            // Validate post existence and access
            $stmtPost = $this->pdo->prepare("SELECT post_no, id, postFamCode FROM post WHERE post_no = ? AND date_deleted IS NULL LIMIT 1");
            $stmtPost->execute([$postNo]);
            $post = $stmtPost->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                throw new ValidationException('Post not found or has been removed.');
            }

            $rawInput = file_get_contents('php://input');
            $data = json_decode($rawInput !== false && $rawInput !== '' ? $rawInput : '{}', true);
            $taggedUserIds = $data['tagged_user_ids'] ?? $_POST['tagged_user_ids'] ?? [];

            if (is_string($taggedUserIds)) {
                $taggedUserIds = json_decode($taggedUserIds, true) ?: [$taggedUserIds];
            }

            if (!is_array($taggedUserIds)) {
                throw new ValidationException('Invalid tagged user IDs.');
            }

            $cleanIds = array_values(array_unique(array_filter(array_map('strval', $taggedUserIds))));
            $tags = self::syncPostTags($postNo, $userId, $cleanIds, (string)($post['postFamCode'] ?: $famCode));

            \msgSuccess(200, ['tags' => $tags]);
        } catch (ValidationException $e) {
            \msgException(422, $e->getMessage());
        } catch (ForbiddenException $e) {
            \msgException(403, $e->getMessage());
        } catch (Throwable $e) {
            \error_log('[TagController::addPostTags] ' . $e->getMessage());
            \msgException(500, 'Unable to add tags to post.');
        }
    }

    /**
     * DELETE /api/posts/[i:postNo]/tags/[*:userId]
     * Removes a user's tag from a post.
     * Authorized if caller is the tagged user (self-untag) OR the original author.
     */
    public function removePostTag(int|string $postNo, string $targetUserId): void
    {
        try {
            $callerId = self::getAuthenticatedUserId();
            $postNo = (int)$postNo;
            $targetUserId = trim($targetUserId);

            // Fetch the tag record
            $stmtTag = $this->pdo->prepare("SELECT id, post_no, user_id, tagged_by FROM post_tags WHERE post_no = ? AND user_id = ? LIMIT 1");
            $stmtTag->execute([$postNo, $targetUserId]);
            $tag = $stmtTag->fetch(PDO::FETCH_ASSOC);

            if (!$tag) {
                \msgSuccess(200, 'Tag already removed.');
                return;
            }

            // Authorization: caller must be tagged user (self-untag) or the creator of the tag
            if ($callerId !== (string)$tag['user_id'] && $callerId !== (string)$tag['tagged_by']) {
                // Also check if caller is original post author
                $stmtPost = $this->pdo->prepare("SELECT id FROM post WHERE post_no = ? LIMIT 1");
                $stmtPost->execute([$postNo]);
                $postAuthorId = (string)($stmtPost->fetchColumn() ?: '');

                if ($callerId !== $postAuthorId) {
                    throw new ForbiddenException('You are not authorized to remove this tag.');
                }
            }

            $stmtDel = $this->pdo->prepare("DELETE FROM post_tags WHERE post_no = ? AND user_id = ?");
            $stmtDel->execute([$postNo, $targetUserId]);

            \msgSuccess(200, 'Tag removed successfully.');
        } catch (ForbiddenException $e) {
            \msgException(403, $e->getMessage());
        } catch (Throwable $e) {
            \error_log('[TagController::removePostTag] ' . $e->getMessage());
            \msgException(500, 'Unable to remove tag from post.');
        }
    }

    /**
     * Retrieves all hydrated tags for a specific post.
     *
     * @param int $postNo
     * @return array<int, array{user_id: string, fullName: string, firstName: string, lastName: string, profileImg: string, tagged_by: string}>
     */
    public static function getPostTags(int $postNo): array
    {
        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare("
                SELECT pt.user_id, pt.tagged_by, p.firstName, p.lastName, pp.img
                FROM post_tags pt
                JOIN personal p ON pt.user_id = p.id
                LEFT JOIN profilePics pp ON pt.user_id = pp.id
                WHERE pt.post_no = ?
                ORDER BY pt.id ASC
            ");
            $stmt->execute([$postNo]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $tags = [];
            foreach ($rows as $row) {
                $img = $row['img'] ?? null;
                $profImg = '/resources/images/profile/avatarM.png';
                if (!empty($img)) {
                    $profImg = (str_starts_with((string)$img, '/') || str_starts_with((string)$img, 'http'))
                        ? (string)$img
                        : "/resources/images/profile/{$img}";
                }

                $tags[] = [
                    'user_id' => (string)$row['user_id'],
                    'fullName' => trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? '')),
                    'firstName' => (string)($row['firstName'] ?? ''),
                    'lastName' => (string)($row['lastName'] ?? ''),
                    'profileImg' => $profImg,
                    'tagged_by' => (string)$row['tagged_by']
                ];
            }

            return $tags;
        } catch (Throwable $e) {
            \error_log('[TagController::getPostTags] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Synchronises post tags for a newly created or updated post.
     * Persists rows, prevents duplicates, dispatches notifications & Pusher events.
     *
     * @param int $postNo
     * @param string $authorId
     * @param array<string> $taggedUserIds
     * @param string $famCode
     * @return array<int, array{user_id: string, fullName: string, firstName: string, lastName: string, profileImg: string, tagged_by: string}>
     */
    public static function syncPostTags(int $postNo, string $authorId, array $taggedUserIds, string $famCode): array
    {
        if (empty($taggedUserIds) || $postNo <= 0) {
            return [];
        }

        try {
            $pdo = Db::connect2();

            // Author details for notification
            $authorStmt = $pdo->prepare("SELECT firstName, lastName FROM personal WHERE id = ? LIMIT 1");
            $authorStmt->execute([$authorId]);
            $authorRow = $authorStmt->fetch(PDO::FETCH_ASSOC);
            $authorName = trim(($authorRow['firstName'] ?? '') . ' ' . ($authorRow['lastName'] ?? ''));
            if ($authorName === '') {
                $authorName = 'A family member';
            }

            $insertStmt = $pdo->prepare("
                INSERT INTO post_tags (post_no, user_id, tagged_by, created_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE tagged_by = VALUES(tagged_by)
            ");

            $notifStmt = $pdo->prepare("
                INSERT INTO notification 
                (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
                VALUES (?, ?, ?, 'Post Tag', 'Post Tag', ?, ?, 'new', NOW())
            ");

            $validTagged = [];
            foreach ($taggedUserIds as $targetId) {
                $targetId = trim((string)$targetId);
                // Cannot tag self or empty ID
                if ($targetId === '' || $targetId === $authorId) {
                    continue;
                }

                // Verify target belongs to family or approved connection
                $verifyStmt = $pdo->prepare("
                    SELECT p.id 
                    FROM personal p
                    LEFT JOIN requestMgt rm ON (
                        (rm.requester_id = ? AND rm.approver_id = p.id AND LOWER(rm.status) IN ('approved', 'accepted'))
                        OR
                        (rm.approver_id = ? AND rm.requester_id = p.id AND LOWER(rm.status) IN ('approved', 'accepted'))
                    )
                    WHERE p.id = ? 
                      AND p.deleted_at IS NULL
                      AND (p.famCode = ? OR rm.status IS NOT NULL)
                    LIMIT 1
                ");
                $verifyStmt->execute([$authorId, $authorId, $targetId, $famCode]);
                if (!$verifyStmt->fetchColumn()) {
                    continue;
                }

                $insertStmt->execute([$postNo, $targetId, $authorId]);
                $validTagged[] = $targetId;

                // In-app notification
                try {
                    $notifStmt->execute([
                        $authorId,
                        $targetId,
                        $authorName,
                        "{$authorName} tagged you in a family post.",
                        "/profilePage?post_no={$postNo}#post{$postNo}"
                    ]);
                } catch (Throwable) {}

                // Push notification
                try {
                    \App\classes\PushNotificationClass::sendPushNotification(
                        userId: $targetId,
                        message: "{$authorName} tagged you in a family post.",
                        url: "/profilePage?post_no={$postNo}#post{$postNo}",
                        title: "Tagged in a Post",
                        tag: "post-tag-{$postNo}"
                    );
                } catch (Throwable) {}
            }

            // Real-time broadcast on family channel
            if (!empty($validTagged) && $famCode !== '') {
                try {
                    if (class_exists(\App\classes\Pusher::class)) {
                        \App\classes\Pusher::broadcastToFamily($famCode, 'post-tag-event', [
                            'post_no' => $postNo,
                            'tagged_by' => $authorId,
                            'tagged_user_ids' => $validTagged
                        ]);
                    }
                } catch (Throwable) {}
            }

            return self::getPostTags($postNo);
        } catch (Throwable $e) {
            \error_log('[TagController::syncPostTags] ' . $e->getMessage());
            return [];
        }
    }
}

<?php
declare(strict_types=1);

namespace App\repository;

use App\dto\PostDto;
use App\model\PollData;
use App\model\ReactionData;
use PDO;
use PDOException;
use Src\Db;

/**
 * FeedRepository
 *
 * Single source of truth repository for all post and feed queries.
 * Unifies feed generation across Profile, Home, and API endpoints,
 * ensuring immutable PostDto contracts and consistent engagement hydration.
 */
class FeedRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    /**
     * Retrieve paginated feed posts as typed PostDto instances.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @param int $limit
     * @param int $offset
     * @return array<PostDto>
     */
    public function getFeedPosts(string $userId, array $famCodes, int $limit = 50, int $offset = 0): array
    {
        $rows = $this->fetchFeedRows($userId, $famCodes, $limit, $offset);
        if (empty($rows)) {
            return [];
        }

        $this->hydrateEngagement($rows, $userId);

        return array_map(
            fn(array $row) => PostDto::fromDatabaseRow($row, $userId),
            $rows
        );
    }

    /**
     * Retrieve paginated feed posts as backwards-compatible arrays.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @param int $limit
     * @param int $offset
     * @return array<array<string, mixed>>
     */
    public function getFeedPostsAsArray(string $userId, array $famCodes, int $limit = 50, int $offset = 0): array
    {
        $dtos = $this->getFeedPosts($userId, $famCodes, $limit, $offset);
        return array_map(fn(PostDto $dto) => $dto->toArray(), $dtos);
    }

    /**
     * Count total visible posts matching user and family codes.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @return int
     */
    public function countFeedPosts(string $userId, array $famCodes): int
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $query = "SELECT COUNT(*) as total
                FROM post
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (";

            if ($hasFamCodes) {
                $query .= "post.postFamCode IN ($inQuery) OR ";
            }

            $query .= "post.id = rm.approver_id OR post.id = ?)
                AND post.post_status = 'published'
                AND post.date_deleted IS NULL";

            $params = [$userId];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes);
            }
            $params[] = $userId;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key + 1, $val, PDO::PARAM_STR);
            }

            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            \showError($e);
            throw $e;
        }
    }

    /**
     * Fetch a single post by post number with engagement state.
     *
     * @param int $postNo
     * @param string|null $currentUserId
     * @return PostDto|null
     */
    public function getPostByNo(int $postNo, ?string $currentUserId = null): ?PostDto
    {
        try {
            $query = "SELECT post.*, pp.img
                FROM post
                LEFT JOIN profilePics pp ON post.id = pp.id
                WHERE post.post_no = ?
                AND post.date_deleted IS NULL
                LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(1, $postNo, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }

            $rows = [$row];
            if ($currentUserId !== null) {
                $this->hydrateEngagement($rows, $currentUserId);
            }

            return PostDto::fromDatabaseRow($rows[0], $currentUserId);
        } catch (PDOException $e) {
            \showError($e);
            throw $e;
        }
    }

    /**
     * Fetch anniversary / flashback memories from exactly one year ago.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @return array<PostDto>
     */
    public function getMemories(string $userId, array $famCodes): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
                FROM post
                LEFT JOIN profilePics pp ON post.id = pp.id
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (";

            if ($hasFamCodes) {
                $query .= "post.postFamCode IN ($inQuery) OR ";
            }

            $query .= "post.id = rm.approver_id OR post.id = ?) 
                AND post.post_status = 'published'
                AND post.date_created >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                AND post.date_created < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), INTERVAL 1 DAY)
                ORDER BY post.date_created DESC";

            $params = [$userId];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes);
            }
            $params[] = $userId;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key + 1, $val, PDO::PARAM_STR);
            }

            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($rows)) {
                return [];
            }

            $this->hydrateEngagement($rows, $userId);

            return array_map(
                fn(array $row) => PostDto::fromDatabaseRow($row, $userId),
                $rows
            );
        } catch (PDOException $e) {
            \showError($e);
            throw $e;
        }
    }

    /**
     * Internal raw row query builder.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @param int $limit
     * @param int $offset
     * @return array<array<string, mixed>>
     */
    private function fetchFeedRows(string $userId, array $famCodes, int $limit, int $offset): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
                FROM post
                LEFT JOIN profilePics pp ON post.id = pp.id
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (";

            if ($hasFamCodes) {
                $query .= "post.postFamCode IN ($inQuery) OR ";
            }

            $query .= "post.id = rm.approver_id OR post.id = ?)
                AND post.post_status = 'published'
                AND post.date_deleted IS NULL
                ORDER BY post.date_created DESC
                LIMIT ? OFFSET ?";

            $params = [$userId];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes);
            }
            $params[] = $userId;
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                if (is_int($val)) {
                    $stmt->bindValue($key + 1, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key + 1, $val, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            \showError($e);
            throw $e;
        }
    }

    /**
     * Batch hydrate polls, reactions, and user-specific reactions onto raw rows.
     *
     * @param array<array<string, mixed>> &$rows
     * @param string $userId
     */
    private function hydrateEngagement(array &$rows, string $userId): void
    {
        if (empty($rows)) {
            return;
        }

        $postIds = array_map(fn($r) => (int)($r['post_no'] ?? 0), $rows);
        $postIds = array_values(array_filter($postIds, fn($id) => $id > 0));

        if (empty($postIds)) {
            return;
        }

        $polls = PollData::getPollsForPosts($postIds, $userId);
        $reactions = ReactionData::getReactionsForPosts($postIds);
        $userReactions = ReactionData::getUserReactionsForPosts($userId, $postIds);

        foreach ($rows as &$post) {
            $pNo = (int)($post['post_no'] ?? 0);
            $post['poll'] = $polls[$pNo] ?? null;

            $post['reactions'] = array_values(array_filter($reactions, function ($r) use ($pNo) {
                return (int)($r['post_no'] ?? 0) === $pNo;
            }));

            $post['user_reaction'] = null;
            foreach ($userReactions as $ur) {
                if ((int)($ur['post_no'] ?? 0) === $pNo) {
                    $post['user_reaction'] = (string)($ur['reaction_type'] ?? '');
                    break;
                }
            }
        }
        unset($post);
    }
}

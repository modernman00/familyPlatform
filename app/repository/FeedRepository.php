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
     * @param string $filter 'all'|'tagged'
     * @return array<PostDto>
     */
    public function getFeedPosts(string $userId, array $famCodes, int $limit = 50, int $offset = 0, string $filter = ''): array
    {
        $rows = $this->fetchFeedRows($userId, $famCodes, $limit, $offset, $filter);
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
     * @param string $filter 'all'|'tagged'
     * @return array<array<string, mixed>>
     */
    public function getFeedPostsAsArray(string $userId, array $famCodes, int $limit = 50, int $offset = 0, string $filter = ''): array
    {
        $dtos = $this->getFeedPosts($userId, $famCodes, $limit, $offset, $filter);
        return array_map(fn(PostDto $dto) => $dto->toArray(), $dtos);
    }

    /**
     * Count total visible posts matching user and family codes.
     *
     * @param string $userId
     * @param array<string> $famCodes
     * @param string $filter
     * @return int
     */
    public function countFeedPosts(string $userId, array $famCodes, string $filter = ''): int
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $targetCheck = $hasFamCodes
                ? "EXISTS (SELECT 1 FROM post_family_targets pft WHERE pft.post_no = post.post_no AND pft.family_code IN ($inQuery))"
                : "1 = 0";

            $legacyCodeCheck = $hasFamCodes
                ? "post.postFamCode IN ($inQuery)"
                : "1 = 0";

            $query = "SELECT COUNT(*) as total
                FROM post
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (
                    post.id = ?
                    OR EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_no = post.post_no AND pt.user_id = ?)
                    OR post.id = ?
                    OR (
                        EXISTS (SELECT 1 FROM post_family_targets pft_all WHERE pft_all.post_no = post.post_no)
                        AND {$targetCheck}
                    )
                    OR (
                        NOT EXISTS (SELECT 1 FROM post_family_targets pft_none WHERE pft_none.post_no = post.post_no)
                        AND (
                            {$legacyCodeCheck}
                            OR post.id = rm.approver_id
                        )
                    )
                )
                AND post.post_status = 'published'
                AND post.date_deleted IS NULL";

            $params = [
                $userId,
                $userId,
                $userId,
                \App\services\FamilyPlatformAccountService::getAccountId()
            ];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes, $cleanedCodes);
            }

            if ($filter === 'tagged') {
                $query .= " AND EXISTS (SELECT 1 FROM post_tags pt_filter WHERE pt_filter.post_no = post.post_no AND pt_filter.user_id = ?)";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key + 1, $val, PDO::PARAM_STR);
            }

            $stmt->execute();
            return (int)$stmt->fetchColumn();
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
                $audienceSvc = new \App\services\FamilyPostAudienceService($this->db);
                if (!$audienceSvc->canUserAccessPost($postNo, $currentUserId)) {
                    return null;
                }
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

            $targetCheck = $hasFamCodes
                ? "EXISTS (SELECT 1 FROM post_family_targets pft WHERE pft.post_no = post.post_no AND pft.family_code IN ($inQuery))"
                : "1 = 0";

            $legacyCodeCheck = $hasFamCodes
                ? "post.postFamCode IN ($inQuery)"
                : "1 = 0";

            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
                FROM post
                LEFT JOIN profilePics pp ON post.id = pp.id
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (
                    post.id = ?
                    OR post.id = ?
                    OR (
                        EXISTS (SELECT 1 FROM post_family_targets pft_all WHERE pft_all.post_no = post.post_no)
                        AND {$targetCheck}
                    )
                    OR (
                        NOT EXISTS (SELECT 1 FROM post_family_targets pft_none WHERE pft_none.post_no = post.post_no)
                        AND (
                            {$legacyCodeCheck}
                            OR post.id = rm.approver_id
                        )
                    )
                ) 
                AND post.post_status = 'published'
                AND post.date_created >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                AND post.date_created < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), INTERVAL 1 DAY)
                ORDER BY post.date_created DESC";

            $params = [
                $userId,
                $userId,
                \App\services\FamilyPlatformAccountService::getAccountId()
            ];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes, $cleanedCodes);
            }

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
     * @param string $filter
     * @return array<array<string, mixed>>
     */
    private function fetchFeedRows(string $userId, array $famCodes, int $limit, int $offset, string $filter = ''): array
    {
        try {
            $cleanedCodes = array_values(array_filter(array_map('strval', $famCodes), fn($c) => $c !== ''));
            $hasFamCodes = !empty($cleanedCodes);
            $inQuery = $hasFamCodes ? implode(',', array_fill(0, count($cleanedCodes), '?')) : "''";

            $targetCheck = $hasFamCodes
                ? "EXISTS (SELECT 1 FROM post_family_targets pft WHERE pft.post_no = post.post_no AND pft.family_code IN ($inQuery))"
                : "1 = 0";

            $legacyCodeCheck = $hasFamCodes
                ? "post.postFamCode IN ($inQuery)"
                : "1 = 0";

            $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
                FROM post
                LEFT JOIN profilePics pp ON post.id = pp.id
                LEFT JOIN (
                    SELECT requester_id, approver_id, status, requesterCode
                    FROM requestMgt
                    WHERE requester_id IS NOT NULL AND requester_id = ?
                ) AS rm ON post.id = rm.approver_id
                WHERE (
                    post.id = ?
                    OR EXISTS (SELECT 1 FROM post_tags pt WHERE pt.post_no = post.post_no AND pt.user_id = ?)
                    OR post.id = ?
                    OR (
                        EXISTS (SELECT 1 FROM post_family_targets pft_all WHERE pft_all.post_no = post.post_no)
                        AND {$targetCheck}
                    )
                    OR (
                        NOT EXISTS (SELECT 1 FROM post_family_targets pft_none WHERE pft_none.post_no = post.post_no)
                        AND (
                            {$legacyCodeCheck}
                            OR post.id = rm.approver_id
                        )
                    )
                )
                AND post.post_status = 'published'
                AND post.date_deleted IS NULL";

            $params = [
                $userId,
                $userId,
                $userId,
                \App\services\FamilyPlatformAccountService::getAccountId()
            ];
            if ($hasFamCodes) {
                $params = array_merge($params, $cleanedCodes, $cleanedCodes);
            }

            if ($filter === 'tagged') {
                $query .= " AND EXISTS (SELECT 1 FROM post_tags pt_filter WHERE pt_filter.post_no = post.post_no AND pt_filter.user_id = ?)";
                $params[] = $userId;
            }

            $query .= " ORDER BY post.date_created DESC LIMIT ? OFFSET ?";
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
        try {
            $comparisons = \App\model\PostComparison::getForPosts($postIds);
        } catch (\Throwable $cmpEx) {
            $comparisons = [];
            \error_log('[FeedRepository::hydrateEngagement comparisons] ' . $cmpEx->getMessage());
        }
        $reactions = ReactionData::getReactionsForPosts($postIds);
        $userReactions = ReactionData::getUserReactionsForPosts($userId, $postIds);

        // Batch hydrate post tags
        $postTagsByPostNo = [];
        try {
            $tagPlaceholders = implode(',', array_fill(0, count($postIds), '?'));
            $tagSql = sprintf(
                "SELECT pt.post_no, pt.user_id, pt.tagged_by, p.firstName, p.lastName, pp.img 
                FROM post_tags pt 
                JOIN personal p ON pt.user_id = p.id 
                LEFT JOIN profilePics pp ON pt.user_id = pp.id 
                WHERE pt.post_no IN (%s)
                ORDER BY pt.id ASC",
                $tagPlaceholders
            );
            $tagStmt = $this->db->prepare($tagSql);
            $tagStmt->execute($postIds);
            $tagRows = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

            // Collect all unique tagged user IDs for batch kinship resolution
            $taggedUserIds = array_values(array_unique(array_filter(array_map('strval', array_column($tagRows, 'user_id')))));
            $kinshipMap = $this->batchResolveKinship($userId, $taggedUserIds);

            foreach ($tagRows as $tRow) {
                $pNo = (int)$tRow['post_no'];
                $uId = (string)$tRow['user_id'];
                $img = $tRow['img'] ?? null;
                $profImg = '/resources/images/profile/avatarM.png';
                if (!empty($img)) {
                    $profImg = (str_starts_with((string)$img, '/') || str_starts_with((string)$img, 'http'))
                        ? (string)$img
                        : "/resources/images/profile/{$img}";
                }
                $postTagsByPostNo[$pNo][] = [
                    'user_id' => $uId,
                    'fullName' => trim(($tRow['firstName'] ?? '') . ' ' . ($tRow['lastName'] ?? '')),
                    'firstName' => (string)($tRow['firstName'] ?? ''),
                    'lastName' => (string)($tRow['lastName'] ?? ''),
                    'profileImg' => $profImg,
                    'tagged_by' => (string)$tRow['tagged_by'],
                    'kinship' => $kinshipMap[$uId] ?? 'Family Member',
                ];
            }
        } catch (\Throwable $tagEx) {
            \error_log('[FeedRepository::hydrateEngagement tags] ' . $tagEx->getMessage());
        }

        // Batch hydrate voice capsules from family_nodes as fallback for living oral history
        $authorIds = array_values(array_unique(array_filter(array_map('strval', array_column($rows, 'id')))));
        $nodeVoiceMap = [];
        if (!empty($authorIds)) {
            try {
                $voicePlaceholders = implode(',', array_fill(0, count($authorIds), '?'));
                $vSql = sprintf(
                    "SELECT user_id, voice_capsule_url 
                     FROM family_nodes 
                     WHERE user_id IN (%s) 
                       AND voice_capsule_url IS NOT NULL 
                       AND voice_capsule_url != ''",
                    $voicePlaceholders
                );
                $vStmt = $this->db->prepare($vSql);
                $vStmt->execute($authorIds);
                while ($vRow = $vStmt->fetch(PDO::FETCH_ASSOC)) {
                    $nodeVoiceMap[(string)$vRow['user_id']] = (string)$vRow['voice_capsule_url'];
                }
            } catch (\Throwable $ve) {
                \error_log('[FeedRepository::hydrateEngagement voice] ' . $ve->getMessage());
            }
        }

        // Batch hydrate Two Truths & a Lie games
        $twoTruthsGames = [];
        try {
            $twoTruthsGames = \App\services\TwoTruthsGameService::getGamesForPosts($postIds, $userId);
        } catch (\Throwable $ttEx) {
            \error_log('[FeedRepository::hydrateEngagement twoTruths] ' . $ttEx->getMessage());
        }

        foreach ($rows as &$post) {
            $pNo = (int)($post['post_no'] ?? 0);
            $post['poll'] = $polls[$pNo] ?? null;
            $post['comparison'] = $comparisons[$pNo] ?? null;
            $post['two_truths'] = $twoTruthsGames[$pNo] ?? null;
            $post['post_tags'] = $postTagsByPostNo[$pNo] ?? [];

            // Fallback to author's family tree voice capsule if not directly set on post
            if (empty($post['voice_capsule_url'])) {
                $postAuthorId = (string)($post['id'] ?? '');
                if (isset($nodeVoiceMap[$postAuthorId])) {
                    $post['voice_capsule_url'] = $nodeVoiceMap[$postAuthorId];
                }
            }

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

            // Hydrate OpenGraph link preview (from fast cache or auto-recovery)
            $msg = (string)($post['postMessage'] ?? ($post['content'] ?? ''));
            $hasExternalUrl = $msg !== '' && (str_contains($msg, 'http://') || str_contains($msg, 'https://'));
            $post['link_preview'] = $hasExternalUrl ? \App\services\LinkPreviewService::getPreviewForPostText($msg) : null;

            // Promote linked photos (Google Photos, iCloud, direct image links) to native post images (Multi-Image Support)
            $hasLocalImg = false;
            $existingExternalImages = [];
            $existingExternalVideos = [];
            for ($i = 0; $i < 6; $i++) {
                $imgVal = (string)($post['post_img' . $i] ?? '');
                if (!empty($imgVal)) {
                    if (!str_starts_with($imgVal, 'http://') && !str_starts_with($imgVal, 'https://')) {
                        $hasLocalImg = true;
                    } else {
                        if (\App\classes\VideoParser::isVideoUrl($imgVal)) {
                            $existingExternalVideos[] = $imgVal;
                        } else {
                            $existingExternalImages[] = $imgVal;
                        }
                    }
                }
            }

            $alreadyHasExtractedMedia = !empty($existingExternalImages) || !empty($existingExternalVideos);
            if (!$hasLocalImg && !$alreadyHasExtractedMedia && $hasExternalUrl) {
                $extractedAssets = \App\services\LinkPreviewService::extractPhotoAssetsFromText($msg, 6);
                $finalImages = !empty($extractedAssets['images']) ? $extractedAssets['images'] : $existingExternalImages;
                $finalVideos = !empty($extractedAssets['videos']) ? $extractedAssets['videos'] : $existingExternalVideos;

                if (!empty($finalImages)) {
                    $post['images'] = $finalImages;
                    foreach ($finalImages as $slotIdx => $imgUrl) {
                        $post['post_img' . $slotIdx] = $imgUrl;
                    }
                }

                if (!empty($finalVideos)) {
                    $hook = \App\dto\PostDto::deriveHookFromMessage($msg ?? ($post['postMessage'] ?? ''));
                    $formattedVideos = [];
                    foreach ($finalVideos as $vUrl) {
                        $thumb = null;
                        if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|ggpht\.com)/#i', $vUrl)) {
                            $thumb = preg_replace('/=.*$/i', '=s1600-no', $vUrl);
                        }
                        $formattedVideos[] = [
                            'type' => 'direct',
                            'videoId' => null,
                            'thumbnailUrl' => $thumb,
                            'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($vUrl),
                            'originalUrl' => $vUrl,
                            'trimStart' => null,
                            'trimEnd' => null,
                            'hookText' => $hook,
                            'aspectMode' => 'reel',
                        ];
                    }
                    $post['videos'] = $formattedVideos;
                    $post['video'] = $formattedVideos[0];
                }

                if (!empty($finalImages) || !empty($finalVideos)) {
                    if (!empty($post['link_preview']['is_google_photo']) || !empty($post['link_preview']['is_photo_link'])) {
                        $post['link_preview'] = null;
                    }
                } elseif ($post['link_preview'] === null && ($extractedAssets['preview'] ?? null) !== null) {
                    $post['link_preview'] = $extractedAssets['preview'];
                }

                if (!empty($extractedAssets['cleaned_message'])) {
                    $post['displayMessage'] = $extractedAssets['cleaned_message'];
                } elseif (!empty($finalImages) || !empty($finalVideos)) {
                    $post['displayMessage'] = \App\services\LinkPreviewService::cleanPhotoLinkFromMessage(
                        $msg,
                        array_merge($finalImages, $finalVideos)
                    );
                }
            } elseif (!$hasLocalImg && $alreadyHasExtractedMedia) {
                if (!empty($existingExternalImages)) {
                    $post['images'] = $existingExternalImages;
                }
                if (!empty($existingExternalVideos) && empty($post['videos'])) {
                    $hook = \App\dto\PostDto::deriveHookFromMessage($msg ?? ($post['postMessage'] ?? ''));
                    $formattedVideos = [];
                    foreach ($existingExternalVideos as $vUrl) {
                        $thumb = null;
                        if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|ggpht\.com)/#i', $vUrl)) {
                            $thumb = preg_replace('/=.*$/i', '=s1600-no', $vUrl);
                        }
                        $formattedVideos[] = [
                            'type' => 'direct',
                            'videoId' => null,
                            'thumbnailUrl' => $thumb,
                            'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($vUrl),
                            'originalUrl' => $vUrl,
                            'trimStart' => null,
                            'trimEnd' => null,
                            'hookText' => $hook,
                            'aspectMode' => 'reel',
                        ];
                    }
                    $post['videos'] = $formattedVideos;
                    $post['video'] = $formattedVideos[0];
                }
            } elseif (!empty($existingExternalVideos) && empty($post['videos'])) {
                $hook = \App\dto\PostDto::deriveHookFromMessage($msg ?? ($post['postMessage'] ?? ''));
                $formattedVideos = [];
                foreach ($existingExternalVideos as $vUrl) {
                    $thumb = null;
                    if (preg_match('#^https://(?:[a-zA-Z0-9_-]+\.)?(?:googleusercontent\.com|ggpht\.com)/#i', $vUrl)) {
                        $thumb = preg_replace('/=.*$/i', '=s1600-no', $vUrl);
                    }
                    $formattedVideos[] = [
                        'type' => 'direct',
                        'videoId' => null,
                        'thumbnailUrl' => $thumb,
                        'embedUrl' => \App\services\LinkPreviewService::getProxiedVideoUrl($vUrl),
                        'originalUrl' => $vUrl,
                        'trimStart' => null,
                        'trimEnd' => null,
                        'hookText' => $hook,
                        'aspectMode' => 'reel',
                    ];
                }
                $post['videos'] = $formattedVideos;
                $post['video'] = $formattedVideos[0];
            }
        }
        unset($post);
    }

    /**
     * Efficiently batch-resolves kinship labels between viewer and tagged relatives.
     *
     * @param string $currentUserId
     * @param array<string> $targetUserIds
     * @return array<string, string> Map of userId => kinship title
     */
    public function batchResolveKinship(string $currentUserId, array $targetUserIds): array
    {
        if (empty($targetUserIds) || $currentUserId === '') {
            return [];
        }

        $results = [];
        $unresolved = [];

        foreach ($targetUserIds as $tid) {
            $tidStr = (string)$tid;
            if ($tidStr === $currentUserId) {
                $results[$tidStr] = 'You';
            } else {
                $unresolved[] = $tidStr;
            }
        }

        if (empty($unresolved)) {
            return $results;
        }

        try {
            // 1. Primary Authoritative Resolution: DAG BFS Traversal via KinshipCalculatorService
            // Matches the exact genealogical calculation used by the interactive Family Tree.
            $egoNodesStmt = $this->db->prepare(
                "SELECT id, family_code, generation_level FROM family_nodes WHERE user_id = ? AND COALESCE(is_erased, 0) = 0 ORDER BY id ASC"
            );
            $egoNodesStmt->execute([$currentUserId]);
            $egoNodes = $egoNodesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Fetch personal names of target users for secondary name-matching if a tree node is unlinked
            $inTarget = implode(',', array_fill(0, count($unresolved), '?'));
            $personalSql = sprintf(
                "SELECT id, firstName, lastName, gender FROM personal WHERE id IN (%s)",
                $inTarget
            );
            $personalStmt = $this->db->prepare($personalSql);
            $personalStmt->execute($unresolved);
            $targetPersonalMap = [];
            while ($pRow = $personalStmt->fetch(PDO::FETCH_ASSOC)) {
                $pId = (string)($pRow['id'] ?? '');
                if ($pId !== '') {
                    $targetPersonalMap[$pId] = [
                        'first' => strtolower(trim((string)($pRow['firstName'] ?? ''))),
                        'last'  => strtolower(trim((string)($pRow['lastName'] ?? ''))),
                        'gender' => strtolower(trim((string)($pRow['gender'] ?? ''))),
                    ];
                }
            }

            $visitedFamCodes = [];
            foreach ($egoNodes as $egoRow) {
                $egoNodeId = (int)($egoRow['id'] ?? 0);
                $famCode = trim((string)($egoRow['family_code'] ?? ''));
                if ($egoNodeId <= 0 || $famCode === '' || isset($visitedFamCodes[$famCode])) {
                    continue;
                }
                $visitedFamCodes[$famCode] = true;

                $nodesStmt = $this->db->prepare(
                    "SELECT id, user_id, first_name, last_name, gender, generation_level, is_deceased, is_placeholder, placeholder_label
                     FROM family_nodes
                     WHERE family_code = ? AND COALESCE(is_erased, 0) = 0
                     ORDER BY generation_level ASC, id ASC"
                );
                $nodesStmt->execute([$famCode]);
                $rawNodes = $nodesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
                if (empty($rawNodes)) {
                    continue;
                }

                $unionsStmt = $this->db->prepare(
                    "SELECT id, partner_1_id, partner_2_id, union_type, is_current FROM family_unions WHERE family_code = ?"
                );
                $unionsStmt->execute([$famCode]);
                $rawUnions = $unionsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $childrenStmt = $this->db->prepare(
                    "SELECT fnc.union_id, fnc.child_id, fnc.relationship_type
                     FROM family_node_children fnc
                     INNER JOIN family_unions fu ON fu.id = fnc.union_id
                     WHERE fu.family_code = ?"
                );
                $childrenStmt->execute([$famCode]);
                $rawChildren = $childrenStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                $decorated = \App\services\KinshipCalculatorService::calculateEgoKinship([
                    'nodes'    => $rawNodes,
                    'unions'   => $rawUnions,
                    'children' => $rawChildren,
                ], $egoNodeId);

                // Pass A: Direct user_id match on the calculated family graph
                foreach ($decorated as $dNode) {
                    $dUid = trim((string)($dNode['user_id'] ?? ''));
                    if ($dUid === '' || !in_array($dUid, $unresolved, true)) {
                        continue;
                    }
                    $rel = trim((string)($dNode['ego_relation'] ?? ''));
                    if ($rel !== '' && !in_array($rel, ['Relative', 'Extended Relative', 'You (Primary Member)'], true)) {
                        $results[$dUid] = $rel;
                    }
                }

                // Pass B: Name match within the same family graph for nodes whose user_id is not yet linked
                foreach ($unresolved as $uId) {
                    if (isset($results[$uId]) || !isset($targetPersonalMap[$uId])) {
                        continue;
                    }
                    $tFirst = $targetPersonalMap[$uId]['first'];
                    $tLast  = $targetPersonalMap[$uId]['last'];
                    if ($tFirst === '') {
                        continue;
                    }
                    foreach ($decorated as $dNode) {
                        $nFirst = strtolower(trim((string)($dNode['first_name'] ?? '')));
                        $nLast  = strtolower(trim((string)($dNode['last_name'] ?? '')));
                        if ($nFirst === $tFirst && ($tLast === '' || $nLast === '' || $nLast === $tLast)) {
                            $rel = trim((string)($dNode['ego_relation'] ?? ''));
                            if ($rel !== '' && !in_array($rel, ['Relative', 'Extended Relative', 'You (Primary Member)'], true)) {
                                $results[$uId] = $rel;
                                break;
                            }
                        }
                    }
                }
            }

            // 2. Secondary Fallback: Check legacy tables for any remaining unresolved targets
            $stillUnresolved = array_values(array_filter($unresolved, fn($id) => !isset($results[$id])));
            if (!empty($stillUnresolved)) {
                $inSib = implode(',', array_fill(0, count($stillUnresolved), '?'));
                $sibSql = sprintf(
                    "SELECT sibling_linked FROM sibling WHERE id = ? AND sibling_linked IN (%s)",
                    $inSib
                );
                $sibStmt = $this->db->prepare($sibSql);
                $sibStmt->execute(array_merge([$currentUserId], $stillUnresolved));
                while ($row = $sibStmt->fetch(PDO::FETCH_ASSOC)) {
                    $linkedId = (string)($row['sibling_linked'] ?? '');
                    if ($linkedId !== '') {
                        $g = $targetPersonalMap[$linkedId]['gender'] ?? '';
                        $results[$linkedId] = $g === 'male' ? 'Brother' : ($g === 'female' ? 'Sister' : 'Sibling');
                    }
                }

                $childSql = sprintf(
                    "SELECT children_linked FROM children WHERE id = ? AND children_linked IN (%s)",
                    $inSib
                );
                $childStmt = $this->db->prepare($childSql);
                $childStmt->execute(array_merge([$currentUserId], $stillUnresolved));
                while ($row = $childStmt->fetch(PDO::FETCH_ASSOC)) {
                    $linkedId = (string)($row['children_linked'] ?? '');
                    if ($linkedId !== '' && !isset($results[$linkedId])) {
                        $g = $targetPersonalMap[$linkedId]['gender'] ?? '';
                        $results[$linkedId] = $g === 'male' ? 'Son' : ($g === 'female' ? 'Daughter' : 'Child');
                    }
                }

                $parentSql = sprintf(
                    "SELECT id FROM children WHERE id IN (%s) AND children_linked = ?",
                    $inSib
                );
                $parentStmt = $this->db->prepare($parentSql);
                $parentStmt->execute(array_merge($stillUnresolved, [$currentUserId]));
                while ($row = $parentStmt->fetch(PDO::FETCH_ASSOC)) {
                    $parentId = (string)($row['id'] ?? '');
                    if ($parentId !== '' && !isset($results[$parentId])) {
                        $g = $targetPersonalMap[$parentId]['gender'] ?? '';
                        $results[$parentId] = $g === 'male' ? 'Father' : ($g === 'female' ? 'Mother' : 'Parent');
                    }
                }
            }

            // 3. Tertiary Fallback: Same-family generation_level delta (Note: -1 is parent/older, +1 is child/younger)
            $stillUnresolved = array_values(array_filter($unresolved, fn($id) => !isset($results[$id])));
            if (!empty($stillUnresolved) && !empty($egoNodes)) {
                $viewerFamCode = trim((string)($egoNodes[0]['family_code'] ?? ''));
                $viewerGenInt  = (int)($egoNodes[0]['generation_level'] ?? 0);
                if ($viewerFamCode !== '') {
                    $inNode = implode(',', array_fill(0, count($stillUnresolved), '?'));
                    $targetNodeSql = sprintf(
                        "SELECT user_id, generation_level, gender FROM family_nodes WHERE family_code = ? AND user_id IN (%s) AND COALESCE(is_erased, 0) = 0",
                        $inNode
                    );
                    $targetNodeStmt = $this->db->prepare($targetNodeSql);
                    $targetNodeStmt->execute(array_merge([$viewerFamCode], $stillUnresolved));
                    while ($tNode = $targetNodeStmt->fetch(PDO::FETCH_ASSOC)) {
                        $tUid = (string)($tNode['user_id'] ?? '');
                        $tGen = (int)($tNode['generation_level'] ?? 0);
                        $gender = strtolower((string)($tNode['gender'] ?? ''));
                        // In family_nodes: lower generation_level = older generation (-1 = Parent, 0 = Ego, +1 = Child/Nephew)
                        $diff = $tGen - $viewerGenInt;

                        if ($diff === -1) {
                            $results[$tUid] = $gender === 'female' ? 'Aunt' : ($gender === 'male' ? 'Uncle' : 'Elder Kin');
                        } elseif ($diff <= -2) {
                            $results[$tUid] = $gender === 'female' ? 'Grandmother' : ($gender === 'male' ? 'Grandfather' : 'Grandparent');
                        } elseif ($diff === 1) {
                            $results[$tUid] = $gender === 'female' ? 'Niece' : ($gender === 'male' ? 'Nephew' : 'Niece/Nephew');
                        } elseif ($diff >= 2) {
                            $results[$tUid] = $gender === 'female' ? 'Granddaughter' : ($gender === 'male' ? 'Grandson' : 'Grandchild');
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \error_log('[FeedRepository::batchResolveKinship] ' . $e->getMessage());
        }

        // Fill remaining with 'Family Member'
        foreach ($targetUserIds as $tid) {
            $tidStr = (string)$tid;
            if (!isset($results[$tidStr])) {
                $results[$tidStr] = 'Family Member';
            }
        }

        return $results;
    }
}

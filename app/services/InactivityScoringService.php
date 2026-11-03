<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;

/**
 * Autonomous Inactivity & Engagement Depth Scoring Service
 *
 * Evaluates registered members against the Engagement Depth Score (EDS) metric:
 *   EDS = (family_nodes * 10) + (posts * 5) + (reactions * 3) + (logins_30d * 2)
 *
 * Categorises members into progressive dormancy tiers:
 * - Tier 1: Ghost Pioneer (Registered 48h–7d ago, EDS = 0, 0 nodes, 0 posts)
 * - Tier 2: Stalled Explorer (Registered 7d–14d ago, EDS <= 10, <= 1 node, 0 posts)
 * - Tier 3: Dormant Banter (Registered 14d–30d ago, no activity in last 14 days)
 * - Tier 4: Deep Hibernation (Registered 30d+ ago, no activity in last 30 days)
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class InactivityScoringService
{
    public const EVENT_TIER1_GHOST    = 'inactivity_tier1_one_name';
    public const EVENT_TIER2_DELEGATE = 'inactivity_tier2_delegate_copilot';
    public const EVENT_TIER3_BANTER   = 'inactivity_tier3_sunday_banter';
    public const EVENT_TIER4_DEEP     = 'inactivity_tier4_deep_dormant_magic';

    /**
     * Compute comprehensive Engagement Depth Score (EDS) and inactivity metadata for a member.
     *
     * @param string $userId
     * @return array{
     *     user_id: string,
     *     eds: int,
     *     node_count: int,
     *     post_count: int,
     *     reaction_count: int,
     *     login_count_30d: int,
     *     days_since_registration: int,
     *     days_since_last_activity: int,
     *     tier: string
     * }
     */
    public static function calculateScore(string $userId): array
    {
        $db = Db::connect2();

        // 1. Account created_at and status
        $accStmt = $db->prepare('SELECT id, created_at, status, deleted_at FROM account WHERE id = ? LIMIT 1');
        $accStmt->execute([$userId]);
        /** @var array<string, mixed>|false $acc */
        $acc = $accStmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc) {
            return [
                'user_id'                  => $userId,
                'eds'                      => 0,
                'node_count'               => 0,
                'post_count'               => 0,
                'reaction_count'           => 0,
                'login_count_30d'          => 0,
                'days_since_registration'  => 0,
                'days_since_last_activity' => 0,
                'tier'                     => 'unknown',
            ];
        }

        $regTime = strtotime((string)($acc['created_at'] ?? 'now')) ?: time();
        $daysSinceReg = max(0, (int)floor((time() - $regTime) / 86400));

        // 2. Family Nodes Count
        $nStmt = $db->prepare('SELECT COUNT(*) FROM family_nodes WHERE user_id = ?');
        $nStmt->execute([$userId]);
        $nodeCount = (int)$nStmt->fetchColumn();

        // 3. Posts Count
        $pStmt = $db->prepare('SELECT COUNT(*) FROM post WHERE id = ? AND date_deleted IS NULL');
        $pStmt->execute([$userId]);
        $postCount = (int)$pStmt->fetchColumn();

        // 4. Reactions Count
        $rStmt = $db->prepare('SELECT COUNT(*) FROM post_reactions WHERE user_id = ?');
        $rStmt->execute([$userId]);
        $reactionCount = (int)$rStmt->fetchColumn();

        // 5. Recent Logins (Last 30 Days) and Last Activity Timestamp
        $lStmt = $db->prepare('
            SELECT COUNT(*), MAX(created_at) 
            FROM audit_logs 
            WHERE user_id = ? 
              AND action IN (?, ?) 
              AND created_at >= ?
        ');
        $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
        $lStmt->execute([$userId, 'success', 'magic_login_success', $thirtyDaysAgo]);
        /** @var array{0: int|string, 1: string|null} $logins */
        $logins = $lStmt->fetch(PDO::FETCH_NUM);
        $loginCount30d = (int)($logins[0] ?? 0);
        $lastLoginStr = $logins[1] ?? null;

        $lastActivityTime = $regTime;
        if ($lastLoginStr !== null) {
            $lastActivityTime = max($lastActivityTime, (int)strtotime($lastLoginStr));
        }

        $daysSinceActivity = max(0, (int)floor((time() - $lastActivityTime) / 86400));

        // Calculate EDS
        $eds = ($nodeCount * 10) + ($postCount * 5) + ($reactionCount * 3) + ($loginCount30d * 2);

        // Determine Dormancy Tier
        $tier = 'active';
        if ($nodeCount === 0 && $postCount === 0) {
            if ($daysSinceReg >= 30) {
                $tier = 'deep_dormant_30d';
            } elseif ($daysSinceReg >= 14) {
                $tier = 'dormant_banter_14d';
            } elseif ($daysSinceReg >= 7) {
                $tier = 'stalled_starter_7d';
            } elseif ($daysSinceReg >= 2) {
                $tier = 'ghost_pioneer_48h';
            }
        } elseif ($daysSinceActivity >= 30) {
            $tier = 'deep_dormant_30d';
        } elseif ($daysSinceActivity >= 14) {
            $tier = 'dormant_banter_14d';
        }

        return [
            'user_id'                  => $userId,
            'eds'                      => $eds,
            'node_count'               => $nodeCount,
            'post_count'               => $postCount,
            'reaction_count'           => $reactionCount,
            'login_count_30d'          => $loginCount30d,
            'days_since_registration'  => $daysSinceReg,
            'days_since_last_activity' => $daysSinceActivity,
            'tier'                     => $tier,
        ];
    }

    /**
     * Fetch eligible candidates for a given dormancy tier.
     *
     * @param string $eventType One of self::EVENT_TIER* constants
     * @param ?string $forceUserId Optional override for targeted execution or testing
     * @param int $limit Max records to process per batch
     * @return array<int, array<string, mixed>>
     */
    public static function fetchCandidatesForTier(
        string $eventType,
        ?string $forceUserId = null,
        int $limit = 100
    ): array {
        $db = Db::connect2();

        if ($forceUserId !== null && $forceUserId !== '') {
            $sql = '
                SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode, a.created_at
                FROM account a
                JOIN personal p ON a.id = p.id
                LEFT JOIN contact c ON a.id = c.id
                LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
                WHERE a.deleted_at IS NULL
                  AND a.status IN (\'active\', \'approved\', \'verified\')
                  AND a.id = ?
                  AND e.id IS NULL
                LIMIT 1
            ';
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventType, $forceUserId]);
            /** @var array<int, array<string, mixed>> $res */
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }

        // Tier-specific registration age ranges
        [$minAgeDate, $maxAgeDate] = match ($eventType) {
            self::EVENT_TIER1_GHOST => [
                date('Y-m-d H:i:s', strtotime('-48 hours')),
                date('Y-m-d H:i:s', strtotime('-7 days')),
            ],
            self::EVENT_TIER2_DELEGATE => [
                date('Y-m-d H:i:s', strtotime('-7 days')),
                date('Y-m-d H:i:s', strtotime('-14 days')),
            ],
            self::EVENT_TIER3_BANTER => [
                date('Y-m-d H:i:s', strtotime('-14 days')),
                date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            self::EVENT_TIER4_DEEP => [
                date('Y-m-d H:i:s', strtotime('-30 days')),
                null,
            ],
            default => [null, null],
        };

        if ($minAgeDate === null) {
            return [];
        }

        if ($maxAgeDate !== null) {
            $sql = '
                SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode, a.created_at
                FROM account a
                JOIN personal p ON a.id = p.id
                LEFT JOIN contact c ON a.id = c.id
                LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
                WHERE a.deleted_at IS NULL
                  AND a.status IN (\'active\', \'approved\', \'verified\')
                  AND a.type != ?
                  AND a.type != ?
                  AND a.created_at <= ?
                  AND a.created_at >= ?
                  AND e.id IS NULL
                ORDER BY a.created_at DESC
                LIMIT ' . (int)$limit . '
            ';
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventType, 'admin', 'super_admin', $minAgeDate, $maxAgeDate]);
        } else {
            // Tier 4 has no upper age bound
            $sql = '
                SELECT a.id, COALESCE(c.email, a.email) AS email, p.firstName, p.lastName, p.famCode, a.created_at
                FROM account a
                JOIN personal p ON a.id = p.id
                LEFT JOIN contact c ON a.id = c.id
                LEFT JOIN user_lifecycle_events e ON a.id = e.user_id AND e.event_type = ?
                WHERE a.deleted_at IS NULL
                  AND a.status IN (\'active\', \'approved\', \'verified\')
                  AND a.type != ?
                  AND a.type != ?
                  AND a.created_at <= ?
                  AND e.id IS NULL
                ORDER BY a.created_at DESC
                LIMIT ' . (int)$limit . '
            ';
            $stmt = $db->prepare($sql);
            $stmt->execute([$eventType, 'admin', 'super_admin', $minAgeDate]);
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter out members who have active engagement (e.g. node_count > 1 or post_count > 0)
        $candidates = [];
        foreach ($rows as $row) {
            $uid = (string)$row['id'];
            $scoreData = self::calculateScore($uid);

            if ($eventType === self::EVENT_TIER1_GHOST && $scoreData['node_count'] === 0 && $scoreData['post_count'] === 0) {
                $row['score_data'] = $scoreData;
                $candidates[] = $row;
            } elseif ($eventType === self::EVENT_TIER2_DELEGATE && $scoreData['node_count'] <= 1 && $scoreData['post_count'] === 0) {
                $row['score_data'] = $scoreData;
                $candidates[] = $row;
            } elseif ($eventType === self::EVENT_TIER3_BANTER && $scoreData['post_count'] === 0) {
                $row['score_data'] = $scoreData;
                $candidates[] = $row;
            } elseif ($eventType === self::EVENT_TIER4_DEEP && $scoreData['days_since_last_activity'] >= 30) {
                $row['score_data'] = $scoreData;
                $candidates[] = $row;
            }
        }

        return $candidates;
    }
}

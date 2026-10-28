<?php
declare(strict_types=1);

namespace App\services;

use Src\Select;
use PDO;
use Exception;

/**
 * Kinship Suggestion Engine — "People You May Know / Suggested Kin & In-Laws"
 *
 * V2: Optimised with SQL pre-filtering, batched mutual kin lookup,
 * and elimination of N+1 queries.
 */
final class KinshipEngineService
{
    /**
     * Compute and retrieve high-confidence suggested kin for a given user.
     *
     * Architecture:
     *   1. Fetch current user's lineage context (1 query)
     *   2. Compute excluded IDs (3 queries, batched)
     *   3. SQL pre-filter: only fetch candidates who match at least ONE signal (1 query)
     *   4. Batch-fetch mutual kin counts for ALL candidates (1 query, not N+1)
     *   5. Score in PHP memory and return top N
     *
     * Total queries: 6 (constant, regardless of user count)
     *
     * @param string|int $userId Current logged-in user ID
     * @param int $limit Maximum suggestions to return
     * @return array<int, array<string, mixed>>
     */
    public static function getSuggestedKin(string|int $userId, int $limit = 8): array
    {
        $userIdStr = (string)$userId;
        if (empty($userIdStr)) {
            return [];
        }

        // ── Check user's Radar Settings ──
        $settings = self::getRadarSettings($userIdStr);
        if (!$settings['radar_enabled']) {
            return [];
        }

        try {
            $pdo = Select::connect2();

            // ── Query 1: Current user's lineage context ──
            $userStmt = $pdo->prepare("
                SELECT p.id, p.firstName, p.lastName, p.famCode, p.familyCode2,
                       o.maiden_name, o.otherFamCode, o.father_name, o.mother_name, o.spouse_name
                FROM personal AS p
                LEFT JOIN otherFamily AS o ON p.id = o.id
                WHERE p.id = :userId LIMIT 1
            ");
            $userStmt->execute([':userId' => $userIdStr]);
            $currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentUser) {
                return [];
            }

            $userLastName     = strtoupper(trim((string)($currentUser['lastName'] ?? '')));
            $userFamCode      = strtoupper(trim((string)($currentUser['famCode'] ?? '')));
            $userFamCode2     = strtoupper(trim((string)($currentUser['familyCode2'] ?? '')));
            $userMaiden       = strtoupper(trim((string)($currentUser['maiden_name'] ?? '')));
            $userOtherFamCode = strtoupper(trim((string)($currentUser['otherFamCode'] ?? '')));

            // ── Queries 2-4: Build exclusion set (constant 3 queries) ──
            $excludedIds = self::getExcludedUserIds($pdo, $userIdStr, $userFamCode);
            $excludePlaceholders = self::buildPlaceholders($excludedIds, 'excl');

            // ── Query 5: SQL pre-filtered candidates ──
            // Only fetch users who match at least ONE kinship signal via WHERE clause.
            // This replaces the full table scan.
            $whereClauses = [];
            $params = [];

            // Signal A: Surname match
            if (!empty($userLastName)) {
                $whereClauses[] = "UPPER(p.lastName) = :sigLastName";
                $params[':sigLastName'] = $userLastName;
            }

            // Signal B & C: Maternal Lineage & Maiden Name Cross-Matching
            if ($settings['include_maternal']) {
                if (!empty($userMaiden)) {
                    $whereClauses[] = "(UPPER(p.lastName) = :sigMaiden1 OR UPPER(p.famCode) = :sigMaiden2)";
                    $params[':sigMaiden1'] = $userMaiden;
                    $params[':sigMaiden2'] = $userMaiden;
                }

                if (!empty($userLastName)) {
                    $whereClauses[] = "UPPER(o.maiden_name) = :sigCandMaiden1";
                    $params[':sigCandMaiden1'] = $userLastName;
                }
                if (!empty($userFamCode)) {
                    $whereClauses[] = "UPPER(o.maiden_name) = :sigCandMaiden2";
                    $params[':sigCandMaiden2'] = $userFamCode;
                }
            }

            // Signal D & E: Secondary family code & In-Law overlap
            if ($settings['include_inlaws']) {
                if (!empty($userFamCode2)) {
                    $whereClauses[] = "(UPPER(p.famCode) = :sigFam2a OR UPPER(p.familyCode2) = :sigFam2b)";
                    $params[':sigFam2a'] = $userFamCode2;
                    $params[':sigFam2b'] = $userFamCode2;
                }

                if (!empty($userOtherFamCode)) {
                    $whereClauses[] = "(UPPER(p.famCode) = :sigOfc1 OR UPPER(o.otherFamCode) = :sigOfc2)";
                    $params[':sigOfc1'] = $userOtherFamCode;
                    $params[':sigOfc2'] = $userOtherFamCode;
                }
            }

            // Signal F: Mutual kin (users connected to someone the current user is connected to)
            $userKinIds = self::getApprovedKinIds($pdo, $userIdStr);
            if (!empty($userKinIds)) {
                $kinPh1 = self::buildPlaceholders($userKinIds, 'kin1');
                $kinPh2 = self::buildPlaceholders($userKinIds, 'kin2');
                $kinPhStr1 = implode(',', array_keys($kinPh1));
                $kinPhStr2 = implode(',', array_keys($kinPh2));
                $whereClauses[] = "(
                    p.id IN (
                        SELECT rm.approver_id FROM requestMgt rm
                        WHERE rm.requester_id IN ({$kinPhStr1})
                          AND LOWER(rm.status) IN ('approved','accepted')
                        UNION
                        SELECT rm2.requester_id FROM requestMgt rm2
                        WHERE rm2.approver_id IN ({$kinPhStr2})
                          AND LOWER(rm2.status) IN ('approved','accepted')
                    )
                )";
                $params = array_merge($params, $kinPh1, $kinPh2);
            }

            // If no signals exist (user has no lineage data at all), return empty
            if (empty($whereClauses)) {
                return [];
            }

            $signalWhere = '(' . implode(" OR ", $whereClauses) . ')';
            $excludeWhere = '';
            if (!empty($excludePlaceholders)) {
                $exclPhStr = implode(',', array_keys($excludePlaceholders));
                $excludeWhere = "AND p.id NOT IN ({$exclPhStr})";
                $params = array_merge($params, $excludePlaceholders);
            }

            $sql = "
                SELECT p.id, p.firstName, p.lastName, p.famCode, p.familyCode2,
                       o.maiden_name, o.otherFamCode, o.father_name, o.mother_name, o.spouse_name,
                       pp.img AS profilePics
                FROM personal AS p
                LEFT JOIN otherFamily AS o ON p.id = o.id
                LEFT JOIN profilePics AS pp ON p.id = pp.id
                WHERE p.id != :currId
                  AND {$signalWhere}
                  {$excludeWhere}
            ";
            $params[':currId'] = $userIdStr;

            $candidatesStmt = $pdo->prepare($sql);
            $candidatesStmt->execute($params);
            $candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // De-duplicate candidates by ID (LEFT JOINs can produce duplicates)
            $uniqueCandidates = [];
            foreach ($candidates as $c) {
                $uniqueCandidates[(string)$c['id']] = $c;
            }
            $candidates = array_values($uniqueCandidates);

            // ── Query 6: Batch mutual kin counts (eliminates N+1) ──
            $mutualKinCounts = [];
            if (!empty($userKinIds) && !empty($candidates)) {
                $mutualKinCounts = self::batchMutualKinCounts($pdo, $userKinIds, array_column($candidates, 'id'));
            }

            // ── Score in-memory ──
            $scoredSuggestions = [];

            foreach ($candidates as $cand) {
                $candId       = (string)$cand['id'];
                $candLastName = strtoupper(trim((string)($cand['lastName'] ?? '')));
                $candFamCode  = strtoupper(trim((string)($cand['famCode'] ?? '')));
                $candFamCode2 = strtoupper(trim((string)($cand['familyCode2'] ?? '')));
                $candMaiden   = strtoupper(trim((string)($cand['maiden_name'] ?? '')));
                $candOtherFam = strtoupper(trim((string)($cand['otherFamCode'] ?? '')));

                $matchScore   = 0;
                $matchReasons = [];
                $kinshipType  = 'Potential Kin';

                // Tier 2A: Maiden Name Cross-Matching (Maternal)
                if ($settings['include_maternal']) {
                    if (!empty($userMaiden) && ($candLastName === $userMaiden || $candFamCode === $userMaiden)) {
                        $matchScore += 45;
                        $matchReasons[] = "Maternal Lineage: Matches your mother's maiden name ({$userMaiden})";
                        $kinshipType = 'Maternal Cousin / In-Law';
                    }

                    if (!empty($candMaiden) && ($candMaiden === $userLastName || $candMaiden === $userFamCode)) {
                        $matchScore += 45;
                        $matchReasons[] = "Maternal Match: Has maiden heritage in {$userLastName}";
                        $kinshipType = 'Maternal Relative';
                    }
                }

                // Tier 2B: Secondary Family Code & In-Law Matching
                if ($settings['include_inlaws']) {
                    if (!empty($userFamCode2) && ($candFamCode === $userFamCode2 || $candFamCode2 === $userFamCode2)) {
                        $matchScore += 40;
                        $matchReasons[] = "In-Law Lineage: Connected to family branch {$userFamCode2}";
                        $kinshipType = 'Potential In-Law';
                    }

                    if (!empty($userOtherFamCode) && ($candFamCode === $userOtherFamCode || $candOtherFam === $userOtherFamCode)) {
                        $matchScore += 40;
                        $matchReasons[] = "In-Law Circle: Shared secondary family code ({$userOtherFamCode})";
                        $kinshipType = 'In-Law Relative';
                    }
                }

                // Tier 3: Mutual Kinship Graph (batched — O(1) lookup)
                $mutualCount = $mutualKinCounts[$candId] ?? 0;
                if ($mutualCount > 0) {
                    $scoreBoost = min(35, $mutualCount * 15);
                    $matchScore += $scoreBoost;
                    $matchReasons[] = "{$mutualCount} mutual family connection" . ($mutualCount > 1 ? 's' : '');
                    if ($kinshipType === 'Potential Kin') {
                        $kinshipType = 'Extended Cousin';
                    }
                }

                // Tier 4: Surname / Heritage Clan Overlap
                if ($candLastName === $userLastName && !empty($userLastName)) {
                    $matchScore += 30;
                    $matchReasons[] = "Shared Surname ({$userLastName})";
                    if ($kinshipType === 'Potential Kin') {
                        $kinshipType = 'Paternal Clan';
                    }
                }

                // Match confidence filtering (respect user's minimum confidence preference)
                if ($matchScore >= 30) {
                    $confidencePct = min(98, max(55, $matchScore + 20));
                    if ($confidencePct >= $settings['min_confidence']) {
                        $scoredSuggestions[] = [
                            'user_id'          => $candId,
                            'firstName'        => $cand['firstName'] ?? '',
                            'lastName'         => $cand['lastName'] ?? '',
                            'famCode'          => $cand['famCode'] ?? '',
                            'familyCode2'      => $cand['familyCode2'] ?? '',
                            'maiden_name'      => $cand['maiden_name'] ?? '',
                            'father_name'      => $cand['father_name'] ?? '',
                            'mother_name'      => $cand['mother_name'] ?? '',
                            'spouse_name'      => $cand['spouse_name'] ?? '',
                            'otherFamCode'     => $cand['otherFamCode'] ?? '',
                            'profilePics'      => $cand['profilePics'] ?? '',
                            'confidence_score' => $confidencePct,
                            'kinship_type'     => $kinshipType,
                            'reasons'          => $matchReasons,
                            'primary_reason'   => $matchReasons[0] ?? 'Shared family heritage network'
                        ];
                    }
                }
            }

            // Sort by confidence score descending
            usort($scoredSuggestions, static fn($a, $b) => $b['confidence_score'] <=> $a['confidence_score']);

            return array_slice($scoredSuggestions, 0, $limit);
        } catch (\Throwable $e) {
            error_log("[KinshipEngineService] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get list of user IDs to exclude from suggestions.
     * Constant 3 queries regardless of data size.
     *
     * @return array<int, string>
     */
    private static function getExcludedUserIds(PDO $pdo, string $userId, string $famCode): array
    {
        $excluded = [$userId];

        // Exclude members sharing primary family code
        if (!empty($famCode)) {
            $famStmt = $pdo->prepare("SELECT id FROM personal WHERE famCode = :famCode");
            $famStmt->execute([':famCode' => $famCode]);
            while ($row = $famStmt->fetch(PDO::FETCH_ASSOC)) {
                $excluded[] = (string)$row['id'];
            }
        }

        // Exclude members with any request relationship (pending, approved, rejected)
        $reqStmt = $pdo->prepare("
            SELECT approver_id FROM requestMgt WHERE requester_id = :u1
            UNION
            SELECT requester_id FROM requestMgt WHERE approver_id = :u2
        ");
        $reqStmt->execute([':u1' => $userId, ':u2' => $userId]);
        while ($row = $reqStmt->fetch(PDO::FETCH_NUM)) {
            $excluded[] = (string)$row[0];
        }

        // Exclude dismissed users
        try {
            $dStmt = $pdo->prepare("SELECT dismissed_user_id FROM kinship_dismissed WHERE user_id = :u");
            $dStmt->execute([':u' => $userId]);
            while ($row = $dStmt->fetch(PDO::FETCH_NUM)) {
                $excluded[] = (string)$row[0];
            }
        } catch (\Throwable $e) {
            // Non-blocking in case kinship_dismissed table is not yet migrated
        }

        // Exclude users who have opted out of radar discovery (discoverable = 0)
        try {
            $discStmt = $pdo->query("SELECT user_id FROM user_radar_settings WHERE discoverable = 0");
            if ($discStmt) {
                while ($row = $discStmt->fetch(PDO::FETCH_NUM)) {
                    $excluded[] = (string)$row[0];
                }
            }
        } catch (\Throwable $e) {
            // Non-blocking in case user_radar_settings table is not yet migrated
        }

        return array_unique($excluded);
    }

    /**
     * Get list of approved kin user IDs for a user.
     *
     * @return array<int, string>
     */
    private static function getApprovedKinIds(PDO $pdo, string $userId): array
    {
        $stmt = $pdo->prepare("
            SELECT approver_id FROM requestMgt WHERE requester_id = :u1 AND LOWER(status) IN ('approved', 'accepted')
            UNION
            SELECT requester_id FROM requestMgt WHERE approver_id = :u2 AND LOWER(status) IN ('approved', 'accepted')
        ");
        $stmt->execute([':u1' => $userId, ':u2' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Batch-compute mutual kin counts for ALL candidate IDs in a SINGLE query.
     * Eliminates the N+1 per-candidate `getApprovedKinIds()` loop.
     *
     * Returns: ['candidateId' => mutualCount, ...]
     *
     * @param array<int, string> $userKinIds    Current user's approved kin
     * @param array<int, string> $candidateIds  All candidate user IDs
     * @return array<string, int>
     */
    private static function batchMutualKinCounts(PDO $pdo, array $userKinIds, array $candidateIds): array
    {
        if (empty($userKinIds) || empty($candidateIds)) {
            return [];
        }

        $kinPh1 = self::buildPlaceholders($userKinIds, 'bk1');
        $candPh1 = self::buildPlaceholders($candidateIds, 'bc1');
        $kinPh2 = self::buildPlaceholders($userKinIds, 'bk2');
        $candPh2 = self::buildPlaceholders($candidateIds, 'bc2');

        $kinPhStr1 = implode(',', array_keys($kinPh1));
        $candPhStr1 = implode(',', array_keys($candPh1));
        $kinPhStr2 = implode(',', array_keys($kinPh2));
        $candPhStr2 = implode(',', array_keys($candPh2));

        // For each candidate, count how many of the user's kin they are also connected to
        $sql = "
            SELECT candidate_id, COUNT(DISTINCT mutual_kin_id) AS mutual_count
            FROM (
                SELECT rm.requester_id AS candidate_id, rm.approver_id AS mutual_kin_id
                FROM requestMgt rm
                WHERE rm.requester_id IN ({$candPhStr1})
                  AND rm.approver_id IN ({$kinPhStr1})
                  AND LOWER(rm.status) IN ('approved','accepted')
                UNION ALL
                SELECT rm2.approver_id AS candidate_id, rm2.requester_id AS mutual_kin_id
                FROM requestMgt rm2
                WHERE rm2.approver_id IN ({$candPhStr2})
                  AND rm2.requester_id IN ({$kinPhStr2})
                  AND LOWER(rm2.status) IN ('approved','accepted')
            ) AS mutual_map
            GROUP BY candidate_id
        ";

        $params = array_merge($candPh1, $kinPh1, $candPh2, $kinPh2);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[(string)$row['candidate_id']] = (int)$row['mutual_count'];
        }
        return $result;
    }

    /**
     * Build named placeholders for an array of IDs.
     * Returns [':prefix_0' => 'val0', ':prefix_1' => 'val1', ...]
     *
     * @param array<int, string> $ids
     * @return array<string, string>
     */
    private static function buildPlaceholders(array $ids, string $prefix): array
    {
        $placeholders = [];
        foreach (array_values($ids) as $i => $id) {
            $placeholders[":{$prefix}_{$i}"] = (string)$id;
        }
        return $placeholders;
    }

    /**
     * Dismiss a suggested kin so they don't appear again.
     */
    public static function dismissSuggestion(string $userId, string $dismissedUserId): bool
    {
        try {
            $pdo = Select::connect2();
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO kinship_dismissed (user_id, dismissed_user_id)
                VALUES (:u, :du)
            ");
            $stmt->execute([':u' => $userId, ':du' => $dismissedUserId]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get Radar Settings for a user.
     * Returns default values if no row exists.
     *
     * @param string|int $userId
     * @return array{
     *     user_id: string,
     *     radar_enabled: bool,
     *     include_inlaws: bool,
     *     include_maternal: bool,
     *     min_confidence: int,
     *     discoverable: bool
     * }
     */
    public static function getRadarSettings(string|int $userId): array
    {
        $userIdStr = (string)$userId;
        $defaults = [
            'user_id'          => $userIdStr,
            'radar_enabled'    => true,
            'include_inlaws'   => true,
            'include_maternal' => true,
            'min_confidence'   => 50,
            'discoverable'     => true,
        ];

        if (empty($userIdStr)) {
            return $defaults;
        }

        try {
            $pdo = Select::connect2();
            $stmt = $pdo->prepare("
                SELECT radar_enabled, include_inlaws, include_maternal, min_confidence, discoverable
                FROM user_radar_settings
                WHERE user_id = :userId LIMIT 1
            ");
            $stmt->execute([':userId' => $userIdStr]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return $defaults;
            }

            return [
                'user_id'          => $userIdStr,
                'radar_enabled'    => (bool)($row['radar_enabled'] ?? 1),
                'include_inlaws'   => (bool)($row['include_inlaws'] ?? 1),
                'include_maternal' => (bool)($row['include_maternal'] ?? 1),
                'min_confidence'   => max(30, min(95, (int)($row['min_confidence'] ?? 50))),
                'discoverable'     => (bool)($row['discoverable'] ?? 1),
            ];
        } catch (\Throwable $e) {
            error_log('[KinshipEngineService::getRadarSettings] ' . $e->getMessage());
            return $defaults;
        }
    }

    /**
     * Update Radar Settings for a user.
     *
     * @param string|int $userId
     * @param array<string, mixed> $settings
     * @return bool
     */
    public static function updateRadarSettings(string|int $userId, array $settings): bool
    {
        $userIdStr = (string)$userId;
        if (empty($userIdStr)) {
            return false;
        }

        $radarEnabled    = isset($settings['radar_enabled']) ? (int)(bool)$settings['radar_enabled'] : 1;
        $includeInlaws   = isset($settings['include_inlaws']) ? (int)(bool)$settings['include_inlaws'] : 1;
        $includeMaternal = isset($settings['include_maternal']) ? (int)(bool)$settings['include_maternal'] : 1;
        $minConfidence   = isset($settings['min_confidence']) ? max(30, min(95, (int)$settings['min_confidence'])) : 50;
        $discoverable    = isset($settings['discoverable']) ? (int)(bool)$settings['discoverable'] : 1;

        try {
            $pdo = Select::connect2();
            $stmt = $pdo->prepare("
                INSERT INTO user_radar_settings 
                    (user_id, radar_enabled, include_inlaws, include_maternal, min_confidence, discoverable)
                VALUES 
                    (:u, :re, :ii, :im, :mc, :d)
                ON DUPLICATE KEY UPDATE
                    radar_enabled = VALUES(radar_enabled),
                    include_inlaws = VALUES(include_inlaws),
                    include_maternal = VALUES(include_maternal),
                    min_confidence = VALUES(min_confidence),
                    discoverable = VALUES(discoverable),
                    updated_at = CURRENT_TIMESTAMP
            ");
            return $stmt->execute([
                ':u'  => $userIdStr,
                ':re' => $radarEnabled,
                ':ii' => $includeInlaws,
                ':im' => $includeMaternal,
                ':mc' => $minConfidence,
                ':d'  => $discoverable,
            ]);
        } catch (\Throwable $e) {
            error_log('[KinshipEngineService::updateRadarSettings] ' . $e->getMessage());
            return false;
        }
    }
}

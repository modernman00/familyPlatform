<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use PDO;

/**
 * 🌌 Autonomous Constellation Alignment Engine
 *
 * Implements the Fellegi-Sunter Topological Record Linkage framework
 * to discover, score, and align cross-studio family tree branches.
 *
 * Evaluates Partner similarities, Children cluster Jaccard indices, and
 * generational anchors to propose seamless 1-click branch connections.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class ConstellationAlignmentService
{
    /** Minimum confidence score required to surface an alignment recommendation (70%) */
    public const CONFIDENCE_THRESHOLD = 0.70;

    /** Weight matrix components */
    private const WEIGHT_PARTNERS = 0.40;
    private const WEIGHT_CHILDREN = 0.45;
    private const WEIGHT_GENERATION = 0.15;

    /**
     * Discover high-confidence constellation match proposals for a user's family tree.
     *
     * @param string $familyCode Current family circle code
     * @param string $userId The viewing user ID
     * @return list<array{
     *     target_union_id: int,
     *     target_family_code: string,
     *     target_family_name: string,
     *     confidence: float,
     *     confidence_percentage: int,
     *     partners: array{father: string, mother: string},
     *     matched_relatives: array{parents: list<string>, siblings: list<string>},
     *     new_relatives_count: int,
     *     new_relatives_preview: list<string>,
     *     has_half_siblings: bool,
     *     has_ancestors: bool
     * }>
     */
    public static function findMatchesForUser(string $familyCode, string $userId): array
    {
        if (empty($familyCode) || empty($userId)) {
            return [];
        }

        $db = Db::connect2();

        // 1. Resolve user's base node in this family tree
        $stmtBase = $db->prepare('SELECT id, generation_level, first_name, last_name FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1');
        $stmtBase->execute([$familyCode, $userId]);
        $baseNode = $stmtBase->fetch(PDO::FETCH_ASSOC);

        if (!$baseNode) {
            // Check if there is an unattached root node
            $stmtRoot = $db->prepare('SELECT id, generation_level, first_name, last_name FROM family_nodes WHERE family_code = ? ORDER BY id ASC LIMIT 1');
            $stmtRoot->execute([$familyCode]);
            $baseNode = $stmtRoot->fetch(PDO::FETCH_ASSOC);
            if (!$baseNode) {
                return [];
            }
        }

        $baseNodeId = (int)$baseNode['id'];

        // 2. Extract user's local parent unions and sibling cluster
        $sourceClusters = self::extractLocalClusters($db, $familyCode, $baseNodeId);
        if (empty($sourceClusters)) {
            return [];
        }

        // 3. Retrieve user dismissal records to avoid re-prompting rejected proposals
        $stmtDismiss = $db->prepare('SELECT node_id FROM kinship_match_dismissals WHERE user_id = ? AND family_code = ?');
        $stmtDismiss->execute([$userId, $familyCode]);
        /** @var list<int> $dismissedUnionIds */
        $dismissedUnionIds = array_map('intval', $stmtDismiss->fetchAll(PDO::FETCH_COLUMN));

        // 4. Query candidate unions from other family trees (defensively capped at 100 recent active unions)
        $stmtCand = $db->prepare('
            SELECT u.id, u.family_code, u.partner_1_id, u.partner_2_id, u.union_type,
                   p1.first_name AS p1_first, p1.last_name AS p1_last, p1.gender AS p1_gender, p1.generation_level AS p1_gen,
                   p2.first_name AS p2_first, p2.last_name AS p2_last, p2.gender AS p2_gender, p2.generation_level AS p2_gen
            FROM family_unions u
            LEFT JOIN family_nodes p1 ON u.partner_1_id = p1.id
            LEFT JOIN family_nodes p2 ON u.partner_2_id = p2.id
            WHERE u.family_code != ?
              AND (u.partner_1_id IS NOT NULL OR u.partner_2_id IS NOT NULL)
            ORDER BY u.id DESC
            LIMIT 100
        ');
        $stmtCand->execute([$familyCode]);
        /** @var list<array<string, mixed>> $candidateUnions */
        $candidateUnions = $stmtCand->fetchAll(PDO::FETCH_ASSOC);

        $proposals = [];

        foreach ($candidateUnions as $candU) {
            $uId = (int)$candU['id'];
            if (in_array($uId, $dismissedUnionIds, true)) {
                continue;
            }

            // Extract candidate children cluster
            $stmtKids = $db->prepare('
                SELECT n.id, n.first_name, n.last_name, n.gender, n.generation_level, fnc.relationship_type
                FROM family_node_children fnc
                JOIN family_nodes n ON fnc.child_id = n.id
                WHERE fnc.union_id = ?
            ');
            $stmtKids->execute([$uId]);
            /** @var list<array<string, mixed>> $candKids */
            $candKids = $stmtKids->fetchAll(PDO::FETCH_ASSOC);

            // Construct candidate cluster shape
            $candCluster = [
                'union_id' => $uId,
                'family_code' => (string)$candU['family_code'],
                'partner_1' => [
                    'id' => (int)($candU['partner_1_id'] ?? 0),
                    'first_name' => (string)($candU['p1_first'] ?? ''),
                    'last_name' => (string)($candU['p1_last'] ?? ''),
                    'gender' => (string)($candU['p1_gender'] ?? ''),
                    'gen' => (int)($candU['p1_gen'] ?? 0),
                ],
                'partner_2' => [
                    'id' => (int)($candU['partner_2_id'] ?? 0),
                    'first_name' => (string)($candU['p2_first'] ?? ''),
                    'last_name' => (string)($candU['p2_last'] ?? ''),
                    'gender' => (string)($candU['p2_gender'] ?? ''),
                    'gen' => (int)($candU['p2_gen'] ?? 0),
                ],
                'children' => $candKids,
            ];

            // Evaluate against all source clusters
            foreach ($sourceClusters as $sourceCluster) {
                $scoreResult = self::calculateConstellationScore($sourceCluster, $candCluster);

                if ($scoreResult['confidence'] >= self::CONFIDENCE_THRESHOLD) {
                    // Check for collateral half-siblings on candidate partners
                    $hasHalfSiblings = self::checkCollateralUnions($db, (string)$candU['family_code'], (int)$candU['partner_1_id'], (int)$candU['partner_2_id'], $uId);

                    // Check for upstream ancestors
                    $hasAncestors = self::checkAncestors($db, (string)$candU['family_code'], (int)$candU['partner_1_id'], (int)$candU['partner_2_id']);

                    $fatherName = trim($candCluster['partner_1']['first_name'] . ' ' . $candCluster['partner_1']['last_name']);
                    $motherName = trim($candCluster['partner_2']['first_name'] . ' ' . $candCluster['partner_2']['last_name']);
                    if (strcasecmp($candCluster['partner_1']['gender'], 'Female') === 0) {
                        [$fatherName, $motherName] = [$motherName, $fatherName];
                    }

                    $proposals[] = [
                        'target_union_id' => $uId,
                        'target_family_code' => (string)$candU['family_code'],
                        'target_family_name' => self::resolveFamilyName($db, (string)$candU['family_code']),
                        'confidence' => $scoreResult['confidence'],
                        'confidence_percentage' => (int)round($scoreResult['confidence'] * 100),
                        'partners' => [
                            'father' => $fatherName !== '' ? $fatherName : 'Father',
                            'mother' => $motherName !== '' ? $motherName : 'Mother',
                        ],
                        'matched_relatives' => [
                            'parents' => array_values(array_filter([$fatherName, $motherName])),
                            'siblings' => $scoreResult['matched_children'],
                        ],
                        'new_relatives_count' => count($scoreResult['novel_children']),
                        'new_relatives_preview' => array_slice($scoreResult['novel_children'], 0, 4),
                        'has_half_siblings' => $hasHalfSiblings,
                        'has_ancestors' => $hasAncestors,
                    ];
                    break; // Matched best source cluster for this candidate
                }
            }
        }

        // Sort by highest confidence descending
        usort($proposals, static fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return array_slice($proposals, 0, 5);
    }

    /**
     * Compute the Fellegi-Sunter Constellation Weight Matrix score between two clusters.
     *
     * @param array{
     *     partner_1: array{first_name: string, last_name: string, gender: string, gen: int},
     *     partner_2: array{first_name: string, last_name: string, gender: string, gen: int},
     *     children: list<array<string, mixed>>
     * } $src
     * @param array{
     *     partner_1: array{first_name: string, last_name: string, gender: string, gen: int},
     *     partner_2: array{first_name: string, last_name: string, gender: string, gen: int},
     *     children: list<array<string, mixed>>
     * } $tgt
     * @return array{confidence: float, matched_children: list<string>, novel_children: list<string>}
     */
    public static function calculateConstellationScore(array $src, array $tgt): array
    {
        // 1. Partner Similarity
        $p1Sim = self::comparePersonNames($src['partner_1'], $tgt['partner_1']);
        $p2Sim = self::comparePersonNames($src['partner_2'], $tgt['partner_2']);

        // Check cross-partner orientation (if partner_1 in src matches partner_2 in tgt)
        $cross1Sim = self::comparePersonNames($src['partner_1'], $tgt['partner_2']);
        $cross2Sim = self::comparePersonNames($src['partner_2'], $tgt['partner_1']);

        $partnerScore = max(($p1Sim + $p2Sim) / 2.0, ($cross1Sim + $cross2Sim) / 2.0);

        // If neither partner matches with at least 0.50 score, abort early
        if ($partnerScore < 0.40) {
            return ['confidence' => 0.0, 'matched_children' => [], 'novel_children' => []];
        }

        // 2. Children Cluster Jaccard Similarity
        $srcKids = [];
        foreach ($src['children'] as $k) {
            $f = strtolower(trim((string)($k['first_name'] ?? '')));
            if ($f !== '') {
                $srcKids[$f] = trim((string)($k['first_name'] ?? '') . ' ' . (string)($k['last_name'] ?? ''));
            }
        }

        $tgtKids = [];
        $novelChildren = [];
        $matchedChildren = [];

        foreach ($tgt['children'] as $k) {
            $f = strtolower(trim((string)($k['first_name'] ?? '')));
            $full = trim((string)($k['first_name'] ?? '') . ' ' . (string)($k['last_name'] ?? ''));
            if ($f === '') {
                continue;
            }
            $tgtKids[$f] = $full;

            $foundMatch = false;
            foreach (array_keys($srcKids) as $srcFirst) {
                if ($srcFirst === $f || KinshipEntityResolver::isDiminutiveMatch($srcFirst, $f)) {
                    $matchedChildren[] = $full;
                    $foundMatch = true;
                    break;
                }
            }

            if (!$foundMatch) {
                $novelChildren[] = $full;
            }
        }

        $unionCount = count(array_unique(array_merge(array_keys($srcKids), array_keys($tgtKids))));
        $intersectCount = count($matchedChildren);

        $jaccardKids = $unionCount > 0 ? ((float)$intersectCount / (float)$unionCount) : 0.0;

        // If both parents match strongly and at least one child matches, grant strong constellation anchor bonus
        if ($partnerScore >= 0.85 && $intersectCount >= 1) {
            $jaccardKids = min(1.0, $jaccardKids + 0.35);
        } elseif ($intersectCount >= 2) {
            $jaccardKids = min(1.0, $jaccardKids + 0.20);
        }

        // 3. Generational Alignment
        $genScore = 1.0;
        if (!empty($src['partner_1']['gen']) && !empty($tgt['partner_1']['gen'])) {
            $genDiff = abs($src['partner_1']['gen'] - $tgt['partner_1']['gen']);
            $genScore = match ($genDiff) {
                0 => 1.0,
                1 => 0.6,
                default => 0.2,
            };
        }

        // Composite Weighted Score
        $confidence = (self::WEIGHT_PARTNERS * $partnerScore) +
                      (self::WEIGHT_CHILDREN * $jaccardKids) +
                      (self::WEIGHT_GENERATION * $genScore);

        return [
            'confidence' => round($confidence, 4),
            'matched_children' => $matchedChildren,
            'novel_children' => $novelChildren,
        ];
    }

    /**
     * Compare two person name records using diminutive matching, Levenshtein, and Soundex.
     *
     * @param array{first_name: string, last_name: string} $a
     * @param array{first_name: string, last_name: string} $b
     */
    private static function comparePersonNames(array $a, array $b): float
    {
        $firstA = strtolower(trim($a['first_name']));
        $firstB = strtolower(trim($b['first_name']));
        $lastA  = strtolower(trim($a['last_name']));
        $lastB  = strtolower(trim($b['last_name']));

        if ($firstA === '' && $firstB === '') {
            return 0.50; // Neutral placeholder match
        }
        if ($firstA === '' || $firstB === '') {
            return 0.20;
        }

        $score = 0.0;

        // First name evaluation
        if ($firstA === $firstB) {
            $score += 0.65;
        } elseif (KinshipEntityResolver::isDiminutiveMatch($firstA, $firstB)) {
            $score += 0.60;
        } else {
            $maxLen = max(strlen($firstA), strlen($firstB));
            $lev = levenshtein($firstA, $firstB);
            if (($lev / $maxLen) <= 0.25) {
                $score += 0.45;
            } elseif (soundex($firstA) === soundex($firstB)) {
                $score += 0.35;
            }
        }

        // Surname evaluation
        if ($lastA !== '' && $lastB !== '') {
            if ($lastA === $lastB) {
                $score += 0.35;
            } elseif (soundex($lastA) === soundex($lastB)) {
                $score += 0.20;
            }
        } else {
            // Maiden name or omitted surname fallback
            $score += 0.20;
        }

        return min(1.0, $score);
    }

    /**
     * Extract parent unions and their children clusters for an ego node.
     *
     * @return list<array{
     *     partner_1: array{id: int, first_name: string, last_name: string, gender: string, gen: int},
     *     partner_2: array{id: int, first_name: string, last_name: string, gender: string, gen: int},
     *     children: list<array<string, mixed>>
     * }>
     */
    private static function extractLocalClusters(PDO $db, string $familyCode, int $baseNodeId): array
    {
        // Find parent union of base node
        $stmtUnion = $db->prepare('
            SELECT u.id, u.partner_1_id, u.partner_2_id,
                   p1.first_name AS p1_first, p1.last_name AS p1_last, p1.gender AS p1_gender, p1.generation_level AS p1_gen,
                   p2.first_name AS p2_first, p2.last_name AS p2_last, p2.gender AS p2_gender, p2.generation_level AS p2_gen
            FROM family_node_children fnc
            JOIN family_unions u ON fnc.union_id = u.id
            LEFT JOIN family_nodes p1 ON u.partner_1_id = p1.id
            LEFT JOIN family_nodes p2 ON u.partner_2_id = p2.id
            WHERE fnc.child_id = ? AND u.family_code = ?
        ');
        $stmtUnion->execute([$baseNodeId, $familyCode]);
        /** @var list<array<string, mixed>> $unions */
        $unions = $stmtUnion->fetchAll(PDO::FETCH_ASSOC);

        $clusters = [];

        foreach ($unions as $u) {
            $uId = (int)$u['id'];
            $stmtKids = $db->prepare('
                SELECT n.id, n.first_name, n.last_name, n.gender, n.generation_level
                FROM family_node_children fnc
                JOIN family_nodes n ON fnc.child_id = n.id
                WHERE fnc.union_id = ?
            ');
            $stmtKids->execute([$uId]);
            /** @var list<array<string, mixed>> $kids */
            $kids = $stmtKids->fetchAll(PDO::FETCH_ASSOC);

            $clusters[] = [
                'partner_1' => [
                    'id' => (int)($u['partner_1_id'] ?? 0),
                    'first_name' => (string)($u['p1_first'] ?? ''),
                    'last_name' => (string)($u['p1_last'] ?? ''),
                    'gender' => (string)($u['p1_gender'] ?? ''),
                    'gen' => (int)($u['p1_gen'] ?? 0),
                ],
                'partner_2' => [
                    'id' => (int)($u['partner_2_id'] ?? 0),
                    'first_name' => (string)($u['p2_first'] ?? ''),
                    'last_name' => (string)($u['p2_last'] ?? ''),
                    'gender' => (string)($u['p2_gender'] ?? ''),
                    'gen' => (int)($u['p2_gen'] ?? 0),
                ],
                'children' => $kids,
            ];
        }

        return $clusters;
    }

    /**
     * Check if partners have collateral unions (half-siblings) in their origin tree.
     */
    private static function checkCollateralUnions(PDO $db, string $familyCode, int $p1Id, int $p2Id, int $currentUnionId): bool
    {
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM family_unions
            WHERE family_code = ?
              AND (partner_1_id IN (?, ?) OR partner_2_id IN (?, ?))
              AND id != ?
        ');
        $stmt->execute([$familyCode, $p1Id, $p2Id, $p1Id, $p2Id, $currentUnionId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Check if partners have upstream parents recorded in their origin tree.
     */
    private static function checkAncestors(PDO $db, string $familyCode, int $p1Id, int $p2Id): bool
    {
        $stmt = $db->prepare('
            SELECT COUNT(*) FROM family_node_children fnc
            JOIN family_unions u ON fnc.union_id = u.id
            WHERE u.family_code = ? AND fnc.child_id IN (?, ?)
        ');
        $stmt->execute([$familyCode, $p1Id, $p2Id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Human-readable family surname or label resolver.
     */
    private static function resolveFamilyName(PDO $db, string $familyCode): string
    {
        $stmt = $db->prepare('SELECT last_name FROM family_nodes WHERE family_code = ? AND last_name IS NOT NULL AND last_name != "" LIMIT 1');
        $stmt->execute([$familyCode]);
        $surname = (string)$stmt->fetchColumn();

        if ($surname === '') {
            $stmtP = $db->prepare('SELECT lastName FROM personal WHERE famCode = ? OR famCode = ? LIMIT 1');
            $stmtP->execute([$familyCode, '#' . $familyCode]);
            $surname = (string)$stmtP->fetchColumn();
        }

        return $surname !== '' ? "{$surname} Lineage" : "Family Circle ({$familyCode})";
    }

    /**
     * Persistently dismiss an alignment suggestion.
     */
    public static function dismissMatch(string $familyCode, string $userId, int $targetUnionId): bool
    {
        if (empty($familyCode) || empty($userId) || $targetUnionId <= 0) {
            return false;
        }

        $db = Db::connect2();
        $stmt = $db->prepare('
            INSERT INTO kinship_match_dismissals (user_id, node_id, family_code)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([$userId, $targetUnionId, $familyCode]);

        return true;
    }
}

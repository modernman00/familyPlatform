<?php

declare(strict_types=1);

namespace App\services;

/**
 * World-Class Ego-Centric Kinship Calculation Engine
 * 
 * Computes exact genealogical relationship labels and kinship paths from the
 * signed-in user (Ego) to every member in the family graph using Directed
 * Acyclic Graph (DAG) BFS traversal.
 * 
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class KinshipCalculatorService
{
    /**
     * Annotate a family graph with ego-centric relationship titles
     * 
     * @param array{
     *     nodes: array<int, array<string, mixed>>,
     *     unions: array<int, array<string, mixed>>,
     *     children: array<int, array<string, mixed>>,
     *     root_node_id?: int
     * } $graphData
     * @param int $egoNodeId The viewing user's node ID in the graph
     * @return array<int, array<string, mixed>> Decorated nodes map keyed by node ID
     */
    public static function calculateEgoKinship(array $graphData, int $egoNodeId): array
    {
        $rawNodes = $graphData['nodes'];
        $unions = $graphData['unions'];
        $children = $graphData['children'];

        if (empty($rawNodes)) {
            return [];
        }

        // 1. Index Nodes by ID
        $nodesById = [];
        foreach ($rawNodes as $n) {
            $id = (int)($n['id'] ?? 0);
            if ($id > 0) {
                $nodesById[$id] = $n;
            }
        }

        if (!isset($nodesById[$egoNodeId])) {
            // Fallback: if egoNodeId not found, use first node
            $egoNodeId = (int)(array_key_first($nodesById) ?? 0);
        }

        // 2. Build Graph Adjacency List
        // child -> list of { unionId, fatherId, motherId }
        $childParents = [];
        // union -> list of child IDs
        $unionChildren = [];
        // person -> list of partner IDs
        $personPartners = [];
        /** @var array<string, array{is_current: bool, union_type: string}> $unionOfPair */
        $unionOfPair = [];

        foreach ($unions as $u) {
            $uId = (int)($u['id'] ?? 0);
            $p1 = (int)($u['partner_1_id'] ?? 0);
            $p2 = (int)($u['partner_2_id'] ?? 0);

            if ($p1 > 0 && $p2 > 0) {
                $personPartners[$p1][] = $p2;
                $personPartners[$p2][] = $p1;
                $unionOfPair[min($p1, $p2) . '_' . max($p1, $p2)] = [
                    'is_current' => !empty($u['is_current']),
                    'union_type' => (string)($u['union_type'] ?? 'married'),
                ];
            }
        }

        foreach ($children as $c) {
            $uId = (int)($c['union_id'] ?? 0);
            $cId = (int)($c['child_id'] ?? 0);
            if ($uId > 0 && $cId > 0) {
                $unionChildren[$uId][] = $cId;

                // Find partners of this union
                foreach ($unions as $u) {
                    if ((int)($u['id'] ?? 0) === $uId) {
                        $p1 = (int)($u['partner_1_id'] ?? 0);
                        $p2 = (int)($u['partner_2_id'] ?? 0);
                        $childParents[$cId][] = ['union_id' => $uId, 'p1' => $p1, 'p2' => $p2];
                        break;
                    }
                }
            }
        }

        // Direct parent lookup: personId -> [parentIds]
        $parentsOf = [];
        // Direct child lookup: personId -> [childIds]
        $childrenOf = [];

        foreach ($childParents as $cId => $pUnions) {
            foreach ($pUnions as $pu) {
                if ($pu['p1'] > 0 && $pu['p1'] !== $cId) {
                    $parentsOf[$cId][] = $pu['p1'];
                    $childrenOf[$pu['p1']][] = $cId;
                }
                if ($pu['p2'] > 0 && $pu['p2'] !== $cId) {
                    $parentsOf[$cId][] = $pu['p2'];
                    $childrenOf[$pu['p2']][] = $cId;
                }
            }
            if (!empty($parentsOf[$cId])) {
                $parentsOf[$cId] = array_values(array_unique($parentsOf[$cId]));
            }
        }
        foreach ($childrenOf as $pId => $cList) {
            $childrenOf[$pId] = array_values(array_unique($cList));
        }

        // Direct sibling lookup: personId -> [siblingIds]
        // Anyone sharing at least one parent is a sibling (full OR half), even
        // when the other parent sits in a different union.
        $siblingsOf = [];
        foreach ($parentsOf as $cId => $parents) {
            foreach ($parents as $parId) {
                foreach ($childrenOf[$parId] ?? [] as $otherKid) {
                    if ($otherKid !== $cId) {
                        $siblingsOf[$cId][] = $otherKid;
                    }
                }
            }
            if (isset($siblingsOf[$cId])) {
                $siblingsOf[$cId] = array_values(array_unique($siblingsOf[$cId]));
            }
        }

        // 3. BFS Traversal to Determine Shortest Kinship Edge Paths from Ego
        // Queue stores: [nodeId, pathArray]
        $queue = [[$egoNodeId, []]];
        $visited = [$egoNodeId => true];
        $pathToNode = [$egoNodeId => []];

        while (!empty($queue)) {
            [$currId, $path] = array_shift($queue);

            // A. Explore Parents
            foreach ($parentsOf[$currId] ?? [] as $parId) {
                if (!isset($visited[$parId])) {
                    $visited[$parId] = true;
                    $newPath = array_merge($path, [['type' => 'parent', 'from' => $currId, 'to' => $parId]]);
                    $pathToNode[$parId] = $newPath;
                    $queue[] = [$parId, $newPath];
                }
            }

            // B. Explore Children
            foreach ($childrenOf[$currId] ?? [] as $kidId) {
                if (!isset($visited[$kidId])) {
                    $visited[$kidId] = true;
                    $newPath = array_merge($path, [['type' => 'child', 'from' => $currId, 'to' => $kidId]]);
                    $pathToNode[$kidId] = $newPath;
                    $queue[] = [$kidId, $newPath];
                }
            }

            // C. Explore Partners / Spouses
            foreach ($personPartners[$currId] ?? [] as $partId) {
                if (!isset($visited[$partId])) {
                    $visited[$partId] = true;
                    $newPath = array_merge($path, [['type' => 'spouse', 'from' => $currId, 'to' => $partId]]);
                    $pathToNode[$partId] = $newPath;
                    $queue[] = [$partId, $newPath];
                }
            }

            // D. Explore Siblings
            foreach ($siblingsOf[$currId] ?? [] as $sibId) {
                if (!isset($visited[$sibId])) {
                    $visited[$sibId] = true;
                    $newPath = array_merge($path, [['type' => 'sibling', 'from' => $currId, 'to' => $sibId]]);
                    $pathToNode[$sibId] = $newPath;
                    $queue[] = [$sibId, $newPath];
                }
            }
        }

        // 4. Translate Edge Paths into Natural Kinship Titles Relative to Ego
        $decorated = [];
        foreach ($nodesById as $id => $node) {
            $path = $pathToNode[$id] ?? null;
            $titleInfo = self::resolvePathToTitle($id, $node, $egoNodeId, $path, $nodesById);
            if ($id !== $egoNodeId && !self::hasKnownGender($node['gender'] ?? null)) {
                $titleInfo['title'] = self::neutraliseTitle($titleInfo['title']);
            }

            $node['ego_relation'] = $titleInfo['title'];
            $node['ego_side'] = $titleInfo['side']; // maternal, paternal, direct, none
            $node['consanguinity_degree'] = $titleInfo['degree'];
            $node['kinship_category'] = $titleInfo['category'];
            $node['is_ego'] = ($id === $egoNodeId);
            $node['sibling_kind'] = null;

            // Half-siblings: exactly one shared parent with the ego
            if ($id !== $egoNodeId && $titleInfo['category'] === 'sibling' && is_array($path) && count($path) === 1) {
                $sharedParents = array_values(array_intersect($parentsOf[$egoNodeId] ?? [], $parentsOf[$id] ?? []));
                if (count($sharedParents) === 1) {
                    $sibGender = strtolower((string)($node['gender'] ?? ''));
                    $viaFemale = strtolower((string)($nodesById[$sharedParents[0]]['gender'] ?? '')) === 'female';
                    $halfTitle = match ($sibGender) {
                        'male' => 'Half-brother',
                        'female' => 'Half-sister',
                        default => 'Half-sibling',
                    };
                    $node['ego_relation'] = $halfTitle . ($viaFemale ? ' (same mum)' : ' (same dad)');
                    $node['sibling_kind'] = 'half';
                } elseif (count($sharedParents) > 1) {
                    $node['sibling_kind'] = 'full';
                }
            }

            // Parent's other partner (not Ego's own parent): clarify whose parent they are or label as Stepmother/Stepfather
            if (is_array($path) && count($path) === 2 && $path[0]['type'] === 'parent' && $path[1]['type'] === 'spouse' && !in_array($id, $parentsOf[$egoNodeId] ?? [], true)) {
                $pGender = strtolower((string)($node['gender'] ?? ''));
                $roleWord = match ($pGender) {
                    'female' => 'Mother',
                    'male' => 'Father',
                    default => 'Parent',
                };
                $halfKidFirst = '';
                foreach ($childrenOf[$id] ?? [] as $hkId) {
                    if ($hkId !== $egoNodeId && isset($nodesById[$hkId])) {
                        $hkFirst = trim((string)($nodesById[$hkId]['first_name'] ?? ''));
                        if ($hkFirst === '' && !empty($nodesById[$hkId]['full_name'])) {
                            $hkFirst = explode(' ', trim((string)$nodesById[$hkId]['full_name']))[0] ?? '';
                        }
                        if ($hkFirst !== '') {
                            $halfKidFirst = $hkFirst;
                            break;
                        }
                    }
                }
                if ($halfKidFirst !== '') {
                    $node['ego_relation'] = $halfKidFirst . "'s " . $roleWord;
                } else {
                    $node['ego_relation'] = match ($pGender) {
                        'female' => 'Stepmother',
                        'male' => 'Stepfather',
                        default => 'Step-parent',
                    };
                }
                $node['kinship_category'] = 'step_parent';
            }

            // Former partners keep their own title instead of reading as a current spouse
            if ($titleInfo['category'] === 'spouse' && is_array($path) && count($path) === 1) {
                $pair = $unionOfPair[min($egoNodeId, $id) . '_' . max($egoNodeId, $id)] ?? null;
                if ($pair !== null && !$pair['is_current'] && in_array($pair['union_type'], ['divorced', 'separated', 'annulled', 'widowed'], true)) {
                    $spGender = strtolower((string)($node['gender'] ?? ''));
                    $spTitle = match ($spGender) {
                        'male' => 'Husband',
                        'female' => 'Wife',
                        default => 'Spouse',
                    };
                    $node['ego_relation'] = ($pair['union_type'] === 'widowed' ? 'Late ' : 'Former ') . $spTitle;
                }
            }

            // Placeholders: preserve explicit label or accurate path-resolved title (e.g. Wife, Mother-in-law)
            if (!empty($node['is_placeholder'])) {
                if (!empty($node['placeholder_label'])) {
                    $node['ego_relation'] = (string)$node['placeholder_label'];
                } elseif (!empty($titleInfo['title']) && $titleInfo['title'] !== 'Relative') {
                    $node['ego_relation'] = $titleInfo['title'];
                } else {
                    $node['ego_relation'] = match (strtolower((string)($node['gender'] ?? ''))) {
                        'female' => 'Mother',
                        'male' => 'Father',
                        default => 'Parent',
                    };
                }
            }

            // 5. Layer 1: Compute Mendelian Shared DNA % & centiMorgan (cM) Consanguinity Metrics
            $dnaMetrics = self::computeSharedDnaMetrics(
                $id,
                $egoNodeId,
                $path,
                (string)$node['kinship_category'],
                $node['sibling_kind'],
                $parentsOf
            );
            $node['shared_dna_pct'] = $dnaMetrics['shared_dna_pct'];
            $node['shared_dna_formatted'] = $dnaMetrics['shared_dna_formatted'];
            $node['shared_dna_badge'] = $dnaMetrics['shared_dna_badge'];
            $node['shared_cm_avg'] = $dnaMetrics['shared_cm_avg'];
            $node['shared_cm_range'] = $dnaMetrics['shared_cm_range'];
            $node['is_biological_kin'] = $dnaMetrics['is_biological_kin'];

            $decorated[$id] = $node;
        }

        // 6. Layer 2: Propagate Tree-Derived Ancestral Lineage & Biogeographical DNA Make-Up
        $decorated = self::propagateTreeDnaMakeup($decorated, $parentsOf, $childrenOf);

        return $decorated;
    }

    /**
     * Layer 1: Compute exact Mendelian Shared DNA percentage and centiMorgan (cM) range
     * from the ego-centric kinship path using Wright's Coefficient of Relationship.
     *
     * @param int $nodeId
     * @param int $egoId
     * @param array<int, array{type: string, from: int, to: int}>|null $path
     * @param string $category
     * @param string|null $siblingKind
     * @param array<int, array<int, int>> $parentsOf
     * @return array{
     *     shared_dna_pct: float,
     *     shared_dna_formatted: string,
     *     shared_dna_badge: string,
     *     shared_cm_avg: int,
     *     shared_cm_range: string,
     *     is_biological_kin: bool
     * }
     */
    private static function computeSharedDnaMetrics(
        int $nodeId,
        int $egoId,
        ?array $path,
        string $category,
        ?string $siblingKind,
        array $parentsOf
    ): array {
        if ($nodeId === $egoId) {
            return [
                'shared_dna_pct' => 100.0,
                'shared_dna_formatted' => '100%',
                'shared_dna_badge' => '🧬 100% YOU',
                'shared_cm_avg' => 6800,
                'shared_cm_range' => '6,800 cM',
                'is_biological_kin' => true,
            ];
        }

        if ($path === null || empty($path)) {
            return [
                'shared_dna_pct' => 0.0,
                'shared_dna_formatted' => '0%',
                'shared_dna_badge' => '',
                'shared_cm_avg' => 0,
                'shared_cm_range' => '0 cM',
                'is_biological_kin' => false,
            ];
        }

        // Non-consanguineous categories or any path traversing a marital/spouse edge
        if (in_array($category, ['spouse', 'in_law', 'step_parent'], true)) {
            return [
                'shared_dna_pct' => 0.0,
                'shared_dna_formatted' => '0%',
                'shared_dna_badge' => '💍 Non-Bio',
                'shared_cm_avg' => 0,
                'shared_cm_range' => '0 cM (Marital / In-Law)',
                'is_biological_kin' => false,
            ];
        }

        $halvings = 0;
        foreach ($path as $step) {
            $stepType = $step['type'];
            if ($stepType === 'spouse') {
                return [
                    'shared_dna_pct' => 0.0,
                    'shared_dna_formatted' => '0%',
                    'shared_dna_badge' => '💍 Non-Bio',
                    'shared_cm_avg' => 0,
                    'shared_cm_range' => '0 cM (Marital / In-Law)',
                    'is_biological_kin' => false,
                ];
            }
            if ($stepType === 'parent' || $stepType === 'child') {
                $halvings += 1;
            } elseif ($stepType === 'sibling') {
                $fromId = (int)$step['from'];
                $toId = (int)$step['to'];
                $fromParents = $parentsOf[$fromId] ?? [];
                $toParents = $parentsOf[$toId] ?? [];
                $sharedCount = count(array_intersect($fromParents, $toParents));
                $isHalfStep = ($siblingKind === 'half')
                    || ($sharedCount === 1 && (count($fromParents) > 1 || count($toParents) > 1));
                $halvings += $isHalfStep ? 2 : 1;
            }
        }

        if ($halvings <= 0) {
            return [
                'shared_dna_pct' => 0.0,
                'shared_dna_formatted' => '0%',
                'shared_dna_badge' => '',
                'shared_cm_avg' => 0,
                'shared_cm_range' => '0 cM',
                'is_biological_kin' => false,
            ];
        }

        $rawPct = 100.0 * pow(0.5, $halvings);
        $pct = round($rawPct, 2);

        // Format clean human-readable percentage
        if ($pct >= 10.0) {
            $formattedVal = rtrim(rtrim(number_format($pct, 1, '.', ''), '0'), '.');
        } elseif ($pct >= 1.0) {
            $formattedVal = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.');
        } else {
            $formattedVal = rtrim(rtrim(number_format($rawPct, 2, '.', ''), '0'), '.');
        }

        // Direct parent/child is exact 50%; other relationships have recombination variance (~)
        $isExact50 = ($halvings === 1 && in_array($category, ['parent', 'child'], true));
        $prefix = $isExact50 ? '' : '~';
        $formatted = $prefix . $formattedVal . '%';
        $badge = '🧬 ' . $formatted . ' DNA';

        // Empirical centiMorgan (cM) averages & ranges (Shared cM Project v4.0 benchmarks)
        if ($isExact50) {
            $cmAvg = 3400;
            $cmRange = '3,330–3,720 cM';
        } elseif ($halvings === 1) {
            // Full siblings
            $cmAvg = 2613;
            $cmRange = '2,209–3,384 cM';
        } elseif ($halvings === 2) {
            $cmAvg = 1700;
            $cmRange = '1,156–2,311 cM';
        } elseif ($halvings === 3) {
            $cmAvg = 850;
            $cmRange = '396–1,397 cM';
        } elseif ($halvings === 4) {
            $cmAvg = 425;
            $cmRange = '102–980 cM';
        } elseif ($halvings === 5) {
            $cmAvg = 212;
            $cmRange = '41–592 cM';
        } else {
            $cmAvg = (int)max(1, round(6800 * pow(0.5, $halvings)));
            $cmMin = (int)max(0, round($cmAvg * 0.25));
            $cmMax = (int)round($cmAvg * 1.85);
            $cmRange = number_format($cmMin) . '–' . number_format($cmMax) . ' cM';
        }

        return [
            'shared_dna_pct' => $pct,
            'shared_dna_formatted' => $formatted,
            'shared_dna_badge' => $badge,
            'shared_cm_avg' => $cmAvg,
            'shared_cm_range' => $cmRange,
            'is_biological_kin' => true,
        ];
    }

    /**
     * Layer 2: Multi-Generational Ancestral Lineage & Biogeographical DNA Make-Up Engine.
     *
     * Combines:
     * 1. Recorded Biogeographical Ethnicity JSON (`dna_ethnicity_json`) with 50/50 Mendelian
     *    top-down inheritance (and full-sibling parity) across generations.
     * 2. Universal Tree-Synthesised Ancestral Founder Make-up (`lineage_regions`) so every
     *    node displays an accurate multi-segment ancestral composition bar immediately.
     * 3. Uniparental Haplogroup Propagation (Y-DNA along father-to-son lines, mtDNA along
     *    mother-to-child lines).
     *
     * @param array<int, array<string, mixed>> $nodesById
     * @param array<int, array<int, int>> $parentsOf
     * @param array<int, array<int, int>> $childrenOf
     * @return array<int, array<string, mixed>>
     */
    private static function propagateTreeDnaMakeup(
        array $nodesById,
        array $parentsOf,
        array $childrenOf
    ): array {
        if (empty($nodesById)) {
            return $nodesById;
        }

        // High-contrast WCAG-compliant luxury chromosome segment palette
        $palette = [
            '#10b981', // Emerald
            '#3b82f6', // Royal Blue
            '#f59e0b', // Amber Gold
            '#ec4899', // Rose
            '#8b5cf6', // Violet
            '#06b6d4', // Cyan
            '#f97316', // Coral Orange
            '#14b8a6', // Teal
        ];

        // 1. Parse recorded DNA profiles & establish founder lineage labels
        /** @var array<int, array<int, array{region: string, percentage: float, color: string}>> $recordedEthnicity */
        $recordedEthnicity = [];
        /** @var array<int, string> $haploY */
        $haploY = [];
        /** @var array<int, string> $haploYSource */
        $haploYSource = [];
        /** @var array<int, string> $haploMt */
        $haploMt = [];
        /** @var array<int, string> $haploMtSource */
        $haploMtSource = [];

        foreach ($nodesById as $id => $n) {
            $rawJson = $n['dna_ethnicity_json'] ?? null;
            if (is_string($rawJson) && trim($rawJson) !== '') {
                $decoded = json_decode($rawJson, true);
                if (is_array($decoded)) {
                    if (!empty($decoded['regions']) && is_array($decoded['regions'])) {
                        $cleanRegions = [];
                        $colorIdx = 0;
                        foreach ($decoded['regions'] as $r) {
                            if (!is_array($r)) {
                                continue;
                            }
                            $rawReg = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', (string)($r['region'] ?? '')) ?? '';
                            $regName = trim(strip_tags($rawReg));
                            $regPct = (float)($r['percentage'] ?? 0);
                            $regColor = trim((string)($r['color'] ?? ''));
                            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $regColor)) {
                                $regColor = $palette[$colorIdx % count($palette)];
                            }
                            if ($regName !== '' && $regPct > 0) {
                                $cleanRegions[] = [
                                    'region' => mb_substr($regName, 0, 60),
                                    'percentage' => round($regPct, 1),
                                    'color' => $regColor,
                                ];
                                $colorIdx++;
                            }
                        }
                        if (!empty($cleanRegions)) {
                            $recordedEthnicity[$id] = self::normaliseRegionWeights($cleanRegions);
                        }
                    }
                    $hy = trim(strip_tags((string)($decoded['haplogroup_y'] ?? '')));
                    if ($hy !== '') {
                        $haploY[$id] = mb_substr($hy, 0, 24);
                        $haploYSource[$id] = 'recorded';
                    }
                    $hmt = trim(strip_tags((string)($decoded['haplogroup_mt'] ?? '')));
                    if ($hmt !== '') {
                        $haploMt[$id] = mb_substr($hmt, 0, 24);
                        $haploMtSource[$id] = 'recorded';
                    }
                }
            }
        }

        // 2. Filter out synthetic placeholder parents and identify biological Father and Mother for each node
        $realParentsOf = [];
        foreach ($parentsOf as $childId => $pIds) {
            $validPIds = [];
            foreach ($pIds as $pId) {
                if (isset($nodesById[$pId]) && empty($nodesById[$pId]['is_placeholder'])) {
                    $validPIds[] = (int)$pId;
                }
            }
            if (!empty($validPIds)) {
                $realParentsOf[$childId] = array_values(array_unique($validPIds));
            }
        }
        $parentsOf = $realParentsOf;

        $fatherOf = [];
        $motherOf = [];
        foreach ($parentsOf as $childId => $pIds) {
            foreach ($pIds as $pId) {
                $pGender = strtolower((string)($nodesById[$pId]['gender'] ?? ''));
                if ($pGender === 'male' && !isset($fatherOf[$childId])) {
                    $fatherOf[$childId] = $pId;
                } elseif ($pGender === 'female' && !isset($motherOf[$childId])) {
                    $motherOf[$childId] = $pId;
                }
            }
        }

        // 3. Propagate Y-DNA (Patrilineal: Father <-> Son) and mtDNA (Matrilineal: Mother <-> Children)
        // 3a. Walk upward from recorded nodes to seed patrilineal/matrilineal ancestors
        foreach (array_keys($haploY) as $startId) {
            $curr = $startId;
            $guard = 0;
            while (isset($fatherOf[$curr]) && $guard < 16) {
                $fId = $fatherOf[$curr];
                if (!isset($haploY[$fId])) {
                    $haploY[$fId] = $haploY[$startId];
                    $haploYSource[$fId] = 'paternal_line';
                }
                $curr = $fId;
                $guard++;
            }
        }
        foreach (array_keys($haploMt) as $startId) {
            $curr = $startId;
            $guard = 0;
            while (isset($motherOf[$curr]) && $guard < 16) {
                $mId = $motherOf[$curr];
                if (!isset($haploMt[$mId])) {
                    $haploMt[$mId] = $haploMt[$startId];
                    $haploMtSource[$mId] = 'maternal_line';
                }
                $curr = $mId;
                $guard++;
            }
        }

        // 3b. Sort node IDs topologically from oldest generation to youngest
        $sortedIds = array_keys($nodesById);
        usort($sortedIds, static function (int $a, int $b) use ($nodesById): int {
            $genA = (int)($nodesById[$a]['generation_level'] ?? 0);
            $genB = (int)($nodesById[$b]['generation_level'] ?? 0);
            return $genA !== $genB ? ($genA <=> $genB) : ($a <=> $b);
        });

        // Multi-pass downward haplogroup inheritance
        for ($pass = 0; $pass < 6; $pass++) {
            foreach ($sortedIds as $id) {
                $nGender = strtolower((string)($nodesById[$id]['gender'] ?? ''));
                if ($nGender === 'male' && isset($haploY[$id])) {
                    foreach ($childrenOf[$id] ?? [] as $chId) {
                        $chGender = strtolower((string)($nodesById[$chId]['gender'] ?? ''));
                        if ($chGender === 'male' && !isset($haploY[$chId])) {
                            $haploY[$chId] = $haploY[$id];
                            $haploYSource[$chId] = 'paternal_line';
                        }
                    }
                }
                if ($nGender === 'female' && isset($haploMt[$id])) {
                    foreach ($childrenOf[$id] ?? [] as $chId) {
                        if (!isset($haploMt[$chId])) {
                            $haploMt[$chId] = $haploMt[$id];
                            $haploMtSource[$chId] = 'maternal_line';
                        }
                    }
                }
            }
        }

        // 4. Compute Universal Tree-Synthesised Ancestral Founder Make-up (`lineage_regions`)
        /** @var array<int, array<int, array{region: string, percentage: float, color: string}>> $lineageMakeup */
        $lineageMakeup = [];
        $founderColorMap = [];
        $founderIdx = 0;

        foreach ($sortedIds as $id) {
            $parents = $parentsOf[$id] ?? [];
            if (empty($parents)) {
                $label = self::resolveFounderLineageLabel($id, $nodesById);
                if (!isset($founderColorMap[$label])) {
                    $founderColorMap[$label] = $palette[$founderIdx % count($palette)];
                    $founderIdx++;
                }
                $lineageMakeup[$id] = [[
                    'region' => $label,
                    'percentage' => 100.0,
                    'color' => $founderColorMap[$label],
                ]];
            }
        }

        // Propagate founder lineage top-down across up to 8 generational passes
        for ($pass = 0; $pass < 8; $pass++) {
            foreach ($sortedIds as $id) {
                $parents = $parentsOf[$id] ?? [];
                if (empty($parents)) {
                    continue;
                }
                if (count($parents) >= 2) {
                    $p1 = $parents[0];
                    $p2 = $parents[1];
                    if (isset($lineageMakeup[$p1], $lineageMakeup[$p2])) {
                        $lineageMakeup[$id] = self::blendParentRegions($lineageMakeup[$p1], $lineageMakeup[$p2]);
                    }
                } elseif (count($parents) === 1) {
                    $p1 = $parents[0];
                    if (isset($lineageMakeup[$p1])) {
                        $p1Gender = strtolower((string)($nodesById[$p1]['gender'] ?? ''));
                        $otherLabel = ($p1Gender === 'male') ? 'Maternal Heritage Line' : 'Paternal Heritage Line';
                        if (!isset($founderColorMap[$otherLabel])) {
                            $founderColorMap[$otherLabel] = $palette[$founderIdx % count($palette)];
                            $founderIdx++;
                        }
                        $otherRegions = [[
                            'region' => $otherLabel,
                            'percentage' => 100.0,
                            'color' => $founderColorMap[$otherLabel],
                        ]];
                        $lineageMakeup[$id] = self::blendParentRegions($lineageMakeup[$p1], $otherRegions);
                    }
                }
            }
        }

        // Fallback for any disconnected/cyclic node without lineageMakeup yet
        foreach ($nodesById as $id => $n) {
            if (!isset($lineageMakeup[$id])) {
                $label = self::resolveFounderLineageLabel($id, $nodesById);
                if (!isset($founderColorMap[$label])) {
                    $founderColorMap[$label] = $palette[$founderIdx % count($palette)];
                    $founderIdx++;
                }
                $lineageMakeup[$id] = [[
                    'region' => $label,
                    'percentage' => 100.0,
                    'color' => $founderColorMap[$label],
                ]];
            }
        }

        // 5. Propagate Biogeographical Ethnicity (`recordedEthnicity`) across the tree
        $activeEthnicity = $recordedEthnicity;
        $ethnicitySource = [];
        $ethnicitySourceLabel = [];
        foreach (array_keys($recordedEthnicity) as $id) {
            $ethnicitySource[$id] = 'recorded';
            $ethnicitySourceLabel[$id] = 'Recorded DNA Profile';
        }

        // 5a. Top-down 50/50 Mendelian inheritance from parents to children
        for ($pass = 0; $pass < 8; $pass++) {
            foreach ($sortedIds as $id) {
                if (isset($recordedEthnicity[$id])) {
                    continue;
                }
                $parents = $parentsOf[$id] ?? [];
                if (count($parents) >= 2) {
                    $p1 = $parents[0];
                    $p2 = $parents[1];
                    if (isset($activeEthnicity[$p1], $activeEthnicity[$p2])) {
                        $activeEthnicity[$id] = self::blendParentRegions($activeEthnicity[$p1], $activeEthnicity[$p2]);
                        $ethnicitySource[$id] = 'inherited_dna';
                        $ethnicitySourceLabel[$id] = 'Synthesised from Parents (50/50 Mendelian)';
                    } elseif (isset($activeEthnicity[$p1]) || isset($activeEthnicity[$p2])) {
                        $knownP = isset($activeEthnicity[$p1]) ? $p1 : $p2;
                        $otherP = isset($activeEthnicity[$p1]) ? $p2 : $p1;
                        $otherFallback = $lineageMakeup[$otherP] ?? [[
                            'region' => self::resolveFounderLineageLabel($otherP, $nodesById),
                            'percentage' => 100.0,
                            'color' => '#64748b',
                        ]];
                        $activeEthnicity[$id] = self::blendParentRegions($activeEthnicity[$knownP], $otherFallback);
                        $ethnicitySource[$id] = 'inherited_dna';
                        $ethnicitySourceLabel[$id] = 'Synthesised from Parent & Tree Lineage (50/50)';
                    }
                } elseif (count($parents) === 1) {
                    $p1 = $parents[0];
                    if (isset($activeEthnicity[$p1])) {
                        $activeEthnicity[$id] = $activeEthnicity[$p1];
                        $ethnicitySource[$id] = 'inherited_dna';
                        $ethnicitySourceLabel[$id] = 'Inherited from Parent DNA Profile';
                    }
                }
            }
        }

        // 5b. Full-sibling Mendelian parity: if a full sibling has recorded/inherited DNA and this sibling does not
        foreach ($sortedIds as $id) {
            if (isset($activeEthnicity[$id])) {
                continue;
            }
            $myParents = $parentsOf[$id] ?? [];
            if (empty($myParents)) {
                continue;
            }
            foreach ($sortedIds as $otherId) {
                if ($otherId === $id || !isset($activeEthnicity[$otherId])) {
                    continue;
                }
                $otherParents = $parentsOf[$otherId] ?? [];
                if (!empty($otherParents) && count($myParents) === count($otherParents) && empty(array_diff($myParents, $otherParents))) {
                    $activeEthnicity[$id] = $activeEthnicity[$otherId];
                    $ethnicitySource[$id] = 'inherited_dna';
                    $ethnicitySourceLabel[$id] = 'Shared Full-Sibling Parental DNA Make-up';
                    break;
                }
            }
        }

        // 6. Attach complete `dna_makeup` payload to every node
        foreach ($nodesById as $id => &$node) {
            $hasCustomEthnicity = isset($activeEthnicity[$id]);
            $activeRegions = $hasCustomEthnicity ? $activeEthnicity[$id] : $lineageMakeup[$id];
            $mode = $hasCustomEthnicity ? 'ethnicity' : 'tree_lineage';
            $src = $hasCustomEthnicity ? ($ethnicitySource[$id] ?? 'inherited_dna') : 'tree_synthesised';
            $srcLabel = $hasCustomEthnicity
                ? ($ethnicitySourceLabel[$id] ?? 'Synthesised from Family DNA')
                : 'Synthesised from Family Tree Lineage';

            // Build concise summary string for node cards & tooltips (top 2 regions)
            $summaryParts = [];
            foreach (array_slice($activeRegions, 0, 2) as $reg) {
                $shortReg = preg_replace('/\s*\b(Ancestral|Heritage|Paternal|Maternal)\s+Line\b/i', '', $reg['region']) ?? $reg['region'];
                $shortReg = trim($shortReg);
                if (mb_strlen($shortReg) > 14) {
                    $shortReg = mb_substr($shortReg, 0, 13) . '…';
                }
                $pctStr = rtrim(rtrim(number_format((float)$reg['percentage'], 1, '.', ''), '0'), '.');
                $summaryParts[] = $pctStr . '% ' . $shortReg;
            }
            if (count($activeRegions) > 2) {
                $summaryParts[] = '+' . (count($activeRegions) - 2);
            }

            // Build explicit recursive ancestral DNA hierarchy (50% Parent -> 25% Grandparent -> 12.5% Great-Grandparent)
            $explicitTree = self::buildExplicitDnaHierarchy(
                (int)$id,
                $nodesById,
                $fatherOf,
                $motherOf,
                $parentsOf,
                $activeEthnicity,
                $lineageMakeup,
                $palette
            );

            $node['dna_makeup'] = [
                'mode' => $mode,
                'source' => $src,
                'source_label' => $srcLabel,
                'active_regions' => $activeRegions,
                'recorded_regions' => $recordedEthnicity[$id] ?? [],
                'lineage_regions' => $lineageMakeup[$id],
                'summary_text' => implode(' · ', $summaryParts),
                'explicit_tree' => $explicitTree,
                'haplogroup_y' => $haploY[$id] ?? null,
                'haplogroup_y_source' => $haploYSource[$id] ?? null,
                'haplogroup_mt' => $haploMt[$id] ?? null,
                'haplogroup_mt_source' => $haploMtSource[$id] ?? null,
            ];
        }
        unset($node);

        return $nodesById;
    }

    /**
     * Check whether a node has at least one non-placeholder biological parent in the tree.
     *
     * @param int $nodeId
     * @param array<int, array<string, mixed>> $nodesById
     * @param array<int, array<int, int>> $parentsOf
     * @return bool
     */
    private static function hasRealParentInTree(int $nodeId, array $nodesById, array $parentsOf): bool
    {
        foreach ($parentsOf[$nodeId] ?? [] as $pId) {
            if (isset($nodesById[$pId]) && empty($nodesById[$pId]['is_placeholder'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Resolve the ordered [FatherId|null, MotherId|null] pair of non-placeholder parents for a node.
     *
     * @param int $nodeId
     * @param array<int, array<string, mixed>> $nodesById
     * @param array<int, int> $fatherOf
     * @param array<int, int> $motherOf
     * @param array<int, array<int, int>> $parentsOf
     * @return array{0: int|null, 1: int|null}
     */
    private static function resolveRealParentPair(
        int $nodeId,
        array $nodesById,
        array $fatherOf,
        array $motherOf,
        array $parentsOf
    ): array {
        $fId = isset($fatherOf[$nodeId]) && empty($nodesById[$fatherOf[$nodeId]]['is_placeholder'])
            ? (int)$fatherOf[$nodeId]
            : null;
        $mId = isset($motherOf[$nodeId]) && empty($nodesById[$motherOf[$nodeId]]['is_placeholder'])
            ? (int)$motherOf[$nodeId]
            : null;

        // Fallback if gender was omitted on one or both real parents
        foreach ($parentsOf[$nodeId] ?? [] as $pId) {
            $pId = (int)$pId;
            if (!isset($nodesById[$pId]) || !empty($nodesById[$pId]['is_placeholder'])) {
                continue;
            }
            if ($pId === $fId || $pId === $mId) {
                continue;
            }
            if ($fId === null) {
                $fId = $pId;
            } elseif ($mId === null) {
                $mId = $pId;
            }
        }

        return [$fId, $mId];
    }

    /**
     * Build the explicit recursive hierarchical DNA breakdown for a node:
     * - 50% Father (explicitly consists of 25% Paternal Grandfather + 25% Paternal Grandmother -> 12.5% Great-Grandparents)
     * - 50% Mother (explicitly consists of 25% Maternal Grandfather + 25% Maternal Grandmother -> 12.5% Great-Grandparents)
     *
     * @param int $nodeId
     * @param array<int, array<string, mixed>> $nodesById
     * @param array<int, int> $fatherOf
     * @param array<int, int> $motherOf
     * @param array<int, array<int, int>> $parentsOf
     * @param array<int, array<int, array{region: string, percentage: float, color: string}>> $activeEthnicity
     * @param array<int, array<int, array{region: string, percentage: float, color: string}>> $lineageMakeup
     * @param array<int, string> $palette
     * @return array<string, mixed>
     */
    private static function buildExplicitDnaHierarchy(
        int $nodeId,
        array $nodesById,
        array $fatherOf,
        array $motherOf,
        array $parentsOf,
        array $activeEthnicity,
        array $lineageMakeup,
        array $palette
    ): array {
        if (!self::hasRealParentInTree($nodeId, $nodesById, $parentsOf)) {
            $founderLabel = self::resolveFounderLineageLabel($nodeId, $nodesById);
            $shortFounder = preg_replace('/\s*\b(Ancestral|Heritage|Paternal|Maternal)\s+Line\b/i', '', $founderLabel) ?? $founderLabel;
            $shortFounder = trim($shortFounder);
            return [
                'has_ancestors' => false,
                'is_founder' => true,
                'max_depth' => 0,
                'founder_label' => $founderLabel,
                'parent_line_summary' => '100% Founder (' . $shortFounder . ')',
                'sub_line_summary' => '',
                'sub_line_full' => '',
                'branches' => [],
                'sub_segments' => [[
                    'id' => $nodeId,
                    'percentage' => 100.0,
                    'formatted_pct' => '100%',
                    'name' => $founderLabel,
                    'short_name' => $shortFounder,
                    'role' => 'Founder Ancestor',
                    'generation_depth' => 0,
                    'root_side' => 'founder',
                    'color' => $lineageMakeup[$nodeId][0]['color'] ?? '#3b82f6',
                ]],
                'tooltip_tree_text' => '100% Ancestral Founder: ' . $founderLabel,
            ];
        }

        // Distinct colour palettes for Paternal vs Maternal sub-branches so the 2-tier chromosome bar is visually intuitive
        $paternalPalette = ['#2563eb', '#3b82f6', '#06b6d4', '#6366f1', '#0ea5e9', '#8b5cf6'];
        $maternalPalette = ['#059669', '#10b981', '#f59e0b', '#ec4899', '#14b8a6', '#f43f5e'];
        $patColorIdx = 0;
        $matColorIdx = 0;
        $maxDepth = 1;
        $subSegments = [];
        $gpShortList = [];
        $ggpShortList = [];

        [$fId, $mId] = self::resolveRealParentPair($nodeId, $nodesById, $fatherOf, $motherOf, $parentsOf);

        $buildBranch = function (
            ?int $ancId,
            string $slot,
            string $rootSide,
            int $depth,
            array $visited
        ) use (
            &$buildBranch,
            &$maxDepth,
            &$subSegments,
            &$gpShortList,
            &$ggpShortList,
            &$patColorIdx,
            &$matColorIdx,
            $nodesById,
            $fatherOf,
            $motherOf,
            $parentsOf,
            $activeEthnicity,
            $lineageMakeup,
            $paternalPalette,
            $maternalPalette
        ): array {
            if ($depth > $maxDepth) {
                $maxDepth = $depth;
            }

            $pct = 100.0 * pow(0.5, $depth);
            $formattedPct = rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.') . '%';

            $sidePrefix = $rootSide === 'paternal' ? 'Paternal' : 'Maternal';
            $shortSidePrefix = $rootSide === 'paternal' ? 'Pat.' : 'Mat.';
            $isMaleSlot = ($slot === 'father');

            if ($depth === 1) {
                $role = $isMaleSlot ? 'Father' : 'Mother';
                $shortRole = $role;
            } elseif ($depth === 2) {
                $role = $sidePrefix . ' ' . ($isMaleSlot ? 'Grandfather' : 'Grandmother');
                $shortRole = $shortSidePrefix . ' ' . ($isMaleSlot ? 'Grandfather' : 'Grandmother');
            } elseif ($depth === 3) {
                $role = $sidePrefix . ' ' . ($isMaleSlot ? 'Great-Grandfather' : 'Great-Grandmother');
                $shortRole = $shortSidePrefix . ' ' . ($isMaleSlot ? 'Great-GF' : 'Great-GM');
            } elseif ($depth === 4) {
                $role = $sidePrefix . ' 2nd ' . ($isMaleSlot ? 'Great-Grandfather' : 'Great-Grandmother');
                $shortRole = '2nd ' . ($isMaleSlot ? 'Great-GF' : 'Great-GM');
            } else {
                $ord = ($depth - 2) . 'th';
                $role = $sidePrefix . ' ' . $ord . ' ' . ($isMaleSlot ? 'Great-Grandfather' : 'Great-Grandmother');
                $shortRole = $ord . ' ' . ($isMaleSlot ? 'Great-GF' : 'Great-GM');
            }

            $isUnrecorded = ($ancId === null || !isset($nodesById[$ancId]));
            if (!$isUnrecorded) {
                $ancNode = $nodesById[$ancId];
                $first = trim((string)($ancNode['first_name'] ?? ''));
                $last = trim((string)($ancNode['last_name'] ?? ''));
                $maiden = trim((string)($ancNode['maiden_name'] ?? ''));
                $fullName = trim($first . ' ' . ($maiden !== '' && $maiden !== $last ? $maiden : $last));
                if ($fullName === '') {
                    $fullName = $role;
                }
                $shortName = $first !== '' ? $first : ($maiden !== '' ? $maiden : ($last !== '' ? $last : $role));
            } else {
                $fullName = 'Unrecorded ' . $role;
                $shortName = 'Unrecorded';
            }

            if ($rootSide === 'paternal') {
                $color = $depth === 1 ? '#3b82f6' : $paternalPalette[($patColorIdx++) % count($paternalPalette)];
            } else {
                $color = $depth === 1 ? '#10b981' : $maternalPalette[($matColorIdx++) % count($maternalPalette)];
            }

            // Record concise ethnicity summary for this specific ancestor if available
            $ethSummary = '';
            if (!$isUnrecorded && isset($activeEthnicity[$ancId])) {
                $ethParts = [];
                foreach (array_slice($activeEthnicity[$ancId], 0, 2) as $er) {
                    $ePct = rtrim(rtrim(number_format((float)$er['percentage'], 1, '.', ''), '0'), '.');
                    $ethParts[] = $ePct . '% ' . $er['region'];
                }
                $ethSummary = implode(' · ', $ethParts);
            }

            $ancRegions = (!$isUnrecorded && isset($activeEthnicity[$ancId]))
                ? $activeEthnicity[$ancId]
                : ((!$isUnrecorded && isset($lineageMakeup[$ancId]))
                    ? $lineageMakeup[$ancId]
                    : [['region' => $fullName, 'percentage' => 100.0, 'color' => $color]]);
            if (!empty($ancRegions[0]['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', (string)$ancRegions[0]['color'])) {
                $color = (string)$ancRegions[0]['color'];
            }

            if ($depth === 2 && !$isUnrecorded) {
                $gpShortList[] = $formattedPct . ' ' . $shortName;
            } elseif ($depth === 3 && !$isUnrecorded) {
                $ggpShortList[] = $formattedPct . ' ' . $shortName;
            }

            $consistsOf = [];
            $consistsOfLabel = '';
            $consistsOfSummary = '';

            if (
                !$isUnrecorded
                && $depth < 5
                && !in_array($ancId, $visited, true)
                && self::hasRealParentInTree($ancId, $nodesById, $parentsOf)
            ) {
                $nextVisited = array_merge($visited, [$ancId]);
                [$subFId, $subMId] = self::resolveRealParentPair($ancId, $nodesById, $fatherOf, $motherOf, $parentsOf);

                $childFatherBranch = $buildBranch($subFId, 'father', $rootSide, $depth + 1, $nextVisited);
                $childMotherBranch = $buildBranch($subMId, 'mother', $rootSide, $depth + 1, $nextVisited);
                $consistsOf = [$childFatherBranch, $childMotherBranch];

                $subPctStr = $childFatherBranch['formatted_pct'];
                if ($depth === 1) {
                    $consistsOfLabel = 'Consists of ' . $sidePrefix . ' Grandparents (' . $subPctStr . ' + ' . $subPctStr . ')';
                } elseif ($depth === 2) {
                    $consistsOfLabel = 'Consists of Great-Grandparents (' . $subPctStr . ' + ' . $subPctStr . ')';
                } elseif ($depth === 3) {
                    $consistsOfLabel = 'Consists of 2nd Great-Grandparents (' . $subPctStr . ' + ' . $subPctStr . ')';
                } else {
                    $consistsOfLabel = 'Consists of Ancestral Parents (' . $subPctStr . ' + ' . $subPctStr . ')';
                }

                $consistsOfSummary = $childFatherBranch['formatted_pct'] . ' ' . $childFatherBranch['name'] . ' (' . $childFatherBranch['short_role'] . ') + '
                    . $childMotherBranch['formatted_pct'] . ' ' . $childMotherBranch['name'] . ' (' . $childMotherBranch['short_role'] . ')';
            } else {
                // Leaf of the explicit ancestral tree: push to sub_segments for the 2nd tier of the chromosome bar
                $subSegments[] = [
                    'id' => $ancId,
                    'percentage' => $pct,
                    'formatted_pct' => $formattedPct,
                    'name' => $fullName,
                    'short_name' => $shortName,
                    'role' => $role,
                    'short_role' => $shortRole,
                    'generation_depth' => $depth,
                    'root_side' => $rootSide,
                    'color' => $color,
                    'is_unrecorded' => $isUnrecorded,
                ];
            }

            return [
                'id' => $ancId,
                'slot' => $slot,
                'root_side' => $rootSide,
                'generation_depth' => $depth,
                'role' => $role,
                'short_role' => $shortRole,
                'name' => $fullName,
                'short_name' => $shortName,
                'percentage' => $pct,
                'formatted_pct' => $formattedPct,
                'color' => $color,
                'regions' => $ancRegions,
                'is_unrecorded' => $isUnrecorded,
                'ethnicity_summary' => $ethSummary,
                'consists_of_label' => $consistsOfLabel,
                'consists_of_summary' => $consistsOfSummary,
                'consists_of' => $consistsOf,
            ];
        };

        $fatherBranch = $buildBranch($fId, 'father', 'paternal', 1, [$nodeId]);
        $motherBranch = $buildBranch($mId, 'mother', 'maternal', 1, [$nodeId]);
        $branches = [$fatherBranch, $motherBranch];

        $fShort = mb_strlen($fatherBranch['short_name']) > 10
            ? mb_substr($fatherBranch['short_name'], 0, 9) . '…'
            : $fatherBranch['short_name'];
        $mShort = mb_strlen($motherBranch['short_name']) > 10
            ? mb_substr($motherBranch['short_name'], 0, 9) . '…'
            : $motherBranch['short_name'];

        $parentLineSummary = '50% Father (' . $fShort . ') · 50% Mother (' . $mShort . ')';

        $subLineSummary = '';
        $subLineFull = '';
        if (!empty($gpShortList) && !empty($ggpShortList)) {
            $subLineFull = '↳ GP: ' . implode(' · ', $gpShortList) . ' | GGP: ' . implode(' · ', $ggpShortList);
            $gpNamesOnly = array_map(static fn(string $s): string => preg_replace('/^[0-9.]+%\s*/', '', $s) ?? $s, array_slice($gpShortList, 0, 2));
            $ggpNamesOnly = array_map(static fn(string $s): string => preg_replace('/^[0-9.]+%\s*/', '', $s) ?? $s, array_slice($ggpShortList, 0, 2));
            $subLineSummary = '↳ GP 25% (' . implode(', ', $gpNamesOnly) . (count($gpShortList) > 2 ? '…' : '') . ') · GGP 12.5% (' . implode(', ', $ggpNamesOnly) . (count($ggpShortList) > 2 ? '…' : '') . ')';
        } elseif (!empty($gpShortList)) {
            $subLineFull = '↳ GP: ' . implode(' · ', $gpShortList);
            $shownGp = array_slice($gpShortList, 0, 3);
            $subLineSummary = '↳ GP: ' . implode(' · ', $shownGp) . (count($gpShortList) > 3 ? ' +' . (count($gpShortList) - 3) : '');
        }

        // Format multi-line ASCII tree for native tooltips
        $tooltipLines = ['Explicit Tree DNA Make-up:'];
        $renderTooltipBranch = function (array $b, string $indent, bool $isLast) use (&$renderTooltipBranch, &$tooltipLines): void {
            $connector = $isLast ? '└─ ' : '├─ ';
            $line = $indent . $connector . $b['formatted_pct'] . ' ' . $b['role'] . ': ' . $b['name'];
            if ($b['ethnicity_summary'] !== '') {
                $line .= ' [' . $b['ethnicity_summary'] . ']';
            }
            $tooltipLines[] = $line;
            if (!empty($b['consists_of'])) {
                $childIndent = $indent . ($isLast ? '   ' : '│  ');
                $tooltipLines[] = $childIndent . '↳ ' . $b['consists_of_label'] . ':';
                $count = count($b['consists_of']);
                foreach ($b['consists_of'] as $idx => $childB) {
                    $renderTooltipBranch($childB, $childIndent . '  ', $idx === $count - 1);
                }
            }
        };
        $renderTooltipBranch($fatherBranch, '', false);
        $renderTooltipBranch($motherBranch, '', true);

        return [
            'has_ancestors' => true,
            'is_founder' => false,
            'max_depth' => $maxDepth,
            'founder_label' => null,
            'parent_line_summary' => $parentLineSummary,
            'sub_line_summary' => $subLineSummary,
            'sub_line_full' => $subLineFull,
            'branches' => $branches,
            'sub_segments' => $subSegments,
            'tooltip_tree_text' => implode("\n", $tooltipLines),
        ];
    }

    /**
     * Resolve a dignified, distinct Founder Lineage label for a root ancestor node in the tree.
     *
     * @param int $nodeId
     * @param array<int, array<string, mixed>> $nodesById
     * @return string
     */
    private static function resolveFounderLineageLabel(int $nodeId, array $nodesById): string
    {
        $n = $nodesById[$nodeId] ?? [];
        $gender = strtolower((string)($n['gender'] ?? ''));
        $maiden = trim((string)($n['maiden_name'] ?? ''));
        $last = trim((string)($n['last_name'] ?? ''));
        $first = trim((string)($n['first_name'] ?? ''));
        $birthCode = trim((string)($n['birth_family_code'] ?? ''));
        $isPlaceholder = !empty($n['is_placeholder']);

        if ($isPlaceholder) {
            return $gender === 'female' ? 'Maternal line' : 'Paternal line';
        }

        if ($maiden !== '' && strtolower($maiden) !== strtolower($last)) {
            return $maiden . ' line';
        }

        if ($first !== '' && !in_array(strtolower($first), ['father', 'dad', 'husband', 'grandfather', 'mother', 'mum', 'mom', 'wife', 'grandmother', 'relative'], true)) {
            return $first . ' line';
        }

        if ($maiden !== '') {
            return $maiden . ' line';
        }

        if ($birthCode !== '') {
            return '#' . $birthCode . ' line';
        }

        if ($last !== '') {
            return $last . ($gender === 'female' ? ' Maternal line' : ' Paternal line');
        }

        return 'Ancestral line';
    }

    /**
     * Blend two parent region arrays using exact 50/50 Mendelian inheritance and normalise to 100%.
     *
     * @param array<int, array{region: string, percentage: float, color: string}> $parent1Regions
     * @param array<int, array{region: string, percentage: float, color: string}> $parent2Regions
     * @return array<int, array{region: string, percentage: float, color: string}>
     */
    private static function blendParentRegions(array $parent1Regions, array $parent2Regions): array
    {
        $merged = [];
        foreach ($parent1Regions as $r) {
            $key = strtolower(trim($r['region']));
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'region' => $r['region'],
                    'percentage' => 0.0,
                    'color' => $r['color'],
                ];
            }
            $merged[$key]['percentage'] += ((float)$r['percentage']) * 0.5;
        }
        foreach ($parent2Regions as $r) {
            $key = strtolower(trim($r['region']));
            if (!isset($merged[$key])) {
                $merged[$key] = [
                    'region' => $r['region'],
                    'percentage' => 0.0,
                    'color' => $r['color'],
                ];
            }
            $merged[$key]['percentage'] += ((float)$r['percentage']) * 0.5;
        }

        return self::normaliseRegionWeights(array_values($merged));
    }

    /**
     * Normalise an array of DNA regions so percentages sum to 100.0%, sorted descending.
     *
     * @param array<int, array{region: string, percentage: float, color: string}> $regions
     * @return array<int, array{region: string, percentage: float, color: string}>
     */
    private static function normaliseRegionWeights(array $regions): array
    {
        $total = 0.0;
        foreach ($regions as $r) {
            $total += max(0.0, (float)$r['percentage']);
        }
        if ($total <= 0.0) {
            return [];
        }

        $normalised = [];
        foreach ($regions as $r) {
            $pct = round(((float)$r['percentage'] / $total) * 100.0, 1);
            if ($pct >= 0.5) {
                $normalised[] = [
                    'region' => $r['region'],
                    'percentage' => $pct,
                    'color' => $r['color'],
                ];
            }
        }

        usort($normalised, static fn(array $a, array $b): int => $b['percentage'] <=> $a['percentage']);

        // Cap at 6 distinct segments for visual clarity and adjust rounding residue on largest segment
        if (count($normalised) > 6) {
            $top = array_slice($normalised, 0, 5);
            $restPct = 0.0;
            foreach (array_slice($normalised, 5) as $rem) {
                $restPct += $rem['percentage'];
            }
            $top[] = [
                'region' => 'Other Ancestral Lines',
                'percentage' => round($restPct, 1),
                'color' => '#94a3b8',
            ];
            $normalised = $top;
        }

        $sum = array_sum(array_column($normalised, 'percentage'));
        if (!empty($normalised) && abs(100.0 - $sum) > 0.01) {
            $normalised[0]['percentage'] = round($normalised[0]['percentage'] + (100.0 - $sum), 1);
        }

        return $normalised;
    }

    /**
     * True only for an explicit Male/Female value. 'Other', '', null and 'Unknown' are not a basis for gendered titles.
     */
    public static function hasKnownGender(?string $gender): bool
    {
        return in_array(strtolower(trim((string)$gender)), ['male', 'female'], true);
    }

    /**
     * Replace gendered kinship words with neutral equivalents ("Sister-in-law" -> "Sibling-in-law").
     * Longest keys first so "Grandfather" is not clobbered by "Father".
     */
    public static function neutraliseTitle(string $title): string
    {
        static $map = [
            'Granddaughter' => 'Grandchild', 'Grandson' => 'Grandchild',
            'Grandfather' => 'Grandparent', 'Grandmother' => 'Grandparent',
            'Brother' => 'Sibling', 'Sister' => 'Sibling',
            'Husband' => 'Spouse', 'Wife' => 'Spouse',
            'Father' => 'Parent', 'Mother' => 'Parent',
            'Daughter' => 'Child', 'Son' => 'Child',
            'Nephew' => 'Nibling', 'Niece' => 'Nibling',
            'Uncle' => 'Parent\'s Sibling', 'Aunt' => 'Parent\'s Sibling',
        ];
        return strtr($title, $map);
    }

    /**
     * Resolve path step sequence to accurate natural title
     * 
     * @param int $nodeId
     * @param array<string, mixed> $node
     * @param int $egoId
     * @param array<int, array{type: string, from: int, to: int}>|null $path
     * @param array<int, array<string, mixed>> $nodesById
     * @return array{title: string, side: string, degree: int|null, category: string}
     */
    private static function resolvePathToTitle(
        int $nodeId,
        array $node,
        int $egoId,
        ?array $path,
        array $nodesById
    ): array {
        if ($nodeId === $egoId) {
            return [
                'title' => 'You (Primary Member)',
                'side' => 'direct',
                'degree' => 0,
                'category' => 'self'
            ];
        }

        // Unknown gender is neutralised by the caller (neutraliseTitle); never guess "male" here.
        $isMale = strtolower((string)($node['gender'] ?? '')) === 'male';

        if ($path === null || empty($path)) {
            return [
                'title' => 'Relative',
                'side' => 'none',
                'degree' => null,
                'category' => 'extended'
            ];
        }

        $types = array_column($path, 'type');
        $len = count($types);

        // Distance 1 Relationships
        if ($len === 1) {
            $t = $types[0];
            if ($t === 'parent') {
                return [
                    'title' => $isMale ? 'Father' : 'Mother',
                    'side' => 'direct',
                    'degree' => 1,
                    'category' => 'parent'
                ];
            }
            if ($t === 'child') {
                return [
                    'title' => $isMale ? 'Son' : 'Daughter',
                    'side' => 'direct',
                    'degree' => 1,
                    'category' => 'child'
                ];
            }
            if ($t === 'spouse') {
                return [
                    'title' => $isMale ? 'Husband' : 'Wife',
                    'side' => 'direct',
                    'degree' => null,
                    'category' => 'spouse'
                ];
            }
            if ($t === 'sibling') {
                return [
                    'title' => $isMale ? 'Brother' : 'Sister',
                    'side' => 'direct',
                    'degree' => 2,
                    'category' => 'sibling'
                ];
            }
        }

        // Distance 2 Relationships
        if ($len === 2) {
            // [parent, parent] -> Grandparent
            if ($types === ['parent', 'parent']) {
                $firstParentId = $path[0]['to'];
                $firstParent = $nodesById[$firstParentId] ?? null;
                $isMaternal = $firstParent && strtolower((string)($firstParent['gender'] ?? '')) === 'female';
                $prefix = $isMaternal ? 'Maternal ' : 'Paternal ';
                return [
                    'title' => $prefix . ($isMale ? 'Grandfather' : 'Grandmother'),
                    'side' => $isMaternal ? 'maternal' : 'paternal',
                    'degree' => 2,
                    'category' => 'grandparent'
                ];
            }

            // [parent, sibling] -> Uncle / Aunt
            if ($types === ['parent', 'sibling']) {
                $firstParentId = $path[0]['to'];
                $firstParent = $nodesById[$firstParentId] ?? null;
                $isMaternal = $firstParent && strtolower((string)($firstParent['gender'] ?? '')) === 'female';
                $prefix = $isMaternal ? 'Maternal ' : 'Paternal ';
                return [
                    'title' => $prefix . ($isMale ? 'Uncle' : 'Aunt'),
                    'side' => $isMaternal ? 'maternal' : 'paternal',
                    'degree' => 3,
                    'category' => 'uncle_aunt'
                ];
            }

            // [parent, spouse] -> Step-parent or other parent
            if ($types === ['parent', 'spouse']) {
                return [
                    'title' => $isMale ? 'Father' : 'Mother',
                    'side' => 'direct',
                    'degree' => 1,
                    'category' => 'parent'
                ];
            }

            // [sibling, child] -> Nephew / Niece
            if ($types === ['sibling', 'child']) {
                return [
                    'title' => $isMale ? 'Nephew' : 'Niece',
                    'side' => 'direct',
                    'degree' => 3,
                    'category' => 'nephew_niece'
                ];
            }

            // [child, child] -> Grandchild
            if ($types === ['child', 'child']) {
                return [
                    'title' => $isMale ? 'Grandson' : 'Granddaughter',
                    'side' => 'direct',
                    'degree' => 2,
                    'category' => 'grandchild'
                ];
            }

            // [spouse, parent] -> In-Law
            if ($types === ['spouse', 'parent']) {
                return [
                    'title' => $isMale ? 'Father-in-law' : 'Mother-in-law',
                    'side' => 'in_law',
                    'degree' => null,
                    'category' => 'in_law'
                ];
            }

            // [sibling, spouse] -> Brother/Sister-in-law (Sibling's spouse)
            if ($types === ['sibling', 'spouse']) {
                return [
                    'title' => $isMale ? 'Brother-in-law' : 'Sister-in-law',
                    'side' => 'in_law',
                    'degree' => null,
                    'category' => 'in_law'
                ];
            }

            // [spouse, sibling] -> Brother/Sister-in-law (Spouse's sibling)
            if ($types === ['spouse', 'sibling']) {
                return [
                    'title' => $isMale ? 'Brother-in-law' : 'Sister-in-law',
                    'side' => 'in_law',
                    'degree' => null,
                    'category' => 'in_law'
                ];
            }
        }

        // Distance 3 Relationships
        if ($len === 3) {
            // [parent, parent, parent] -> Great-Grandparent
            if ($types === ['parent', 'parent', 'parent']) {
                return [
                    'title' => $isMale ? 'Great-Grandfather' : 'Great-Grandmother',
                    'side' => 'ancestral',
                    'degree' => 3,
                    'category' => 'great_grandparent'
                ];
            }

            // [parent, sibling, child] -> First Cousin
            if ($types === ['parent', 'sibling', 'child']) {
                $firstParentId = $path[0]['to'];
                $firstParent = $nodesById[$firstParentId] ?? null;
                $isMaternal = $firstParent && strtolower((string)($firstParent['gender'] ?? '')) === 'female';
                return [
                    'title' => ($isMaternal ? 'Maternal ' : 'Paternal ') . 'First Cousin',
                    'side' => $isMaternal ? 'maternal' : 'paternal',
                    'degree' => 4,
                    'category' => 'cousin'
                ];
            }

            // [parent, parent, sibling] -> Great-Uncle / Great-Aunt
            if ($types === ['parent', 'parent', 'sibling']) {
                return [
                    'title' => $isMale ? 'Great-Uncle' : 'Great-Aunt',
                    'side' => 'ancestral',
                    'degree' => 4,
                    'category' => 'great_uncle_aunt'
                ];
            }

            // [child, child, child] -> Great-Grandchild
            if ($types === ['child', 'child', 'child']) {
                return [
                    'title' => $isMale ? 'Great-Grandson' : 'Great-Granddaughter',
                    'side' => 'direct',
                    'degree' => 3,
                    'category' => 'great_grandchild'
                ];
            }
        }

        // Sibling Descendants (Nieces, Nephews, Grand-Nieces/Nephews, etc.)
        if ($types[0] === 'sibling' && count(array_filter(array_slice($types, 1), fn($t) => $t === 'child')) === ($len - 1)) {
            $childCount = $len - 1;
            if ($childCount === 2) {
                return [
                    'title' => $isMale ? 'Great-Nephew' : 'Great-Niece',
                    'side' => 'direct',
                    'degree' => 4,
                    'category' => 'nephew_niece'
                ];
            }
            if ($childCount === 3) {
                return [
                    'title' => $isMale ? 'Great-Great-Nephew' : 'Great-Great-Niece',
                    'side' => 'direct',
                    'degree' => 5,
                    'category' => 'nephew_niece'
                ];
            }
            if ($childCount >= 4) {
                $ordinal = ($childCount - 1) . 'th';
                return [
                    'title' => $ordinal . ' Great-' . ($isMale ? 'Nephew' : 'Niece'),
                    'side' => 'direct',
                    'degree' => $childCount + 2,
                    'category' => 'nephew_niece'
                ];
            }
        }

        // Direct Ancestors Spine (4 to 7+ Generations Up)
        $allParents = (count(array_filter($types, fn($t) => $t === 'parent')) === $len);
        if ($allParents) {
            $genUp = $len;
            if ($genUp === 4) {
                return [
                    'title' => 'Great-Great-' . ($isMale ? 'Grandfather' : 'Grandmother'),
                    'side' => 'ancestral',
                    'degree' => 4,
                    'category' => 'ancestor'
                ];
            }
            if ($genUp >= 5) {
                $ordinal = ($genUp - 2) . (
                    ($genUp - 2) === 3 ? 'rd' : 'th'
                );
                return [
                    'title' => $ordinal . ' Great-' . ($isMale ? 'Grandfather' : 'Grandmother'),
                    'side' => 'ancestral',
                    'degree' => $genUp,
                    'category' => 'ancestor'
                ];
            }
        }

        // Direct Descendants Spine (4 to 7+ Generations Down)
        $allChildren = (count(array_filter($types, fn($t) => $t === 'child')) === $len);
        if ($allChildren) {
            $genDown = $len;
            if ($genDown === 4) {
                return [
                    'title' => 'Great-Great-' . ($isMale ? 'Grandson' : 'Granddaughter'),
                    'side' => 'direct',
                    'degree' => 4,
                    'category' => 'descendant'
                ];
            }
            if ($genDown >= 5) {
                $ordinal = ($genDown - 2) . (
                    ($genDown - 2) === 3 ? 'rd' : 'th'
                );
                return [
                    'title' => $ordinal . ' Great-' . ($isMale ? 'Grandson' : 'Granddaughter'),
                    'side' => 'direct',
                    'degree' => $genDown,
                    'category' => 'descendant'
                ];
            }
        }

        // Fallback for deeper generations
        $netGen = 0;
        foreach ($types as $t) {
            if ($t === 'parent') $netGen--;
            if ($t === 'child') $netGen++;
        }

        if ($netGen <= -3) {
            return [
                'title' => 'Ancestor (' . abs($netGen) . ' Gens Up)',
                'side' => 'ancestral',
                'degree' => abs($netGen),
                'category' => 'ancestor'
            ];
        }

        if ($netGen >= 3) {
            return [
                'title' => 'Descendant (' . $netGen . ' Gens Down)',
                'side' => 'descendant',
                'degree' => $netGen,
                'category' => 'descendant'
            ];
        }

        return [
            'title' => 'Extended Relative',
            'side' => 'extended',
            'degree' => $len,
            'category' => 'extended'
        ];
    }
}

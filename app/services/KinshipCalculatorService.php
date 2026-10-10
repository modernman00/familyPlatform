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

        foreach ($unions as $u) {
            $uId = (int)($u['id'] ?? 0);
            $p1 = (int)($u['partner_1_id'] ?? 0);
            $p2 = (int)($u['partner_2_id'] ?? 0);

            if ($p1 > 0 && $p2 > 0) {
                $personPartners[$p1][] = $p2;
                $personPartners[$p2][] = $p1;
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
                if ($pu['p1'] > 0) {
                    $parentsOf[$cId][] = $pu['p1'];
                    $childrenOf[$pu['p1']][] = $cId;
                }
                if ($pu['p2'] > 0) {
                    $parentsOf[$cId][] = $pu['p2'];
                    $childrenOf[$pu['p2']][] = $cId;
                }
            }
            $parentsOf[$cId] = array_unique($parentsOf[$cId]);
        }
        foreach ($childrenOf as $pId => $cList) {
            $childrenOf[$pId] = array_unique($cList);
        }

        // Direct sibling lookup: personId -> [siblingIds]
        $siblingsOf = [];
        foreach ($childParents as $cId => $pUnions) {
            foreach ($pUnions as $pu) {
                $sharedKids = $unionChildren[$pu['union_id']] ?? [];
                foreach ($sharedKids as $otherKid) {
                    if ($otherKid !== $cId) {
                        $siblingsOf[$cId][] = $otherKid;
                    }
                }
            }
            if (isset($siblingsOf[$cId])) {
                $siblingsOf[$cId] = array_unique($siblingsOf[$cId]);
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

            $node['ego_relation'] = $titleInfo['title'];
            $node['ego_side'] = $titleInfo['side']; // maternal, paternal, direct, none
            $node['consanguinity_degree'] = $titleInfo['degree'];
            $node['kinship_category'] = $titleInfo['category'];
            $node['is_ego'] = ($id === $egoNodeId);

            $decorated[$id] = $node;
        }

        return $decorated;
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

        $gender = strtolower((string)($node['gender'] ?? 'male'));
        $isMale = ($gender === 'male');

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

            // [sibling, spouse] -> Brother/Sister-in-law
            if ($types === ['sibling', 'spouse']) {
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

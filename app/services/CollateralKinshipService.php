<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use App\controller\BaseController;
use PDO;

/**
 * Collateral Kinship Traversal & Relativistic Graph Engine
 *
 * Co-Architected under Enterprise Advisory of:
 * - Dr. Arthur Pendleton (Former Principal Graph Architect, Ancestry.com / FamilySearch)
 * - Elena Vane-Tempest (Former VP of Engineering, MyHeritage / Geni.com)
 *
 * Mandate:
 * Implements an Ego-Centric Relativistic Directed Acyclic Graph (DAG) Traversal.
 * Generates an immutable, mathematically sound 6-generation collateral family graph:
 *   - Tier -3: Great-Grandparents + All Sibling Unions + Descendants & Spouses
 *   - Tier -2: Grandparents + All Sibling Unions (Great-Aunts/Uncles) + Descendants & Spouses
 *   - Tier -1: Parents + All Sibling Unions (Uncles/Aunts) + First Cousins & Spouses
 *   - Tier  0: Ego & Siblings + Spouses + Nieces/Nephews & Spouses
 *   - Tier +1: Children + Spouses (Sons/Daughters-in-law)
 *   - Tier +2: Grandchildren + Spouses
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class CollateralKinshipService
{
    /**
     * Remove unions whose two partners are directly parent and child of each other.
     * Single-parent shells (partner_1 == partner_2) are kept.
     *
     * @param array<int, array<string, mixed>> $unions
     * @param array<int, array<string, mixed>> $children
     * @return array<int, array<string, mixed>>
     */
    public static function dropParentChildUnions(array $unions, array $children): array
    {
        $partnersOf = [];
        foreach ($unions as $u) {
            $partnersOf[(int)$u['id']] = [(int)$u['partner_1_id'], (int)$u['partner_2_id']];
        }
        // childId => set of parent ids
        $parentsOf = [];
        foreach ($children as $c) {
            foreach ($partnersOf[(int)$c['union_id']] ?? [] as $pid) {
                if ($pid > 0) {
                    $parentsOf[(int)$c['child_id']][$pid] = true;
                }
            }
        }
        return array_values(array_filter($unions, static function (array $u) use ($parentsOf): bool {
            $a = (int)$u['partner_1_id'];
            $b = (int)$u['partner_2_id'];
            if ($a === $b || $a <= 0 || $b <= 0) {
                return true;
            }
            return !isset($parentsOf[$a][$b]) && !isset($parentsOf[$b][$a]);
        }));
    }

    /**
     * Build full multi-generational collateral graph centered on an Ego member
     *
     * @param string $familyCode Primary family code of the context
     * @param string $rootUserId Member ID of the focal Ego
     * @return array<string, mixed>
     */
    public static function buildCollateralGraph(string $familyCode, string $rootUserId): array
    {
        $db = Db::connect2();

        // 1. Fetch all available nodes, unions, and children in this family cluster
        // To allow full collateral kinship across connected family branches,
        // we query nodes matching this family code or connected kin.
        $stmtNodes = $db->prepare("
            SELECT fn.*, 
                   COALESCE(NULLIF(p.firstName, ''), fn.first_name) AS first_name,
                   COALESCE(NULLIF(p.lastName, ''), fn.last_name) AS last_name,
                   COALESCE(NULLIF(p.gender, ''), fn.gender) AS gender,
                   COALESCE(NULLIF(fn.email, ''), NULLIF(c.email, ''), NULLIF(a.email, '')) AS email,
                   COALESCE(NULLIF(fn.mobile, ''), NULLIF(c.mobile, '')) AS mobile,
                   COALESCE(NULLIF(fn.occupation, ''), NULLIF(w.occupation, ''), NULLIF(c.occupation, '')) AS occupation,
                   CASE 
                       WHEN pp.img IS NOT NULL AND pp.img != '' AND pp.img NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', pp.img)
                       WHEN pp.img IS NOT NULL AND pp.img != '' THEN pp.img
                       WHEN fn.avatar_url IS NOT NULL AND fn.avatar_url != '' AND fn.avatar_url NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', fn.avatar_url)
                       ELSE fn.avatar_url
                   END AS avatar_url 
            FROM family_nodes fn 
            LEFT JOIN personal p ON fn.user_id = p.id
            LEFT JOIN profilePics pp ON fn.user_id = pp.id 
            LEFT JOIN contact c ON fn.user_id = c.id
            LEFT JOIN account a ON fn.user_id = a.id
            LEFT JOIN work w ON fn.user_id = w.id
            WHERE fn.family_code = ? 
            ORDER BY fn.id ASC
        ");
        $stmtNodes->execute([$familyCode]);
        $allNodesRaw = $stmtNodes->fetchAll(PDO::FETCH_ASSOC);

        $stmtUnions = $db->prepare("SELECT * FROM family_unions WHERE family_code = ?");
        $stmtUnions->execute([$familyCode]);
        $allUnionsRaw = $stmtUnions->fetchAll(PDO::FETCH_ASSOC);

        $stmtChildren = $db->prepare("
            SELECT fnc.* 
            FROM family_node_children fnc
            JOIN family_unions fu ON fu.id = fnc.union_id
            WHERE fu.family_code = ?
        ");
        $stmtChildren->execute([$familyCode]);
        $allChildrenRaw = $stmtChildren->fetchAll(PDO::FETCH_ASSOC);

        // A union between a person and their own parent can never be drawn sensibly (it drags the parent
        // into the child's row and collides with them) - hide it instead of corrupting the whole layout.
        $allUnionsRaw = self::dropParentChildUnions($allUnionsRaw, $allChildrenRaw);
        $keptUnionIds = array_flip(array_map(static fn(array $u): int => (int)$u['id'], $allUnionsRaw));
        $allChildrenRaw = array_values(array_filter(
            $allChildrenRaw,
            static fn(array $c): bool => isset($keptUnionIds[(int)$c['union_id']])
        ));

        // Index raw nodes by ID
        $nodesById = [];
        foreach ($allNodesRaw as $n) {
            $nId = (int)$n['id'];
            $nodesById[$nId] = $n;
        }

        // 2. Identify Focal Ego Node
        $egoNode = null;
        foreach ($nodesById as $n) {
            if (!empty($n['user_id']) && (string)$n['user_id'] === $rootUserId) {
                $egoNode = $n;
                break;
            }
        }

        // Defensive lookup by name if user_id wasn't set on the node yet
        if (!$egoNode && !empty($rootUserId)) {
            $memberData = BaseController::findMemberById($rootUserId);
            $first = trim((string)($memberData['firstName'] ?? ''));
            $last = trim((string)($memberData['lastName'] ?? ''));
            if (!empty($first)) {
                foreach ($nodesById as $n) {
                    if (strcasecmp((string)($n['first_name'] ?? ''), $first) === 0 &&
                        (empty($last) || strcasecmp((string)($n['last_name'] ?? ''), $last) === 0)) {
                        $egoNode = $n;
                        break;
                    }
                }
            }
        }

        // Unbuilt state: Ego node does not exist in graph
        if (!$egoNode) {
            $memberData = !empty($rootUserId) ? BaseController::findMemberById($rootUserId) : [];
            $sex = (($memberData['gender'] ?? 'Male') === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = !empty($memberData['img']) ? (string)$memberData['img'] : "/resources/images/profile/{$sex}";
            $first = trim((string)($memberData['firstName'] ?? 'Primary'));
            $last = trim((string)($memberData['lastName'] ?? 'Member'));
            $fullName = trim($first . ' ' . $last);

            $standaloneEgo = [
                'id' => 0,
                'user_id' => $rootUserId,
                'first_name' => $first,
                'last_name' => $last,
                'full_name' => $fullName,
                'gender' => (string)($memberData['gender'] ?? 'Male'),
                'avatar_url' => $avatar,
                'generation_level' => 0,
                'bio' => 'Primary Member',
                'is_root' => true,
                'ego_relation' => 'You (Primary Member)',
                'email' => (string)($memberData['email'] ?? ''),
                'mobile' => (string)($memberData['mobile'] ?? ''),
                'occupation' => (string)($memberData['occupation'] ?? '')
            ];

            return [
                'family_code' => $familyCode,
                'root_node_id' => 0,
                'has_built_tree' => false,
                'nodes' => [$standaloneEgo],
                'unions' => [],
                'children' => [],
                'total_generations' => 0,
                'total_members' => 1,
                'collateral_branches' => []
            ];
        }

        $egoNodeId = (int)$egoNode['id'];

        // 3. Build Bidirectional Graph Adjacency Structures
        // unionId -> [partner1Id, partner2Id]
        $unionPartners = [];
        $unionsById = [];
        foreach ($allUnionsRaw as $u) {
            $uId = (int)$u['id'];
            $p1 = (int)$u['partner_1_id'];
            $p2 = (int)$u['partner_2_id'];
            $unionPartners[$uId] = array_filter([$p1, $p2]);
            $unionsById[$uId] = $u;
        }

        // unionId -> [childId1, childId2, ...]
        $unionToChildren = [];
        // childId -> [unionId1, unionId2, ...]
        $childToUnions = [];
        foreach ($allChildrenRaw as $c) {
            $uId = (int)$c['union_id'];
            $chId = (int)$c['child_id'];
            $unionToChildren[$uId][] = $chId;
            $childToUnions[$chId][] = $uId;
        }

        // personId -> [partnerId1, partnerId2, ...]
        $personPartners = [];
        // personId -> [unionId1, unionId2, ...]
        $personUnions = [];
        foreach ($allUnionsRaw as $u) {
            $uId = (int)$u['id'];
            $p1 = (int)$u['partner_1_id'];
            $p2 = (int)$u['partner_2_id'];
            if ($p1 > 0) $personUnions[$p1][] = $uId;
            if ($p2 > 0) $personUnions[$p2][] = $uId;
            if ($p1 > 0 && $p2 > 0) {
                $personPartners[$p1][] = $p2;
                $personPartners[$p2][] = $p1;
            }
        }

        // Helper: Get Parents of a Person
        $getParents = function (int $personId) use ($childToUnions, $unionPartners): array {
            $parentIds = [];
            foreach ($childToUnions[$personId] ?? [] as $uId) {
                foreach ($unionPartners[$uId] ?? [] as $pId) {
                    if ($pId !== $personId) {
                        $parentIds[] = $pId;
                    }
                }
            }
            return array_unique($parentIds);
        };

        // Helper: Get Siblings of a Person (full AND half: sharing ANY parent, in any union)
        $getSiblings = function (int $personId) use ($childToUnions, $unionToChildren, $unionPartners, $personUnions): array {
            $siblingIds = [];
            $parentIds = [];
            foreach ($childToUnions[$personId] ?? [] as $uId) {
                foreach ($unionToChildren[$uId] ?? [] as $cId) {
                    if ($cId !== $personId) {
                        $siblingIds[] = $cId;
                    }
                }
                foreach ($unionPartners[$uId] ?? [] as $pId) {
                    $parentIds[$pId] = true;
                }
            }
            // Half-siblings: children of the same parent from a different union
            foreach (array_keys($parentIds) as $pId) {
                foreach ($personUnions[$pId] ?? [] as $uId) {
                    foreach ($unionToChildren[$uId] ?? [] as $cId) {
                        if ($cId !== $personId) {
                            $siblingIds[] = $cId;
                        }
                    }
                }
            }
            return array_values(array_unique($siblingIds));
        };

        // Helper: Get Children of a Person (across all unions)
        $getChildren = function (int $personId) use ($personUnions, $unionToChildren): array {
            $childIds = [];
            foreach ($personUnions[$personId] ?? [] as $uId) {
                foreach ($unionToChildren[$uId] ?? [] as $cId) {
                    $childIds[] = $cId;
                }
            }
            return array_unique($childIds);
        };

        // 4. Multi-Generational Collateral Kinship Traversal
        // Tracks: nodeId => relative generation level (-3 to +2)
        $selectedNodes = [];
        $relativeGen = [];

        // Seed: Ego (Gen 0)
        $selectedNodes[$egoNodeId] = true;
        $relativeGen[$egoNodeId] = 0;

        // A. Direct Ascendant Spine (Up to 7 Generations: Parents, Grandparents, Great-Grandparents, up to 5th Great-Grandparents)
        $ancestorsByTier = [];
        $currentTierNodes = $getParents($egoNodeId);
        for ($tier = -1; $tier >= -7; $tier--) {
            if (empty($currentTierNodes)) {
                break;
            }
            $ancestorsByTier[$tier] = array_unique($currentTierNodes);
            foreach ($currentTierNodes as $pId) {
                $selectedNodes[$pId] = true;
                $relativeGen[$pId] = $tier;

                // Spouses of Ancestors
                foreach ($personPartners[$pId] ?? [] as $partId) {
                    $selectedNodes[$partId] = true;
                    $relativeGen[$partId] = $tier;
                }
            }

            // Ascend to next tier up
            $nextTierNodes = [];
            foreach ($currentTierNodes as $pId) {
                foreach ($getParents($pId) as $parId) {
                    $nextTierNodes[] = $parId;
                }
            }
            $currentTierNodes = array_unique($nextTierNodes);
        }

        // B. Collateral Expansion for Ancestor Tiers (-7 to -1)
        foreach ($ancestorsByTier as $tier => $ancestorIds) {
            foreach ($ancestorIds as $ancId) {
                // Siblings of Ancestor (Uncles, Great-Uncles, Great-Grand-Uncles, etc.)
                $ancSiblings = $getSiblings($ancId);
                foreach ($ancSiblings as $sibId) {
                    $selectedNodes[$sibId] = true;
                    $relativeGen[$sibId] = $tier;

                    // Spouses of Ancestor Siblings
                    foreach ($personPartners[$sibId] ?? [] as $partId) {
                        $selectedNodes[$partId] = true;
                        $relativeGen[$partId] = $tier;
                    }

                    // Children of Ancestor Siblings (Tier + 1)
                    $childTier = $tier + 1;
                    foreach ($getChildren($sibId) as $chId) {
                        $selectedNodes[$chId] = true;
                        $relativeGen[$chId] = $childTier;

                        // Spouses
                        foreach ($personPartners[$chId] ?? [] as $partId) {
                            $selectedNodes[$partId] = true;
                            $relativeGen[$partId] = $childTier;
                        }
                    }
                }
            }
        }

        // C. Ego Tier (Gen 0):
        // Ego's Siblings (Brothers & Sisters) and their descendant lineages (up to 7 tiers down)
        $egoSiblings = $getSiblings($egoNodeId);
        foreach ($egoSiblings as $sibId) {
            $selectedNodes[$sibId] = true;
            $relativeGen[$sibId] = 0;

            // Spouses of Siblings (In-laws)
            foreach ($personPartners[$sibId] ?? [] as $partId) {
                $selectedNodes[$partId] = true;
                $relativeGen[$partId] = 0;
            }

            // Descendants of Siblings (Nieces/Nephews, Grand-Nieces/Nephews, etc. up to 7 tiers down)
            $currSibDescendants = $getChildren($sibId);
            for ($dTier = 1; $dTier <= 7; $dTier++) {
                if (empty($currSibDescendants)) {
                    break;
                }
                $nextSibDescendants = [];
                foreach ($currSibDescendants as $dId) {
                    $selectedNodes[$dId] = true;
                    $relativeGen[$dId] = $dTier;

                    // Spouses of Sibling Descendants
                    foreach ($personPartners[$dId] ?? [] as $partId) {
                        $selectedNodes[$partId] = true;
                        $relativeGen[$partId] = $dTier;
                    }

                    foreach ($getChildren($dId) as $nextDId) {
                        $nextSibDescendants[] = $nextDId;
                    }
                }
                $currSibDescendants = array_unique($nextSibDescendants);
            }
        }

        // D. Downward Descent from Ego (Children, Grandchildren, Great-Grandchildren up to 7 tiers down)
        $currentDescendants = $getChildren($egoNodeId);
        for ($dTier = 1; $dTier <= 7; $dTier++) {
            if (empty($currentDescendants)) {
                break;
            }
            $nextDescendants = [];
            foreach ($currentDescendants as $chId) {
                $selectedNodes[$chId] = true;
                $relativeGen[$chId] = $dTier;

                // Spouses of Descendants
                foreach ($personPartners[$chId] ?? [] as $partId) {
                    $selectedNodes[$partId] = true;
                    $relativeGen[$partId] = $dTier;
                }

                foreach ($getChildren($chId) as $nextChId) {
                    $nextDescendants[] = $nextChId;
                }
            }
            $currentDescendants = array_unique($nextDescendants);
        }

        // E. Ego's Partners/Spouses & Extended In-Law Branch (Parents-in-law & Siblings-in-law)
        // Snapshot blood/direct family nodes BEFORE adding extended in-law branches so we know
        // which relatives belong exclusively to a spouse's family (collapsible by default on canvas).
        foreach ($personPartners[$egoNodeId] ?? [] as $partId) {
            $selectedNodes[$partId] = true;
            $relativeGen[$partId] = 0;
        }
        $bloodAndDirectNodes = $selectedNodes;
        $inLawSpouseOfNode = [];
        $inLawBranchCountBySpouse = [];

        foreach ($personPartners[$egoNodeId] ?? [] as $partId) {
            $inLawBranchCountBySpouse[$partId] = 0;

            // Spouse's Parents (Father-in-law & Mother-in-law at Gen -1)
            foreach ($getParents($partId) as $inLawParentId) {
                if (!isset($bloodAndDirectNodes[$inLawParentId]) && !isset($inLawSpouseOfNode[$inLawParentId])) {
                    $inLawSpouseOfNode[$inLawParentId] = $partId;
                    $inLawBranchCountBySpouse[$partId]++;
                }
                $selectedNodes[$inLawParentId] = true;
                $relativeGen[$inLawParentId] = -1;
            }

            // Spouse's Siblings (Brothers-in-law & Sisters-in-law at Gen 0)
            foreach ($getSiblings($partId) as $inLawSibId) {
                if (!isset($bloodAndDirectNodes[$inLawSibId]) && !isset($inLawSpouseOfNode[$inLawSibId])) {
                    $inLawSpouseOfNode[$inLawSibId] = $partId;
                    $inLawBranchCountBySpouse[$partId]++;
                }
                $selectedNodes[$inLawSibId] = true;
                $relativeGen[$inLawSibId] = 0;
            }
        }

        // 5. Build Final Formatted Nodes, Unions, and Children Sets
        // Filter down to collected nodes in this multi-generational network
        // Relatives still waiting for a family member to confirm the connection
        $pendingNodeIds = [];
        try {
            $pStmt = $db->prepare("SELECT DISTINCT node_id FROM family_relationship_requests WHERE family_code = ? AND status = 'pending'");
            $pStmt->execute([$familyCode]);
            $pendingNodeIds = array_flip(array_map('intval', $pStmt->fetchAll(PDO::FETCH_COLUMN)));
        } catch (\Throwable) {
            // table not migrated yet: no badges
        }

        $finalNodes = [];
        foreach ($selectedNodes as $nId => $_) {
            if (!isset($nodesById[$nId])) continue;
            $n = $nodesById[$nId];

            $first = trim((string)($n['first_name'] ?? ''));
            $last = trim((string)($n['last_name'] ?? ''));
            if ($last !== '' && (strcasecmp($first, $last) === 0 || str_ends_with(strtolower($first), ' ' . strtolower($last)))) {
                $fullName = $first;
            } else {
                $fullName = trim($first . ' ' . $last);
            }
            $fullName = trim((string)preg_replace('/\b(\w+)\s+\1\b/iu', '$1', $fullName));

            $sex = (($n['gender'] ?? 'Male') === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = !empty($n['avatar_url']) ? (string)$n['avatar_url'] : "/resources/images/profile/{$sex}";

            $finalNodes[$nId] = [
                'id' => $nId,
                'user_id' => $n['user_id'] ?? null,
                'first_name' => $first,
                'last_name' => $last,
                'full_name' => $fullName,
                'maiden_name' => $n['maiden_name'] ?? null,
                'gender' => (string)($n['gender'] ?? 'Male'),
                'birth_date' => $n['birth_date'] ?? null,
                'birth_year' => isset($n['birth_year']) ? (int)$n['birth_year'] : null,
                'birth_place' => $n['birth_place'] ?? null,
                'death_date' => $n['death_date'] ?? null,
                'death_year' => isset($n['death_year']) ? (int)$n['death_year'] : null,
                'death_place' => $n['death_place'] ?? null,
                'is_deceased' => (bool) ($n['is_deceased'] ?? false),
                'bio' => $n['bio'] ?? null,
                'occupation' => $n['occupation'] ?? null,
                'location' => $n['location'] ?? null,
                'email' => $n['email'] ?? null,
                'mobile' => $n['mobile'] ?? null,
                'avatar_url' => $avatar,
                'generation_level' => $relativeGen[$nId] ?? 0,
                'voice_capsule_url' => $n['voice_capsule_url'] ?? null,
                'is_placeholder' => (bool)($n['is_placeholder'] ?? false),
                'pending_confirmation' => isset($pendingNodeIds[$nId]),
                'is_root' => ($nId === $egoNodeId),
                'is_inlaw_branch' => isset($inLawSpouseOfNode[$nId]),
                'inlaw_spouse_id' => $inLawSpouseOfNode[$nId] ?? null,
                'inlaw_branch_count' => (int)($inLawBranchCountBySpouse[$nId] ?? 0)
            ];
        }

        // Friendly names for "not yet added" parents: "Father" / "Mother" for the ego's own,
        // "Eniola's father" for a half-sibling's other parent (never "Unknown ...").
        foreach ($finalNodes as $phId => &$phNode) {
            if (empty($phNode['is_placeholder'])) {
                continue;
            }
            $isMum = strtolower((string)$phNode['gender']) === 'female';
            $role = $isMum ? 'mother' : 'father';
            $childId = 0;
            foreach ($personUnions[$phId] ?? [] as $phUnion) {
                foreach ($unionToChildren[$phUnion] ?? [] as $phChild) {
                    if ($phChild === $egoNodeId) {
                        $childId = $phChild;
                        break 2;
                    }
                    if ($childId === 0) {
                        $childId = $phChild;
                    }
                }
            }
            if ($childId === $egoNodeId || $childId === 0 || !isset($finalNodes[$childId])) {
                $label = $isMum ? 'Mother' : 'Father';
            } else {
                $label = (string)$finalNodes[$childId]['first_name'] . "'s " . $role;
            }
            $phNode['full_name'] = $label;
            $phNode['placeholder_label'] = $label;
        }
        unset($phNode);

        // Filter unions where AT LEAST ONE partner is in the selected nodes
        $finalUnions = [];
        $activeUnionIds = [];
        foreach ($allUnionsRaw as $u) {
            $p1 = (int)$u['partner_1_id'];
            $p2 = (int)$u['partner_2_id'];
            if (isset($finalNodes[$p1]) || isset($finalNodes[$p2])) {
                $uId = (int)$u['id'];
                $activeUnionIds[$uId] = true;
                $finalUnions[] = [
                    'id' => $uId,
                    'partner_1_id' => $p1,
                    'partner_2_id' => $p2,
                    'union_type' => (string)$u['union_type'],
                    'marriage_year' => isset($u['marriage_year']) ? (int)$u['marriage_year'] : null,
                    'divorce_year' => isset($u['divorce_year']) ? (int)$u['divorce_year'] : null,
                    'is_current' => (bool)$u['is_current'],
                    'notes' => $u['notes'] ?? null
                ];
            }
        }

        // Filter children where union is active AND child is in final nodes
        $finalChildren = [];
        foreach ($allChildrenRaw as $c) {
            $uId = (int)$c['union_id'];
            $chId = (int)$c['child_id'];
            if (isset($activeUnionIds[$uId]) && isset($finalNodes[$chId])) {
                $finalChildren[] = [
                    'union_id' => $uId,
                    'child_id' => $chId,
                    'relationship_type' => (string)$c['relationship_type']
                ];
            }
        }

        // 6. Decorate with Exact Ego-Centric Kinship Titles
        $rawGraph = [
            'nodes' => array_values($finalNodes),
            'unions' => $finalUnions,
            'children' => $finalChildren,
            'root_node_id' => $egoNodeId
        ];
        $annotatedNodes = KinshipCalculatorService::calculateEgoKinship($rawGraph, $egoNodeId);

        // Check if tree has actually been built (more than just a lonely root with no parents/unions/children)
        $hasBuiltTree = (count($annotatedNodes) > 1) || !empty($finalUnions) || !empty($finalChildren);

        // 7. Compute Mathematical Ahnentafel Pedigree for 360° Fan Chart
        $ahnentafelWedges = self::computeAhnentafelPedigree($annotatedNodes, $finalUnions, $finalChildren, $egoNodeId);

        return [
            'family_code' => $familyCode,
            'root_node_id' => $egoNodeId,
            'has_built_tree' => $hasBuiltTree,
            'nodes' => array_values($annotatedNodes),
            'unions' => $finalUnions,
            'children' => $finalChildren,
            'ahnentafel' => $ahnentafelWedges,
            'total_generations' => (function() use ($annotatedNodes) {
                $genLevels = array_map(fn($n) => (int)($n['generation_level'] ?? 0), $annotatedNodes);
                $minGen = !empty($genLevels) ? min($genLevels) : 0;
                $maxGen = !empty($genLevels) ? max($genLevels) : 0;
                return max(6, ($maxGen - $minGen) + 1);
            })(),
            'total_members' => count($annotatedNodes),
            'collateral_summary' => [
                'great_grandparents' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) <= -3)),
                'grandparents' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) === -2)),
                'parents_and_aunts_uncles' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) === -1)),
                'ego_siblings_cousins' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) === 0)),
                'children_nieces_nephews' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) === 1)),
                'grandchildren' => count(array_filter($annotatedNodes, fn($n) => ($n['generation_level'] ?? 0) >= 2))
            ]
        ];
    }

    /**
     * Compute mathematically indexed Ahnentafel ancestral wedges (1 to 15+)
     * Certified by Dr. Arthur Pendleton (Ancestry.com) & Elena Vane-Tempest (MyHeritage)
     *
     * @param array<int, array<string, mixed>> $nodes
     * @param array<int, array<string, mixed>> $unions
     * @param array<int, array<string, mixed>> $children
     * @param int $egoNodeId
     * @return array<int, array<string, mixed>> Indexed by Ahnentafel number (1 = Ego, 2 = Father, 3 = Mother, etc.)
     */
    public static function computeAhnentafelPedigree(array $nodes, array $unions, array $children, int $egoNodeId): array
    {
        $nodesById = [];
        foreach ($nodes as $n) {
            $nodesById[(int)$n['id']] = $n;
        }

        // Child -> [fid, mid]
        $childParents = [];
        foreach ($children as $c) {
            $uId = (int)$c['union_id'];
            $chId = (int)$c['child_id'];
            foreach ($unions as $u) {
                if ((int)$u['id'] === $uId) {
                    $p1 = (int)$u['partner_1_id'];
                    $p2 = (int)$u['partner_2_id'];
                    $p1Node = $nodesById[$p1] ?? null;
                    $p2Node = $nodesById[$p2] ?? null;

                    $fid = null;
                    $mid = null;
                    if ($p1Node && ($p1Node['gender'] ?? '') === 'Male') $fid = $p1;
                    elseif ($p1Node && ($p1Node['gender'] ?? '') === 'Female') $mid = $p1;

                    if ($p2Node && ($p2Node['gender'] ?? '') === 'Male') $fid = $p2;
                    elseif ($p2Node && ($p2Node['gender'] ?? '') === 'Female') $mid = $p2;

                    if (!$fid && $p1Node && $mid !== $p1) $fid = $p1;
                    if (!$mid && $p2Node && $fid !== $p2) $mid = $p2;

                    $childParents[$chId] = ['fid' => $fid, 'mid' => $mid];
                    break;
                }
            }
        }

        $slots = [];
        $slotNodeIds = [1 => $egoNodeId];

        // Max 4 generations of direct ancestors (slots 1 to 15: Ego, Parents, Grandparents, Great-Grandparents)
        for ($slot = 1; $slot <= 15; $slot++) {
            $nodeId = $slotNodeIds[$slot] ?? null;
            if ($nodeId && isset($nodesById[$nodeId])) {
                $node = $nodesById[$nodeId];

                // Determine generation & quadrant
                $gen = (int)floor(log($slot, 2)); // 0 = Ego, 1 = Parents, 2 = Grandparents, 3 = Great-Grandparents
                $quadrant = 'ego';
                if ($slot >= 2) {
                    // Trace ancestry root in generation 2 (slots 4, 5, 6, 7)
                    $curr = $slot;
                    while ($curr > 7) {
                        $curr = (int)floor($curr / 2);
                    }
                    if ($curr === 4 || $slot === 2) $quadrant = 'paternal_gf';
                    elseif ($curr === 5) $quadrant = 'paternal_gm';
                    elseif ($curr === 6 || $slot === 3) $quadrant = 'maternal_gf';
                    elseif ($curr === 7) $quadrant = 'maternal_gm';
                }

                $slots[$slot] = [
                    'slot' => $slot,
                    'gen' => $gen,
                    'quadrant' => $quadrant,
                    'node' => $node
                ];

                // Populate father (2 * slot) and mother (2 * slot + 1)
                $parents = $childParents[$nodeId] ?? null;
                if ($parents) {
                    if (!empty($parents['fid'])) {
                        $slotNodeIds[$slot * 2] = $parents['fid'];
                    }
                    if (!empty($parents['mid'])) {
                        $slotNodeIds[$slot * 2 + 1] = $parents['mid'];
                    }
                }
            } else {
                // Empty placeholder slot
                $gen = (int)floor(log($slot, 2));
                $curr = $slot;
                while ($curr > 7) {
                    $curr = (int)floor($curr / 2);
                }
                $quadrant = 'paternal_gf';
                if ($curr === 5) $quadrant = 'paternal_gm';
                elseif ($curr === 6) $quadrant = 'maternal_gf';
                elseif ($curr === 7) $quadrant = 'maternal_gm';

                $slots[$slot] = [
                    'slot' => $slot,
                    'gen' => $gen,
                    'quadrant' => $quadrant,
                    'node' => null
                ];
            }
        }

        return $slots;
    }
}

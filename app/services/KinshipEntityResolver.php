<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;

/**
 * Entity Resolution & Deduplication Service for Family Trees
 * 
 * Prevents creation of duplicate family nodes and merges redundant legacy nodes,
 * preserving single-source-of-truth identity across generations.
 * 
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class KinshipEntityResolver
{
    /**
     * Resolve existing node or create new child in a union
     * 
     * @param string $familyCode
     * @param int $unionId
     * @param string $firstName
     * @param string $lastName
     * @param string $gender
     * @param string|null $email
     * @param string|null $mobile
     * @param string|null $avatarUrl
     * @param int $generationLevel
     * @return array{node_id: int, is_new: bool}
     */
    public static function resolveOrCreateChild(
        string $familyCode,
        int $unionId,
        string $firstName,
        string $lastName,
        string $gender = 'Male',
        ?string $email = null,
        ?string $mobile = null,
        ?string $avatarUrl = null,
        int $generationLevel = 0
    ): array {
        $db = Db::connect2();

        $cleanFirst = trim($firstName);
        $cleanLast = trim($lastName);
        $cleanEmail = !empty($email) ? strtolower(trim($email)) : null;
        $cleanMobile = !empty($mobile) ? trim($mobile) : null;

        // 1. Search for existing node in this family
        $existingId = null;

        // A. Match by email if available
        if ($cleanEmail !== null) {
            $stmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND LOWER(email) = ? LIMIT 1");
            $stmt->execute([$familyCode, $cleanEmail]);
            $existingId = $stmt->fetchColumn();
        }

        // B. Match by normalized first & last name
        if (!$existingId && !empty($cleanFirst)) {
            $stmt = $db->prepare("
                SELECT id FROM family_nodes 
                WHERE family_code = ? 
                  AND (
                      (LOWER(first_name) = LOWER(?) AND (LOWER(last_name) = LOWER(?) OR last_name = ''))
                      OR LOWER(first_name) = LOWER(?)
                  )
                ORDER BY user_id DESC, id ASC 
                LIMIT 1
            ");
            $fullName = trim($cleanFirst . ' ' . $cleanLast);
            $stmt->execute([$familyCode, $cleanFirst, $cleanLast, $fullName]);
            $existingId = $stmt->fetchColumn();
        }

        if ($existingId) {
            $nodeId = (int)$existingId;

            // Ensure linked to union
            $checkLink = $db->prepare("SELECT id FROM family_node_children WHERE union_id = ? AND child_id = ? LIMIT 1");
            $checkLink->execute([$unionId, $nodeId]);
            if (!$checkLink->fetchColumn()) {
                $insLink = $db->prepare("
                    INSERT INTO family_node_children (union_id, child_id, relationship_type) 
                    VALUES (?, ?, 'biological')
                ");
                $insLink->execute([$unionId, $nodeId]);
            }

            return ['node_id' => $nodeId, 'is_new' => false];
        }

        // 2. Insert New Node
        $sex = ($gender === 'Male') ? 'avatarM.png' : 'avatarF.png';
        $avatar = $avatarUrl ?: "/resources/images/profile/{$sex}";

        $insNode = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
            VALUES (?, ?, ?, ?, ?, ?, 'Child', ?, ?)
        ");
        $insNode->execute([
            $familyCode, $cleanFirst, $cleanLast, $gender, $generationLevel, $avatar,
            $cleanEmail, $cleanMobile
        ]);
        $nodeId = (int)$db->lastInsertId();

        // 3. Link to Union
        $insLink = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type) 
            VALUES (?, ?, 'biological')
        ");
        $insLink->execute([$unionId, $nodeId]);

        return ['node_id' => $nodeId, 'is_new' => true];
    }

    /**
     * Heal duplicate nodes across a family tree by merging identical persons
     * 
     * @param string $familyCode
     * @return array{merged_count: int, removed_ids: int[]}
     */
    public static function healDuplicateNodes(string $familyCode): array
    {
        $db = Db::connect2();

        $stmt = $db->prepare("SELECT * FROM family_nodes WHERE family_code = ? ORDER BY id ASC");
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $nodes */
        $nodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($nodes) <= 1) {
            return ['merged_count' => 0, 'removed_ids' => []];
        }

        // Group by normalized identity
        $groups = [];
        foreach ($nodes as $n) {
            $id = (int)$n['id'];
            $first = strtolower(trim((string)($n['first_name'] ?? '')));
            $last = strtolower(trim((string)($n['last_name'] ?? '')));
            $fullName = preg_replace('/\s+/', ' ', trim($first . ' ' . $last));
            
            // "Unknown Father/Mother" placeholders are distinct slots (one per half-family), never duplicates
            if (!empty($n['is_placeholder']) || $fullName === '') {
                continue;
            }

            // Same name in a different generation is a different person (grandfather / grandson),
            // so the generation level is part of the identity.
            $normalizedKey = (string)$fullName . '|' . (int)($n['generation_level'] ?? 0);

            $groups[$normalizedKey][] = $n;
        }

        $mergedCount = 0;
        $removedIds = [];

        foreach ($groups as $key => $group) {
            if (count($group) <= 1) {
                continue;
            }

            // Find best master node: prioritize one with user_id, then photo, then lowest ID
            usort($group, function ($a, $b) {
                $aHasUser = !empty($a['user_id']) ? 1 : 0;
                $bHasUser = !empty($b['user_id']) ? 1 : 0;
                if ($aHasUser !== $bHasUser) {
                    return $bHasUser <=> $aHasUser;
                }
                $aHasPhoto = (!empty($a['avatar_url']) && !str_contains((string)$a['avatar_url'], 'avatarM.png') && !str_contains((string)$a['avatar_url'], 'avatarF.png')) ? 1 : 0;
                $bHasPhoto = (!empty($b['avatar_url']) && !str_contains((string)$b['avatar_url'], 'avatarM.png') && !str_contains((string)$b['avatar_url'], 'avatarF.png')) ? 1 : 0;
                if ($aHasPhoto !== $bHasPhoto) {
                    return $bHasPhoto <=> $aHasPhoto;
                }
                return (int)$a['id'] <=> (int)$b['id'];
            });

            $master = $group[0];
            $masterId = (int)$master['id'];

            // Duplicate nodes to remove
            for ($i = 1; $i < count($group); $i++) {
                $dup = $group[$i];
                $dupId = (int)$dup['id'];

                // 1. Repoint unions partner_1_id
                $upd1 = $db->prepare("UPDATE family_unions SET partner_1_id = ? WHERE partner_1_id = ?");
                $upd1->execute([$masterId, $dupId]);

                // 2. Repoint unions partner_2_id
                $upd2 = $db->prepare("UPDATE family_unions SET partner_2_id = ? WHERE partner_2_id = ?");
                $upd2->execute([$masterId, $dupId]);

                // 3. Repoint children child_id
                $upd3 = $db->prepare("UPDATE family_node_children SET child_id = ? WHERE child_id = ?");
                $upd3->execute([$masterId, $dupId]);

                // 4. Delete the duplicate node
                $del = $db->prepare("DELETE FROM family_nodes WHERE id = ?");
                $del->execute([$dupId]);

                $removedIds[] = $dupId;
                $mergedCount++;
            }
        }

        // Clean up duplicate links in family_node_children if any resulted from merge
        $db->exec("
            DELETE c1 FROM family_node_children c1
            INNER JOIN family_node_children c2 
            WHERE c1.id > c2.id 
              AND c1.union_id = c2.union_id 
              AND c1.child_id = c2.child_id
        ");

        return [
            'merged_count' => $mergedCount,
            'removed_ids' => $removedIds
        ];
    }

    /**
     * Yoruba and Anglo name diminutive mapping dictionary
     * @var array<string, array<int, string>>
     */
    private const DIMINUTIVE_MAP = [
        'femi' => ['olufemi', 'femi'],
        'olufemi' => ['femi', 'olufemi'],
        'wale' => ['adewale', 'olawale', 'wale'],
        'adewale' => ['wale', 'adewale'],
        'olawale' => ['wale', 'olawale'],
        'segun' => ['olusegun', 'segun'],
        'olusegun' => ['segun', 'olusegun'],
        'kenny' => ['kehinde', 'kenny'],
        'kehinde' => ['kenny', 'kehinde'],
        'taiwo' => ['taiwo', 'teju'],
        'tobi' => ['olutobi', 'oluwatobi', 'tobi'],
        'olutobi' => ['tobi', 'olutobi'],
        'oluwatobi' => ['tobi', 'oluwatobi'],
        'kunle' => ['adekunle', 'kunle'],
        'adekunle' => ['kunle', 'adekunle'],
        'dayo' => ['oladayo', 'ekundayo', 'dayo'],
        'oladayo' => ['dayo', 'oladayo'],
        'bola' => ['bolanle', 'mobolaji', 'omobola', 'bola'],
        'bolanle' => ['bola', 'bolanle'],
        'mobolaji' => ['bola', 'mobolaji'],
        'bayo' => ['adebayo', 'bayo'],
        'adebayo' => ['bayo', 'adebayo'],
        'yomi' => ['oluyomi', 'abayomi', 'yomi'],
        'oluyomi' => ['yomi', 'oluyomi'],
        'abayomi' => ['yomi', 'abayomi'],
        'deji' => ['olamilekan', 'ayodeji', 'deji'],
        'ayodeji' => ['deji', 'ayodeji'],
        'mike' => ['michael', 'mike'],
        'michael' => ['mike', 'michael'],
        'dave' => ['david', 'dave'],
        'david' => ['dave', 'david'],
        'dan' => ['daniel', 'dan', 'danny'],
        'daniel' => ['dan', 'danny', 'daniel'],
        'chris' => ['christopher', 'chris'],
        'christopher' => ['chris', 'christopher'],
        'tony' => ['anthony', 'tony'],
        'anthony' => ['tony', 'anthony'],
        'alex' => ['alexander', 'alexandra', 'alex'],
        'sam' => ['samuel', 'samantha', 'sam'],
        'samuel' => ['sam', 'samuel'],
        'liz' => ['elizabeth', 'liz', 'lizzie'],
        'elizabeth' => ['liz', 'lizzie', 'elizabeth']
    ];

    /**
     * Find probable unclaimed node matches on the family tree for a given user.
     *
     * @param string $familyCode
     * @param string $userId
     * @param array<string, mixed> $memberData
     * @return array<int, array<string, mixed>>
     */
    public static function findProbableMatches(string $familyCode, string $userId, array $memberData): array
    {
        if (empty($familyCode) || empty($userId)) {
            return [];
        }

        $db = Db::connect2();

        // Check if user already has an established connected node in this family tree
        $checkStmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$familyCode, $userId]);
        $existingNodeId = $checkStmt->fetchColumn();

        if ($existingNodeId) {
            $existingIdInt = (int)$existingNodeId;
            // Check if this existing node is already connected in the family tree
            $chkParent = $db->prepare("SELECT COUNT(*) FROM family_node_children WHERE child_id = ?");
            $chkParent->execute([$existingIdInt]);
            $isChild = (int)$chkParent->fetchColumn() > 0;

            $chkUnion = $db->prepare("SELECT COUNT(*) FROM family_unions WHERE partner_1_id = ? OR partner_2_id = ?");
            $chkUnion->execute([$existingIdInt, $existingIdInt]);
            $hasUnion = (int)$chkUnion->fetchColumn() > 0;

            // If user's node is already a child of parents in the tree, they don't need matching
            if ($isChild) {
                return [];
            }
        }

        $userFirst = strtolower(trim((string)($memberData['firstName'] ?? '')));
        $userLast  = strtolower(trim((string)($memberData['lastName'] ?? '')));
        $userEmail = strtolower(trim((string)($memberData['email'] ?? '')));
        $userMobile = (string)preg_replace('/\D+/', '', (string)($memberData['mobile'] ?? ''));
        $userGender = (string)($memberData['gender'] ?? '');

        // Fetch user dismissals
        $dStmt = $db->prepare("SELECT node_id FROM kinship_match_dismissals WHERE user_id = ? AND family_code = ?");
        $dStmt->execute([$userId, $familyCode]);
        /** @var array<int, int> $dismissedIds */
        $dismissedIds = array_map('intval', $dStmt->fetchAll(\PDO::FETCH_COLUMN));

        // Fetch unclaimed candidate nodes
        $candStmt = $db->prepare("
            SELECT id, family_code, first_name, last_name, gender, bio, email, mobile, avatar_url, generation_level
            FROM family_nodes
            WHERE family_code = ? AND user_id IS NULL AND is_placeholder = 0
            ORDER BY id ASC
        ");
        $candStmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $candidates */
        $candidates = $candStmt->fetchAll(\PDO::FETCH_ASSOC);

        $matches = [];

        foreach ($candidates as $cand) {
            $candId = (int)$cand['id'];
            if (in_array($candId, $dismissedIds, true)) {
                continue;
            }
            if (!empty($_SESSION['dismissed_claim_node_' . $candId])) {
                continue;
            }

            $candFirst = strtolower(trim((string)($cand['first_name'] ?? '')));
            $candLast  = strtolower(trim((string)($cand['last_name'] ?? '')));
            $candEmail = strtolower(trim((string)($cand['email'] ?? '')));
            $candMobile = (string)preg_replace('/\D+/', '', (string)($cand['mobile'] ?? ''));
            $candGender = (string)($cand['gender'] ?? '');

            $score = 0.0;

            // 1. First name evaluation (max 0.50)
            if ($candFirst !== '' && $userFirst !== '') {
                if ($candFirst === $userFirst) {
                    $score += 0.50;
                } elseif (self::isDiminutiveMatch($candFirst, $userFirst)) {
                    $score += 0.45;
                } else {
                    $maxLen = max(strlen($candFirst), strlen($userFirst));
                    $lev = levenshtein($candFirst, $userFirst);
                    if (($lev / $maxLen) <= 0.25) {
                        $score += 0.35;
                    } elseif (soundex($candFirst) === soundex($userFirst)) {
                        $score += 0.30;
                    }
                }
            }

            // 2. Last name evaluation (max 0.30)
            if ($candLast !== '' && $userLast !== '') {
                if ($candLast === $userLast) {
                    $score += 0.30;
                } elseif (soundex($candLast) === soundex($userLast)) {
                    $score += 0.15;
                }
            }

            // 3. Email evaluation (max 0.35)
            if ($userEmail !== '' && $candEmail !== '' && $userEmail === $candEmail) {
                $score += 0.35;
            }

            // 4. Mobile evaluation (max 0.35)
            if ($userMobile !== '' && $candMobile !== '') {
                if ($userMobile === $candMobile) {
                    $score += 0.35;
                } elseif (strlen($userMobile) >= 9 && strlen($candMobile) >= 9) {
                    if (substr($userMobile, -9) === substr($candMobile, -9)) {
                        $score += 0.30;
                    }
                }
            }

            // 5. Gender consistency
            if ($userGender !== '' && $candGender !== '' && strcasecmp($userGender, $candGender) === 0) {
                $score += 0.05;
            }

            // Cap at 1.0
            $finalScore = min(1.0, round($score, 2));

            // Candidate threshold: >= 70%
            if ($finalScore >= 0.70) {
                $context = self::buildKinshipContext($db, $candId, $familyCode);
                $matches[] = [
                    'node_id' => $candId,
                    'first_name' => (string)($cand['first_name'] ?? ''),
                    'last_name' => (string)($cand['last_name'] ?? ''),
                    'full_name' => trim(((string)($cand['first_name'] ?? '')) . ' ' . ((string)($cand['last_name'] ?? ''))),
                    'gender' => $candGender,
                    'avatar_url' => (string)($cand['avatar_url'] ?? ''),
                    'generation_level' => (int)($cand['generation_level'] ?? 0),
                    'confidence_score' => $finalScore,
                    'confidence_percent' => (int)round($finalScore * 100),
                    'relationship_hint' => $context['relationship_hint'],
                    'parents_hint' => $context['parents_hint'],
                    'siblings_hint' => $context['siblings_hint']
                ];
            }
        }

        // Sort descending by confidence score
        usort($matches, static function (array $a, array $b): int {
            return ($b['confidence_score'] <=> $a['confidence_score']);
        });

        return $matches;
    }

    /**
     * Check if two names are recognized diminutives / nicknames of each other
     */
    private static function isDiminutiveMatch(string $nameA, string $nameB): bool
    {
        $a = strtolower($nameA);
        $b = strtolower($nameB);

        if (isset(self::DIMINUTIVE_MAP[$a]) && in_array($b, self::DIMINUTIVE_MAP[$a], true)) {
            return true;
        }
        if (isset(self::DIMINUTIVE_MAP[$b]) && in_array($a, self::DIMINUTIVE_MAP[$b], true)) {
            return true;
        }

        return false;
    }

    /**
     * Assemble rich human-readable kinship context for a candidate node
     *
     * @return array{relationship_hint: string, parents_hint: string, siblings_hint: string}
     */
    private static function buildKinshipContext(\PDO $db, int $nodeId, string $familyCode): array
    {
        $parentsHint = '';
        $siblingsHint = '';
        $relationshipParts = [];

        // 1. Find parents via union
        $pStmt = $db->prepare("
            SELECT u.id AS union_id, u.partner_1_id, u.partner_2_id,
                   p1.first_name AS p1_first, p1.last_name AS p1_last,
                   p2.first_name AS p2_first, p2.last_name AS p2_last
            FROM family_node_children c
            JOIN family_unions u ON c.union_id = u.id
            LEFT JOIN family_nodes p1 ON u.partner_1_id = p1.id
            LEFT JOIN family_nodes p2 ON u.partner_2_id = p2.id
            WHERE c.child_id = ? AND u.family_code = ?
            LIMIT 1
        ");
        $pStmt->execute([$nodeId, $familyCode]);
        $parentRow = $pStmt->fetch(\PDO::FETCH_ASSOC);

        if (is_array($parentRow) && !empty($parentRow['union_id'])) {
            $pNames = [];
            if (!empty($parentRow['p1_first'])) {
                $pNames[] = trim((string)$parentRow['p1_first'] . ' ' . (string)($parentRow['p1_last'] ?? ''));
            }
            if (!empty($parentRow['p2_first'])) {
                $pNames[] = trim((string)$parentRow['p2_first'] . ' ' . (string)($parentRow['p2_last'] ?? ''));
            }

            if (!empty($pNames)) {
                $parentsHint = implode(' & ', $pNames);
                $relationshipParts[] = 'Child of ' . $parentsHint;
            }

            // 2. Find siblings in the same union
            $sStmt = $db->prepare("
                SELECT n.first_name, n.last_name, n.user_id
                FROM family_node_children c
                JOIN family_nodes n ON c.child_id = n.id
                WHERE c.union_id = ? AND c.child_id != ?
                ORDER BY n.user_id DESC, n.id ASC
                LIMIT 3
            ");
            $sStmt->execute([(int)$parentRow['union_id'], $nodeId]);
            $siblings = $sStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($siblings)) {
                $sNames = [];
                foreach ($siblings as $sib) {
                    $sNames[] = trim(((string)($sib['first_name'] ?? '')) . ' ' . ((string)($sib['last_name'] ?? '')));
                }
                $siblingsHint = implode(', ', $sNames);
                $relationshipParts[] = 'Sibling of ' . $siblingsHint;
            }
        }

        if (empty($relationshipParts)) {
            $relationshipHint = 'Relative on the family tree';
        } else {
            $relationshipHint = implode(' • ', $relationshipParts);
        }

        return [
            'relationship_hint' => $relationshipHint,
            'parents_hint' => $parentsHint,
            'siblings_hint' => $siblingsHint
        ];
    }

    /**
     * Atomically claim an unclaimed node and graft any existing subtree.
     *
     * @param string $familyCode
     * @param string $userId
     * @param int $targetNodeId
     * @param array<string, mixed> $userData
     * @return array{success: bool, code: int, message: string, claimed_node_id?: int, grafted?: bool}
     */
    public static function claimAndMergeNode(
        string $familyCode,
        string $userId,
        int $targetNodeId,
        array $userData = []
    ): array {
        if (empty($familyCode) || empty($userId) || $targetNodeId <= 0) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Invalid parameters provided.'
            ];
        }

        $db = Db::connect2();
        $db->beginTransaction();

        try {
            // 1. Adversarial IDOR & Concurrency Gate: Pessimistic Row Lock
            $lockStmt = $db->prepare("
                SELECT id, family_code, user_id, first_name, last_name, avatar_url, email, mobile
                FROM family_nodes
                WHERE id = ?
                FOR UPDATE
            ");
            $lockStmt->execute([$targetNodeId]);
            $targetNode = $lockStmt->fetch(\PDO::FETCH_ASSOC);

            if (!$targetNode) {
                $db->rollBack();
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'The target family node does not exist.'
                ];
            }

            // Cross-family tenant isolation check
            if ((string)$targetNode['family_code'] !== $familyCode) {
                $db->rollBack();
                return [
                    'success' => false,
                    'code' => 403,
                    'message' => 'Cross-family node claiming is strictly forbidden.'
                ];
            }

            // Double-claim concurrency race condition check
            if (!empty($targetNode['user_id'])) {
                $db->rollBack();
                return [
                    'success' => false,
                    'code' => 409,
                    'message' => 'This family node has already been claimed by another user.'
                ];
            }

            // 2. Subtree Grafting: Check if user has an existing node in this family
            $checkExisting = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
            $checkExisting->execute([$familyCode, $userId]);
            $existingId = $checkExisting->fetchColumn();
            $grafted = false;

            if ($existingId && (int)$existingId !== $targetNodeId) {
                $existingNodeId = (int)$existingId;

                // Circular reference defense: verify target is not a descendant of existingNodeId
                if (self::isDescendant($db, $existingNodeId, $targetNodeId)) {
                    $db->rollBack();
                    return [
                        'success' => false,
                        'code' => 422,
                        'message' => 'Genealogical cycle detected: cannot claim a descendant of your current branch.'
                    ];
                }

                // A. Reparent unions where existing node was partner_1
                $updUnion1 = $db->prepare("UPDATE family_unions SET partner_1_id = ? WHERE partner_1_id = ?");
                $updUnion1->execute([$targetNodeId, $existingNodeId]);

                // B. Reparent unions where existing node was partner_2
                $updUnion2 = $db->prepare("UPDATE family_unions SET partner_2_id = ? WHERE partner_2_id = ?");
                $updUnion2->execute([$targetNodeId, $existingNodeId]);

                // C. Reparent children where existing node was child
                $updChild = $db->prepare("UPDATE family_node_children SET child_id = ? WHERE child_id = ?");
                $updChild->execute([$targetNodeId, $existingNodeId]);

                // D. Reparent children where existing node was parent_node_id
                $updParentLink = $db->prepare("UPDATE family_node_children SET parent_node_id = ? WHERE parent_node_id = ?");
                $updParentLink->execute([$targetNodeId, $existingNodeId]);

                // E. Safely delete the orphaned duplicate node
                $delDup = $db->prepare("DELETE FROM family_nodes WHERE id = ?");
                $delDup->execute([$existingNodeId]);

                $grafted = true;
            }

            // 3. Bind user_id and profile data to the target claimed node
            $cleanFirst = trim((string)($userData['firstName'] ?? ''));
            $cleanLast  = trim((string)($userData['lastName'] ?? ''));
            $cleanEmail = !empty($userData['email']) ? strtolower(trim((string)$userData['email'])) : null;
            $cleanMobile = !empty($userData['mobile']) ? trim((string)$userData['mobile']) : null;
            $avatarUrl = !empty($userData['img']) ? (string)$userData['img'] : null;

            $updTarget = $db->prepare("
                UPDATE family_nodes 
                SET user_id = ?,
                    first_name = COALESCE(NULLIF(?, ''), first_name),
                    last_name = COALESCE(NULLIF(?, ''), last_name),
                    avatar_url = COALESCE(NULLIF(?, ''), avatar_url),
                    email = COALESCE(NULLIF(?, ''), email),
                    mobile = COALESCE(NULLIF(?, ''), mobile),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updTarget->execute([
                $userId,
                $cleanFirst,
                $cleanLast,
                $avatarUrl,
                $cleanEmail,
                $cleanMobile,
                $targetNodeId
            ]);

            // 4. Deduplicate any potential duplicate children links resulting from the graft
            $db->exec("
                DELETE c1 FROM family_node_children c1
                INNER JOIN family_node_children c2 
                WHERE c1.id > c2.id 
                  AND c1.union_id = c2.union_id 
                  AND c1.child_id = c2.child_id
            ");

            // 5. Clean up any dismissals for this node
            $delDismiss = $db->prepare("DELETE FROM kinship_match_dismissals WHERE user_id = ? AND node_id = ?");
            $delDismiss->execute([$userId, $targetNodeId]);

            $db->commit();

            if (session_status() === PHP_SESSION_ACTIVE) {
                unset($_SESSION['dismissed_claim_node_' . $targetNodeId]);
            }

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Your family tree position has been successfully claimed and connected!',
                'claimed_node_id' => $targetNodeId,
                'grafted' => $grafted
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[KinshipEntityResolver::claimAndMergeNode] Error: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => 500,
                'message' => 'An internal database error occurred while claiming your position.'
            ];
        }
    }

    /**
     * Cycle detection helper: check if targetId is a descendant of ancestorId
     */
    private static function isDescendant(\PDO $db, int $ancestorId, int $targetId): bool
    {
        $visited = [];
        $queue = [$ancestorId];

        while (!empty($queue)) {
            $curr = array_shift($queue);
            if ($curr === $targetId) {
                return true;
            }
            if (isset($visited[$curr])) {
                continue;
            }
            $visited[$curr] = true;

            // Find all unions where curr is a partner
            $uStmt = $db->prepare("SELECT id FROM family_unions WHERE partner_1_id = ? OR partner_2_id = ?");
            $uStmt->execute([$curr, $curr]);
            $unionIds = $uStmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($unionIds)) {
                $inPlaceholders = implode(',', array_fill(0, count($unionIds), '?'));
                $cStmt = $db->prepare("SELECT child_id FROM family_node_children WHERE union_id IN ($inPlaceholders)");
                $cStmt->execute($unionIds);
                $children = $cStmt->fetchAll(\PDO::FETCH_COLUMN);
                foreach ($children as $cId) {
                    $cIdInt = (int)$cId;
                    if (!isset($visited[$cIdInt])) {
                        $queue[] = $cIdInt;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Persistently dismiss an auto-match recommendation for a user.
     *
     * @param string $familyCode
     * @param string $userId
     * @param int $nodeId
     * @return bool
     */
    public static function dismissMatch(string $familyCode, string $userId, int $nodeId): bool
    {
        if (empty($familyCode) || empty($userId) || $nodeId <= 0) {
            return false;
        }

        $db = Db::connect2();
        $stmt = $db->prepare("
            INSERT INTO kinship_match_dismissals (user_id, node_id, family_code)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $nodeId, $familyCode]);

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['dismissed_claim_node_' . $nodeId] = true;
        }

        return true;
    }
}


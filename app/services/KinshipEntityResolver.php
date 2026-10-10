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
            
            // Normalize "WALE OLAOGUN" vs "Wale" vs "Wale Olaogun"
            $normalizedKey = (string)$fullName;

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
}

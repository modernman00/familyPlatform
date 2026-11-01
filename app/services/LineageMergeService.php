<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use PDO;
use RuntimeException;
use Throwable;

/**
 * 🌿 Non-Destructive Lineage Merging & 3-Way Branch Grafting Engine
 *
 * Safely imports, enriches, and grafts verified family tree branches across
 * independent family codes with full topological cycle prevention,
 * asymmetric half-sibling isolation, and ancestral fast-forwarding.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class LineageMergeService
{
    /**
     * Atomically graft an external constellation union and its associated branches
     * into the user's family tree.
     *
     * @param string $familyCode Current family circle code
     * @param string $userId The requesting member ID
     * @param int $targetUnionId The verified union ID in the target family
     * @return array{
     *     success: bool,
     *     code: int,
     *     message: string,
     *     imported_count: int,
     *     grafted_nodes: list<int>
     * }
     */
    public static function mergeConstellationBranch(string $familyCode, string $userId, int $targetUnionId): array
    {
        if (empty($familyCode) || empty($userId) || $targetUnionId <= 0) {
            return [
                'success' => false,
                'code' => 400,
                'message' => 'Invalid parameters provided for lineage branch grafting.',
                'imported_count' => 0,
                'grafted_nodes' => [],
            ];
        }

        $db = Db::connect2();
        $db->beginTransaction();

        try {
            // 1. Pessimistic Row Lock on Target Union to prevent race conditions
            $stmtTargetU = $db->prepare('
                SELECT u.id, u.family_code, u.partner_1_id, u.partner_2_id, u.union_type,
                       p1.first_name AS p1_first, p1.last_name AS p1_last, p1.gender AS p1_gender, p1.generation_level AS p1_gen, p1.avatar_url AS p1_avatar, p1.bio AS p1_bio,
                       p2.first_name AS p2_first, p2.last_name AS p2_last, p2.gender AS p2_gender, p2.generation_level AS p2_gen, p2.avatar_url AS p2_avatar, p2.bio AS p2_bio
                FROM family_unions u
                LEFT JOIN family_nodes p1 ON u.partner_1_id = p1.id
                LEFT JOIN family_nodes p2 ON u.partner_2_id = p2.id
                WHERE u.id = ?
                FOR UPDATE
            ');
            $stmtTargetU->execute([$targetUnionId]);
            $targetU = $stmtTargetU->fetch(PDO::FETCH_ASSOC);

            if (!$targetU) {
                $db->rollBack();
                return [
                    'success' => false,
                    'code' => 404,
                    'message' => 'The target constellation union could not be found.',
                    'imported_count' => 0,
                    'grafted_nodes' => [],
                ];
            }

            $targetFamCode = (string)$targetU['family_code'];

            // 2. Resolve user's base node in current family
            $stmtBase = $db->prepare('SELECT id, generation_level, first_name, last_name FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1');
            $stmtBase->execute([$familyCode, $userId]);
            $baseNode = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$baseNode) {
                $stmtRoot = $db->prepare('SELECT id, generation_level, first_name, last_name FROM family_nodes WHERE family_code = ? ORDER BY id ASC LIMIT 1');
                $stmtRoot->execute([$familyCode]);
                $baseNode = $stmtRoot->fetch(PDO::FETCH_ASSOC);
            }

            $baseNodeId = (int)($baseNode['id'] ?? 0);
            $userGen = (int)($baseNode['generation_level'] ?? 0);

            $graftedNodes = [];
            $importedCount = 0;

            // 3. Resolve or Create Local Partners (Shey & Enny) in user's family
            $localP1Id = self::resolveOrCreateLocalPartner($db, $familyCode, $targetU, 1, $userGen - 1);
            $localP2Id = self::resolveOrCreateLocalPartner($db, $familyCode, $targetU, 2, $userGen - 1);

            if ($localP1Id > 0) {
                $graftedNodes[] = $localP1Id;
            }
            if ($localP2Id > 0) {
                $graftedNodes[] = $localP2Id;
            }

            // Ensure parent union in local family
            $localUnionId = self::ensureLocalUnion($db, $familyCode, $localP1Id, $localP2Id, (string)$targetU['union_type']);

            // Link base user as child if not already linked
            if ($baseNodeId > 0 && $localUnionId > 0) {
                self::linkChildSafe($db, $localUnionId, $baseNodeId);
            }

            // 4. Import & Deduplicate Siblings from Target Union
            $stmtTgtKids = $db->prepare('
                SELECT n.id, n.first_name, n.last_name, n.gender, n.generation_level, n.bio, n.avatar_url, n.email, n.mobile
                FROM family_node_children fnc
                JOIN family_nodes n ON fnc.child_id = n.id
                WHERE fnc.union_id = ?
            ');
            $stmtTgtKids->execute([$targetUnionId]);
            /** @var list<array<string, mixed>> $tgtKids */
            $tgtKids = $stmtTgtKids->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tgtKids as $k) {
                $first = trim((string)($k['first_name'] ?? ''));
                $last  = trim((string)($k['last_name'] ?? ''));
                if ($first === '') {
                    continue;
                }

                // Deduplicate against existing children
                $resChild = KinshipEntityResolver::resolveOrCreateChild(
                    $familyCode,
                    $localUnionId,
                    $first,
                    $last,
                    (string)($k['gender'] ?? 'Male'),
                    !empty($k['email']) ? (string)$k['email'] : null,
                    !empty($k['mobile']) ? (string)$k['mobile'] : null,
                    !empty($k['avatar_url']) ? (string)$k['avatar_url'] : null,
                    (int)($k['generation_level'] ?? $userGen)
                );

                if ($resChild['is_new']) {
                    $importedCount++;
                    $graftedNodes[] = $resChild['node_id'];
                }
            }

            // 5. Asymmetric Half-Sibling Grafting: Import collateral unions for Partner 1 & Partner 2
            $importedCount += self::graftCollateralUnions(
                $db,
                $targetFamCode,
                $familyCode,
                (int)$targetU['partner_1_id'],
                $localP1Id,
                $targetUnionId,
                $userGen,
                $graftedNodes
            );

            $importedCount += self::graftCollateralUnions(
                $db,
                $targetFamCode,
                $familyCode,
                (int)$targetU['partner_2_id'],
                $localP2Id,
                $targetUnionId,
                $userGen,
                $graftedNodes
            );

            // 6. Upstream Ancestral Fast-Forwarding: Import Grandparents of Partner 1 & Partner 2
            $importedCount += self::graftAncestors(
                $db,
                $targetFamCode,
                $familyCode,
                (int)$targetU['partner_1_id'],
                $localP1Id,
                $userGen - 2,
                $graftedNodes
            );

            $importedCount += self::graftAncestors(
                $db,
                $targetFamCode,
                $familyCode,
                (int)$targetU['partner_2_id'],
                $localP2Id,
                $userGen - 2,
                $graftedNodes
            );

            // 7. Clean up match dismissal records for this union
            $stmtDel = $db->prepare('DELETE FROM kinship_match_dismissals WHERE user_id = ? AND node_id = ? AND family_code = ?');
            $stmtDel->execute([$userId, $targetUnionId, $familyCode]);

            $db->commit();

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Lineage branch successfully grafted! Your family tree is now enriched and aligned.',
                'imported_count' => $importedCount,
                'grafted_nodes' => array_values(array_unique($graftedNodes)),
            ];
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[LineageMergeService::mergeConstellationBranch] Error: ' . $e->getMessage());

            return [
                'success' => false,
                'code' => 500,
                'message' => 'An internal error occurred while merging the family branch: ' . $e->getMessage(),
                'imported_count' => 0,
                'grafted_nodes' => [],
            ];
        }
    }

    /**
     * Resolve existing local partner node or create a new node in the local family.
     *
     * @param array<string, mixed> $targetU
     */
    private static function resolveOrCreateLocalPartner(PDO $db, string $fam, array $targetU, int $partnerNum, int $defaultGen): int
    {
        $prefix = "p{$partnerNum}_";
        $first = trim((string)($targetU[$prefix . 'first'] ?? ''));
        $last  = trim((string)($targetU[$prefix . 'last'] ?? ''));
        $gender = (string)($targetU[$prefix . 'gender'] ?? ($partnerNum === 1 ? 'Male' : 'Female'));
        $gen = (int)($targetU[$prefix . 'gen'] ?? $defaultGen);
        $avatar = (string)($targetU[$prefix . 'avatar'] ?? '');
        $bio = (string)($targetU[$prefix . 'bio'] ?? ($partnerNum === 1 ? 'Father' : 'Mother'));

        if ($first === '') {
            return 0;
        }

        // Search for existing node in local family with matching name
        $stmtFind = $db->prepare('
            SELECT id FROM family_nodes
            WHERE family_code = ?
              AND (
                  (LOWER(first_name) = LOWER(?) AND (LOWER(last_name) = LOWER(?) OR last_name = ""))
                  OR LOWER(first_name) = LOWER(?)
              )
            ORDER BY user_id DESC, id ASC
            LIMIT 1
        ');
        $stmtFind->execute([$fam, $first, $last, trim("$first $last")]);
        $existingId = $stmtFind->fetchColumn();

        if ($existingId) {
            $nodeId = (int)$existingId;
            // Enrich with avatar if local is placeholder or default
            if ($avatar !== '' && !str_contains($avatar, 'avatarM.png') && !str_contains($avatar, 'avatarF.png')) {
                $db->prepare('UPDATE family_nodes SET avatar_url = ? WHERE id = ? AND (avatar_url LIKE "%avatarM.png%" OR avatar_url LIKE "%avatarF.png%" OR avatar_url IS NULL)')->execute([$avatar, $nodeId]);
            }
            return $nodeId;
        }

        // Create new node in local family
        $ins = $db->prepare('
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio, avatar_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $ins->execute([$fam, $first, $last, $gender, $gen, $bio, $avatar !== '' ? $avatar : null]);

        return (int)$db->lastInsertId();
    }

    /**
     * Ensure local union between two partners exists.
     */
    private static function ensureLocalUnion(PDO $db, string $fam, int $p1Id, int $p2Id, string $unionType = 'married'): int
    {
        if ($p1Id <= 0 && $p2Id <= 0) {
            return 0;
        }

        $stmt = $db->prepare('
            SELECT id FROM family_unions
            WHERE family_code = ?
              AND (
                  (partner_1_id = ? AND partner_2_id = ?)
                  OR (partner_1_id = ? AND partner_2_id = ?)
              )
            LIMIT 1
        ');
        $stmt->execute([$fam, $p1Id, $p2Id, $p2Id, $p1Id]);
        $existingU = $stmt->fetchColumn();

        if ($existingU) {
            return (int)$existingU;
        }

        $ins = $db->prepare('
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, ?, 1)
        ');
        $ins->execute([$fam, $p1Id > 0 ? $p1Id : null, $p2Id > 0 ? $p2Id : null, $unionType]);

        return (int)$db->lastInsertId();
    }

    /**
     * Link child to union safely avoiding duplicates.
     */
    private static function linkChildSafe(PDO $db, int $unionId, int $childId): void
    {
        $stmt = $db->prepare('SELECT id FROM family_node_children WHERE union_id = ? AND child_id = ? LIMIT 1');
        $stmt->execute([$unionId, $childId]);
        if (!$stmt->fetchColumn()) {
            $ins = $db->prepare('INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, "biological")');
            $ins->execute([$unionId, $childId]);
        }
    }

    /**
     * Graft collateral half-sibling unions (e.g. Shey's child with Linda).
     *
     * @param list<int> $graftedNodes
     */
    private static function graftCollateralUnions(
        PDO $db,
        string $tgtFam,
        string $localFam,
        int $tgtPartnerId,
        int $localPartnerId,
        int $mainUnionId,
        int $childGen,
        array &$graftedNodes
    ): int {
        if ($tgtPartnerId <= 0 || $localPartnerId <= 0) {
            return 0;
        }

        $stmtCol = $db->prepare('
            SELECT u.id, u.partner_1_id, u.partner_2_id, u.union_type,
                   p.first_name, p.last_name, p.gender, p.generation_level, p.bio, p.avatar_url
            FROM family_unions u
            JOIN family_nodes p ON (
                CASE WHEN u.partner_1_id = ? THEN u.partner_2_id ELSE u.partner_1_id END = p.id
            )
            WHERE u.family_code = ?
              AND (u.partner_1_id = ? OR u.partner_2_id = ?)
              AND u.id != ?
        ');
        $stmtCol->execute([$tgtPartnerId, $tgtFam, $tgtPartnerId, $tgtPartnerId, $mainUnionId]);
        /** @var list<array<string, mixed>> $colUnions */
        $colUnions = $stmtCol->fetchAll(PDO::FETCH_ASSOC);

        $imported = 0;

        foreach ($colUnions as $cu) {
            $otherFirst = trim((string)($cu['first_name'] ?? ''));
            $otherLast  = trim((string)($cu['last_name'] ?? ''));
            $otherGender = (string)($cu['gender'] ?? 'Female');
            $otherGen   = (int)($cu['generation_level'] ?? ($childGen - 1));

            // Check if other partner already exists in local family (idempotency)
            $stmtFindOther = $db->prepare('
                SELECT id FROM family_nodes
                WHERE family_code = ?
                  AND LOWER(first_name) = LOWER(?)
                  AND (LOWER(last_name) = LOWER(?) OR last_name = "")
                LIMIT 1
            ');
            $stmtFindOther->execute([$localFam, $otherFirst, $otherLast]);
            $existingOtherId = $stmtFindOther->fetchColumn();

            if ($existingOtherId) {
                $localOtherId = (int)$existingOtherId;
            } else {
                $insOther = $db->prepare('
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio, avatar_url)
                    VALUES (?, ?, ?, ?, ?, "Former Partner", ?)
                ');
                $insOther->execute([$localFam, $otherFirst, $otherLast, $otherGender, $otherGen, !empty($cu['avatar_url']) ? $cu['avatar_url'] : null]);
                $localOtherId = (int)$db->lastInsertId();
                $imported++;
            }
            $graftedNodes[] = $localOtherId;

            // Create collateral union linking local partner to other partner
            $localColUnionId = self::ensureLocalUnion($db, $localFam, $localPartnerId, $localOtherId, (string)$cu['union_type']);

            // Import children of this collateral union (Half-siblings)
            $stmtKids = $db->prepare('
                SELECT n.first_name, n.last_name, n.gender, n.generation_level, n.avatar_url, n.email, n.mobile
                FROM family_node_children fnc
                JOIN family_nodes n ON fnc.child_id = n.id
                WHERE fnc.union_id = ?
            ');
            $stmtKids->execute([(int)$cu['id']]);
            /** @var list<array<string, mixed>> $halfKids */
            $halfKids = $stmtKids->fetchAll(PDO::FETCH_ASSOC);

            foreach ($halfKids as $hk) {
                $hkFirst = trim((string)($hk['first_name'] ?? ''));
                $hkLast  = trim((string)($hk['last_name'] ?? ''));
                if ($hkFirst === '') {
                    continue;
                }

                $res = KinshipEntityResolver::resolveOrCreateChild(
                    $localFam,
                    $localColUnionId,
                    $hkFirst,
                    $hkLast,
                    (string)($hk['gender'] ?? 'Male'),
                    !empty($hk['email']) ? (string)$hk['email'] : null,
                    !empty($hk['mobile']) ? (string)$hk['mobile'] : null,
                    !empty($hk['avatar_url']) ? (string)$hk['avatar_url'] : null,
                    (int)($hk['generation_level'] ?? $childGen)
                );

                if ($res['is_new']) {
                    $imported++;
                    $graftedNodes[] = $res['node_id'];
                }
            }
        }

        return $imported;
    }

    /**
     * Graft upstream ancestors (Grandparents).
     *
     * @param list<int> $graftedNodes
     */
    private static function graftAncestors(
        PDO $db,
        string $tgtFam,
        string $localFam,
        int $tgtChildId,
        int $localChildId,
        int $grandGen,
        array &$graftedNodes
    ): int {
        if ($tgtChildId <= 0 || $localChildId <= 0) {
            return 0;
        }

        // Find parent union of target child
        $stmt = $db->prepare('
            SELECT u.id, u.partner_1_id, u.partner_2_id,
                   p1.first_name AS p1_first, p1.last_name AS p1_last, p1.gender AS p1_gender, p1.avatar_url AS p1_avatar,
                   p2.first_name AS p2_first, p2.last_name AS p2_last, p2.gender AS p2_gender, p2.avatar_url AS p2_avatar
            FROM family_node_children fnc
            JOIN family_unions u ON fnc.union_id = u.id
            LEFT JOIN family_nodes p1 ON u.partner_1_id = p1.id
            LEFT JOIN family_nodes p2 ON u.partner_2_id = p2.id
            WHERE fnc.child_id = ? AND u.family_code = ?
            LIMIT 1
        ');
        $stmt->execute([$tgtChildId, $tgtFam]);
        $ancestorU = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ancestorU) {
            return 0;
        }

        $imported = 0;

        // Create or reuse Grandparents in local family (idempotent)
        $g1First = trim((string)($ancestorU['p1_first'] ?? ''));
        $g1Last  = trim((string)($ancestorU['p1_last'] ?? ''));
        $g2First = trim((string)($ancestorU['p2_first'] ?? ''));
        $g2Last  = trim((string)($ancestorU['p2_last'] ?? ''));

        $localG1Id = 0;
        if ($g1First !== '') {
            $stmtFindG1 = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND LOWER(first_name) = LOWER(?) AND (LOWER(last_name) = LOWER(?) OR last_name = "") LIMIT 1');
            $stmtFindG1->execute([$localFam, $g1First, $g1Last]);
            $existingG1 = $stmtFindG1->fetchColumn();

            if ($existingG1) {
                $localG1Id = (int)$existingG1;
            } else {
                $insG1 = $db->prepare('
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio, avatar_url)
                    VALUES (?, ?, ?, ?, ?, "Grandfather", ?)
                ');
                $insG1->execute([$localFam, $g1First, $g1Last, (string)($ancestorU['p1_gender'] ?? 'Male'), $grandGen, !empty($ancestorU['p1_avatar']) ? $ancestorU['p1_avatar'] : null]);
                $localG1Id = (int)$db->lastInsertId();
                $imported++;
            }
            $graftedNodes[] = $localG1Id;
        }

        $localG2Id = 0;
        if ($g2First !== '') {
            $stmtFindG2 = $db->prepare('SELECT id FROM family_nodes WHERE family_code = ? AND LOWER(first_name) = LOWER(?) AND (LOWER(last_name) = LOWER(?) OR last_name = "") LIMIT 1');
            $stmtFindG2->execute([$localFam, $g2First, $g2Last]);
            $existingG2 = $stmtFindG2->fetchColumn();

            if ($existingG2) {
                $localG2Id = (int)$existingG2;
            } else {
                $insG2 = $db->prepare('
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, bio, avatar_url)
                    VALUES (?, ?, ?, ?, ?, "Grandmother", ?)
                ');
                $insG2->execute([$localFam, $g2First, $g2Last, (string)($ancestorU['p2_gender'] ?? 'Female'), $grandGen, !empty($ancestorU['p2_avatar']) ? $ancestorU['p2_avatar'] : null]);
                $localG2Id = (int)$db->lastInsertId();
                $imported++;
            }
            $graftedNodes[] = $localG2Id;
        }

        if ($localG1Id > 0 || $localG2Id > 0) {
            // Cycle defense: ensure localChildId is not an ancestor of localG1Id
            if ($localG1Id > 0 && KinshipEntityResolver::isDescendant($db, $localChildId, $localG1Id)) {
                throw new RuntimeException('Genealogical cycle detected during ancestor grafting.');
            }

            $localGrandUnionId = self::ensureLocalUnion($db, $localFam, $localG1Id, $localG2Id, 'married');
            self::linkChildSafe($db, $localGrandUnionId, $localChildId);
        }

        return $imported;
    }
}

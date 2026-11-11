<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;

/**
 * ImmediateFamilyService
 *
 * Computes a member's authoritative immediate family circle (Parents, Spouses,
 * Children, and Siblings) by combining:
 * 1. The primary Family Tree graph (family_nodes, family_unions, family_node_children)
 * 2. Reciprocal / bidirectional resolution across legacy relationship tables (otherFamily, children, sibling)
 * 3. Semantic gender validation (e.g. Male parent -> Father, Female parent -> Mother, Male sibling -> Brother)
 * 4. Account linking and route protection (/allMembers/seeProfile/{id} for members with accounts, graceful card for unlinked)
 * 5. Multi-key deduplication (by user ID, node ID, and normalized full name)
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class ImmediateFamilyService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * Get all immediate family members for a given member ID.
     *
     * @param string $memberId The member's ID (e.g. '117540OLAWALE')
     * @return array<int, array{
     *     id: string,
     *     fullName: string,
     *     relationship: string,
     *     gender: string,
     *     img: string,
     *     isLinked: bool,
     *     profileUrl: string,
     *     orderWeight: int
     * }>
     */
    public function getImmediateFamily(string $memberId): array
    {
        $memberId = trim($memberId);
        if ($memberId === '') {
            return [];
        }

        $records = [];

        // 1. Ingest from Family Tree Graph (DAG 1-Degree Traversal)
        $this->ingestFromFamilyTree($memberId, $records);

        // 2. Ingest Direct Legacy Records (otherFamily, sibling, children)
        $this->ingestFromDirectLegacy($memberId, $records);

        // 3. Ingest Reciprocal Legacy Records (other relatives who linked this user)
        $this->ingestFromReciprocalLegacy($memberId, $records);

        // 4. Enrich, Deduplicate and Sort
        return $this->finalizeRecords($records, $memberId);
    }

    /**
     * Traverse 1-degree connections on family_nodes and family_unions.
     *
     * @param array<string, array<string, mixed>> $records
     */
    private function ingestFromFamilyTree(string $memberId, array &$records): void
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, family_code FROM family_nodes WHERE user_id = ? AND is_erased = 0"
            );
            $stmt->execute([$memberId]);
            $userNodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($userNodes)) {
                return;
            }

            foreach ($userNodes as $uNode) {
                $nodeId = (int) $uNode['id'];

                // ── Parents (unions where this node is registered as a child) ──
                $pStmt = $this->pdo->prepare(
                    "SELECT c.union_id, u.partner_1_id, u.partner_2_id, c.parent_node_id, c.relationship_type
                     FROM family_node_children c
                     LEFT JOIN family_unions u ON c.union_id = u.id
                     WHERE c.child_id = ?"
                );
                $pStmt->execute([$nodeId]);
                while ($pRow = $pStmt->fetch(PDO::FETCH_ASSOC)) {
                    $relType = (string)($pRow['relationship_type'] ?? 'biological');
                    foreach (['partner_1_id', 'partner_2_id', 'parent_node_id'] as $col) {
                        $pId = (int)($pRow[$col] ?? 0);
                        if ($pId > 0 && $pId !== $nodeId) {
                            $this->addNodeCandidate($pId, 'parent', $relType, $records);
                        }
                    }

                    // ── Siblings (other children of the same union) ──
                    $unionId = (int)($pRow['union_id'] ?? 0);
                    if ($unionId > 0) {
                        $sibStmt = $this->pdo->prepare(
                            "SELECT child_id, relationship_type FROM family_node_children
                             WHERE union_id = ? AND child_id != ?"
                        );
                        $sibStmt->execute([$unionId, $nodeId]);
                        while ($sibRow = $sibStmt->fetch(PDO::FETCH_ASSOC)) {
                            $sId = (int)($sibRow['child_id'] ?? 0);
                            if ($sId > 0 && $sId !== $nodeId) {
                                $this->addNodeCandidate($sId, 'sibling', (string)($sibRow['relationship_type'] ?? 'biological'), $records);
                            }
                        }
                    }
                }

                // ── Spouses / Partners (unions where this node is a partner) ──
                $spStmt = $this->pdo->prepare(
                    "SELECT partner_1_id, partner_2_id, union_type FROM family_unions
                     WHERE (partner_1_id = ? OR partner_2_id = ?) AND is_current = 1"
                );
                $spStmt->execute([$nodeId, $nodeId]);
                while ($spRow = $spStmt->fetch(PDO::FETCH_ASSOC)) {
                    $partnerId = ((int)$spRow['partner_1_id'] === $nodeId) ? (int)$spRow['partner_2_id'] : (int)$spRow['partner_1_id'];
                    if ($partnerId > 0 && $partnerId !== $nodeId) {
                        $this->addNodeCandidate($partnerId, 'spouse', (string)($spRow['union_type'] ?? 'married'), $records);
                    }
                }

                // ── Children (children in unions where this node is a partner, or direct parent_node_id) ──
                $chStmt = $this->pdo->prepare(
                    "SELECT c.child_id, c.relationship_type
                     FROM family_node_children c
                     JOIN family_unions u ON c.union_id = u.id
                     WHERE (u.partner_1_id = ? OR u.partner_2_id = ?) AND c.child_id != ?"
                );
                $chStmt->execute([$nodeId, $nodeId, $nodeId]);
                while ($chRow = $chStmt->fetch(PDO::FETCH_ASSOC)) {
                    $chId = (int)($chRow['child_id'] ?? 0);
                    if ($chId > 0 && $chId !== $nodeId) {
                        $this->addNodeCandidate($chId, 'child', (string)($chRow['relationship_type'] ?? 'biological'), $records);
                    }
                }

                $chDirectStmt = $this->pdo->prepare(
                    "SELECT child_id, relationship_type FROM family_node_children
                     WHERE parent_node_id = ? AND child_id != ?"
                );
                $chDirectStmt->execute([$nodeId, $nodeId]);
                while ($chDirectRow = $chDirectStmt->fetch(PDO::FETCH_ASSOC)) {
                    $chId = (int)($chDirectRow['child_id'] ?? 0);
                    if ($chId > 0 && $chId !== $nodeId) {
                        $this->addNodeCandidate($chId, 'child', (string)($chDirectRow['relationship_type'] ?? 'biological'), $records);
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::ingestFromFamilyTree] ' . $e->getMessage());
        }
    }

    /**
     * Resolve node record details and register candidate.
     *
     * @param array<string, array<string, mixed>> $records
     */
    private function addNodeCandidate(int $nodeId, string $role, string $relType, array &$records): void
    {
        $key = 'node_' . $nodeId;
        if (isset($records[$key])) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT id, user_id, first_name, last_name, maiden_name, gender, avatar_url, email, mobile
                 FROM family_nodes WHERE id = ? AND is_erased = 0 LIMIT 1"
            );
            $stmt->execute([$nodeId]);
            $node = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($node)) {
                return;
            }

            $first = trim((string)($node['first_name'] ?? ''));
            $last = trim((string)($node['last_name'] ?? ''));
            $fullName = trim($first . ' ' . $last);

            if ($fullName === '') {
                return;
            }

            $gender = (string)($node['gender'] ?? '');
            $userId = trim((string)($node['user_id'] ?? ''));

            // Check if unlinked node matches a registered user in contact
            if ($userId === '') {
                $email = trim((string)($node['email'] ?? ''));
                $mobile = trim((string)($node['mobile'] ?? ''));
                $matchedUser = $this->lookupUserByContact($email, $mobile);
                if ($matchedUser !== null) {
                    $userId = $matchedUser;
                }
            }

            $records[$key] = [
                'source' => 'tree',
                'node_id' => $nodeId,
                'user_id' => $userId,
                'fullName' => $fullName,
                'gender' => $gender,
                'role' => $role,
                'relType' => $relType,
                'avatar_url' => (string)($node['avatar_url'] ?? '')
            ];
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::addNodeCandidate] ' . $e->getMessage());
        }
    }

    /**
     * Ingest direct records entered by this member in legacy tables.
     *
     * @param array<string, array<string, mixed>> $records
     */
    private function ingestFromDirectLegacy(string $memberId, array &$records): void
    {
        try {
            // otherFamily: father, mother, spouse
            $ofStmt = $this->pdo->prepare("SELECT * FROM otherFamily WHERE id = ? AND deleted_at IS NULL");
            $ofStmt->execute([$memberId]);
            while ($row = $ofStmt->fetch(PDO::FETCH_ASSOC)) {
                // Father
                $fName = trim((string)($row['father_name'] ?? ''));
                if ($fName !== '') {
                    $fUser = $this->lookupUserByContact((string)($row['father_email'] ?? ''), (string)($row['father_mobile'] ?? ''));
                    $records['legacy_dir_father_' . ($fUser ?: $fName)] = [
                        'source' => 'legacy_direct',
                        'node_id' => null,
                        'user_id' => $fUser ?? '',
                        'fullName' => $fName,
                        'gender' => 'Male',
                        'role' => 'parent',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
                // Mother
                $mName = trim((string)($row['mother_name'] ?? ''));
                if ($mName !== '') {
                    $mUser = $this->lookupUserByContact((string)($row['mother_email'] ?? ''), (string)($row['mother_mobile'] ?? ''));
                    $records['legacy_dir_mother_' . ($mUser ?: $mName)] = [
                        'source' => 'legacy_direct',
                        'node_id' => null,
                        'user_id' => $mUser ?? '',
                        'fullName' => $mName,
                        'gender' => 'Female',
                        'role' => 'parent',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
                // Spouse
                $sName = trim((string)($row['spouse_name'] ?? ''));
                if ($sName !== '') {
                    $sUser = $this->lookupUserByContact((string)($row['spouse_email'] ?? ''), (string)($row['spouse_mobile'] ?? ''));
                    $records['legacy_dir_spouse_' . ($sUser ?: $sName)] = [
                        'source' => 'legacy_direct',
                        'node_id' => null,
                        'user_id' => $sUser ?? '',
                        'fullName' => $sName,
                        'gender' => '',
                        'role' => 'spouse',
                        'relType' => 'married',
                        'avatar_url' => ''
                    ];
                }
            }

            // siblings
            $sibStmt = $this->pdo->prepare("SELECT * FROM sibling WHERE id = ? AND deleted_at IS NULL");
            $sibStmt->execute([$memberId]);
            while ($row = $sibStmt->fetch(PDO::FETCH_ASSOC)) {
                $sibName = trim((string)($row['sibling_name'] ?? ''));
                if ($sibName !== '') {
                    $sibUser = $this->lookupUserByContact((string)($row['sibling_email'] ?? ''), (string)($row['sibling_mobile'] ?? ''));
                    $records['legacy_dir_sibling_' . ($sibUser ?: $sibName)] = [
                        'source' => 'legacy_direct',
                        'node_id' => null,
                        'user_id' => $sibUser ?? '',
                        'fullName' => $sibName,
                        'gender' => (string)($row['sibling_gender'] ?? ''),
                        'role' => 'sibling',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
            }

            // children
            $chStmt = $this->pdo->prepare("SELECT * FROM children WHERE id = ? AND deleted_at IS NULL");
            $chStmt->execute([$memberId]);
            while ($row = $chStmt->fetch(PDO::FETCH_ASSOC)) {
                $chName = trim((string)($row['children_name'] ?? ''));
                if ($chName !== '') {
                    $chUser = $this->lookupUserByContact((string)($row['children_email'] ?? ''), (string)($row['children_mobile'] ?? ''));
                    $records['legacy_dir_child_' . ($chUser ?: $chName)] = [
                        'source' => 'legacy_direct',
                        'node_id' => null,
                        'user_id' => $chUser ?? '',
                        'fullName' => $chName,
                        'gender' => (string)($row['children_gender'] ?? ''),
                        'role' => 'child',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::ingestFromDirectLegacy] ' . $e->getMessage());
        }
    }

    /**
     * Ingest reciprocal relationships: other registered users who listed this member.
     *
     * @param array<string, array<string, mixed>> $records
     */
    private function ingestFromReciprocalLegacy(string $memberId, array &$records): void
    {
        try {
            // Find this user's email and mobile to match against foreign entries
            $cStmt = $this->pdo->prepare("SELECT email, mobile FROM contact WHERE id = ? LIMIT 1");
            $cStmt->execute([$memberId]);
            $contact = $cStmt->fetch(PDO::FETCH_ASSOC);

            $email = trim((string)($contact['email'] ?? ''));
            $mobile = trim((string)($contact['mobile'] ?? ''));

            if ($email === '' && $mobile === '') {
                return;
            }

            // 1. Who listed this user as their child? -> They are this user's parent!
            $pQuery = $this->pdo->prepare(
                "SELECT id FROM children
                 WHERE (children_email = ? AND ? != '')
                    OR (children_mobile = ? AND ? != '')
                    OR children_linked = ?
                 GROUP BY id"
            );
            $pQuery->execute([$email, $email, $mobile, $mobile, $memberId]);
            while ($row = $pQuery->fetch(PDO::FETCH_ASSOC)) {
                $parentId = (string)$row['id'];
                if ($parentId !== '' && $parentId !== $memberId) {
                    $records['legacy_recip_parent_' . $parentId] = [
                        'source' => 'legacy_reciprocal',
                        'node_id' => null,
                        'user_id' => $parentId,
                        'fullName' => '',
                        'gender' => '',
                        'role' => 'parent',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
            }

            // 2. Who listed this user as their parent? -> They are this user's child!
            $chQuery = $this->pdo->prepare(
                "SELECT id FROM otherFamily
                 WHERE ((father_email = ? OR mother_email = ?) AND ? != '')
                    OR ((father_mobile = ? OR mother_mobile = ?) AND ? != '')
                 GROUP BY id"
            );
            $chQuery->execute([$email, $email, $email, $mobile, $mobile, $mobile]);
            while ($row = $chQuery->fetch(PDO::FETCH_ASSOC)) {
                $childId = (string)$row['id'];
                if ($childId !== '' && $childId !== $memberId) {
                    $records['legacy_recip_child_' . $childId] = [
                        'source' => 'legacy_reciprocal',
                        'node_id' => null,
                        'user_id' => $childId,
                        'fullName' => '',
                        'gender' => '',
                        'role' => 'child',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
            }

            // 3. Who listed this user as their spouse? -> They are this user's spouse!
            $spQuery = $this->pdo->prepare(
                "SELECT id FROM otherFamily
                 WHERE (spouse_email = ? AND ? != '')
                    OR (spouse_mobile = ? AND ? != '')
                 GROUP BY id"
            );
            $spQuery->execute([$email, $email, $mobile, $mobile]);
            while ($row = $spQuery->fetch(PDO::FETCH_ASSOC)) {
                $spouseId = (string)$row['id'];
                if ($spouseId !== '' && $spouseId !== $memberId) {
                    $records['legacy_recip_spouse_' . $spouseId] = [
                        'source' => 'legacy_reciprocal',
                        'node_id' => null,
                        'user_id' => $spouseId,
                        'fullName' => '',
                        'gender' => '',
                        'role' => 'spouse',
                        'relType' => 'married',
                        'avatar_url' => ''
                    ];
                }
            }

            // 4. Who listed this user as their sibling? -> They are this user's sibling!
            $sibQuery = $this->pdo->prepare(
                "SELECT id FROM sibling
                 WHERE (sibling_email = ? AND ? != '')
                    OR (sibling_mobile = ? AND ? != '')
                    OR sibling_linked = ?
                 GROUP BY id"
            );
            $sibQuery->execute([$email, $email, $mobile, $mobile, $memberId]);
            while ($row = $sibQuery->fetch(PDO::FETCH_ASSOC)) {
                $siblingId = (string)$row['id'];
                if ($siblingId !== '' && $siblingId !== $memberId) {
                    $records['legacy_recip_sibling_' . $siblingId] = [
                        'source' => 'legacy_reciprocal',
                        'node_id' => null,
                        'user_id' => $siblingId,
                        'fullName' => '',
                        'gender' => '',
                        'role' => 'sibling',
                        'relType' => 'biological',
                        'avatar_url' => ''
                    ];
                }
            }
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::ingestFromReciprocalLegacy] ' . $e->getMessage());
        }
    }

    /**
     * Merge, enrich with registered account details, deduplicate, and sort.
     *
     * @param array<string, array<string, mixed>> $rawRecords
     * @return array<int, array{
     *     id: string,
     *     fullName: string,
     *     relationship: string,
     *     gender: string,
     *     img: string,
     *     isLinked: bool,
     *     profileUrl: string,
     *     orderWeight: int
     * }>
     */
    private function finalizeRecords(array $rawRecords, string $egoId): array
    {
        $deduped = [];

        foreach ($rawRecords as $item) {
            $userId = trim((string)($item['user_id'] ?? ''));
            $fullName = trim((string)($item['fullName'] ?? ''));
            $gender = (string)($item['gender'] ?? '');
            $role = (string)($item['role'] ?? 'relative');
            $relType = (string)($item['relType'] ?? 'biological');
            $avatarUrl = (string)($item['avatar_url'] ?? '');

            // Do not include the ego user as their own relative
            if ($userId !== '' && $userId === $egoId) {
                continue;
            }

            // If userId is present, fetch authoritative personal and profile image details
            if ($userId !== '') {
                $userProfile = $this->lookupMemberProfile($userId);
                if ($userProfile !== null) {
                    $fullName = $userProfile['fullName'];
                    if (!empty($userProfile['gender'])) {
                        $gender = $userProfile['gender'];
                    }
                    if (!empty($userProfile['img'])) {
                        $avatarUrl = $userProfile['img'];
                    }
                }
            }

            if ($fullName === '' && $userId === '') {
                continue;
            }

            // Semantic relationship title with gender consistency
            $relationship = $this->resolveSemanticTitle($role, $gender, $relType);

            // Default Avatar fallback if empty
            if ($avatarUrl === '') {
                $isFemale = (strcasecmp($gender, 'Female') === 0 || in_array($relationship, ['Mother', 'Sister', 'Daughter', 'Wife'], true));
                $avatarUrl = $isFemale ? '/resources/images/profile/avatarF.png' : '/resources/images/profile/avatarM.png';
            } elseif (!str_starts_with($avatarUrl, 'http://') && !str_starts_with($avatarUrl, 'https://') && !str_starts_with($avatarUrl, '/')) {
                $avatarUrl = '/resources/images/profile/' . rawurlencode(basename($avatarUrl));
            }

            $isLinked = ($userId !== '');
            $profileUrl = $isLinked ? "/allMembers/seeProfile/{$userId}" : '';
            $orderWeight = $this->getRoleOrderWeight($role);

            // Deduplication Key:
            // 1. By userId if linked
            // 2. Or by normalized name if unlinked
            $cleanName = strtolower((string)preg_replace('/\s+/', ' ', trim($fullName)));
            $dedupeKey = $userId !== '' ? 'user_' . $userId : 'name_' . $cleanName;

            // If already present, tree source takes priority over legacy
            if (isset($deduped[$dedupeKey])) {
                $existing = $deduped[$dedupeKey];
                if (!$existing['isLinked'] && $isLinked) {
                    $deduped[$dedupeKey] = [
                        'id' => $userId,
                        'fullName' => $fullName,
                        'relationship' => $relationship,
                        'gender' => $gender,
                        'img' => $avatarUrl,
                        'isLinked' => $isLinked,
                        'profileUrl' => $profileUrl,
                        'orderWeight' => $orderWeight
                    ];
                }
                continue;
            }

            $deduped[$dedupeKey] = [
                'id' => $userId,
                'fullName' => $fullName,
                'relationship' => $relationship,
                'gender' => $gender,
                'img' => $avatarUrl,
                'isLinked' => $isLinked,
                'profileUrl' => $profileUrl,
                'orderWeight' => $orderWeight
            ];
        }

        // Sort by hierarchy: Spouses (1), Parents (2), Children (3), Siblings (4)
        usort($deduped, static function (array $a, array $b): int {
            if ($a['orderWeight'] !== $b['orderWeight']) {
                return $a['orderWeight'] <=> $b['orderWeight'];
            }
            return strcasecmp($a['fullName'], $b['fullName']);
        });

        return $deduped;
    }

    /**
     * Resolve semantic relationship title using role, gender, and biological/marital modifier.
     */
    private function resolveSemanticTitle(string $role, string $gender, string $relType): string
    {
        $isFemale = (strcasecmp($gender, 'Female') === 0);
        $isMale = (strcasecmp($gender, 'Male') === 0);

        return match ($role) {
            'parent' => match (true) {
                $isFemale => ($relType === 'step') ? 'Step-Mother' : 'Mother',
                $isMale => ($relType === 'step') ? 'Step-Father' : 'Father',
                default => 'Parent'
            },
            'spouse' => match (true) {
                $isFemale => 'Wife',
                $isMale => 'Husband',
                default => 'Spouse'
            },
            'child' => match (true) {
                $isFemale => ($relType === 'step') ? 'Step-Daughter' : 'Daughter',
                $isMale => ($relType === 'step') ? 'Step-Son' : 'Son',
                default => 'Child'
            },
            'sibling' => match (true) {
                $isFemale => ($relType === 'half') ? 'Half-Sister' : 'Sister',
                $isMale => ($relType === 'half') ? 'Half-Brother' : 'Brother',
                default => 'Sibling'
            },
            default => 'Relative'
        };
    }

    private function getRoleOrderWeight(string $role): int
    {
        return match ($role) {
            'spouse' => 1,
            'parent' => 2,
            'child' => 3,
            'sibling' => 4,
            default => 5
        };
    }

    /**
     * Lookup registered member ID by email or mobile.
     */
    private function lookupUserByContact(string $email, string $mobile): ?string
    {
        $email = trim($email);
        $mobile = trim($mobile);

        if ($email === '' && $mobile === '') {
            return null;
        }

        try {
            if ($email !== '') {
                $stmt = $this->pdo->prepare("SELECT id FROM contact WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $id = $stmt->fetchColumn();
                if (is_string($id) && $id !== '') {
                    return $id;
                }
            }

            if ($mobile !== '') {
                $stmt = $this->pdo->prepare("SELECT id FROM contact WHERE mobile = ? LIMIT 1");
                $stmt->execute([$mobile]);
                $id = $stmt->fetchColumn();
                if (is_string($id) && $id !== '') {
                    return $id;
                }
            }
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::lookupUserByContact] ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieve member profile details from personal and profilePics.
     *
     * @return array{fullName: string, gender: string, img: string}|null
     */
    private function lookupMemberProfile(string $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT p.firstName, p.lastName, p.gender, pp.img
                 FROM personal p
                 LEFT JOIN profilePics pp ON p.id = pp.id
                 WHERE p.id = ? LIMIT 1"
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($row)) {
                return null;
            }

            $fullName = trim((string)($row['firstName'] ?? '') . ' ' . (string)($row['lastName'] ?? ''));
            $gender = (string)($row['gender'] ?? '');
            $img = trim((string)($row['img'] ?? ''));

            $avatarUrl = '';
            if ($img !== '') {
                $avatarUrl = '/resources/images/profile/' . rawurlencode(basename($img));
            }

            return [
                'fullName' => $fullName,
                'gender' => $gender,
                'img' => $avatarUrl
            ];
        } catch (\Throwable $e) {
            error_log('[ImmediateFamilyService::lookupMemberProfile] ' . $e->getMessage());
            return null;
        }
    }
}

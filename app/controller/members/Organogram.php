<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\SingleCustomerData;
use Src\{Db, SelectFn};
use Src\Exceptions\NotFoundException;

final class Organogram extends SingleCustomerData
{
    /**
     * Fetch full family data for the main person and render 6-gen view
     * @param string|array<string, mixed>|null $id
     * @return void
     */
    public function index($id = null): void
    {
        try {
            $rawId = is_string($id) ? checkInput($id) : ($_SESSION['id'] ?? '');
            $idStr = is_string($rawId) ? $rawId : (string)($_SESSION['id'] ?? '');

            if (empty($idStr)) {
                throw new NotFoundException('Main person ID required');
            }

            $data = BaseController::findMemberById($idStr);
            $familyCode = (string)($data['famCode'] ?? ($_SESSION['famCode'] ?? ''));

            // Ensure graph tables are initialized and synced with legacy records
            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);

            // Fetch 6-generation graph data
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);

            // Backwards-compatible orgData for existing templates
            $spouse = $this->fetchRelationsData($idStr, 'otherFamily', 'spouse');
            $father = $this->fetchRelationsData($idStr, 'otherFamily', 'father');
            $mother = $this->fetchRelationsData($idStr, 'otherFamily', 'mother');
            $siblings = $this->fetchRelationsData($idStr, 'sibling', 'sibling');
            $children = $this->fetchRelationsData($idStr, 'children', 'children');
            $siblingChildren = ($siblings !== null) ? $this->getSiblingChildren($siblings) : null;

            $orgData = [
                'spouse' => $spouse[0] ?? null,
                'father' => $father[0] ?? null,
                'mother' => $mother[0] ?? null,
                'siblings' => $siblings,
                'children' => $children,
                'sibling_children' => $siblingChildren,
                'graph' => $graphData
            ];

            $graphJson = json_encode($graphData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}';

            view('member/organogram', compact('orgData', 'data', 'graphData', 'graphJson'));
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * API: Get JSON graph data for a specific root member (up to 6 generations)
     * @param string|array<string, mixed>|null $id
     */
    public function getGraphData($id = null): void
    {
        try {
            $rawId = is_string($id) ? checkInput($id) : ($_SESSION['id'] ?? '');
            $idStr = is_string($rawId) ? $rawId : (string)($_SESSION['id'] ?? '');

            $data = BaseController::findMemberById($idStr);
            $familyCode = (string)($data['famCode'] ?? ($_SESSION['famCode'] ?? ''));

            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);

            msgSuccess(200, $graphData);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * API: Get complete details for a single node (Slide-out Heritage Dossier)
     * @param int|string $nodeId
     */
    public function getNodeDetails(int|string $id): void
    {
        try {
            $db = Db::connect2();

            if (is_numeric($id)) {
                $stmt = $db->prepare("SELECT * FROM family_nodes WHERE id = ?");
                $stmt->execute([(int)$id]);
            } else {
                $familyCode = $_SESSION['famCode'] ?? '';
                $stmt = $db->prepare("SELECT * FROM family_nodes WHERE user_id = ? AND family_code = ?");
                $stmt->execute([$id, $familyCode]);
            }
            
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(404, 'Node not found');
                return;
            }

            $nodeIdInt = (int) $node['id'];

            // Fetch Unions (including past divorces & current spouse)
            $unionStmt = $db->prepare("
                SELECT u.*, 
                    p1.first_name AS p1_first, p1.last_name AS p1_last, p1.avatar_url AS p1_avatar,
                    p2.first_name AS p2_first, p2.last_name AS p2_last, p2.avatar_url AS p2_avatar
                FROM family_unions u
                JOIN family_nodes p1 ON p1.id = u.partner_1_id
                JOIN family_nodes p2 ON p2.id = u.partner_2_id
                WHERE u.partner_1_id = ? OR u.partner_2_id = ?
                ORDER BY u.is_current DESC, u.marriage_year ASC
            ");
            $unionStmt->execute([$nodeIdInt, $nodeIdInt]);
            $unions = $unionStmt->fetchAll(\PDO::FETCH_ASSOC);

            $formattedUnions = [];
            foreach ($unions as $u) {
                $isPartner1 = ((int)$u['partner_1_id'] === $nodeIdInt);
                $partnerId = $isPartner1 ? (int)$u['partner_2_id'] : (int)$u['partner_1_id'];
                $partnerName = $isPartner1 
                    ? ($u['p2_first'] . ' ' . $u['p2_last']) 
                    : ($u['p1_first'] . ' ' . $u['p1_last']);
                $partnerAvatar = $isPartner1 ? $u['p2_avatar'] : $u['p1_avatar'];

                // Fetch children belonging to this specific union
                $childStmt = $db->prepare("
                    SELECT fn.*, fnc.relationship_type 
                    FROM family_node_children fnc
                    JOIN family_nodes fn ON fn.id = fnc.child_id
                    WHERE fnc.union_id = ?
                ");
                $childStmt->execute([$u['id']]);
                $children = $childStmt->fetchAll(\PDO::FETCH_ASSOC);

                $formattedUnions[] = [
                    'union_id' => (int)$u['id'],
                    'partner_id' => $partnerId,
                    'partner_name' => $partnerName,
                    'partner_avatar' => $partnerAvatar ?: '/resources/images/profile/avatarM.png',
                    'union_type' => (string)$u['union_type'],
                    'marriage_date' => $u['marriage_date'] ?? null,
                    'marriage_year' => $u['marriage_year'] ?? null,
                    'divorce_date' => $u['divorce_date'] ?? null,
                    'divorce_year' => $u['divorce_year'] ?? null,
                    'is_current' => (bool)$u['is_current'],
                    'notes' => $u['notes'] ?? null,
                    'children' => $children
                ];
            }

            // Fetch Parents
            $parentStmt = $db->prepare("
                SELECT parent.*, u.union_type, fnc.relationship_type
                FROM family_node_children fnc
                JOIN family_unions u ON u.id = fnc.union_id
                JOIN family_nodes parent ON (parent.id = u.partner_1_id OR parent.id = u.partner_2_id)
                WHERE fnc.child_id = ?
            ");
            $parentStmt->execute([$nodeIdInt]);
            $parents = $parentStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch Parent Unions (Unions where this node is a child)
            $parentUnionStmt = $db->prepare("
                SELECT u.*, 
                    p1.first_name AS p1_first, p1.last_name AS p1_last, p1.avatar_url AS p1_avatar,
                    p2.first_name AS p2_first, p2.last_name AS p2_last, p2.avatar_url AS p2_avatar
                FROM family_node_children fnc
                JOIN family_unions u ON u.id = fnc.union_id
                JOIN family_nodes p1 ON p1.id = u.partner_1_id
                JOIN family_nodes p2 ON p2.id = u.partner_2_id
                WHERE fnc.child_id = ?
            ");
            $parentUnionStmt->execute([$nodeIdInt]);
            $parentUnionsRaw = $parentUnionStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $formattedParentUnions = [];
            foreach ($parentUnionsRaw as $pu) {
                $p1Name = trim($pu['p1_first'] . ' ' . $pu['p1_last']);
                $p2Name = trim($pu['p2_first'] . ' ' . $pu['p2_last']);
                $formattedParentUnions[] = [
                    'union_id' => (int)$pu['id'],
                    'label' => ($p1Name ?: 'Unknown') . ' & ' . ($p2Name ?: 'Unknown')
                ];
            }

            $response = [
                'node' => $node,
                'unions' => $formattedUnions,
                'parents' => $parents,
                'parent_unions' => $formattedParentUnions
            ];

            msgSuccess(200, $response);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Build full 6-generation graph data
     * @return array<string, mixed>
     */
    private function buildSixGenGraphData(string $familyCode, string $rootUserId): array
    {
        $db = Db::connect2();

        // 1. Fetch all nodes for this family
        $stmt = $db->prepare("
            SELECT fn.*, 
                   CASE 
                       WHEN pp.img IS NOT NULL AND pp.img != '' AND pp.img NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', pp.img)
                       WHEN pp.img IS NOT NULL AND pp.img != '' THEN pp.img
                       WHEN fn.avatar_url IS NOT NULL AND fn.avatar_url != '' AND fn.avatar_url NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', fn.avatar_url)
                       ELSE fn.avatar_url
                   END AS avatar_url 
            FROM family_nodes fn 
            LEFT JOIN profilePics pp ON fn.user_id = pp.id 
            WHERE fn.family_code = ? 
            ORDER BY fn.generation_level ASC, fn.id ASC
        ");
        $stmt->execute([$familyCode]);
        $nodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Fetch all unions for this family
        $unionStmt = $db->prepare("SELECT * FROM family_unions WHERE family_code = ?");
        $unionStmt->execute([$familyCode]);
        $unions = $unionStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Fetch all children linkages
        $childStmt = $db->prepare("
            SELECT fnc.* 
            FROM family_node_children fnc
            JOIN family_unions fu ON fu.id = fnc.union_id
            WHERE fu.family_code = ?
        ");
        $childStmt->execute([$familyCode]);
        $children = $childStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 4. Find Root Node
        $rootNode = null;
        foreach ($nodes as $n) {
            if (($n['user_id'] ?? null) === $rootUserId) {
                $rootNode = $n;
                break;
            }
        }

        if (!$rootNode && !empty($nodes)) {
            $rootNode = $nodes[0];
        }

        // Format nodes for frontend
        $formattedNodes = [];
        foreach ($nodes as $node) {
            $sex = (($node['gender'] ?? 'Male') === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = !empty($node['avatar_url']) ? (string)$node['avatar_url'] : "/resources/images/profile/{$sex}";

            $formattedNodes[$node['id']] = [
                'id' => (int) $node['id'],
                'user_id' => $node['user_id'] ?? null,
                'first_name' => (string)($node['first_name'] ?? ''),
                'last_name' => (string)($node['last_name'] ?? ''),
                'full_name' => trim(($node['first_name'] ?? '') . ' ' . ($node['last_name'] ?? '')),
                'maiden_name' => $node['maiden_name'] ?? null,
                'gender' => (string)($node['gender'] ?? 'Male'),
                'birth_date' => $node['birth_date'] ?? null,
                'birth_year' => isset($node['birth_year']) ? (int)$node['birth_year'] : null,
                'birth_place' => $node['birth_place'] ?? null,
                'death_date' => $node['death_date'] ?? null,
                'death_year' => isset($node['death_year']) ? (int)$node['death_year'] : null,
                'death_place' => $node['death_place'] ?? null,
                'is_deceased' => (bool) ($node['is_deceased'] ?? false),
                'bio' => $node['bio'] ?? null,
                'occupation' => $node['occupation'] ?? null,
                'location' => $node['location'] ?? null,
                'email' => $node['email'] ?? null,
                'mobile' => $node['mobile'] ?? null,
                'avatar_url' => $avatar,
                'generation_level' => (int) ($node['generation_level'] ?? 0),
                'voice_capsule_url' => $node['voice_capsule_url'] ?? null,
                'is_root' => ($rootNode && (int)$node['id'] === (int)$rootNode['id'])
            ];
        }

        // Format unions
        $formattedUnions = [];
        foreach ($unions as $u) {
            $formattedUnions[] = [
                'id' => (int) $u['id'],
                'partner_1_id' => (int) $u['partner_1_id'],
                'partner_2_id' => (int) $u['partner_2_id'],
                'union_type' => (string) $u['union_type'],
                'marriage_year' => isset($u['marriage_year']) ? (int)$u['marriage_year'] : null,
                'divorce_year' => isset($u['divorce_year']) ? (int)$u['divorce_year'] : null,
                'is_current' => (bool) $u['is_current'],
                'notes' => $u['notes'] ?? null
            ];
        }

        // Format children links
        $formattedChildren = [];
        foreach ($children as $c) {
            $formattedChildren[] = [
                'union_id' => (int) $c['union_id'],
                'child_id' => (int) $c['child_id'],
                'relationship_type' => (string) $c['relationship_type']
            ];
        }

        return [
            'family_code' => $familyCode,
            'root_node_id' => $rootNode ? (int) $rootNode['id'] : 0,
            'nodes' => array_values($formattedNodes),
            'unions' => $formattedUnions,
            'children' => $formattedChildren,
            'total_generations' => 6,
            'total_members' => count($formattedNodes)
        ];
    }

    /**
     * Synchronize legacy family tables into the graph structure
     * @param array<string, mixed> $memberData
     */
    private function syncLegacyFamilyToGraph(string $familyCode, string $userId, array $memberData): void
    {
        if (empty($familyCode)) return;

        $db = Db::connect2();

        // Check if root user node already exists
        $checkStmt = $db->prepare("SELECT id FROM family_nodes WHERE family_code = ? AND user_id = ?");
        $checkStmt->execute([$familyCode, $userId]);
        $existingRootId = $checkStmt->fetchColumn();

        if ($existingRootId) {
            return; // Already initialized
        }

        // AUTO-CLAIM ALGORITHM
        // Try to find if a relative already manually created a node for this user
        $firstName = trim((string)($memberData['firstName'] ?? ''));
        $lastName = trim((string)($memberData['lastName'] ?? ''));
        $email = trim((string)($memberData['email'] ?? ''));
        $mobile = trim((string)($memberData['mobile'] ?? ''));

        $claimQuery = "SELECT id FROM family_nodes WHERE family_code = ? AND user_id IS NULL AND (";
        $claimConditions = [];
        $claimParams = [$familyCode];

        if (!empty($email)) {
            $claimConditions[] = "email = ?";
            $claimParams[] = $email;
        }
        if (!empty($mobile)) {
            $claimConditions[] = "mobile = ?";
            $claimParams[] = $mobile;
        }
        if (!empty($firstName) && !empty($lastName)) {
            $claimConditions[] = "(first_name = ? AND last_name = ?)";
            $claimParams[] = $firstName;
            $claimParams[] = $lastName;
        }

        if (!empty($claimConditions)) {
            $claimQuery .= implode(' OR ', $claimConditions) . ") LIMIT 1";
            $claimStmt = $db->prepare($claimQuery);
            $claimStmt->execute($claimParams);
            $claimedNodeId = $claimStmt->fetchColumn();

            if ($claimedNodeId) {
                // Link the user to the existing node seamlessly!
                $updateClaim = $db->prepare("UPDATE family_nodes SET user_id = ? WHERE id = ?");
                $updateClaim->execute([$userId, $claimedNodeId]);
                return; // Stop execution to prevent spawning duplicate parents/grandparents
            }
        }

        // 1. Insert Root User Node (Generation 0)
        $sex = (($memberData['gender'] ?? 'Male') === 'Male') ? 'avatarM.png' : 'avatarF.png';
        $rootAvatar = (string)($memberData['img'] ?? "/resources/images/profile/{$sex}");

        $insRoot = $db->prepare("
            INSERT INTO family_nodes (family_code, user_id, first_name, last_name, gender, birth_year, occupation, location, email, mobile, avatar_url, generation_level, bio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
        ");
        $insRoot->execute([
            $familyCode,
            $userId,
            (string)($memberData['firstName'] ?? 'Self'),
            (string)($memberData['lastName'] ?? ''),
            (string)($memberData['gender'] ?? 'Male'),
            !empty($memberData['year']) ? (int)$memberData['year'] : null,
            $memberData['occupation'] ?? null,
            $memberData['country'] ?? null,
            $memberData['email'] ?? null,
            $memberData['mobile'] ?? null,
            $rootAvatar,
            "Family Platform Root Member"
        ]);
        $rootNodeId = (int) $db->lastInsertId();

        // 2. Fetch and insert Parents (Generation -1)
        $fatherName = (string)($memberData['father_name'] ?? 'Father');
        $motherName = (string)($memberData['mother_name'] ?? 'Mother');

        $insParent = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
            VALUES (?, ?, ?, ?, -1, ?, ?)
        ");

        $insParent->execute([
            $familyCode,
            $fatherName,
            (string)($memberData['lastName'] ?? ''),
            'Male',
            '/resources/images/profile/avatarM.png',
            'Father'
        ]);
        $fatherId = (int) $db->lastInsertId();

        $insParent->execute([
            $familyCode,
            $motherName,
            (string)($memberData['lastName'] ?? ''),
            'Female',
            '/resources/images/profile/avatarF.png',
            'Mother'
        ]);
        $motherId = (int) $db->lastInsertId();

        // Create Parent Union
        $insUnion = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $insUnion->execute([$familyCode, $fatherId, $motherId]);
        $parentUnionId = (int) $db->lastInsertId();

        // Link Root User as Child of Parents Union
        $insChild = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");
        $insChild->execute([$parentUnionId, $rootNodeId]);

        // 3. Grandparents (Generation -2) — Paternal & Maternal
        $insGP = $db->prepare("
            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, is_deceased, avatar_url)
            VALUES (?, ?, ?, ?, ?, 1, ?)
        ");

        // Paternal Grandparents
        $insGP->execute([$familyCode, 'Grandfather (Paternal)', (string)($memberData['lastName'] ?? ''), 'Male', -2, '/resources/images/profile/avatarM.png']);
        $patGFId = (int) $db->lastInsertId();
        $insGP->execute([$familyCode, 'Grandmother (Paternal)', '', 'Female', -2, '/resources/images/profile/avatarF.png']);
        $patGMId = (int) $db->lastInsertId();

        $insUnion->execute([$familyCode, $patGFId, $patGMId]);
        $patUnionId = (int) $db->lastInsertId();
        $insChild->execute([$patUnionId, $fatherId]);

        // Maternal Grandparents
        $insGP->execute([$familyCode, 'Grandfather (Maternal)', '', 'Male', -2, '/resources/images/profile/avatarM.png']);
        $matGFId = (int) $db->lastInsertId();
        $insGP->execute([$familyCode, 'Grandmother (Maternal)', '', 'Female', -2, '/resources/images/profile/avatarF.png']);
        $matGMId = (int) $db->lastInsertId();

        $insUnion->execute([$familyCode, $matGFId, $matGMId]);
        $matUnionId = (int) $db->lastInsertId();
        $insChild->execute([$matUnionId, $motherId]);

        // Great-Grandparents (Generation -3)
        $insGP->execute([$familyCode, 'Great-Grandfather (Paternal Line)', (string)($memberData['lastName'] ?? ''), 'Male', -3, '/resources/images/profile/avatarM.png']);
        $ggfId = (int) $db->lastInsertId();
        $insGP->execute([$familyCode, 'Great-Grandmother (Paternal Line)', '', 'Female', -3, '/resources/images/profile/avatarF.png']);
        $ggmId = (int) $db->lastInsertId();
        $insUnion->execute([$familyCode, $ggfId, $ggmId]);
        $ggUnionId = (int) $db->lastInsertId();
        $insChild->execute([$ggUnionId, $patGFId]);

        // 4. Spouse & Multiple Partners
        $spouseName = (string)($memberData['spouse_name'] ?? '');
        $currentUnionId = null;
        if (!empty($spouseName)) {
            $insSpouse = $db->prepare("
                INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
                VALUES (?, ?, '', 'Female', 0, '/resources/images/profile/avatarF.png', 'Current Spouse')
            ");
            $insSpouse->execute([$familyCode, $spouseName]);
            $currentSpouseId = (int) $db->lastInsertId();

            $insUnion->execute([$familyCode, $rootNodeId, $currentSpouseId]);
            $currentUnionId = (int) $db->lastInsertId();
        }

        // 5. Siblings from legacy table
        $siblings = SelectFn::selectAllRowsById('sibling', 'id', $userId);
        if (!empty($siblings)) {
            foreach ($siblings as $sib) {
                $sibName = (string)($sib['sibling_name'] ?? 'Sibling');
                $sibGender = (string)($sib['sibling_gender'] ?? 'Male');
                $sibSex = ($sibGender === 'Male') ? 'avatarM.png' : 'avatarF.png';

                $insSib = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, email)
                    VALUES (?, ?, ?, ?, 0, ?, ?)
                ");
                $insSib->execute([
                    $familyCode,
                    $sibName,
                    (string)($memberData['lastName'] ?? ''),
                    $sibGender,
                    "/resources/images/profile/{$sibSex}",
                    $sib['sibling_email'] ?? null
                ]);
                $sibNodeId = (int) $db->lastInsertId();
                $insChild->execute([$parentUnionId, $sibNodeId]);
            }
        }

        // 6. Children from legacy table (Generation +1)
        $children = SelectFn::selectAllRowsById('children', 'id', $userId);
        if (!empty($children)) {
            foreach ($children as $ch) {
                $chName = (string)($ch['children_name'] ?? 'Child');
                $chGender = (string)($ch['children_gender'] ?? 'Female');
                $chSex = ($chGender === 'Male') ? 'avatarM.png' : 'avatarF.png';

                $insCh = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, email)
                    VALUES (?, ?, ?, ?, 1, ?, ?)
                ");
                $insCh->execute([
                    $familyCode,
                    $chName,
                    (string)($memberData['lastName'] ?? ''),
                    $chGender,
                    "/resources/images/profile/{$chSex}",
                    $ch['children_email'] ?? null
                ]);
                $chNodeId = (int) $db->lastInsertId();

                $targetUnion = $currentUnionId ?? $parentUnionId;
                $insChild->execute([$targetUnion, $chNodeId]);
            }
        }
    }

    /**
     * @return array[]|null
     * @psalm-return list{0?: array,...}|null
     */
    private function fetchRelationsData(string $id, string $table, string $who): array|null
    {
        $data = [];
        $otherFamily = SelectFn::selectAllRowsById($table, 'id', $id);
        if (empty($otherFamily)) return null;

        foreach ($otherFamily as $row) {
            $email = $row["{$who}_email"] ?? null;
            if ($email) {
                $fetchTheId = SelectFn::selectAllRowsById('contact', 'email', $email);

                if (!empty($fetchTheId)) {
                    foreach ($fetchTheId as $getData) {
                        $fullData = BaseController::findMemberById($getData['id']);
                        if (!$fullData) continue;

                        if (isset($fullData['gender'])) {
                            $fullData['relationship'] = $this->resolveRelationship($who, $fullData['gender']);
                        }

                        $data[] = $fullData;
                    }
                } else {
                    $relationship = $this->resolveRelationship($who, $row["{$who}_gender"] ?? null);
                    $sex = (($row["{$who}_gender"] ?? '') === 'Male' || $who === 'father') ? 'avatarM.png' : 'avatarF.png';
                    $data[] = [
                        'fullName' => $row["{$who}_name"] ?? '',
                        'email' => $row["{$who}_email"] ?? '',
                        'mobile' => $row["{$who}_mobile"] ?? '',
                        'linked' => $row["{$who}_linked"] ?? '',
                        'relationship' => $relationship,
                        'gender' => $row["{$who}_gender"] ?? '',
                        'img' => isset($row['img']) ? "/resources/images/profile/{$row['img']}" : "/resources/images/profile/$sex"
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Fetch sibling children
     * @param array<int, array<string, mixed>> $siblings
     * @return array<int, array<string, mixed>>
     */
    private function getSiblingChildren(array $siblings): array
    {
        $siblingChildren = [];
        foreach ($siblings as $sibling) {
            $id = (string)($sibling['id'] ?? '');
            if (empty($id)) continue;

            $children = SelectFn::selectAllRowsById('children', 'id', $id);

            if (!empty($children)) {
                foreach ($children as $data) {
                    $email = $data["children_email"] ?? null;
                    if (!$email) continue;

                    $fetchTheId = SelectFn::selectAllRowsById('contact', 'email', $email);

                    if (!empty($fetchTheId)) {
                        foreach ($fetchTheId as $getData) {
                            $fullData = BaseController::findMemberById($getData['id']);
                            if (!$fullData) continue;

                            if (isset($fullData['gender'])) {
                                $fullData['relationship'] = $this->resolveRelationship('sibling_children', $fullData['gender']);
                                $fullData['father_id'] = $id;
                            }

                            $siblingChildren[] = $fullData;
                        }
                    } else {
                        $gender = (string)($data['children_gender'] ?? 'Female');
                        $sex = $gender === 'Male' ? 'avatarM.png' : 'avatarF.png';
                        $siblingChildren[] = [
                            'father_id' => $id,
                            'fullName' => $data['children_name'] ?? '',
                            'email' => $data['children_email'] ?? '',
                            'gender' => $gender,
                            'relationship' => $this->resolveRelationship('sibling_children', $gender),
                            'linked' => $data['children_linked'] ?? '',
                            'img' => isset($data['img']) ? "/resources/images/profile/{$data['img']}" : "/resources/images/profile/$sex"
                        ];
                    }
                }
            }
        }
        return $siblingChildren;
    }

    /**
     * Resolve relationship label based on role and gender.
     */
    private function resolveRelationship(string $role, ?string $gender): ?string
    {
        $map = [
            'sibling' => [
                'Male' => 'Brother',
                'Female' => 'Sister',
            ],
            'children' => [
                'Male' => 'Son',
                'Female' => 'Daughter',
            ],
            'sibling_children' => [
                'Male' => 'Nephew',
                'Female' => 'Niece',
            ],
        ];

        return $map[$role][$gender] ?? null;
    }
}

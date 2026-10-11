<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\SingleCustomerData;
use Src\{Db, SelectFn};
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ForbiddenException;

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

            if (empty($familyCode)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid family context']);
                exit;
            }

            // Access control: viewable by members of that family OR approved connected kin.
            if (!BaseController::sessionCanViewMember($idStr, $familyCode)) {
                http_response_code(403);
                echo json_encode(['error' => 'You can only view trees of your own family or approved connections.']);
                exit;
            }

            $isReadOnly = !BaseController::sessionSharesFamily($familyCode);

            // Ensure graph tables are initialized and synced with legacy records
            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);
            $this->repairOrphanedFamilyGraph($familyCode, $idStr);
            \App\services\KinshipEntityResolver::healDuplicateNodes($familyCode);

            // Fetch 6-generation graph data
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);
            $graphData['isReadOnly'] = $isReadOnly;

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

            $nodeAnalysis = $this->analyzeMissingNodes($orgData, $graphData);

            view('member/organogram', compact('orgData', 'data', 'graphData', 'graphJson', 'nodeAnalysis', 'isReadOnly'));
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Dedicated Family Lineage Studio Page
     * Provides a world-class dedicated hub to manage generations, relatives, maiden names, and invites.
     * @param string|array<string, mixed>|null $id
     * @return void
     */
    public function familyStudio($id = null): void
    {
        try {
            $rawId = is_string($id) ? checkInput($id) : ($_SESSION['id'] ?? '');
            $idStr = is_string($rawId) ? $rawId : (string)($_SESSION['id'] ?? '');

            if (empty($idStr)) {
                throw new NotFoundException('Member ID required');
            }

            $data = BaseController::findMemberById($idStr);
            $familyCode = (string)($data['famCode'] ?? ($_SESSION['famCode'] ?? ''));

            // Access control: viewable by members of that family OR approved connected kin.
            if (!BaseController::sessionCanViewMember($idStr, $familyCode)) {
                throw new ForbiddenException('You can only view family studios of your own family or approved connections.');
            }

            $isReadOnly = !BaseController::sessionSharesFamily($familyCode);

            // Ensure graph is initialized & synced
            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);
            $this->repairOrphanedFamilyGraph($familyCode, $idStr);
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);
            $graphData['isReadOnly'] = $isReadOnly;

            // Backwards-compatible orgData
            $spouse = $this->fetchRelationsData($idStr, 'otherFamily', 'spouse');
            $father = $this->fetchRelationsData($idStr, 'otherFamily', 'father');
            $mother = $this->fetchRelationsData($idStr, 'otherFamily', 'mother');
            $siblings = $this->fetchRelationsData($idStr, 'sibling', 'sibling');
            $children = $this->fetchRelationsData($idStr, 'children', 'children');

            $nodes = $graphData['nodes'] ?? [];
            $unions = $graphData['unions'] ?? [];

            // Group nodes by generation level
            $generations = [
                'grandparents' => [], // level -2
                'parents'      => [], // level -1
                'household'    => [], // level 0 (self & partners & siblings)
                'children'     => [], // level 1
                'grandchildren'=> [], // level 2
                'other'        => []
            ];

            $livingCount = 0;
            $deceasedCount = 0;

            foreach ($nodes as $node) {
                $lvl = (int)($node['generation_level'] ?? 0);
                if (!empty($node['is_deceased'])) {
                    $deceasedCount++;
                } else {
                    $livingCount++;
                }

                if ($lvl === -2) {
                    $generations['grandparents'][] = $node;
                } elseif ($lvl === -1) {
                    $generations['parents'][] = $node;
                } elseif ($lvl === 0) {
                    $generations['household'][] = $node;
                } elseif ($lvl === 1) {
                    $generations['children'][] = $node;
                } elseif ($lvl === 2) {
                    $generations['grandchildren'][] = $node;
                } else {
                    $generations['other'][] = $node;
                }
            }

            $stats = [
                'total_members'    => count($nodes),
                'living_count'     => $livingCount,
                'deceased_count'   => $deceasedCount,
                'total_unions'     => count($unions),
                'generations_count'=> count(array_filter($generations, fn($g) => count($g) > 0))
            ];

            $orgData = [
                'spouse'   => $spouse[0] ?? null,
                'father'   => $father[0] ?? null,
                'mother'   => $mother[0] ?? null,
                'siblings' => $siblings,
                'children' => $children,
                'graph'    => $graphData
            ];

            $rootNodeId = 0;
            foreach ($nodes as $node) {
                if (($node['user_id'] ?? null) === $idStr) {
                    $rootNodeId = (int)$node['id'];
                    break;
                }
            }
            if ($rootNodeId === 0 && !empty($nodes)) {
                $rootNodeId = (int)$nodes[0]['id'];
            }

            $graphJson = json_encode($graphData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}';

            view('member/familyStudio', compact('data', 'graphData', 'orgData', 'generations', 'stats', 'graphJson', 'rootNodeId', 'isReadOnly'));
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

            if (empty($familyCode)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid family context.']);
                exit;
            }

            // Access control: viewable by members of that family OR approved connected kin.
            if (!BaseController::sessionCanViewMember($idStr, $familyCode)) {
                http_response_code(403);
                echo json_encode(['error' => 'You can only view trees of your own family or approved connections.']);
                exit;
            }

            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);
            $this->repairOrphanedFamilyGraph($familyCode, $idStr);
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);
            $graphData['isReadOnly'] = !BaseController::sessionSharesFamily($familyCode);

            msgSuccess(200, $graphData);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * API: Get complete details for a single node (Slide-out Heritage Dossier)
     * @param int|string $id
     */
    public function getNodeDetails(int|string $id): void
    {
        try {
            $db = Db::connect2();

            if (is_numeric($id)) {
                $stmt = $db->prepare("
                    SELECT fn.*,
                           COALESCE(NULLIF(p.firstName, ''), fn.first_name) AS first_name,
                           COALESCE(NULLIF(p.lastName, ''), fn.last_name) AS last_name
                    FROM family_nodes fn
                    LEFT JOIN personal p ON fn.user_id = p.id
                    WHERE fn.id = ?
                ");
                $stmt->execute([(int)$id]);
            } else {
                $familyCode = $_SESSION['famCode'] ?? '';
                $stmt = $db->prepare("
                    SELECT fn.*,
                           COALESCE(NULLIF(p.firstName, ''), fn.first_name) AS first_name,
                           COALESCE(NULLIF(p.lastName, ''), fn.last_name) AS last_name
                    FROM family_nodes fn
                    LEFT JOIN personal p ON fn.user_id = p.id
                    WHERE fn.user_id = ? AND fn.family_code = ?
                ");
                $stmt->execute([$id, $familyCode]);
            }

            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(404, 'Node not found');
                return;
            }

            $nodeFamCode = (string) ($node['family_code'] ?? '');
            $nodeUserId = (string) ($node['user_id'] ?? '');

            // IDOR Protection: Validate node belongs to an accessible family
            if (empty($nodeFamCode)) {
                msgException(403, 'Invalid node context');
                return;
            }

            // Access control: viewable by members of that family OR approved connected kin.
            // Must pass BOTH checks: either same family OR (has valid connection AND user not empty)
            $isOwnFamily = BaseController::sessionSharesFamily($nodeFamCode);
            $isApprovedConnection = !empty($nodeUserId) && BaseController::sessionCanViewMember($nodeUserId, $nodeFamCode);

            if (!$isOwnFamily && !$isApprovedConnection) {
                msgException(403, 'Access denied: You do not have permission to view this node');
                return;
            }

            $isReadOnly = !BaseController::sessionSharesFamily($nodeFamCode);
            $node['isReadOnly'] = $isReadOnly;

            if ($isReadOnly) {
                // Privacy redaction for connected kin: mask contact and exact birth dates of living nodes
                $node['email'] = '';
                $node['mobile'] = '';
                if (empty($node['is_deceased']) && !empty($node['birth_date'])) {
                    // Only show birth year to connected kin for privacy
                    $birthTs = strtotime((string) $node['birth_date']);
                    if ($birthTs !== false) {
                        $node['birth_date'] = date('Y', $birthTs);
                    }
                }
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
     * Build full multi-generational collateral graph data
     * Powered by CollateralKinshipService (Ancestry & MyHeritage Enterprise Topology)
     *
     * @param string $familyCode Primary family code of the context
     * @param string $rootUserId Member ID of the focal Ego
     * @return array<string, mixed>
     */
    private function buildSixGenGraphData(string $familyCode, string $rootUserId): array
    {
        return \App\services\CollateralKinshipService::buildCollateralGraph($familyCode, $rootUserId);
    }

    /**
     * Synchronize legacy family tables into the graph structure
     * @param array<string, mixed> $memberData
     */
    private function syncLegacyFamilyToGraph(string $familyCode, string $userId, array $memberData): void
    {
        if (empty($familyCode)) return;

        $db = Db::connect2();

        // 1. Resolve or initialize root node for this user
        $checkStmt = $db->prepare("SELECT id, generation_level, gender FROM family_nodes WHERE family_code = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$familyCode, $userId]);
        $rootRow = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        if ($rootRow) {
            $rootNodeId = (int)$rootRow['id'];
            $genLevel = (int)($rootRow['generation_level'] ?? 0);
            $userGender = (string)($rootRow['gender'] ?? 'Male');

            // Keep first_name and last_name in sync with latest personal profile
            $firstName = trim((string)($memberData['firstName'] ?? ''));
            $lastName = trim((string)($memberData['lastName'] ?? ''));
            if (!empty($firstName)) {
                $upd = $db->prepare("UPDATE family_nodes SET first_name = ?, last_name = ? WHERE id = ?");
                $upd->execute([$firstName, $lastName, $rootNodeId]);
            }
        } else {
            $rootNodeId = \App\services\FamilyClaimService::claimOrInitializeNode($familyCode, $userId, $memberData);
            if ($rootNodeId <= 0) {
                return;
            }
            $checkStmt->execute([$familyCode, $userId]);
            $rootRow = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            $genLevel = (int)($rootRow['generation_level'] ?? 0);
            $userGender = (string)($rootRow['gender'] ?? 'Male');
        }

        // Reusable inserts
        $insUnion = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $insChild = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");

        // 2. Sync Spouse / Partner for this user
        $spouseName = trim((string)($memberData['spouse_name'] ?? ''));
        if (empty($spouseName)) {
            $legacyOther = SelectFn::selectAllRowsById('otherFamily', 'id', $userId);
            if (!empty($legacyOther) && !empty($legacyOther[0]['spouse_name'])) {
                $spouseName = trim((string)$legacyOther[0]['spouse_name']);
            }
        }

        // Check if user already has an active union
        $findCurrentUnion = $db->prepare("
            SELECT id, partner_1_id, partner_2_id 
            FROM family_unions 
            WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?) 
            LIMIT 1
        ");
        $findCurrentUnion->execute([$familyCode, $rootNodeId, $rootNodeId]);
        $existingUnion = $findCurrentUnion->fetch(\PDO::FETCH_ASSOC);
        $currentUnionId = $existingUnion ? (int)$existingUnion['id'] : null;

        if (!empty($spouseName) && !$existingUnion) {
            $spouseGender = ($userGender === 'Male') ? 'Female' : 'Male';
            $spouseSex = ($spouseGender === 'Male') ? 'avatarM.png' : 'avatarF.png';

            // Check if partner node already exists by name
            $findSpNode = $db->prepare("
                SELECT id FROM family_nodes 
                WHERE family_code = ? AND (LOWER(TRIM(CONCAT(first_name, ' ', last_name))) = LOWER(?) OR LOWER(first_name) = LOWER(?))
                LIMIT 1
            ");
            $findSpNode->execute([$familyCode, $spouseName, $spouseName]);
            $existingSpId = $findSpNode->fetchColumn();

            if ($existingSpId) {
                $currentSpouseId = (int)$existingSpId;
            } else {
                $spParts = explode(' ', $spouseName, 2);
                $insSpouse = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
                    VALUES (?, ?, ?, ?, ?, ?, 'Partner/Spouse')
                ");
                $insSpouse->execute([
                    $familyCode,
                    $spParts[0],
                    $spParts[1] ?? '',
                    $spouseGender,
                    $genLevel,
                    "/resources/images/profile/{$spouseSex}"
                ]);
                $currentSpouseId = (int)$db->lastInsertId();
            }

            $insUnion->execute([$familyCode, $rootNodeId, $currentSpouseId]);
            $currentUnionId = (int)$db->lastInsertId();
        }

        // 3. Sync Children for this user (Generation Level + 1)
        $children = SelectFn::selectAllRowsById('children', 'id', $userId);
        if (!empty($children)) {
            // If user has children but no union, create a personal/co-parent union
            if (!$currentUnionId) {
                $insUnion->execute([$familyCode, $rootNodeId, $rootNodeId]);
                $currentUnionId = (int)$db->lastInsertId();
            }

            foreach ($children as $ch) {
                $chName = trim((string)($ch['children_name'] ?? ''));
                if (empty($chName) || strcasecmp($chName, 'Child') === 0) continue;

                $chGender = trim((string)($ch['children_gender'] ?? 'Male'));
                if ($chGender !== 'Female' && $chGender !== 'Male') $chGender = 'Male';
                $chSex = ($chGender === 'Male') ? 'avatarM.png' : 'avatarF.png';
                $chEmail = !empty($ch['children_email']) && !str_contains($ch['children_email'], 'provide') ? trim($ch['children_email']) : null;

                // Check if this child node already exists
                $findChildNode = $db->prepare("
                    SELECT id FROM family_nodes 
                    WHERE family_code = ? AND (LOWER(TRIM(CONCAT(first_name, ' ', last_name))) = LOWER(?) OR LOWER(first_name) = LOWER(?))
                    LIMIT 1
                ");
                $findChildNode->execute([$familyCode, $chName, $chName]);
                $existingChildId = $findChildNode->fetchColumn();

                if ($existingChildId) {
                    $chNodeId = (int)$existingChildId;
                } else {
                    $chParts = explode(' ', $chName, 2);
                    $insCh = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email)
                        VALUES (?, ?, ?, ?, ?, ?, 'Child', ?)
                    ");
                    $insCh->execute([
                        $familyCode,
                        $chParts[0],
                        $chParts[1] ?? (string)($memberData['lastName'] ?? ''),
                        $chGender,
                        $genLevel + 1,
                        "/resources/images/profile/{$chSex}",
                        $chEmail
                    ]);
                    $chNodeId = (int)$db->lastInsertId();
                }

                // Check if already linked to this union
                $checkLink = $db->prepare("SELECT id FROM family_node_children WHERE union_id = ? AND child_id = ? LIMIT 1");
                $checkLink->execute([$currentUnionId, $chNodeId]);
                if (!$checkLink->fetchColumn()) {
                    $insChild->execute([$currentUnionId, $chNodeId]);
                }
            }
        }

        // 4. Parents & Siblings from legacy table (only if root node is not yet connected to a parent union)
        $findPUnion = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ? LIMIT 1");
        $findPUnion->execute([$rootNodeId]);
        $parentUnionId = (int)$findPUnion->fetchColumn();

        if ($parentUnionId <= 0) {
            $otherFamily = SelectFn::selectAllRowsById('otherFamily', 'id', $userId);
            if (!empty($otherFamily)) {
                $fatherName = trim((string)($otherFamily[0]['father_name'] ?? ''));
                $motherName = trim((string)($otherFamily[0]['mother_name'] ?? ''));

                if (!empty($fatherName) || !empty($motherName)) {
                    $fParts = !empty($fatherName) ? explode(' ', $fatherName, 2) : ['Father', ''];
                    $mParts = !empty($motherName) ? explode(' ', $motherName, 2) : ['Mother', ''];

                    $insParent = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");

                    $fId = null;
                    if (!empty($fatherName)) {
                        $insParent->execute([$familyCode, $fParts[0], $fParts[1] ?? '', 'Male', $genLevel - 1, '/resources/images/profile/avatarM.png', 'Father']);
                        $fId = (int)$db->lastInsertId();
                    }

                    $mId = null;
                    if (!empty($motherName)) {
                        $insParent->execute([$familyCode, $mParts[0], $mParts[1] ?? '', 'Female', $genLevel - 1, '/resources/images/profile/avatarF.png', 'Mother']);
                        $mId = (int)$db->lastInsertId();
                    }

                    if ($fId && $mId) {
                        $insUnion->execute([$familyCode, $fId, $mId]);
                        $parentUnionId = (int)$db->lastInsertId();
                    } elseif ($fId) {
                        $insUnion->execute([$familyCode, $fId, $fId]);
                        $parentUnionId = (int)$db->lastInsertId();
                    } elseif ($mId) {
                        $insUnion->execute([$familyCode, $mId, $mId]);
                        $parentUnionId = (int)$db->lastInsertId();
                    }

                    if ($parentUnionId > 0) {
                        $insChild->execute([$parentUnionId, $rootNodeId]);
                    }
                }
            }
        }

        // 5. Siblings from legacy table
        if ($parentUnionId > 0) {
            $siblings = SelectFn::selectAllRowsById('sibling', 'id', $userId);
            if (!empty($siblings)) {
                foreach ($siblings as $sib) {
                    $sibName = trim((string)($sib['sibling_name'] ?? ''));
                    if (empty($sibName) || strcasecmp($sibName, 'Sibling') === 0) continue;

                    $sibGender = (string)($sib['sibling_gender'] ?? 'Male');
                    $sibSex = ($sibGender === 'Male') ? 'avatarM.png' : 'avatarF.png';

                    // Check if sibling node already exists
                    $findSibNode = $db->prepare("
                        SELECT id FROM family_nodes 
                        WHERE family_code = ? AND (LOWER(TRIM(CONCAT(first_name, ' ', last_name))) = LOWER(?) OR LOWER(first_name) = LOWER(?))
                        LIMIT 1
                    ");
                    $findSibNode->execute([$familyCode, $sibName, $sibName]);
                    $existingSibId = $findSibNode->fetchColumn();

                    if ($existingSibId) {
                        $sibNodeId = (int)$existingSibId;
                    } else {
                        $sParts = explode(' ', $sibName, 2);
                        $insSib = $db->prepare("
                            INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, email)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insSib->execute([
                            $familyCode,
                            $sParts[0],
                            $sParts[1] ?? (string)($memberData['lastName'] ?? ''),
                            $sibGender,
                            $genLevel,
                            "/resources/images/profile/{$sibSex}",
                            $sib['sibling_email'] ?? null
                        ]);
                        $sibNodeId = (int)$db->lastInsertId();
                    }

                    $checkSibLink = $db->prepare("SELECT id FROM family_node_children WHERE union_id = ? AND child_id = ? LIMIT 1");
                    $checkSibLink->execute([$parentUnionId, $sibNodeId]);
                    if (!$checkSibLink->fetchColumn()) {
                        $insChild->execute([$parentUnionId, $sibNodeId]);
                    }
                }
            }
        }
    }

    /**
     * Auto-heal disconnected or legacy families where nodes exist across generations
     * but DAG unions and child links are missing (e.g. from partial legacy sync or imported data).
     */
    private function repairOrphanedFamilyGraph(string $familyCode, string $rootUserId): void
    {
        if (empty($familyCode)) {
            return;
        }

        $db = Db::connect2();

        // 1. Fast-path check: if family already has valid unions, no healing required
        $checkUnions = $db->prepare("SELECT COUNT(*) FROM family_unions WHERE family_code = ?");
        $checkUnions->execute([$familyCode]);
        if ((int)$checkUnions->fetchColumn() > 0) {
            return;
        }

        // 2. Check if family has multiple nodes that need hierarchical reconnection
        $checkNodes = $db->prepare("SELECT COUNT(*) FROM family_nodes WHERE family_code = ?");
        $checkNodes->execute([$familyCode]);
        if ((int)$checkNodes->fetchColumn() <= 1) {
            return;
        }

        // Fetch all nodes for this family ordered by generation level
        $stmt = $db->prepare("SELECT * FROM family_nodes WHERE family_code = ? ORDER BY generation_level ASC, id ASC");
        $stmt->execute([$familyCode]);
        /** @var array<int, array<string, mixed>> $nodes */
        $nodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($nodes)) {
            return;
        }

        // Identify parent nodes (Gen -1 or bio Father/Mother)
        $father = null;
        $mother = null;
        foreach ($nodes as $n) {
            $lvl = (int)($n['generation_level'] ?? 0);
            $bio = strtolower((string)($n['bio'] ?? ''));
            if ($lvl === -1 || str_contains($bio, 'father')) {
                if (($n['gender'] ?? '') === 'Male' && !$father) {
                    $father = $n;
                }
            }
            if ($lvl === -1 || str_contains($bio, 'mother')) {
                if (($n['gender'] ?? '') === 'Female' && !$mother) {
                    $mother = $n;
                }
            }
        }

        $insUnion = $db->prepare("
            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
            VALUES (?, ?, ?, 'married', 1)
        ");
        $insChild = $db->prepare("
            INSERT INTO family_node_children (union_id, child_id, relationship_type)
            VALUES (?, ?, 'biological')
        ");

        $parentUnionId = null;
        if ($father && $mother) {
            $insUnion->execute([$familyCode, (int)$father['id'], (int)$mother['id']]);
            $parentUnionId = (int)$db->lastInsertId();
        } elseif ($father) {
            $insUnion->execute([$familyCode, (int)$father['id'], (int)$father['id']]);
            $parentUnionId = (int)$db->lastInsertId();
        } elseif ($mother) {
            $insUnion->execute([$familyCode, (int)$mother['id'], (int)$mother['id']]);
            $parentUnionId = (int)$db->lastInsertId();
        }

        // Connect all Generation 0 siblings & primary members to parent union
        if ($parentUnionId) {
            foreach ($nodes as $n) {
                if ((int)($n['generation_level'] ?? 0) === 0) {
                    $insChild->execute([$parentUnionId, (int)$n['id']]);
                }
            }
        }

        // Find primary root user node to connect Generation 1 children
        $rootNode = null;
        foreach ($nodes as $n) {
            if (($n['user_id'] ?? '') === $rootUserId) {
                $rootNode = $n;
                break;
            }
        }
        if (!$rootNode) {
            foreach ($nodes as $n) {
                if ((int)($n['generation_level'] ?? 0) === 0) {
                    $rootNode = $n;
                    break;
                }
            }
        }

        // Connect Generation 1 children
        $gen1Children = array_filter($nodes, fn($n) => (int)($n['generation_level'] ?? 0) === 1);
        if ($rootNode && !empty($gen1Children)) {
            $insUnion->execute([$familyCode, (int)$rootNode['id'], (int)$rootNode['id']]);
            $rootUnionId = (int)$db->lastInsertId();

            foreach ($gen1Children as $ch) {
                $insChild->execute([$rootUnionId, (int)$ch['id']]);
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

    public function claimNode(): void
    {
        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($userId) || empty($famCode)) {
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            $rawInput = file_get_contents('php://input') ?: '';
            $input = json_decode($rawInput, true) ?: $_POST;
            $nodeId = (int)($input['node_id'] ?? 0);

            if ($nodeId <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid node ID']);
                return;
            }

            $claimed = \App\services\FamilyClaimService::claimNodeById($nodeId, $userId, $famCode);
            if ($claimed) {
                unset($_SESSION['dismissed_claim_node_' . $nodeId]);
                echo json_encode(['status' => 'success', 'message' => 'Tree node successfully claimed!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'This node is already claimed or invalid.']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function dismissClaimNode(): void
    {
        try {
            $rawInput = file_get_contents('php://input') ?: '';
            $input = json_decode($rawInput, true) ?: $_POST;
            $nodeId = (int)($input['node_id'] ?? 0);
            if ($nodeId > 0) {
                $_SESSION['dismissed_claim_node_' . $nodeId] = true;
            }
            echo json_encode(['status' => 'success', 'message' => 'Prompt dismissed']);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export High-Resolution Scalable Vector Dynasty Poster (A2/A3 Printable)
     * Renders a royal, framed multi-generation genealogical tree in vector SVG.
     * @param string|array<string, mixed>|null $id
     * @return void
     */
    public function exportDynastyPoster($id = null): void
    {
        try {
            $rawId = is_string($id) ? checkInput($id) : ($_SESSION['id'] ?? '');
            $idStr = is_string($rawId) ? $rawId : (string)($_SESSION['id'] ?? '');

            if (empty($idStr)) {
                throw new NotFoundException('Member ID required');
            }

            $data = BaseController::findMemberById($idStr);
            $familyCode = (string)($data['famCode'] ?? ($_SESSION['famCode'] ?? ''));

            if (!BaseController::sessionSharesFamily($familyCode)) {
                throw new ForbiddenException('You can only export your own family dynasty poster.');
            }

            $this->syncLegacyFamilyToGraph($familyCode, $idStr, $data);
            $graphData = $this->buildSixGenGraphData($familyCode, $idStr);

            $familyName = htmlspecialchars((string)($data['lastName'] ?? 'Family'), ENT_QUOTES, 'UTF-8');
            $nodes = $graphData['nodes'] ?? [];

            // Group by generation level
            $genNames = [
                -2 => 'Grandparents & Ancestors',
                -1 => 'Parents, Uncles & Aunts',
                 0 => 'Core Household & Siblings',
                 1 => 'Children & Descendants',
                 2 => 'Grandchildren',
            ];
            $genBuckets = array_fill_keys(array_keys($genNames), []);

            foreach ($nodes as $node) {
                $lvl = (int)($node['generation_level'] ?? 0);
                if (isset($genBuckets[$lvl])) {
                    $genBuckets[$lvl][] = $node;
                } else {
                    $genBuckets[0][] = $node;
                }
            }

            // Track poster export analytics
            \App\services\AnalyticsService::track($idStr, 'dynasty_poster_exported', null, [
                'family_code' => $familyCode,
                'total_nodes' => count($nodes)
            ]);

            // Vector Canvas Dimensions (300dpi standard 2400 x 3400)
            $W = 2400;
            $H = 3400;

            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="' . $familyName . '_Dynasty_Poster.svg"');

            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">';
            echo '<defs>';
            echo '<linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">';
            echo '<stop offset="0%" stop-color="#091e12"/>';
            echo '<stop offset="50%" stop-color="#143621"/>';
            echo '<stop offset="100%" stop-color="#0a1a0f"/>';
            echo '</linearGradient>';
            echo '<linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="0%">';
            echo '<stop offset="0%" stop-color="#bf953f"/>';
            echo '<stop offset="25%" stop-color="#fcf6ba"/>';
            echo '<stop offset="50%" stop-color="#b38728"/>';
            echo '<stop offset="75%" stop-color="#fbf5b7"/>';
            echo '<stop offset="100%" stop-color="#aa771c"/>';
            echo '</linearGradient>';
            echo '<filter id="goldGlow" x="-20%" y="-20%" width="140%" height="140%">';
            echo '<feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#d4af37" flood-opacity="0.35"/>';
            echo '</filter>';
            echo '</defs>';

            // Background & Borders
            echo '<rect width="' . $W . '" height="' . $H . '" fill="url(#bgGrad)"/>';
            echo '<rect x="60" y="60" width="' . ($W - 120) . '" height="' . ($H - 120) . '" fill="none" stroke="url(#goldGrad)" stroke-width="10"/>';
            echo '<rect x="80" y="80" width="' . ($W - 160) . '" height="' . ($H - 160) . '" fill="none" stroke="rgba(212, 175, 55, 0.4)" stroke-width="2"/>';

            // Ornate Header
            echo '<text x="' . ($W / 2) . '" y="240" font-family="Cinzel, Georgia, serif" font-size="82" font-weight="bold" fill="url(#goldGrad)" text-anchor="middle" letter-spacing="6">THE ' . strtoupper($familyName) . ' DYNASTY</text>';
            echo '<text x="' . ($W / 2) . '" y="310" font-family="sans-serif" font-size="34" font-weight="600" fill="rgba(255, 255, 255, 0.75)" text-anchor="middle" letter-spacing="4">OFFICIAL LINEAGE &amp; HERITAGE RECORD</text>';
            echo '<line x1="' . ($W / 2 - 300) . '" y1="360" x2="' . ($W / 2 + 300) . '" y2="360" stroke="url(#goldGrad)" stroke-width="4"/>';

            // Generations
            $currentY = 480;
            $activeGens = [];
            foreach ($genBuckets as $lvl => $bucket) {
                if (count($bucket) > 0) {
                    $activeGens[] = ['name' => $genNames[$lvl], 'nodes' => $bucket];
                }
            }
            $tierHeight = (int)(($H - 700) / max(count($activeGens), 1));

            foreach ($activeGens as $gen) {
                // Tier Header
                echo '<rect x="160" y="' . ($currentY - 40) . '" width="' . ($W - 320) . '" height="50" rx="25" fill="rgba(212, 175, 55, 0.12)" stroke="rgba(212, 175, 55, 0.3)" stroke-width="1"/>';
                echo '<text x="' . intdiv($W, 2) . '" y="' . ($currentY - 6) . '" font-family="sans-serif" font-size="24" font-weight="bold" fill="#d4af37" text-anchor="middle" letter-spacing="3">' . strtoupper($gen['name']) . '</text>';

                // Nodes in this generation
                $genNodes = $gen['nodes'];
                $nodeCount = count($genNodes);
                $nodeWidth = 320;
                $nodeHeight = 160;
                $totalWidth = $nodeCount * ($nodeWidth + 60);
                $startX = (int)(($W - $totalWidth) / 2);

                if ($startX < 120) {
                    $startX = 120;
                }

                $nx = $startX;
                $ny = $currentY + 50;

                foreach ($genNodes as $gn) {
                    $name = htmlspecialchars((string)($gn['full_name'] ?? 'Relative'), ENT_QUOTES, 'UTF-8');
                    $role = htmlspecialchars((string)($gn['bio'] ?? 'Family Member'), ENT_QUOTES, 'UTF-8');
                    $isDec = !empty($gn['is_deceased']);

                    echo '<g transform="translate(' . $nx . ', ' . $ny . ')">';
                    echo '<rect width="' . $nodeWidth . '" height="' . $nodeHeight . '" rx="20" fill="rgba(255, 255, 255, 0.08)" stroke="' . ($isDec ? '#94a3b8' : 'url(#goldGrad)') . '" stroke-width="3" filter="url(#goldGlow)"/>';
                    echo '<text x="' . intdiv($nodeWidth, 2) . '" y="60" font-family="sans-serif" font-size="24" font-weight="bold" fill="#ffffff" text-anchor="middle">' . $name . '</text>';
                    echo '<text x="' . intdiv($nodeWidth, 2) . '" y="95" font-family="sans-serif" font-size="16" font-weight="600" fill="#d4af37" text-anchor="middle">' . strtoupper($role) . '</text>';
                    if ($isDec) {
                        echo '<text x="' . intdiv($nodeWidth, 2) . '" y="130" font-family="sans-serif" font-size="14" font-style="italic" fill="#cbd5e1" text-anchor="middle">In Loving Memory</text>';
                    }
                    echo '</g>';

                    $nx += $nodeWidth + 60;
                    if ($nx + $nodeWidth > $W - 120) {
                        $nx = $startX;
                        $ny += $nodeHeight + 30;
                    }
                }

                $currentY += $tierHeight;
            }

            // Footer
            echo '<text x="' . ($W / 2) . '" y="' . ($H - 140) . '" font-family="sans-serif" font-size="22" fill="rgba(255, 255, 255, 0.6)" text-anchor="middle">Generated on ' . date('F j, Y') . ' • Total Dynasty Members: ' . count($nodes) . ' • Verified on FamilyPlatform</text>';
            echo '</svg>';
            exit;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Analyze family graph and pinpoint missing family nodes dynamically
     * @param array<string, mixed> $orgData
     * @param array<string, mixed> $graphData
     * @return array<string, mixed>
     */
    private function analyzeMissingNodes(array $orgData, array $graphData): array
    {
        $nodes = is_array($graphData['nodes'] ?? null) ? $graphData['nodes'] : [];

        $hasFather = !empty($orgData['father']['fullName']) || !empty($orgData['father']['name']) || !empty($orgData['father']['first_name']);
        $hasMother = !empty($orgData['mother']['fullName']) || !empty($orgData['mother']['name']) || !empty($orgData['mother']['first_name']);

        // Count nodes by generation level
        $grandparentsCount = 0; // level -2
        $parentsCount = 0;      // level -1
        $siblingsCount = is_array($orgData['siblings'] ?? null) ? count($orgData['siblings']) : 0;
        $childrenCount = is_array($orgData['children'] ?? null) ? count($orgData['children']) : 0;
        $grandchildrenCount = 0; // level 2
        $hasSpouse = !empty($orgData['spouse']['fullName']) || !empty($orgData['spouse']['name']) || !empty($orgData['spouse']['first_name']);

        foreach ($nodes as $n) {
            if (!is_array($n)) {
                continue;
            }
            $gen = (int)($n['generation_level'] ?? 0);
            if ($gen === -2) {
                $grandparentsCount++;
            } elseif ($gen === -1) {
                $parentsCount++;
            } elseif ($gen === 2) {
                $grandchildrenCount++;
            }
            if (!empty($n['user_id']) && isset($orgData['spouse']['user_id']) && $n['user_id'] === $orgData['spouse']['user_id']) {
                $hasSpouse = true;
            }
        }

        $hasParents = $hasFather || $hasMother || ($parentsCount > 0);

        $missingNodeTypes = [];
        $missingLabels = [];

        if (!$hasFather && !$hasMother) {
            $missingNodeTypes[] = 'parents';
            $missingLabels[] = 'Parents';
        } elseif (!$hasFather) {
            $missingNodeTypes[] = 'father';
            $missingLabels[] = 'Father';
        } elseif (!$hasMother) {
            $missingNodeTypes[] = 'mother';
            $missingLabels[] = 'Mother';
        }

        if ($grandparentsCount < 4) {
            $missingNodeTypes[] = 'grandparents';
            $missingLabels[] = 'Grandparents';
        }

        if ($siblingsCount === 0) {
            $missingNodeTypes[] = 'siblings';
            $missingLabels[] = 'Siblings';
        }

        if (!$hasSpouse) {
            $missingNodeTypes[] = 'spouse';
            $missingLabels[] = 'Spouse / Partner';
        }

        if ($childrenCount === 0) {
            $missingNodeTypes[] = 'children';
            $missingLabels[] = 'Children';
        } elseif ($grandchildrenCount === 0) {
            $missingNodeTypes[] = 'grandchildren';
            $missingLabels[] = 'Grandchildren';
        }

        // Determine primary priority & recommendations
        if (!$hasParents) {
            $primaryMissing = 'parents';
            $recommendedCtaType = 'parents';
            $title = 'Build your family tree!';
            $subtitle = 'Your tree looks a bit empty. Add your Father & Mother to anchor your lineage.';
        } elseif ($grandparentsCount < 2) {
            $primaryMissing = 'grandparents';
            $recommendedCtaType = 'parents';
            $title = 'Preserve your ancestral heritage!';
            $subtitle = 'Parents added! Now add your Paternal & Maternal Grandparents to unlock 3 generations.';
        } elseif ($siblingsCount === 0) {
            $primaryMissing = 'siblings';
            $recommendedCtaType = 'sibling';
            $title = 'Expand your immediate household!';
            $subtitle = 'Add your brothers and sisters to complete your immediate family branch.';
        } elseif (!$hasSpouse) {
            $primaryMissing = 'spouse';
            $recommendedCtaType = 'partner';
            $title = 'Add your Partner or Spouse!';
            $subtitle = 'Connect your spouse or partner to build your current household and family branch.';
        } elseif ($childrenCount === 0) {
            $primaryMissing = 'children';
            $recommendedCtaType = 'child';
            $title = 'Pass down your legacy!';
            $subtitle = 'Add your children to extend your lineage into future generations.';
        } elseif ($grandchildrenCount === 0) {
            $primaryMissing = 'grandchildren';
            $recommendedCtaType = 'child';
            $title = 'Extend your future dynasty!';
            $subtitle = 'Your children are in the tree. Add your grandchildren to complete 4+ generations.';
        } else {
            $primaryMissing = 'flourishing';
            $recommendedCtaType = 'general';
            $title = 'Your Family Dynasty is Flourishing!';
            $subtitle = 'You have connected key family nodes across generations. Keep inviting relatives and capturing memories!';
        }

        // Calculate completeness score (0 - 100%)
        $score = 0;
        if ($hasFather) $score += 20;
        if ($hasMother) $score += 20;
        if ($grandparentsCount >= 2) $score += 15;
        if ($grandparentsCount >= 4) $score += 15;
        if ($siblingsCount > 0) $score += 10;
        if ($hasSpouse) $score += 10;
        if ($childrenCount > 0) $score += 10;
        $completenessScore = min(100, $score);

        return [
            'completeness_score'   => $completenessScore,
            'primary_missing'      => $primaryMissing,
            'missing_types'        => $missingNodeTypes,
            'missing_labels'       => $missingLabels,
            'recommended_cta_type' => $recommendedCtaType,
            'banner_title'         => $title,
            'banner_subtitle'      => $subtitle,
            'is_flourishing'       => ($primaryMissing === 'flourishing')
        ];
    }
}

<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\Db;
use Src\LoginUtility;

final class OrganogramEditorController extends BaseController
{
    public function __construct()
    {
        // Enforce Authentication
        parent::__construct();
    }

    /**
     * Resolve the active family code (marital / primary household vs. birth / maiden family)
     * and map $baseNodeId or $targetNodeId across the signed-in user's held family trees.
     *
     * @param \PDO $db
     * @param int $baseNodeId
     * @param int $targetNodeId
     * @param array<string, mixed>|null $jsonInput
     * @return array{family_code: string, base_node_id: int, lineage_scope: string}
     */
    private function resolveEditorFamilyContext(\PDO $db, int $baseNodeId = 0, int $targetNodeId = 0, ?array $jsonInput = null): array
    {
        $userId = (string)($_SESSION['id'] ?? '');
        $primaryCode = strtoupper(trim((string)($_SESSION['famCode'] ?? '')));
        $lineageScope = trim((string)($_POST['lineage_scope'] ?? ($jsonInput['lineage_scope'] ?? '')));
        $requestedCode = strtoupper(trim(str_replace('#', '', (string)($_POST['lineage_code'] ?? ($_POST['family_code'] ?? ($jsonInput['lineage_code'] ?? ($jsonInput['family_code'] ?? '')))))));

        $familyCode = $primaryCode;
        $resolvedScope = 'primary';

        // 1. Explicit maternal / birth lineage request
        if (($lineageScope === 'maternal' || $lineageScope === 'birth')) {
            if ($requestedCode !== '' && $userId !== '' && (new \App\services\BirthFamilyCodeService($db))->userHoldsCode($userId, $requestedCode)) {
                $familyCode = $requestedCode;
                $resolvedScope = 'maternal';
            } elseif ($requestedCode === '' && $userId !== '') {
                $birthStatus = (new \App\services\BirthFamilyCodeService($db))->status($userId);
                if ($birthStatus['state'] === 'active' && !empty($birthStatus['code'])) {
                    $familyCode = strtoupper(trim((string)$birthStatus['code']));
                    $resolvedScope = 'maternal';
                }
            }
        } elseif (($lineageScope === 'wife' || $lineageScope === 'inlaw' || $lineageScope === 'spouse') && $requestedCode !== '') {
            $isAllowedWifeCode = false;
            if ($userId !== '') {
                $ofCheck = $db->prepare("SELECT 1 FROM otherFamily WHERE id = ? AND (otherFamCode = ? OR otherFamCode = ?) LIMIT 1");
                $ofCheck->execute([$userId, $requestedCode, '#' . $requestedCode]);
                $isAllowedWifeCode = (bool)$ofCheck->fetchColumn();

                if (!$isAllowedWifeCode) {
                    $ufCheck = $db->prepare("SELECT 1 FROM user_families WHERE user_id = ? AND family_code = ? AND status = 'approved' LIMIT 1");
                    $ufCheck->execute([$userId, $requestedCode]);
                    $isAllowedWifeCode = (bool)$ufCheck->fetchColumn();
                }

                if (!$isAllowedWifeCode) {
                    $stmtOwner = $db->prepare("SELECT created_by FROM family_codes WHERE code = ? LIMIT 1");
                    $stmtOwner->execute([$requestedCode]);
                    $codeCreator = $stmtOwner->fetchColumn();
                    if ($codeCreator === false || (string)$codeCreator === $userId) {
                        $isAllowedWifeCode = true;
                    }
                }
            }
            if ($isAllowedWifeCode) {
                $familyCode = $requestedCode;
                $resolvedScope = 'wife';
            }
        } elseif ($requestedCode !== '' && $requestedCode !== $primaryCode) {
            if ($userId !== '' && (new \App\services\BirthFamilyCodeService($db))->userHoldsCode($userId, $requestedCode)) {
                $familyCode = $requestedCode;
                $resolvedScope = 'maternal';
            } elseif (BaseController::sessionSharesFamily($requestedCode)) {
                $familyCode = $requestedCode;
                $resolvedScope = 'external';
            }
        }

        // 2. If operating on an existing target node (update, delete, fillPlaceholder, setGender, linkMember),
        // check which family tree that node actually lives in and verify session ownership.
        $checkId = $targetNodeId > 0 ? $targetNodeId : $baseNodeId;
        if ($checkId > 0) {
            $stmtNode = $db->prepare('SELECT id, user_id, family_code FROM family_nodes WHERE id = ? LIMIT 1');
            $stmtNode->execute([$checkId]);
            $nodeRow = $stmtNode->fetch(\PDO::FETCH_ASSOC);

            if ($nodeRow) {
                $nodeFamCode = strtoupper(trim((string)($nodeRow['family_code'] ?? '')));
                $nodeUserId = (string)($nodeRow['user_id'] ?? '');

                // Case A: User is adding a relative (e.g. Parents or Sibling) to their own root node
                // while explicitly routing to their Birth / Maiden family tree ($resolvedScope === 'maternal'),
                // or vice-versa when viewing one tree and routing to the other.
                if ($baseNodeId > 0
                    && $targetNodeId === 0
                    && $nodeFamCode !== ''
                    && $familyCode !== ''
                    && $nodeFamCode !== $familyCode
                    && $userId !== ''
                    && $nodeUserId === $userId
                ) {
                    $stmtTargetSelf = $db->prepare('SELECT id FROM family_nodes WHERE user_id = ? AND family_code = ? LIMIT 1');
                    $stmtTargetSelf->execute([$userId, $familyCode]);
                    $targetSelfId = (int)$stmtTargetSelf->fetchColumn();

                    if ($targetSelfId <= 0) {
                        $memberProfile = parent::findMemberById($userId);
                        if (!empty($memberProfile)) {
                            $targetSelfId = \App\services\FamilyClaimService::claimOrInitializeNode($familyCode, $userId, $memberProfile);
                        }
                    }
                    if ($targetSelfId > 0) {
                        $baseNodeId = $targetSelfId;
                    }
                }
                // Case B: No explicit cross-tree switch requested, or editing an existing relative node in the user's birth tree
                elseif ($nodeFamCode !== '' && $nodeFamCode !== $familyCode && BaseController::sessionSharesFamily($nodeFamCode)) {
                    $familyCode = $nodeFamCode;
                    $resolvedScope = ($nodeFamCode !== $primaryCode) ? 'maternal' : 'primary';
                }
            }
        }

        return [
            'family_code'   => $familyCode,
            'base_node_id'  => $baseNodeId,
            'lineage_scope' => $resolvedScope,
        ];
    }

    /**
     * Add a Partner/Spouse to an existing node
     */
    public function addPartner(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);
            if ($baseNodeId <= 0) {
                msgException(400, 'Invalid Base Node ID');
                return;
            }

            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // IDOR Protection: Verify the base node belongs to this user's family
            $stmt = $db->prepare("SELECT user_id, generation_level FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$baseNodeId, $familyCode]);
            $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$baseNode) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $genLevel = (int)$baseNode['generation_level'];

            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $mobile = trim((string)($_POST['mobile'] ?? ''));
            $gender = trim((string)($_POST['gender'] ?? 'Female'));
            $unionStatus = \App\services\FamilyGraphService::normaliseUnionType($_POST['union_status'] ?? '');
            $hasStatus = isset($_POST['union_status']) && $_POST['union_status'] !== '';
            $isCurrent = $hasStatus
                ? (in_array($unionStatus, ['married', 'partner'], true) ? 1 : 0)
                : ((isset($_POST['is_current']) && $_POST['is_current'] === 'yes') ? 1 : 0);
            $marriageYear = !empty($_POST['marriage_year']) ? (int)$_POST['marriage_year'] : null;
            $divorceYear = !empty($_POST['divorce_year']) ? (int)$_POST['divorce_year'] : null;

            if (empty($firstName)) {
                msgException(400, 'Partner\'s First Name is required.');
                return;
            }

            $sex = ($gender === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = "/resources/images/profile/{$sex}";

            // TRANSACTION START (David's Structural Mandate)
            $db->beginTransaction();

            try {
                // 1. Insert New Partner Node
                $insNode = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, maiden_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Partner/Spouse', ?, ?)
                ");
                $insNode->execute([
                    $familyCode, $firstName, $lastName,
                    ($pMaiden = trim((string)($_POST['maiden_name'] ?? ''))) !== '' ? $pMaiden : null,
                    $gender, $genLevel, $avatar,
                    !empty($email) ? $email : null,
                    !empty($mobile) ? $mobile : null
                ]);
                $partnerId = (int)$db->lastInsertId();

                // 2. Insert Union
                $unionType = $hasStatus ? $unionStatus : (($isCurrent === 1) ? 'married' : (($divorceYear) ? 'divorced' : 'separated'));
                
                $insUnion = $db->prepare("
                    INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_year, divorce_year, is_current)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insUnion->execute([$familyCode, $baseNodeId, $partnerId, $unionType, $marriageYear, $divorceYear, $isCurrent]);
                $newUnionId = (int)$db->lastInsertId();
                $partnerRequests = in_array($unionType, ['divorced', 'separated'], true)
                    ? \App\services\FamilyRelationshipRequestService::createFor($db, $familyCode, (string)($_SESSION['id'] ?? ''), $partnerId, \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, $newUnionId)
                    : [];

                $db->commit();
                if ($partnerRequests) {
                    \App\services\FamilyRelationshipRequestService::notify($db, $familyCode, (string)($_SESSION['id'] ?? ''), $this->requesterName(), $partnerId, \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, $partnerRequests);
                }
                msgSuccess(200, [
                    "message" => "Partner added successfully.",
                    "family_code" => $familyCode,
                    "lineage_scope" => $ctx['lineage_scope'],
                    "node" => [
                        "id" => $partnerId,
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "gender" => $gender,
                        "avatar_url" => $avatar,
                        "email" => $email,
                        "mobile" => $mobile,
                        "bio" => "Partner/Spouse",
                        "role" => "partner",
                        "union_id" => $newUnionId,
                        "union_status" => $unionType
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add a Child to a specific Union (or auto-resolve union from base node)
     */
    public function addChild(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $db = Db::connect2();

            $rawUnion = (string)($_POST['union_id'] ?? '');
            $unionId = ctype_digit($rawUnion) ? (int)$rawUnion : 0;
            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);
            $wantsNewPartner = ($rawUnion === 'new');

            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if ($unionId > 0) {
                $stmtU = $db->prepare('SELECT family_code FROM family_unions WHERE id = ? LIMIT 1');
                $stmtU->execute([$unionId]);
                $unionFamCode = strtoupper(trim((string)$stmtU->fetchColumn()));
                if ($unionFamCode !== '' && $unionFamCode !== $familyCode && BaseController::sessionSharesFamily($unionFamCode)) {
                    $familyCode = $unionFamCode;
                }
            }

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // "Someone else": create the partnership first (named, or an unnamed placeholder partner)
            if ($wantsNewPartner) {
                if ($baseNodeId <= 0) {
                    msgException(400, 'Invalid Base Node ID');
                    return;
                }
                $ownCheck = $db->prepare('SELECT id FROM family_nodes WHERE id = ? AND family_code = ?');
                $ownCheck->execute([$baseNodeId, $familyCode]);
                if (!$ownCheck->fetchColumn()) {
                    msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                    return;
                }
                $db->beginTransaction();
                try {
                    $newStatus = (string)($_POST['new_partner_status'] ?? 'partner');
                    $unionId = \App\services\FamilyGraphService::createPartnerUnion(
                        $db,
                        $familyCode,
                        $baseNodeId,
                        (string)($_POST['new_partner_name'] ?? ''),
                        $newStatus
                    );
                    $newPartnerRequests = [];
                    $newPartnerId = 0;
                    if ($unionId > 0 && in_array($newStatus, ['divorced', 'separated'], true)) {
                        $pu = $db->prepare('SELECT partner_1_id, partner_2_id FROM family_unions WHERE id = ?');
                        $pu->execute([$unionId]);
                        $puRow = $pu->fetch(\PDO::FETCH_ASSOC) ?: [];
                        $newPartnerId = (int)(($puRow['partner_1_id'] ?? 0) === $baseNodeId ? ($puRow['partner_2_id'] ?? 0) : ($puRow['partner_1_id'] ?? 0));
                        $newPartnerRequests = \App\services\FamilyRelationshipRequestService::createFor($db, $familyCode, (string)($_SESSION['id'] ?? ''), $newPartnerId, \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, $unionId);
                    }
                    $db->commit();
                    if ($newPartnerRequests) {
                        \App\services\FamilyRelationshipRequestService::notify($db, $familyCode, (string)($_SESSION['id'] ?? ''), $this->requesterName(), $newPartnerId, \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, $newPartnerRequests);
                    }
                } catch (\Throwable $e) {
                    $db->rollBack();
                    throw $e;
                }
            }

            // Auto-resolve or create parent union if missing
            if ($unionId <= 0 && $baseNodeId > 0) {
                $findU = $db->prepare("SELECT id FROM family_unions WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?) ORDER BY is_current DESC, id ASC LIMIT 1");
                $findU->execute([$familyCode, $baseNodeId, $baseNodeId]);
                $uRow = $findU->fetch(\PDO::FETCH_ASSOC);
                if ($uRow) {
                    $unionId = (int)$uRow['id'];
                } else {
                    // Create default spouse union
                    $stmtB = $db->prepare("SELECT gender, generation_level FROM family_nodes WHERE id = ? AND family_code = ?");
                    $stmtB->execute([$baseNodeId, $familyCode]);
                    $bNode = $stmtB->fetch(\PDO::FETCH_ASSOC);
                    if ($bNode) {
                        $gen = (int)$bNode['generation_level'];
                        $spGender = ($bNode['gender'] === 'Male') ? 'Female' : 'Male';
                        $spAvatar = ($spGender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';
                        $insSp = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Partner', ?, ?, ?, 'Partner/Spouse')");
                        $insSp->execute([$familyCode, $spGender, $gen, $spAvatar]);
                        $spId = (int)$db->lastInsertId();

                        $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                        $insU->execute([$familyCode, $baseNodeId, $spId]);
                        $unionId = (int)$db->lastInsertId();
                    }
                }
            }

            if ($unionId <= 0) {
                msgException(400, 'Invalid Union ID. Please select a parent partnership.');
                return;
            }

            // IDOR Protection: Verify the union belongs to this user's family
            $stmt = $db->prepare("
                SELECT u.id, n.generation_level 
                FROM family_unions u
                JOIN family_nodes n ON n.id = u.partner_1_id
                WHERE u.id = ? AND u.family_code = ?
            ");
            $stmt->execute([$unionId, $familyCode]);
            $union = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$union) {
                msgException(403, 'Permission Denied: Union does not belong to your family tree.');
                return;
            }

            $childGenLevel = (int)$union['generation_level'] + 1;

            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $mobile = trim((string)($_POST['mobile'] ?? ''));
            $gender = trim((string)($_POST['gender'] ?? 'Male'));

            if (empty($firstName)) {
                msgException(400, 'Child\'s First Name is required.');
                return;
            }

            $sex = ($gender === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = "/resources/images/profile/{$sex}";

            // TRANSACTION START
            $db->beginTransaction();

            try {
                // 1. Resolve or Create Child Node using KinshipEntityResolver
                $resolution = \App\services\KinshipEntityResolver::resolveOrCreateChild(
                    $familyCode,
                    $unionId,
                    $firstName,
                    $lastName,
                    $gender,
                    !empty($email) ? $email : null,
                    !empty($mobile) ? $mobile : null,
                    $avatar,
                    $childGenLevel
                );
                $childId = $resolution['node_id'];

                // Optional Gated Family Child Account Provisioning
                $childAccount = null;
                $enableAccess = ($_POST['enable_child_access'] ?? '') === '1';
                $childUsername = trim((string)($_POST['child_username'] ?? ''));
                $childPin = trim((string)($_POST['child_pin'] ?? ''));

                if ($enableAccess && !empty($childUsername) && !empty($childPin)) {
                    $childAccount = \App\services\ChildAccountService::provisionChildAccount(
                        parentUserId: (string)($_SESSION['id'] ?? ''),
                        childNodeId: (int)$childId,
                        username: $childUsername,
                        pin: $childPin,
                        dobYear: !empty($_POST['dob_year']) ? (string)$_POST['dob_year'] : null,
                        dobMonth: !empty($_POST['dob_month']) ? (string)$_POST['dob_month'] : null,
                        dobDay: !empty($_POST['dob_day']) ? (string)$_POST['dob_day'] : null,
                        clientIp: (string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
                    );
                }

                $db->commit();
                msgSuccess(200, [
                    "message" => "Child added successfully." . ($childAccount ? " Child account provisioned." : ""),
                    "family_code" => $familyCode,
                    "lineage_scope" => $ctx['lineage_scope'],
                    "child_account" => $childAccount,
                    "node" => [
                        "id" => $childId,
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "gender" => $gender,
                        "avatar_url" => $avatar,
                        "email" => $email,
                        "mobile" => $mobile,
                        "bio" => "Child",
                        "role" => "child"
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add Parents to a node (Creates Father, Mother, Union, and links child)
     */
    public function addParents(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);
            if ($baseNodeId <= 0) {
                msgException(400, 'Invalid Base Node ID');
                return;
            }

            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // IDOR Protection: Verify the base node belongs to this user's family
            $stmt = $db->prepare("SELECT user_id, generation_level FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$baseNodeId, $familyCode]);
            $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$baseNode) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $parentGenLevel = (int)$baseNode['generation_level'] - 1;

            // Grandparent mode: the base node is one of the user's parents; refuse to stack a second set of parents on it
            if (($_POST['rel_mode'] ?? '') === 'grandparents') {
                $chk = $db->prepare("SELECT 1 FROM family_node_children WHERE child_id = ? LIMIT 1");
                $chk->execute([$baseNodeId]);
                if ($chk->fetchColumn()) {
                    msgException(409, 'This parent already has parents recorded.');
                    return;
                }
            }

            $fatherFirst = trim((string)($_POST['father_first_name'] ?? ''));
            $fatherLast = trim((string)($_POST['father_last_name'] ?? ''));
            $fatherEmail = trim((string)($_POST['father_email'] ?? ''));
            $fatherMobile = trim((string)($_POST['father_mobile'] ?? ''));

            $motherFirst = trim((string)($_POST['mother_first_name'] ?? ''));
            $motherLast = trim((string)($_POST['mother_last_name'] ?? ''));
            $motherEmail = trim((string)($_POST['mother_email'] ?? ''));
            $motherMobile = trim((string)($_POST['mother_mobile'] ?? ''));

            if (empty($fatherFirst) && empty($fatherLast) && empty($motherFirst) && empty($motherLast)) {
                msgException(400, 'You must provide at least one parent\'s name.');
                return;
            }

            if (empty($fatherFirst) && empty($fatherLast)) {
                $fatherFirst = 'Unknown';
                $fatherLast = 'Father';
            }
            if (empty($motherFirst) && empty($motherLast)) {
                $motherFirst = 'Unknown';
                $motherLast = 'Mother';
            }

            // TRANSACTION START
            $db->beginTransaction();

            try {
                // 1. Insert Father
                $insFather = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Father', ?, ?)
                ");
                $insFather->execute([
                    $familyCode, $fatherFirst, $fatherLast, $parentGenLevel,
                    !empty($fatherEmail) ? $fatherEmail : null,
                    !empty($fatherMobile) ? $fatherMobile : null
                ]);
                $fatherId = (int)$db->lastInsertId();

                // 2. Insert Mother
                $insMother = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother', ?, ?)
                ");
                $insMother->execute([
                    $familyCode, $motherFirst, $motherLast, $parentGenLevel,
                    !empty($motherEmail) ? $motherEmail : null,
                    !empty($motherMobile) ? $motherMobile : null
                ]);
                $motherId = (int)$db->lastInsertId();

                // 3. Create Union
                $insUnion = $db->prepare("
                    INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                    VALUES (?, ?, ?, 'married', 1)
                ");
                $insUnion->execute([$familyCode, $fatherId, $motherId]);
                $unionId = (int)$db->lastInsertId();

                // 4. Link Base Node to Union as a Child
                $insChild = $db->prepare("
                    INSERT INTO family_node_children (union_id, child_id, relationship_type)
                    VALUES (?, ?, 'biological')
                ");
                $insChild->execute([$unionId, $baseNodeId]);

                $db->commit();
                msgSuccess(200, [
                    "message" => "Parents added successfully.",
                    "family_code" => $familyCode,
                    "lineage_scope" => $ctx['lineage_scope'],
                    "father" => [
                        "id" => $fatherId,
                        "first_name" => $fatherFirst,
                        "last_name" => $fatherLast,
                        "gender" => "Male",
                        "avatar_url" => "/resources/images/profile/avatarM.png",
                        "email" => $fatherEmail,
                        "mobile" => $fatherMobile,
                        "bio" => "Father",
                        "role" => "father"
                    ],
                    "mother" => [
                        "id" => $motherId,
                        "first_name" => $motherFirst,
                        "last_name" => $motherLast,
                        "gender" => "Female",
                        "avatar_url" => "/resources/images/profile/avatarF.png",
                        "email" => $motherEmail,
                        "mobile" => $motherMobile,
                        "bio" => "Mother",
                        "role" => "mother"
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add a Sibling to a node (Resolves parent union and links sibling)
     */
    public function addSibling(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);
            if ($baseNodeId <= 0) {
                msgException(400, 'Invalid Base Node ID');
                return;
            }

            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // Verify base node
            $stmt = $db->prepare("SELECT generation_level FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$baseNodeId, $familyCode]);
            $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$baseNode) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $genLevel = (int)$baseNode['generation_level'];

            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $mobile = trim((string)($_POST['mobile'] ?? ''));
            $gender = trim((string)($_POST['gender'] ?? 'Male'));

            if (empty($firstName)) {
                msgException(400, 'Sibling\'s First Name is required.');
                return;
            }

            $sex = ($gender === 'Male') ? 'avatarM.png' : 'avatarF.png';
            $avatar = "/resources/images/profile/{$sex}";

            // TRANSACTION START
            $db->beginTransaction();

            try {
                // Routes to the correct parent union: full / same mother only / same father only / step / adopted
                $sibCtx = null;
                $sibId = \App\services\FamilyGraphService::upsertSibling($db, $familyCode, $baseNodeId, [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'gender' => $gender,
                    'email' => $email,
                    'mobile' => $mobile,
                    'parentage' => $_POST['parentage'] ?? '',
                    'other_parent_name' => $_POST['other_parent_name'] ?? '',
                    'maiden_name' => $_POST['maiden_name'] ?? '',
                ], $sibCtx);
                $relKind = is_array($sibCtx) ? \App\services\FamilyRelationshipRequestService::kindForParentage((string)$sibCtx['parentage']) : null;
                $relRequests = ($relKind !== null && $sibId > 0)
                    ? \App\services\FamilyRelationshipRequestService::createFor($db, $familyCode, (string)($_SESSION['id'] ?? ''), $sibId, $relKind, (int)$sibCtx['union_id'], (int)$sibCtx['shared_parent_id'])
                    : [];
                if ($sibId <= 0) {
                    throw new \RuntimeException('Unable to create sibling.');
                }
                $parentage = \App\services\FamilyGraphService::normaliseParentage($_POST['parentage'] ?? '');

                $db->commit();
                if ($relKind !== null && $relRequests) {
                    \App\services\FamilyRelationshipRequestService::notify($db, $familyCode, (string)($_SESSION['id'] ?? ''), $this->requesterName(), $sibId, $relKind, $relRequests);
                }
                msgSuccess(200, [
                    "message" => "Sibling added successfully.",
                    "family_code" => $familyCode,
                    "lineage_scope" => $ctx['lineage_scope'],
                    "node" => [
                        "id" => $sibId,
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "gender" => $gender,
                        "avatar_url" => $avatar,
                        "email" => $email,
                        "mobile" => $mobile,
                        "bio" => "Sibling",
                        "role" => "sibling",
                        "parentage" => $parentage
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add an Auntie or Uncle to the family tree
     * Supports both Paternal (Marital FamCode) and Maternal (Birth FamCode) branches.
     */
    public function addUncleAunt(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $rawInput = file_get_contents('php://input') ?: '';
            $jsonInput = json_decode($rawInput, true) ?: [];

            $db = Db::connect2();
            $userId = (string)($_SESSION['id'] ?? '');

            $baseNodeId = (int)($_POST['base_node_id'] ?? ($jsonInput['base_node_id'] ?? 0));
            $relationship = strtolower(trim((string)($_POST['relationship'] ?? ($jsonInput['relationship'] ?? 'uncle'))));
            $side = strtolower(trim((string)($_POST['side'] ?? ($jsonInput['side'] ?? ''))));
            $famcodeType = strtolower(trim((string)($_POST['famcode_type'] ?? ($jsonInput['famcode_type'] ?? ''))));

            // Default side and lineage scope:
            $lineageScope = trim((string)($_POST['lineage_scope'] ?? ($jsonInput['lineage_scope'] ?? '')));
            if ($famcodeType === 'birth' || $lineageScope === 'birth' || $lineageScope === 'maternal') {
                if ($side === '') {
                    $side = 'maternal';
                }
                if ($lineageScope === '') {
                    $_POST['lineage_scope'] = 'maternal';
                }
            } else {
                if ($side === '') {
                    $side = 'paternal';
                }
            }

            // Auto-resolve lineage code if birth requested and empty
            $requestedCode = strtoupper(trim(str_replace('#', '', (string)($_POST['lineage_code'] ?? ($_POST['family_code'] ?? ($jsonInput['lineage_code'] ?? ($jsonInput['family_code'] ?? '')))))));
            $birthFamCode = strtoupper(trim(str_replace('#', '', (string)($_POST['birth_famcode'] ?? ($jsonInput['birth_famcode'] ?? '')))));
            $maritalFamCode = strtoupper(trim(str_replace('#', '', (string)($_POST['marital_famcode'] ?? ($jsonInput['marital_famcode'] ?? '')))));

            if (($famcodeType === 'birth' || $side === 'maternal' || $lineageScope === 'maternal' || $lineageScope === 'birth') && $requestedCode === '') {
                if ($birthFamCode !== '') {
                    $_POST['lineage_code'] = $birthFamCode;
                } else {
                    $birthStatus = (new \App\services\BirthFamilyCodeService($db))->status($userId);
                    if ($birthStatus['state'] === 'active' && !empty($birthStatus['code'])) {
                        $_POST['lineage_code'] = $birthStatus['code'];
                        $_POST['lineage_scope'] = 'maternal';
                    }
                }
            } elseif ($famcodeType === 'marital' && $requestedCode === '' && $maritalFamCode !== '') {
                $_POST['lineage_code'] = $maritalFamCode;
                $_POST['lineage_scope'] = 'primary';
            }

            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId, 0, $jsonInput);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // If baseNodeId is 0, auto-resolve to user's node in this family tree
            if ($baseNodeId <= 0 && $userId !== '') {
                $stmtSelf = $db->prepare('SELECT id FROM family_nodes WHERE user_id = ? AND family_code = ? LIMIT 1');
                $stmtSelf->execute([$userId, $familyCode]);
                $baseNodeId = (int)$stmtSelf->fetchColumn();

                if ($baseNodeId <= 0) {
                    $memberProfile = parent::findMemberById($userId);
                    if (!empty($memberProfile)) {
                        $baseNodeId = \App\services\FamilyClaimService::claimOrInitializeNode($familyCode, $userId, $memberProfile);
                    }
                }
            }

            if ($baseNodeId <= 0) {
                msgException(400, 'Invalid Base Node ID');
                return;
            }

            // IDOR Protection: Verify the base node belongs to this family tree
            $stmt = $db->prepare("SELECT id, user_id, first_name, last_name, generation_level FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$baseNodeId, $familyCode]);
            $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$baseNode) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $firstName = trim((string)($_POST['first_name'] ?? ($jsonInput['first_name'] ?? '')));
            $lastName = trim((string)($_POST['last_name'] ?? ($jsonInput['last_name'] ?? '')));
            $maidenName = trim((string)($_POST['maiden_name'] ?? ($jsonInput['maiden_name'] ?? '')));
            $email = trim((string)($_POST['email'] ?? ($jsonInput['email'] ?? '')));
            $mobile = trim((string)($_POST['mobile'] ?? ($jsonInput['mobile'] ?? '')));
            $gender = trim((string)($_POST['gender'] ?? ($jsonInput['gender'] ?? '')));

            $isFemale = in_array($relationship, ['aunt', 'auntie'], true) || strcasecmp($gender, 'Female') === 0;
            $gender = $isFemale ? 'Female' : 'Male';
            $bioRole = $isFemale ? 'Auntie' : 'Uncle';

            if (empty($firstName)) {
                msgException(400, "{$bioRole}'s First Name is required.");
                return;
            }

            if (empty($lastName)) {
                $lastName = (string)($baseNode['last_name'] ?? '');
            }

            $baseGenLevel = (int)$baseNode['generation_level'];
            $parentGenLevel = $baseGenLevel - 1;
            $avatar = ($gender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';

            // Atomic Transaction
            $db->beginTransaction();

            try {
                // 1. Identify or Create Target Parent (Father for Paternal, Mother for Maternal)
                $parentStmt = $db->prepare("
                    SELECT parent.id, parent.gender, parent.first_name, parent.last_name, fnc.union_id
                    FROM family_node_children fnc
                    JOIN family_unions u ON u.id = fnc.union_id
                    JOIN family_nodes parent ON (parent.id = u.partner_1_id OR parent.id = u.partner_2_id)
                    WHERE fnc.child_id = ? AND parent.family_code = ?
                ");
                $parentStmt->execute([$baseNodeId, $familyCode]);
                $existingParents = $parentStmt->fetchAll(\PDO::FETCH_ASSOC);

                $targetParentId = 0;
                $targetParentUnionId = 0;

                $wantedGender = ($side === 'paternal') ? 'Male' : 'Female';
                foreach ($existingParents as $pRow) {
                    if (strtolower((string)$pRow['gender']) === strtolower($wantedGender)) {
                        $targetParentId = (int)$pRow['id'];
                        $targetParentUnionId = (int)$pRow['union_id'];
                        break;
                    }
                }

                if ($targetParentId <= 0) {
                    $parentRoleName = ($side === 'paternal') ? 'Father' : 'Mother';
                    $parentSex = ($side === 'paternal') ? 'avatarM.png' : 'avatarF.png';
                    $parentSurname = ($side === 'maternal' && $maidenName !== '') ? $maidenName : $lastName;

                    $insParent = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, is_placeholder)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $insParent->execute([
                        $familyCode,
                        $parentRoleName,
                        $parentSurname,
                        $wantedGender,
                        $parentGenLevel,
                        "/resources/images/profile/{$parentSex}",
                        $parentRoleName
                    ]);
                    $targetParentId = (int)$db->lastInsertId();

                    $otherGender = ($wantedGender === 'Male') ? 'Female' : 'Male';
                    $otherRoleName = ($wantedGender === 'Male') ? 'Mother' : 'Father';
                    $otherSex = ($wantedGender === 'Male') ? 'avatarF.png' : 'avatarM.png';

                    $insOtherParent = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, is_placeholder)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $insOtherParent->execute([
                        $familyCode,
                        $otherRoleName,
                        $parentSurname,
                        $otherGender,
                        $parentGenLevel,
                        "/resources/images/profile/{$otherSex}",
                        $otherRoleName
                    ]);
                    $otherParentId = (int)$db->lastInsertId();

                    $insParentUnion = $db->prepare("
                        INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                        VALUES (?, ?, ?, 'married', 1)
                    ");
                    $partner1 = ($wantedGender === 'Male') ? $targetParentId : $otherParentId;
                    $partner2 = ($wantedGender === 'Male') ? $otherParentId : $targetParentId;
                    $insParentUnion->execute([$familyCode, $partner1, $partner2]);
                    $targetParentUnionId = (int)$db->lastInsertId();

                    $insChildLink = $db->prepare("
                        INSERT INTO family_node_children (union_id, child_id, relationship_type)
                        VALUES (?, ?, 'biological')
                    ");
                    $insChildLink->execute([$targetParentUnionId, $baseNodeId]);
                }

                // 2. Identify or Create Grandparent Union for the Target Parent
                $gpStmt = $db->prepare("
                    SELECT u.id AS gp_union_id, u.partner_1_id, u.partner_2_id
                    FROM family_node_children fnc
                    JOIN family_unions u ON u.id = fnc.union_id
                    WHERE fnc.child_id = ?
                    LIMIT 1
                ");
                $gpStmt->execute([$targetParentId]);
                $gpRow = $gpStmt->fetch(\PDO::FETCH_ASSOC);

                $gpUnionId = $gpRow ? (int)$gpRow['gp_union_id'] : 0;

                if ($gpUnionId <= 0) {
                    $gpGenLevel = $parentGenLevel - 1;
                    $gpSurname = ($side === 'maternal' && $maidenName !== '') ? $maidenName : $lastName;

                    $insGf = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, is_placeholder)
                        VALUES (?, 'Grandfather', ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Grandfather', 1)
                    ");
                    $insGf->execute([$familyCode, $gpSurname, $gpGenLevel]);
                    $gfId = (int)$db->lastInsertId();

                    $insGm = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, is_placeholder)
                        VALUES (?, 'Grandmother', ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Grandmother', 1)
                    ");
                    $insGm->execute([$familyCode, $gpSurname, $gpGenLevel]);
                    $gmId = (int)$db->lastInsertId();

                    $insGpUnion = $db->prepare("
                        INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                        VALUES (?, ?, ?, 'married', 1)
                    ");
                    $insGpUnion->execute([$familyCode, $gfId, $gmId]);
                    $gpUnionId = (int)$db->lastInsertId();

                    $insParentChild = $db->prepare("
                        INSERT INTO family_node_children (union_id, child_id, relationship_type)
                        VALUES (?, ?, 'biological')
                    ");
                    $insParentChild->execute([$gpUnionId, $targetParentId]);
                }

                // 3. Insert Uncle / Aunt Node
                $metaNotes = [];
                if (!empty($birthFamCode)) {
                    $metaNotes[] = "Birth FamCode: #{$birthFamCode}";
                }
                if (!empty($maritalFamCode)) {
                    $metaNotes[] = "Marital FamCode: #{$maritalFamCode}";
                }
                $bioText = $bioRole . ($metaNotes ? ' (' . implode(', ', $metaNotes) . ')' : '');

                $birthYear = !empty($_POST['birth_year']) ? (int)$_POST['birth_year'] : (!empty($jsonInput['birth_year']) ? (int)$jsonInput['birth_year'] : null);

                $insNode = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, maiden_name, gender, generation_level, avatar_url, bio, email, mobile, birth_year)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insNode->execute([
                    $familyCode,
                    $firstName,
                    $lastName,
                    ($maidenName !== '') ? $maidenName : null,
                    $gender,
                    $parentGenLevel,
                    $avatar,
                    $bioText,
                    (!empty($email)) ? $email : null,
                    (!empty($mobile)) ? $mobile : null,
                    $birthYear
                ]);
                $uncleAuntId = (int)$db->lastInsertId();

                // 4. Link Uncle/Aunt into Grandparent Union as sibling of Target Parent
                $insChildLink = $db->prepare("
                    INSERT INTO family_node_children (union_id, child_id, relationship_type)
                    VALUES (?, ?, 'biological')
                ");
                $insChildLink->execute([$gpUnionId, $uncleAuntId]);

                $db->commit();

                $treeLabel = ($ctx['lineage_scope'] === 'maternal') ? 'Birth Family' : 'Marital Tree';

                msgSuccess(200, [
                    "message" => "{$bioRole} added successfully to your {$treeLabel} (#{$familyCode}).",
                    "node" => [
                        "id" => $uncleAuntId,
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "maiden_name" => $maidenName,
                        "gender" => $gender,
                        "avatar_url" => $avatar,
                        "email" => $email,
                        "mobile" => $mobile,
                        "bio" => $bioText,
                        "role" => strtolower($bioRole),
                        "side" => $side,
                        "generation_level" => $parentGenLevel,
                        "family_code" => $familyCode,
                        "lineage_scope" => $ctx['lineage_scope'],
                        "birth_famcode" => $birthFamCode,
                        "marital_famcode" => $maritalFamCode
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add an In-Law (Father-in-law, Mother-in-law, Brother-in-law, Sister-in-law)
     * Routed with the Wife's Family Code or Marital Family Code.
     */
    public function addInLaw(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $rawInput = file_get_contents('php://input') ?: '';
            $jsonInput = json_decode($rawInput, true) ?: [];

            $db = Db::connect2();
            $userId = (string)($_SESSION['id'] ?? '');

            $baseNodeId = (int)($_POST['base_node_id'] ?? ($jsonInput['base_node_id'] ?? 0));
            $relationship = strtolower(trim((string)($_POST['relationship'] ?? ($jsonInput['relationship'] ?? 'in_law'))));
            $famcodeType = strtolower(trim((string)($_POST['famcode_type'] ?? ($jsonInput['famcode_type'] ?? 'wife'))));
            $lineageScope = trim((string)($_POST['lineage_scope'] ?? ($jsonInput['lineage_scope'] ?? '')));

            $wifeFamCode = strtoupper(trim(str_replace('#', '', (string)($_POST['wife_famcode'] ?? ($_POST['spouse_famcode'] ?? ($jsonInput['wife_famcode'] ?? ($jsonInput['spouse_famcode'] ?? '')))))));
            $maritalFamCode = strtoupper(trim(str_replace('#', '', (string)($_POST['marital_famcode'] ?? ($jsonInput['marital_famcode'] ?? '')))));
            $requestedCode = strtoupper(trim(str_replace('#', '', (string)($_POST['lineage_code'] ?? ($_POST['family_code'] ?? ($jsonInput['lineage_code'] ?? ($jsonInput['family_code'] ?? '')))))));

            if ($wifeFamCode === '' && $requestedCode !== '') {
                $wifeFamCode = $requestedCode;
            }

            // Auto-detect wife/spouse family code from otherFamily if empty
            if ($wifeFamCode === '' && $userId !== '') {
                $stmtOf = $db->prepare('SELECT otherFamCode FROM otherFamily WHERE id = ? ORDER BY no ASC LIMIT 1');
                $stmtOf->execute([$userId]);
                $foundOf = $stmtOf->fetchColumn();
                if ($foundOf) {
                    $wifeFamCode = strtoupper(trim(str_replace('#', '', (string)$foundOf)));
                }
            }

            if ($famcodeType === 'wife' || $lineageScope === 'wife' || $lineageScope === 'inlaw') {
                if ($wifeFamCode !== '') {
                    // Security Gate: Validate user entitlement to wifeFamCode (BOLA / Privilege Escalation Guard)
                    $isAllowedWifeCode = false;
                    if ($userId !== '') {
                        $ofCheck = $db->prepare("SELECT 1 FROM otherFamily WHERE id = ? AND (otherFamCode = ? OR otherFamCode = ?) LIMIT 1");
                        $ofCheck->execute([$userId, $wifeFamCode, '#' . $wifeFamCode]);
                        $isAllowedWifeCode = (bool)$ofCheck->fetchColumn();

                        if (!$isAllowedWifeCode) {
                            $ufCheck = $db->prepare("SELECT 1 FROM user_families WHERE user_id = ? AND family_code = ? AND status = 'approved' LIMIT 1");
                            $ufCheck->execute([$userId, $wifeFamCode]);
                            $isAllowedWifeCode = (bool)$ufCheck->fetchColumn();
                        }

                        if (!$isAllowedWifeCode) {
                            $stmtOwner = $db->prepare("SELECT created_by FROM family_codes WHERE code = ? LIMIT 1");
                            $stmtOwner->execute([$wifeFamCode]);
                            $codeCreator = $stmtOwner->fetchColumn();
                            if ($codeCreator === false || (string)$codeCreator === $userId) {
                                $isAllowedWifeCode = true;
                            }
                        }
                    }

                    if (!$isAllowedWifeCode) {
                        msgException(403, "Permission Denied: You do not have authorization to link in-laws to foreign family code #{$wifeFamCode}.");
                        return;
                    }

                    $_POST['lineage_code'] = $wifeFamCode;
                    $_POST['lineage_scope'] = 'wife';
                    // Auto-associate user with wife family code in user_families if legitimate
                    $insUf = $db->prepare("INSERT IGNORE INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')");
                    $insUf->execute([$userId, $wifeFamCode]);
                }
            } elseif ($famcodeType === 'marital' && $maritalFamCode !== '') {
                $_POST['lineage_code'] = $maritalFamCode;
                $_POST['lineage_scope'] = 'primary';
            }

            $ctx = $this->resolveEditorFamilyContext($db, $baseNodeId, 0, $jsonInput);
            $familyCode = $ctx['family_code'];
            $baseNodeId = $ctx['base_node_id'];

            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // If baseNodeId is 0, auto-resolve to user's node in this family tree
            if ($baseNodeId <= 0 && $userId !== '') {
                $stmtSelf = $db->prepare('SELECT id FROM family_nodes WHERE user_id = ? AND family_code = ? LIMIT 1');
                $stmtSelf->execute([$userId, $familyCode]);
                $baseNodeId = (int)$stmtSelf->fetchColumn();

                if ($baseNodeId <= 0) {
                    $memberProfile = parent::findMemberById($userId);
                    if (!empty($memberProfile)) {
                        $baseNodeId = \App\services\FamilyClaimService::claimOrInitializeNode($familyCode, $userId, $memberProfile);
                    }
                }
            }

            if ($baseNodeId <= 0) {
                msgException(400, 'Invalid Base Node ID');
                return;
            }

            // IDOR Protection: Verify the base node belongs to this family tree
            $stmt = $db->prepare("SELECT id, user_id, first_name, last_name, generation_level, gender, maiden_name FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$baseNodeId, $familyCode]);
            $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$baseNode) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $firstName = trim((string)($_POST['first_name'] ?? ($jsonInput['first_name'] ?? '')));
            $lastName = trim((string)($_POST['last_name'] ?? ($jsonInput['last_name'] ?? '')));
            $maidenName = trim((string)($_POST['maiden_name'] ?? ($jsonInput['maiden_name'] ?? '')));
            $email = trim((string)($_POST['email'] ?? ($jsonInput['email'] ?? '')));
            $mobile = trim((string)($_POST['mobile'] ?? ($jsonInput['mobile'] ?? '')));
            $gender = trim((string)($_POST['gender'] ?? ($jsonInput['gender'] ?? '')));
            $birthYear = !empty($_POST['birth_year']) ? (int)$_POST['birth_year'] : (!empty($jsonInput['birth_year']) ? (int)$jsonInput['birth_year'] : null);

            // Normalize in-law relationship role
            $relNorm = strtolower(str_replace(['-', ' '], '_', $relationship));
            $isFatherInLaw = str_contains($relNorm, 'father');
            $isMotherInLaw = str_contains($relNorm, 'mother');
            $isBrotherInLaw = str_contains($relNorm, 'brother');
            $isSisterInLaw = str_contains($relNorm, 'sister');

            if ($isFatherInLaw) {
                $bioRole = 'Father-in-law';
                $gender = 'Male';
            } elseif ($isMotherInLaw) {
                $bioRole = 'Mother-in-law';
                $gender = 'Female';
            } elseif ($isBrotherInLaw) {
                $bioRole = 'Brother-in-law';
                $gender = 'Male';
            } elseif ($isSisterInLaw) {
                $bioRole = 'Sister-in-law';
                $gender = 'Female';
            } else {
                // Generic in-law fallback
                $isFemale = strcasecmp($gender, 'Female') === 0;
                $gender = $isFemale ? 'Female' : 'Male';
                $bioRole = $isFemale ? 'Sister-in-law' : 'Brother-in-law';
            }

            if (empty($firstName)) {
                msgException(400, "{$bioRole}'s First Name is required.");
                return;
            }

            $avatar = ($gender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';

            // Atomic Transaction
            $db->beginTransaction();

            try {
                // 1. Locate or Auto-Create the Spouse (Wife) node in this family tree
                $spouseNodeId = 0;
                $spouseGenLevel = (int)$baseNode['generation_level'];
                $spouseNode = [];

                // Check if baseNode itself is already the Wife / Spouse
                if (strtolower((string)($baseNode['gender'] ?? '')) === 'female' && (string)($baseNode['user_id'] ?? '') !== $userId) {
                    $spouseNodeId = (int)$baseNode['id'];
                    $spouseNode = $baseNode;
                } else {
                    // Find active spouse of baseNode
                    $stmtSpouse = $db->prepare("
                        SELECT u.id AS union_id,
                               CASE WHEN u.partner_1_id = ? THEN u.partner_2_id ELSE u.partner_1_id END AS spouse_id
                        FROM family_unions u
                        WHERE (u.partner_1_id = ? OR u.partner_2_id = ?) AND u.is_current = 1
                        LIMIT 1
                    ");
                    $stmtSpouse->execute([$baseNodeId, $baseNodeId, $baseNodeId]);
                    $spUnion = $stmtSpouse->fetch(\PDO::FETCH_ASSOC);

                    if ($spUnion && !empty($spUnion['spouse_id'])) {
                        $spouseNodeId = (int)$spUnion['spouse_id'];
                        $stmtSpNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
                        $stmtSpNode->execute([$spouseNodeId]);
                        $spouseNode = $stmtSpNode->fetch(\PDO::FETCH_ASSOC) ?: [];
                    } else {
                        // Auto-create Spouse (Wife) node for Ego
                        $spFirstName = 'Wife';
                        $spLastName = $wifeFamCode !== '' ? ucfirst(strtolower($wifeFamCode)) : ($baseNode['last_name'] ?? 'Spouse');
                        $spMaiden = '';

                        if ($userId !== '') {
                            $ofCheck = $db->prepare('SELECT spouse_name, maiden_name FROM otherFamily WHERE id = ? LIMIT 1');
                            $ofCheck->execute([$userId]);
                            $ofData = $ofCheck->fetch(\PDO::FETCH_ASSOC);
                            if ($ofData) {
                                if (!empty($ofData['spouse_name'])) {
                                    $spFirstName = trim((string)$ofData['spouse_name']);
                                }
                                if (!empty($ofData['maiden_name'])) {
                                    $spMaiden = trim((string)$ofData['maiden_name']);
                                }
                            }
                        }

                        $insSpouse = $db->prepare("
                            INSERT INTO family_nodes (family_code, first_name, last_name, maiden_name, gender, generation_level, avatar_url, bio, is_placeholder)
                            VALUES (?, ?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Spouse (Wife)', 1)
                        ");
                        $insSpouse->execute([
                            $familyCode, $spFirstName, $spLastName,
                            ($spMaiden !== '') ? $spMaiden : null,
                            $spouseGenLevel
                        ]);
                        $spouseNodeId = (int)$db->lastInsertId();

                        $insSpUnion = $db->prepare("
                            INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                            VALUES (?, ?, ?, 'married', 1)
                        ");
                        $insSpUnion->execute([$familyCode, $baseNodeId, $spouseNodeId]);

                        $stmtSpNode = $db->prepare('SELECT * FROM family_nodes WHERE id = ?');
                        $stmtSpNode->execute([$spouseNodeId]);
                        $spouseNode = $stmtSpNode->fetch(\PDO::FETCH_ASSOC) ?: [];
                    }
                }

                $spouseGenLevel = (int)($spouseNode['generation_level'] ?? $baseNode['generation_level']);
                $inlawParentGen = $spouseGenLevel - 1;

                // 2. Resolve or Create Spouse's Parent Union (Wife's Parents)
                $stmtInLawUnion = $db->prepare("
                    SELECT fnc.union_id, u.partner_1_id, u.partner_2_id
                    FROM family_node_children fnc
                    JOIN family_unions u ON u.id = fnc.union_id
                    WHERE fnc.child_id = ?
                    LIMIT 1
                ");
                $stmtInLawUnion->execute([$spouseNodeId]);
                $inLawUnionRow = $stmtInLawUnion->fetch(\PDO::FETCH_ASSOC);

                $inLawUnionId = 0;
                $fatherInLawId = 0;
                $motherInLawId = 0;

                $defaultInLawSurname = $wifeFamCode !== '' ? ucfirst(strtolower($wifeFamCode)) : (!empty($spouseNode['maiden_name']) ? $spouseNode['maiden_name'] : (!empty($spouseNode['last_name']) ? $spouseNode['last_name'] : 'In-Law'));

                if (empty($lastName)) {
                    $lastName = $defaultInLawSurname;
                }

                if ($inLawUnionRow && !empty($inLawUnionRow['union_id'])) {
                    $inLawUnionId = (int)$inLawUnionRow['union_id'];
                    $fatherInLawId = (int)($inLawUnionRow['partner_1_id'] ?? 0);
                    $motherInLawId = (int)($inLawUnionRow['partner_2_id'] ?? 0);
                } else {
                    // Create Parent Union for Spouse
                    $fName = $isFatherInLaw ? $firstName : 'Father-in-law';
                    $fSurname = $isFatherInLaw ? $lastName : $defaultInLawSurname;
                    $fIsPlaceholder = $isFatherInLaw ? 0 : 1;

                    $insF = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile, birth_year, is_placeholder)
                        VALUES (?, ?, ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Father-in-law', ?, ?, ?, ?)
                    ");
                    $insF->execute([
                        $familyCode, $fName, $fSurname, $inlawParentGen,
                        $isFatherInLaw && !empty($email) ? $email : null,
                        $isFatherInLaw && !empty($mobile) ? $mobile : null,
                        $isFatherInLaw && $birthYear ? $birthYear : null,
                        $fIsPlaceholder
                    ]);
                    $fatherInLawId = (int)$db->lastInsertId();

                    $mName = $isMotherInLaw ? $firstName : 'Mother-in-law';
                    $mSurname = $isMotherInLaw ? $lastName : $defaultInLawSurname;
                    $mMaiden = $isMotherInLaw && $maidenName !== '' ? $maidenName : null;
                    $mIsPlaceholder = $isMotherInLaw ? 0 : 1;

                    $insM = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, maiden_name, gender, generation_level, avatar_url, bio, email, mobile, birth_year, is_placeholder)
                        VALUES (?, ?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother-in-law', ?, ?, ?, ?)
                    ");
                    $insM->execute([
                        $familyCode, $mName, $mSurname, $mMaiden, $inlawParentGen,
                        $isMotherInLaw && !empty($email) ? $email : null,
                        $isMotherInLaw && !empty($mobile) ? $mobile : null,
                        $isMotherInLaw && $birthYear ? $birthYear : null,
                        $mIsPlaceholder
                    ]);
                    $motherInLawId = (int)$db->lastInsertId();

                    $insU = $db->prepare("
                        INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current)
                        VALUES (?, ?, ?, 'married', 1)
                    ");
                    $insU->execute([$familyCode, $fatherInLawId, $motherInLawId]);
                    $inLawUnionId = (int)$db->lastInsertId();

                    // Link Spouse as child of this union
                    $insChildLink = $db->prepare("
                        INSERT INTO family_node_children (union_id, child_id, relationship_type)
                        VALUES (?, ?, 'biological')
                    ");
                    $insChildLink->execute([$inLawUnionId, $spouseNodeId]);
                }

                // 3. Populate or Insert the In-Law Node
                $metaNotes = [];
                if (!empty($wifeFamCode)) {
                    $metaNotes[] = "Wife's FamCode: #{$wifeFamCode}";
                }
                $bioText = $bioRole . ($metaNotes ? ' (' . implode(', ', $metaNotes) . ')' : '');

                $finalNodeId = 0;
                $finalGenLevel = 0;

                if ($isFatherInLaw) {
                    $finalNodeId = $fatherInLawId;
                    $finalGenLevel = $inlawParentGen;

                    // Security Gate: Check if node is claimed by an active user
                    $chkClaim = $db->prepare("SELECT user_id FROM family_nodes WHERE id = ?");
                    $chkClaim->execute([$finalNodeId]);
                    $claimedUid = $chkClaim->fetchColumn();
                    if (!empty($claimedUid)) {
                        $db->rollBack();
                        msgException(403, "Permission Denied: Cannot modify details of a claimed family member's node.");
                        return;
                    }

                    $upd = $db->prepare("
                        UPDATE family_nodes 
                        SET first_name = ?, last_name = ?, bio = ?, email = COALESCE(?, email), mobile = COALESCE(?, mobile), birth_year = COALESCE(?, birth_year), is_placeholder = 0
                        WHERE id = ?
                    ");
                    $upd->execute([
                        $firstName, $lastName, $bioText,
                        !empty($email) ? $email : null,
                        !empty($mobile) ? $mobile : null,
                        $birthYear ?: null,
                        $finalNodeId
                    ]);
                } elseif ($isMotherInLaw) {
                    $finalNodeId = $motherInLawId;
                    $finalGenLevel = $inlawParentGen;

                    // Security Gate: Check if node is claimed by an active user
                    $chkClaim = $db->prepare("SELECT user_id FROM family_nodes WHERE id = ?");
                    $chkClaim->execute([$finalNodeId]);
                    $claimedUid = $chkClaim->fetchColumn();
                    if (!empty($claimedUid)) {
                        $db->rollBack();
                        msgException(403, "Permission Denied: Cannot modify details of a claimed family member's node.");
                        return;
                    }

                    $upd = $db->prepare("
                        UPDATE family_nodes 
                        SET first_name = ?, last_name = ?, maiden_name = COALESCE(?, maiden_name), bio = ?, email = COALESCE(?, email), mobile = COALESCE(?, mobile), birth_year = COALESCE(?, birth_year), is_placeholder = 0
                        WHERE id = ?
                    ");
                    $upd->execute([
                        $firstName, $lastName,
                        $maidenName !== '' ? $maidenName : null,
                        $bioText,
                        !empty($email) ? $email : null,
                        !empty($mobile) ? $mobile : null,
                        $birthYear ?: null,
                        $finalNodeId
                    ]);
                } else {
                    // Brother-in-law or Sister-in-law (Sibling of Spouse)
                    $finalGenLevel = $spouseGenLevel;
                    $insSib = $db->prepare("
                        INSERT INTO family_nodes (family_code, first_name, last_name, maiden_name, gender, generation_level, avatar_url, bio, email, mobile, birth_year)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $insSib->execute([
                        $familyCode, $firstName, $lastName,
                        ($maidenName !== '') ? $maidenName : null,
                        $gender, $finalGenLevel, $avatar, $bioText,
                        !empty($email) ? $email : null,
                        !empty($mobile) ? $mobile : null,
                        $birthYear ?: null
                    ]);
                    $finalNodeId = (int)$db->lastInsertId();

                    // Link as child of Spouse's parent union (so they are siblings of Spouse)
                    $insChildLink = $db->prepare("
                        INSERT INTO family_node_children (union_id, child_id, relationship_type)
                        VALUES (?, ?, 'biological')
                    ");
                    $insChildLink->execute([$inLawUnionId, $finalNodeId]);
                }

                $db->commit();

                $treeLabel = ($ctx['lineage_scope'] === 'wife') ? "Wife's Family Tree (#{$familyCode})" : "Marital Tree (#{$familyCode})";

                msgSuccess(200, [
                    "message" => "{$bioRole} added successfully to your {$treeLabel}.",
                    "node" => [
                        "id" => $finalNodeId,
                        "first_name" => $firstName,
                        "last_name" => $lastName,
                        "maiden_name" => $maidenName,
                        "gender" => $gender,
                        "avatar_url" => $avatar,
                        "email" => $email,
                        "mobile" => $mobile,
                        "bio" => $bioText,
                        "role" => strtolower(str_replace(['-', ' '], '_', $bioRole)),
                        "side" => 'in_law',
                        "generation_level" => $finalGenLevel,
                        "family_code" => $familyCode,
                        "lineage_scope" => $ctx['lineage_scope'],
                        "wife_famcode" => $wifeFamCode,
                        "marital_famcode" => $maritalFamCode
                    ]
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Update an existing family node's details
     */
    public function updateNode(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $rawInput = file_get_contents('php://input') ?: '';
            $jsonInput = json_decode($rawInput, true) ?: [];

            $nodeId = (int)($_POST['node_id'] ?? ($jsonInput['node_id'] ?? 0));
            if ($nodeId <= 0) {
                msgException(400, 'Invalid Node ID');
                return;
            }

            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, 0, $nodeId, $jsonInput);
            $familyCode = $ctx['family_code'];
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // Verify node belongs to this user's family
            $stmt = $db->prepare("SELECT id, user_id FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $firstName = trim((string)($_POST['first_name'] ?? ($jsonInput['first_name'] ?? '')));
            $lastName = trim((string)($_POST['last_name'] ?? ($jsonInput['last_name'] ?? '')));
            $maidenName = trim((string)($_POST['maiden_name'] ?? ($jsonInput['maiden_name'] ?? '')));
            $email = trim((string)($_POST['email'] ?? ($jsonInput['email'] ?? '')));
            $mobile = trim((string)($_POST['mobile'] ?? ($jsonInput['mobile'] ?? '')));
            $gender = trim((string)($_POST['gender'] ?? ($jsonInput['gender'] ?? '')));
            $location = trim((string)($_POST['location'] ?? ($jsonInput['location'] ?? '')));
            $occupation = trim((string)($_POST['occupation'] ?? ($jsonInput['occupation'] ?? '')));
            $bio = trim((string)($_POST['bio'] ?? ($jsonInput['bio'] ?? '')));
            $isDeceased = (isset($_POST['is_deceased']) && ($_POST['is_deceased'] === '1' || $_POST['is_deceased'] === 'yes'))
                || (isset($jsonInput['is_deceased']) && ($jsonInput['is_deceased'] === '1' || $jsonInput['is_deceased'] === 'yes' || $jsonInput['is_deceased'] === 1 || $jsonInput['is_deceased'] === true)) ? 1 : 0;
            $birthDate = !empty($_POST['birth_date']) ? trim((string)$_POST['birth_date']) : (!empty($jsonInput['birth_date']) ? trim((string)$jsonInput['birth_date']) : null);

            if (empty($firstName)) {
                msgException(400, 'First Name is required.');
                return;
            }

            // Update node
            $upd = $db->prepare("
                UPDATE family_nodes
                SET first_name = ?,
                    is_placeholder = 0,
                    last_name = ?,
                    maiden_name = ?,
                    gender = COALESCE(NULLIF(?, ''), gender),
                    email = ?,
                    mobile = ?,
                    location = ?,
                    occupation = ?,
                    bio = ?,
                    is_deceased = ?,
                    birth_date = ?
                WHERE id = ? AND family_code = ?
            ");
            $upd->execute([
                $firstName,
                $lastName,
                !empty($maidenName) ? $maidenName : null,
                $gender,
                !empty($email) ? $email : null,
                !empty($mobile) ? $mobile : null,
                !empty($location) ? $location : null,
                !empty($occupation) ? $occupation : null,
                !empty($bio) ? $bio : null,
                $isDeceased,
                $birthDate,
                $nodeId,
                $familyCode
            ]);

            msgSuccess(200, "Relative details updated successfully.");
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Set the gender of a node whose gender is unknown ('Other'/blank).
     * Never overrides an explicit Male/Female value, and is scoped to the session family (anti-IDOR).
     */
    public function setGender(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $jsonInput = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
            $nodeId = (int)($_POST['node_id'] ?? ($jsonInput['node_id'] ?? 0));
            $gender = \App\services\FamilyGraphService::normaliseGender((string)($_POST['gender'] ?? ($jsonInput['gender'] ?? '')), '');

            if ($nodeId <= 0 || !in_array($gender, ['Male', 'Female'], true)) {
                msgException(400, 'A valid node and gender (Male or Female) are required.');
                return;
            }

            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, 0, $nodeId, $jsonInput);
            $familyCode = $ctx['family_code'];
            if ($familyCode === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $stmt = $db->prepare("SELECT id, user_id, gender FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            if (\App\services\KinshipCalculatorService::hasKnownGender((string)($node['gender'] ?? ''))) {
                msgException(409, 'Gender is already set for this relative.');
                return;
            }

            $db->prepare("UPDATE family_nodes SET gender = ? WHERE id = ? AND family_code = ?")
                ->execute([$gender, $nodeId, $familyCode]);

            // Own node: keep the account profile in step (it takes precedence in the tree query)
            $sessionUserId = (string)($_SESSION['id'] ?? '');
            if ($sessionUserId !== '' && (string)($node['user_id'] ?? '') === $sessionUserId) {
                $db->prepare("UPDATE personal SET gender = ? WHERE id = ? AND (gender IS NULL OR gender = '')")
                    ->execute([$gender, $sessionUserId]);
            }

            msgSuccess(200, ['message' => 'Gender updated.', 'gender' => $gender]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Remove / Delete a relative from the family tree
     */
    public function deleteNode(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $rawInput = file_get_contents('php://input') ?: '';
            $jsonInput = json_decode($rawInput, true) ?: [];
            $nodeId = (int)($_POST['node_id'] ?? ($jsonInput['node_id'] ?? 0));

            if ($nodeId <= 0) {
                msgException(400, 'Invalid Node ID');
                return;
            }

            $sessionUserId = (string)($_SESSION['id'] ?? '');
            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, 0, $nodeId, $jsonInput);
            $familyCode = $ctx['family_code'];
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            // Verify node belongs to this user's family
            $stmt = $db->prepare("SELECT id, user_id, first_name, last_name, email FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            // Self-deletion Guard: User cannot delete their own root node
            if (!empty($sessionUserId) && !empty($node['user_id']) && (string)$node['user_id'] === $sessionUserId) {
                msgException(400, 'You cannot remove yourself from your family tree.');
                return;
            }

            // TRANSACTION START (David's Structural Mandate)
            $db->beginTransaction();

            try {
                // 1. Remove this node from child links (when this node is a child of any union)
                $delChildLinks = $db->prepare("DELETE FROM family_node_children WHERE child_id = ?");
                $delChildLinks->execute([$nodeId]);

                // 2. Find all unions where this node is partner 1 or partner 2
                $findUnions = $db->prepare("SELECT id FROM family_unions WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?)");
                $findUnions->execute([$familyCode, $nodeId, $nodeId]);
                $unions = $findUnions->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($unions)) {
                    $unionIds = array_column($unions, 'id');
                    $placeholders = implode(',', array_fill(0, count($unionIds), '?'));

                    // Remove child links belonging to these deleted unions
                    $delUnionChildren = $db->prepare("DELETE FROM family_node_children WHERE union_id IN ($placeholders)");
                    $delUnionChildren->execute($unionIds);

                    // Delete the unions
                    $delUnions = $db->prepare("DELETE FROM family_unions WHERE id IN ($placeholders)");
                    $delUnions->execute($unionIds);
                }

                // 3. Delete the node itself
                $delNode = $db->prepare("DELETE FROM family_nodes WHERE id = ? AND family_code = ?");
                $delNode->execute([$nodeId, $familyCode]);

                // 4. Synchronize & clean up legacy tables if applicable (sibling, children, otherFamily)
                if (!empty($sessionUserId)) {
                    $nodeEmail = trim((string)($node['email'] ?? ''));
                    $firstName = trim((string)($node['first_name'] ?? ''));
                    $lastName = trim((string)($node['last_name'] ?? ''));
                    $fullName = trim($firstName . ' ' . $lastName);

                    if (!empty($nodeEmail)) {
                        $db->prepare("DELETE FROM sibling WHERE id = ? AND sibling_email = ?")->execute([$sessionUserId, $nodeEmail]);
                        $db->prepare("DELETE FROM children WHERE id = ? AND children_email = ?")->execute([$sessionUserId, $nodeEmail]);
                        $db->prepare("DELETE FROM otherFamily WHERE id = ? AND (spouse_email = ? OR father_email = ? OR mother_email = ?)")->execute([$sessionUserId, $nodeEmail, $nodeEmail, $nodeEmail]);
                    }
                    if (!empty($fullName)) {
                        $db->prepare("DELETE FROM sibling WHERE id = ? AND sibling_name = ?")->execute([$sessionUserId, $fullName]);
                        $db->prepare("DELETE FROM children WHERE id = ? AND children_name = ?")->execute([$sessionUserId, $fullName]);
                        $db->prepare("DELETE FROM otherFamily WHERE id = ? AND (spouse_name = ? OR father_name = ? OR mother_name = ?)")->execute([$sessionUserId, $fullName, $fullName, $fullName]);
                    }
                }

                $db->commit();
                msgSuccess(200, [
                    "message" => "Relative removed from family tree successfully.",
                    "node_id" => $nodeId
                ]);
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Upload or Record a Living Voice Capsule for a family node
     * Supported by Damilola & James with Marcus SecOps Hardening
     */
    public function uploadVoiceCapsule(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $nodeId = (int)($_POST['node_id'] ?? 0);
            if ($nodeId <= 0) {
                msgException(400, 'Invalid Node ID');
                return;
            }

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

            // Verify node belongs to this user's family
            $stmt = $db->prepare("SELECT id, voice_capsule_url FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $uploadDir = dirname(__DIR__, 3) . '/public/resources/audio/voice_capsules';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $audioContent = null;
            $extension = 'webm';
            $allowedAudioMimeMap = [
                'audio/mpeg'               => 'mp3',
                'audio/mp3'                => 'mp3',
                'audio/ogg'                => 'ogg',
                'application/ogg'          => 'ogg',
                'audio/wav'                => 'wav',
                'audio/x-wav'              => 'wav',
                'audio/mp4'                => 'm4a',
                'video/mp4'                => 'm4a',
                'audio/x-m4a'              => 'm4a',
                'audio/webm'               => 'webm',
                'video/webm'               => 'webm',
                'application/octet-stream' => 'webm',
            ];

            // 1. Direct file upload via $_FILES
            if (!empty($_FILES['audio']['tmp_name'])) {
                $file = $_FILES['audio'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    msgException(400, 'File upload error: ' . $file['error']);
                    return;
                }
                if ($file['size'] > 10 * 1024 * 1024) {
                    msgException(400, 'Audio size exceeds 10MB limit.');
                    return;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo !== false ? finfo_file($finfo, (string)$file['tmp_name']) : false;
                if (!is_string($detectedMime) || !isset($allowedAudioMimeMap[$detectedMime])) {
                    msgException(422, 'Invalid audio file content. Only MP3, WAV, OGG, M4A, and WebM audio formats are permitted.');
                    return;
                }

                $extension = $allowedAudioMimeMap[$detectedMime];
                $audioContent = file_get_contents((string)$file['tmp_name']);
            }
            // 2. Base64 Audio Blob from In-Browser MediaRecorder
            elseif (!empty($_POST['audio_base64'])) {
                $rawBase64 = (string)$_POST['audio_base64'];
                if (str_contains($rawBase64, ',')) {
                    [, $data] = explode(',', $rawBase64, 2);
                    $audioContent = base64_decode($data, true);
                } else {
                    $audioContent = base64_decode($rawBase64, true);
                }

                if ($audioContent === false || strlen($audioContent) === 0) {
                    msgException(400, 'Invalid audio data provided.');
                    return;
                }
                if (strlen($audioContent) > 10 * 1024 * 1024) {
                    msgException(400, 'Audio size exceeds 10MB limit.');
                    return;
                }

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo !== false ? finfo_buffer($finfo, $audioContent) : false;
                if (is_string($detectedMime) && isset($allowedAudioMimeMap[$detectedMime])) {
                    $extension = $allowedAudioMimeMap[$detectedMime];
                } elseif (str_starts_with($audioContent, 'RIFF') || str_starts_with($audioContent, 'ID3') || str_starts_with($audioContent, 'OggS') || str_starts_with($audioContent, "\x1A\x45\xDF\xA3")) {
                    $extension = 'webm';
                } else {
                    msgException(422, 'Decoded payload is not a valid audio stream.');
                    return;
                }
            } else {
                msgException(400, 'No audio file or recording provided.');
                return;
            }

            // Generate cryptographically random filename using verified DB node ID
            $verifiedNodeId = (int)$node['id'];
            $filename = 'vc_' . $verifiedNodeId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $filePath = $uploadDir . '/' . basename($filename);

            // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename
            if (file_put_contents($filePath, $audioContent) === false) {
                msgException(500, 'Failed to save audio file to server.');
                return;
            }

            // Remove previous voice capsule file if exists
            if (!empty($node['voice_capsule_url'])) {
                $oldPath = dirname(__DIR__, 3) . '/public' . $node['voice_capsule_url'];
                if (is_file($oldPath)) {
                    // nosemgrep: php.lang.security.unlink-use.unlink-use -- Safe deletion of validated internal audio file path
                    @unlink($oldPath);
                }
            }

            $relativeUrl = '/resources/audio/voice_capsules/' . $filename;

            $upd = $db->prepare("UPDATE family_nodes SET voice_capsule_url = ? WHERE id = ? AND family_code = ?");
            $upd->execute([$relativeUrl, $nodeId, $familyCode]);

            msgSuccess(200, [
                'message' => 'Voice Capsule saved successfully.',
                'voice_capsule_url' => $relativeUrl,
                'node_id' => $nodeId
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Delete Voice Capsule for a family node
     */
    public function deleteVoiceCapsule(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $nodeId = (int)($_POST['node_id'] ?? 0);
            if ($nodeId <= 0) {
                msgException(400, 'Invalid Node ID');
                return;
            }

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

            $stmt = $db->prepare("SELECT id, voice_capsule_url FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            if (!empty($node['voice_capsule_url'])) {
                $oldPath = dirname(__DIR__, 3) . '/public' . $node['voice_capsule_url'];
                if (is_file($oldPath)) {
                    // nosemgrep: php.lang.security.unlink-use.unlink-use -- Safe deletion of validated internal audio file path
                    @unlink($oldPath);
                }
            }

            $upd = $db->prepare("UPDATE family_nodes SET voice_capsule_url = NULL WHERE id = ? AND family_code = ?");
            $upd->execute([$nodeId, $familyCode]);

            msgSuccess(200, [
                'message' => 'Voice Capsule deleted successfully.',
                'node_id' => $nodeId
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function requesterName(): string
    {
        $n = trim((string)($_SESSION['fName'] ?? '') . ' ' . (string)($_SESSION['lName'] ?? ''));
        return $n !== '' ? $n : 'A family member';
    }

    /**
     * Confirm or decline a pending relationship request addressed to the signed-in member.
     */
    public function resolveRelationshipRequest(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }
            $userId = (string)($_SESSION['id'] ?? '');
            $requestId = (int)($_POST['request_id'] ?? 0);
            $decision = (string)($_POST['decision'] ?? '');
            if ($userId === '' || $requestId <= 0 || !in_array($decision, ['approve', 'decline'], true)) {
                msgException(400, 'Invalid request.');
                return;
            }
            $res = \App\services\FamilyRelationshipRequestService::resolve(Db::connect2(), $requestId, $userId, $decision === 'approve');
            if (!$res['ok']) {
                msgException(404, $res['message']);
                return;
            }
            msgSuccess(200, ['message' => $res['message'], 'status' => $res['status']]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Click a "Father / Mother — tap to add" card: give the placeholder a real name.
     */
    public function fillPlaceholder(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }
            $nodeId = (int)($_POST['node_id'] ?? 0);
            $first = trim((string)($_POST['first_name'] ?? ''));
            if ($nodeId <= 0 || $first === '') {
                msgException(400, 'Please enter a first name.');
                return;
            }
            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, 0, $nodeId);
            $familyCode = $ctx['family_code'];
            if ($familyCode === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }
            $node = \App\services\FamilyGraphService::fillPlaceholder(
                $db,
                $familyCode,
                $nodeId,
                $first,
                (string)($_POST['last_name'] ?? ''),
                trim((string)($_POST['email'] ?? '')) ?: null,
                trim((string)($_POST['mobile'] ?? '')) ?: null
            );
            if ($node === null) {
                msgException(403, 'Permission Denied: this relative cannot be edited here.');
                return;
            }
            msgSuccess(200, ['message' => 'Saved.', 'node' => ['id' => (int)$node['id'], 'first_name' => $node['first_name'], 'last_name' => $node['last_name']]]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * POST: is the typed email / mobile already a registered member? (masked answer only)
     */
    public function lookupMember(): void
    {
        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db);
            $fam = $ctx['family_code'];
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $userId === '' || $fam === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }
            \Src\Limiter::limit($userId . ':member_lookup');
            $res = \App\services\MemberLookupService::lookup(
                $db,
                $userId,
                $fam,
                (string)($_POST['email'] ?? ''),
                (string)($_POST['mobile'] ?? '')
            );
            msgSuccess(200, $res);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * POST: after saving a relative, link the card to the registered member the lookup found.
     */
    public function linkMember(): void
    {
        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $nodeId = (int)($_POST['node_id'] ?? 0);
            $db = Db::connect2();
            $ctx = $this->resolveEditorFamilyContext($db, 0, $nodeId);
            $fam = $ctx['family_code'];
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $userId === '' || $fam === '' || $nodeId <= 0) {
                msgException(400, 'Invalid request.');
                return;
            }
            \Src\Limiter::limit($userId . ':member_link');
            $res = \App\services\MemberLookupService::link($db, $fam, $userId, $this->requesterName(), $nodeId, (string)($_POST['link_token'] ?? ''));
            if (!$res['ok']) {
                msgException(400, $res['message']);
                return;
            }
            msgSuccess(200, ['message' => $res['message']]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}


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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

            $rawUnion = (string)($_POST['union_id'] ?? '');
            $unionId = ctype_digit($rawUnion) ? (int)$rawUnion : 0;
            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);
            $wantsNewPartner = ($rawUnion === 'new');

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

                $db->commit();
                msgSuccess(200, [
                    "message" => "Child added successfully.",
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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if ($familyCode === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();
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

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            $sessionUserId = (string)($_SESSION['id'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

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

                $origName = (string)$file['name'];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $extension = match ($ext) {
                    'mp3' => 'mp3',
                    'ogg' => 'ogg',
                    'wav' => 'wav',
                    'm4a' => 'm4a',
                    'mp4' => 'mp4',
                    default => 'webm',
                };
                $audioContent = file_get_contents($file['tmp_name']);
            }
            // 2. Base64 Audio Blob from In-Browser MediaRecorder
            elseif (!empty($_POST['audio_base64'])) {
                $rawBase64 = (string)$_POST['audio_base64'];
                if (str_contains($rawBase64, ',')) {
                    [$header, $data] = explode(',', $rawBase64, 2);
                    if (str_contains($header, 'audio/mp3') || str_contains($header, 'audio/mpeg')) {
                        $extension = 'mp3';
                    } elseif (str_contains($header, 'audio/wav')) {
                        $extension = 'wav';
                    } elseif (str_contains($header, 'audio/ogg')) {
                        $extension = 'ogg';
                    } else {
                        $extension = 'webm';
                    }
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
            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if ($familyCode === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }
            $nodeId = (int)($_POST['node_id'] ?? 0);
            $first = trim((string)($_POST['first_name'] ?? ''));
            if ($nodeId <= 0 || $first === '') {
                msgException(400, 'Please enter a first name.');
                return;
            }
            $node = \App\services\FamilyGraphService::fillPlaceholder(
                Db::connect2(),
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
            $fam = (string)($_SESSION['famCode'] ?? '');
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $userId === '' || $fam === '') {
                msgException(403, 'Unauthorized Access');
                return;
            }
            \Src\Limiter::limit($userId . ':member_lookup');
            $res = \App\services\MemberLookupService::lookup(
                Db::connect2(),
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
            $fam = (string)($_SESSION['famCode'] ?? '');
            $nodeId = (int)($_POST['node_id'] ?? 0);
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $userId === '' || $fam === '' || $nodeId <= 0) {
                msgException(400, 'Invalid request.');
                return;
            }
            \Src\Limiter::limit($userId . ':member_link');
            $res = \App\services\MemberLookupService::link(Db::connect2(), $fam, $userId, $this->requesterName(), $nodeId, (string)($_POST['link_token'] ?? ''));
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


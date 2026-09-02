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
            $isCurrent = (isset($_POST['is_current']) && $_POST['is_current'] === 'yes') ? 1 : 0;
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
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, ?, ?, ?, 'Partner/Spouse', ?, ?)
                ");
                $insNode->execute([
                    $familyCode, $firstName, $lastName, $gender, $genLevel, $avatar,
                    !empty($email) ? $email : null,
                    !empty($mobile) ? $mobile : null
                ]);
                $partnerId = (int)$db->lastInsertId();

                // 2. Insert Union
                $unionType = ($isCurrent === 1) ? 'married' : (($divorceYear) ? 'divorced' : 'separated');
                
                $insUnion = $db->prepare("
                    INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_year, divorce_year, is_current)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insUnion->execute([$familyCode, $baseNodeId, $partnerId, $unionType, $marriageYear, $divorceYear, $isCurrent]);

                $db->commit();
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
                        "role" => "partner"
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

            $unionId = (int)($_POST['union_id'] ?? 0);
            $baseNodeId = (int)($_POST['base_node_id'] ?? 0);

            // Auto-resolve or create parent union if missing
            if ($unionId <= 0 && $baseNodeId > 0) {
                $findU = $db->prepare("SELECT id FROM family_unions WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?) LIMIT 1");
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
                // 1. Insert New Child Node
                $insNode = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, ?, ?, ?, 'Child', ?, ?)
                ");
                $insNode->execute([
                    $familyCode, $firstName, $lastName, $gender, $childGenLevel, $avatar,
                    !empty($email) ? $email : null,
                    !empty($mobile) ? $mobile : null
                ]);
                $childId = (int)$db->lastInsertId();

                // 2. Link Child to Union
                $insChild = $db->prepare("
                    INSERT INTO family_node_children (union_id, child_id, relationship_type)
                    VALUES (?, ?, 'biological')
                ");
                $insChild->execute([$unionId, $childId]);

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
                // Find or create parent union
                $chkP = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ? LIMIT 1");
                $chkP->execute([$baseNodeId]);
                $pRow = $chkP->fetch(\PDO::FETCH_ASSOC);
                $pUnionId = $pRow ? (int)$pRow['union_id'] : 0;

                if ($pUnionId <= 0) {
                    // Create default parent union
                    $insF = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Father', 'Male', ?, '/resources/images/profile/avatarM.png', 'Father')");
                    $insF->execute([$familyCode, $genLevel - 1]);
                    $fId = (int)$db->lastInsertId();

                    $insM = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Mother', 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother')");
                    $insM->execute([$familyCode, $genLevel - 1]);
                    $mId = (int)$db->lastInsertId();

                    $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                    $insU->execute([$familyCode, $fId, $mId]);
                    $pUnionId = (int)$db->lastInsertId();

                    $insLinkSelf = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                    $insLinkSelf->execute([$pUnionId, $baseNodeId]);
                }

                // 1. Insert Sibling Node
                $insSib = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, ?, ?, ?, 'Sibling', ?, ?)
                ");
                $insSib->execute([
                    $familyCode, $firstName, $lastName, $gender, $genLevel, $avatar,
                    !empty($email) ? $email : null,
                    !empty($mobile) ? $mobile : null
                ]);
                $sibId = (int)$db->lastInsertId();

                // 2. Link Sibling to Parent Union
                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                $insLink->execute([$pUnionId, $sibId]);

                $db->commit();
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
                        "role" => "sibling"
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
            $stmt = $db->prepare("SELECT id, user_id FROM family_nodes WHERE id = ? AND family_code = ?");
            $stmt->execute([$nodeId, $familyCode]);
            $node = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$node) {
                msgException(403, 'Permission Denied: Node does not belong to your family tree.');
                return;
            }

            $firstName = trim((string)($_POST['first_name'] ?? ''));
            $lastName = trim((string)($_POST['last_name'] ?? ''));
            $maidenName = trim((string)($_POST['maiden_name'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $mobile = trim((string)($_POST['mobile'] ?? ''));
            $gender = trim((string)($_POST['gender'] ?? ''));
            $location = trim((string)($_POST['location'] ?? ''));
            $occupation = trim((string)($_POST['occupation'] ?? ''));
            $bio = trim((string)($_POST['bio'] ?? ''));
            $isDeceased = (isset($_POST['is_deceased']) && ($_POST['is_deceased'] === '1' || $_POST['is_deceased'] === 'yes')) ? 1 : 0;
            $birthDate = !empty($_POST['birth_date']) ? trim((string)$_POST['birth_date']) : null;

            if (empty($firstName)) {
                msgException(400, 'First Name is required.');
                return;
            }

            // Update node
            $upd = $db->prepare("
                UPDATE family_nodes
                SET first_name = ?,
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
}

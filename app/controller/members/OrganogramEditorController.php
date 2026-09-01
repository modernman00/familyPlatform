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
            $nodeUserId = $baseNode['user_id'] ?? null;

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
                $insNode->execute([$familyCode, $firstName, $lastName, $gender, $genLevel, $avatar, $email, $mobile]);
                $partnerId = (int)$db->lastInsertId();

                // 2. Insert Union
                $unionType = ($isCurrent === 1) ? 'married' : (($divorceYear) ? 'divorced' : 'separated');
                
                $insUnion = $db->prepare("
                    INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, marriage_year, divorce_year, is_current)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insUnion->execute([$familyCode, $baseNodeId, $partnerId, $unionType, $marriageYear, $divorceYear, $isCurrent]);

                $db->commit();
                msgSuccess(200, "Partner added successfully.");
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * Add a Child to a specific Union
     */
    public function addChild(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                msgException(405, 'Method Not Allowed');
                return;
            }

            $unionId = (int)($_POST['union_id'] ?? 0);
            if ($unionId <= 0) {
                msgException(400, 'Invalid Union ID. A child must belong to a specific partnership.');
                return;
            }

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode)) {
                msgException(403, 'Unauthorized Access');
                return;
            }

            $db = Db::connect2();

            // IDOR Protection: Verify the union belongs to this user's family
            // We also need the generation level of the parents to calculate the child's generation.
            $stmt = $db->prepare("
                SELECT u.id, n.generation_level, n.user_id as p1_user, n2.user_id as p2_user 
                FROM family_unions u
                JOIN family_nodes n ON n.id = u.partner_1_id
                JOIN family_nodes n2 ON n2.id = u.partner_2_id
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

            // TRANSACTION START (David's Structural Mandate)
            $db->beginTransaction();

            try {
                // 1. Insert New Child Node
                $insNode = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, ?, ?, ?, 'Child', ?, ?)
                ");
                $insNode->execute([$familyCode, $firstName, $lastName, $gender, $childGenLevel, $avatar, $email, $mobile]);
                $childId = (int)$db->lastInsertId();

                // 2. Link Child to Union
                $insChild = $db->prepare("
                    INSERT INTO family_node_children (union_id, child_id, relationship_type)
                    VALUES (?, ?, 'biological')
                ");
                $insChild->execute([$unionId, $childId]);

                $db->commit();
                msgSuccess(200, "Child added successfully.");
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

            // Fallbacks for missing parents to ensure union is created structurally sound
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
                $insFather->execute([$familyCode, $fatherFirst, $fatherLast, $parentGenLevel, $fatherEmail, $fatherMobile]);
                $fatherId = (int)$db->lastInsertId();

                // 2. Insert Mother
                $insMother = $db->prepare("
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile)
                    VALUES (?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother', ?, ?)
                ");
                $insMother->execute([$familyCode, $motherFirst, $motherLast, $parentGenLevel, $motherEmail, $motherMobile]);
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
                msgSuccess(200, "Parents added successfully.");
            } catch (\Exception $e) {
                $db->rollBack();
                throw $e;
            }

        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

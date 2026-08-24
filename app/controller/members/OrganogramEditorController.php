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
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
                    VALUES (?, ?, ?, ?, ?, ?, 'Partner/Spouse')
                ");
                $insNode->execute([$familyCode, $firstName, $lastName, $gender, $genLevel, $avatar]);
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
                    INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio)
                    VALUES (?, ?, ?, ?, ?, ?, 'Child')
                ");
                $insNode->execute([$familyCode, $firstName, $lastName, $gender, $childGenLevel, $avatar]);
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
}

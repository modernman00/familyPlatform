<?php
declare(strict_types=1);

namespace App\Controller\members;


use Src\{
    Select,
    Delete
};
use App\model\AllMembersData;
use App\controller\BaseController;
use Exception;
use Src\functionality\SignIn;

final class AllMembersController extends AllMembersData
{
    public function index(): void
    {
        try {

            view('member/showMembers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData(): void
    {
        try {
            $id = $_SESSION['id'] ?? null;
            if (!$id) {
                $tokenVerify = SignIn::verify();
                $id = $tokenVerify['id'] ?? null;
            }

            $result = $this->getAllMembers($id);

            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData2(): void
    {
        try {
            $result = $this->getAllMembersNoPics();
            echo json_encode(['status' => 'success', 'message' => $result]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    // /**
    //  * @return never
    //  */
    // public function setProfile($id)
    // {
    //     $id = checkInput($id);
    //     $_SESSION['id'] = $id;
    //     header("Location: /allMembers/getProfile");
    //     die();
    // }
    // /allMembers/setProfile?id
    public function getProfile($id): void
    {
        try {
            $sessId = $_SESSION['id'] ?? null;
            if (!$sessId) {
                SignIn::verify();
            }

            $id = checkInput($id);
            $data = BaseController::findMemberById($id);

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");
            $pictures = Select::selectFn2(query: $query, bind: [$id]) ?? [];

            // Fetch relatives for immediate family card
            $relativesWithImgs = [];
            $roles = [
                ['table' => 'otherFamily', 'who' => 'spouse', 'defaultRel' => 'Spouse'],
                ['table' => 'otherFamily', 'who' => 'father', 'defaultRel' => 'Father'],
                ['table' => 'otherFamily', 'who' => 'mother', 'defaultRel' => 'Mother'],
                ['table' => 'sibling', 'who' => 'sibling', 'defaultRel' => 'Sibling'],
                ['table' => 'children', 'who' => 'children', 'defaultRel' => 'Child'],
            ];

            foreach ($roles as $role) {
                $relations = BaseController::fetchRelationsData($id, $role['table'], $role['who']);
                if (!empty($relations) && is_array($relations)) {
                    foreach ($relations as $rel) {
                        if (!empty($rel['fullName'])) {
                            $rel['relationship'] = $rel['relationship'] ?? $role['defaultRel'];
                            $rel['img'] = !empty($rel['profilePics']) ? "/resources/images/profile/{$rel['profilePics']}" : ($rel['img'] ?? '/resources/images/profile/avatarM.png');
                            $relativesWithImgs[] = $rel;
                        }
                    }
                }
            }

            view('member/getProfile', compact('data', 'pictures', 'relativesWithImgs'));
        } catch (Exception $e) {
            showError($e);
        }
    }

    // /allMembers/removeProfile?removeProfile
    public function removeProfile($apr, $req): bool
    {
        try {
            //  verify token
            
SignIn::verify();

            // Sanitize inputs 
            $apr = checkInput($apr); 
            $req = checkInput($req);

            $query = Delete::formAndMatchQuery(
                selection: "DELETE_AND",
                table: 'requestMgt',
                identifier1: 'approver_id',
                identifier2: 'requester_id'
            );

            $deleteProfile = Delete::deleteFn(
                query: $query,
                bind: [$apr, $req,]
            );

            if ($deleteProfile > 0) {
                msgSuccess(200, "success");
                return true;
            } else {
                msgException(500, "Database update failed");
                return false;
            }
        } catch (Exception $e) {
            showError($e);
            return false;
        }
    }

    /**
     * GET /allMembers/search?q=...&limit=30&offset=0
     * Server-side search across family + wider directory.
     */
    public function search(): void
    {
        try {
            $payload = SignIn::verify('users');

            if (!$payload) {
                msgException(401, 'Unauthorized');
                return;
            }

            $requesterId = (int) cleanSession((string)($payload['id'] ?? ''));
            $famCode     = (string) cleanSession((string)($payload['famCode'] ?? ''));

            $term   = isset($_GET['q']) ? (string) $_GET['q'] : '';
            $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 30;
            $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

            $results = $this->searchMembers($requesterId, $famCode, $term, $limit, $offset);

            msgSuccess(200, $results);
        } catch (\Throwable $th) {
            showError($th);
            msgException(500, 'Unable to search members');
        }
    }
}


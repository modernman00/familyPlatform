<?php

declare(strict_types=1);

namespace App\Controller\members;

use Src\{
    Select,
    Delete,
    functionality\SignIn
};
use App\model\AllMembersData;
use Exception;
use App\Controller\BaseController as Base;

class AllMembersController extends AllMembersData
{
    public function index(): void
    {
        try {
            $data = Base::membersData();

            view('member/showMembers', \compact('data'));
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * GET /allMembers/processApiData
     * Returns your network: same famCode + approved relationships.
     */
    public function processApiData(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');


            if (!$VerifyJWT) {
                msgException(401, 'Unauthorized');
            }

            $id = (string) \cleanSession($VerifyJWT['id']);
            $famCode = (string) \cleanSession($VerifyJWT['famCode']);

            $members = $this->getAllMembers($famCode, $id);
    

            msgSuccess(200, $members, $famCode);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData2(): void
    {
        try {
            $result = $this->getAllMembersNoPics();
            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

     /**
     * GET /allMembers/allData
     * Returns every other user (outside your famCode).
     */
    public function getAllUsersDataFn(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            if (!$VerifyJWT) {
                msgException(401, 'Unauthorized');
                return;
            }
            $id = \cleanSession($VerifyJWT['id']);
            $famCode = \cleanSession($VerifyJWT['famCode']);
            $result = $this->getAllUsersData($id, $famCode);
            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
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

        $requesterId = (int) cleanSession($payload['id']);
        $famCode     = (string) cleanSession($payload['famCode']);

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




    /**
     * @return never
     */
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
            //  verify token
            $VerifyJWT = SignIn::verify('users');
            // if token is verified

            if (!$VerifyJWT) {
                \redirect($_ENV['401URL']);
            }
            $id = checkInput($id);
            // $result = $this->getAllMembersById($id);
            $data= Base::fullMemberData($id);
            $relativesWithImgs = Base::collectPeopleWithImages($data, $id);

         
            if (!$data) {
                throw new Exception("It could not process the data", 1);
            }

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");

            $pictures = Select::selectFn2(query: $query, bind: [$id]);

            view('member.getProfile', compact('data', 'pictures', 'relativesWithImgs'));
        } catch (Exception $e) {
            showError($e);
        }
    }

    // /allMembers/removeProfile?removeProfile
    public function removeProfile($apr, $req): bool
    {
        try {
            //  verify token
            $VerifyJWT = SignIn::verify('users');
            $result = $VerifyJWT;

            // if token is verified

            if (!$result) {
                $tokenErr = "Authentication failed";
                view('error/genError', ['error' => $tokenErr]);
                return false; // Ensure the function exits if authentication fails
            }

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
}

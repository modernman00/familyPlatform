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

class AllMembersController extends AllMembersData
{
    public function index(): void
    {
        try {

            // create id for the family tree link
            $data = [
                'id' => checkInput($_SESSION['id']),
                'fullName' => checkInput($_SESSION['fullName']),
            ];
         

            view('member/showMembers', \compact('data'));
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData(): void
    {
        try {
            $VerifyJWT = SignIn::verify('users');
            if ($VerifyJWT) {
                $id = \cleanSession($VerifyJWT['id']);
                $famCode = \cleanSession($VerifyJWT['famCode']);
                $result = $this->getAllMembers($famCode, $id);
                msgSuccess(200, $result);
            }
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

    public function getAllUsersDataFn(): void
    {
        try {
             $VerifyJWT = SignIn::verify('users');
            if ($VerifyJWT) {
                $id = \cleanSession($VerifyJWT['id']);
                $famCode = \cleanSession($VerifyJWT['famCode']);
            }
            $result = $this->getAllUsersData($id, $famCode);
            msgSuccess(200, $result);
        } catch (\Throwable $th) {
            showError($th);
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
            $VerifyJWT = SignIn::verify();
            // if token is verified

            if (!$VerifyJWT) {
                \redirect($_ENV['401URL']);
            }
            $id = checkInput($id);
            $result = $this->getAllMembersById($id);
            if (!$result) {
                throw new Exception("It could not process the data", 1);
            }

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");

            $pictures = Select::selectFn2(query: $query, bind: [$id]);

            $data = null;
            foreach ($result as $data);

            view('member.getProfile', compact('data', 'pictures'));
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

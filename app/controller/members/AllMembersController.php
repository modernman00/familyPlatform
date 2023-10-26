<?php

declare(strict_types=1);

namespace App\controller\members;


use App\model\AllMembersData;

use App\classes\{
    Select,
    VerifyToken
};
use Exception;

class AllMembersController extends AllMembersData
{
    public function index(): void
    {
        try {

             view('member/showMembers');
            // view('member/allMembers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData(): void
    {
        try {
            $id = $_GET['id'];
            $tokenVerify = new VerifyToken();

            $tokenConfirm = $tokenVerify->profilePage();

            if (!$tokenConfirm) {
                $tokenErr = "Authentication failed";
                view('error/genError', ['error' => $tokenErr]);
            }

            $result = $this->getAllMembers($id);
            echo json_encode($result);
 
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function processApiData2(): void
    {
        try {
            $result = $this->getAllMembersNoPics();
            echo json_encode($result);
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    /**
     * @return never
     */
    public function setProfile()
    {
        $id = checkInput($_GET['id']);
        $_SESSION['id'] = $id;
        header("Location: /allMembers/getProfile");
        die();
    }

    public function getProfile(): void
    {
        try {
            //  verify token
            $tokenVerify = new verifyToken();
            $result = $tokenVerify->profilePage();

            // if token is verified

            if (!$result) {
                $tokenErr = "Authentication failed";
                view('error/genError', ['error' => $tokenErr]);
            }

            $id = checkInput($_SESSION['id']);
            $result = $this->getAllMembersById($id);
            if (!$result) {
                throw new Exception("It could not process the data", 1);
            }

            $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");

            $pictures = Select::selectFn2(query: $query, bind: [$id]);

            $data = null;
            foreach ($result as $data);

            view('member/getProfile', compact('data', 'pictures'));
        } catch (Exception $e) {
            showError($e);
        }
    }
}

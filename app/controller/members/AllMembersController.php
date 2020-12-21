<?php

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};

use App\classes\{
    Sanitise,
    ProcessImg
};

class AllMembersController extends AllMembersData
{
    public function index()
    {
        try {
            $result = $this->getAllMembers();
            // printArr($result);
            view('member/allMembers', compact('result'));
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function setProfile()
    {
        $id = checkInput($_GET['id']);
        $_SESSION['id'] = $id;
        header("Location: /getProfile");
    }

    public function getProfile()
    {
        $id = checkInput($_SESSION['id']);
        $result = $this->joinManyCondition7('personal', 'interest', 'contact', 'otherFamily', 'post',  'profile_pics', 'images','id', $id);
        foreach($result as $data);
        printArr($data);
        view('member/profilePage', compact('data'));
    }
}

<?php

declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};

use App\classes\{
    Sanitise,
    ProcessImg
};
use Exception;
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

        if(!$result) {
             throw new Exception("It could not process the data", 1);
        }

        $pictures = $this->select_from('images', 'id', $id);
 
        foreach($result as $data);
       // foreach($pictures as $memberPics);
        view('member/profilePage', compact('data', 'pictures'));
    }
}

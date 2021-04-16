<?php

declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};

use App\classes\{
    Sanitise,
    ProcessImg, Select
};
use Exception;
class AllMembersController extends AllMembersData
{
    public function index()
    {
        try {
            $result = $this->getAllMembers();
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
        
        $result = $this->getAllMembersById($id);
       

        if(!$result) {
             throw new Exception("It could not process the data", 1);
        }

         $query = Select::formAndMatchQuery(selection: "SELECT_ONE", table: 'images', identifier1: "id");

         $pictures= Select::selectFn2(query: $query, bind: [$id]);

 
        foreach($result as $data);

        view('member/profile', compact('data', 'pictures'));
    }
}

<?php
declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};

class Organogram extends SingleCustomerData
{
    public function index()
    {
        $id =  checkInput($_GET['id']);
        $data = $this->getCustomerData($id);
        view('member/organogram', compact('data'));
    }
}
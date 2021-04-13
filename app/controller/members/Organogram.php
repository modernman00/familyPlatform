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

         $table = ['personal', 'profile_pics', 'contact', 'otherFamily'];

        $data = $this->getCustomerData($id, $table);
        
        view('member/organogram', compact('data'));
    }
}
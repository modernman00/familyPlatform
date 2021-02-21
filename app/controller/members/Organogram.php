<?php
declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};

// use App\classes\{
//     Sanitise,
//     ProcessImg
// };

class Organogram extends SingleCustomerData
{
    public function index()
    {
        $id =  checkInput($_GET['id']);
        $data = $this->getCustomerData($id);
       // printArr($data);
        view('member/organogram', compact('data'));
    }
}
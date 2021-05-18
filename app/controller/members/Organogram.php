<?php
declare(strict_types =1);

namespace App\controller\members;

use App\model\{
    AllMembersData,
    SingleCustomerData
};
use App\classes\Select;

class Organogram extends SingleCustomerData
{
    public function index()
    {
        $id =  checkInput($_GET['id']);

         $table = ['personal', 'profile_pics', 'otherFamily'];

         $siblingQuery = Select::formAndMatchQuery('SELECT_ONE', 'siblings', 'id');
         $getSibling = Select::selectFn2($siblingQuery, [$id]);

        $kidQuery = Select::formAndMatchQuery('SELECT_ONE', 'kids', 'id');
         $getKids = Select::selectFn2($kidQuery, [$id]);

        $data = $this->getCustomerData($id, $table);
        
        view('member/organogram', compact('data','getSibling', 'getKids'));
    }
}
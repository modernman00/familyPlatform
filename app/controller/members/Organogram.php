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


        // get the kids / spouse  of the siblings

        /**
         * 1. get the siblings from table siblings
         * 2. use siblings emails to get siblings id from contact
         * 3. use sibling id to get sibling othefamily table and kids from the kids table
         */

        $newSiblingData= [] ;

        $findSiblingIdQuery = Select::formAndMatchQuery('SELECT_ONE', 'contact', 'email');

        foreach ($getSibling as $value) {
            
            $findSiblingId = Select::selectFn2( $findSiblingIdQuery, [$value['sibling_email']]);

            if($findSiblingId){

                array_push($newSiblingData, $findSiblingId);

            }
        }
    //    printArr($newSiblingData[0]);
        
        view('member/organogram', compact('data','getSibling', 'getKids'));
    }
}
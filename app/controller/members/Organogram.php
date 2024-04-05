<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\SingleCustomerData;
use App\classes\{
    Select, 
    VerifyToken
    };


class Organogram extends SingleCustomerData
{
    public function index()
    {

          //  verify token

        $tokenVerify = new VerifyToken();
        $result = $tokenVerify->profilePage();
        $id =  checkInput($_GET['id']);
        $table = ['personal', 'profile_pics', 'otherFamily'];
        $siblingQuery = Select::formAndMatchQuery('SELECT_ONE', 'siblings', 'id');       

        // get the siblings and their email
        $getSiblings = Select::selectFn2($siblingQuery, [$id]) ?? [];

        $kidQuery = Select::formAndMatchQuery('SELECT_ONE', 'kids', 'id');
        $getKids = Select::selectFn2($kidQuery, [$id]) ?? [];
        
        $siblingKid =[];

        $data = $this->getCustomerData($id, $table);  

        // get the kids / spouse  of the siblings

        /**
         * 1. get the siblings from table siblings
         * 2. use siblings emails to get siblings id from contact
         * 3. use sibling id to get sibling othefamily table and kids from the kids table
         */



        if (!empty($getSiblings) || $getSiblings !== null) {

            $newSiblingData = []; // the siblings with their own data

            $findSiblingIdQuery = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'contact', identifier1: 'email');

            foreach ($getSiblings as $getSibling) {

                // get the siblings details : sibling id using the email 
                $findSiblingId = Select::selectFn2($findSiblingIdQuery, [$getSibling['sibling_email']]);


                unset($getSibling['id'], $getSibling['no']);



                if (!empty($findSiblingId)) {
                    array_push($newSiblingData, $findSiblingId[0]);
                }
            }



            // $newSiblingData - the siblings data showing the id and email

            // if the siblings are registered and their data is on the database then this will work



            if ($newSiblingData !== null && !empty($newSiblingData)) {

                foreach ($newSiblingData as $sib) {

                    $getKidsBySib = Select::selectFn2($kidQuery, [$sib['id']]);

                    $theKidCount = count($getKidsBySib);
                    $kidCount = ['kidCount' => $theKidCount];

                    $siblingKid = array_merge($sib, $getSiblings, $kidCount, $getKidsBySib) ?? [];

                    // foreach ($getKidsBySib as $sibKids) {
                    // }
                }
            } 
        }

        view('member/organogram', compact('data', 'getSiblings', 'getKids', 'siblingKid', 'newSiblingData'));
    }
}

<?php

declare(strict_types=1);

namespace App\controller\members;

use App\model\{
    // AllMembersData,
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

        // get the siblings and their email
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


        $newSiblingData = []; // the siblings with their own data

        $findSiblingIdQuery = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'contact', identifier1: 'email');

        foreach ($getSibling as $getSibling2) {

            // get the siblings details : sibling id using the email 
            $findSiblingId = Select::selectFn2($findSiblingIdQuery, [$getSibling2['sibling_email']]);

            unset($getSibling2['id'], $getSibling2['no']);

            if ($findSiblingId[0] != null) {
                array_push($newSiblingData, $findSiblingId[0]);
            }
        }

        // $newSiblingData - the siblings data showing the id and email

        foreach ($newSiblingData as $sib) {

            $getKidsBySib = Select::selectFn2($kidQuery, [$sib['id']]);

            $theKidCount = count($getKidsBySib);
            $kidCount = ['kidCount' => $theKidCount];

            $siblingKid = array_merge($sib, $getSibling2, $kidCount, $getKidsBySib);

            foreach ($getKidsBySib as $sibKids) {
            }
        }


        view('member/organogram', compact('data', 'getSibling', 'getKids', 'siblingKid'));
    }
}

<?php

namespace App\controller\login;

use App\classes\Insert;


class ShowAll extends Insert
{
    function show() {

        $allUser = $this->selectall('users'); 
        foreach($allUser as $outcome) {
                $allUserr = $outcome;
        } 
        
//var_dump($allUser);
     
    return view('alluser', $allUserr);

        // return view('alluser', compact($allUser));      
        
        // return view('customer/customer', compact($allUser));
    }
    
}
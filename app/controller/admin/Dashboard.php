<?php 

namespace App\controller\admin;
use App\classes\AllFunctionalities;


class Dashboard extends AllFunctionalities {

    function index ()
    {
        view ('admin/dashboard');
    }


}
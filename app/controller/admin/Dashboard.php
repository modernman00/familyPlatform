<?php 
declare(strict_types =1);

namespace App\controller\admin;
class Dashboard {

    function index ()
    {
        view ('admin/ReviewApps');
    }


}
<?php 

namespace App\controller;

use App\classes\tables\Register;

class Create 
{
    function index()
    {
        $table = new Register;
        $table->index();
    }

}
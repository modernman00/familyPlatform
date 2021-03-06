<?php

namespace App\controller;

use App\classes\AllFunctionalities;

/** @package App\controller */
class Base extends Allfunctionalities
{
    function index()
    {
        $bbc = "it is well";
        return $bbc;
    }

}
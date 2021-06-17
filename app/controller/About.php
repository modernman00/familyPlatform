<?php

namespace App\controller;
use App\classes\{
    Select
};

class About {

    private $var = "delta";

    public function index()
    {
       view('about');
    }

    function testing()
    {
        //    $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'post', identifier1: "post_no");

        //     $message = Select::selectFn2($query, [$_SESSION['LAST_INSERT_ID']]);
        //     echo $_SESSION['LAST_INSERT_ID'];

             printArr($_SERVER);
    }

    function testing2()
    {
        return view('learn');
    }
}

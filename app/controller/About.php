<?php

namespace App\controller;
use App\classes\{
    Select, InnerJoin
};

class About {

    private $var = "delta";

    public function index()
    {
       view('about');
    }

    function testing()
    {

        $data = InnerJoin::joinAll2('post', 'id', ['comment'], 'post.post_no');

        printArr($data);
        //    $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'post', identifier1: "post_no");

        //     $message = Select::selectFn2($query, [$_SESSION['LAST_INSERT_ID']]);
        //     echo $_SESSION['LAST_INSERT_ID'];

            //  printArr($_SERVER);
    }

    function testing2()
    {
        return view('learn');
    }
}

<?php

namespace App\controller;

class About {

    private $var = "delta";

    public function index()
    {
       view('about');
    }

    function testing()
    {
        echo "this testing worked";
    }

    function testing2()
    {
        return view('learn');
    }
}

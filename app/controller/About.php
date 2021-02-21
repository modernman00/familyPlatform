<?php

namespace App\controller;

class About {

    public function index()
    {
       view('about');
    }

    function testing()
    {
        echo "this testing worked";
    }
}
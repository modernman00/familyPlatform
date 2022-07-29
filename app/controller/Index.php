<?php

namespace App\controller;

class Index
{
    public function index()
    {
        view('index');
    }


    /**
     * the launch page
     * @return void 
     */
    function launch()
    {
        view('launch');
    }

    public function privacy()
    {
        view('privacy');
    }
}

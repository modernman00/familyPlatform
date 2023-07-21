<?php

namespace App\controller;

class Index
{
    public function index(): void
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

    public function privacy(): void
    {
        view('privacy');
    }

     public function terms(): void
    {
        view('termOfUse');
    }
}

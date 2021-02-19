<?php

namespace App\controller;

use App\controller\Base;

class Index extends Base
{
    function index()
    {
        view('index');
    }

     function boot()
    {
        view('boot');
    }

    function launch()
    {
        view('launch');
    }

}


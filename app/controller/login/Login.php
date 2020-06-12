<?php 

namespace App\controller\login;
use App\controller\Base;

class Login extends Base
{
    public function index()
    {
        view('login');
    }
}

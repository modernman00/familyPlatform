<?php

declare(strict_types=1);

namespace App\controller;
use Src\Utility;

class BaseController
{
    public static function viewWithCsp($view, $data = [])
    {
        Utility::view($view, $data);
    }
}

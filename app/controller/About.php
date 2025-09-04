<?php

namespace App\Controller;
use Src\Utility;


class About {

    //private $var = "delta";

    public function index(): void
    {
       view('about');
          try {
            BaseController::viewWithCsp('about');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


}

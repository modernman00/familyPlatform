<?php
namespace App\controller;
use Src\Utility;
use App\controller\BaseController;


final class About {

    //private $var = "delta";

    public function index(): void
    {
          try {
            BaseController::viewWithCsp('about');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


}

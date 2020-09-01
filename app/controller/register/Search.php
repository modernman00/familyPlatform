<?php

namespace App\controller\register;

use App\Controller\Base;

class Search extends Base
{
    public function index()
    {
        try {
            $hint = $_REQUEST['hint'];
            $attribute = $_GET['attribute'];
            $inputAttribute = $attribute."Email";
            $placeholder = "Please enter the email for $hint";
 
            $hint = strtolower($hint);
            $msg1 = "Good news! We think {$hint} has already registered on the platform";

            $msg2 = "We don't think $hint is on the platform yet so kindly enter the email address below <br> 
            <input type='email' placeholder ='$placeholder' class='form-control' name='$inputAttribute' id = '$inputAttribute'>";

            $outcome = $this->select_count('personal', $attribute, $hint);
            $error = ($outcome >= 1) ? $msg1 : $msg2 ;
            echo ($error);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

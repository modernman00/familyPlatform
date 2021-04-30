<?php

namespace App\controller\register;

use App\Controller\Base;
use App\classes\Select;

class Search extends Base
{
    public function index()
    {
        try {
            
            $hint =  checkInput($_REQUEST['hint']);  
            $attribute = checkInput($_GET['attribute']);
            $subject = checkInput($_GET['subject']);
            $d = 3;
            $ans = $d + 4;
            echo $ans;
            $hint = strtolower($hint);
            $msg1 = "Good news! your $subject is already registered on the platform";

            $msg2 = "<h4><i>Your $subject is not on the platform. Do you want us to send them a text to register to the platform</i>? </h4>". $this->checkBox($subject);

            $query = Select::formAndMatchQuery(selection:"SELECT_COUNT_ONE", table:'contact', identifier1: $attribute);
            $outcome = Select::selectFn2($query, [$hint]);

            // $outcome = $this->select_count('otherFamily', $attribute, $hint); 

            $result = (!$outcome) ? $msg2 : $msg1 ;
            echo $result;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function checkBox($subject)
    {
        return "<div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' id='{$subject}Yes' name = '{$subject}Checkbox' value=Yes'>
            <label class='form-check-label' for='{$subject}Yes'>Yes</label>
            </div>
            <div class='form-check form-check-inline'>
            <input class='form-check-input' type='radio' id='{$subject}No' name = '{$subject}Checkbox' value='No'>
            <label class='form-check-label' for='{$subject}No'>No</label>
            </div>
           ";
    }
}

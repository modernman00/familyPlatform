<?php

namespace App\controller;
use App\classes\Sanitise;

class Register extends Base 
{
    private $cleanData;

    public function processForm($array, $table, $data =null) 
    {
        $sanitise = new Sanitise($array, $data);
        $this->cleanData = $sanitise->getData();
        if($this->cleanData == "Error") {
            $_SESSION['error'] = $sanitise->getErr();
            header("location: /error") ;
        }
        $this->submitForm($table, $this->cleanData);
     
    }
}
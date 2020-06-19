<?php

namespace App\classes;
use Exception;

class CheckToken
{
    protected function checkToken($tokenName)
    {
      
        $sessionToken = $_SESSION[$tokenName];
        $postToken = $_POST[$tokenName];
    
         if($sessionToken != $postToken) {
                throw new Exception("Hmmm -> Are you sure you are not a Robot?", 1);      
            }
    }
}


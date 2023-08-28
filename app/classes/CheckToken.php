<?php

namespace App\classes;

class CheckToken
{

    /**
     * Undocumented function
     *
     * @param [type] $token  this token is the same for session and post
     *
     * @return void
     *
     * @psalm-param 'token' $token
     */
    public static function tokenCheck(string $token):void
    {
        // try {
            $tokenCheck = $_SESSION[$token] ?? "bad";
            $postToken = $_POST[$token] ?? "bad";
            // invalidate $token stored in session
            unset($_SESSION[$token]);
            if ($tokenCheck != $postToken) {

                msgException(401, "We are not familiar with the nature of your activities.");
              
      }
    }
}

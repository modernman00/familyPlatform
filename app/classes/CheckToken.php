<?php

namespace App\classes;


class CheckToken
{

    public  $tokenCheck;
    public  $postToken;


    function tokenCheck($token, $redirect)
    {
        try {
            $this->tokenCheck = $_SESSION[$token] ?? 1;
            $this->postToken = $_POST[$token] ?? 2;
            // invalidate $token stored in session
            unset($_SESSION[$token]);
            if ($this->tokenCheck != $this->postToken) {
                header("Location: $redirect");
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

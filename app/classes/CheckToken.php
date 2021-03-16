<?php

namespace App\classes;


class CheckToken
{

    public  $tokenCheck;
    public  $postToken;

    /**
     * Undocumented function
     *
     * @param [type] $token  this token is the same for session and post 
     * @param [type] $redirect if verification failed, it redirects here
     *
     * @return void
     */
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

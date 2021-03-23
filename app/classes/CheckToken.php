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
    static function tokenCheck($token, $redirect)
    {
        try {
            self::$tokenCheck = $_SESSION[$token] ?? 1;
            self::$postToken = $_POST[$token] ?? 2;
            // invalidate $token stored in session
            unset($_SESSION[$token]);
            if (self::$tokenCheck != self::$postToken) {
                header("Location: $redirect");
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

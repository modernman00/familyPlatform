<?php

namespace App\classes;


class CheckToken
{

    /**
     * Undocumented function
     *
     * @param [type] $token  this token is the same for session and post 
     * @param [type] $redirect if verification failed, it redirects here
     *
     * @return void
     */
    public static function tokenCheck($token, $redirect)
    {
        try {
            $tokenCheck = $_SESSION[$token] ?? "bad";
            $postToken = $_POST[$token] ?? "bad";
            // invalidate $token stored in session
            unset($_SESSION[$token]);
            if ($tokenCheck != $postToken) {
                header("Location: $redirect");
                exit();
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

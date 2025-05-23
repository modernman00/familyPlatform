<?php

namespace App\classes;

use App\exceptions\NotFoundException;

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
    public static function tokenCheck(string $token): void
    {
        // try {
        $tokenCheck = $_SESSION[$token] ?? "bad";
        $postToken = $_POST[$token] ?? "bad";
        $xsrfToken = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
        // invalidate $token stored in session
        unset($_SESSION[$token]);
        if ($tokenCheck != $postToken && $postToken != $xsrfToken) {

            throw new NotFoundException("We are not familiar with the nature of your activities.$postToken --  $xsrfToken");
        }
    }
}

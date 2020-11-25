<?php

namespace App\controller\login;

use Exception;

use App\classes\{
    CheckToken as token,
    allFunctionalities
};
use Throwable;

class PassChange extends allFunctionalities
{
    public $table = 'account';

    function show()
    {
        // unset unneeded sessions
        unset($_SESSION['changePW'], $_SESSION['loggedIn'], $_SESSION['memberId']);

        if (!isset($_SESSION['email'])) {
            throw new Exception("<h1>We cant find your credentials </h1> ", 1);
        }
        return view('login/passChange');
    }


    function verify()
    {
        try {
            new token('token', '/login/forgot');
            $reDirect = $_SESSION['loginType'];  // was set on the login page
            $cleanData = getSanitisedInputData($_POST, null);
            $email = checkInput($_SESSION['email']);
            $result = $this->update($this->table, 'password', $cleanData['password'], 'email', $email);
            if (!$result) {
                throw new Exception("Password cannot be updated");
            }
            // set the email data - path, subject etc
            $emailData = genEmailArray('msg/pwdChange', ['email' => $email], 'PASSWORD CHANGE', NULL, NULL);
            // send 
            sendEmailWrapper($emailData, 'member');
            session_regenerate_id();
            header("Location: $reDirect");
            unset($_SESSION['loginType']);
        } catch (\Throwable $e) {
            showError($e);
        }
    }
}

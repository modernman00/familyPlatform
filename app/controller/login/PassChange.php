<?php
declare(strict_types =1);

namespace App\controller\login;

use Exception;

use App\classes\{
    CheckToken, Pass
};
use Throwable;

class PassChange extends Pass
{
    public $table = 'account';

    function show()
    {
        // unset unneeded sessions
        unset($_SESSION['changePW'], $_SESSION['loggedIn'], $_SESSION['memberId']);

        $_SESSION['email'] ??= throw new Exception("<h1>We cant find your credentials </h1> ", 1);

        return view('login/passChange');
    }


    function verify()
    {
        try {
            //1.  token verified
            CheckToken::tokenCheck('token', '/login/changePW');

            $reDirect = $_SESSION['loginType'];  // was set on the login page
            // 2. sanitise Post Data
            $cleanData = getSanitisedInputData(inputData: $_POST);

            // 3. Check if email exists
            $email = checkInputEmail($_SESSION['email']);
            $result = $this->update($this->table, 'password', $cleanData['password'], 'email', $email);
            if (!$result) {
                throw new Exception("Password cannot be updated");
            }

            // 4. set the email data - path, subject etc
            $emailData = genEmailArray(viewPath: 'msg/pwdChange', data: ['email' => $email], subject: 'PASSWORD CHANGE');
            // 4.1 send 
            sendEmailWrapper(var :$emailData, recipientType:'member');

            session_regenerate_id();
            header("Location: $reDirect");
            unset($_SESSION['loginType']);

        } catch (Throwable $e) {
            showError($e);
        }
    }
}

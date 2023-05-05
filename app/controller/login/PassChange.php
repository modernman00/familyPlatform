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

        $_SESSION['email'] ??= throw new Exception("<h1>What are you doing here! </h1>", 1);

        return view('login/passChange');
    }


    function verify()
    {
        try {

            // 1. sanitise Post Data
            $cleanData = getSanitisedInputData(inputData: $_POST);

            // 2. Check if email exists
            $email = checkInputEmail($_SESSION['email']);

            //3.  token verified
            CheckToken::tokenCheck('token');

            $result = $this->update($this->table, 'password', $cleanData['password'], 'email', $email);
            if (!$result) {
                http_response_code(501);
                echo http_response_code();
                throw new Exception("Password cannot be updated");
            }

            // 4. set the email data - path, subject etc
            $emailData = genEmailArray(viewPath: 'msg/pwdChange', data: ['email' => $email], subject: 'PASSWORD CHANGE');
            // 4.1 send 
            sendEmailWrapper(var :$emailData, recipientType:'member');

            session_regenerate_id();
            unset($_SESSION['loginType']);
            http_response_code(200);
            echo http_response_code();
            echo json_encode("Password was successfully changed");


        } catch (Throwable $e) {
            showError($e);
        }
    }
}

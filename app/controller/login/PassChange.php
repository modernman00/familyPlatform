<?php

declare(strict_types=1);

namespace App\controller\login;

use Exception;
use App\classes\{CheckToken, Pass};
use Throwable;

class PassChange extends Pass
{
    public string $table = 'account';

    public function show(): void
    {
        unset($_SESSION['changePW'], $_SESSION['loggedIn'], $_SESSION['memberId']);

        $_SESSION['email'] ??= throw new Exception("<h1>What are you doing here! </h1>", 1);

        view('login/passChange');
    }

    public function verify(): void
    {
        try {
            $cleanData = getSanitisedInputData(inputData: $_POST);
            $email = checkInputEmail($_SESSION['email']);

            CheckToken::tokenCheck('token');

            $result = $this->update($this->table, 'password', $cleanData['password'], 'email', $email);
            if (!$result) {

                msgException(401, "Password cannot be updated");
            }

            $emailData = genEmailArray(
                viewPath: 'msg/pwdChange',
                data: ['email' => $email],
                subject: 'PASSWORD CHANGE'
            );

            sendEmailWrapper(var: $emailData, recipientType: 'member');

            session_regenerate_id();
            unset($_SESSION['loginType']);
            msgSuccess(200, "Password was successfully changed");
        } catch (Throwable $e) {
            showError($e);
        }
    }
}

<?php
declare(strict_types =1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select
};
use Exception;

class Code extends Select
{
    public $table = 'account';
    public $email;
    public $next;
    private $memberId;

    public function show()
    {

        return view('login/code');
    }

    public function verify()
    {
        try {
            CheckToken::tokenCheck('token', '/login/code');
            $code = checkInput($_POST["code"]);
            $this->memberId = checkInput($_SESSION['identifyCust']);

            // set time limit to use code
            if ((time() - $_SESSION['2FA_token_ts']) > 10000) {
                throw new Exception("Invalid or expired Token", 1);
            }
            unset($_SESSION['2FA_token_ts']);

            // check if the code is stored in the database
            $formCodeQuery = ['selection' => "SELECT_COUNT_TWO", 'table' => 'account', 'identifier1' => 'token', 'identifier2' => 'id', 'bind' => [$code, $this->memberId]];

            $result = Select::combineSelect($formCodeQuery, 'selectCountFn', 'TWO_IDENTIFIERS');

            if (!$result) {
                throw new Exception("CODE NOT RECOGNISED", 1);
            }
            // for normal login redirection
            if (isset($_SESSION['login'])) {

                $_SESSION['loggedIn'] = true;
                session_regenerate_id();
                $_SESSION['memberId'] = $this->memberId;

                if ($_SESSION['loginType'] = "/login") {

                    unset($_SESSION['login']);
                    header("Location: /member/ProfilePage");

                } else {

                    header("Location: /admin/dashboard");
                }
            } elseif ($_SESSION['changePW']) {

                // login if password is forgotten
                header('Location: /login/changePW');
                unset($_SESSION['changePW']);
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

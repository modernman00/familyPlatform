<?php

declare(strict_types=1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select, VerifyToken
};
use Exception;

class Code extends Select
{
    public $table = 'account';
    public $email;
    public $next;
    private $memberId;
    private const TOKEN_SESSION = '2FA_token_ts';
    private $errorArr = array();

    public function show()
    {
        if (!$_SESSION[self::TOKEN_SESSION]) {
            $login = "login";
            return view('error/notFound', compact('login'));
        }
        //  printArr($_SESSION);
        return view('login/code');
    }

    public function verify()
    {
        try {

           CheckToken::tokenCheck('token', '/login/code');

            $code = checkInput($_POST["code"]);

            if (!$_SESSION['identifyCust']) {
                $this->errorArr[] = "Hmm, we can't seem to find you - try again";
                msgException(401, "Hmm, we can't seem to find you - try again");
            }

            $this->memberId = checkInput($_SESSION['identifyCust']);

            // // set time limit to use code
            if ((time() - ($_SESSION[self::TOKEN_SESSION])) > 100000) {

                $this->errorArr[] = "Invalid or expired Token";
                msgException(401, "Invalid or expired Token");
            }
            unset($_SESSION[self::TOKEN_SESSION]); // job done! delete

            // check if the code is stored in the database

            $query = Select::formAndMatchQuery(selection: "SELECT_COUNT_TWO", table: 'account', identifier1: 'id', identifier2: 'token');

            $result = Select::selectCountFn2($query, [$this->memberId, $code]);

            if (!$result) {

                $this->errorArr[] = "There is a problem - Code";
                msgException(401, "There is a problem - check the Code");
                // throw new Exception(, 1);
            }

            if (count($this->errorArr) == 0) {

                // for normal login redirection
                if (isset($_SESSION['/loginType'])) {

                    $_SESSION['loggedIn'] = true;

                    $_SESSION['memberId'] = $this->memberId;

                    // if ($_SESSION['loginType'] = "/login") {
                        session_regenerate_id();
                        unset($_SESSION['login'], $_SESSION['id']);

                        // verify token  

                        $tokenVerify = new verifyToken();
                        $tokenVerify->index();

                    unset($_SESSION['changePW']);
                } 
                else {
                    throw new Exception("You are an alien!");
                }
            }
        } catch (\Throwable $th) {
            showError($th);

        }
    }
}

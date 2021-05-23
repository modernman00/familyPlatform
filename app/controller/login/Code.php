<?php

declare(strict_types=1);

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
    private const TOKEN_SESSION = '2FA_token_ts';
    private $errorArr = array();

    public function show()
    {
        if (!$_SESSION[self::TOKEN_SESSION]) {
            $login = "login";
            return view('error/notFound', compact('login'));
        }

        return view('login/code');
    }

    public function verify()
    {
        try {

            CheckToken::tokenCheck('token', '/login/code');

            $code = checkInput($_POST["code"]);

            if(!$_SESSION['identifyCust']){
                $this->errorArr[] = "Hmm, we can't seem to find you - try again";
                throw new Exception("Hmm, we can't seem to find you - try again", 1);
            }

            $this->memberId = checkInput($_SESSION['identifyCust']);

            // set time limit to use code
            if ((time() - ($_SESSION[self::TOKEN_SESSION])) > 10000) {

                $this->errorArr[] = "Invalid or expired Token";
                throw new Exception("Invalid or expired Token", 1);
            }
            unset($_SESSION[self::TOKEN_SESSION]); // job done! delete

            // check if the code is stored in the database

            $query = Select::formAndMatchQuery(selection: "SELECT_COUNT_TWO", table: 'account', identifier1: 'id', identifier2: 'token');

            $result = Select::selectCountFn2($query, [$this->memberId, $code]);

            if (!$result) {
                $this->errorArr[] = "There is a problem - Code";
                throw new Exception("There is a problem - check the Code", 1);
            }

            if (count($this->errorArr) == 0) {

                // for normal login redirection
                if (isset($_SESSION['login'])) {

                    $_SESSION['loggedIn'] = true;

                    $_SESSION['memberId'] = $this->memberId;

                    if ($_SESSION['loginType'] = "/login") {
                        session_regenerate_id();
                        unset($_SESSION['login'], $_SESSION['id']);
                        header("Location: /member/ProfilePage");
                    } else {

                        header("Location: /admin/dashboard");
                    }
                } elseif ($_SESSION['changePW']) {

                    // login if password is forgotten
                    header('Location: /login/changePW');
                    unset($_SESSION['changePW']);
                } else {
                    throw new Exception("You are an alien!");
                }
            }
        } catch (\Throwable $th) {
            $error = $th->getMessage();
            return view("error/genError", compact('error'));
        }
    }
}

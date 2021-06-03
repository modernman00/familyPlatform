<?php

declare(strict_types=1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select,
    jwtHandler
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
        return view('login/code');
    }

    public function verify()
    {
        try {

            $code = checkInput($_POST["code"]);

            if (!$_SESSION['identifyCust']) {
                $this->errorArr[] = "Hmm, we can't seem to find you - try again";
                msgException(401, "Hmm, we can't seem to find you - try again");
            }

            $this->memberId = checkInput($_SESSION['identifyCust']);

            if ((time() - ($_SESSION[self::TOKEN_SESSION])) > 1000) {
                $diff = time() - $_SESSION[self::TOKEN_SESSION];
                $this->errorArr[] = "Invalid or expired Token";
                msgException(401, "Invalid or expired Token $diff");
            }
            //unset($_SESSION[self::TOKEN_SESSION]); // job done! delete

            // check if the code is stored in the database

            CheckToken::tokenCheck('token', '/login/code');

            $query = Select::formAndMatchQuery(selection: "SELECT_COUNT_TWO", table: 'account', identifier1: 'id', identifier2: 'token');

            $result = Select::selectCountFn2($query, [$this->memberId, $code]);

            if (!$result) {

                $this->errorArr[] = "There is a problem - Code";
                msgException(401, "There is a problem - check the Code");
                // throw new Exception(, 1);
            }

            if (count($this->errorArr) == 0) {

                // for normal login redirection
                if (isset($_SESSION['login'])) {

                    $_SESSION['loggedIn'] = true;

                    $_SESSION['memberId'] = $this->memberId;

                    // generate a jwt token
                    $jwt = new JwtHandler();
                    $token = $jwt->jwtEncodeData(getenv('APP_URL'), ['id' => $this->memberId]);
                    // 86400 = 1 day

                    // only set a cookie if not already set

                    if (!isset($_COOKIE['waleToken'])) {
                        if (getenv("APP_ENV") === "local") {

                            setcookie("waleToken", $token, time() + getenv('COOKIE_EXPIRE'), '/', "", false, true);
                        } else {
                            setcookie("waleToken", $token, time() + getenv('COOKIE_EXPIRE'), "/", getenv('APP_URL'), true, true);
                        }
                    }

                    // if ($_SESSION['loginType'] = "/login") {
                    session_regenerate_id();
                    unset($_SESSION['login'], $_SESSION['id']);

                    msgSuccess(200, "Code authentication", $token);

                    unset($_SESSION['changePW']);

                } elseif ($_SESSION['changePW']) {

                    msgSuccess(200, "Code authentication");

                } else {

                    msgException(401, "You are an alien");
                }
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

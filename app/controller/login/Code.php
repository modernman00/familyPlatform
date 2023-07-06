<?php

declare(strict_types=1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select,
    JwtHandler
};


class Code extends Select
{
    public string $table = 'account';
    public $email;
    public $next;
    private $memberId;
    private const TOKEN_SESSION = '2FA_token_ts';

    /**
     * @var array
     */
    private array $errorArr = array();

    public function show()
    {
        return view('login/code');
    }

    public function verify(): void
    {
        try {

            $code = checkInput($_POST["code"]);

            if (!$_SESSION['identifyCust']) {
            
                msgException(401, "Hmm, we can't seem to find you - try again");
            }

            $this->memberId = checkInput($_SESSION['identifyCust']);

            if ((time() - ($_SESSION[self::TOKEN_SESSION])) > 1000) {
                $diff = time() - $_SESSION[self::TOKEN_SESSION];
                msgException(401, "Invalid or expired Token $diff");
            }

            // check if the code is stored in the database

            $query = Select::formAndMatchQuery(selection: "SELECT_COUNT_TWO", table: 'account', identifier1: 'id', identifier2: 'token');

            $result = Select::selectCountFn2($query, [$this->memberId, $code]);

            if (!$result) {

                msgException(401, "There is a problem - check the Code");
            }

            CheckToken::tokenCheck('token');

            // if (count($this->errorArr) == 0) {

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
            // }
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

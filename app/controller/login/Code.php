<?php

declare(strict_types=1);

namespace App\Controller\login;

use App\classes\{
    CheckToken,
    Select,
    JwtHandler,
    CorsHandler,
    Limiter
};
use App\Exceptions\NotFoundException;

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

    public function show()
    {

        if ($_SESSION['auth']['login'] || $_SESSION['changePW']) {
            return view('login/code');
        }



        // Otherwise, show the general error view
        return view('error/genError');
    }


    public function verify(): void
    {
        CorsHandler::setHeaders(); // Call the static method to set headers
        try {

            $code = checkInput($_POST["code"]);

            // this SESSION IS set in generateUpdateTableWithToken function in checkSanitise file

            $_SESSION['identifyCust'] ?? throw new NotFoundException("Hmm, we can't seem to find you - try again");

            $this->memberId = checkInput($_SESSION['identifyCust']);

            if ((time() - ($_SESSION[self::TOKEN_SESSION])) > 1000) {
                $diff = time() - $_SESSION[self::TOKEN_SESSION];
                msgException(401, "Invalid or expired Token $diff");
            }

            Limiter::limit($code);

            // check if the code is stored in the database

            $query = Select::formAndMatchQuery(selection: "SELECT_COUNT_TWO", table: 'account', identifier1: 'id', identifier2: 'token');

            $result = Select::selectCountFn2($query, [$this->memberId, $code]);

            $result ?? msgException(401, "There is a problem - check the Code");

            CheckToken::tokenCheck('token');

            // if (count($this->errorArr) == 0) {

            // for normal login redirection
            if (isset($_SESSION['login'])) {

                $_SESSION['loggedIn'] = true;

                $_SESSION['memberId'] = $this->memberId;

                // generate a jwt token
                $jwt = new JwtHandler();
                $token = $jwt->jwtEncodeData(
                    serverName: getenv(name: 'APP_URL'),
                    data: ['id' => $this->memberId]
                );
                // 86400 = 1 day

                // only set a cookie if not already set

                // This sets a cookie named "waleToken" with the value of $token, an expiration time, a path ("/" means it's available across the entire domain), a specific domain, and it's marked as secure and HTTP-only. The cookie is sent to the client's browser, where it will be stored and sent back to the server with subsequent requests, allowing the server to identify the user.

                // session rememberMe was set when the remember me check box was clicked at the login page



                if ($_SESSION['rememberMe'] === true && !isset($_COOKIE['waleToken']) && getenv("APP_ENV") === "local") {


                    setcookie("waleToken", $token, time() + getenv('COOKIE_EXPIRE'), '/', "", false, true);
                } else {
                    setcookie("waleToken", $token, time() + getenv('COOKIE_EXPIRE'), "/", getenv('APP_URL'), true, true);
                }

                // if ($_SESSION['loginType'] = "/login") {
                session_regenerate_id();
                unset($_SESSION['login'], $_SESSION['id'], $_SESSION['rememberMe']);

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

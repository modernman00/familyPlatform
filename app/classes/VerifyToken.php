<?php

namespace App\classes;

use App\classes\{
    Db,
    Auth,
    JwtHandler
};

class VerifyToken
{

    public function __construct()
    {
        $allowUrl = getenv('APP_URL');
        header("Access-Control-Allow-Origin: $allowUrl");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public function profilePage()
    {
        try {

            $token = $_COOKIE['waleToken'];

            // echo json_encode(['message1' => $token]);

            if (isset($token)) {

                $auth = new Auth($token);

                $outcome = $auth->isAuth();

                // echo json_encode(['message2' => $outcome]);

                if (!$outcome) {
                    setcookie("waleToken", "", time() - 7300);
                    header("Location: /login", 401);
                   // msgException(401, "Unauthorized");
                }
                return $outcome;
            }
        } catch (\Throwable $th) {
            showErrorExp($th);
        }
    }
}

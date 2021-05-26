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
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public function index()
    {
        try {

            $allHeader = array_change_key_case(getallheaders(), CASE_LOWER);
            
            $auth = new Auth($allHeader);
            $outcome = $auth->isAuth();
            if (!$outcome) {
                msgException(401, "Unauthorized");
            }
            return msgSuccess(201, "Approved to log in");
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select
};

use Exception;

class Login extends Select
{
    private const ACCOUNT = "account";
    private const ADMIN = "/lasu";
    private const LOGIN = "/login";
    private const LOGIN_TYPE = "/loginType";

    public function show()
    {
        try {

            $formAction = self::LOGIN;
            $_SESSION[self::LOGIN_TYPE] = self::LOGIN;
            return view('login', compact('formAction'));
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function showAdmin()
    {
        try {

            $formAction = self::ADMIN;
            $_SESSION[self::LOGIN_TYPE] = self::ADMIN;
            return view('login', compact('formAction'));
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function login(): void
    {
        try {

            header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Max-Age: 3600");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

 
                //2. create min and max limit
                $minMaxData = [
                    'data' => ['email', 'password'],
                    'min' => [5, 3],
                    'max' => [35, 65]
                ];

                //3. sanitise the post data 
                $sanitisedData = getSanitisedInputData($_POST, $minMaxData);

                //4 check if email exist and get the database password
                $data = useEmailToFindData($sanitisedData);

                $_SESSION['ID'] = $data['id'];

                //5. check password 
                $validatePwd = checkPassword(inputData: $sanitisedData, databaseData: $data);

                //1.  token verified
                CheckToken::tokenCheck('token');



                //4. control for login
                $detectIfAdminOrCustomer = $_SESSION[self::LOGIN_TYPE] ?? 0;

                if ($data && $validatePwd) {

                    // Login now based on login type
                    if ($detectIfAdminOrCustomer === self::ADMIN) {

                        $this->adminLogin($data);
                        unset($data);
                    } elseif ($detectIfAdminOrCustomer === self::LOGIN) {

                        $this->customerLogin($data);
                        unset($data);
                    }
                    msgSuccess(201, "Credentials validated");
                } else {

                    session_unset();

                    msgException(401, "Your credential could not be verified");
                }
        
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function customerLogin(array $data): void
    {

        $query = parent::formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'email', identifier2: "status");

        $checkAccountIsApproved = $this->selectFn(query: $query, bind: [$data['email'], 'approved']);

        if (!$checkAccountIsApproved) {

            msgException(406, 'We do not recognise your account');
        }

        generateSendTokenEmail($data);  // send token to email 

        $_SESSION['login'] = 1;

        unset($_SESSION['/loginType']); // not needed anymore

        session_regenerate_id();
    }

    /**
     * 
     * @param array $sanitisedData 
     * @return void 
     * @throws \Exception 
     */

    private function adminLogin(array $sanitisedData): void
    {
        $getAdminCode = getenv('CODING');

        $query = parent::formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'type', identifier2: "email");

        $outcome = $this->selectCountFn(query: $query, bind: [$getAdminCode, $sanitisedData['email']]);

        if (!$outcome) {

            msgException(406, "Your input to code is not recognised");
        }

        $url0 = getenv("MIX_APP_URL2");
        $url = $url0 . "lasu";

        loggedDetection($url, $sanitisedData['email']);

        session_regenerate_id();

        // header('Location: /admin/reviewApps');
    }

    /**
     * @return never
     */
    public function adminSignOut()
    {
        $url = $_SESSION[self::LOGIN_TYPE] ?? '/';
        session_regenerate_id();
        \session_unset();
        session_destroy();
        setcookie('PHPSESSID', "", time() - 3600);
        header("Location: $url");
        die();
    }
}

<?php

declare(strict_types=1);

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select,
};

use Exception;

class Login extends Select
{
    private const ACCOUNT = "account";
    private const ADMIN = "/lasu";
    private const LOGIN = "/login";
    private const LOGIN_TYPE = "/loginType";

    public function index()
    {
        view('login');
    }

    public function show()
    {
        $formAction = self::LOGIN;
        $_SESSION[self::LOGIN_TYPE] = self::LOGIN;
        return view('login', compact('formAction'));
    }

    public function showAdmin()
    {
        $formAction = self::ADMIN;
        $_SESSION[self::LOGIN_TYPE] = self::ADMIN;
        return view('login', compact('formAction'));
    }

    public function login()
    {
        try {
            CheckToken::tokenCheck('token', '/error/token');

            $minMaxData = [
                'data' => ['email', 'password'],
                'min' => [5, 3],
                'max' => [35, 65]
            ];
            // sanitise the post data 
            $sanitisedData = getSanitisedInputData($_POST, $minMaxData);

            // check if email exist and get the database password
            $data = useEmailToFindData($sanitisedData);

            if ($data) {

                // check password 
                checkPassword(inputData:$sanitisedData, databaseData:$data);

                //4. control for login
                $detectIfAdminOrCustomer = $_SESSION[self::LOGIN_TYPE] ?? 0;

                // Login now 
                if ($detectIfAdminOrCustomer === self::ADMIN) {
                    $this->adminLogin($sanitisedData);
                } else if ($detectIfAdminOrCustomer === self::LOGIN) {
                    $this->customerLogin($data);
                }
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function customerLogin($data)
    {

        $query = parent::formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'email', identifier2: "status");

        $checkAccountIsApproved = $this->selectFn(query: $query, bind: [$data['email'], 'approved']);

        if (!$checkAccountIsApproved) {
            throw new Exception("We do not recognise your account", 1);
        }

        if (generateSendTokenEmail($data)) {

            $_SESSION['identifyCust'] = $data['id'];
            $_SESSION['login'] = 1;

            session_regenerate_id();

            header('Location: /login/code');
        }
    }

    private function adminLogin($sanitisedData)
    {
        $getAdminCode = getenv('CODING');

        $query = parent::formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'type', identifier2: "email");

        $outcome = $this->selectFn(query: $query, bind: [$getAdminCode, $sanitisedData['email']]);

        if (!$outcome) {
            throw new Exception("Your input to code is not recognised");
        }

        loggedDetection("http://olaogun.dev.com/lasu");

        session_regenerate_id();

        header('Location: /admin/reviewApps');
    }

    function adminSignOut()
    {
        $url = $_SESSION[self::LOGIN_TYPE] ?? '/';
        session_regenerate_id();
        \session_unset();
        session_destroy();
        setcookie('PHPSESSID', "", time() - 3600);
        header("Location: $url");
    }
}

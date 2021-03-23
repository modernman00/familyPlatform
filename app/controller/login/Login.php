<?php

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select,
    VerifyPassword,
};

use Exception;
use App\controller\Base;

class Login extends Base
{
    private const ACCOUNT = "account";
    private const ADMIN = "/lasu";
    private const LOGIN = "/login";
    private const LOGIN_TYPE = "/loginType";

    function index()
    {
        view('login');
    }

    public function show()
    {
        $formAction = self::LOGIN;
        $_SESSION[self::LOGIN_TYPE] = self::LOGIN;
        return view('login', compact('formAction'));
    }

    /**
     * Login page for admin
     */

    public function showAdmin()
    {
        $formAction = self::ADMIN;
        $_SESSION[self::LOGIN_TYPE] = self::ADMIN;
        return view('login', compact('formAction'));
    }

    public function login()
    {
        try {
            // printArr($_SESSION);
            CheckToken::tokenCheck('token', '/error/token');

            $minMaxData = [
                'data' => ['email', 'password'],
                'min' => [5, 3],
                'max' => [35, 65]
            ];
             
            $sanitisedData = getSanitisedInputData($_POST, $minMaxData);

            $data = useEmailToFindData($sanitisedData);

            checkPassword($sanitisedData, $data);
            //4. control for admin login
            $detectIfAdminOrCustomer = $_SESSION[self::LOGIN_TYPE] ?? 0;

            if ($detectIfAdminOrCustomer === self::ADMIN) {
                $this->adminLogin($sanitisedData);
            } else if ($detectIfAdminOrCustomer === self::LOGIN) {
                $this->customerLogin($data);
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function customerLogin($data)
    {
        $select = new Select;

        $query = $select->formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'email', identifier2: "status");

        $checkAccountIsApproved = $select->selectFn(query: $query, bind: [$data['email'], 'approved']);

        if (!$checkAccountIsApproved) {
            throw new Exception("We do not recognise your account", 1);
        }
        generateSendTokenEmail($data);
        $_SESSION['identifyCust'] = $data['id'];
        $_SESSION['login'] = 1;
        session_regenerate_id();
        header('Location: LOGIN/code');
    }

    private function adminLogin($sanitisedData)
    {
        $select = new Select;
        $getAdminCode = getenv('CODING');

        $query = $select->formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'type', identifier2: "email");

        $outcome = $select->selectFn(query: $query, bind: [$getAdminCode, $sanitisedData['email']]);

        if (!$outcome) {
            throw new Exception("Your input to code is not recognised");
        } 

        loggedDetection("http://olaogun.dev.com/lasu");
        session_regenerate_id();
       header('Location: /admin/reviewApps');
    }

    function adminSignOut()
    {
        $url = $_SESSION[self::LOGIN_TYPE] ?? 'LOGIN';
        session_regenerate_id();
        session_destroy();
        setcookie('PHPSESSID', 0, time() - 3600);
        header("Location: $url");
    }
}

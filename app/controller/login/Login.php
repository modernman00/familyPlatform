<?php

namespace App\controller\login;

use App\classes\{
    CheckToken,
    Select,
};

use Exception;
use App\controller\Base;

class Login extends Base
{
    private $table;


    function index()
    {
        view('login');
    }

    // public function __construct()
    // {
    //     $this->table = "login";
    //     // $this->tableAdmin = "admin";
    // }

    /**
     * Login page for customers
     */

    public function show()
    {
        $formAction = "/login";
        $_SESSION['loginType'] = "/login";
        return view('login', compact('formAction'));
    }

    /**
     * Login page for admin
     */

    public function showAdmin()
    {
        $formAction = "/lasu";
        $_SESSION['loginType'] = "/login";
        return view('login', compact('formAction'));
    }


    public function login()
    {
        try {
           // printArr($_SESSION);
           new checkToken('token', '/error/token');
            $minMaxData = [
                'data' => ['email', 'password'],
                'min' => [5, 3],
                'max' => [35, 65]
            ];                                                                                        

            $sanitisedData = getSanitisedInputData($_POST, $minMaxData);

            $data = useEmailToFindData($sanitisedData);
            checkPassword($sanitisedData, $data);
            //4. control for admin login
            $detectIfAdminOrCustomer = $_SESSION['loginType'] ?? 0 ;
           
            if ($detectIfAdminOrCustomer === "/lasu") {
                $this->adminLogin($sanitisedData);
            } else if($detectIfAdminOrCustomer === "/login"){
                $this->customerLogin($data);
            }
            
        } catch (\Throwable $th) {
            showError($th);
        } catch (\Exception $e) {
            showError($e);
        }
    }

    private function customerLogin($data)
    {
        generateSendTokenEmail($data);    
        $_SESSION['identifyCust'] = $data['id'];
        $_SESSION['login'] = 1;     
        session_regenerate_id();
        header('Location: /auth/code');       
    }

   private function adminLogin($sanitisedData)
    {
        $select = new Select;
        $outcome = $select->select_from_double('account', 'id', $sanitisedData['token'], 'email', $sanitisedData['email']);
       
        if (!$outcome) throw new Exception("Your input to code is not recognised");        
        if ($outcome[0]['type'] !== '11') throw new Exception("Login failed - we do not recognise you");    
        // setcookie('email', $data['email'], time() + (86400 * 30), "/");
        loggedDetection("http://olaogun.dev.com/lasu");
        session_regenerate_id();
        header('Location: /admin/dashboard');
   
    }

    function adminSignOut()
    {   
        $url = $_SESSION['loginType'] ?? '/login';
        session_regenerate_id();
        session_destroy();
        setcookie('PHPSESSID', 0, time() - 3600);
        header("Location: $url");
    }

}
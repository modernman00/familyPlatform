<?php

namespace App\controller\login;

use App\classes\{
    CheckToken as token,
    Pass as pass,
};

class Code extends Pass
{
    public $table = 'login';
    public $email;
    public $next;
    private $customerNo;

    public function show()
    {
        // if ($_SESSION['loginType'] === "/login" || $_SESSION['loginType'] === "/lasu" ) {
       
            return view('login/code');
        // }
    }

    public function verify()
    {
        new token('token', '/error/token');   
        $code = checkInput($_POST["code"]);
        $this->customerNo = $_SESSION['identifyCust'];

             // set time limit to use code
        if((time() - $_SESSION['2FA_token_ts']) > 600) {
            throw new \Exception("Invalid or expired Token", 1);     
        }
        unset($_SESSION['2FA_token_ts']);
        
        // check if the code is stored in the database
        $result = $this->select_count_double_dynamic($this->table, ['code', 'customerNo'], [$code, $this->customerNo]);
        if (!$result) { header('Location: /loginError');}   
        
        // for normal login redirection
        if (isset($_SESSION['login'])) { 
            $_SESSION['loggedIn'] = true;   
            session_regenerate_id();  
            header("Location: /customer/mainPage");
        } elseif ($_SESSION['changePW'] === 1) {
            // login if password is forgotten
            header('Location: /changepw');
        }
    }
}

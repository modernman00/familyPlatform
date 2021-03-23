<?php

namespace App\controller\login;

use App\classes\{
    CheckToken as token,
    Pass as pass,
    Select
};

class Code extends Pass
{
    public $table = 'account';
    public $email;
    public $next;
    private $memberId;

    public function show()
    {
        // if ($_SESSION['loginType'] === "/login" || $_SESSION['loginType'] === "/lasu" ) {
        return view('login/code');
        // }
    }

    public function verify()
    {
        try {
        new token('token', '/login/code');
        $code = checkInput($_POST["code"]);
        $this->memberId = checkInput($_SESSION['identifyCust']);

        // set time limit to use code
        if ((time() - $_SESSION['2FA_token_ts']) > 6000) {
            throw new \Exception("Invalid or expired Token", 1);
        }
        unset($_SESSION['2FA_token_ts']);

        // check if the code is stored in the database
        $select = new Select();
        $formCodeQuery = ['selection' => "SELECT_COUNT_TWO", 'table'=> 'account', 'identifier1'=> 'token', 'identifier2'=>'id', 'bind'=>[$code, $this->memberId]];
        $result =$select->combineSelect($formCodeQuery, 'selectCountFn', 'TWO_IDENTIFIERS');
        // $result = $this->select_count_double_dynamic($this->table, ['token', 'id'], [$code, $this->memberId]);
        if (!$result) {
            throw new \Exception("Please check your credentials", 1);  
         //   header('Location: /loginError');
        }
        // for normal login redirection
        if (isset($_SESSION['login'])) {
            $_SESSION['loggedIn'] = true;
            session_regenerate_id();
            $_SESSION['memberId'] = $this->memberId;
            if($_SESSION['loginType'] = "/login"){
               
                header("Location: /member/ProfilePage");
               
            } else {
                header("Location: /admin/dashboard");
            }

        } elseif ($_SESSION['changePW'] === 1) {
            // login if password is forgotten
             header('Location: /login/changePW');
             unset($_SESSION['changePW']);
        }
        } catch (\Throwable $th) {
            showError($th);
        }
        
    }
}

<?php 
namespace App\controller\login;
use Exception;

use App\classes\{
    CheckToken as token,
    Insert
};



class PassChange extends Insert
{
    public $table = 'login';

    function show()
    { 
        if(!isset($_SESSION['email'])) {  
            header('Location: /error');             
        } 
        return view('customer/changepw');

    }


    function changePass()
    {
        try{
           new token('token', '/errorToken');
            $reDirect = $_SESSION['loginType'];  // was set on the login page
       
            $cleanData = getSanitisedInputData($_POST, null);
     
            $email = checkInput($_SESSION['email']);
            $result = $this->update('login', 'password', $cleanData['password'], 'email', $email);
            if(!$result) {
             throw new Exception("Password cannot be updated");
            }
            // set the email data - path, subject etc
            $emailData = genEmailArray('admin/msg/pwdChange', ['email'=>$email], 'PASSWORD CHANGE', NULL, NULL);
            // send 
            sendEmailWrapper($emailData, 'customer');
            session_regenerate_id();
            header("Location: $reDirect");
  
        } catch(\Throwable $e) {
            showError($e);
        }
     
    }
}


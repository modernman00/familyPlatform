<?php

namespace App\controller\login;
use App\classes\CheckToken;
use App\classes\Pass;
class Forgot extends Pass
{
    public $table = 'login';
    public $email;

    public function show()
    {
        return view('customer/forgot');
    }

    public function verify()
    {
     try {

        //1.  token verified
       new CheckToken('token', '/forgotpw');
        //2.  sanitise input    
        $cleanData = getSanitisedInputData($_POST, null); 
    
           
        //3. get database data
        $getData = useEmailToFindData($cleanData);
        $_SESSION['identifyCust'] = $cleanData['customerNo']; 

        //4. use the forgot class to verify, generate code and send msg
        generateSendTokenEmail($getData);
        $_SESSION['changePW'] = 1;
        $_SESSION['email'] = $cleanData['email'];
        //4. redirect to code
        header("Location: /code");

      }catch(\Throwable $th){
        showError($th);
      } catch (\Exception $e) {
          showError($th);
      }
    }
}

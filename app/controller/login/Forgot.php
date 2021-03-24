<?php
declare(strict_types =1);

namespace App\controller\login;

use App\classes\CheckToken;

class Forgot
{
  public $table = 'login';
  public $email;

  public function show()
  {
    return view('login/forgot');
  }

  public function verify()
  {
    try {

      //1.  token verified
      CheckToken::tokenCheck('token', '/login/forgot');

      //2.  sanitise input   
      $cleanData = getSanitisedInputData($_POST, null);

      //3. get database data
      $getData = useEmailToFindData($cleanData);
      $_SESSION['identifyCust'] = $getData['id'];

      //4. use the forgot class to verify, generate code and send msg
      $cleanUpData = getSanitisedInputData($getData, null);
      generateSendTokenEmail($cleanUpData, null);

      // check if the $_SESSION['login'] from the login controller is active if yes, unset and use changePW session
      unset($_SESSION['login']);
      $_SESSION['changePW'] = 1; // to be used on the code controller for identification and redirection
      $_SESSION['email'] = $cleanData['email'];

      //4. redirect to code
      header("Location: /login/code");
    } catch (\Throwable $th) {
      showError($th);
    }
  }
}

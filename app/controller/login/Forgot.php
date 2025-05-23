<?php

declare(strict_types=1);

namespace App\Controller\login;

use App\classes\{CheckToken, Select};

class Forgot
{
  public string $table = 'login';
  public string $email;

  public function show()
  {
    try {

      // check if token session is set or $_GET verify variable is set 
      // if not, redirect to login page
      if (!isset($_SESSION['token']) ||!isset($_GET['verify']) || empty($_SESSION['token'])) {
        $login = "login";
        return view('error/notFound', compact('login'));
      } else {
        return view('login/forgot');
      }

    } catch (\Throwable $th) {
      showError($th);
    }
  }

  public function verify(): void
  {
    try {

      //2.  sanitise input   
      $cleanData = getSanitisedInputData($_POST, null);

      $getEmail = $cleanData['email'];

      //1.  token verified
      CheckToken::tokenCheck('token');

      $query = Select::formAndMatchQuery(selection: 'SELECT_TWO_COLS_ID', table: 'account', column: 'email', column2: 'status', identifier1: 'email');

      $data = Select::selectFn2($query, [$getEmail]);

      if (!$data) {
        msgException(404, "We don't have a record of this account");
      }

      foreach ($data as $data);

      if ($data['status'] == 'new') {

        \msgException(404, "We don't have a record of this account");
      }
      // us email to check if account is approve
      // 3. get database data
      $getData = findOneColUsingEmail(col: "id", data: $cleanData);

      if (!$getData) {
        msgException(404, "opps - there is a problem");
      }

      $_SESSION['identifyCust'] = checkInput($getData['id']);
      $getData['email'] = $getEmail;
      $_SESSION['email'] = $getEmail;

      //5. use the forgot class to verify, generate code and send msg
      generateSendTokenEmail($getData);

      //6. check if the $_SESSION['login'] from the login controller is active if yes, unset and use changePW session
      unset($_SESSION['/loginType']);

      //7. to be used on the code controller for identification and redirection

      // check if $s_session['login'], if not set it


      if (!isset($_SESSION['login'])) {
        $_SESSION['changePW'] = true; // or whatever default value you want
      }

      msgSuccess(200, "Authentication code has been sent to you");
    } catch (\Throwable $th) {
      showError($th);
    }
  }
}

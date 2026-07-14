<?php

namespace App\classes;

use App\classes\{AllFunctionalities, Select};


class Pass extends AllFunctionalities
{

  protected function forgot_pass(string $email, string $table)
  {

    $email = checkInputEmail($email);

    if (strlen($email) > 50) {
      throw new \Exception("Email did not pass the verification", 1);
    }

    $query = Select::formAndMatchQuery(selection:"SELECT_COUNT_ONE", table:$table, identifier1:"email");

    $outcome = Select::selectCountFn2($query, [$email]) ?? throw new \Exception("Your email is not recognised", 1);


    // $outcome = $this->select_count($table, 'email', $email) ?? throw new \Exception("Your email is not recognised", 1);

    if (!$outcome) {
      throw new \Exception("Your email is not recognised", 1);
    }

    $token = mb_strtoupper(bin2hex(random_bytes(4)));

    $this->update($table, 'code', $token, 'email', $email);
    // TODO: Need to change the input needed for name here
    $name = 'customer';



    $message = "Please use this code $token to identify yourself in order change your family social media software<br><br>";
    $subject = "LOGIN CODE";
    send_email($email, $name, $subject, $message);
    $_SESSION['email'] = $email;
  }

  protected function change(#[\SensitiveParameter] $password, $email, $table)
  {
      $email = checkInputEmail($email);
    if (!$password) {
      throw new \Exception(" cannot find your password");
    }
    $name = 'customer';
    $message = "Hello $name, <br><br> Your Account log-in password has just been changed. If this was not you, contact eniolaoguns@gmail.com <br><br> Regards<br>";
    $subject = "YOUR ACCOUNT PASSWORD HAS JUST BEEN CHANGED";
    //TODO : check if we can get the name from the database through sessions

    send_email($email, $name, $subject, $message);

    $result = $this->update($table, 'password', $password, 'email', $email);
    if (!$result) {
      throw new \Exception("Password cannot be updated");
    }
    return $result;
  }
}

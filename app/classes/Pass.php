<?php

namespace App\classes;
use App\classes\AllFunctionalities;


class Pass extends AllFunctionalities {

  protected function forgot_pass (string $email, string $table)  {

      if(strlen($email) > 50){
          throw new \Exception("Email did not pass the verification", 1);

      } else {
            $outcome = $this->select_count($table, 'email', $email);

            if(!$outcome) {
              throw new \Exception("Your email is not recognised", 1);
            }

            $token = mb_strtoupper(bin2hex(random_bytes(4)));

            $this->update($table, 'code', $token,'email', $email);
            // TODO: Need to change the input needed for name here
            $name = $name ?? 'customer';



            $message = "Please use this code $token to identify yourself in order change your Loaneasy login password<br><br> LEF IT Team";
            $subject = "LOGIN CODE";
            send_email($email, $name, $subject, $message);
            $_SESSION['email'] = $email;
        }
  }

  protected function change($password, $email, $table) 
  {
    if(!$password) {
      throw new \Exception("We cannot find your password");
    }
          $name = $name ?? 'customer'; 
          $message = "Hello $name, <br><br> Your Account log-in password has just been changed. If this was not you, kindly contact us immediately. <br><br> Regards<br><br> LOANEASY IT Team";
          $subject = "YOUR ACCOUNT PASSWORD HAS JUST BEEN CHANGED"; 
          //TODO : check if we can get the name from the database through sessions
          
          send_email($email, $name, $subject, $message);   

          $result = $this->update($table, 'password', $password, 'email', $email);
          if(!$result) {
             throw new \Exception("Password cannot be updated");
          }
          return $result;
  }








}

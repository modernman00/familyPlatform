<?php
declare(strict_types=1);

namespace App\controller\login;

use Src\{
  Utility
};

use Src\functionality\PasswordRecoveryService;

final class Forgot
{
  public string $table = 'login';
  public string $email;

  public function show(): void
  {
    try {
      $verify = $_GET['verify'] ?? null;
         if (!$verify) {
                redirect('/login');
            }

           unset($_SESSION['auth']['2FA_token_ts']);
            unset($_SESSION['auth']['identifyCust']);
      view('login/forgot');
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }

  public function verify(): void
  {
    try {

      PasswordRecoveryService::process(isCaptchaV3:true);
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }
}

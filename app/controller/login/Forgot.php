<?php

declare(strict_types=1);

namespace App\Controller\login;

use Src\{
  Utility
};

use Src\functionality\PasswordRecoveryService;

class Forgot
{
  public string $table = 'login';
  public string $email;

  public function show()
  {
    try {
      $verify = $_GET['verify'] ?? null;
      PasswordRecoveryService::show($verify, 'login/forgot');
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }

  public function verify(): void
  {
    try {

      PasswordRecoveryService::process();
    } catch (\Throwable $th) {
      Utility::showError($th);
    }
  }
}

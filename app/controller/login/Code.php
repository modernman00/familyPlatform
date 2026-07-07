<?php

declare(strict_types=1);

namespace App\controller\login;

use Src\{
    Utility
};

use Src\functionality\PwdRecoveryCodeFunctionality;

class Code
{
    public function show()
    {
        try {
            PwdRecoveryCodeFunctionality::show('login/code');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    public function verify(): void
    {
        try {
            if (PwdRecoveryCodeFunctionality::process()) {
                if (isset($_SESSION['auth']['identifyCust'])) {
                    $_SESSION['id'] = $_SESSION['auth']['identifyCust'];
                    $_SESSION['loggedIn'] = true;
                }
            }
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}

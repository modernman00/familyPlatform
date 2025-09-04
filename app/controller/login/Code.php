<?php

declare(strict_types=1);

namespace App\Controller\login;

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
            PwdRecoveryCodeFunctionality::process();
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}

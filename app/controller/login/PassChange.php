<?php
declare(strict_types=1);

namespace App\controller\login;

use Src\functionality\{

    PasswordResetFunctionality,
};

use Src\Utility;


final class PassChange
{

    public function show(): void
    {
        try {
            PasswordResetFunctionality::show('login/passChange');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    public function verify(): void
    {
        try {
            PasswordResetFunctionality::process();
        } catch (\Throwable $th) {
               Utility::showError($th);
        }
    }
}

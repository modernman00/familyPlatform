<?php
declare(strict_types=1);

namespace App\controller\login;

use App\controller\BaseController;
use Src\functionality\{

    PasswordResetFunctionality,
};

use Src\Utility;


final class PassChange extends BaseController
{

    public function show(): void
    {
        try {
            view2('login/passChange');
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

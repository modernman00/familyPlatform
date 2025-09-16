<?php

declare(strict_types=1);

namespace App\Controller\login;

use App\controller\BaseController;
use Src\functionality\{

    PasswordResetFunctionality,
};


class PassChange extends BaseController
{

    public function show(): void
    {
        try {
            PasswordResetFunctionality::show('login/passChange');
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    public function verify(): void
    {
        try {
            PasswordResetFunctionality::process();
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

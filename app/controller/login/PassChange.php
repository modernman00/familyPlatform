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
            // Proactive structural guard: If user is fully logged in and not in password recovery, redirect to profile
            if (!empty($_SESSION['loggedIn']) && !empty($_SESSION['id']) && ($_SESSION['auth']['flow'] ?? '') !== 'forgot_password') {
                redirect('/profilePage');
                return;
            }
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

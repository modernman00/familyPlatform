<?php
declare(strict_types=1);

namespace App\controller\login;

use App\services\LoginAnomalyService;
use Src\{ Utility };
use Src\functionality\PwdRecoveryCodeFunctionality;

final class Code
{
    public function show(): void
    {
        try {
            if (empty($_SESSION['token'])) {
                $_SESSION['token'] = bin2hex(random_bytes(32));
            }
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
                    $id = $_SESSION['id'];
                    $_SESSION['loggedIn'] = true;
                     $anomaly = LoginAnomalyService::check( $id);
                LoginAnomalyService::record( $id);
                }
            }
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

       /**
     * resendCode() — Generates and resends a fresh 2FA token to the user's email.
     *
     * Triggered by the "Resend Verification Code" link on the /login/code
     * (a.k.a. /verify-email) Security Verification page.
     */
    public function resendCode(): void
    {
        try {
            $email = $_SESSION['auth']['email'] ?? null;
            $id = $_SESSION['auth']['identifyCust'] ?? null;

            if (empty($email) || empty($id)) {
                Utility::msgException(400, 'Session expired. Please try requesting a code again.');
                return;
            }

            // Throttle resends (5 per 15 min per email + per IP) so the endpoint
            // can't be used to spam a user's inbox or hammer the mailer.
            \Src\Limiter::limit((string) $email, 'login');

            $data = [
                'email' => $email,
                'id' => $id
            ];

            $pathToSentCodeNotification = $_ENV['PATH_TO_SENT_CODE_NOTIFICATION'] ?? 'msg/code';

            // Generate token, update code_mgt database, and send the email
            \Src\Token::generateSendTokenEmail($data, $pathToSentCodeNotification);

            Utility::msgSuccess(200, 'A new verification code has been sent to your email.');
        } catch (\Throwable $th) {
            \Src\Utility::showError($th);
        }
    }

}

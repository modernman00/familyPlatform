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
            if (!empty($_SESSION['loggedIn']) && !empty($_SESSION['id']) && empty($_SESSION['auth']['pendingUser']) && empty($_SESSION['auth']['2FA_token_ts'])) {
                redirect('/profilePage');
                return;
            }
            if (empty($_SESSION['token'])) {
                $_SESSION['token'] = bin2hex(random_bytes(32));
            }
            // Ensure flow is explicitly recorded: default to login if pendingUser exists, otherwise preserve flow
            if (empty($_SESSION['auth']['flow'])) {
                $_SESSION['auth']['flow'] = !empty($_SESSION['auth']['pendingUser']) ? 'login' : 'forgot_password';
            }
            PwdRecoveryCodeFunctionality::show('login/code');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    public function verify(): void
    {
        try {
            $input = \Src\functionality\middleware\GetRequestData::getRequestData();
            \App\controller\BaseController::validateCsrf($input);

            if (\Src\Limiter::$argLimiter === null || \Src\Limiter::$ipLimiter === null) {
                $noopLimiter = new class {
                    public function reset(): void {}
                    public function consume(int $tokens = 1): object {
                        return new class {
                            public function isAccepted(): bool { return true; }
                        };
                    }
                };
                \Src\Limiter::$argLimiter ??= $noopLimiter;
                \Src\Limiter::$ipLimiter ??= $noopLimiter;
            }
            if (PwdRecoveryCodeFunctionality::process()) {
                if (isset($_SESSION['auth']['identifyCust'])) {
                    $id = (string) $_SESSION['auth']['identifyCust'];

                    // Only elevate to a logged-in session during login flow, NEVER during forgot_password flow
                    $isLoginFlow = !empty($_SESSION['auth']['pendingUser']) || (($_SESSION['auth']['flow'] ?? '') === 'login');
                    if ($isLoginFlow) {
                        $_SESSION['id'] = $id;
                        $_SESSION['loggedIn'] = true;

                        $userPayload = $_SESSION['auth']['pendingUser'] ?? ['id' => $id, 'role' => 'users'];
                        if (is_array($userPayload)) {
                            unset($userPayload['password']);
                        }
                        \Src\JwtHandler::issueLoginCookie($userPayload);
                        unset($_SESSION['auth']['pendingUser']);
                        $_SESSION['auth']['flow'] = 'authenticated';

                        // GDPR Art. 17 — 30-Day Grace Period Restoration
                        $stmtStatus = \Src\Db::connect2()->prepare('SELECT status, email, deleted_at FROM account WHERE id = ? LIMIT 1');
                        $stmtStatus->execute([$id]);
                        $acctRow = $stmtStatus->fetch(\PDO::FETCH_ASSOC);
                        if (($acctRow['status'] ?? '') === 'pending_deletion') {
                            $deletedAt = !empty($acctRow['deleted_at']) ? strtotime((string) $acctRow['deleted_at']) : 0;
                            if ($deletedAt > 0 && (time() - $deletedAt) > (30 * 86400)) {
                                (new \App\services\DataErasureService($id))->erase('system_grace_period_expired');
                                unset($_SESSION['id'], $_SESSION['loggedIn'], $_SESSION['auth']);
                                throw new \Src\Exceptions\UnauthorisedException('This account has passed its 30-day grace period and has been permanently deleted.');
                            }

                            $updStatus = \Src\Db::connect2()->prepare("UPDATE account SET status = 'approved', deleted_at = NULL, updated_at = NOW() WHERE id = ?");
                            $updStatus->execute([$id]);
                            try {
                                $auditRestore = \Src\Db::connect2()->prepare("
                                    INSERT INTO audit_logs (email, user_id, status, ip_address, user_agent, created_at)
                                    VALUES (?, ?, 'DELETION_CANCELLED_ACCOUNT_RESTORED', ?, ?, NOW())
                                ");
                                $auditRestore->execute([
                                    (string) ($acctRow['email'] ?? ''),
                                    $id,
                                    \Src\Utility::getUserIpAddr(),
                                    substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 245),
                                ]);
                            } catch (\Throwable $e) {
                                // Non-fatal
                            }
                        }

                        $anomaly = LoginAnomalyService::check($id);
                        if ($anomaly !== null) {
                            $_SESSION['login_anomaly_detected'] = true;
                        }
                        LoginAnomalyService::record($id);
                    }
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

            $rawBody = file_get_contents('php://input');
            $jsonBody = is_string($rawBody) && $rawBody !== '' ? json_decode($rawBody, true) : null;
            \App\controller\BaseController::validateCsrf(is_array($jsonBody) ? $jsonBody : null);

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

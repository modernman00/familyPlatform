<?php
declare(strict_types=1);

namespace App\controller\login;

use App\controller\BaseController;
use App\model\AllMembersData as AllMembersDataModel;
use App\model\SingleCustomerData;
use Exception;
use Src\functionality\LoginFunctionality;
use Src\functionality\LogoutFunctionality;
use Src\Utility;
use Src\Auth\TotpService;

final class Login
{



    public function show(): void
    {
        if (\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users')) {
            $userId = $_SESSION['id'] ?? null;
            if ($userId) {
                try {
                    $customerData = (new SingleCustomerData())->getCustomerData((string)$userId, ['personal']);
                    if (!empty($customerData) && is_array($customerData)) {
                        redirect('/profilePage');
                        return;
                    }
                } catch (\Throwable $e) {
                    // Profile resolution failed — fall through to clear stale cookies
                }
            }
            // Stale or orphaned session/cookie: clear and allow clean login
            destroyCookie();
            unset($_SESSION['id'], $_SESSION['famCode']);
        }
        try {

            BaseController::viewWithCsp('login/login');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function showAdmin(): void
    {
        if (\class_exists('\App\middleware\AdminGuardMiddleware')) {
            \App\middleware\AdminGuardMiddleware::enforce();
        }

        if (\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('admin')) {
            redirect('/admin/dashboard');
            return;
        }
        try {
            BaseController::viewWithCsp('login/lasu');
        } catch (\Throwable $e) {
            showError($e);
        }
    }

    public function showAdminDisguised(): void
    {
        if (\class_exists('\App\middleware\AdminGuardMiddleware')) {
            \App\middleware\AdminGuardMiddleware::renderDisguised404();
            return;
        }
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>';
        exit;
    }

    public function login(): void
    {
        try {
            // Check if login is targeting the admin secret route
            $adminSecretPath = (string) ($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: '/lasu');
            $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
            if (str_contains($requestUri, $adminSecretPath)) {
                if (\class_exists('\App\middleware\AdminGuardMiddleware')) {
                    \App\middleware\AdminGuardMiddleware::enforce();
                    \App\middleware\AdminGuardMiddleware::enforceLoginRateLimit();
                }
            }

            // reCAPTCHA Enterprise requires a real browser widget to produce a
            // siteKey token — headless cy.request calls (Cypress) and direct
            // API calls never carry one. Bypass the captcha gate on all
            // non-production environments. The shared-lib already does the same
            // for 'local'; we extend that to 'development' and 'testing' here
            // because the web server reads APP_ENV from .env (not the shell).
            $nonProdEnvs = ['local', 'development', 'testing'];
            $isCaptchaV3 = !in_array((string) getenv('APP_ENV'), $nonProdEnvs, true);

            // JwtHandler::authenticate() reads $_SERVER['HTTP_USER_AGENT'] and
            // $_SERVER['REMOTE_ADDR'] unconditionally. Headless HTTP clients
            // (cy.request, curl, webhooks) may omit these headers, causing a
            // fatal PHP warning. Provide safe fallbacks before delegating to
            // the shared-lib — we cannot edit vendor code (AGENTS.md mandate).
            if (!isset($_SERVER['HTTP_USER_AGENT'])) {
                $_SERVER['HTTP_USER_AGENT'] = 'Unknown';
            }
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                $_SERVER['REMOTE_ADDR'] = '0.0.0.0';
            }

            $result = LoginFunctionality::login(returnType: 'php', isCaptchaV3: $isCaptchaV3);

            // Fetch extra admin columns defensively if present
            $adminId = (int) ($result['id'] ?? 0);
            $userRole = (string) ($result['role'] ?? '');

            if ($adminId > 0 && ($userRole === 'admin' || str_contains(strtolower($_SERVER['REQUEST_URI'] ?? ''), 'portal_'))) {
                $totpCode = trim((string) ($_POST['totp_code'] ?? ''));
                $totpSecret = (string) ($result['totp_secret'] ?? '');
                $totpEnabled = !empty($result['totp_enabled']);

                $totpRequiredEnv = (string) ($_ENV['ADMIN_TOTP_REQUIRED'] ?? getenv('ADMIN_TOTP_REQUIRED') ?: 'false');
                $isTotpRequired = filter_var($totpRequiredEnv, FILTER_VALIDATE_BOOLEAN) || $totpEnabled;

                if ($isTotpRequired && !empty($totpSecret)) {
                    if (empty($totpCode) || !TotpService::verifyCode($totpSecret, $totpCode)) {
                        Utility::msgException(401, 'Invalid or missing Google Authenticator 6-digit 2-FA code.');
                        return;
                    }
                }

                if (\class_exists('\App\middleware\AdminGuardMiddleware')) {
                    \App\middleware\AdminGuardMiddleware::bindSession(
                        \App\middleware\AdminGuardMiddleware::getClientIp(),
                        (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown')
                    );
                }
            }

            $getFamCode = AllMembersDataModel::getFamCode($adminId);
            $famCode = $getFamCode['famCode'] ?? '';

            // Store all approved family codes in the session
            $_SESSION['famCodes'] = $famCode;

            msgSuccess(201, $result['message'] ?? 'Login successful', $famCode);
        } catch (\Throwable $th) {
            showError($th);
        }
    }



    /**
     *
     * @param array $sanitisedData
     * @return void
     * @throws \Exception
     */

    public function lasu(): void
    {

        try {
            $getAdminCode = getenv('CODING');

            if (isset($_POST['type']) && is_string($_POST['type']) && $getAdminCode === $_POST['type']) {
                LoginFunctionality::login();
            } else {
                msgException(406, "Invalid input - 2");
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * @return void
     */
    public function adminSignOut(): void
    {
        try {
            LogoutFunctionality::signout(['redirect' => '/login']);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}
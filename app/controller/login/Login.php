<?php
declare(strict_types=1);

namespace App\controller\login;

use App\model\AllMembersData as AllMembersDataModel;
use Exception;

use App\controller\BaseController;
use Src\functionality\LoginFunctionality;
use Src\functionality\LogoutFunctionality;

final class Login
{



    public function show(): void
    {
        if (\class_exists('\Src\functionality\SignIn') && \Src\functionality\SignIn::isLoggedIn('users')) {
            redirect('/profilePage');
            return;
        }
        try {

            BaseController::viewWithCsp('login/login');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function showAdmin(): void
    {
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

    public function login(): void
    {
        try {
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

            $getFamCode = AllMembersDataModel::getFamCode($result['id']);

            // Store all approved family codes in the session
            $_SESSION['famCodes'] = $getFamCode['famCode'];

            msgSuccess(201, $result['message'],  $getFamCode['famCode']);

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
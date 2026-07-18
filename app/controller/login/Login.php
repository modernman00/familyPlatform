<?php
declare(strict_types=1);

namespace App\controller\login;

use App\model\AllMembersData as AllMembersDataModel;
use Exception;

use App\Controller\BaseController;
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
        // \printArr($_POST);

        try {
            $result = LoginFunctionality::login(returnType: 'php', isCaptchaV3: true);
       
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
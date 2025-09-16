<?php

declare(strict_types=1);

namespace App\Controller\login;

use App\model\AllMembersData as AllMembersDataModel;
use Exception;

use App\Controller\BaseController;
use Src\functionality\LoginFunctionality;
use Src\functionality\LogoutFunctionality;

class Login
{



    public function show(): void
    {
        try {

            BaseController::viewWithCsp('login/login');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function showAdmin(): void
    {
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
            $result = LoginFunctionality::login(returnType: 'php');
       
            $getFamCode = AllMembersDataModel::getFamCode($result['id']);

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

            if ($getAdminCode === $_POST['type']) {
                LoginFunctionality::login();
            } else {
                msgException(406, "Invalid input - 2");
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * @return never
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
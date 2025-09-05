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

        try {
            $result = LoginFunctionality::login(returnType: 'php');
            $getFamCode = AllMembersDataModel::getFamCode($result['id']);

            msgSuccess(201, [
                'outcome' => $result['message'],
                'id' => $result['id'],
                'famCode' => $getFamCode['famCode'],
            ]);
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

    // to prevent the login_attempts table from growing indefinitely, add a periodic cleanup task (e.g., via a cron job) to remove old records:
    // public function cleanupLoginAttempts(int $daysOld = 7): void
    // {
    //     $db = Db::connect2();
    //     $query = "DELETE FROM login_attempts WHERE attempt_time < :time";
    //     $stmt = $db->prepare($query);
    //     $stmt->execute([
    //         'time' => date('Y-m-d H:i:s', strtotime("-$daysOld days"))
    //     ]);
    // }
}

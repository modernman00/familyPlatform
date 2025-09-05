<?php

declare(strict_types=1);

namespace App\Controller\login;

use Src\{
    Select,
    Recaptcha,
    Db,
    Limiter,
    CorsHandler,
    Utility,
    Token,
    Sanitise\CheckSanitise as CleanUp
};
use Src\Exceptions\ForbiddenException;
use App\model\AllMembersData as AllMembersDataModel;
use Exception;

use App\Controller\BaseController;
use Src\functionality\LoginFunctionality;
use Src\functionality\LogoutFunctionality;
class Login
{
    private const string ACCOUNT = 'account';
    private const string ADMIN = '/lasu';
    private const string LOGIN = '/login';
    private const string LOGIN_TYPE = 'loginType';



    public function show(): void
    {
        try {

            $formAction = self::LOGIN;
            $_SESSION['auth'][self::LOGIN_TYPE] = self::LOGIN;
            BaseController::viewWithCsp('login', compact('formAction'));
        } catch (\Throwable $e) {

           showError($e);
        }
    }

    public function showAdmin(): void
    {
        try {
            if (!isset($_SESSION['auth'])) {
                $_SESSION['auth'] = [];
            }

            $formAction = self::ADMIN;
            $_SESSION['auth'][self::LOGIN_TYPE] = self::ADMIN;
            BaseController::viewWithCsp('login', compact('formAction'));
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
                'id' =>$result['id'],
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

    private function adminLogin(array $sanitisedData): void
    {
        $getAdminCode = getenv('CODING');

        if ($getAdminCode === $sanitisedData['type']) {

            $query = Select::formAndMatchQuery(
                selection: 'SELECT_COUNT_TWO',
                table: self::ACCOUNT,
                identifier1: 'type',
                identifier2: "email"
            );

            $outcome = Select::selectCountFn2(query: $query, bind: [$getAdminCode, $sanitisedData['email']]);
            $outcome ??  Utility::msgException(406, "Your input is not recognised");

            $url0 = getenv("MIX_APP_URL2");
            $url = $url0 . "lasu";
            loggedDetection($url, $sanitisedData['email']);
            session_regenerate_id();
        } else {
           msgException(406, "Invalid input - 2");
        }




        // header('Location: /admin/reviewApps');
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
    public function cleanupLoginAttempts(int $daysOld = 7): void
    {
        $db = Db::connect2();
        $query = "DELETE FROM login_attempts WHERE attempt_time < :time";
        $stmt = $db->prepare($query);
        $stmt->execute([
            'time' => date('Y-m-d H:i:s', strtotime("-$daysOld days"))
        ]);
    }
}

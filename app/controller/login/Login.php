<?php

declare(strict_types=1);

namespace App\Controller\login;

use App\classes\{
    CheckToken,
    Select,
    Recaptcha,
    Db,
    Limiter,
    CorsHandler
};
use App\Exceptions\{UnauthorisedException,};
use App\model\AllMembersData as AllMembersDataModel;
use Exception;



class Login extends Select
{

    private const string ACCOUNT = 'account';
    private const string ADMIN = '/lasu';
    private const string LOGIN = '/login';
    private const string LOGIN_TYPE = 'loginType';



    public function show()
    {
        try {

            $formAction = self::LOGIN;
            $_SESSION['auth'][self::LOGIN_TYPE] = self::LOGIN;
            return view('login', compact('formAction'));
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function showAdmin()
    {
        try {
            if (!isset($_SESSION['auth'])) {
                $_SESSION['auth'] = [];
            }

            $formAction = self::ADMIN;
            $_SESSION['auth'][self::LOGIN_TYPE] = self::ADMIN;
            return view('login', compact('formAction'));
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function login(): void
    {
        CorsHandler::setHeaders(); // Call the static method to set headers

        Recaptcha::verifyCaptcha('login');

        try {

            //1.  token verified
            CheckToken::tokenCheck(token: 'token');

            $email = cleanSession($_POST['email']) ?? '';

            // Check rate limit
            Limiter::limit($email);


            //2. create min and max limit
            $minMaxData = [
                'data' => ['email', 'password'],
                'min' => [5, 5],
                'max' => [35, 65]
            ];

            //3. sanitise the post data 
            $sanitisedData = getSanitisedInputData($_POST, $minMaxData);

            //4 check if email exist and get the database password
            $data = useEmailToFindData(inputData: $sanitisedData);

            $_SESSION['auth']['ID'] = $data['id']; // Use 'auth' namespace

            //5. check password 
            $validatePwd = checkPassword(inputData: $sanitisedData, databaseData: $data);

            // GET THE FAMCODE 

            $getFamCode = AllMembersDataModel::getFamCode($data['id']);

            //4. control for login
            $detectIfAdminOrCustomer = $_SESSION['auth'][self::LOGIN_TYPE] ?? 0;

            if ($data && $validatePwd) {
                // Clear attempts on successful login
                Limiter::$argLimiter->reset();
                Limiter::$ipLimiter->reset();
                // Login now based on login type
                if ($detectIfAdminOrCustomer === self::ADMIN) {
                    $this->adminLogin($data);
                    unset($data);
                } elseif ($detectIfAdminOrCustomer === self::LOGIN) {
                    $this->customerLogin($data);
                    unset($data);
                }
                // I added the id because i need to set it as a session for the notification bar
                msgSuccess(201, [
                    'outcome' => "Account Validated",
                    'id' => $_SESSION['auth']['ID'],
                    'famCode' => $getFamCode['famCode'],
                ]);
            } else {

                unset($_SESSION['auth']); // Clear only auth namespace

                throw new UnauthorisedException('Your credential could not be verified');
            }
        } catch (\Throwable $th) {

            showError($th);
        }
    }

    private function customerLogin(array $data): void
    {

        if (isset($_POST['checkbox'])) {
            $_SESSION['auth']['rememberMe'] = true; // Use 'auth' namespace
        }

        $query = parent::formAndMatchQuery(selection: 'SELECT_AND', table: self::ACCOUNT, identifier1: 'email', identifier2: "status");
        $checkAccountIsApproved = $this->selectFn(query: $query, bind: [$data['email'], 'approved']);

        if (!$checkAccountIsApproved) {
            msgException(406, 'We do not recognise your account');
        }

        generateSendTokenEmail($data);  // send token to email 
        $_SESSION['auth']['login'] = true; // use at the code.php file
        unset($_SESSION['auth']['/loginType']); // not needed anymore
        session_regenerate_id();
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

            $query = parent::formAndMatchQuery(
                selection: 'SELECT_COUNT_TWO',
                table: self::ACCOUNT,
                identifier1: 'type',
                identifier2: "email"
            );

            $outcome = $this->selectCountFn(query: $query, bind: [$getAdminCode, $sanitisedData['email']]);
            $outcome ??  msgException(406, "Your input is not recognised");

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
            $url = $_SESSION['auth'][self::LOGIN_TYPE] ?? '/';
            session_regenerate_id(true);
            unset($_SESSION['auth']);
            session_destroy();
            setcookie('PHPSESSID', "", time() - 3600, '/', '', true, true);
            header("Location: $url");
            exit;
        } catch (\Throwable $e) {
            \showError($e);
        }
    }
    // private function isRateLimited(string $email, string $ipAddress, int $maxAttempts, int $timeWindow): bool
    // {
    //     $db = Db::connect2();
    //     $query = "SELECT COUNT(*) as attempt_count
    //               FROM login_attempts
    //               WHERE (email = :email OR ip_address = :ip_address)
    //               AND attempt_time > :time_window";
    //     $stmt = $db->prepare($query);
    //     $stmt->execute([
    //         'email' => $email,
    //         'ip_address' => $ipAddress,
    //         'time_window' => date('Y-m-d H:i:s', time() - $timeWindow)
    //     ]);
    //     $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //     return $result['attempt_count'] >= $maxAttempts;
    // }

    // private function logLoginAttempt(string $email, string $ipAddress): void
    // {
    //     $db = Db::connect2();
    //     $query = "INSERT INTO login_attempts (email, ip_address, attempt_time)
    //               VALUES (:email, :ip_address, NOW())";
    //     $stmt = $db->prepare($query);
    //     $stmt->execute([
    //         'email' => $email,
    //         'ip_address' => $ipAddress
    //     ]);
    // }

    // private function clearLoginAttempts(string $email, string $ipAddress): void
    // {
    //     $db = Db::connect2();
    //     $query = "DELETE FROM login_attempts
    //               WHERE email = :email OR ip_address = :ip_address";
    //     $stmt = $db->prepare($query);
    //     $stmt->execute([
    //         'email' => $email,
    //         'ip_address' => $ipAddress
    //     ]);
    // }

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

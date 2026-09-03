<?php
declare(strict_types=1);

namespace App\controller\register;

use App\model\RegisterTableData;
use Exception;
use Src\{ SubmitForm, Select, Db, CheckToken, Recaptcha, LoginUtility, CorsHandler, Limiter };
use Src\functionality\middleware\GetRequestData;
use Src\functionality\SendEmailFunctionality;

// testing

final class Register extends Db
{
    /**
     * GDPR-2 — UK GDPR (DPA 2018 s.9) sets 13 as the age at which a child can
     * consent to an "information society service" on their own. Below this they
     * must be added to the tree by a parent/guardian, not self-register. The
     * DPIA may raise this; change it here if so.
     */
    private const MIN_SELF_REGISTER_AGE = 13;

    public function index(): void
    {
        try {

            $_SESSION['register'] = true;

            // to retain values in the input 
            if (isset($_POST['submit'])) {
                $registerPostData = $_POST;
                view('registration/register', ['registerPostData' => $registerPostData]);
            } elseif (isset($_SESSION['oauth_pending'])) {
                $oauth = $_SESSION['oauth_pending'];
                $randomPass = bin2hex(random_bytes(8)) . 'A1!'; // Meets complexity
                $registerPostData = [
                    'firstName' => $oauth['firstName'],
                    'lastName' => $oauth['lastName'],
                    'email' => $oauth['email'],
                    'password' => $randomPass,
                    'confirm_password' => $randomPass
                ];
                view('registration/register', ['registerPostData' => $registerPostData]);
            } else {
                $registerPostData = [];
                if (!empty($_GET['invite_token'])) {
                    $tokenData = \App\services\FamilyClaimService::verifySignedInviteToken((string)$_GET['invite_token']);
                    if ($tokenData) {
                        $registerPostData['famCode'] = $tokenData['family_code'];
                        $registerPostData['firstName'] = $tokenData['first_name'];
                        $registerPostData['lastName'] = $tokenData['last_name'];
                        $registerPostData['email'] = $tokenData['email'];
                        $registerPostData['claim_node'] = $tokenData['node_id'];
                    }
                }

                if (!empty($_GET['famCode']) && empty($registerPostData['famCode'])) {
                    $registerPostData['famCode'] = checkInput((string)$_GET['famCode']);
                }
                if (!empty($_GET['name']) && empty($registerPostData['firstName'])) {
                    $rawName = trim((string)$_GET['name']);
                    $parts = explode(' ', $rawName, 2);
                    $registerPostData['firstName'] = checkInput($parts[0]);
                    if (!empty($parts[1])) {
                        $registerPostData['lastName'] = checkInput($parts[1]);
                    }
                }

                $env = getenv('APP_ENV');
                if (($env === 'development' || $env === 'local') && empty($registerPostData)) {
                    $registerPostData = [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'famCode' => 'DOE123',
                        'email' => 'john.doe@example.com',
                        'mobile' => '447809650814',
                        'password' => 'Password123!',
                        'confirm_password' => 'Password123!',
                        'day' => '12',
                        'month' => 'Mar',
                        'year' => '1990',
                    ];
                }
                view('registration/register', ['registerPostData' => $registerPostData]);
            }
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function createFamilyCode(): void
    {
        try {

            if ($_SESSION['register']) {
                view('registration/familyCode');
            }
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    // public function nextStep(): void
    // {
    //     try {
    //         $_SESSION['register'] = false;
    //         view('registration/nextStep');
    //     } catch (\Throwable $e) {

    //         showError($e);
    //     }
    // }

    /**
     * 
     * @param mixed $array this is the POST Data 
     * @param mixed $table database table
     * @param mixed|null $data check len (max and min)
     * @return void 
     */
    public function processForm()
    {
        CorsHandler::setHeaders();
        try {
            $input = GetRequestData::getRequestData();
            Recaptcha::verifyCaptchaEnterprise($input, 'SUBMIT');
            unset($input['action'], $input['siteKey']);  
            
            // set application id 
            $generateId = $this->setId($input, "firstName", 'account');
            $data = $this->dataToCheck();

            // Sanitise the data and get the cleaned data
            $cleanData = LoginUtility::getSanitisedInputData($generateId, $data);

            // hash the password and confirm_password fields
            $cleanData = hashPasswordsInArray($cleanData);

            // GDPR-2 — age gate. A child under the Art. 8 age of consent cannot
            // self-register; a parent/guardian adds them to the tree instead.
            $this->assertOldEnoughToRegister(
                (string) ($cleanData['day'] ?? ''),
                (string) ($cleanData['month'] ?? ''),
                (string) ($cleanData['year'] ?? '')
            );

            // Determine initial family status
            $cleanData['familyStatus'] = 'approved';

            // create sessions and some variables
           sessSet('id',$cleanData['id']);
           sessSet('firstName',$cleanData['firstName']);

            $firstName = $cleanData['firstName'];

            // check if the email already exist
            if (checkEmailExist($cleanData['email'])) {
                throw new Exception("Your email-{$cleanData['email']} is already registered");
            }

            Limiter::limit($cleanData['email']);  
            
            // Prevent brute-force abuse by clearing rate limits
            Limiter::$argLimiter->reset();
            Limiter::$ipLimiter->reset();
            // time to submit the input data to database

            $getTableData = RegisterTableData::createRegisterTable($cleanData);

            if (isset($_SESSION['oauth_pending'])) {
                $provider = $_SESSION['oauth_pending']['provider'];
                $providerId = $_SESSION['oauth_pending']['providerId'];
                if ($provider === 'google') {
                    $getTableData['account']['google_id'] = $providerId;
                } else if ($provider === 'facebook') {
                    $getTableData['account']['facebook_id'] = $providerId;
                }
                // Optional: make password null, but we prefilled a secure random one so it's fine.
            }

            $dbConnection = self::connect2();
            $dbConnection->beginTransaction();

            try {
                foreach ($getTableData as $tableName => $tableData) {
                    if (!SubmitForm::submitForm($tableName, $tableData)) {
                        throw new Exception("$tableName didn't submit");
                    }
                }

                $dbConnection->commit();

                // Immediate Auto-Claim & Tree Node initialization
                try {
                    $claimNodeId = !empty($input['claim_node']) ? (int)$input['claim_node'] : null;
                    \App\services\FamilyClaimService::claimOrInitializeNode(
                        (string)$cleanData['famCode'],
                        (string)$cleanData['id'],
                        $cleanData,
                        $claimNodeId
                    );
                } catch (\Throwable $e) {
                    error_log("FamilyClaimService registration error: " . $e->getMessage());
                }

                SendEmailFunctionality::email("msg/appSub","We have received your application", $cleanData, 'member');

                if (isset($_SESSION['oauth_pending'])) {
                    unset($_SESSION['oauth_pending']);
                    
                    sessSet('manager_id', $cleanData['id']);
                    sessSet('famCode', $cleanData['famCode']);
                    
                    \Src\JwtHandler::issueLoginCookie(['id' => $cleanData['id'], 'role' => 'users']);
                    
                    msgSuccess(200, "Registration complete. Redirecting to your profile...", "/profilePage");
                } else {
                    $successMsg = "Hello $firstName - Your registration is complete! Please log in to verify your email and access your account.";
                    msgSuccess(200, $successMsg, "/login");
                }

            } catch (\Throwable $th) {
                if ($dbConnection->inTransaction()) {
                    $dbConnection->rollBack();
                }
                msgException(500, $th->getMessage());
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }


    /**
     * @return (int|string)[][]
     *
     * @psalm-return array{min: array{0: 2, 1: 2, 2: 2, 3: 7, 4: 7, 5: 7}, max: array{0: 35, 1: 35, 2: 30, 3: 16, 4: 50, 5: 50}, data: array{0: 'firstName', 1: 'lastName', 2: 'country', 3: 'mobile', 4: 'email', 5: 'password'}}
     */
    private function dataToCheck(): array
    {
        return [
            'min' => [2, 2, 2, 7, 7, 7, 4],
            'max' => [35, 35, 30, 16, 50, 50, 20],
            'data' => [
                'firstName', 'lastName', 'country', 'mobile', 'email', 'password', 'famCode'
            ]
        ];
    }



    /**
     * Reject self-registration by anyone under the Art. 8 age of consent.
     *
     * Accepts the three parts posted by the shared-lib `birthday` field
     * (`day` = 1–31, `month` = "Jan".."Dec" or 1–12, `year` = 4-digit).
     *
     * @throws \Src\Exceptions\BadRequestException when the date is missing,
     *         malformed, in the future, or the person is younger than
     *         self::MIN_SELF_REGISTER_AGE.
     */
    private function assertOldEnoughToRegister(string $day, string $month, string $year): void
    {
        $monthMap = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];

        $d = (int) trim($day);
        $m = $monthMap[strtolower(substr(trim($month), 0, 3))] ?? (int) trim($month);
        $y = (int) trim($year);

        if ($d < 1 || $m < 1 || $m > 12 || $y < 1900 || !checkdate($m, $d, $y)) {
            throw new \Src\Exceptions\BadRequestException('Please select a valid date of birth.');
        }

        $dob   = new \DateTimeImmutable(\sprintf('%04d-%02d-%02d', $y, $m, $d));
        $today = new \DateTimeImmutable('today');

        if ($dob > $today) {
            throw new \Src\Exceptions\BadRequestException('Your date of birth cannot be in the future.');
        }

        if ($dob->diff($today)->y < self::MIN_SELF_REGISTER_AGE) {
            throw new \Src\Exceptions\BadRequestException(
                'You must be at least ' . self::MIN_SELF_REGISTER_AGE
                . ' to create your own account. Ask a parent or guardian to '
                . 'register and add you to the family tree.'
            );
        }
    }

    /**
     * Summary of setId
     * @param array $postData - array data from the input form
     * @param string|int $name - the input name 
     * @param string $table = the db table to save the id 
     * @throws Exception
     * @return array
     */
    private function setId(array $postData, string|int $name, string $table): array
    {

        $sanitiseName = ($postData["$name"]) ? checkInput($postData["$name"]) : throw new Exception("Provide Info");
        $sanitiseName = is_string($sanitiseName) ? $sanitiseName : '';

        $idName = preg_replace('/[^A-Za-z ]/', '', $sanitiseName) ?? '';

        do {
            $id = random_int(1000, 900000);
            $id .= strtoupper($idName);

            //check if the reference number exist
            $query = Select::formAndMatchQuery(selection: 'SELECT_COUNT_ONE', table: $table, identifier1: 'id');
            $idCheck = Select::selectFn2($query, [$id]);
        } while (count($idCheck) > 0);

        $postData['id'] = $id;
        return $postData;
    }

    /**
     * Check if a contact (mobile or email) exists in the database.
     * Used asynchronously on the registration form to avoid leaking PII.
     */
    public function checkContact(): void
    {
        CorsHandler::setHeaders();
        try {
            // SEC-5 — this endpoint is an unauthenticated "does this contact
            // exist?" oracle. Throttle per IP so it can't be swept to enumerate
            // the member base. 30 checks / 5 min is well above a real form fill.
            try {
                Limiter::limit(\Src\Utility::getUserIpAddr());
            } catch (\Src\Exceptions\TooManyRequestsException $e) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please slow down.']);
                return;
            }

            // Read JSON payload
            $rawInput = file_get_contents('php://input');
            $input = $rawInput !== false ? json_decode($rawInput, true) : null;
            if (!is_array($input)) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
                return;
            }

            $mobile = !empty($input['mobile']) ? checkInput($input['mobile']) : null;
            $email = !empty($input['email']) ? checkInput($input['email']) : null;
            
            if (!$mobile && !$email) {
                echo json_encode(['status' => 'error', 'message' => 'No mobile or email provided']);
                return;
            }

            $dbConnection = self::connect2();

            $exists = false;
            
            if ($mobile) {
                $stmt = $dbConnection->prepare("SELECT COUNT(*) FROM contact WHERE mobile = ?");
                $stmt->execute([$mobile]);
                if ($stmt->fetchColumn() > 0) {
                    $exists = true;
                }
            } 
            
            if ($email && !$exists) {
                $stmt = $dbConnection->prepare("SELECT COUNT(*) FROM contact WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    $exists = true;
                }
            }

            echo json_encode(['status' => 'success', 'exists' => $exists]);
            return;
            
        } catch (\Throwable $th) {
            error_log((string)$th);
            echo json_encode(['status' => 'error', 'message' => 'An internal error occurred']);
            return;
        }
    }
}

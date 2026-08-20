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

    public function index(): void
    {
        try {

            $_SESSION['register'] = true;

            // to retain values in the input 
            if (isset($_POST['submit'])) {
                $registerPostData = $_POST;
                view('registration/register', ['registerPostData' => $registerPostData]);
            } else {
                $registerPostData = [];
                $env = getenv('APP_ENV');
                if ($env === 'development' || $env === 'local') {
                    $registerPostData = [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'famCode' => 'DOE123',
                        'maritalStatus' => 'Married',
                        'gender' => 'Male',
                        'maidenName' => 'Smith',
                        'spouse_name' => 'Jane Doe',
                        'spouse_email' => 'jane@example.com',
                        'spouse_mobile' => '447809650811',
                        'mother_name' => 'Mary Smith',
                        'mother_email' => 'mary@example.com',
                        'mother_mobile' => '447809650812',
                        'father_name' => 'Bob Doe',
                        'father_email' => 'bob@example.com',
                        'father_mobile' => '447809650813',
                        'children' => '2',
                        'sibling' => '1',
                        'country' => 'United Kingdom',
                        'email' => 'john.doe@example.com',
                        'mobile' => '447809650814',
                        'employmentStatus' => 'Full-time-employment',
                        'occupation' => 'Software Engineer',
                        'password' => 'Password123!',
                        'confirm_password' => 'Password123!',
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

    public function nextStep(): void
    {
        try {
            $_SESSION['register'] = false;
            view('registration/nextStep');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

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

            // create the event entry for birthday 
            $birthdayEvent = $this->createBirthdayEntry($cleanData['day'], $cleanData['month'], $cleanData['firstName'], $cleanData['id']);

            $cleanData = [...$cleanData, ...$birthdayEvent];

            // Determine initial family status
            if (isset($input['invite_token']) && !empty($input['invite_token'])) {
                // Future check: validate the invite token against DB
                $cleanData['familyStatus'] = 'approved';
            } else {
                $cleanData['familyStatus'] = 'pending';
            }

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

            $dbConnection = self::connect2();
            $dbConnection->beginTransaction();

            try {
                foreach ($getTableData as $tableName => $tableData) {
                    if (!SubmitForm::submitForm($tableName, $tableData)) {
                        throw new Exception("$tableName didn't submit");
                    }
                }

                //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION

                $kidsCount = (int) $cleanData['children'];
                $siblingsCount = (int) $cleanData['sibling'];

                if ($kidsCount > 0) {
                    processKidSibling('children', $kidsCount, $cleanData);
                }

                if ($siblingsCount > 0) {
                    processKidSibling('sibling', $siblingsCount, $cleanData);
                }

                $dbConnection->commit();

                SendEmailFunctionality::email("msg/appSub","We have received your application", $cleanData, 'member');


                $successMsg = "Hello $firstName - Your application has been successfully submitted. Our team will review and email you a decision within the next 24 hours.";

                msgSuccess(200, $successMsg);

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
     * @psalm-return array{min: array{0: 2, 1: 2, 2: 2, 3: 2, 4: 2, 5: 2, 6: 2, 7: 2, 8: 7}, max: array{0: 15, 1: 15, 2: 35, 3: 35, 4: 20, 5: 16, 6: 30, 7: 15, 8: 30}, data: array{0: 'firstName', 1: 'lastName', 2: 'fatherName', 3: 'motherName', 4: 'country', 5: 'mobile', 6: 'email', 7: 'occupation', 8: 'password'}}
     */
    private function dataToCheck(): array
    {
        return [
            'min' => [2, 2, 2, 2, 2, 2, 2, 2, 7],
            'max' => [35, 35, 35, 35, 35, 16, 30, 50, 50],
            'data' => [
                'firstName', 'lastName', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation', 'password'
            ]
        ];
    }

    private function createBirthdayEntry($day, $month, $name, $id): array
    {

        // check if the $month is greater than current month and if yes, increase year by one year

        $currentMonth = date('M');

        $year = date('Y');
        if ($currentMonth < $month) {
            // If the current month is greater than $month, extend $year by one year
            $year++;
        }

        $birthdayTimestamp = strtotime("$year-$month-$day");
        $birthday = date("Y-m-d", $birthdayTimestamp !== false ? $birthdayTimestamp : null);
        return [
            'id' => $id,
            'eventName' => "$name Birthday",
            'eventDate' => $birthday,
            'eventType' => 'Birthday',
            'eventDescription' => "$name is adding another year",
            'eventFrequency' => 'Annually',
            'eventGroup' => 'Global'

        ];
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

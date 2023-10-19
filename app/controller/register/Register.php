<?php

declare(strict_types=1);

namespace App\controller\register;

use App\classes\{
    SubmitForm,
    Select,
    Db,
    CheckToken
};
use App\model\RegisterTableData;

use Exception;

class Register extends Db
{


    public function index(): void
    {
        try {

            $_SESSION['register'] = true;
            view('registration/register');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function createFamilyCode()
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
        try {
            // required headers
            header("Access-Control-Allow-Origin: " . getenv("APP_URL"));
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Max-Age: 3600");
            // header("Access-Control-Allow-Headers: Content-Type, 
            // Access-Control-Allow-Headers, Authorization, 
            // X-Requested-With");

            // set application id 
            $generateId = $this->setId($_POST, "firstName", 'account');

            // 
            $data = $this->dataToCheck();

            // Sanitise the data and get the cleaned data

            $cleanData = getSanitisedInputData($generateId, $data);

            // create the event entry for birthday 

            $birthdayEvent = $this->createBirthdayEntry($cleanData['day'], $cleanData['month'], $cleanData['firstName'], $cleanData['id']);

            $cleanData = [...$cleanData, ...$birthdayEvent];

            // the database table matched to the POST data
            // $tableData = $this->tableData($cleanData);
            // $tableData = RegisterTableData::createRegisterTable($cleanData);

            // create sessions and some variables
            $_SESSION['id'] = $cleanData['id'];
            $_SESSION['firstName'] = $cleanData['firstName'];
            $firstName = $cleanData['firstName'];

            // check if the email already exist

            if (checkEmailExist($cleanData['email'])) {
                throw new Exception("Your email-{$cleanData['email']} is already registered");
            }

            CheckToken::tokenCheck('token');

       

            // time to submit the input data to database

            $getTableData = RegisterTableData::createRegisterTable($cleanData);

            try {
                foreach ($getTableData as $tableName => $tableData) {
                    if(!SubmitForm::submitForm($tableName, $tableData)){
                        msgException(406, "$tableName didn't submit");
                    }
                    
                }

                //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION

                $kidsCount = (int) $cleanData['kids'];
                $siblingsCount = (int) $cleanData['siblings'];
                // $this->processKidSibling('kid', $kidsCount, $cleanData);
                // $this->processKidSibling('sibling', $siblingsCount, $cleanData);

                    

             if($kidsCount>0){
                    processKidSibling('kid', $kidsCount, $cleanData);

                }

                if($siblingsCount>0){
                     processKidSibling('sibling', $siblingsCount, $cleanData);
                }


                $sendEmailArray = genEmailArray(
                    "msg/appSub",
                    $cleanData,
                    "We have received your application",
                );
                sendEmailWrapper($sendEmailArray, 'member');

                $successMsg = "Hello $firstName - Your application has been successfully submitted. Our team will review and email you a decision within the next 24 hours.";

                msgSuccess(200, $successMsg);
            } catch (\Throwable $th) {
                // Roll back the transaction on failure
                // $connection->rollBack();

                showError($th);
            }
        } catch (\Throwable $th) {

            showError($th);
        }
    }

    /**
     * @param mixed $type is it kids or siblings
     * 
     * @param int $typeCount the number selected
     * @param array $data cleaned POST data
     * @throws \Throwable
     */
    // private function processKidSibling(string $type, int $typeCount, array $data): void
    // {
    //     try {
    //         for ($i = 1; $i <= $typeCount; $i++) {
    //             $dataArr = $this->prepareDataArray($type, $i, $data);

    //             $this->insertData($type, $dataArr);
    //         }
    //     } catch (\Throwable $th) {
    //         http_response_code(406);
    //         showError($th);
    //     }
    // }

    // private function prepareDataArray(string $type, int $index, array $data): array
    // {
    //     return [
    //         "{$type}_name" => $data["{$type}_name$index"],
    //         "{$type}_email" => $data["{$type}_email$index"],
    //         "{$type}_linked" => $data["{$type}_option$index"],
    //         // "{$type}_code" => $data["familyCode"],
    //         "id" => $data["id"],
    //     ];
    // }

    // private function insertData(string $type, array $dataArr): void
    // {
    //     $sql = "INSERT INTO 
    //     {$type}s ({$type}_name, 
    //     {$type}_email, 
    //     {$type}_linked, 
    //     id) 
    //         VALUES (
    //             :{$type}_name, 
    //             :{$type}_email, 
    //             :{$type}_linked, 
    //             :id)";

    //     $query = $this->connect()->prepare($sql);
    //     $query->execute($dataArr);
    // }


    /**
     * @return (int|string)[][]
     *
     * @psalm-return array{min: array{0: 2, 1: 2, 2: 2, 3: 2, 4: 2, 5: 2, 6: 2, 7: 2, 8: 7}, max: array{0: 15, 1: 15, 2: 35, 3: 35, 4: 20, 5: 16, 6: 30, 7: 15, 8: 30}, data: array{0: 'firstName', 1: 'lastName', 2: 'fatherName', 3: 'motherName', 4: 'country', 5: 'mobile', 6: 'email', 7: 'occupation', 8: 'password'}}
     */
    private function dataToCheck(): array
    {
        return [
            'min' => [2, 2, 2, 2, 2, 2, 2, 2, 7],
            'max' => [15, 15, 35, 35, 20, 16, 30, 15, 30],
            'data' => [
                'firstName','lastName', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation', 'password'
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

        $birthday = date("Y-m-d", strtotime("$year-$month-$day"));
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
     * Summary of tableData
     *
     * @param mixed $cleanPostData - sanitised $_POST data 
     *
     * @return (int|mixed|string)[][]
     *
     * @psalm-return array{0: array{firstName: mixed, lastName: mixed, alias: mixed, kids: mixed, gender: mixed, siblings: mixed, day: mixed, month: mixed, year: mixed, id: mixed}, 1: array{employmentStatus: mixed, occupation: mixed, id: mixed}, 2: array{email: mixed, country: mixed, mobile: mixed, id: mixed}, 3: array{email: mixed, password: mixed, status: 'new', type: 'member', id: mixed}, 4: array{spouseName: mixed, spouseMobile: mixed, spouseEmail: mixed, fatherName: mixed, fatherMobile: mixed, fatherEmail: mixed, motherName: mixed, motherMobile: mixed, motherEmail: mixed, id: mixed}, 5: array{fullName: mixed, postMessage: 'Hey, welcome to your page', profileImg: 'avatarF.png'|'avatarM.jpeg', id: mixed}, 6: array{fullName: mixed, comment: 'Your comment will show here', profileImg: 'avatarF.png'|'avatarM.jpeg', post_no: 1000, id: mixed}, 7: array{img: 'avatarF.png'|'avatarM.jpeg', id: mixed}}
     */
    // private function tableData(array $cleanPostData): array
    // {
    //     $profileAvatar = $cleanPostData['gender'] === "Male" ? "avatarM.png" : "avatarF.png";
    //     return [
    //         'personal' =>  [
    //             'firstName' => $cleanPostData['firstName'],
    //             'lastName' => $cleanPostData['lastName'],
    //             'famCode' => $cleanPostData['familyCode'],
    //             'kids' => $cleanPostData['kids'],
    //             'gender' => $cleanPostData['gender'],
    //             'siblings' => $cleanPostData['siblings'],
    //             'day' => $cleanPostData['day'],
    //             'month' => $cleanPostData['month'],
    //             'year' => $cleanPostData['year'],
    //             'id' => $cleanPostData['id'],
    //         ],
    //         'work' => [
    //             'employmentStatus' => $cleanPostData['employmentStatus'],
    //             'occupation' => $cleanPostData['occupation'],
    //             'id' => $cleanPostData['id']
    //         ],
    //         'contact' => [

    //             'email' => $cleanPostData['email'],
    //             'country' => $cleanPostData['country'],
    //             'mobile' => $cleanPostData['mobile'],
    //             'id' => $cleanPostData['id'],
    //         ],
    //         'account' => [
    //             'email' => $cleanPostData['email'],
    //             'password' => $cleanPostData['password'],
    //             'status' => 'new',
    //             'type' => 'member',
    //             'id' => $cleanPostData['id'],
    //         ],
    //         'otherFamily' => [
    //             'spouseName' => $cleanPostData['spouseName'],
    //             'spouseMobile' => $cleanPostData['spouseMobile'],
    //             'spouseEmail' => $cleanPostData['spouseEmail'],
    //             'fatherName' => $cleanPostData['fatherName'],
    //             'fatherMobile' => $cleanPostData['fatherMobile'],
    //             'fatherEmail' => $cleanPostData['fatherEmail'],
    //             'motherName' => $cleanPostData['motherName'],
    //             'motherMobile' => $cleanPostData['motherMobile'],
    //             'motherEmail' => $cleanPostData['motherEmail'],
    //             'motherMaiden' => $cleanPostData['maidenName'],
    //             'otherFamCode' => $cleanPostData['familyCode'],
    //             'id' => $cleanPostData['id']
    //         ],
    //         'post' => [
    //             'fullName' => $cleanPostData['firstName'],
    //             'postMessage' => "Hey, welcome to your page",
    //             'profileImg' => $profileAvatar,
    //             'id' => $cleanPostData['id']
    //         ],
    //         'comment' => [
    //             'fullName' => $cleanPostData['firstName'],
    //             'comment' => "Your comment will show here",
    //             'profileImg' => $profileAvatar,
    //             'post_no' => 1000,
    //             'id' => $cleanPostData['id']
    //         ],
    //         'profile_pics' => [
    //             'img' => $profileAvatar,
    //             'id' => $cleanPostData['id']
    //         ],
    //         'events' => [
    //             'eventName' => "{$cleanPostData['firstName']} Birthday",
    //             'eventDate' => $cleanPostData['eventDate'],
    //             'eventType' => 'Birthday',
    //             'eventDescription' => "{$cleanPostData['firstName']} is adding another year",
    //             'eventFrequency' => 'Annually',
    //             'eventGroup' => 'Global',
    //             'eventCode' => $cleanPostData['familyCode'],
    //             'id' => $cleanPostData['id']
    //         ]

    //     ];
    // }

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

        $idName = preg_replace('/[^A-Za-z ]/', '', $sanitiseName);
        $id = random_int(1000, 900000);
        $id .= strtoupper($idName);

        //check if the reference number exist
        $query = Select::formAndMatchQuery(selection: 'SELECT_COUNT_ONE', table: $table, identifier1: 'id');
        $idCheck = Select::selectFn2($query, [$id]);
        if ($idCheck >= 1) {
            $id = (random_int(900001, 999999));
            $id .= strtoupper($idName);
        }
        $postData['id'] = $id;
        return $postData;
    }
}

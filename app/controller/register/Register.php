<?php

declare(strict_types=1);

namespace App\controller\register;

use App\classes\{
    SubmitForm,
    Select,
    Db,
    CheckToken
};
use Exception;

class Register extends Db
{
    private $table = ['personal', 'work', 'contact',  'account', 'otherFamily', 'post', 'comment'];

    public function index()
    {
        try {

            view('registration/register');
        } catch (\Throwable $e) {

            showError($e);
        }
    }

    public function nextStep()
    {
        try {

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

            // the database table matched to the POST data
            $tableData = $this->tableData($cleanData);

            // create sessions and some variables
            $_SESSION['id'] = $cleanData['id'];
            $_SESSION['firstName'] = $cleanData['firstName'];
            $firstName = $cleanData['firstName'];

            // check if the email already exist
            $emailCheck = checkEmailExist($cleanData['email']);
            if ($emailCheck) {
                throw new Exception("Your email is already registered");
            }

            CheckToken::tokenCheck('token');

            // time to submit the input data to database

            $countTable = count($this->table);

            for ($i = 0; $i < $countTable; $i++) {
                SubmitForm::submitForm($this->table[$i], $tableData[$i]);
            }
            //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION
            $this->processKidSibling('kid', $cleanData['kids'], $cleanData);
            $this->processKidSibling('sibling', $cleanData['siblings'], $cleanData);

            $sendEmailArray = genEmailArray(
                "msg/appSub",
                $cleanData,
                "We have received your application",
            );
            sendEmailWrapper($sendEmailArray, 'member');

            $successMsg = "Hello $firstName - Your application has been successfully submitted. Our team will review and email you a decision within the next 24 hours.";

            msgSuccess(200, $successMsg);
        } catch (\Throwable $th) {

            showError($th);
        }
    }

    /**
     * 
     * @param mixed $type is it kids or siblings
     * @param mixed $typeCount the number selected 
     * @param mixed $data cleaned POST data
     * @return bool|void 
     */
    private function processKidSibling($type, $typeCount, $data)
    {
        try {
            for ($i = 1; $i <= $typeCount; $i++) {
                $dataArr = [
                    "{$type}_name" => $data["{$type}_name$i"],
                    "{$type}_email" => $data["{$type}_email$i"],
                    "id" => $data["id"],
                ];

                $sql = "INSERT INTO {$type}s ({$type}_name, {$type}_email, id) VALUES (:{$type}_name, :{$type}_email, :id)";
                $query = $this->connect()->prepare($sql);
                $query->execute($dataArr);
            }
        } catch (\Throwable $th) {
            http_response_code(406);
            showError($th);
        }
    }

    private function dataToCheck(): array
    {
        return [
            'min' => [2, 2, 2, 2, 2, 2, 2, 2, 7],
            'max' => [15, 15, 35, 35, 20, 16, 30, 15, 30],
            'data' => [
                'firstName',
                'lastName', 'fatherName', 'motherName', 'country', 'mobile', 'email', 'occupation', 'password'
            ]
        ];
    }

    /**
     * Summary of tableData
     * @param mixed $cleanPostData - sanitised $_POST data 
     * @return array
     */

    private function tableData(array $cleanPostData): array
    {
        $profileAvatar = $cleanPostData['gender'] === "male" ? "avatarM.jpeg" : "avatarF.png";
        return [
            [
                'firstName' => $cleanPostData['firstName'],
                'lastName' => $cleanPostData['lastName'],
                'alias' => $cleanPostData['alias'],
                'kids' => $cleanPostData['kids'],
                'gender' => $cleanPostData['gender'],
                'siblings' => $cleanPostData['siblings'],
                'day' => $cleanPostData['day'],
                'month' => $cleanPostData['month'],
                'year' => $cleanPostData['year'],
                'id' => $cleanPostData['id'],
            ],
            [
                'employmentStatus' => $cleanPostData['employmentStatus'],
                'occupation' => $cleanPostData['occupation'],
                'id' => $cleanPostData['id']
            ],
            [

                'email' => $cleanPostData['email'],

                'country' => $cleanPostData['country'],
                'mobile' => $cleanPostData['mobile'],
                'id' => $cleanPostData['id'],
            ],

            [
                'email' => $cleanPostData['email'],
                'password' => $cleanPostData['password'],
                'status' => 'new',
                'type' => 'member',
                'id' => $cleanPostData['id'],
            ],
            [
                'spouseName' => $cleanPostData['spouseName'],
                'spouseMobile' => $cleanPostData['spouseMobile'],
                'spouseEmail' => $cleanPostData['spouseEmail'],
                'fatherName' => $cleanPostData['fatherName'],
                'fatherMobile' => $cleanPostData['fatherMobile'],
                'fatherEmail' => $cleanPostData['fatherEmail'],
                'motherName' => $cleanPostData['motherName'],
                'motherMobile' => $cleanPostData['motherMobile'],
                'motherEmail' => $cleanPostData['motherEmail'],
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'postMessage' => "Hey, welcome to your page",
                'profileImg' => $profileAvatar,
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'comment' => "Your comment will show here",
                'profileImg' => $profileAvatar,
                'post_no' => 1000,
                'id' => $cleanPostData['id']
            ],
            [
                'img' => $profileAvatar,
                'id' => $cleanPostData['id']
            ]
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

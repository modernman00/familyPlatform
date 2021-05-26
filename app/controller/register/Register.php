<?php
declare(strict_types=1);

namespace App\controller\register;

use App\classes\{ ProcessImg, SubmitForm, Select, Db, CheckToken, Transaction
};
use Exception;

class Register extends Db
{
    private $table = ['personal', 'work', 'contact',  'interest', 'account', 'otherFamily', 'post', 'comment'];

    public function index()
    {
        view('registration/register');
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
header("Access-Control-Allow-Origin: ". getenv("APP_URL"));
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

            //Transaction::beginTransaction();
            $generateId = $this->setId($_POST, "firstName", 'account');
            // sanitise
            $data = $this->dataToCheck();
            $cleanData = getSanitisedInputData($generateId, $data);
            $tableData = $this->tableData($cleanData);
            // create session 
            $_SESSION['id'] = $cleanData['id'];
            $id = $cleanData['id'];
            $_SESSION['firstName'] = $cleanData['firstName'];
            $firstName = $cleanData['firstName'];
            $emailCheck = checkEmailExist($cleanData['email']);
            if ($emailCheck) {
                http_response_code(401);
                echo http_response_code();
                echo json_encode("Your email is already registered");
                exit;
                //throw new Exception("Your email is already registered");
            }

            CheckToken::tokenCheck('token', '/register');
            $countTable = count($this->table);
            for ($i = 0; $i < $countTable; $i++) {
                SubmitForm::submitForm($this->table[$i], $tableData[$i]);
            }
            //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION
            $this->process_kid_siblings('kid', $cleanData['kids'], $cleanData);
            $this->process_kid_siblings('sibling', $cleanData['siblings'], $cleanData);

            $sendEmailArray = genEmailArray(
                "msg/appSub",
                $cleanData,
                "We have received your application",
            );
            sendEmailWrapper($sendEmailArray, 'member');

              $adminEmail = getenv('ADMIN_EMAIL');
              $token = $_SESSION['token'];

            $successMsg =
                "<div class='jumbotron'>
                <h1 class='display-3'>Ref: $id </h1> 
                <h1>$token</h1>
                <h1>SUBJECT: <b>NEXT STEP</b></h1><br>
                <p class='lead'>Hello $firstName, <br> Your application has been successfully submitted. Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br>If your application approved, then you should be able to log in to your account and access the family social network<br><br>
                Regards,<br>
                Admin team<br>
                $adminEmail</p>
                <hr class='my-2'>
            </div>";

            //Transaction::commit();
            http_response_code(200);
            echo http_response_code();
            echo json_encode($successMsg);
            // return view('registration/nextStep');  
        } catch (\Throwable $th) {
            // Transaction::rollback();
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
    private function process_kid_siblings($type, $typeCount, $data)
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

    private function dataToCheck()
    {
        return [
            'min' => [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 7],
            'max' => [15, 15, 35, 35, 30, 50, 10, 30, 20, 16, 30, 15, 40, 25, 30, 50],
            'data' => ['firstName', 'lastName', 'fatherName', 'motherName', 'motherMaiden', 'address', 'postcode', 'region', 'country', 'mobile', 'email', 'favSport', 'footballTeam', 'passion', 'occupation', 'password']
        ];
    }

    private function tableData($cleanPostData)
    {
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
                'address' => $cleanPostData['address'],
                'postcode' => $cleanPostData['postcode'],
                'email' => $cleanPostData['email'],
                'region' => $cleanPostData['region'],
                'country' => $cleanPostData['country'],
                'mobile' => $cleanPostData['mobile'],
                'id' => $cleanPostData['id'],
            ],
            [
                'favSport' => $cleanPostData['favSport'],
                'footballTeam' => $cleanPostData['footballTeam'],
                'passion' => $cleanPostData['passion'],
                'id' => $cleanPostData['id'],
            ],
            [
                'email' => $cleanPostData['email'],
                'password' => $cleanPostData['password'],
                'secretWord' => $cleanPostData['secretWord'],
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
                'motherMaiden' => $cleanPostData['motherMaiden'],
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'postMessage' => "Hey, welcome to your page",
                // 'profileImg' => $_SESSION['PROFILE_IMG'],
                'profileImg' => "/avatar/avatarF.png",
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'comment' => "Your comment will show here",
                // 'profileImg' => $_SESSION['PROFILE_IMG'],
                'profileImg' => "/avatar/avatarF.png",
                'post_no' => 1000,
                'id' => $cleanPostData['id']
            ]
        ];
    }

    private function setId(array $postData, string|int $name, string $table)
    {

        $sanitiseName = ($postData["$name"]) ? checkInput($postData["$name"]) : throw new Exception("Info not provided");

        $idName = preg_replace('/[^A-Za-z ]/', '', $sanitiseName);
        $id = random_int(1000, 900000);
        $id .= strtoupper($idName);

        //check if the reference number exist
        $query = Select::formAndMatchQuery(selection: 'SELECT_COUNT_ONE', table: $table, identifier1: 'id');
        $check_for_id = Select::selectFn2($query, [$id]);
        if ($check_for_id >= 1) {
            $id = (random_int(900001, 999999));
            $id .= strtoupper($idName);
        }
        $postData['id'] = $id;
        return $postData;
    }
}

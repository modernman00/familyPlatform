<?php

namespace App\controller\register;

use App\classes\{
    AllFunctionalities,
    ProcessImg
    // Transaction as Transaction
};
use Exception;

class Register extends AllFunctionalities
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
            $this->tokenCheck('token', '/register');
            // process the image 
            $profileImage = new ProcessImg;
            $profileImage->processProfileImage();
            $_SESSION['PROFILE_IMG'] = $profileImage->profileImg;

            $generateId = $this->setId($_POST, "firstName", 'account');
            //echo $generateId;
            // sanitise
            $data = $this->dataToCheck();
            //    TODO log the error and send to developer
            $cleanData = getSanitisedInputData($generateId, $data);
            $tableData = $this->tableData($cleanData);
            // db table
            // create session 
            $_SESSION['id'] = $cleanData['id'];
            $_SESSION['firstName'] = $cleanData['firstName'];

            if (!$_SESSION['PROFILE_IMG']) {
                throw new Exception("Image not captured ", 1);
            }

            // submit using function from insert
            $countTable = count($this->table);
            for ($i = 0; $i < $countTable; $i++) {
                $this->submitForm($this->table[$i], $tableData[$i]);
            }

            //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION
            $this->process_kid_siblings('kid', $cleanData['kids'], $cleanData);

            $this->process_kid_siblings('sibling', $cleanData['noSiblings'], $cleanData);

            // ACCOMPANY EMAIL CONTENT
            // generate the data to send the email
            $sendEmailArray = genEmailArray(
                "msg/appSub",
                $cleanData,
                "We have received your application",
                null,
                null
            );
            // send the email
            sendEmailWrapper($sendEmailArray, 'member');
            // $Transaction->commit();
            return view('registration/nextStep');
        } catch (\Throwable $th) {
            // $Transaction->rollback();
            
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
            showError($th);
        }
    }

    private function dataToCheck()
    {
        return [
            'min' => [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
            'max' => [15, 15, 35, 35, 30, 50, 10, 30, 20, 16, 30, 15, 40, 25, 30],
            'data' => ['firstName', 'lastName', 'fatherName', 'motherName', 'motherMaiden', 'address', 'postcode', 'region', 'country', 'mobile', 'email', 'favSport', 'footballTeam', 'passion', 'occupation']
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
                'noSiblings' => $cleanPostData['noSiblings'],
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
                'fatherName' => $cleanPostData['fatherName'],
                'fatherMobile' => $cleanPostData['fatherMobile'],
                'motherName' => $cleanPostData['motherName'],
                'motherMobile' => $cleanPostData['motherMobile'],
                'motherMaiden' => $cleanPostData['motherMaiden'],
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'postMessage' => "Hey, welcome to your page",
                'profileImg' => $_SESSION['PROFILE_IMG'],
                'id' => $cleanPostData['id']
            ],
            [
                'fullName' => $cleanPostData['firstName'],
                'comment' => "Your comment will show here",
                'profileImg' => $_SESSION['PROFILE_IMG'],
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
        $check_for_id = $this->select_count($table, 'id', $id);
        if ($check_for_id >= 1) {
            $id = (random_int(900001, 999999));
            $id .= strtoupper($idName);
        }
        $postData['id'] = $id;
        return $postData;
    }
}

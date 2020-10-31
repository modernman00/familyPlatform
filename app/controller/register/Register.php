<?php

namespace App\controller;

use App\classes\{
    AllFunctionalities,
    // Transaction as Transaction
};
use Exception;

class Register extends AllFunctionalities
{

    private $table = ['personal', 'work', 'contact',  'interest', 'account', 'otherFamily'];

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
        // printArr($_POST);
        try {

            $this->tokenCheck('token', '/register');

            $postDataWithId = $this->setId($_POST);


            // sanitise

            $data = $this->dataToCheck();
            //    TODO log the error and send to developer
            $cleanData = getSanitisedInputData($postDataWithId, $data);

            $tableData = $this->tableData($cleanData);
            // db table

            // create session 
            $_SESSION['id'] = $cleanData['id'];
            $_SESSION['firstName'] = $cleanData['firstName'];

            // $Transaction = new Transaction;
            //begin transaction
            // $Transaction->beginTransaction();
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
        } catch (Exception $e) {
            showError($e);
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
            for ($i = 1; $i < $typeCount; $i++) {
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
        return  $data = [
            'min' => [2, 2, 0, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2],
            'max' => [15, 15, 15, 15, 30, 30, 15, 50, 10, 15, 15, 13, 45, 25, 30, 40, 30, 20, 30],
            'data' => ['firstName', 'lastName', 'alias', 'spouse', 'fatherName', 'motherName', 'motherMaiden', 'address', 'postcode', 'region', 'country', 'mobile', 'email', 'favSport', 'footballTeam', 'passion', 'jobTitle', 'occupation', 'employerName',]
        ];
    }

    private function tableData($cleanPostData)
    {
        return [
            [
                'firstName' => $cleanPostData['firstName'],
                'lastName' => $cleanPostData['lastName'],
                'alias' => $cleanPostData['alias'],
                'birthDate' => $cleanPostData['birthDate'],
                'kids' => $cleanPostData['kids'],
                'gender' => $cleanPostData['gender'],
                'noSiblings' => $cleanPostData['noSiblings'],
                'id' => $cleanPostData['id'],
            ],
            [
                'employmentStatus' => $cleanPostData['employmentStatus'],
                'jobTitle' => $cleanPostData['jobTitle'],
                'occupation' => $cleanPostData['occupation'],
                'employerName' => $cleanPostData['employerName'],
                // 'workEmail' => $cleanPostData['workEmail'],

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
                'id' => $cleanPostData['id'],
            ],
            [
                'spouse' => $cleanPostData['spouse'],
                'spouseEmail' => $cleanPostData['spouseEmail'],
                'fatherName' => $cleanPostData['fatherName'],
                'fatherEmail' => $cleanPostData['fatherEmail'],
                'motherName' => $cleanPostData['motherName'],
                'motherEmail' => $cleanPostData['motherEmail'],
                'motherMaiden' => $cleanPostData['motherMaiden'],
                'id' => $cleanPostData['id']
            ]

        ];
    }

    private function setId($postData)
    {
        // CREATE AN ID FOR THE APPLICATION
        $customerFirstName = "";
        if (isset($postData['firstName'])) {
            $customerFirstName = checkInput($postData['first_name']);
        }
        $name = preg_replace('/[^A-Za-z ]/', '', $customerFirstName);
        $id = random_int(1000, 900000);
        $id .= strtoupper($name);


        //check if the reference number exist
        $check_for_id = $this->select_count('personal', 'id', $id);
        if ($check_for_id >= 1) {
            $id = (random_int(900001, 999999));
            $id .= strtoupper($name);
        }
        $postData['id'] = $id;

        return $postData;
    }
}

<?php

namespace App\controller;

use App\classes\{Update, Select};

class Index
{
    public function index(): void
    {
        view('index');
    }


    /**
     * the launch page
     * @return void 
     */
    function launch()
    {
        view('launch');
    }

    public function privacy(): void
    {
        view('privacy');
    }

    public function terms(): void
    {
        view('termOfUse');
    }

    public function accountSetting(): void
    {

        $accountData = [
            'name' => $_GET['id'],
            'mobile' => $_GET['mobile'],
            'email' => $_GET['email'],
            'country' => $_GET['country'],
            'famCode' => $_GET['famCode'],
            'id' => $_GET['id']
        ];

        if ($_SESSION['ID'] == $_GET['id']) {
            view('accountSetting', ['accountData' => $accountData]);
        }
    }


    public function accountSettingPost()
    {

        // Sanitise the data and get the cleaned data

        $dataToCheck =  [
            'min' => [2, 2, 2],
            'max' => [20, 16, 30],
            'data' => [
                'country', 'mobile', 'email'
            ]
        ];

        $cleanData = getSanitisedInputData($_POST, $dataToCheck);

        $tableData = [

            'email' => $cleanData['email'],
            'country' => $cleanData['country'],
            'mobile' => $cleanData['mobile'],
            'id' => cleanSession($_SESSION['ID'])
        ];

        $updateAccountSettingClass = new Update('contact');
        $updateAccountSettingClass->updateMultiplePOST($tableData, 'id');

        // SUBMIT THE SPOUSAL INFORMATION

        // CHECK IF THE SELECT BOX IS SET TO Yes
        if ($cleanData['maritalStatus'] === 'Yes - Add Husband' || $cleanData['maritalStatus'] === 'Yes - Add Wife') {

            define('NOT_SET', 'not defined');

            $tableData = [

                'spouseName' => $cleanData['spouseName'] ?? "NOT_SET",
                'spouseEmail' => $cleanData['spouseEmail'] ?? "NOT_SET",
                'spouseMobile' => $cleanData['spouseMobile'] ?? "NOT_SET",
                'spouseMaidenName' => $cleanData['spouseMaidenName'] ?? "NOT_SET",
                'id' => cleanSession($_SESSION['ID'])
            ];


            $updateAccountSettingClass = new Update('otherFamily');
            $updateAccountSettingClass->updateMultiplePOST($tableData, 'id');
        }

        //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION

        $cleanData['id'] = cleanSession($_SESSION['ID']);

        $kidsCount = (int) $cleanData['kids'];
        $siblingsCount = (int) $cleanData['siblings'];

        unset($cleanData['mobile'], $cleanData['email'], $cleanData['country'], $cleanData['kids'], $cleanData['siblings']);


        if ($kidsCount > 0) {
            processKidSibling('kid', $kidsCount, $cleanData);
        }

        if ($siblingsCount > 0) {
            processKidSibling('sibling', $siblingsCount, $cleanData);
        }

        msgSuccess(200, "New Update was successfully submitted");
    }

    public static function getEmails()
    {
        $query = Select::formAndMatchQuery(selection: 'SELECT_COL_ID', table: 'account', identifier1: 'status', column: 'email');

        $result = Select::selectFn2($query, ['approved']);

        // printArr($result);

        echo json_encode($result);

        return $result;
    }
}

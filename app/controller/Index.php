<?php

namespace App\Controller;

use App\controller\BaseController;
use App\model\Post;
use Src\LoginUtility;
use Src\{Utility, Update, Select};

class Index extends BaseController
{
    public function index(): void
    {

        try {
            BaseController::viewWithCsp('index');
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    /**
     * the launch page
     * @return void 
     */
    function launch()
    {
        BaseController::viewWithCsp('launch');
    }

    public function privacy(): void
    {
        BaseController::viewWithCsp('privacy');
    }

    public function terms(): void
    {
        BaseController::viewWithCsp('termOfUse');
    }

    public function accountSetting($id, $mobile, $email, $country, $famCode): void
    {


        $accountData = [
            'name' => checkInput($id),
            'mobile' => checkInput($mobile),
            'email' => checkInput($email),
            'country' => checkInput($country),
            'famCode' => checkInput($famCode),
            'id' => checkInput($_SESSION['ID'])
        ];



        if ($_SESSION['id'] == $id) {
            BaseController::viewWithCsp('accountSetting', ['accountData' => $accountData]);
        }
    }


    public function accountSettingPost()
    {

        // Sanitise the data and get the cleaned data

        $dataToCheck =  [
            'min' => [2, 2, 2],
            'max' => [20, 16, 30],
            'data' => [
                'country',
                'mobile',
                'email'
            ]
        ];

        $cleanData = LoginUtility::getSanitisedInputData($_POST, $dataToCheck);

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

    public static function checking()
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        $maxDuration = 300; // 5 minutes
        $startTime = time();

        while (true) {
            $getUnpublishedPost = Post::getUnpublishedPost();
            printArr($getUnpublishedPost);
            echo ": heartbeat\n\n";
        }



        // Infinite loop to keep the connection alive
        // while (time() - $startTime < $maxDuration) {
        //     // Check if the client has disconnected
        //     if (connection_aborted()) {
        //         break;
        //     }

        //     // // Fetch unpublished posts
        //     // $getUnpublishedPost = Post::getUnpublishedPost();

        //     // foreach ($getUnpublishedPost as $post) {
        //     //     $response = $post;
        //     //     $postNo = $post['id'];

        //     //     // Send the event
        //     //     self::msgServerSent(
        //     //         data: $response,
        //     //         id: $postNo,
        //     //         event: 'updatePost'
        //     //     );

        //     // Post::updatePostByStatusAsPublished($postNo);
        //     // }

        //     // Sleep for a short time before the next iteration to prevent overloading


        //     // Send a heartbeat to keep the connection alive

        // ob_flush();
        // flush();

        //        sleep(1);
        // }
    }
}

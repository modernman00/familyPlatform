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

    public function accountSetting(): void
    {
        try {

            $accountData = parent::membersData();

            parent::viewWithCsp('accountSetting', ['accountData' => $accountData]);

        } catch (\Throwable $th) {
            Utility::showError($th);
        }


    }


    public function accountSettingPost()
    {

        // Sanitise the data and get the cleaned data

        $dataToCheck = [
            'min' => [2, 2, 2],
            'max' => [20, 16, 30],
            'data' => [
                'country',
                'mobile',
                'email'
            ]
        ];
      

        $tableData = [

            'email' => $_POST['email'],
            'country' => $_POST['country'],
            'mobile' => $_POST['mobile'],
         
        ];
        $cleanData = LoginUtility::getSanitisedInputData($tableData, $dataToCheck);
        $cleanData['id'] = cleanSession($_SESSION['id']);

        $updateAccountSettingClass = new Update('contact');
        $updateAccountSettingClass->updateMultiplePOST($cleanData, 'id');

        // SUBMIT THE SPOUSAL INFORMATION

        // CHECK IF THE SELECT BOX IS SET TO Yes
        if ($cleanData['maritalStatus'] === 'Yes - Add Husband' || $cleanData['maritalStatus'] === 'Yes - Add Wife') {

            define('NOT_SET', 'not defined');

            $tableData = [

                'spous_name' => $_POST['spouse_name'] ?? "NOT_SET",
                'spous_email' => $_POST['spouse_email'] ?? "NOT_SET",
                'spous_mobile' => $_POST['spouse_mobile'] ?? "NOT_SET",
                // 'spous_maidenName' => $_POST['spouse_maidenName'] ?? "NOT_SET",
                'id' => cleanSession($_SESSION['id'])
            ];
            $cleanSpouseData = LoginUtility::getSanitisedInputData($tableData, $dataToCheck);

            $updateAccountSettingClass = new Update('otherFamily');
            $updateAccountSettingClass->updateMultiplePOST($cleanSpouseData, 'id');
        }

        //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION



        $kidsCount = (int) $_POST['children'];
        $siblingsCount = (int) $_POST['sibling'];

        unset($_POST['mobile'], $_POST['email'], $_POST['country'], $_POST['children'], $_POST['sibling'], 
        $_POST['spouse_name'], $_POST['spouse_email'], $_POST['spouse_mobile'], $_POST['spouse_maidenName'], $_POST['maritalStatus']);

        $cleanChildrenSiblingData = LoginUtility::getSanitisedInputData($_POST, $dataToCheck);


        if ($kidsCount > 0) {
            processKidSibling('children', $kidsCount, $cleanChildrenSiblingData);
        }

        if ($siblingsCount > 0) {
            processKidSibling('sibling', $siblingsCount, $cleanChildrenSiblingData);
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

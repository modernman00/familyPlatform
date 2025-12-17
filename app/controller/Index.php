<?php

namespace App\Controller;

use App\controller\BaseController;
use App\model\Post;
use Src\LoginUtility;
use Src\{Utility, Select, UpdateFn, SelectFn};

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

        try {

            $_POST['id'] = cleanSession($_SESSION['id']);

            $allowed = ['mobile', 'email', 'country', 'occupation']; // add yours
            $updates = [];

            foreach ($allowed as $field) {
                if (isset($_POST[$field])) {
                    $val = trim((string) $_POST[$field]);
                    $updates[$field] = $val;
                }
            }
            // if $update is not empty update the database
            if (!empty($updates)) {
                $updates['id'] = $_POST['id'];
                $data = LoginUtility::getSanitisedInputData($updates);
                UpdateFn::updateMultiple('contact', $data, 'id');
            }

            if (isset($_POST['occupation'])) {
                $data = [
                    'occupation' => checkInput($_POST['occupation']),
                    'id' => $_POST['id']
                ];
                UpdateFn::updateMultiple('contact', $data, 'id');
            }

            // SUBMIT THE SPOUSAL INFORMATION

            // CHECK IF THE SELECT BOX IS SET TO Yes
            if ($_POST['maritalStatus'] === 'Yes - Add Husband' || $_POST['maritalStatus'] === 'Yes - Add Wife') {

                // --- Spouse fields (only if present) ---
                $spouseAllowed = ['spouse_name', 'spouse_email', 'spouse_mobile', 'maiden_name'];
                $spouse = [];
                foreach ($spouseAllowed as $f) {
                    if (isset($_POST[$f]))
                        $spouse[$f] = trim((string) $_POST[$f]);
                }
                if (!empty($spouse)) {
                    $spouse['id'] = $_POST['id'];
                    $cleanSpouseData = LoginUtility::getSanitisedInputData($spouse);

                   UpdateFn::updateMultiple('otherFamily', $cleanSpouseData, 'id');
                }
            }
            //SUBMIT BOTH THE KIDS AND SIBLING INFORMATION

            if (isset($_POST['children']) || isset($_POST['sibling'])) {

                $kidsCount = (int) $_POST['children'];
                $siblingsCount = (int) $_POST['sibling'];
                unsetPostData($_POST, ['email', 'mobile', 'country', 'occupation', 'spouse_name', 'spouse_email', 'spouse_mobile', 'maiden_name', 'children', 'sibling', 'maritalStatus', 'button']);
                

                if ($kidsCount > 0) {

                    $childrenData= [];
                    foreach ($_POST as $key => $value) {
                        if (str_contains($key, 'children')) {
                            $childrenData[$key] = $value;
                        }
                    }
                    $childrenData['id'] = $_POST['id'];

                    $cleanChildrenData = LoginUtility::getSanitisedInputData($childrenData);

                    processKidSibling('children', $kidsCount, $cleanChildrenData);
                }

                if ($siblingsCount > 0) {
                    $siblingsData= [];
                    foreach ($_POST as $key => $value) {
                        if (str_contains($key, 'sibling')) {
                            $siblingsData[$key] = $value;
                        }
                    }
                    $siblingsData['id'] = $_POST['id'];

                    $cleanSiblingData = LoginUtility::getSanitisedInputData($siblingsData);
                    processKidSibling('sibling', $siblingsCount, $cleanSiblingData);
                }
            }

            msgSuccess(200, "New Update was successfully submitted");
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public static function getEmails()
    {
        $result = SelectFn::selectColumnByIdentifier('account', 'email', 'status', 'approved');

        msgSuccess(200, $result);

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

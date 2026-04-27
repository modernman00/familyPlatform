<?php 

namespace App\controller\members;

use App\controller\BaseController;
use Src\{Utility, LoginUtility, UpdateFn, UpdateData };



class SettingController extends BaseController
{
    public function index(): void
    {
          
  
        try {
            // This is a protected route — enforce auth here explicitly
            parent::__construct();

            $accountData = parent::membersData();

            Utility::view('/member/accountSetting', ['accountData' => $accountData]);

        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }


    public function post()
    {
        // This is a protected route — enforce auth here explicitly
        parent::__construct();

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

    
}

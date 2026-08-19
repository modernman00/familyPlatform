<?php
namespace App\controller\members;

use App\controller\BaseController;
use Src\{Utility, LoginUtility, UpdateFn, UpdateData };



final class SettingController extends BaseController
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


    /**
     * @return void
     */
    public function post()
    {
        // This is a protected route — enforce auth here explicitly
        parent::__construct();

        // Sanitise the data and get the cleaned data

        try {
            $_POST['id'] = cleanSession((string)$_SESSION['id']);

            // Handle the new tabs (Password, Preferences, Privacy)
            if (isset($_POST['action'])) {
                $action = $_POST['action'];

                // 1. Change Password
                if ($action === 'updatePassword') {
                    $currentPassword = $_POST['current_password'] ?? '';
                    $newPassword = $_POST['new_password'] ?? '';
                    $confirmPassword = $_POST['confirm_password'] ?? '';

                    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                        msgException(400, "All password fields are required.");
                        return;
                    }
                    if ($newPassword !== $confirmPassword) {
                        msgException(400, "New passwords do not match.");
                        return;
                    }
                    if (strlen($newPassword) < 6) {
                        msgException(400, "Password must be at least 6 characters.");
                        return;
                    }

                    // Get current password hash
                    $user = \App\model\SingleCustomerData::getCustById($_POST['id'], 'login');
                    if (empty($user) || !password_verify($currentPassword, $user[0]['password'])) {
                        msgException(400, "Current password is incorrect.");
                        return;
                    }

                    // Update password
                    $hashedPassword = \hashPassword($newPassword);
                    UpdateFn::updateMultiple('login', ['password' => $hashedPassword, 'id' => $_POST['id']], 'id');

                    msgSuccess(200, "Password successfully updated.");
                    return;
                }

                // 2. Preferences
                if ($action === 'updatePreferences') {
                    $data = [
                        'id' => $_POST['id'],
                        'email_notifications' => isset($_POST['email_notifications']) ? 'on' : 'off',
                        'sms_notifications' => isset($_POST['sms_notifications']) ? 'on' : 'off',
                    ];
                    UpdateFn::updateMultiple('contact', $data, 'id');
                    msgSuccess(200, "Preferences successfully updated.");
                    return;
                }

                // 3. Privacy
                if ($action === 'updatePrivacy') {
                    $data = [
                        'id' => $_POST['id'],
                        'two_factor_auth' => isset($_POST['two_factor_auth']) ? 'on' : 'off',
                        'profile_visibility' => $_POST['profile_visibility'] ?? 'Private',
                        'show_my_profile' => isset($_POST['show_my_profile']) ? 'on' : 'off',
                        'data_sharing' => isset($_POST['data_sharing']) ? 'on' : 'off',
                    ];
                    UpdateFn::updateMultiple('contact', $data, 'id');
                    msgSuccess(200, "Privacy settings successfully updated.");
                    return;
                }
            }

            // Original Profile Form Update logic
            $allowedContact = ['mobile', 'email', 'country', 'occupation'];
            $updatesContact = [];
            foreach ($allowedContact as $field) {
                if (isset($_POST[$field])) {
                    $val = trim((string) $_POST[$field]);
                    $updatesContact[$field] = $val;
                }
            }
            if (!empty($updatesContact)) {
                $updatesContact['id'] = $_POST['id'];
                $cleanContact = LoginUtility::getSanitisedInputData($updatesContact);
                UpdateFn::updateMultiple('contact', $cleanContact, 'id');
            }

            // Personal table updates (firstName, lastName, marital_status)
            $allowedPersonal = ['firstName', 'lastName', 'marital_status'];
            $updatesPersonal = [];
            foreach ($allowedPersonal as $field) {
                if (isset($_POST[$field])) {
                    $val = trim((string) $_POST[$field]);
                    $updatesPersonal[$field] = $val;
                }
            }
            if (!empty($updatesPersonal)) {
                $updatesPersonal['id'] = $_POST['id'];
                $cleanPersonal = LoginUtility::getSanitisedInputData($updatesPersonal);
                UpdateFn::updateMultiple('personal', $cleanPersonal, 'id');

                // Update session name if changed
                if (isset($cleanPersonal['firstName'])) $_SESSION['fName'] = $cleanPersonal['firstName'];
                if (isset($cleanPersonal['lastName'])) $_SESSION['lName'] = $cleanPersonal['lastName'];
            }

            // SUBMIT THE SPOUSAL INFORMATION

            // CHECK IF THE SELECT BOX IS SET TO Yes
            if (($_POST['maritalStatus'] ?? null) === 'Yes - Add Husband' || ($_POST['maritalStatus'] ?? null) === 'Yes - Add Wife') {

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

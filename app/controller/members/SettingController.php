<?php
namespace App\controller\members;

use App\controller\BaseController;
use Src\{Utility, LoginUtility, UpdateFn, UpdateData, Db};
use Src\functionality\middleware\FileUploadProcess;



final class SettingController extends BaseController
{
    public function index(): void
    {
          
  
        try {
            // This is a protected route — enforce auth here explicitly
            parent::__construct();

            $accountData = parent::membersData();

            $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());
            $pendingFamilyRequest = $approvalService->getPendingRequestForUser((string)$accountData['id']);

            Utility::view('/member/accountSetting', [
                'accountData' => $accountData,
                'pendingFamilyRequest' => $pendingFamilyRequest
            ]);

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

                // 4. Stay Solo / Generate New Family Code
                if ($action === 'staySoloCode') {
                    $userId = (string)$_POST['id'];
                    $memberData = parent::findMemberById($userId);
                    $surname = (string)($_POST['surname'] ?? ($memberData['lastName'] ?? ''));
                    if (empty($surname)) {
                        $surname = 'Family';
                    }

                    $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());
                    $newCode = $approvalService->switchUserToSoloFamily($userId, $surname, $memberData);

                    // Update session and return new code
                    $_SESSION['famCode'] = $newCode;

                    msgSuccess(200, "Successfully branched out! Your new solo Family Code is {$newCode}.", [
                        'family_code' => $newCode
                    ]);
                    return;
                }

                // 5. Request to Join Another Family Network
                if ($action === 'requestJoinFamily') {
                    $userId = (string)$_POST['id'];
                    $targetCode = trim((string)($_POST['family_code'] ?? ''));
                    $inviterFirstName = trim((string)($_POST['inviter_first_name'] ?? ''));
                    $inviterLastName = trim((string)($_POST['inviter_last_name'] ?? ''));
                    $inviterContact = trim((string)($_POST['inviter_email_or_mobile'] ?? ''));

                    if (empty($targetCode) || empty($inviterFirstName) || empty($inviterLastName) || empty($inviterContact)) {
                        msgException(400, "All fields are required to join another family network.");
                        return;
                    }

                    $cleanTargetCode = strtoupper(trim(str_replace('#', '', $targetCode)));
                    $currentCode = strtoupper(trim(str_replace('#', '', (string)($_SESSION['famCode'] ?? ''))));

                    if ($cleanTargetCode === $currentCode) {
                        msgException(400, "You are already a member of family code {$cleanTargetCode}.");
                        return;
                    }

                    $pdo = \Src\Db::connect2();
                    $approvalService = new \App\service\FamilyCodeApprovalService($pdo);

                    if (!$approvalService->familyCodeExists($cleanTargetCode)) {
                        msgException(404, "Family code '{$targetCode}' was not found. Please verify the code with your family member.");
                        return;
                    }

                    // Check for existing pending request
                    $existingReq = $approvalService->getPendingRequestForUser($userId);
                    if ($existingReq) {
                        msgException(409, "You already have a pending transfer request for family code {$existingReq['family_code']}. Please cancel it before requesting a new one.");
                        return;
                    }

                    // Verify inviter exists in target family
                    $inviter = $approvalService->findMatchingInviter(
                        $cleanTargetCode,
                        $inviterFirstName,
                        $inviterLastName,
                        $inviterContact
                    );

                    if (!$inviter) {
                        msgException(422, "Could not find a matching family member in family {$cleanTargetCode} with the provided details. Please verify their name and contact information.");
                        return;
                    }

                    // Create approval request
                    $approvalData = $approvalService->createApprovalRequest(
                        $userId,
                        $cleanTargetCode,
                        $inviterFirstName,
                        $inviterLastName,
                        $inviterContact
                    );

                    // Dispatch notification to inviter
                    $notificationService = new \App\service\NotificationService($pdo);
                    $newUserInfo = [
                        'id' => $userId,
                        'firstName' => $_SESSION['fName'] ?? '',
                        'lastName' => $_SESSION['lName'] ?? '',
                        'email' => $_SESSION['email'] ?? '',
                        'mobile' => ''
                    ];

                    $notificationService->sendFamilyApprovalNotification(
                        (string)$inviter['id'],
                        $newUserInfo,
                        (int)$approvalData['request_id'],
                        $cleanTargetCode,
                        (string)$approvalData['approval_token']
                    );

                    msgSuccess(200, "Transfer request submitted! An approval request has been sent to {$inviterFirstName} {$inviterLastName}. Your family code will update as soon as they approve.");
                    return;
                }

                // 6. Cancel pending family join/transfer request
                if ($action === 'cancelFamilyRequest') {
                    $userId = (string)$_POST['id'];
                    $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());
                    $approvalService->cancelPendingRequest($userId);

                    msgSuccess(200, "Pending family transfer request has been cancelled.");
                    return;
                }

                // 7. Update Secondary / Maternal / Maiden Family Code (otherFamCode)
                if ($action === 'updateSecondaryFamilyCode') {
                    $userId = (string)$_POST['id'];
                    $rawCode = trim((string)($_POST['otherFamCode'] ?? ''));
                    $cleanCode = strtoupper(trim(str_replace('#', '', $rawCode)));

                    $currentPrimaryCode = strtoupper(trim((string)($_SESSION['famCode'] ?? '')));

                    if (!empty($cleanCode) && $cleanCode === $currentPrimaryCode) {
                        msgException(422, "Your secondary/maternal family code cannot be identical to your primary family code ({$currentPrimaryCode}). It is meant for your maternal kin or biological maiden family.");
                        return;
                    }

                    $db = Db::connect2();
                    $chkOther = $db->prepare("SELECT id FROM otherFamily WHERE id = ?");
                    $chkOther->execute([$userId]);

                    if ($chkOther->fetch()) {
                        $upd = $db->prepare("UPDATE otherFamily SET otherFamCode = ? WHERE id = ?");
                        $upd->execute([$cleanCode !== '' ? $cleanCode : null, $userId]);
                    } else {
                        $ins = $db->prepare("INSERT INTO otherFamily (id, otherFamCode) VALUES (?, ?)");
                        $ins->execute([$userId, $cleanCode !== '' ? $cleanCode : null]);
                    }

                    if (empty($cleanCode)) {
                        msgSuccess(200, "Secondary family code has been cleared.");
                    } else {
                        msgSuccess(200, "Secondary / maternal family code successfully updated to {$cleanCode}! The Kinship Engine will now discover maternal kin and in-laws for you.", [
                            'otherFamCode' => $cleanCode
                        ]);
                    }
                    return;
                }
            }

            // Handle Profile Image Upload
            if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
                // The filename will be checked and sanitized within FileUploadProcess::process
                $uploadResult = FileUploadProcess::process([], 'profile', 'img', 'resources/images/profile/', 'images');
                
                $fileName = checkInputImage($_FILES['img']['name']);
                $sanitizedFileName = $uploadResult['sanitisedData']['profile']['img'] ?? $fileName;

                UpdateFn::makeUpdateFn('profilePics',[
                    'img'=> $sanitizedFileName, 
                    'id'=> $_SESSION['id']], 
                    'id', 'AND'
                );
                
                // Keep session updated for immediate UI reflection if needed
                $_SESSION['profilePics'] = $sanitizedFileName;
            }

            // Original Profile Form Update logic
            $allowedContact = ['mobile', 'email', 'country'];
            $updatesContact = [];
            foreach ($allowedContact as $field) {
                if (isset($_POST[$field])) {
                    $val = trim((string) $_POST[$field]);
                    $updatesContact[$field] = $val;
                }
            }
            if (!empty($updatesContact)) {
                $updatesContact['id'] = $_POST['id'];
                $cleanContact = LoginUtility::getSanitisedInputData($updatesContact, null, ['mobile', 'email', 'country']);
                UpdateFn::updateMultiple('contact', $cleanContact, 'id');
            }

            // Work Information Update logic
            $allowedWork = ['occupation', 'employmentStatus'];
            $updatesWork = [];
            foreach ($allowedWork as $field) {
                if (isset($_POST[$field])) {
                    $val = trim((string) $_POST[$field]);
                    $updatesWork[$field] = $val;
                }
            }
            if (!empty($updatesWork)) {
                $updatesWork['id'] = $_POST['id'];
                $cleanWork = LoginUtility::getSanitisedInputData($updatesWork, null, ['occupation', 'employmentStatus']);
                UpdateFn::updateMultiple('work', $cleanWork, 'id');
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
                $cleanPersonal = LoginUtility::getSanitisedInputData($updatesPersonal, null, ['firstName', 'lastName', 'marital_status']);
                UpdateFn::updateMultiple('personal', $cleanPersonal, 'id');

                // Update session name if changed
                if (isset($cleanPersonal['firstName'])) $_SESSION['fName'] = $cleanPersonal['firstName'];
                if (isset($cleanPersonal['lastName'])) $_SESSION['lName'] = $cleanPersonal['lastName'];

                // Sync Name changes to the Graph (family_nodes)
                if (isset($cleanPersonal['firstName']) || isset($cleanPersonal['lastName'])) {
                    $db = Db::connect2();
                    $stmt = $db->prepare("UPDATE family_nodes SET first_name = COALESCE(?, first_name), last_name = COALESCE(?, last_name) WHERE user_id = ?");
                    $stmt->execute([
                        $cleanPersonal['firstName'] ?? null, 
                        $cleanPersonal['lastName'] ?? null, 
                        $_POST['id']
                    ]);
                }
            }

            // OtherFamily table updates (Father, Mother, Spouse, etc.)
            $fatherFirstName = trim((string)($_POST['father_first_name'] ?? ''));
            $fatherLastName  = trim((string)($_POST['father_last_name'] ?? ''));
            $fatherFullName  = trim("$fatherFirstName $fatherLastName");
            if (empty($fatherFullName) && isset($_POST['father_name'])) {
                $fatherFullName = trim((string)$_POST['father_name']);
            }

            $motherFirstName = trim((string)($_POST['mother_first_name'] ?? ''));
            $motherLastName  = trim((string)($_POST['mother_last_name'] ?? ''));
            $motherFullName  = trim("$motherFirstName $motherLastName");
            if (empty($motherFullName) && isset($_POST['mother_name'])) {
                $motherFullName = trim((string)$_POST['mother_name']);
            }

            $updatesOtherFamily = [];
            if (isset($_POST['father_first_name']) || isset($_POST['father_name'])) {
                $updatesOtherFamily['father_name'] = $fatherFullName;
            }
            if (isset($_POST['father_email'])) {
                $updatesOtherFamily['father_email'] = trim((string)$_POST['father_email']);
            }
            if (isset($_POST['father_mobile'])) {
                $updatesOtherFamily['father_mobile'] = trim((string)$_POST['father_mobile']);
            }
            if (isset($_POST['mother_first_name']) || isset($_POST['mother_name'])) {
                $updatesOtherFamily['mother_name'] = $motherFullName;
            }
            if (isset($_POST['mother_maiden']) || isset($_POST['maiden_name'])) {
                $updatesOtherFamily['maiden_name'] = trim((string)($_POST['mother_maiden'] ?? $_POST['maiden_name']));
            }
            if (isset($_POST['mother_email'])) {
                $updatesOtherFamily['mother_email'] = trim((string)$_POST['mother_email']);
            }
            if (isset($_POST['mother_mobile'])) {
                $updatesOtherFamily['mother_mobile'] = trim((string)$_POST['mother_mobile']);
            }
            if (isset($_POST['spouse_name'])) {
                $updatesOtherFamily['spouse_name'] = trim((string)$_POST['spouse_name']);
            }
            if (isset($_POST['spouse_email'])) {
                $updatesOtherFamily['spouse_email'] = trim((string)$_POST['spouse_email']);
            }
            if (isset($_POST['spouse_mobile'])) {
                $updatesOtherFamily['spouse_mobile'] = trim((string)$_POST['spouse_mobile']);
            }
            if (isset($_POST['otherFamCode'])) {
                $updatesOtherFamily['otherFamCode'] = strtoupper(trim(str_replace('#', '', (string)$_POST['otherFamCode'])));
            }

            if (!empty($updatesOtherFamily)) {
                $updatesOtherFamily['id'] = $_POST['id'];
                $cleanOtherFamily = LoginUtility::getSanitisedInputData($updatesOtherFamily, null, [
                    'otherFamCode',
                    'father_name', 'father_email', 'father_mobile',
                    'mother_name', 'maiden_name', 'mother_email', 'mother_mobile',
                    'spouse_name', 'spouse_email', 'spouse_mobile'
                ]);

                $db = Db::connect2();
                $chkOther = $db->prepare("SELECT id FROM otherFamily WHERE id = ?");
                $chkOther->execute([$_POST['id']]);
                if ($chkOther->fetch()) {
                    UpdateFn::updateMultiple('otherFamily', $cleanOtherFamily, 'id');
                } else {
                    // Column names are restricted to this fixed allowlist so the
                    // INSERT column list can never contain user-controlled text;
                    // every value is still bound through a "?" placeholder.
                    $allowedCols = [
                        'id', 'otherFamCode',
                        'father_name', 'father_email', 'father_mobile',
                        'mother_name', 'maiden_name', 'mother_email', 'mother_mobile',
                        'spouse_name', 'spouse_email', 'spouse_mobile',
                    ];
                    // array_intersect keeps only entries that appear in the
                    // hard-coded $allowedCols, so $cols is a subset of literal
                    // strings regardless of the request.
                    $cols = array_values(array_intersect($allowedCols, array_keys($cleanOtherFamily)));
                    $values = array_map(static fn(string $c): mixed => $cleanOtherFamily[$c], $cols);
                    $placeholders = array_fill(0, count($cols), '?');
                    $sql = "INSERT INTO otherFamily (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")"; // nosemgrep: php.lang.security.injection.tainted-sql-string.tainted-sql-string -- column names from fixed $allowedCols allowlist; values bound via placeholders
                    $insStmt = $db->prepare($sql); // nosemgrep: php.lang.security.injection.tainted-callable.tainted-callable -- $sql built from allowlist, not request data
                    $insStmt->execute($values);
                }
            }

            // ---------------------------------------------------------
            // NEW GRAPH SYNCHRONIZATION FOR RELATIVES
            // ---------------------------------------------------------
            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (!empty($familyCode)) {
                $db = Db::connect2();
                $db->beginTransaction();

                try {
                    // 1. Get Base Node
                    $stmt = $db->prepare("SELECT id, generation_level, gender FROM family_nodes WHERE user_id = ? AND family_code = ?");
                    $stmt->execute([$_POST['id'], $familyCode]);
                    $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($baseNode) {
                        $baseNodeId = (int)$baseNode['id'];
                        $genLevel = (int)$baseNode['generation_level'];
                        $userGender = $baseNode['gender'] ?? 'Male';

                        // 2. Spouse
                        if (($_POST['maritalStatus'] ?? null) === 'Yes - Add Husband' || ($_POST['maritalStatus'] ?? null) === 'Yes - Add Wife') {
                            $spouseName = trim((string)($_POST['spouse_name'] ?? ''));
                            if (!empty($spouseName)) {
                                $spouseGender = ($_POST['maritalStatus'] === 'Yes - Add Husband') ? 'Male' : 'Female';
                                $sAvatar = ($spouseGender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';
                                $sEmail = trim((string)($_POST['spouse_email'] ?? ''));
                                $sMobile = trim((string)($_POST['spouse_mobile'] ?? ''));
                                
                                $insNode = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio, email, mobile) VALUES (?, ?, ?, ?, ?, 'Partner/Spouse', ?, ?)");
                                $insNode->execute([$familyCode, $spouseName, $spouseGender, $genLevel, $sAvatar, !empty($sEmail) ? $sEmail : null, !empty($sMobile) ? $sMobile : null]);
                                $partnerId = (int)$db->lastInsertId();

                                $insUnion = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                                $insUnion->execute([$familyCode, $baseNodeId, $partnerId]);
                            }
                        }

                        // 3. Parents Graph Node Sync (UPSERT)
                        if (!empty($fatherFullName) || !empty($motherFullName)) {
                            $checkParent = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ?");
                            $checkParent->execute([$baseNodeId]);
                            $pUnionRow = $checkParent->fetch();

                            $fFirst = !empty($fatherFirstName) ? $fatherFirstName : (!empty($fatherFullName) ? $fatherFullName : 'Unknown Father');
                            $fLast  = !empty($fatherLastName) ? $fatherLastName : '';
                            $fEmail = trim((string)($_POST['father_email'] ?? ''));
                            $fMobile = trim((string)($_POST['father_mobile'] ?? ''));

                            $mFirst = !empty($motherFirstName) ? $motherFirstName : (!empty($motherFullName) ? $motherFullName : 'Unknown Mother');
                            $mLast  = !empty($motherLastName) ? $motherLastName : '';
                            $mEmail = trim((string)($_POST['mother_email'] ?? ''));
                            $mMobile = trim((string)($_POST['mother_mobile'] ?? ''));

                            if ($pUnionRow && !empty($pUnionRow['union_id'])) {
                                $existingUnionId = (int)$pUnionRow['union_id'];
                                $stmtUnion = $db->prepare("SELECT partner_1_id, partner_2_id FROM family_unions WHERE id = ?");
                                $stmtUnion->execute([$existingUnionId]);
                                $unionNodes = $stmtUnion->fetch();

                                if ($unionNodes) {
                                    $p1Id = (int)$unionNodes['partner_1_id'];
                                    $p2Id = (int)$unionNodes['partner_2_id'];

                                    if ($p1Id > 0 && !empty($fatherFullName)) {
                                        $updF = $db->prepare("UPDATE family_nodes SET first_name = ?, last_name = ?, email = COALESCE(NULLIF(?, ''), email), mobile = COALESCE(NULLIF(?, ''), mobile) WHERE id = ?");
                                        $updF->execute([$fFirst, $fLast, !empty($fEmail) ? $fEmail : null, !empty($fMobile) ? $fMobile : null, $p1Id]);
                                    }

                                    if ($p2Id > 0 && !empty($motherFullName)) {
                                        $updM = $db->prepare("UPDATE family_nodes SET first_name = ?, last_name = ?, email = COALESCE(NULLIF(?, ''), email), mobile = COALESCE(NULLIF(?, ''), mobile) WHERE id = ?");
                                        $updM->execute([$mFirst, $mLast, !empty($mEmail) ? $mEmail : null, !empty($mMobile) ? $mMobile : null, $p2Id]);
                                    }
                                }
                            } else {
                                $insF = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile) VALUES (?, ?, ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Father', ?, ?)");
                                $insF->execute([$familyCode, $fFirst, $fLast, $genLevel - 1, !empty($fEmail) ? $fEmail : null, !empty($fMobile) ? $fMobile : null]);
                                $fId = (int)$db->lastInsertId();

                                $insM = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile) VALUES (?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother', ?, ?)");
                                $insM->execute([$familyCode, $mFirst, $mLast, $genLevel - 1, !empty($mEmail) ? $mEmail : null, !empty($mMobile) ? $mMobile : null]);
                                $mId = (int)$db->lastInsertId();

                                $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                                $insU->execute([$familyCode, $fId, $mId]);
                                $parentUnionId = (int)$db->lastInsertId();

                                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                                $insLink->execute([$parentUnionId, $baseNodeId]);
                            }
                        }

                        // 4. Children
                        $kidsCount = (int)($_POST['children'] ?? 0);
                        if ($kidsCount > 0) {
                            for ($i = 1; $i <= $kidsCount; $i++) {
                                $cFirst = trim((string)($_POST["children_first_name$i"] ?? ''));
                                $cLast  = trim((string)($_POST["children_last_name$i"] ?? ''));
                                $cName  = trim("$cFirst $cLast");
                                if (empty($cName) && isset($_POST["children_name$i"])) {
                                    $cName = trim((string)$_POST["children_name$i"]);
                                }
                                if (empty($cFirst) && !empty($cName)) {
                                    $cParts = explode(' ', $cName, 2);
                                    $cFirst = $cParts[0];
                                    $cLast  = $cParts[1] ?? '';
                                }
                                if (empty($cFirst) && empty($cName)) continue;
                                
                                $cOption = $_POST["children_option$i"] ?? '';
                                $cEmail = trim((string)($_POST["children_email$i"] ?? ''));

                                $unionIdToLink = null;
                                if ($cOption === 'With Spouse') {
                                    $findUnion = $db->prepare("SELECT id FROM family_unions WHERE family_code = ? AND (partner_1_id = ? OR partner_2_id = ?) AND union_type = 'married' LIMIT 1");
                                    $findUnion->execute([$familyCode, $baseNodeId, $baseNodeId]);
                                    $u = $findUnion->fetch();
                                    if ($u) {
                                        $unionIdToLink = (int)$u['id'];
                                    } else {
                                        $spGender = ($userGender === 'Male') ? 'Female' : 'Male';
                                        $spAvatar = ($spGender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';
                                        
                                        $insSp = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Unknown Spouse', ?, ?, ?, 'Unknown Spouse')");
                                        $insSp->execute([$familyCode, $spGender, $genLevel, $spAvatar]);
                                        $spId = (int)$db->lastInsertId();

                                        $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                                        $insU->execute([$familyCode, $baseNodeId, $spId]);
                                        $unionIdToLink = (int)$db->lastInsertId();
                                    }
                                } else {
                                    $spGender = ($userGender === 'Male') ? 'Female' : 'Male';
                                    $spAvatar = ($spGender === 'Male') ? '/resources/images/profile/avatarM.png' : '/resources/images/profile/avatarF.png';
                                    
                                    $insSp = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Unknown Partner', ?, ?, ?, 'Unknown Partner')");
                                    $insSp->execute([$familyCode, $spGender, $genLevel, $spAvatar]);
                                    $spId = (int)$db->lastInsertId();

                                    $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'other', 1)");
                                    $insU->execute([$familyCode, $baseNodeId, $spId]);
                                    $unionIdToLink = (int)$db->lastInsertId();
                                }

                                $cAvatar = '/resources/images/profile/avatarM.png';
                                $insChild = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email) VALUES (?, ?, ?, 'Male', ?, ?, 'Child', ?)");
                                $insChild->execute([$familyCode, $cFirst, $cLast, $genLevel + 1, $cAvatar, !empty($cEmail) ? $cEmail : null]);
                                $childNodeId = (int)$db->lastInsertId();

                                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                                $insLink->execute([$unionIdToLink, $childNodeId]);
                            }
                        }

                        // 5. Siblings
                        $siblingsCount = (int)($_POST['sibling'] ?? 0);
                        if ($siblingsCount > 0) {
                            $checkParent = $db->prepare("SELECT union_id FROM family_node_children WHERE child_id = ? LIMIT 1");
                            $checkParent->execute([$baseNodeId]);
                            $pUnionRow = $checkParent->fetch();
                            $pUnionId = $pUnionRow ? (int)$pUnionRow['union_id'] : null;

                            if (!$pUnionId) {
                                $insF = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Unknown Father', 'Male', ?, '/resources/images/profile/avatarM.png', 'Father')");
                                $insF->execute([$familyCode, $genLevel - 1]);
                                $fId = (int)$db->lastInsertId();

                                $insM = $db->prepare("INSERT INTO family_nodes (family_code, first_name, gender, generation_level, avatar_url, bio) VALUES (?, 'Unknown Mother', 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother')");
                                $insM->execute([$familyCode, $genLevel - 1]);
                                $mId = (int)$db->lastInsertId();

                                $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                                $insU->execute([$familyCode, $fId, $mId]);
                                $pUnionId = (int)$db->lastInsertId();

                                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                                $insLink->execute([$pUnionId, $baseNodeId]);
                            }

                            for ($i = 1; $i <= $siblingsCount; $i++) {
                                $sFirst = trim((string)($_POST["sibling_first_name$i"] ?? ''));
                                $sLast  = trim((string)($_POST["sibling_last_name$i"] ?? ''));
                                $sName  = trim("$sFirst $sLast");
                                if (empty($sName) && isset($_POST["sibling_name$i"])) {
                                    $sName = trim((string)$_POST["sibling_name$i"]);
                                }
                                if (empty($sFirst) && !empty($sName)) {
                                    $sParts = explode(' ', $sName, 2);
                                    $sFirst = $sParts[0];
                                    $sLast  = $sParts[1] ?? '';
                                }
                                if (empty($sFirst) && empty($sName)) continue;

                                $sEmail = trim((string)($_POST["sibling_email$i"] ?? ''));
                                
                                $insSib = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email) VALUES (?, ?, ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Sibling', ?)");
                                $insSib->execute([$familyCode, $sFirst, $sLast, $genLevel, !empty($sEmail) ? $sEmail : null]);
                                $sibNodeId = (int)$db->lastInsertId();

                                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                                $insLink->execute([$pUnionId, $sibNodeId]);
                            }
                        }
                    }

                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }

            $action = $_POST['action'] ?? '';
            unsetPostData($_POST, [
                'email', 'mobile', 'country', 'occupation', 'employmentStatus',
                'father_first_name', 'father_last_name', 'father_name', 'father_email', 'father_mobile',
                'mother_first_name', 'mother_last_name', 'mother_name', 'mother_maiden', 'maiden_name', 'mother_email', 'mother_mobile',
                'spouse_name', 'spouse_email', 'spouse_mobile', 'children', 'sibling', 'maritalStatus', 'button', 'action'
            ]);

            $successMsg = match ($action) {
                'updateProfile' => 'Profile details successfully updated.',
                'updateParents' => 'Parents information successfully updated.',
                'updateChildren' => 'Children information successfully updated.',
                'updateSiblings' => 'Siblings information successfully updated.',
                'updateMarital' => 'Marital status and spouse details successfully updated.',
                default => 'Changes successfully saved.'
            };

            msgSuccess(200, $successMsg);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    
}

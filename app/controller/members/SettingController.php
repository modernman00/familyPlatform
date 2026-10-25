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
            $birthFamily = (new \App\services\BirthFamilyCodeService(\Src\Db::connect2(), $approvalService))->status((string)$accountData['id']);
            $codeRename = (new \App\services\FamilyCodeRenameService(\Src\Db::connect2()))->statusFor((string)$accountData['id']);

            $passkeys = (new \Src\Select())->selectFn1(
                "SELECT id, credential_id, device_name, created_at, last_used_at FROM user_passkeys WHERE user_id = ? ORDER BY created_at DESC",
                [(string)$accountData['id']]
            );

            // RFC-0008: show-my-age / anniversary choices and the wedding date
            $celebrationPrefs = ['show_age' => false, 'announce_anniversary' => false];
            $weddingDate = null;
            try {
                $celebrationPrefs = \App\services\CelebrationService::preferences((string)$accountData['id']);
                $weddingDate = \App\services\CelebrationService::weddingDate((string)$accountData['id'], (string)($_SESSION['famCode'] ?? ''));
            } catch (\Throwable $e) {
                error_log('[SettingController] Celebration settings unavailable: ' . $e->getMessage());
            }

            Utility::view('/member/accountSetting', [
                'accountData' => $accountData,
                'pendingFamilyRequest' => $pendingFamilyRequest,
                'birthFamily' => $birthFamily,
                'codeRename' => $codeRename,
                'passkeys' => $passkeys,
                'celebrationPrefs' => $celebrationPrefs,
                'weddingDate' => $weddingDate
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

                $normalizeSwitch = static fn(mixed $val): string => in_array(strtolower(trim((string) $val)), ['on', '1', 'true'], true) ? 'on' : 'off';

                $ensureContactRow = static function (string $uid): void {
                    $db = Db::connect2();
                    $chk = $db->prepare('SELECT id FROM contact WHERE id = ? LIMIT 1');
                    $chk->execute([$uid]);
                    if (!$chk->fetch()) {
                        $email = (string) ($_SESSION['auth']['email'] ?? $_SESSION['email'] ?? '');
                        $ins = $db->prepare('INSERT INTO contact (id, email, country, mobile, profile_visibility, show_my_profile, email_notifications, sms_notifications) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $ins->execute([$uid, substr($email, 0, 50), '', '', 'Public', 'on', 'on', 'on']);
                    }
                };

                // 2. Preferences
                if ($action === 'updatePreferences') {
                    $ensureContactRow((string) $_POST['id']);
                    $data = [
                        'id' => $_POST['id'],
                        'email_notifications' => $normalizeSwitch($_POST['email_notifications'] ?? 'off'),
                        'sms_notifications' => $normalizeSwitch($_POST['sms_notifications'] ?? 'off'),
                    ];
                    UpdateFn::updateMultiple('contact', $data, 'id');
                    if (isset($_POST['celebrate_show_age'])) {
                        \App\services\CelebrationService::savePreferences(
                            (string) $_POST['id'],
                            showAge: $normalizeSwitch($_POST['celebrate_show_age']) === 'on',
                            announceAnniversary: null
                        );
                    }
                    msgSuccess(200, "Preferences successfully updated.");
                    return;
                }

                // 3. Privacy
                if ($action === 'updatePrivacy') {
                    $ensureContactRow((string) $_POST['id']);
                    $allowedVisibilities = ['Private', 'Public', 'Family Only'];
                    $visibility = in_array($_POST['profile_visibility'] ?? '', $allowedVisibilities, true)
                        ? (string) $_POST['profile_visibility']
                        : 'Public';
                    $data = [
                        'id' => $_POST['id'],
                        'two_factor_auth' => $normalizeSwitch($_POST['two_factor_auth'] ?? 'off'),
                        'profile_visibility' => $visibility,
                        'show_my_profile' => $normalizeSwitch($_POST['show_my_profile'] ?? 'off'),
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
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    $targetCode = trim((string)($_POST['family_code'] ?? ''));
                    $inviterFirstName = trim((string)($_POST['inviter_first_name'] ?? ''));
                    $inviterLastName = trim((string)($_POST['inviter_last_name'] ?? ''));
                    $inviterContact = trim((string)($_POST['inviter_email_or_mobile'] ?? ''));

                    if ($userId === '') {
                        msgException(401, "Unauthorized: Please log in to request a family transfer.");
                        return;
                    }

                    if (empty($targetCode) || empty($inviterFirstName) || empty($inviterLastName) || empty($inviterContact)) {
                        msgException(400, "All fields are required to join another family network.");
                        return;
                    }

                    $cleanTargetCode = \App\services\FamilyCodeRenameService::resolveAlias(\Src\Db::connect2(), $targetCode);
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
                        // Fall back to family-wide migration request routed to existing approved family members
                        $migrationService = new \App\services\FamilyMigrationService($pdo);
                        $note = "Inviter specified: {$inviterFirstName} {$inviterLastName} ({$inviterContact})";
                        $migRes = $migrationService->createRequest($userId, $cleanTargetCode, null, $note);
                        if ($migRes['success']) {
                            msgSuccess(200, "Migration request submitted to family #{$cleanTargetCode}. Your request has been routed to existing family members to review and approve.", $migRes);
                            return;
                        }
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

                    // Persist approver_id immediately so the inviter can see and manage it on their profile page
                    $approvalService->setApproverId((int)$approvalData['request_id'], (string)$inviter['id']);

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

                    $safeInviterName = htmlspecialchars(trim(($inviter['firstName'] ?? '') . ' ' . ($inviter['lastName'] ?? '')), ENT_QUOTES, 'UTF-8');
                    // nosemgrep: php.lang.security.injection.tainted-sql-string.tainted-sql-string -- Human-readable API success message, not a SQL statement.
                    msgSuccess(200, "Transfer request submitted! An approval request has been sent to {$safeInviterName}. Your family code will refresh as soon as they approve.");
                    return;
                }

                // 5b. Direct Family Migration Request (when discovering family code without inviter details)
                if ($action === 'requestFamilyMigration') {
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    $targetCode = trim((string)($_POST['family_code'] ?? ''));
                    $note = trim((string)($_POST['note'] ?? ''));

                    if ($userId === '') {
                        msgException(401, "Unauthorized: Please log in to request a family migration.");
                        return;
                    }

                    if (empty($targetCode)) {
                        msgException(400, "Please enter the target family code.");
                        return;
                    }

                    $migrationService = new \App\services\FamilyMigrationService(\Src\Db::connect2());
                    $result = $migrationService->createRequest($userId, $targetCode, null, $note !== '' ? $note : null);

                    if (!$result['success']) {
                        msgException(422, $result['message']);
                        return;
                    }

                    msgSuccess(200, $result['message'], $result);
                    return;
                }

                // 6. Cancel pending family join/transfer request
                if ($action === 'cancelFamilyRequest') {
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    $approvalService = new \App\service\FamilyCodeApprovalService(\Src\Db::connect2());
                    $approvalService->cancelPendingRequest($userId);

                    msgSuccess(200, "Pending family transfer request has been cancelled.");
                    return;
                }

                // 6a. RFC-0005: the code creator opens a family poll to change the family code
                if ($action === 'proposeFamilyCodeRename') {
                    $userId = (string)($_SESSION['id'] ?? '');
                    \Src\Limiter::limit('code_rename_' . $userId);
                    try {
                        $proposal = (new \App\services\FamilyCodeRenameService(Db::connect2()))
                            ->propose($userId, (string)($_POST['new_code'] ?? ''));
                    } catch (\DomainException $de) {
                        msgException($de->getCode(), $de->getMessage());
                        return;
                    }

                    $msg = $proposal['status'] === 'applied'
                        ? "Your family code is now #{$proposal['new_code']}."
                        : "Vote opened. The family code changes to #{$proposal['new_code']} automatically if most of your family agrees.";
                    msgSuccess(200, $msg, $proposal);
                    return;
                }

                if ($action === 'cancelFamilyCodeRename') {
                    $userId = (string)($_SESSION['id'] ?? '');
                    try {
                        (new \App\services\FamilyCodeRenameService(Db::connect2()))->cancel($userId);
                    } catch (\DomainException $de) {
                        msgException($de->getCode(), $de->getMessage());
                        return;
                    }
                    msgSuccess(200, 'The family vote was cancelled.');
                    return;
                }

                // 6b. RFC-0004: create a brand-new birth / maiden family code from the maiden name
                if ($action === 'createBirthFamilyCode') {
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    \Src\Limiter::limit('birth_code_' . $userId);
                    $birthService = new \App\services\BirthFamilyCodeService(Db::connect2());
                    try {
                        $created = $birthService->create($userId, (string)($_POST['maiden_name'] ?? ''));
                    } catch (\DomainException $de) {
                        msgException($de->getCode(), $de->getMessage());
                        return;
                    }

                    msgSuccess(200, "Your birth family code is #{$created['code']}. Invite your parents and siblings with it from the Parents and Siblings tabs.", [
                        'otherFamCode' => $created['code'],
                        'origin' => $created['origin'],
                    ]);
                    return;
                }

                // 6c. RFC-0004: ask a verified member of an existing family to link this user as a birth-family member
                if ($action === 'requestBirthFamilyLink') {
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    \Src\Limiter::limit('birth_link_' . $userId);
                    $pdo = Db::connect2();
                    $birthService = new \App\services\BirthFamilyCodeService($pdo);
                    try {
                        $link = $birthService->requestLink(
                            $userId,
                            (string)($_POST['family_code'] ?? ''),
                            trim((string)($_POST['inviter_first_name'] ?? '')),
                            trim((string)($_POST['inviter_last_name'] ?? '')),
                            trim((string)($_POST['inviter_email_or_mobile'] ?? ''))
                        );
                    } catch (\DomainException $de) {
                        msgException($de->getCode(), $de->getMessage());
                        return;
                    }

                    try {
                        (new \App\service\NotificationService($pdo))->sendFamilyApprovalNotification(
                            (string)$link['inviter']['id'],
                            [
                                'id' => $userId,
                                'firstName' => $_SESSION['fName'] ?? '',
                                'lastName' => $_SESSION['lName'] ?? '',
                                'email' => $_SESSION['email'] ?? '',
                                'mobile' => ''
                            ],
                            (int)$link['request']['request_id'],
                            $link['code'],
                            (string)$link['request']['approval_token']
                        );
                    } catch (\Throwable $notifyEx) {
                        error_log('[SettingController] birth link notification failed: ' . $notifyEx->getMessage());
                    }

                    $safeInviter = htmlspecialchars(trim(($link['inviter']['firstName'] ?? '') . ' ' . ($link['inviter']['lastName'] ?? '')), ENT_QUOTES, 'UTF-8');
                    msgSuccess(200, "Request sent! {$safeInviter} has been asked to confirm you belong to #{$link['code']}.", ['family_code' => $link['code']]);
                    return;
                }

                // 7. Update Secondary / Maternal / Maiden Family Code (otherFamCode)
                if ($action === 'updateSecondaryFamilyCode') {
                    $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                    $rawCode = trim((string)($_POST['otherFamCode'] ?? ''));
                    $cleanCode = strtoupper(trim(str_replace('#', '', $rawCode)));

                    $currentPrimaryCode = strtoupper(trim((string)($_SESSION['famCode'] ?? '')));

                    if (!empty($cleanCode) && $cleanCode === $currentPrimaryCode) {
                        msgException(422, "Your secondary/maternal family code cannot be identical to your primary family code ({$currentPrimaryCode}). It is meant for your maternal kin or biological maiden family.");
                        return;
                    }

                    // RFC-0004: the code must be one this user verifiably holds; free text is no longer accepted.
                    $birthService = new \App\services\BirthFamilyCodeService(Db::connect2());
                    try {
                        if ($cleanCode === '') {
                            $birthService->clear($userId);
                        } else {
                            $cleanCode = $birthService->select($userId, $cleanCode);
                        }
                    } catch (\DomainException $de) {
                        msgException($de->getCode(), $de->getMessage());
                        return;
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
                if (!empty($cleanContact['mobile'])) {
                    $cleanContact['mobile'] = \App\classes\PhoneUtility::toE164((string)$cleanContact['mobile'], (string)($cleanContact['country'] ?? ''));
                }
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
                    $colStr = implode(', ', $cols);
                    $placeholderStr = implode(', ', $placeholders);
                    $insertTpl = 'INSERT INTO otherFamily (%s) VALUES (%s)';
                    $sql = sprintf($insertTpl, $colStr, $placeholderStr); // nosemgrep: php.lang.security.injection.tainted-sql-string.tainted-sql-string
                    $insStmt = $db->prepare($sql); // nosemgrep: php.lang.security.injection.tainted-callable.tainted-callable -- $sql built from allowlist, not request data
                    $insStmt->execute($values);
                }
            }

            // ---------------------------------------------------------
            // NEW GRAPH SYNCHRONIZATION FOR RELATIVES
            // Lineage routing: Parents & Siblings can belong to the user's
            // maiden / maternal family code (otherFamCode) rather than their
            // primary marital household code.  The front-end sends a hidden
            // field `lineage_scope` = 'maternal' to request this routing.
            // We validate the scope server-side before accepting it.
            // ---------------------------------------------------------
            $primaryFamCode  = (string)($_SESSION['famCode'] ?? '');
            $lineageScope    = (string)($_POST['lineage_scope'] ?? 'primary');
            $requestedMaternalCode = strtoupper(trim(str_replace('#', '', (string)($_POST['lineage_code'] ?? ''))));

            // Resolve which family code the graph nodes should land in.
            $familyCode = $primaryFamCode; // default: marital / primary tree

            if ($lineageScope === 'maternal' && $requestedMaternalCode !== '') {
                // Security: verify the submitted otherFamCode actually belongs to this user
                $userId = (string)($_POST['id'] ?? $_SESSION['id'] ?? '');
                $dbCheck = Db::connect2();
                $stmtOwn = $dbCheck->prepare('SELECT otherFamCode FROM otherFamily WHERE id = ? LIMIT 1');
                $stmtOwn->execute([$userId]);
                $ownRow = $stmtOwn->fetch(\PDO::FETCH_ASSOC);
                $verifiedMaternalCode = strtoupper(trim((string)($ownRow['otherFamCode'] ?? '')));

                // RFC-0004: the stored code must also be one the user verifiably holds (approved membership).
                if ($verifiedMaternalCode !== ''
                    && $verifiedMaternalCode === $requestedMaternalCode
                    && (new \App\services\BirthFamilyCodeService($dbCheck))->userHoldsCode($userId, $verifiedMaternalCode)
                ) {
                    $familyCode = $verifiedMaternalCode; // route to maiden/maternal tree
                } else {
                    // Silently fall back to primary — never write into an unowned family tree
                    error_log("[SettingController] Security: user {$userId} submitted unowned lineage_code '{$requestedMaternalCode}'. Falling back to primary '{$primaryFamCode}'.");
                    $familyCode = $primaryFamCode;
                }
            }

            // Keep backward-compat: if no scope given, use session primary code
            if (empty($familyCode)) {
                $familyCode = $primaryFamCode;
            }
            // RFC-0008: reject a bad wedding date before anything is written
            $weddingDateInput = trim((string)($_POST['marriage_date'] ?? ''));
            if ($weddingDateInput !== '' && !\App\services\CelebrationService::isValidWeddingDate($weddingDateInput)) {
                throw new \Src\Exceptions\ValidationException('Please enter a real wedding date that is not in the future.');
            }

            if (!empty($familyCode)) {
                $db = Db::connect2();
                $db->beginTransaction();

                try {
                    $relRequests = [];
                    $relRequesterId = (string)($_SESSION['id'] ?? $_POST['id'] ?? '');

                    // 1. Get Base Node
                    $stmt = $db->prepare("SELECT id, generation_level, gender FROM family_nodes WHERE user_id = ? AND family_code = ?");
                    $stmt->execute([$_POST['id'], $familyCode]);
                    $baseNode = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($baseNode) {
                        $baseNodeId = (int)$baseNode['id'];
                        $genLevel = (int)$baseNode['generation_level'];
                        $userGender = $baseNode['gender'] ?? 'Male';

                        // Confirmation requests collected here and sent AFTER commit
                        /** @var list<array{node_id:int, kind:string, requests:list<array{id:int, approver_id:string}>}> $relRequests */
                        $addRelRequest = static function (int $nodeId, string $kind, ?int $unionId, int $sharedParentId = 0) use ($db, $familyCode, $relRequesterId, &$relRequests): void {
                            $created = \App\services\FamilyRelationshipRequestService::createFor($db, $familyCode, $relRequesterId, $nodeId, $kind, $unionId, $sharedParentId);
                            if ($created) {
                                $relRequests[] = ['node_id' => $nodeId, 'kind' => $kind, 'requests' => $created];
                            }
                        };

                        // 2. Spouse / former partner (idempotent: re-saving never stacks duplicates)
                        $maritalPost = (string)($_POST['maritalStatus'] ?? '');
                        $spouseName = trim((string)($_POST['spouse_name'] ?? ''));
                        if ($spouseName !== '' && in_array($maritalPost, ['Yes - Add Husband', 'Yes - Add Wife', 'Divorced', 'Widowed'], true)) {
                            [$spFirst, $spLast] = \App\services\FamilyGraphService::splitName($spouseName);
                            $spGender = match ($maritalPost) {
                                'Yes - Add Husband' => 'Male',
                                'Yes - Add Wife' => 'Female',
                                default => (string)($_POST['spouse_gender'] ?? ''),
                            };
                            $partnerRes = \App\services\FamilyGraphService::upsertPartner($db, $familyCode, $baseNodeId, [
                                'first_name' => $spFirst,
                                'last_name' => $spLast,
                                'gender' => $spGender,
                                'email' => $_POST['spouse_email'] ?? '',
                                'mobile' => $_POST['spouse_mobile'] ?? '',
                                'maiden_name' => $maritalPost === 'Yes - Add Wife' ? ($_POST['maiden_name'] ?? '') : '',
                                'status' => match ($maritalPost) {
                                    'Divorced' => 'divorced',
                                    'Widowed' => 'widowed',
                                    default => 'married',
                                },
                            ]);
                            if ($maritalPost === 'Divorced' && $partnerRes['partner_id'] > 0) {
                                $addRelRequest($partnerRes['partner_id'], \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, $partnerRes['union_id']);
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
                                        $updF = $db->prepare("UPDATE family_nodes SET is_placeholder = 0, first_name = ?, last_name = ?, email = COALESCE(NULLIF(?, ''), email), mobile = COALESCE(NULLIF(?, ''), mobile) WHERE id = ?");
                                        $updF->execute([$fFirst, $fLast, !empty($fEmail) ? $fEmail : null, !empty($fMobile) ? $fMobile : null, $p1Id]);
                                    }

                                    if ($p2Id > 0 && !empty($motherFullName)) {
                                        $updM = $db->prepare("UPDATE family_nodes SET is_placeholder = 0, first_name = ?, last_name = ?, email = COALESCE(NULLIF(?, ''), email), mobile = COALESCE(NULLIF(?, ''), mobile) WHERE id = ?");
                                        $updM->execute([$mFirst, $mLast, !empty($mEmail) ? $mEmail : null, !empty($mMobile) ? $mMobile : null, $p2Id]);
                                    }
                                }
                            } else {
                                $insF = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile, is_placeholder) VALUES (?, ?, ?, 'Male', ?, '/resources/images/profile/avatarM.png', 'Father', ?, ?, ?)");
                                $insF->execute([$familyCode, $fFirst, $fLast, $genLevel - 1, !empty($fEmail) ? $fEmail : null, !empty($fMobile) ? $fMobile : null, empty($fatherFullName) ? 1 : 0]);
                                $fId = (int)$db->lastInsertId();

                                $insM = $db->prepare("INSERT INTO family_nodes (family_code, first_name, last_name, gender, generation_level, avatar_url, bio, email, mobile, is_placeholder) VALUES (?, ?, ?, 'Female', ?, '/resources/images/profile/avatarF.png', 'Mother', ?, ?, ?)");
                                $insM->execute([$familyCode, $mFirst, $mLast, $genLevel - 1, !empty($mEmail) ? $mEmail : null, !empty($mMobile) ? $mMobile : null, empty($motherFullName) ? 1 : 0]);
                                $mId = (int)$db->lastInsertId();

                                $insU = $db->prepare("INSERT INTO family_unions (family_code, partner_1_id, partner_2_id, union_type, is_current) VALUES (?, ?, ?, 'married', 1)");
                                $insU->execute([$familyCode, $fId, $mId]);
                                $parentUnionId = (int)$db->lastInsertId();

                                $insLink = $db->prepare("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (?, ?, 'biological')");
                                $insLink->execute([$parentUnionId, $baseNodeId]);
                            }
                        }

                        // 3b. Parents' relationship status (together / divorced / separated / widowed)
                        $parentsStatus = trim((string)($_POST['parents_status'] ?? ''));
                        if ($parentsStatus !== '' && (!empty($fatherFullName) || !empty($motherFullName))) {
                            \App\services\FamilyGraphService::setParentsStatus($db, $familyCode, $baseNodeId, $parentsStatus);
                        }

                        // 4. Children (each routed to the right union: current spouse / former partner / other)
                        $kidsCount = (int)($_POST['children'] ?? 0);
                        for ($i = 1; $i <= $kidsCount; $i++) {
                            $cFirst = trim((string)($_POST["children_first_name$i"] ?? ''));
                            $cLast  = trim((string)($_POST["children_last_name$i"] ?? ''));
                            if ($cFirst === '' && isset($_POST["children_name$i"])) {
                                [$cFirst, $cLast] = \App\services\FamilyGraphService::splitName((string)$_POST["children_name$i"]);
                            }
                            if ($cFirst === '') continue;

                            $cOption = (string)($_POST["children_option$i"] ?? '');
                            $childCtx = null;
                            \App\services\FamilyGraphService::upsertChild($db, $familyCode, $baseNodeId, [
                                'first_name' => $cFirst,
                                'last_name' => $cLast,
                                'gender' => $_POST["children_gender$i"] ?? '',
                                'email' => $_POST["children_email$i"] ?? '',
                                'with' => match ($cOption) {
                                    'With Spouse' => 'spouse',
                                    'With Former Partner' => 'former',
                                    default => 'other',
                                },
                                'other_parent_name' => $_POST["children_other_parent$i"] ?? '',
                                'union_status' => $_POST["children_union_status$i"] ?? 'divorced',
                            ], $childCtx);
                            if (is_array($childCtx) && ($childCtx['with'] ?? '') === 'former' && (int)($childCtx['partner_id'] ?? 0) > 0
                                && ($_POST["children_union_status$i"] ?? 'divorced') !== 'widowed') {
                                $addRelRequest((int)$childCtx['partner_id'], \App\services\FamilyRelationshipRequestService::FORMER_PARTNER, (int)$childCtx['union_id']);
                            }
                        }

                        // 5. Siblings (full / same mother only / same father only / step / adopted)
                        $siblingsCount = (int)($_POST['sibling'] ?? 0);
                        for ($i = 1; $i <= $siblingsCount; $i++) {
                            $sFirst = trim((string)($_POST["sibling_first_name$i"] ?? ''));
                            $sLast  = trim((string)($_POST["sibling_last_name$i"] ?? ''));
                            if ($sFirst === '' && isset($_POST["sibling_name$i"])) {
                                [$sFirst, $sLast] = \App\services\FamilyGraphService::splitName((string)$_POST["sibling_name$i"]);
                            }
                            if ($sFirst === '') continue;

                            $sibCtx = null;
                            $sibNodeId = \App\services\FamilyGraphService::upsertSibling($db, $familyCode, $baseNodeId, [
                                'first_name' => $sFirst,
                                'last_name' => $sLast,
                                'gender' => $_POST["sibling_gender$i"] ?? '',
                                'email' => $_POST["sibling_email$i"] ?? '',
                                'parentage' => $_POST["sibling_option$i"] ?? '',
                                'other_parent_name' => $_POST["sibling_other_parent$i"] ?? '',
                            ], $sibCtx);
                            $sibKind = is_array($sibCtx) ? \App\services\FamilyRelationshipRequestService::kindForParentage((string)$sibCtx['parentage']) : null;
                            if ($sibNodeId > 0 && $sibKind !== null) {
                                $addRelRequest($sibNodeId, $sibKind, (int)$sibCtx['union_id'], (int)$sibCtx['shared_parent_id']);
                            }
                        }
                    }

                    $db->commit();

                    // RFC-0008: wedding date + anniversary opt-in, saved after the
                    // commit (the first preferences write may create its table,
                    // which would otherwise end the transaction early).
                    $maritalNow = (string)($_POST['maritalStatus'] ?? '');
                    if (in_array($maritalNow, ['Yes - Add Husband', 'Yes - Add Wife'], true)) {
                        $celebrantId = (string)$_POST['id'];
                        if (isset($_POST['marriage_date'])) {
                            \App\services\CelebrationService::saveWeddingDate($celebrantId, $familyCode, $weddingDateInput);
                        }
                        if (isset($_POST['announce_anniversary'])) {
                            \App\services\CelebrationService::savePreferences(
                                $celebrantId,
                                showAge: null,
                                announceAnniversary: in_array(strtolower(trim((string)$_POST['announce_anniversary'])), ['on', '1', 'true'], true)
                            );
                        }
                    }

                    $requesterName = trim((string)($_SESSION['fName'] ?? '') . ' ' . (string)($_SESSION['lName'] ?? '')) ?: 'A family member';
                    foreach ($relRequests as $rr) {
                        \App\services\FamilyRelationshipRequestService::notify($db, $familyCode, $relRequesterId, $requesterName, $rr['node_id'], $rr['kind'], $rr['requests']);
                    }
                } catch (\Exception $e) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    throw $e;
                }
            }

            $action = $_POST['action'] ?? '';
            unsetPostData($_POST, [
                'email', 'mobile', 'country', 'occupation', 'employmentStatus',
                'father_first_name', 'father_last_name', 'father_name', 'father_email', 'father_mobile',
                'mother_first_name', 'mother_last_name', 'mother_name', 'mother_maiden', 'maiden_name', 'mother_email', 'mother_mobile',
                'spouse_name', 'spouse_email', 'spouse_mobile', 'spouse_gender', 'parents_status', 'children', 'sibling', 'maritalStatus', 'button', 'action'
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

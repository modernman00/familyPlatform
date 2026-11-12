<?php
namespace App\controller\members;

use App\classes\{
  Update,
  Insert,
  PushNotificationClass,
  Pusher
};
use Src\CheckToken;

use App\controller\BaseController;
use App\model\SingleCustomerData;
use Src\functionality\SendEmailFunctionality;
use Src\SelectFn;
use Src\SubmitForm;
use Src\ToSendEmail;
use Src\Select;
use Src\UpdateFn;


final class FamilyRequestController extends BaseController
{

  public static function addToFamily(): void
  {
    echo "<h1> add to family</h1>";
  }
  /**
   * Summary of request
   * /members/familyRequestMgt
   * @return void
   */
  public static function request(): void
  {
    try {
      $rawInput = file_get_contents("php://input");
      $dataFromJs = json_decode($rawInput !== false ? $rawInput : '', true) ?: [];

      // Defensively validate CSRF token for JSON and form payloads
      $sessionToken = $_SESSION['token'] ?? '';
      $jsonToken = $dataFromJs['token'] ?? '';
      $headerToken = $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
      $postToken = $_POST['token'] ?? '';

      $isTokenValid = false;
      if (!empty($sessionToken)) {
        if (!empty($headerToken) && hash_equals($sessionToken, $headerToken)) {
          $isTokenValid = true;
        } elseif (!empty($jsonToken) && hash_equals($sessionToken, $jsonToken)) {
          $isTokenValid = true;
        } elseif (!empty($postToken) && hash_equals($sessionToken, (string)$postToken)) {
          $isTokenValid = true;
        }
      }

      if (!$isTokenValid) {
        CheckToken::tokenCheck();
      }

      if (empty($dataFromJs)) {
        msgException(301, "Invalid request data.");
      }


      // SUBMIT TO THE DATABASE TABLE - requestMgt Table

      // form the table data 

      // $dataFromJs('requester') = example 
      // (
      // 'requesterFirstName' => 'SEGUN',
      // 'requesterLastName' => 'OLAOGUN',
      // 'requesterId' => '117540OLAWALE',
      // 'requesterEmail' => 'woguns@ymail.com',
      // 'requesterProfileImg' => 'olutobs_13th.jpeg',
      // 'requesterFamCode' => 'MODERNMAN',}

      $requesterData = BaseController::membersData();

      $theApproverID = $dataFromJs['approver']['approverId'] ?? $dataFromJs['approverId'] ?? $dataFromJs['target_id'] ?? '';
      $theRequesterID = $requesterData['id'] ?? '';
      $theRequesterCode = $requesterData['famCode'] ?? '';

      $theApproverCode = $dataFromJs['approver']['approverCode'] ?? $dataFromJs['approverCode'] ?? '';
      $approverEmail = $dataFromJs['approver']['approverEmail'] ?? $dataFromJs['approverEmail'] ?? '';
      $approverFirstName = $dataFromJs['approver']['approverFirstName'] ?? $dataFromJs['approverFirstName'] ?? '';
      $approverLastName = $dataFromJs['approver']['approverLastName'] ?? $dataFromJs['approverLastName'] ?? '';

      // If approver details are missing from payload, resolve from database
      if (!empty($theApproverID) && (empty($theApproverCode) || empty($approverEmail))) {
        $singleCust = new SingleCustomerData();
        $approverCust = $singleCust->getCustomerData((string)$theApproverID, ['personal', 'contact']);
        if ($approverCust && is_array($approverCust)) {
          $theApproverCode = $theApproverCode ?: ($approverCust['famCode'] ?? '');
          $approverEmail = $approverEmail ?: ($approverCust['email'] ?? '');
          $approverFirstName = $approverFirstName ?: ($approverCust['firstName'] ?? '');
          $approverLastName = $approverLastName ?: ($approverCust['lastName'] ?? '');
        }
      }

      $approverData = [
        'approverId' => $theApproverID,
        'approverCode' => $theApproverCode,
        'approverEmail' => $approverEmail,
        'approverFirstName' => $approverFirstName,
        'approverLastName' => $approverLastName,
        ...($dataFromJs['approver'] ?? [])
      ];

      // validate the input 

      if (empty($theApproverID) || empty($theRequesterID) || empty($theApproverCode)) {
        msgException(301, "Invalid request data for Request.");
      }

      // Validate and sanitize relationship attribution for Connected Kin
      $rawCategory = (string)($dataFromJs['relationship_category'] ?? '');
      $rawRole = (string)($dataFromJs['relationship_role'] ?? '');
      $rawNote = (string)($dataFromJs['relationship_note'] ?? '');

      $validCategories = ['in_laws', 'extended_lineage', 'family_friend'];
      $category = in_array($rawCategory, $validCategories, true) ? $rawCategory : 'family_friend';
      $role = !empty($rawRole) ? htmlspecialchars(trim(mb_substr($rawRole, 0, 100)), ENT_QUOTES, 'UTF-8') : 'Connected Kin';
      $note = !empty($rawNote) ? htmlspecialchars(trim(mb_substr($rawNote, 0, 255)), ENT_QUOTES, 'UTF-8') : '';

      $categoryLabels = [
        'in_laws' => 'In-Laws',
        'extended_lineage' => 'Extended Lineage',
        'family_friend' => 'Family Friend'
      ];
      $categoryLabel = $categoryLabels[$category];
      $fullRelationLabel = ($role !== '' && $role !== $categoryLabel) ? "{$categoryLabel} ({$role})" : $categoryLabel;

      $tableData = [
        'approver_id' => $theApproverID,
        'requester_id' => $theRequesterID,
        'requesterCode' => $theRequesterCode,
        'approverCode' => $theApproverCode,
        'relationship_category' => $category,
        'relationship_role' => $role,
        'relationship_note' => $note,
        'status' => "Request sent"
      ];

      // check if there is a combination of requester and approved id and the status is request sent

      $result = SelectFn::selectWhereBothIdentifiersMatch('requestMgt', 'approver_id', 'requester_id', $theApproverID, $theRequesterID);
      // if request is already made, then ignore the request
      if ($result && $result[0]['status'] === 'Request sent') {
        msgException(409, "Request already pending");
      } else {

        // insert the request to the table.

        SubmitForm::submitFormDynamicLastId(
          table: 'requestMgt',
          field: $tableData,
          lastIdCol: 'no'
        );

        $requesterName = trim("{$requesterData['firstName']} {$requesterData['lastName']}");
        $approverName = trim("{$approverFirstName} {$approverLastName}") ?: 'Family Member';

        // remove the requester email so it is not confused with the approver email,
        // and mask unshared family codes so the approver never sees another family's code.
        $approverCanSeeRequesterCode = parent::viewerCanSeeFamilyCode((string)$theRequesterCode, (string)$theApproverID);
        $requesterPayload = $requesterData;
        unset($requesterPayload['email']);
        if (!$approverCanSeeRequesterCode) {
          $requesterPayload['famCode'] = '';
          $requesterPayload['otherFamCode'] = '';
          $requesterPayload['familyCode2'] = '';
        }

        $requesterImg = $requesterData['profileImg']
          ?? $requesterData['profilePics']
          ?? (!empty($requesterData['img']) ? basename($requesterData['img']) : '')
          ?: (($requesterData['gender'] ?? '') === 'Female' ? 'avatarF.png' : 'avatarM.png');
        $requesterPayload['profileImg'] = $requesterImg;
        $requesterPayload['profilePics'] = $requesterImg;

        $emailViewPath = $dataFromJs['emailPath'] ?? 'msg.request';

        // Persist in-app notification row FIRST so bell & live feed reflect immediately
        $notificationContent = "$requesterName requested to connect with you as: $fullRelationLabel" . ($note !== '' ? " — \"$note\"" : "");
        $notificationData = [
          'sender_id' => $theRequesterID,
          'receiver_id' => $theApproverID,
          'sender_name' => $requesterName,
          'receiver' => $approverName,
          'notification_name' => $dataFromJs['eventName'] ?? "Connected Kin Request from $requesterName",
          'notification_date' => date('Y-m-d H:i:s'),
          'notification_type' => 'Connected Kin Request',
          'notification_content' => $notificationContent,
          'action_url' => '/profilePage#kinshipRequest',
          'notification_status' => 'new'
        ];

        SubmitForm::submitFormDynamicLastId(table: 'notification', field: $notificationData, lastIdCol: 'no');

        // Keep real-time Pusher payload lean (< 2KB) while carrying full banner card metadata
        $broadcastPayload = array_merge($notificationData, [
          'requester_id' => $theRequesterID,
          'approver_id' => $theApproverID,
          'firstName' => $requesterData['firstName'] ?? 'Family Member',
          'lastName' => $requesterData['lastName'] ?? '',
          'famCode' => $approverCanSeeRequesterCode ? $theRequesterCode : '',
          'img' => $requesterImg,
          'relationship_category' => $category,
          'relationship_category_label' => $categoryLabel,
          'relationship_role' => $role,
          'relationship_note' => $note,
          'relationship_full_label' => $fullRelationLabel,
        ]);

        // Batch channels into single multi-channel Pusher triggers (eliminates 5 sequential round-trips)
        $userChannel = Pusher::userChannel((string)$theApproverID);
        $lowerChannel = 'private-user-' . strtolower(Pusher::slug((string)$theApproverID));
        $userChannels = array_values(array_unique([$userChannel, $lowerChannel]));
        $requestChannels = array_values(array_unique([$userChannel, $lowerChannel, 'friend-request-channel']));

        Pusher::trigger($userChannels, 'new-notification', $broadcastPayload);
        Pusher::trigger($requestChannels, 'new-request', $broadcastPayload);

        // Send Web Push BEFORE slow SMTP email, routing deep-link cleanly to /profilePage#kinshipRequest
        PushNotificationClass::sendPushNotification(
          userId: $theApproverID,
          message: "$requesterName wants to connect as $fullRelationLabel" . ($note !== '' ? " — \"$note\"" : ''),
          url: '/profilePage#kinshipRequest',
          title: '🤝 New Connected Kin Request',
          tag: 'kinship-req-' . Pusher::slug((string)$theRequesterID)
        );

        $sendEmailFn = static function () use (
          $approverEmail,
          $approverName,
          $category,
          $categoryLabel,
          $role,
          $note,
          $fullRelationLabel,
          $approverData,
          $requesterPayload,
          $requesterName,
          $emailViewPath
        ): void {
          if (empty($approverEmail)) {
            return;
          }
          try {
            $emailArray = [
              'data' => [
                'name' => $approverName,
                'email' => $approverEmail,
                'relationship_category' => $category,
                'relationship_category_label' => $categoryLabel,
                'relationship_role' => $role,
                'relationship_note' => $note,
                'relationship_full_label' => $fullRelationLabel,
                'isFunctional' => true,
                ...$approverData,
                ...$requesterPayload
              ],
              'subject' => "Connected Kin request from $requesterName: $fullRelationLabel",
              'viewPath' => $emailViewPath,
            ];

            ToSendEmail::sendEmailGeneral($emailArray, 'member');
          } catch (\Throwable $emailEx) {
            error_log('[FamilyRequestController] Email dispatch warning: ' . $emailEx->getMessage());
          }
        };

        $canFinishFast = !(function_exists('isTestEnv') && \isTestEnv()) && \PHP_SAPI !== 'cli' && function_exists('fastcgi_finish_request');
        if ($canFinishFast) {
          msgSuccess(200, "Request sent");
          @fastcgi_finish_request();
          $sendEmailFn();
        } else {
          $sendEmailFn();
          msgSuccess(200, "Request sent");
        }
      }
    } catch (\Throwable $err) {

      showError($err);
    }
  }
  /**
   *  /member/request/[a:req]/[a:appr]/[a:dec]/[a:reqCode]/[a:src]
   * @param mixed $req
   * @param mixed $appr
   * @param mixed $dec
   * @param mixed $reqCode
   * @param mixed $src
   * @return void
   */
  public static function approveDelete($req, $appr, $dec, $reqCode = null, $src = null): void
  {

    // Check if the code has already run in this session
    // if ($_SESSION['preventReload'] != $_GET['appr']) {


    // Set the session variable to indicate that the code has executed
    $_SESSION['preventReload'] = checkInput($appr);

    if (isset($req) && isset($appr)) {
      $requesterRaw = checkInput($req);
      $approverRaw = checkInput($appr);
      $requester = is_string($requesterRaw) ? $requesterRaw : '';
      $approver = is_string($approverRaw) ? $approverRaw : '';
      $getDecision = checkInput($dec);
      if ($reqCode !== null) {
        checkInput($reqCode);
      }

      // check if the dec is 50 or 10 (50 is approved and 10 is rejection)

      $decision = match ($getDecision) {
        "10" => "rejected",
        "50" => "approved",
        default => "Unknown", // Handle other cases or provide a default value
      };

      // check the requestMgt table for the requester and the approver where status is approved or rejected
      $query = Select::formAndMatchQuery(
        selection: 'SELECT_ALL3',
        table: 'requestMgt',
        identifier1: 'approver_id',
        identifier2: 'requester_id',
        identifier3: 'status'
      );

      $result = Select::selectFn2(
        query: $query,
        bind: [$approver, $requester, 'approved']
      );

      $data = [
        'status' => $decision,
        'requester_id' => $requester,
        'approver_id' => $approver
      ];

      $identifier = ['approver_id', 'requester_id'];

      if (!$result) {

        UpdateFn::makeUpdateFn('requestMgt', $data, $identifier, 'AND');

        // Fetch requester and approver details with the id 
        $req = parent::findMemberById($requester);
        $app = parent::findMemberById($approver);

        if ($decision === "approved") {

          // ADDENDUM TO THE REQUESTER DETAIL FROM THE APPROVER AND STATUS

          $req['approverName'] = $app['fullName'];
          $req['decision'] = $decision;
          $subject = "{$app['fullName']} approved your request";

          try {
                if (!\App\classes\Pusher::outboundBlocked()) {
                    SendEmailFunctionality::email('msg/requestRequest', $subject, $req, 'member');
                }
          } catch (\Throwable $e) {
              error_log('[FamilyRequestController] Approval notification email failed: ' . $e->getMessage());
          }

          $cleanDataNotification = [
            'sender_id' => $approver,
            'receiver_id' => $requester,
            'sender_name' => cleanSession((string)($req['approverName'] ?? '')),
            'notification_name' => "Request approval",
            'notification_date' => date("Y-m-d H:i:s"),
            'receiver' => 'everyone filtered',
            'notification_type' => "Request",
            'notification_content' => $subject,
            'notification_status' => 'new'
          ];


          $lastInsertedId = Insert::submitFormDynamicLastId(table: 'notification', field: $cleanDataNotification, lastIdCol: 'no');

          // Send push notification to the requester about the decision
          try {
            $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: ($_ENV['MIX_APP_URL2'] ?? getenv('MIX_APP_URL2') ?: 'https://familyplatform.test')), '/');
            $profileUrl = "{$baseUrl}/allMembers/getProfile?id=" . urlencode((string)$approver);
            PushNotificationClass::sendPushNotification(
              userId: (string)$requester,
              message: $subject,
              url: $profileUrl,
              title: 'Family Request Accepted',
              tag: 'friend-request-approved'
            );
          } catch (\Throwable $pushEx) {
            error_log('[FamilyRequestController] Push notification dispatch warning: ' . $pushEx->getMessage());
          }

          // Broadcast real-time approval to requester via Pusher
          try {
            $approvalPayload = [
              'sender_id' => $approver,
              'receiver_id' => $requester,
              'sender_name' => (string)($app['fullName'] ?? 'A family member'),
              'status' => 'approved',
              'message' => $subject,
              'notification' => $cleanDataNotification
            ];
            $requesterChannel = Pusher::userChannel((string)$requester);
            Pusher::broadcast($requesterChannel, 'new-notification', $cleanDataNotification);
            Pusher::broadcast($requesterChannel, 'request-approved', $approvalPayload);

            $lowerReqChannel = 'private-user-' . strtolower(Pusher::slug((string)$requester));
            if ($lowerReqChannel !== $requesterChannel) {
              Pusher::broadcast($lowerReqChannel, 'new-notification', $cleanDataNotification);
              Pusher::broadcast($lowerReqChannel, 'request-approved', $approvalPayload);
            }

            Pusher::broadcast('friend-request-channel', 'request-approved', $approvalPayload);
          } catch (\Throwable $pusherEx) {
            error_log('[FamilyRequestController] Pusher broadcast warning: ' . $pusherEx->getMessage());
          }

          // show the approver what they have just done
          $app['decision'] = $decision;
          $app['requesterName'] = "{$req['firstName']} {$req['lastName']}";

          // if the source is from the profile page, refresh the page or use javascript to manage it 

          $requestApprovalFromProfilePage = $src ?? null;

          if ($requestApprovalFromProfilePage === "pp") {
            header("location: /profilePage");
          } else {
            // Show premium approval landing page (email link click)
            view('msg/requestApprovalSuccess', compact('app'));
          }
        } else {
          // Rejection flow — show the same premium page with "declined" state
          $app = parent::findMemberById($approver);
          $app['decision'] = $decision;
          $reqFirst = $req['firstName'] ?? 'A family member';
          $reqLast = $req['lastName'] ?? '';
          $app['requesterName'] = "{$reqFirst} {$reqLast}";

          $requestApprovalFromProfilePage = $src ?? null;
          if ($requestApprovalFromProfilePage === "pp") {
            header("location: /profilePage");
          } else {
            view('msg/requestApprovalSuccess', compact('app'));
          }
        }
      } else {
        // redirect to the login page 
        header("location: /profilePage");
      }
    } else {
      msgException(401, "How did you get here?");
    }
    // } else {

    //   header('location: /');
    // }
  }
  /**
   * Summary of getApprover/members/familyRequestMgt/getApprover
   * @return void
   */
  public static function getApprover()
  {

    try {

      $id = checkInput($_GET['id']);
      $id = is_string($id) ? $id : '';
      $details = new SingleCustomerData;
      $result = $details->getCustomerData($id, ['personal', 'contact']);
      if (is_array($result)) {
        $viewerId = (string)(parent::returnId() ?: ($_SESSION['auth']['id'] ?? ($_SESSION['id'] ?? '')));
        if ($viewerId !== $id) {
          $rawFamCode = (string)($result['famCode'] ?? '');
          if (!parent::viewerCanSeeFamilyCode($rawFamCode, $viewerId)) {
            $result['famCode'] = '';
          }
          if (!parent::viewerCanSeeFamilyCode((string)($result['otherFamCode'] ?? ''), $viewerId)) {
            $result['otherFamCode'] = '';
          }
          if (!parent::viewerCanSeeFamilyCode((string)($result['familyCode2'] ?? ''), $viewerId)) {
            $result['familyCode2'] = '';
          }
        }
        \msgSuccess(200, $result);
      }
    } catch (\Throwable $err) {
      \showError($err);
    }
  }


  /**
   * Gets the friend request data sent to the user identified by the given ID
   *
   * @param int $id The ID of the user
   * /getFriendRequestById
   *
   * @return array[]|string An array of the requester data
   *
   * @psalm-return ' No Requester Data'|list{0?: array,...}
   */
  public static function getFriendRequestData(): array|string
  {
    try {
      $id = checkInput($_GET['id'] ?? '') ?: parent::returnId();
      $result = [];

      // Read-only endpoint (per-member findMemberById lookups can be slow): let go
      // of the session lock so it does not stall the profile page's other
      // concurrent AJAX calls sharing the same session file.
      \releaseSessionLock();

      $select = Select::formAndMatchQuery(selection: "SELECT_AND", table: "requestMgt", identifier1: "approver_id", identifier2: "status");

      $getRequesterDataById = Select::selectFn2(query: $select, bind: [$id, 'Request sent']);

      foreach ($getRequesterDataById as $getRequesterDataById1) {

        if ($getRequesterDataById1['requester_id']) {
          $data = parent::findMemberById($getRequesterDataById1['requester_id']);
          $viewerIdStr = is_scalar($id) ? (string)$id : '';
          if ($viewerIdStr !== (string)($data['id'] ?? '')) {
            if (!parent::viewerCanSeeFamilyCode((string)($data['famCode'] ?? ''), $viewerIdStr)) {
              $data['famCode'] = '';
            }
            if (!parent::viewerCanSeeFamilyCode((string)($data['otherFamCode'] ?? ''), $viewerIdStr)) {
              $data['otherFamCode'] = '';
            }
            if (!parent::viewerCanSeeFamilyCode((string)($data['familyCode2'] ?? ''), $viewerIdStr)) {
              $data['familyCode2'] = '';
            }
          }
          $relCat = $getRequesterDataById1['relationship_category'] ?? 'family_friend';
          $relRole = $getRequesterDataById1['relationship_role'] ?? 'Connected Kin';
          $relNote = $getRequesterDataById1['relationship_note'] ?? '';
          $categoryLabels = [
            'in_laws' => 'In-Laws',
            'extended_lineage' => 'Extended Lineage',
            'family_friend' => 'Family Friend'
          ];
          $data['relationship_category'] = $relCat;
          $data['relationship_category_label'] = $categoryLabels[$relCat] ?? 'Connected Kin';
          $data['relationship_role'] = $relRole;
          $data['relationship_note'] = $relNote;
          $data['relationship_full_label'] = ($relRole !== '' && $relRole !== ($categoryLabels[$relCat] ?? '')) ? ($categoryLabels[$relCat] ?? 'Connected Kin') . " ({$relRole})" : ($categoryLabels[$relCat] ?? 'Connected Kin');

          array_push($result, $data);
        } else {
          $result = " No Requester Data";
        }
      }

      msgSuccess(200, $result);
      return $result;
    } catch (\Throwable $th) {
      showError($th);
      return [];
    }
  }



  public static function test($req, $res): void
  {
    echo $req;
    echo BR;
    echo $res;

    view('test');
  }

  public static function testURL(): void
  {

    view('test');
  }
}

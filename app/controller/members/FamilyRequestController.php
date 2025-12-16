<?php

namespace App\Controller\members;

use App\classes\{
  Update,
  Insert,
  PushNotificationClass,
  Pusher
};

use App\controller\BaseController;
use App\model\SingleCustomerData;
use Src\functionality\SendEmailFunctionality;
use Src\SelectFn;
use Src\SubmitForm;
use Src\ToSendEmail;
use Src\Select;
use Src\UpdateFn;


class FamilyRequestController extends BaseController
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
      // printArr jS DATA 

      $dataFromJs = json_decode(file_get_contents("php://input"), true);

      if (!$dataFromJs) {
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

      $theApproverID = $dataFromJs['approver']['approverId'];
      $theRequesterID = $requesterData['id'];
      $theApproverCode = $dataFromJs['approver']['approverCode'];
      $theRequesterCode = $requesterData['famCode'];
      $approverEmail = $dataFromJs['approver']['approverEmail'];

      // validate the input 

      if (empty($theApproverID) || empty($theRequesterID) || empty($theApproverCode)) {
        msgException(301, "Invalid request data for Request.");
      }

      $tableData = [
        'approver_id' => $theApproverID,
        'requester_id' => $theRequesterID,
        'requesterCode' => $theRequesterCode,
        'approverCode' => $theApproverCode,
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

        // SEND EMAIL TO THE APPROVER

        $requesterName = "
        {$requesterData['firstName']} 
        {$requesterData['lastName']}";

        $approverName = "
        {$dataFromJs['approver']['approverFirstName']} 
        {$dataFromJs['approver']['approverLastName']}";

        // remove the requester email so it is not confused with the approver email
        unset($requesterData['email']);

        $emailArray = [
          'data' => [
            'name' => $approverName,
            'email' => $approverEmail,
            ...$dataFromJs['approver'],
            ...$requesterData
          ],
          'subject' => "Family request from $requesterName",
          'viewPath' => $dataFromJs['emailPath'],
        ];

        ToSendEmail::sendEmailGeneral($emailArray, 'member');

        // SENT NOTIFICATION TO APPROVER TAB

        $notificationData = [
          'sender_id' => $theRequesterID,
          'receiver_id' => $theApproverID,
          'sender_name' => $requesterName,
          'receiver' => $approverName,
          'notification_name' => $dataFromJs['eventName'] ?? "Friend Request from $requesterName",
          'notification_date' => date('Y-m-d'),
          'notification_type' => 'Friend Request',
          'notification_content' => "",
          'notification_status' => 'new'
        ];

        // SEND BACK THE APPROVER ID

        SubmitForm::submitFormDynamicLastId(table: 'notification', field: $notificationData, lastIdCol: 'no');


        $url = "{$_ENV["MIX_APP_URLS"]}member/request?req=$theRequesterID&appr=$theApproverID&dec=50&reqCode=$theApproverCode&src=email";


        $notificationData['approverDetails'] = $dataFromJs['approver'];
        $notificationData['requesterDetails'] = $requesterData;

        // Broadcast to the family members via Pusher to notification, friendrequestcard and increaseNotificationCount
        Pusher::broadcast('friend-request-channel', 'new-request', $notificationData);

        // Send push notification to the receiver about the new friend request
        PushNotificationClass::sendPushNotification(userId: $theApproverID, message: "Friend Request from $requesterName", url: $url);


        msgSuccess(200, "Request sent");
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
  public static function approveDelete($req, $appr, $dec, $reqCode, $src): void
  {

    // Check if the code has already run in this session
    // if ($_SESSION['preventReload'] != $_GET['appr']) {


    // Set the session variable to indicate that the code has executed
    $_SESSION['preventReload'] = checkInput($appr);

    if (isset($req) && isset($appr)) {
      $requester = checkInput($req);
      $approver = checkInput($appr);
      $getDecision = checkInput($dec);
      $requestCode = checkInput($reqCode);

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

        // get requester and approver details with the id 

        if ($decision === "approved") {

          $req = parent::findMemberById($requester);
          $app = parent::findMemberById($approver);

          // ADDENDUM TO THE REQUESTER DETAIL FROM THE APPROVER AND STATUS

          $req['approverName'] = $app['fullName'];
          $req['decision'] = $decision;
          $subject = "{$app['fullName']} approved your request";

          // email the requester that the request has been approved

          SendEmailFunctionality::email('msg/requestRequest', $subject, $req, 'member');

          $cleanDataNotification = [
            'sender_id' => $approver,
            'receiver_id' => $requester,
            'sender_name' => cleanSession($req['approverName']),
            'notification_name' => "Request approval",
            'notification_date' => date("Y-m-d H:i:s"),
            'receiver' => 'everyone filtered',
            'notification_type' => "Request",
            'notification_content' => $subject,
            'notification_status' => 'new'
          ];


          $lastInsertedId = Insert::submitFormDynamicLastId(table: 'notification', field: $cleanDataNotification, lastIdCol: 'no');

          msgSuccess(code: 200, msg: $lastInsertedId);

          // Send push notification to the requester about the decision - Service Manager JS
          PushNotificationClass::sendPushNotification(userId: $requester, message: $subject);

          // show the approver what they have just done
          $app['decision'] = $decision;
          $app['requesterName'] = "{$req['firstName']} {$req['lastName']}";

          // if the source is from the profile page, refresh the page or use javascript to manage it 


          $requestApprovalFromProfilePage = $src ?? null;

          if ($requestApprovalFromProfilePage === "pp") {
            header("location: /profilePage");
          } else {
            view('msg/requestApprover', compact('app'));
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
      $details = new SingleCustomerData;
      $result = $details->getCustomerData($id, ['personal', 'contact']);
      if ($result) {
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
   * @return array An array of the requester data
   */
  public static function getFriendRequestData()
  {
    try {
      $id = parent::returnId();
      $result = [];

      $select = Select::formAndMatchQuery(selection: "SELECT_AND", table: "requestMgt", identifier1: "approver_id", identifier2: "status");

      $getRequesterDataById = Select::selectFn2(query: $select, bind: [$id, 'Request sent']);

      foreach ($getRequesterDataById as $getRequesterDataById1) {

        if ($getRequesterDataById1['requester_id']) {
          $data = parent::findMemberById($getRequesterDataById1['requester_id']);

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



  public static function test($req, $res)
  {
    echo $req;
    echo BR;
    echo $res;

    view('test');
  }

  public static function testURL()
  {

    view('test');
  }
}

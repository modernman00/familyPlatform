<?php

namespace App\controller\members;

use App\classes\{
  Select,
  Update,
  Insert,
  PushNotificationClass
};

use App\model\SingleCustomerData;


class FamilyRequest extends Select
{

  public static function addToFamily(): void
  {
    echo "<h1> add to family</h1>";
  }

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

      $theApproverID = $dataFromJs['approver']['approverId'];
      $theRequesterID = $dataFromJs['requester']['requesterId'];
      $theApproverCode = $dataFromJs['approver']['approverCode'];

      // validate the input 

      if (empty($theApproverID) || empty($theRequesterID) || empty($theApproverCode)) {
        msgException(errCode: 301, msg: "Invalid request data for Request.");
      }

      $tableData = [
        'approver_id' => $theApproverID,
        'requester_id' => $theRequesterID,
        'status' => "Request sent"
      ];

      // check if there is a combination of requester and approved id and the status is request sent

      $query = Select::formAndMatchQuery(
        selection: 'SELECT_AND',
        table: 'requestMgt',
        identifier1: 'approver_id',
        identifier2: 'requester_id'
      );

      $result = Select::selectFn2(
        query: $query,
        bind: [$theApproverID, $theRequesterID]
      );

      if ($result) {

        msgSuccess(code: 200, msg: $result);
      } else {

        Insert::submitFormDynamicLastId(
          table: 'requestMgt',
          field: $tableData,
          lastIdCol: 'no'
        );

        // SEND EMAIL TO THE APPROVER

        $requesterName = "
        {$dataFromJs['requester']['requesterFirstName']} 
        {$dataFromJs['requester']['requesterLastName']}";

        $approverName = "
        {$dataFromJs['approver']['approverFirstName']} 
        {$dataFromJs['approver']['approverLastName']}";

        $emailArray = [
          'data' => [
            'name' => $approverName,
            'email' => getenv('TEST_EMAIL'),
            ...$dataFromJs['approver'],
            ...$dataFromJs['requester']
          ],
          'subject' => "$requesterName sent you a family request",
          'viewPath' => $dataFromJs['emailPath'],
          // 'name' => $approverName,
          // 'email' => 'waledevtest@gmail.com',
          // 'email' => $dataFromJs['approver']['email'],
        ];

        sendEmailGeneral(array: $emailArray, recipient: 'member');

        // SENT NOTIFICATION TO APPROVER TAB

        $notificationData = [
          'sender_id' => $theRequesterID,
          'receiver_id' => $theApproverID,
          'sender_name' => $requesterName,
          'receiver' => $approverName,
          'notification_name' => $dataFromJs['eventName'] ?? "Friend Request from $requesterName",
          'notification_date' => date('Y-m-d'),
          'notification_type' => 'Friend Request',
          'notification_content' => "$requesterName sent $approverName a family request",
          'notification_status' => 'new'
        ];

        // SEND BACK THE APPROVER ID

        Insert::submitFormDynamicLastId(table: 'notification', field: $notificationData, lastIdCol: 'no');

        $url = getenv("MIX_APP_URLS") . "/member/request?req=$theRequesterID&appr=$theApproverID&reqCode=$theApproverCode&dec=50";

        // Send push notification to the receiver about the new friend request
        PushNotificationClass::sendPushNotification(userId: $theApproverID, message: "Friend Request from $requesterName", url: $url);


        msgSuccess(code: 200, msg: $notificationData);
      }
    } catch (\Throwable $err) {

      showError(th: $err);
    }
  }

  public static function approveDelete(): void
  {

    // Check if the code has already run in this session
    // if ($_SESSION['preventReload'] != $_GET['appr']) {


    // Set the session variable to indicate that the code has executed
    $_SESSION['preventReload'] = $_GET['appr'];

    if (isset($_GET['req']) && isset($_GET['appr'])) {

      $requester = checkInput($_GET['req']);
      $approver = checkInput($_GET['appr']);
      $getDecision = checkInput($_GET['dec']);
      $requestCode = checkInput($_GET['reqCode']);

      // check if the dec is 100 or 10 (50 is approved and 10 is rejection)

      $decision = match ($getDecision) {
        "10" => "rejected",
        "50" => "approved",
        default => "Unknown", // Handle other cases or provide a default value
      };

      $updateClass = new Update(table: 'requestMgt');

      $identifiers = [
        'requester_id' => $requester,
        'approver_id' => $approver
      ];


      if ($decision === "approved") {
        $updateClass->updateTwoColumns(columns: ['status', 'requesterCode'], columnAnswers: ['Approved', $requestCode], identifiers: $identifiers);
      } elseif ($decision === "rejected") {
        $updateClass->updateTableMulti(column: 'status', columnAnswer: 'Rejected', identifiers: $identifiers);
      }


      // get requester details with the id 

      if ($decision != "Unknown") {

        $details = new SingleCustomerData;
        $req = $details->getCustomerData(custId: $requester, table: ['personal', 'contact']);
        $app = $details->getCustomerData(custId: $approver, table: ['personal', 'contact']);

        // ADDENDUM TO THE REQUESTER DETAIL FROM THE APPROVER AND STATUS

        $req['approverName'] = "{$app['firstName']} {$app['lastName']}";
        $req['decision'] = $decision;
        $subject = "{$app['firstName']} {$app['lastName']} approved your request";

        // email the requester

        toSendEmail(viewPath: 'msg/requestRequest', data: $req, subject: $subject, emailRoute: 'member');

        // Send push notification to the requester about the decision
        PushNotificationClass::sendPushNotification(userId: $requester, message: $subject);

        // show the approver what they have just done
        $app['decision'] = $decision;
        $app['requesterName'] = "{$req['firstName']} {$req['lastName']}";

        // if the source is from the profile page, refresh the page or use javascript to manage it

        $getPP = checkInput($_GET['src']) ?? null;

        if ($getPP === "pp") {
          header("location: /member/ProfilePage");
        } else {
          view('msg/requestApprover', compact('app'));
        }
      }
    } else {
      msgException(401, "How did you get here?");
    }
    // } else {

    //   header('location: /');
    // }
  }

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


  public static function getFriendRequestData()
  {
    try {
      $id = checkInput($_GET['id']);
      $result = [];
      $select = Select::formAndMatchQuery(selection: "SELECT_AND", table: "requestMgt", identifier1: "approver_id", identifier2: "status");
      $getRequesterDataById = Select::selectFn2(query: $select, bind: [$id, 'Request sent']);

      foreach ($getRequesterDataById as $getRequesterDataById1) {

        if ($getRequesterDataById1['requester_id']) {
          $custData = new SingleCustomerData();
          $data = $custData->getCustomerData($getRequesterDataById1['requester_id'], ['personal', 'contact', 'profile_pics']);

          array_push($result, $data);
        } else {
          $result = " No Requester Data";
        }
      }

      msgSuccess(200, $result);
      return $result;
    } catch (\Throwable $th) {
      showError($th);
    }
  }
}

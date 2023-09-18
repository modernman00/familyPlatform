<?php

namespace App\controller\members;

use App\classes\{
  Select,
  SubmitForm,
  Update
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

      // submit to database table Request

      // form the table data 

      $tableData = [
        'approver_id' => $dataFromJs['approver']['approverId'],
        'requester_id' => $dataFromJs['requester']['requesterId'],
        'status' => "Request sent"
      ];
      if (!SubmitForm::submitForm('requestMgt', $tableData)) {
        msgException(406, "requestMgt didn't submit");
      } else {

        $requesterName = "{$dataFromJs['requester']['requesterFirstName']} {$dataFromJs['requester']['requesterLastName']}";
        $approverName = "{$dataFromJs['approver']['approverFirstName']} {$dataFromJs['approver']['approverLastName']}";

        $emailArray = [
          'data' => [
            'name' => $approverName, 
            'email' => 'waledevtest@gmail.com',
            ...$dataFromJs['approver'],
            ...$dataFromJs['requester']
          ],
          'subject' => "$requesterName sent you a family request",
          'viewPath' => $dataFromJs['emailPath'],
          // 'name' => $approverName,
          // 'email' => 'waledevtest@gmail.com',
          // 'email' => $dataFromJs['approver']['email'],
        ];

        sendEmailGeneral($emailArray, 'member');

        msgSuccess(200, $emailArray);
      }
    } catch (\Throwable $err) {

      showError($err);
    }
  }

  public static function approveDelete()
  {

    // Check if the code has already run in this session
    if ($_SESSION['preventReload'] != $_GET['appr']) {


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

        $updateClass = new Update('requestMgt');

        $identifiers = [
          'requester_id' => $requester,
          'approver_id' => $approver
        ];


        if ($decision === "approved") {
          $updateClass->updateTwoColumns(['status', 'requesterCode'], ['Approved', $requestCode], $identifiers);
        } elseif ($decision === "rejected") {
          $updateClass->updateTableMulti('status', 'Rejected', $identifiers);
        }


        // get requester details with the id 

        if ($decision != "Unknown") {

          $details = new SingleCustomerData;
          $req = $details->getCustomerData($requester, ['personal', 'contact']);
          $app = $details->getCustomerData($approver, ['personal', 'contact']);




          // ADDENDUM TO THE REQUESTER DETAIL FROM THE APPROVER AND STATUS

          $req['approverName'] = "{$app['firstName']} {$app['lastName']}";
          $req['decision'] = $decision;
          $subject = "{$app['firstName']} {$app['lastName']} approved your request";

          // email the requester

          toSendEmail('msg/requestRequest', $req, $subject, 'member');

          // show the approver what they have just done
          $app['decision'] = $decision;
          $app['requesterName'] = "{$req['firstName']} {$req['lastName']}";

          view('msg/requestApprover', compact('app'));
        }
      } else {
        msgException(401, "How did you get here?");
      }
    } else {

      header('location: /');
    }
  }
}

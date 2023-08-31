<?php

namespace App\controller\members;

use App\classes\{
  Select,
  SubmitForm,
};

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
          'data' => ['name' => $approverName,'email' => 'waledevtest@gmail.com',
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
}

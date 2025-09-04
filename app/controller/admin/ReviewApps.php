<?php

declare(strict_types=1);

namespace App\Controller\admin;

use App\model\ReviewAppsData;
use App\Controller\BaseController;
use Src\Update;
use Src\functionality\SendEmailFunctionality;
use Src\DeleteFn;

class ReviewApps extends ReviewAppsData
{
    private $id;
    private const REDIRECT = "/admin/reviewApps";

    // get the new application page 
    public function get()
    {
        $result = $this->index();
        BaseController::viewWithCsp('admin/ReviewApps', ['result' => $result, 'no' => 1]);
    }

    // once the $GET IS clicked, use this to get the customers data and set customers id as well
    private function getCustomerData() : array
    {
        $this->id = checkInput($_GET['id']);
        $data = $this->getWithId($this->id);
        $data2 = null;
        foreach ($data as $data2);

        // // Set the customer Id
        // $custNo = $data2['last_name'];
        // $custNo .= $this->rand;
        // $this->custId = $custNo;
        // $_SESSION['terms'] = $data2['terms'];

        return $data2;
    }

    // // update the account status in the decision table 
    private function updateAccountStatus(string $acctStatus)
    {
        $accountUpdate = new Update('account');
        $accountUpdate->updateTable(column: 'status', columnAnswer: $acctStatus, identifier: 'id', identifierAnswer: $this->id);

    //    return $checkUpdateStatus ??= throw new Exception("Error Processing Request - account status");
    }

    public function approve(): void
    {
        try {
            $data = $this->getCustomerData();
            SendEmailFunctionality::email("msg/admin/approve",  "Membership Approval for {$data['firstName']}", $data, 'member');

            $this->updateAccountStatus('approved');
            redirect(self::REDIRECT);
            exit;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function cancel(): void
    {
        try {
            $data = $this->getCustomerData();
            SendEmailFunctionality::email("msg/admin/cancel", "Loan application cancellation", $data,  'member');
            $this->updateAccountStatus('cancel');

            DeleteFn::deleteOneRow("contact", "id", $data['id']);

            redirect(self::REDIRECT);

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function delete(): void
    {
        try {

               

            $data = $this->getCustomerData();
      
            $data['email'] = 'application@loaneasyfinance.com';
            SendEmailFunctionality::email("msg/admin/delete","Delete App", $data,  'admin');
            $this->updateAccountStatus('deleted');

            DeleteFn::deleteOneRow("contact", "id", $data['id']);

            redirect(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function decline(): void
    {
        try {
            $data = $this->getCustomerData();
            SendEmailFunctionality::email("msg/admin/decline",  'Decision', $data, 'member');
            $this->updateAccountStatus('declined');
            redirect(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

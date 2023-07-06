<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\model\ReviewAppsData;
use Exception;
use App\classes\AllFunctionalities;
use App\classes\Delete;

class ReviewApps extends ReviewAppsData
{
    private $id;
    private const REDIRECT = "Location: /admin/reviewApps";

    // get the new application page 
    function get()
    {
        $result = $this->index();
        return view('admin/ReviewApps', ['result' => $result, 'no' => 1]);
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


    // // email functionality
    /**
     * @psalm-param 'admin'|'member' $emailRoute
     */
    private function toSendEmail(string $viewPath, array $data, string $subject, string $emailRoute)
    {
        // generate the data to send the email
        $sendEmailArray = genEmailArray(
            $viewPath,
            $data,
            $subject
        );

        // send the email
        return sendEmailWrapper(var: $sendEmailArray, recipientType: $emailRoute);
    }
    // // update the account status in the decision table 
    private function updateAccountStatus(string $acctStatus)
    {
        $updateClass = new AllFunctionalities();
        $checkUpdateStatus = $updateClass->update('account', 'status', $acctStatus, 'id', $this->id);

       return $checkUpdateStatus ??= throw new Exception("Error Processing Request - account status");
    }

    public function approve(): void
    {
        try {
            $data = $this->getCustomerData();
            $this->toSendEmail("msg/admin/approve", $data, "Membership Approval for {$data['firstName']}", 'member');

            $this->updateAccountStatus('approved');
            header(self::REDIRECT);
            exit;
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function cancel(): void
    {
        try {
            $data = $this->getCustomerData();
            $this->toSendEmail("msg/admin/cancel", $data, "Loan application cancellation", 'member');
            $this->updateAccountStatus('cancel');

           

            $deleteQuery = Delete::formAndMatchQuery(selection:"DELETE_ONE", table:"contact", identifier1:"id", limit:1);
      

            Delete::deleteFn($deleteQuery, [$data['id']]);

            header(self::REDIRECT);

        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function delete(): void
    {
        try {

               

            $data = $this->getCustomerData();
      
            $data['email'] = 'application@loaneasyfinance.com';
            $this->toSendEmail("msg/admin/delete", $data, "Delete App", 'admin');
            $this->updateAccountStatus('deleted');

            // DELETE IT FROM THE CONTACT TABLE
             $deleteQuery = Delete::formAndMatchQuery(selection:"DELETE_ONE", table:"contact", identifier1:"id", limit: "LIMIT 1");

            Delete::deleteFn($deleteQuery, [$data['id']]);

            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function decline(): void
    {
        try {
            $data = $this->getCustomerData();
            $this->toSendEmail("msg/admin/decline", $data, 'Decision', 'member');
            $this->updateAccountStatus('declined');
            header(self::REDIRECT);
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

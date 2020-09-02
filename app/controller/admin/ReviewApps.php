<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\model\ReviewAppsData;
use App\classes\Select;
use Exception;
use App\classes\AllFunctionalities;

class ReviewApps extends AllFunctionalities
{
    private $id;
    private $rand;
    private $custId;

    // function __construct()
    // {

    //     $this->id = checkInput($_GET['id']);
    //     $this->rand = rand(10, 900);
    // }

    // get the new application page 
    function get()
    {
        $select = new ReviewAppsData;
        $result = $select->index();
        return view('admin/ReviewApps', ['result' => $result, 'no' => 1]);
    }

    // // once the $GET IS clicked, use this to get the customers data and set customers id as well
    // private function getCustomerData()
    // {
    //     $select = new Select;
    //     $data = $select->joinManyCondition4('contact', 'personal', 'loaninfo', 'account', 'id', $this->id);

    //     foreach ($data as $data2);
    //     // Set the customer Id
    //     $custNo = $data2['last_name'];
    //     $custNo .= $this->rand;
    //     $this->custId = $custNo;
    //     $_SESSION['terms'] = $data2['terms'];
    //     return $data2;
    // }


    // // email functionality
    private function toSendEmail($viewPath, $data, $subject, $emailRoute)
    {
        // generate the data to send the email
        $sendEmailArray = genEmailArray(
            $viewPath,
            $data,
            $subject,
            null,
            null
        );

        // send the email
        return sendEmailWrapper($sendEmailArray, $emailRoute);
    }
    // // update the account status in the decision table 
    private function updateAccountStatus($acctStatus)
    {
        $checkUpdateStatus = $this->update('account', 'status', $acctStatus, 'id', $this->id);

        if (!$checkUpdateStatus) throw new Exception("Error Processing Request - account status");

        return $checkUpdateStatus;
    }

    function approve()
    {

        try {
           
            $this->toSendEmail("admin/msg/ReviewApps/cancel", $data, "Loan approval for {$data['first_name']}", 'internal');
            $this->updateAccountStatus('approved');
            header('Location: /admin/customer/manage');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function cancel()
    {
        try {           
           
            $this->toSendEmail("admin/msg/ReviewApps/cancel", $data, "Loan application cancellation", 'customer');
            $this->updateAccountStatus('cancel');
            header('Location: /admin/newCustomers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function delete()
    {
        try {
           
           
            $data['email'] = 'application@loaneasyfinance.com';
            $this->toSendEmail("admin/msg/ReviewApps/delete", $data, "Delete App", 'internal');
             $this->updateAccountStatus('deleted');
            header('Location: /admin/newCustomers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function decline()
    {
        try {            
           
            $this->toSendEmail("admin/msg/ReviewApps/decline", $data, 'Decision', 'customer');
            $this->updateAccountStatus('declined');
            header('Location: /admin/newCustomers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

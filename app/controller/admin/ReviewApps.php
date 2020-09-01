<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\model\ReviewAppsData;
use App\classes\Select;
use Exception;
use App\classes\Insert;

class ReviewApps extends Insert
{
    private $id;
    private $rand;
    private $custId;

    function __construct()
    {

        $this->id = checkInput($_GET['id']);
        $this->rand = rand(10, 900);
    }

    // get the new application page 
    function get()
    {
        $select = new ReviewAppsData;
        $result = $select->index();
        return view('admin/ReviewApps', ['result' => $result, 'no' => 1]);
    }

    // once the $GET IS clicked, use this to get the customers data and set customers id as well
    private function getCustomerData()
    {
        $select = new Select;
        $data = $select->joinManyCondition4('contact', 'personal', 'loaninfo', 'account', 'id', $this->id);

        foreach ($data as $data2);
        // Set the customer Id
        $custNo = $data2['last_name'];
        $custNo .= $this->rand;
        $this->custId = $custNo;
        $_SESSION['terms'] = $data2['terms'];
        return $data2;
    }


    // email functionality
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
    // update the account status in the decision table 
    private function updateAccountStatus($acctStatus)
    {
        $checkUpdateStatus = $this->update('decision', 'account_status', $acctStatus, 'id', $this->id);

        if (!$checkUpdateStatus) throw new Exception("Error Processing Request - account status");

        return $checkUpdateStatus;
    }

    private function insertToLogin($data)
    {
        //insert username and password to the login table
        $checkIfCustomerIsNew = $this->select_count('login', 'email', $data['email']);

        if ($checkIfCustomerIsNew  < 1) {

            $loginData = array(
                'email' => $data['email'],
                'customerNo'  => $this->custId,
                'password' => $data['password']
            );

            $insertToLogin = $this->insertData_NoRedirect($loginData, 'login');

            if (!$insertToLogin) throw new Exception("Error Processing Request - Login details not updated", 1);
        }
    }

    private function insertToLoanInfoLive($data)
    {
        $loanInfoData = array(
            'loanamount' => $data['loanamount'],
            'terms' => $data['terms'],
            'id'  => $data['id'],
            'repayment' => $data['repayment'],
            'monthly' => $data['monthly'],
            'interest'  => $data['interest'],
            'customerNo' => $this->custId
        );

        $checkLoanData = $this->insertData_NoRedirect($loanInfoData, 'loaninfolive');

        if (!$checkLoanData) throw new Exception("Error Processing Request - Loanlive data details not updated", 1);
    }

    private function insertToPaymentChargesDefaultScoreAcctMgt(array $data)
    {
        $data = array('id'  => $data['id'], 'customerNo' => $this->custId);
        $tableArr = ['charges', 'default_payment', 'payment', 'paymentstatus', 'acct_mgt'];

        try {
            for ($i = 0; $i < count($tableArr); $i++) {
                $this->insertData_NoRedirect($data, $tableArr[$i]);
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private function updatePersonalContactAddress($data)
    {
        $data = array('customerNo' => $this->custId, 'id'  => $data['id']);
        $tableArr = ['personal', 'contact', 'address'];

        try {
            for ($i = 0; $i < count($tableArr); $i++) {
                $this->updateMultiplePOST($data, $tableArr[$i], 'id');
            }
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    function approve()
    {

        try {
            $data = $this->getCustomerData();
            $this->insertToLogin($data);
            $this->insertToLoanInfoLive($data);
            $this->insertToPaymentChargesDefaultScoreAcctMgt($data);
            $this->updatePersonalContactAddress($data);
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
            $data = $this->getCustomerData();
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
           
            $data = $this->getCustomerData();
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
            $data = $this->getCustomerData();
            $this->toSendEmail("admin/msg/ReviewApps/decline", $data, 'Decision', 'customer');
            $this->updateAccountStatus('declined');
            header('Location: /admin/newCustomers');
        } catch (\Throwable $th) {
            showError($th);
        }
    }
}

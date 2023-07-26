<?php

declare(strict_types=1);

use App\model\EmailData;
use App\classes\Select;

function sendEmailMember()
{
    $notifyMember = new EmailData('member');
    return $notifyMember->getEmailData();
}

function sendEmailPayment()
{
    $notifyCustomer = new EmailData('payment');
    return $notifyCustomer->getEmailData();
}

function sendEmailSelf()
{
    $notifyCustomer = new EmailData('admin');
    return $notifyCustomer->getEmailData();
}

function getAllEmails()
{
    $queryData = [
        'selection' => 'SELECT_COL_ID',
        'column' => 'email',
        'table' => 'account',
        'identifier1' => 'status',
        'bind' => ['approved']
    ];

    $result = Select::combineSelect($queryData, 'selectFn2', 'ONE_IDENTIFIER_COLUMN');

    return array_column($result, 'email');
}





// SEND FORMATTED EMAIL

/**
 * You have to generate the $var using the genEmailArray()
 * $var is an array of the viewPath, data, subject, email
 * 'viewPath' => ,
 *  data'=>
 * 'subject'=>
 * recipientType can be either member or admin
 */

function sendEmailWrapper($var, $recipientType)
{
    $notifyCustomer = new EmailData($recipientType);

    if (!defined('PASS')) {
        $notifyCustomer->getEmailData();
    }

    $data = $var['data'];

    ob_start();
    $emailPage = view($var['viewPath'], compact('data'));
    $emailContent = ob_get_contents();
    ob_end_clean();

    $email =  checkInputEmail($data['email']);
    $name = $data['firstName'] ?? $data['first_name'] ?? 'there';
    $file = $var['file'];
    $filename = $var['fileName'];

    //  mail("waledevtest@gmail.com", "TEST_EMAIL", $email);

    sendEmail($email, $name, $var['subject'], $emailContent, $file, $filename);
}

/**
 * 
 * @param mixed $viewPath 
 * @param mixed $data 
 * @param mixed $subject 
 * @param mixed $file 
 * @param mixed $fileName 
 * @return array 
 */

function genEmailArray($viewPath, $data, $subject, $file = null, $fileName = null): array
{
    return [
        'viewPath' => $viewPath,
        'data' => $data,
        'subject' => $subject,
        'file' => $file,
        'fileName' => $fileName
    ];
}

/**
 * 
 * @param mixed $array 'viewPath' => string $viewPath, 'data' => array $data,'subject' => string $subject, 'file' => $file, 'fileName' => $fileName
 * @param mixed $recipient 
 * @return void 
 */

function sendEmailGeneral($array, $recipient)
{
    $notifyCustomer = new EmailData($recipient);

    if (!defined('PASS')) {
        $notifyCustomer->getEmailData();
        // if it is still not set, then throw an error
        if (!defined('PASS')) {
            msgException(401, 'Email credentials (constant) not set');
        }

        $data = $array['data'];

        ob_start();
        // $emailPage = view($array['viewPath'], compact('data'));
        view($array['viewPath'], compact('data'));

        $emailContent = ob_get_contents() ?? throw new Exception('email content not available');

        ob_end_clean();

        $email =  checkInputEmail($data['email']);


        if (!$email) {
            msgException(401, "Email not provided");
        }

        $name = checkInput($data['name']) ?? 'there';

        sendEmail($email, $name, $array['subject'], $emailContent);
    }
}

/**
 * 
 * @param mixed $data this must include firstName and lastName 
 * @param mixed $viewPath 
 * @param mixed $subject 
 * @return void 
 * @throws \Psr\Log\InvalidArgumentException 
 * @throws \Exception 
 */

function sendEmailAll($data, $viewPath, $subject)
{
    $notifyCustomer = new EmailData("member");

    if (!defined('PASS')) {
        $notifyCustomer->getEmailData();

        if (!defined('PASS')) {
            msgException(401, 'Email credentials (constant) not set');
        }

        if (!$data) {
            $error = "Data not provided";
            echo json_encode($error);
            throw new Exception($error);
        }

        ob_start();

        $emailPage = view($viewPath, compact('data'));
        // $emailContent = ob_get_contents();
        $emailContent = ob_get_clean();
        // ob_end_clean();

        $email =  checkInputEmail($data['email']);


        if (!$email) {
            $error = "Email not provided";
            echo json_encode($error);
            throw new Exception($error);
        }

        $fName = $data['firstName'];
        $lName = $data['lastName'];

        $name = "$fName $lName";

        // $name = checkInput($data['fullName']) ?? checkInput($data['firstName'])?? 'there';
        // $file = $data['file'];
        // $filename = $data['fileName'];

        sendEmail($email, $name, $subject, $emailContent);
    }
}

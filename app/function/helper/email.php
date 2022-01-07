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
    $dataArray = ['selection' => 'SELECT_COL_ID', 'column' => 'email', 'table' => 'account', 'identifier1' => 'status', 'bind' => ['approved']];
    $result = Select::combineSelect($dataArray, 'selectFn2', 'ONE_IDENTIFIER_COLUMN');
    for ($r = 0; $r < count($result); $r++) {
        $email1[] = $result[$r]['email'];
    }
    return $email1;
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
    $name = $data['first_name'] ?? 'there';
    $file = $var['file'];
    $filename = $var['fileName'];

        // mail("waledevtest@gmail.com", TEST_EMAIL, checkInputEmail($data['email']));

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

        if (!defined('PASS')) {
            msgException(401, 'Email credentials (constant) not set');
        }

        $data = $array['data'];

        ob_start();
        $emailPage = view($array['viewPath'], compact('data'));
        $emailContent = ob_get_contents();
        ob_end_clean();

        $email =  checkInputEmail($data['email']);


        if (!$email) {
            $error = "Email not provided";
            echo json_encode($error);
            throw new Exception($error);
        }

        $name = checkInput($data['name']) ?? 'there';
        // $file = $array['file'];
        // $filename = $array['fileName'];

        sendEmail($email, $name, $array['subject'], $emailContent);
    }
}

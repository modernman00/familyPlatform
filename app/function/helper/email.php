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

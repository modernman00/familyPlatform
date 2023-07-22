<?php

use App\model\EmailData;
use \Rollbar\Rollbar;
use \Rollbar\Payload\Level;

// Rollbar::init(array(
//     'access_token' => 'aabae2591eac40e3b26cfeb2da28a5fc',
//     'environment' => 'production'
// ));

//376306559e2124e72b1577cb75a9a6475

/**
 * @return true
 */
function loggedDetection(string $filename, string $receivingEmail): bool
{
    //TODO send text to the user with the code
    $emailSender = new EmailData('admin');
    $emailSender->getEmailData();
    $msg = "Hello, <br><br> This is a notification that a <strong>logged -in</strong> has been detected from this file : $filename at this time: " .  date("h:i:sa") . "  and with this IP address: " . getUserIpAddr() . " <br><br>  IT Security Team";

    sendEmail($receivingEmail, 'logged-in', 'LOGGED-IN DETECTION', $msg);
    return true;
}

function notifyCustOfLogIn($data)
{
    $generateEmailArray = genEmailArray("customer/msg/loginDetection", $data, "LOGGED-IN DETECTION", null, null);
    return sendEmailWrapper($generateEmailArray, 'customer');
}

/**
 * It will generate the token, update the login table using the customer No from the $data and send token to customer 
 * @param mixed $data -  must contain customerNo and email
 * @return mixed
 * @throws \Exception 
 * @throws \PDOException 
 */
function generateSendTokenEmail($data): mixed
{
    $id = $data['id'];
    // 1. check if email exists 
    $email = checkInputEmail($data['email']);

    //2. generate token and update table
    $deriveToken = generateUpdateTableWithToken($id);
    //TODO send text to the user with the code

    //3. ACCOMPANY EMAIL CONTENT             
    $emailData = ['token' => $deriveToken, 'email' => $email];
    $generateEmailArray = genEmailArray(viewPath: "msg/customer/token", data: $emailData, subject: "TOKEN");

    return sendEmailWrapper(var: $generateEmailArray, recipientType: 'member');
}


function checkInput($data): mixed
{
    if ($data !== null) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = strip_tags($data);
    $data = htmlentities($data);
    $data = preg_replace('/[^0-9A-Za-z.@-]/', ' ', $data);
    return $data;}
    else{
        return false;
    }
}

function checkInputImage($data): string|null
{
    if ($data !== null) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strip_tags($data);
        $data = htmlentities($data);
        $data = preg_replace('/[^0-9A-Za-z.@-_]/', ' ', $data);
        return $data;
    } else {
        exit();
    }
}

function checkInputEmail($data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    $data = htmlentities($data);
    $data = filter_var($data, FILTER_SANITIZE_EMAIL);
    return $data;
}

function returnErrorCode(int $errCode, $errObj)
{
    http_response_code($errCode);
    echo http_response_code();
    return showError($errObj);
}

function returnSuccessCode($msg): void
{
    http_response_code(200);
    echo http_response_code();
    echo json_encode($msg);
}

/**
 * 
 * @param mixed $errCode 401, 404 
 * @param mixed $msg message that goes in side the throw exception
 * @return never 
 * @throws \Exception 
 */
// 8/9/22- i commented out the json code because I want to throw the exception and catch it before using the json
function msgException(int $errCode, string | int  $msg)
{
    // http_response_code($errCode); // sets the response to 406

    // echo http_response_code(); // echo the new response code
    // echo json_encode(['message' => $msg]);
    // //   echo json_encode($msg);
    // // Rollbar::log(Level::info(), $msg);
    throw new Exception($msg, $errCode);
}

function msgSuccess(int $code, string|array $msg, mixed $token = null): void
{
    http_response_code($code); // sets the response to 406
    // echo http_response_code(); // echo the new response code
    echo json_encode(['message' => $msg, 'token' => $token]);
}

function msgServerSent(string|array $data, string | int $id, string $event): void
{

    $get = json_encode($data);
    echo "retry: 1000\n";   // one seconds
    echo "id: $id\n";
    echo "event: $event\n";
    echo "data: {$get}\n\n";
    ob_flush();
    flush();
}

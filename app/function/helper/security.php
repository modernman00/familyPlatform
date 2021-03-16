<?php 
use App\model\EmailData;

function loggedDetection($filename)
{
     //TODO send text to the user with the code
    $emailSender = new EmailData('internal');
    $emailSender->getEmailData();
    $msg = "Hello, <br><br> This is a notification that a <strong>logged -in</strong> has been detected from this file : $filename at this time: ".  date("h:i:sa") . "  and with this IP address: ". getUserIpAddr(). " <br><br>  IT Security Team";

    sendEmail('wale@loaneasyfinance.com','logged-in', 'LOGGED-IN DETECTION', $msg);
}

function notifyCustOfLogIn($data) : mixed
{
    $generateEmailArray = genEmailArray("customer/msg/loginDetection", $data, "LOGGED-IN DETECTION", null,null);
    return sendEmailWrapper($generateEmailArray, 'customer');   
}

/**
 * It will generate the token, update the login table using the customer No from the $data and send token to customer 
 * @param mixed $data -  must contain customerNo and email
 * @return void 
 * @throws \Exception 
 * @throws \PDOException 
 */
function generateSendTokenEmail($data) : mixed
{
        $id = $data['id'];
        $email = checkInputEmail($data['email']);
        $deriveToken = generateUpdateTableWithToken($id);  
        //TODO send text to the user with the code
        // ACCOMPANY EMAIL CONTENT             
        $emailData = ['token' => $deriveToken, 'email' => $email];
        $generateEmailArray = genEmailArray("msg/customer/token", $emailData, "FAMILY - TOKEN", null,null);
        return sendEmailWrapper($generateEmailArray, 'member');      
}


function checkInput($data) : mixed
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    $data = htmlentities($data, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $data = preg_replace('/[^0-9A-Za-z.@-]/', ' ', $data);
    return $data;
}

function checkInputImage($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    $data = htmlentities($data, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $data = preg_replace('/[^0-9A-Za-z.@-_]/', ' ', $data);
    return $data;
}

function checkInputEmail($data): string
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = strip_tags($data);
    $data = htmlentities($data, ENT_QUOTES | ENT_HTML5, "UTF-8");
    $data = filter_var($data, FILTER_SANITIZE_EMAIL);
    return $data;
}


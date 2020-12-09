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

function notifyCustOfLogIn($data)
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
function generateSendTokenEmail($data)
{
        $id = $data['id'];
        $email = $data['email'];
        $deriveToken = generateUpdateTableWithToken($id);  
        //TODO send text to the user with the code
        // ACCOMPANY EMAIL CONTENT             
        $emailData = ['token' => $deriveToken, 'email' => $email];
        $generateEmailArray = genEmailArray("msg/customer/token", $emailData, "FAMILY - TOKEN", null,null);
        return sendEmailWrapper($generateEmailArray, 'member');      
}

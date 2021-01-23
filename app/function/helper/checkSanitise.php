<?php

use App\classes\{
    Sanitise,
    Select,
    VerifyPassword,
    Update
};

/**
 * 
 * @param mixed $inputData  this is the form data 
 * @param mixed $databaseData this must be database data that already has the database password
 * @return void 
 * @throws \Exception 
 */
function checkPassword($inputData, $databaseData)
{
   $passwordCheck = new VerifyPassword($inputData['password'], $databaseData['password'], $databaseData['id'], 'account');
    $passSuccess = $passwordCheck->passMgt();
    if (!$passSuccess) {
        throw new Exception("<h1>Password could not be verified</h1>");
    }
    return $passSuccess;

    // $pass = new VerifyPassword($inputData['password'], $databaseData['password'], $databaseData['id'], 'account');
    // return $pass->passMgt();
}

/**
 * find email from login table and output data
 *
 * @param [type] $inputData form data as a array $inputData['email']
 *
 * @return void
 */
function useEmailToFindData($inputData)
{
    var_dump($inputData['email']);
    $select = new Select;
    $data = $select->select_from('account', 'email', $inputData['email']);
    if (!$data) {
        throw new Exception("<h1>We cannot find your email</h1>");
    }
    foreach ($data as $data);
    return $data;
}

/**
 * sanitise and get the clean data
 * @param mixed $inputData  - $_post or form input
 * @param mixed $minMaxData  - set metric for min and max and the input name(data) you want to check
 * @return mixed 
 * @throws \Exception 
 */
function getSanitisedInputData($inputData, $minMaxData = NULL)
{
   // printArr($inputData);
    $sanitise = new Sanitise($inputData, $minMaxData);
    $sanitisedData = $sanitise->getData();
    //printArr($sanitisedData);
    $error = $sanitise->error;
    if ($error) {
        throw new Exception('<h1><b>There is a problem with your input.</b></h1><br>' . implode(', <br>', $error));
    }

    return $sanitisedData;
}
/**
 * to generate random byte - token
 * @return string|array|null|false 
 * @throws \Exception 
 */

function generateAuthToken()
{
    $token = mb_strtoupper(bin2hex(random_bytes(4)));
    return $token;
}
/**
 * Helps to generate token, and it updates the login table as well
 *
 * @param [type] $customerNo - input the customer no
 *
 * @return void
 */
function generateUpdateTableWithToken($customerId)
{
    //5. generate code
    $token = generateAuthToken();
    $_SESSION['2FA_token_ts'] = time();
    //6.  update login account table with the code
    $updateCodeToCustomer = new Update('account');
    $updateCodeToCustomer->updateTable('token', $token, 'id', $customerId);
    if (!$updateCodeToCustomer) throw new Exception("<h1>Error : Could not update token</h1>E", 1);
    return $token;
}

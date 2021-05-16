<?php

use App\classes\{
    Sanitise,
    Select,
    Update,
    AllFunctionalities
};

/**
 * 
 * @param mixed $inputData  this is the form data 
 * @param mixed $databaseData this must be database data that already has the database password
 * @return mixed 
 * @throws \Exception 
 */
function checkPassword($inputData, $databaseData)
{

    $textPassword = $inputData['password'];
    $dbPassword = $databaseData['password'];
    $id = $databaseData['id'];
    $table = "account";
    $options = array('cost' => 12);

    if (password_verify($textPassword, $dbPassword) === false) {
         http_response_code(406); // sets the response to 406
        echo http_response_code(); // echo the new response code
        // throw new Exception("<h1>We cannot find your email</h1>");
        // $error = 'There is a problem with your credential!';
        // echo json_encode($error);
        throw new Exception("<h1>There is a problem with your login credential!</h1>");
    }
    if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT, $options)) {
        // If so, create a new hash, and replace the old one
        $newHash = password_hash($textPassword, PASSWORD_DEFAULT, $options);
        $data = ['password' => $newHash, 'id' => $id];
        $passUpdate = new AllFunctionalities();
        return $passUpdate->updateMultiplePOST($data, $table, 'id');
    }
}


/**
 * 
 * @param mixed $inputData form data as a array $inputData['email']
 * @return mixed 
 * @throws \Exception 
 */
function useEmailToFindData($inputData): array
{
    $select = new Select;
    $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'account', identifier1: 'email');
    $data = $select->selectFn(query: $query, bind: [$inputData['email']]);

    if (!$data) {
        http_response_code(406); // sets the response to 406
        echo http_response_code(); // echo the new response code
        // $theError = "We cannot find your email";
        // echo json_encode($theError);
        throw new Exception("We cannot find your email");
        // exit;
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
    $sanitise = new Sanitise($inputData, $minMaxData);
    $sanitisedData = $sanitise->getData();
    $error = $sanitise->error;
    if ($error) {
        http_response_code(406); // sets the response to 406
        echo http_response_code(); // echo the new response code
        $theError = "There is a problem with your input<br>" . implode('; <br>', $error);
        echo json_encode($theError);
        exit;
        // throw new Exception('<h1><b>There is a problem with your input.</b></h1><br>' . implode('; <br>', $error));
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
    return mb_strtoupper(bin2hex(random_bytes(4)));
}
/**
 * Helps to generate token, and it updates the login table as well
 * @param mixed $customerId 
 * @return string|array|null|false 
 * @throws \Exception 
 */
function generateUpdateTableWithToken($customerId)
{
    //5. generate code
    $token = generateAuthToken();

    //6.  update login account table with the code
    $updateCodeToCustomer = new Update('account');
    $updateCodeToCustomer->updateTable('token', $token, 'id', $customerId);
    if (!$updateCodeToCustomer) {
        throw new Exception("<h1>Error : Could not update token</h1>E", 1);
    }
    $_SESSION['2FA_token_ts'] = time();
    $_SESSION['identifyCust'] = $customerId;
    return $token;
}

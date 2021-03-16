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
    try {
        $textPassword = $inputData['password'];
        $dbPassword = $databaseData['password'];
        $id = $databaseData['id'];
        $table = "account";
        $options = array('cost' => 12);

        if (password_verify($textPassword, $dbPassword) === false) {
            $error = 'There is a problem with your credential!';
            echo json_encode(['error' => $error]);
            throw new Exception("<h1>There is a problem with your login credential! $textPassword ---- $dbPassword</h1>");
        }
        if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT, $options)) {
            // If so, create a new hash, and replace the old one
            $newHash = password_hash($textPassword, PASSWORD_DEFAULT, $options);
            $data = ['password' => $newHash, 'id' => $id];
            $passUpdate = new AllFunctionalities();
            return $passUpdate->updateMultiplePOST($data, $table, 'id');
        }
    } catch (\Exception $e) {
        showError($e);
    } catch (\PDOException $e) {
        showError($e);
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
    $_SESSION['2FA_token_ts'] = time();
    //6.  update login account table with the code
    $updateCodeToCustomer = new Update('account');
    $updateCodeToCustomer->updateTable('token', $token, 'id', $customerId);
    if (!$updateCodeToCustomer) {
        throw new Exception("<h1>Error : Could not update token</h1>E", 1);
    }
    return $token;
}

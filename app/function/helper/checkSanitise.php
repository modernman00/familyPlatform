<?php

use App\classes\{
    Sanitise,
    Select,
    Update,
    AllFunctionalities
};

/**
 * @param mixed $inputData  this is the form data 
 * @param mixed $databaseData this must be database data that already has the database password
 *
 * @return true
 *
 * @throws \Exception 
 */
function checkPassword(#[SensitiveParameter] array $inputData, #[SensitiveParameter] array $databaseData): bool
{

    $textPassword = $inputData['password'];
    $dbPassword = $databaseData['password'];
    $id = $databaseData['id'];
    $table = "account";
    $options = array('cost' => 12);

    if (password_verify($textPassword, $dbPassword) === false) {
        msgException(401, 'There is a problem with your login credential! - Password');
        }
    
    if (password_needs_rehash($dbPassword, PASSWORD_DEFAULT, $options)) {
        // If so, create a new hash, and replace the old one
        $newHash = password_hash($textPassword, PASSWORD_DEFAULT, $options);

        $data = ['password' => $newHash, 'id' => $id];
        $passUpdate = new AllFunctionalities();
        $result = $passUpdate->updateMultiplePOST($data, $table, 'id');

        if (!$result) {
            msgException(422, 'Password could not be updated');
            return false;
        }

       
    }
     return true;
}


/**
 * 
 * @param mixed $inputData form data as a array $inputData['email']
 * @return array
 * @throws \Exception 
 */
function useEmailToFindData($inputData)
{
    $email = $inputData['email'];

    $query = Select::formAndMatchQuery(selection: 'SELECT_ONE', table: 'account', identifier1: 'email');
    $emailData = Select::selectFn2(query: $query, bind: [$email]);

    if (empty($emailData)) {
        msgException(401, 'We do not recognise your account');
    }

    return $emailData[0];
}

/**
 * 
 * @param mixed $inputData form data as a $email
 * @return mixed 
 * @throws \Exception 
 */
function checkIfEmailExist(string $email): mixed
{

    $query = Select::formAndMatchQuery(selection: 'SELECT_COL_ID', table: 'account', identifier1: 'email', column: "email");
    $data = Select::selectCountFn2(query: $query, bind: [$email]);

    if (!$data) {

        msgException(404, "We cannot find your email");
    }
    foreach ($data as $data);
    return $data;
}

/**
 * 
 * @param string $col  the first column could be "id"
 * @param string $col2 the second column, could be "status'
 * @param array $data , could be the postdata but must have a email
 * @return mixed 
 * @throws \Exception 
 */
function findTwoColUsingEmail(string $col, string $col2, array $data): mixed
{
    $outcome = useEmailToFindData($data);
    $colOne =  $outcome["$col"];
    $colTwo =  $outcome["$col2"];


    $query = Select::formAndMatchQuery(selection: 'SELECT_TWO_COLS_ID', table: 'account', identifier1: 'email', column: $col, column2: $col2);
    $result = Select::selectFn2(query: $query, bind: [$colOne, $colTwo]);

    if (!$result) {

        msgException(404, "We cannot locate the information");
    }
    foreach ($result as $data);
    return $data;
}

/**
 * 
 * @param string $col  the first column could be "id"
 * @param array $data , could be the postdata but must have a email
 * @return mixed 
 * @throws \Exception 
 */
function findOneColUsingEmail(string $col, array $data): mixed
{
    $email =  $data['email'];

    $query = Select::formAndMatchQuery(selection: 'SELECT_COL_ID', table: 'account', identifier1: 'email', column: $col);

    $data = Select::selectFn2(query: $query, bind: [$email]);

    if (!$data) {

        msgException(404, "We cannot locate the information");
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
function getSanitisedInputData(array $inputData, $minMaxData = NULL)
{
    $sanitise = new Sanitise($inputData, $minMaxData);
    $sanitisedData = $sanitise->getCleanData();
    $error = $sanitise->error;
    if ($error) {

        $theError = "There is a problem with your input<br>" . implode('; <br>', $error);
        msgException(400, $theError);
    }

    return $sanitisedData;
}
/**
 * to generate random byte - token
 *
 * @throws \Exception 
 */
function generateAuthToken(): string
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

        msgException(406, "Error : Could not update token");
    }
    $_SESSION['2FA_token_ts'] = time();
    $_SESSION['identifyCust'] = $customerId ?? "TEST";
    return $token;
}

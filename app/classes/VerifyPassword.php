<?php


namespace App\classes;

use App\classes\AllFunctionalities;
use \PDOException;
use Exception;

class VerifyPassword extends AllFunctionalities
{
    public $error;
    private $passwordHash;
    private $inputPass;
    private $dbPass;
    private $options = array('cost' => 15);
    private $hash = PASSWORD_DEFAULT;

    // if password is successfully verified or not
    public function __construct($inputPass,  $dbPass, $customerId, $table)
    {
        $this->inputPass = $inputPass;
        $this->dbPass = $dbPass;
        $this->id = $customerId;
        $this->table = $table;
        
    }


    // hash the password once verified
    protected function hashPassword()
    {
        if (empty($this->inputPass)) {
            throw new Exception("Empty password");
        }
        if (password_verify($this->inputPass, $this->dbPass) === false) {
            $this->error = 'Your password is incorrect!';
            throw new Exception("<h1>Your password is incorrect!</h1>");
        }

        if(password_needs_rehash($this->dbPass,
            $this->hash,
            $this->options
        )) {
              $this->passwordHash = password_hash(
                $this->inputPass,
                $this->hash,
                $this->options
            );
        } 
   
        return $this->passwordHash;
    }

    // update table with the new password
    public function updateTableWithNewPass()
    {
        $this->hashPassword(); // hash the password

        // create the data
        $data = [
            'password' => $this->passwordHash,
            'id' => $this->id
        ];
        // update the database
        $outcome = $this->updateMultiplePOST($data, $this->table, 'id');

        return $outcome;
    }
}

<?php


namespace App\classes;

use App\classes\AllFunctionalities;
use \PDOException;
use Exception;

class VerifyPassword extends AllFunctionalities
{
    public $error;
    private $outcome;
    private $passwordHash;
    private $inputPass;
    private $dbPass;

    // if password is successfully verified or not
    public function __construct($inputPass, $dbPass, $customerId, $table)
    {
        $this->inputPass = $inputPass;
        $this->dbPass = $dbPass;
        $this->id = $customerId;
        $this->table = $table;

        if (empty($this->inputPass)) {
            throw new Exception("Empty password");
        }
        if (password_verify($this->inputPass, $this->dbPass) === false) {
            $this->error = 'Your password is incorrect!';
            throw new Exception("<h1>Your password is incorrect!</h1>");
        }
      //  return $this->outcome = 1;
    }

    // hash the password once verified
    protected function hashPassword()
    {
        $currentHashAlgorithm = PASSWORD_DEFAULT;
        $currentHashOptions = array('cost' => 15);
        $passwordNeedsRehash = password_needs_rehash(
            $this->dbPass,
            $currentHashAlgorithm,
            $currentHashOptions
        );
        // if ($passwordNeedsRehash === true) {
        // Save new password hash (PASSWORD NEWLY HASHED)
        $this->passwordHash = password_hash(
            $this->inputPass,
            $currentHashAlgorithm,
            $currentHashOptions
        );
      //  }

        return $this->passwordHash;
    }

    // update table with the new password
    protected function updateTableWithNewPsw()
    {
        try {
            $data = [
                'password' => $this->passwordHash,
                'id' => $this->id
            ];

            $outcome = $this->updateMultiplePOST($data, $this->table, 'id', $this->id, 'id');

            return $outcome;
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public function passMgt()
    {
        try {
            $this->hashPassword($this->dbPass, $this->inputPass);
            $result = $this->updateTableWithNewPsw($this->id, $this->table);
            return $result;
    
        } catch (\Exception $e) {
            showError($e);
        } catch (PDOException $e) {
            showError($e);
        }
    }
}

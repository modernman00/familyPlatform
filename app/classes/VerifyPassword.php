<?php

namespace App\classes;

use App\classes\AllFunctionalities;
use Exception;

class VerifyPassword extends AllFunctionalities
{
    public $error;
    private $options = array('cost' => 12);
    private $hash = PASSWORD_DEFAULT;

    // if password is successfully verified or not
    public function __construct($inputPass,  $dbPass, $id, $table)
    {}


    // hash the password once verified
    public function hashPassword() : mixed
    {
    
        if (empty($this->inputPass)) {
            echo json_encode (['error'=> "Empty password"]);
            throw new Exception("Empty password");
        }
     
        if (password_verify($this->inputPass, $this->dbPass) === false) {
            $this->error = 'Your password is incorrect!';
            echo json_encode (['psdError'=> $this->error]);
            throw new Exception("<h1>Your password is incorrect!</h1>");
        }
        
        // check if the password needs rehash.
        if (password_needs_rehash($this->dbPass, PASSWORD_DEFAULT, $this->options)) : mixed {
            $this->passwordHash = password_hash($this->inputPass, PASSWORD_DEFAULT, $this->options);
            // create the data
            $data = [
                'password' => $this->passwordHash,
                'id' => $this->id
            ];
            // update the database
            return $this->updateMultiplePOST($data, $this->table, 'id');
        }
    }

}

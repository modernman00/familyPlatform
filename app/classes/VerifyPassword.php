<?php

namespace App\classes;

use App\classes\AllFunctionalities;
use Exception;

class VerifyPassword extends AllFunctionalities
{
    /**
     * @var null|string
     *
     * @psalm-var 'Your password is incorrect!'|null
     */
    public $error;
    private array $options = ['cost' => 12];

    /**
     * @var null|string
     */
    private $passwordHash;

    public function __construct(
        private string $inputPass,
        private string $dbPass,
        private int $id,
        private string $table
    ) {}

    public function hashPassword(): mixed
    {
        if (empty($this->inputPass)) {
            echo json_encode(['error' => "Empty password"]);
            throw new Exception("Empty password");
        }

        if (password_verify($this->inputPass, $this->dbPass) === false) {
            $this->error = 'Your password is incorrect!';
            echo json_encode(['psdError' => $this->error]);
            throw new Exception("<h1>Your password is incorrect!</h1>");
        }

        if (password_needs_rehash($this->dbPass, PASSWORD_DEFAULT, $this->options)) {
            $this->passwordHash = password_hash($this->inputPass, PASSWORD_DEFAULT, $this->options);
            $data = [
                'password' => $this->passwordHash,
                'id' => $this->id
            ];
            return $this->updateMultiplePOST($data, $this->table, 'id');
        } else {
            throw new Exception("faulty -- not good passport");
        }
    }
}

<?php

namespace App\classes;
use \PDO;
use \PDOException;
use App\classes\CheckToken;

class Db extends CheckToken
{
        private $serverName = getenv('DB_HOST');
        private $dbName = getenv('DB_NAME');
        private $username = getenv('DB_USERNAME');
        private $password = getenv("DB_PASSWORD");

    function connect()
    {
        try {
                 //create a new connection variable
        $conn = new PDO("mysql:host=$this->serverName; dbname=$this->dbName", $this->username, $this->password);

        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;

        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            //$errorLine = $e->getLine();
            echo "Connection Failed: $errorMsg ";
        }
   
    }

    function connectSql()
    {
        try {
                $conn = mysqli_connect($this->serverName, $this->username, $this->password, $this->dbName);
                return $conn;
        } catch (\Throwable $e) {
                echo "Connection Failed" . $e->getMessage();
        }
   
    }
}



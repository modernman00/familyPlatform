<?php

namespace App\classes;

use \PDO;
use \PDOException;
use App\classes\CheckToken;

class Db extends CheckToken
{
    private static function dbVariables()
    {
        return [
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv("DB_PASSWORD")
        ];
    }

    public function connect()
    {
        try {
            $dbVar = self::dbVariables();
            $conn = new PDO("mysql:host={$dbVar['host']}; dbname={$dbVar['name']}", $dbVar['username'], $dbVar['password'], array(
                PDO::ATTR_PERSISTENT => true
            ));

            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            echo "Connection Failed: $errorMsg ";
        }
    }

    public static function connect2()
    {
        try {
            $dbVar = self::dbVariables();
            $conn = new PDO("mysql:host={$dbVar['host']}; dbname={$dbVar['name']}", $dbVar['username'], $dbVar['password'], array(
                PDO::ATTR_PERSISTENT => true
            ));

            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            echo "Connection Failed: $errorMsg ";
        }
    }



    function connectSql()
    {
        try {
            return mysqli_connect($this->serverName, $this->username, $this->password, $this->dbName);
        } catch (\Throwable $e) {
            echo "Connection Failed" . $e->getMessage();
        }
    }
}

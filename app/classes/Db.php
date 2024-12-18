<?php

namespace App\classes;

use \PDO;
use \PDOException;
use App\classes\CheckToken;

class Db extends CheckToken
{
    public const BR = "<br>"; // can't be changed
    private static function dbVariables(): array
    {
        return [
            'host' => getenv(name: 'DB_HOST'),
            'name' => getenv(name: 'DB_NAME'),
            'username' => getenv(name: 'DB_USERNAME'),
            'password' => getenv(name: "DB_PASSWORD"),
            'charset' => 'utf8mb4'
        ];
    }

    public function connect()
    {
        // apply singleton pattern by checking if db connection is already established before connecting again
        $conn = null;
        try {
            if (!isset($conn)) {
                $dbVar = self::dbVariables();
                $conn = new PDO("mysql:host={$dbVar['host']}; dbname={$dbVar['name']}; charset={$dbVar['charset']}", username: $dbVar['username'],  password: $dbVar['password'], options: array(
                    PDO::ATTR_PERSISTENT => true
                ));

                $conn->setAttribute(attribute: PDO::ATTR_DEFAULT_FETCH_MODE, value: PDO::FETCH_ASSOC);
                $conn->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(attribute: PDO::ATTR_EMULATE_PREPARES, value: false);

                return $conn;
            } else {
                return $conn;
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }

    public static function connect2()
    {
        try {
            if (!isset($conn)) {
                $dbVar = self::dbVariables();
                $conn = new PDO("mysql:host={$dbVar['host']}; dbname={$dbVar['name']}; charset={$dbVar['charset']}", $dbVar['username'], $dbVar['password'], array(
                    PDO::ATTR_PERSISTENT => true
                ));

                $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                return $conn;
            } else {
                return $conn;
            }
        } catch (PDOException $e) {
            showError($e);
        }
    }



    public function connectSql()
    {
        $dbVar2 = self::dbVariables();
        try {
            return mysqli_connect($dbVar2['host'], $dbVar2['username'], $dbVar2['password'], $dbVar2['name']);
        } catch (\Throwable $e) {
            showError($e);
        }
    }
}

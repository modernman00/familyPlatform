<?php

namespace App\classes\tables;

use PDOException;
use App\classes\Insert;

class Account extends Insert
{

    public function index()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS  `family`.`account`(
                 `no` INT NOT NULL AUTO_INCREMENT , 
                 `id` VARCHAR(255) NOT NULL , 
                 `email` VARCHAR(50) NOT NULL ,
                `password` TEXT NOT NULL ,
                `token` TEXT NOT NULL ,
                `secretWord` TEXT NOT NULL ,
                `status` TEXT NOT NULL ,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,
                PRIMARY KEY (`no`),
                FOREIGN KEY (`id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";

            $conn = $this->connect();
            // use exec() because no results are returned
            return $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . `<br><br>` . $e->getMessage() . $e->getLine();
        }
    }
}

<?php

namespace App\classes\tables;

use \PDOException;
use App\classes\Insert;

class personal2 extends Insert {

   public function index() 
    {   
        try {
                $sql = "CREATE TABLE IF NOT EXISTS  `family`.`personal2` ( 
                    `no` INT NOT NULL AUTO_INCREMENT , 
                    `id` VARCHAR(255) NOT NULL , 
                `firstName` TEXT NOT NULL , 
                `lastName` TEXT NOT NULL ,
                `fatherName` TEXT NOT NULL,
                `motherName` TEXT NOT NULL,
                `birthDate` DATE NOT NULL,
                `noKids` INT NOT NULL,
                `gender` TEXT NOT NULL,
                `noSiblings` INT NOT NULL,                      
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP, 
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP , 
                PRIMARY KEY (`no`),
                INDEX `id` (`id`))";
    
                $conn = $this->connect();
                    // use exec() because no results are returned
                $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

    }

}

<?php

namespace App\classes\tables;

use \PDOException;
use App\classes\Insert;

class Register extends Insert {

   public function index() 
    {   
        try {
                $sql = "CREATE TABLE IF NOT EXISTS  `family`.`register` ( `no` INT NOT NULL AUTO_INCREMENT , `id` VARCHAR(255) NOT NULL , 
                `username` TEXT NOT NULL , 
                `password` VARCHAR(255) NOT NULL ,                     
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP, 
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP , 
                PRIMARY KEY (`no`),
                INDEX `id` (`id`))
                ";
    
                $conn = $this->connect();
                    // use exec() because no results are returned
                $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

    }

}

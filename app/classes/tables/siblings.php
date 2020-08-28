<?php

namespace App\classes\tables;

use \PDOException;
use App\classes\Insert;

class Siblings extends Insert {

   public function index() 
    {   
        try {
                $sql = "CREATE TABLE IF NOT EXISTS  `family`.`siblings` ( 
                    `no` INT NOT NULL AUTO_INCREMENT , 
                    `id` VARCHAR(255) NOT NULL , 
                `sibling1` TEXT NOT NULL , 
                `sibling2` TEXT NOT NULL ,
                `sibling3` TEXT NOT NULL,
                `sibling4` TEXT NOT NULL,
                `sibling5` TEXT NOT NULL,
                `sibling6` TEXT NOT NULL,
                `sibling7` TEXT NOT NULL,
                `sibling8` TEXT NOT NULL,
                `sibling9` TEXT NOT NULL,
                `sibling10` TEXT NOT NULL,                    
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP, 
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP , 
                PRIMARY KEY (`no`),
                 FOREIGN KEY (`id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";
    
                $conn = $this->connect();
                    // use exec() because no results are returned
                $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }

    }

}

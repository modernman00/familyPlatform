<?php

namespace App\classes\tables;

use PDOException;
use App\classes\Insert;

class Kids extends Insert
{

    public function index()
    {
        try {
                $sql = "CREATE TABLE IF NOT EXISTS  `family`.`kids` ( 
                    `no` INT NOT NULL AUTO_INCREMENT , 
                    `id` VARCHAR(255) NOT NULL , 
                `kid_name` TEXT NOT NULL , 
                `kid_email` TEXT NOT NULL ,
                `kid_linked` TEXT NOT NULL,
              
                                  
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP, 
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP , 
                PRIMARY KEY (`no`),
                 FOREIGN KEY (`id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";

                $conn = $this->connect();
                    // use exec() because no results are returned
                return $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . "<br>" . $e->getMessage();
        }
    }
}

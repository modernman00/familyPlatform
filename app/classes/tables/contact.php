<?php
namespace App\classes\tables;

use \PDOException;
use App\classes\Insert;

class Contact extends Insert {

   public function index() 
    {   
        try {
                $sql = "CREATE TABLE IF NOT EXISTS  `family`.`contact`(
                 `no` INT NOT NULL AUTO_INCREMENT , 
                 `id` VARCHAR(255) NOT NULL , 
                `address` TEXT NOT NULL ,
                `postcode` VARCHAR(50) NOT NULL ,
                `region` TEXT NOT NULL ,
                `email` VARCHAR(50) NOT NULL ,
                `country` VARCHAR(30) NOT NULL ,
                `mobile` INT NOT NULL ,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,
                PRIMARY KEY (`no`),
                FOREIGN KEY (`id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";
    
                $conn = $this->connect();
                    // use exec() because no results are returned
                $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql.`<br><br>`. $e->getMessage(). $e->getLine() ;
        }

    }

}





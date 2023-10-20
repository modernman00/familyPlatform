<?php

namespace App\classes\tables;

use PDOException;
use App\classes\Insert;

class Notification extends Insert
{

    public static function index()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS  `family`.`notification` ( 
                `no` INT NOT NULL AUTO_INCREMENT, 
                `sender_id` VARCHAR(255) COLLATE utf8mb3_general_ci NOT NULL,
                `sender_name` TEXT NULL,
                `notification_name` TEXT NULL,
                `receiver` TEXT NULL,
                `when` TEXT NULL,
                `notification_type` TEXT NULL,
                `notification_content` TEXT NULL,       
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP, 
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP , 
                PRIMARY KEY (`no`),
                FOREIGN KEY (`sender_id`) REFERENCES personal(`id`),
                INDEX `sender_id` (`sender_id`))";

            $conn = parent::connect2();
            // use exec() because no results are returned
            return $conn->exec($sql);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}

<?php

namespace App\classes\tables;

use PDOException;
use App\classes\Insert;

class FriendRequest extends Insert
{

    public function index()
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS  `family`.`friendRequest`(
                `no` INT NOT NULL AUTO_INCREMENT, 
                `id` VARCHAR(255) NOT NULL,
                `approver_id` VARCHAR(255) NOT NULL,
                status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP ,        
                PRIMARY KEY (`no`),
                -- FOREIGN KEY (`id`) REFERENCES personal(`id`),
                -- FOREIGN KEY (`approver_id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";

            $conn = $this->connect();
            // use exec() because no results are returned
            return $conn->exec($sql);
        } catch (PDOException $e) {
            echo $sql . `<br><br>` . $e->getMessage() . $e->getLine();
        }
    }
}

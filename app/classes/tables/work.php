<?php
declare(strict_types=1);

namespace App\Classes\Tables;

use PDOException;
use App\Classes\Insert;

final class Work extends Insert
{

    /**
     * Create the work table if it doesn't exist
     *
     * @return int|null Returns the number of affected rows or null on failure
     */
    public function index(): ?int
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS  `family`.`work` (  
                `no` INT NOT NULL AUTO_INCREMENT , 
                `id` VARCHAR(255) NOT NULL ,
                `employmentStatus` TEXT NOT NULL , 
                `jobTitle` TEXT NOT NULL , 
                `occupation` TEXT NOT NULL ,
                `employerName` TEXT NOT NULL,
                `workEmail` TEXT NOT NULL, 
                `employers`  INT NOT NULL,
                 `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                `deleted_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`no`),
                FOREIGN KEY (`id`) REFERENCES personal(`id`),
                INDEX `id` (`id`))";

            $conn = $this->connect();
            // use exec() because no results are returned
            return $conn->exec($sql);
        } catch (PDOException $e) {
            // Log the error instead of echoing it
            error_log("Error creating work table: " . $e->getMessage());
            return null;
        }
    }
}

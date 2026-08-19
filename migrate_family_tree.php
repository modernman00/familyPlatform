<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

use Src\Db;

class FamilyTreeMigration extends Db {
    public function up() {
        $db = self::connect2();
        
        // 1. Family Nodes (Individuals in the 6-generation graph)
        $sqlNodes = "
        CREATE TABLE IF NOT EXISTS `family_nodes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `family_code` VARCHAR(64) NOT NULL,
            `user_id` VARCHAR(255) NULL COMMENT 'Linked member account ID if registered',
            `first_name` VARCHAR(100) NOT NULL,
            `last_name` VARCHAR(100) NOT NULL,
            `maiden_name` VARCHAR(100) NULL,
            `gender` ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Other',
            `birth_date` DATE NULL,
            `birth_year` INT NULL,
            `birth_place` VARCHAR(255) NULL,
            `death_date` DATE NULL,
            `death_year` INT NULL,
            `death_place` VARCHAR(255) NULL,
            `is_deceased` TINYINT(1) NOT NULL DEFAULT 0,
            `bio` TEXT NULL,
            `occupation` VARCHAR(150) NULL,
            `location` VARCHAR(255) NULL,
            `email` VARCHAR(255) NULL,
            `mobile` VARCHAR(50) NULL,
            `avatar_url` VARCHAR(255) NULL,
            `generation_level` SMALLINT NOT NULL DEFAULT 0,
            `voice_capsule_url` VARCHAR(255) NULL COMMENT 'Living voice memory audio file',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_fam_code` (`family_code`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $db->exec($sqlNodes);
        echo "Table `family_nodes` created or verified successfully.\n";

        // 2. Family Unions (Couples, Marriages, Divorces, Domestic Partnerships)
        $sqlUnions = "
        CREATE TABLE IF NOT EXISTS `family_unions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `family_code` VARCHAR(64) NOT NULL,
            `partner_1_id` INT UNSIGNED NOT NULL,
            `partner_2_id` INT UNSIGNED NOT NULL,
            `union_type` ENUM('married', 'divorced', 'separated', 'partner', 'widowed', 'annulled') NOT NULL DEFAULT 'married',
            `marriage_date` DATE NULL,
            `marriage_year` INT NULL,
            `divorce_date` DATE NULL,
            `divorce_year` INT NULL,
            `is_current` TINYINT(1) NOT NULL DEFAULT 1,
            `notes` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_union_partners` (`partner_1_id`, `partner_2_id`),
            INDEX `idx_union_fam` (`family_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $db->exec($sqlUnions);
        echo "Table `family_unions` created or verified successfully.\n";

        // 3. Family Node Children (Parent-Child DAG Linkage)
        $sqlChildren = "
        CREATE TABLE IF NOT EXISTS `family_node_children` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `union_id` INT UNSIGNED NULL COMMENT 'Union ID if born within marriage/partnership',
            `parent_node_id` INT UNSIGNED NULL COMMENT 'Direct parent node if single parent',
            `child_id` INT UNSIGNED NOT NULL,
            `relationship_type` ENUM('biological', 'adopted', 'foster', 'step') NOT NULL DEFAULT 'biological',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_union_child` (`union_id`, `child_id`),
            INDEX `idx_child_parent` (`child_id`, `parent_node_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $db->exec($sqlChildren);
        echo "Table `family_node_children` created or verified successfully.\n";
    }
}

$m = new FamilyTreeMigration();
$m->up();

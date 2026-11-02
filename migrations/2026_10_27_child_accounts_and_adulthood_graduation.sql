-- ============================================================
-- Migration: 2026_10_27_child_accounts_and_adulthood_graduation
-- Purpose:   Supervised Child Accounts, Gated Family Posting &
--            The "Turning 18" Adulthood Graduation Engine.
-- Author:    TAT 2.0 & Engineering Council
-- ============================================================

-- 1. Extend account table for child lifecycle & transition tracking
SET @exist_adulthood := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND COLUMN_NAME = 'adulthood_status');
SET @sql_adulthood := IF(@exist_adulthood = 0, 'ALTER TABLE account ADD COLUMN `adulthood_status` ENUM(\'standard_adult\', \'minor_supervised\', \'minor_teen\', \'graduated_adult\') NOT NULL DEFAULT \'standard_adult\'', 'SELECT 1');
PREPARE stmt_adulthood FROM @sql_adulthood;
EXECUTE stmt_adulthood;
DEALLOCATE PREPARE stmt_adulthood;

SET @exist_child := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND COLUMN_NAME = 'is_child_account');
SET @sql_child := IF(@exist_child = 0, 'ALTER TABLE account ADD COLUMN `is_child_account` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt_child FROM @sql_child;
EXECUTE stmt_child;
DEALLOCATE PREPARE stmt_child;

SET @exist_parent := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND COLUMN_NAME = 'parent_user_id');
SET @sql_parent := IF(@exist_parent = 0, 'ALTER TABLE account ADD COLUMN `parent_user_id` VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt_parent FROM @sql_parent;
EXECUTE stmt_parent;
DEALLOCATE PREPARE stmt_parent;

SET @exist_transition := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND COLUMN_NAME = 'transition_due_date');
SET @sql_transition := IF(@exist_transition = 0, 'ALTER TABLE account ADD COLUMN `transition_due_date` DATE NULL', 'SELECT 1');
PREPARE stmt_transition FROM @sql_transition;
EXECUTE stmt_transition;
DEALLOCATE PREPARE stmt_transition;

SET @exist_graduated_at := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND COLUMN_NAME = 'graduated_at');
SET @sql_graduated_at := IF(@exist_graduated_at = 0, 'ALTER TABLE account ADD COLUMN `graduated_at` TIMESTAMP NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt_graduated_at FROM @sql_graduated_at;
EXECUTE stmt_graduated_at;
DEALLOCATE PREPARE stmt_graduated_at;

-- Index for daily transition watchdog
SET @exist_idx_trans := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account' AND INDEX_NAME = 'idx_account_transition_due');
SET @sql_idx_trans := IF(@exist_idx_trans = 0, 'ALTER TABLE account ADD INDEX `idx_account_transition_due` (`transition_due_date`, `adulthood_status`)', 'SELECT 1');
PREPARE stmt_idx_trans FROM @sql_idx_trans;
EXECUTE stmt_idx_trans;
DEALLOCATE PREPARE stmt_idx_trans;

-- 2. Audit-ready Guardian Consents (UK GDPR s.9 / DPA 2018 / ICO Children\'s Code)
CREATE TABLE IF NOT EXISTS `guardian_consents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_user_id` VARCHAR(255) NOT NULL,
  `child_user_id` VARCHAR(255) NOT NULL,
  `family_code` VARCHAR(64) NOT NULL,
  `consent_type` VARCHAR(64) NOT NULL DEFAULT 'parental_account_sponsorship',
  `parent_ip` VARCHAR(64) NULL DEFAULT NULL,
  `consented_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parent_child` (`parent_user_id`, `child_user_id`),
  KEY `idx_child_user` (`child_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Child PIN & Username Credentials (For supervised minors under 13)
CREATE TABLE IF NOT EXISTS `child_pin_credentials` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(255) NOT NULL,
  `child_username` VARCHAR(100) NOT NULL,
  `family_code` VARCHAR(64) NOT NULL,
  `pin_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_child_user_unique` (`user_id`),
  UNIQUE KEY `idx_fam_child_username` (`family_code`, `child_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

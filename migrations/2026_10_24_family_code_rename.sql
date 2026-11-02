-- RFC-0005 Family code rename by poll consent. Additive; both tables are also created by
-- FamilyCodeRenameService::ensureSchema() so a missed migration cannot break the feature.
CREATE TABLE IF NOT EXISTS `family_code_rename_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `old_code` VARCHAR(50) NOT NULL,
  `new_code` VARCHAR(50) NOT NULL,
  `requested_by` VARCHAR(255) NOT NULL,
  `post_no` INT NULL,
  `poll_id` INT NULL,
  `yes_option_id` INT NULL,
  `no_option_id` INT NULL,
  `status` ENUM('pending','applied','rejected','expired','cancelled','failed') NOT NULL DEFAULT 'pending',
  `outcome_note` VARCHAR(255) NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL,
  KEY `idx_rename_old_status` (`old_code`, `status`),
  KEY `idx_rename_new` (`new_code`),
  KEY `idx_rename_poll` (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Old code -> current code, kept forever so shared links, invites and stale sessions keep resolving.
CREATE TABLE IF NOT EXISTS `family_code_aliases` (
  `old_code` VARCHAR(50) NOT NULL PRIMARY KEY,
  `new_code` VARCHAR(50) NOT NULL,
  `request_id` INT UNSIGNED NULL,
  `renamed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_alias_new` (`new_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

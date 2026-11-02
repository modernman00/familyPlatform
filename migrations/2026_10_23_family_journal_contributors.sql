-- ==============================================================================
-- Co-contributors for Family Chronicles. Additive, idempotent.
-- A contributor may edit a chronicle; only the author/admin can manage the list or delete.
-- ==============================================================================
CREATE TABLE IF NOT EXISTS `family_journal_contributors` (
  `journal_id` INT UNSIGNED NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `added_by` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`journal_id`, `user_id`),
  INDEX `idx_fjc_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

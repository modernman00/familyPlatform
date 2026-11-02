-- ==============================================================================
-- Kinship Auto-Matching Resolution & Multi-Touch Onboarding Lifecycle Migration
-- (c) 2026 The Modernman Platform Group
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `user_lifecycle_events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(191) NOT NULL,
  `event_type` VARCHAR(64) NOT NULL COMMENT 'e.g. welcome_day1, voice_capsule_day3, referral_flywheel_day7',
  `channel` VARCHAR(32) NOT NULL COMMENT 'email, push, in_app',
  `metadata` TEXT NULL COMMENT 'JSON payload or delivery context',
  `dispatched_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_lifecycle_lookup` (`user_id`, `event_type`),
  INDEX `idx_lifecycle_dispatched` (`dispatched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kinship_match_dismissals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(191) NOT NULL,
  `node_id` INT UNSIGNED NOT NULL,
  `family_code` VARCHAR(64) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_node_dismiss` (`user_id`, `node_id`),
  INDEX `idx_dismissals_fam` (`family_code`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

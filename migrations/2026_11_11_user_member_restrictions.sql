-- Migration: User Member Content & Visibility Restrictions
-- Purpose: Allow users to selectively restrict specific family members or connected kin
--          from seeing their posts, comments, reels, events, journals, announcements,
--          time capsules, search results, notifications, and optionally profile media.
-- Date: 2026-11-11

CREATE TABLE IF NOT EXISTS `user_member_restrictions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `owner_id` VARCHAR(255) NOT NULL,
  `restricted_user_id` VARCHAR(255) NOT NULL,
  `restrict_posts` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_comments` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_reels` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_events` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_journals` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_announcements` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_capsules` TINYINT(1) NOT NULL DEFAULT 1,
  `restrict_profile` TINYINT(1) NOT NULL DEFAULT 0,
  `note` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_owner_restricted` (`owner_id`, `restricted_user_id`),
  INDEX `idx_owner` (`owner_id`),
  INDEX `idx_restricted_owner` (`restricted_user_id`, `owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

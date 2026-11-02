-- Migration: 2026_10_27_post_and_comment_views_tracking.sql
-- Granular Post and Comment Views Tracking & Author Inspection

ALTER TABLE `comment`
  ADD COLUMN `views_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `comment_status`;

CREATE TABLE IF NOT EXISTS `post_views` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_no` INT NOT NULL,
  `user_id` VARCHAR(100) NOT NULL,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_post_user_view` (`post_no`, `user_id`),
  KEY `idx_post_no` (`post_no`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `comment_views` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_no` INT NOT NULL,
  `user_id` VARCHAR(100) NOT NULL,
  `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_comment_user_view` (`comment_no`, `user_id`),
  KEY `idx_comment_no` (`comment_no`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

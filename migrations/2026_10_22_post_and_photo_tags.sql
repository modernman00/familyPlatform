-- Migration: 2026_10_22_post_and_photo_tags.sql
-- Description: Create post_tags and photo_tags tables for post-level and spatial photo tagging

CREATE TABLE IF NOT EXISTS `post_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `tagged_by` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_post_user` (`post_no`, `user_id`),
  INDEX `idx_post_no` (`post_no`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_tagged_by` (`tagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `photo_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `tagged_by` VARCHAR(255) NOT NULL,
  `x_pos` DECIMAL(5,2) NOT NULL COMMENT 'X-coordinate percentage from left (0.00 to 100.00)',
  `y_pos` DECIMAL(5,2) NOT NULL COMMENT 'Y-coordinate percentage from top (0.00 to 100.00)',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_photo_post_img` (`post_no`, `image_path`(191)),
  INDEX `idx_photo_user` (`user_id`),
  INDEX `idx_photo_tagged_by` (`tagged_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

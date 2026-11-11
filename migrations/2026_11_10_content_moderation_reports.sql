-- Migration: Content Moderation Reports & Community Flagging
-- Author: Super Admin & SecOps (Marcus & Olutobi)
-- Date: 2026-11-10

CREATE TABLE IF NOT EXISTS `content_reports` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `report_uuid` VARCHAR(64) NOT NULL UNIQUE,
  `fam_code` VARCHAR(64) NOT NULL,
  `reporter_id` VARCHAR(64) NOT NULL,
  `target_type` ENUM('post', 'comment') NOT NULL DEFAULT 'post',
  `target_id` BIGINT UNSIGNED NOT NULL,
  `target_author_id` VARCHAR(64) NOT NULL,
  `target_content` TEXT NOT NULL,
  `reason` VARCHAR(64) NOT NULL,
  `details` TEXT NULL,
  `status` ENUM('pending', 'actioned', 'dismissed') NOT NULL DEFAULT 'pending',
  `action_taken` VARCHAR(64) NULL,
  `admin_note` TEXT NULL,
  `reviewed_by` VARCHAR(64) NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_fam_status` (`fam_code`, `status`),
  INDEX `idx_target` (`target_type`, `target_id`),
  INDEX `idx_reporter` (`reporter_id`),
  INDEX `idx_author` (`target_author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

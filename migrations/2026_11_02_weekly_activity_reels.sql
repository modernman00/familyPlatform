-- Migration: 2026_11_02_weekly_activity_reels.sql
-- Weekly Activity Video Curation & Approval Pipeline
-- Cluster: Social Media, Lifestyle & Platform Growth Sovereign Board / ATG Pods

CREATE TABLE IF NOT EXISTS `weekly_activity_reels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(191) NOT NULL,
  `famCode` VARCHAR(191) NOT NULL,
  `week_start_date` DATE NOT NULL,
  `week_end_date` DATE NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `caption` TEXT NULL,
  `duration_seconds` INT NOT NULL DEFAULT 45,
  `video_url` VARCHAR(500) NOT NULL,
  `thumbnail_url` VARCHAR(500) NULL,
  `audio_url` VARCHAR(500) NULL,
  `music_title` VARCHAR(255) NULL,
  `storyboard_json` LONGTEXT NOT NULL,
  `status` ENUM('draft_pending_approval', 'approved', 'discarded') NOT NULL DEFAULT 'draft_pending_approval',
  `post_no` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_user_status` (`user_id`, `status`),
  INDEX `idx_fam_week` (`famCode`, `week_start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

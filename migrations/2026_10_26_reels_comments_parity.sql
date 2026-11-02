-- Migration: 2026_10_26_reels_comments_parity.sql
-- Reels Comments Parity with Post Comments: 1-level threaded replies, curated GIFs, updated timestamp, and comment reactions.

ALTER TABLE `family_reel_comments`
  ADD COLUMN `parent_comment_no` INT NULL DEFAULT NULL AFTER `user_id`,
  ADD COLUMN `gif_url` VARCHAR(2048) NULL DEFAULT NULL AFTER `comment`,
  ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD KEY `idx_reel_comment_parent` (`parent_comment_no`);

CREATE TABLE IF NOT EXISTS `family_reel_comment_reactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `comment_id` INT NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `reaction_type` VARCHAR(20) NOT NULL DEFAULT 'like',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_reel_comment_reaction` (`comment_id`, `user_id`),
  KEY `idx_reel_comment_id` (`comment_id`),
  KEY `idx_reel_comment_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

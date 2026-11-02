-- Migration: 2026_10_23_post_comparisons.sql
-- RFC-0002 "Then & Now". Additive, create-only: no ALTER on `post`.
CREATE TABLE IF NOT EXISTS `post_comparisons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `before_img` VARCHAR(255) NOT NULL,
  `after_img` VARCHAR(255) NOT NULL,
  `before_label` VARCHAR(40) NULL,
  `after_label` VARCHAR(40) NULL,
  `parent_post_no` INT NULL COMMENT 'Set when this Then & Now answers another one',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_post_no` (`post_no`),
  INDEX `idx_parent_post_no` (`parent_post_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

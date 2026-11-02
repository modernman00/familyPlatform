-- ==============================================================================
-- 🔄 Migration: Support Reposting Earlier Posts
-- Date: 2026-10-30
-- Author: Olutobi (TAT 2.0 Lead), Victor (CTO), Callum Wright (Lead Staff Engineer)
-- ==============================================================================

SET @col_exists := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'post' 
      AND COLUMN_NAME = 'repost_of_post_no'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `post` ADD COLUMN `repost_of_post_no` INT UNSIGNED DEFAULT NULL COMMENT \'Original post number if this post is a repost of an earlier memory\' AFTER `voice_capsule_url`, ADD INDEX `idx_repost_of_post_no` (`repost_of_post_no`)',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

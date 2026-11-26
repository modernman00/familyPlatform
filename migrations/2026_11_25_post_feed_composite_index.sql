-- ==============================================================================
-- ⚡ Migration: Composite Index for Active Feed Scan & Keyset Pagination
-- Date: 2026-11-25
-- Author: Olutobi (TAT 2.0 Lead), Quality Team (CTO), BRATS Principal Engineers
-- Rollback: ALTER TABLE `post` DROP INDEX `idx_post_feed_active`;
-- ==============================================================================

SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'post'
      AND INDEX_NAME = 'idx_post_feed_active'
);

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE `post` ADD INDEX `idx_post_feed_active` (`post_status`, `date_deleted`, `date_created`, `post_no`)',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

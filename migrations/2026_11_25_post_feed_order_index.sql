-- Tier 3 Migration: Composite covering/ordering index for FeedRepository::fetchFeedRows()
-- Eliminates full table scan + filesort on `post` when fetching published feed pages.
-- Date: 2026-11-25

SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'post'
      AND INDEX_NAME = 'idx_post_feed_order'
);

SET @ddl := IF(
    @idx_exists = 0,
    'CREATE INDEX idx_post_feed_order ON post (post_status, date_deleted, date_created, post_no)',
    'SELECT 1'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

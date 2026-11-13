-- ==============================================================================
-- Add is_pinned and is_muted columns to kin_thread_participants table.
-- Additive, idempotent across MySQL 8/9 and MariaDB 10/11.
-- ==============================================================================
SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `kin_thread_participants` ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `deleted_at`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kin_thread_participants' AND COLUMN_NAME = 'is_pinned');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `kin_thread_participants` ADD COLUMN `is_muted` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_pinned`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kin_thread_participants' AND COLUMN_NAME = 'is_muted');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

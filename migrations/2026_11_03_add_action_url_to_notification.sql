-- ==============================================================================
-- Add action_url column to notification table for in-app navigation deep links.
-- Additive, idempotent across MySQL 8/9 and MariaDB 10/11.
-- ==============================================================================
SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `notification` ADD COLUMN `action_url` VARCHAR(500) NULL DEFAULT NULL AFTER `notification_content`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notification' AND COLUMN_NAME = 'action_url');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ==============================================================================
-- Add post_email_notifications column to contact table (default 'off').
-- Immediate per-post emails are now opt-in; in-app, Pusher, WebPush, and
-- Sunday digest remain active by default.
-- Additive, idempotent across MySQL 8/9 and MariaDB 10/11.
-- Rollback: ALTER TABLE `contact` DROP COLUMN `post_email_notifications`;
-- ==============================================================================
SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `contact` ADD COLUMN `post_email_notifications` varchar(10) NOT NULL DEFAULT ''off'' AFTER `email_notifications`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact' AND COLUMN_NAME = 'post_email_notifications');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

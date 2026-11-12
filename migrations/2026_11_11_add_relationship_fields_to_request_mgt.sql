-- ==============================================================================
-- Add relationship attribution and note columns to requestMgt for Connected Kin.
-- Additive, idempotent across MySQL 8/9 and MariaDB 10/11.
-- ==============================================================================

SET @s1 = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `requestMgt` ADD COLUMN `relationship_category` VARCHAR(50) NULL DEFAULT NULL AFTER `approverCode`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'requestMgt' AND COLUMN_NAME = 'relationship_category');
PREPARE stmt1 FROM @s1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @s2 = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `requestMgt` ADD COLUMN `relationship_role` VARCHAR(100) NULL DEFAULT NULL AFTER `relationship_category`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'requestMgt' AND COLUMN_NAME = 'relationship_role');
PREPARE stmt2 FROM @s2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @s3 = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `requestMgt` ADD COLUMN `relationship_note` VARCHAR(255) NULL DEFAULT NULL AFTER `relationship_role`',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'requestMgt' AND COLUMN_NAME = 'relationship_note');
PREPARE stmt3 FROM @s3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

SET @s4 = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `requestMgt` ADD INDEX `idx_rm_approver_status` (`approver_id`, `status`)',
  'DO 0')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'requestMgt' AND INDEX_NAME = 'idx_rm_approver_status');
PREPARE stmt4 FROM @s4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

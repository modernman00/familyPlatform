-- ==============================================================================
-- 🎙️ Migration: Attach Voice Capsule Audio Keepsakes to Posts
-- Date: 2026-10-29
-- Author: Olutobi (TAT 2.0 Lead) & Victor (CTO)
-- ==============================================================================

SET @col_exists := (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'post' 
      AND COLUMN_NAME = 'voice_capsule_url'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `post` ADD COLUMN `voice_capsule_url` VARCHAR(255) DEFAULT NULL COMMENT \'Attached voice capsule audio keepsake URL\' AFTER `profileImg`',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

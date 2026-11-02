-- Remove legacy ON UPDATE CURRENT_TIMESTAMP trap from images.deleted_at so row updates don't stamp deletion
ALTER TABLE `images` MODIFY COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL;

-- Clear any accidental auto-stamps where deleted_at equals updated_at
UPDATE `images` SET `deleted_at` = NULL WHERE `deleted_at` IS NOT NULL AND `deleted_at` = `updated_at`;

-- ==============================================================================
-- Birth surname (maiden name) of the member themselves.
-- Additive, idempotent. Distinct from otherFamily.maiden_name, which holds the
-- member's MOTHER's maiden name.
-- ==============================================================================
SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `personal` ADD COLUMN `birth_surname` VARCHAR(100) NULL DEFAULT NULL, ALGORITHM=INSTANT',
  'DO 0')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personal' AND COLUMN_NAME = 'birth_surname');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

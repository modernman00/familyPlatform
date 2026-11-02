-- ==============================================================================
-- personal.id must be unique (it was only a plain index, so a double-submitted
-- registration created two rows for one member). Idempotent.
-- 1) Copy any duplicate rows into a backup table, 2) delete all but the oldest
-- row per id, 3) replace the plain index with a UNIQUE one.
-- ==============================================================================
CREATE TABLE IF NOT EXISTS `personal_duplicates_backup` AS SELECT * FROM `personal` WHERE 1 = 0;

INSERT INTO `personal_duplicates_backup`
SELECT p.* FROM `personal` p
JOIN (SELECT id, MIN(`no`) AS keep_no FROM `personal` GROUP BY id HAVING COUNT(*) > 1) d
  ON d.id = p.id AND p.`no` <> d.keep_no
WHERE NOT EXISTS (SELECT 1 FROM `personal_duplicates_backup` b WHERE b.`no` = p.`no`);

DELETE p FROM `personal` p
JOIN (SELECT id, MIN(`no`) AS keep_no FROM `personal` GROUP BY id HAVING COUNT(*) > 1) d
  ON d.id = p.id AND p.`no` <> d.keep_no;

SET @s = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE `personal` DROP INDEX `id`, ADD UNIQUE INDEX `uniq_personal_id` (`id`)',
  'DO 0')
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personal' AND INDEX_NAME = 'uniq_personal_id');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

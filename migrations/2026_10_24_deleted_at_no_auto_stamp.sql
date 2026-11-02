-- RFC-0006: deleted_at must only change when something deliberately deletes a row.
-- account / personal / contact had `deleted_at TIMESTAMP NULL ... ON UPDATE CURRENT_TIMESTAMP`, so ANY later
-- UPDATE of a live row stamped it. Phase 1 covers the three tables that drive member visibility.
-- Order matters: back up -> remove the auto-stamp -> clear stamps (explicit NULL, not affected by ON UPDATE anyway).

-- 0. SAFETY GUARD (runs first, changes nothing). Aborts the migration -- so it is NOT recorded as applied and the
--    deploy stops -- by selecting from a table whose NAME is the reason (see the error message). The pass path is `DO 0`
--    (no result set: PDO::exec() in run_migrations.php would otherwise leave a cursor open and break the next statement).
--    It aborts when:
--    (a) an erased / pending_deletion account still has a profile row carrying a deleted_at: possible deliberate
--        soft-delete we must not un-hide (RFC-0006 section 4); or
--    (b) a table has deleted_at values but NO `ON UPDATE CURRENT_TIMESTAMP` on the column: those values are not
--        explained by the known trap, so they may be real deletions. Review them before clearing anything.
--    Recovery: investigate with the audit queries in docs/rfcs/0006-deleted-at-auto-stamp.md, then re-run the deploy.
--    NOTE: scripts/deploy.sh leaves the site in maintenance mode when a migration fails.
SET @rfc0006_trap_account  = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'account'  AND COLUMN_NAME = 'deleted_at' AND EXTRA LIKE '%on update%');
SET @rfc0006_trap_personal = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'personal' AND COLUMN_NAME = 'deleted_at' AND EXTRA LIKE '%on update%');
SET @rfc0006_trap_contact  = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contact'  AND COLUMN_NAME = 'deleted_at' AND EXTRA LIKE '%on update%');
SET @rfc0006_stamped_account  = (SELECT COUNT(*) FROM `account`  WHERE `deleted_at` IS NOT NULL AND `status` NOT IN ('erased', 'pending_deletion'));
SET @rfc0006_stamped_personal = (SELECT COUNT(*) FROM `personal` WHERE `deleted_at` IS NOT NULL);
SET @rfc0006_stamped_contact  = (SELECT COUNT(*) FROM `contact`  WHERE `deleted_at` IS NOT NULL);
SET @rfc0006_conflicts = (SELECT COUNT(*) FROM `personal` p JOIN `account` a ON a.`id` = p.`id` WHERE p.`deleted_at` IS NOT NULL AND a.`status` IN ('erased', 'pending_deletion'));
SET @rfc0006_guard = CASE
  WHEN @rfc0006_conflicts > 0 THEN 'SELECT 1 FROM RFC0006_ABORT_erased_or_pending_account_has_stamped_profile'
  WHEN (@rfc0006_stamped_account > 0 AND @rfc0006_trap_account = 0)
    OR (@rfc0006_stamped_personal > 0 AND @rfc0006_trap_personal = 0)
    OR (@rfc0006_stamped_contact > 0 AND @rfc0006_trap_contact = 0) THEN 'SELECT 1 FROM RFC0006_ABORT_stamps_exist_without_on_update_trap'
  ELSE 'DO 0'
END;
PREPARE rfc0006_guard_stmt FROM @rfc0006_guard;
EXECUTE rfc0006_guard_stmt;
DEALLOCATE PREPARE rfc0006_guard_stmt;

-- 1. Targeted backups of every row that will be touched (restore source for rollback).
CREATE TABLE IF NOT EXISTS `_bak_20261024_account_deleted_at` AS
  SELECT `id`, `status`, `deleted_at` FROM `account` WHERE `deleted_at` IS NOT NULL;
CREATE TABLE IF NOT EXISTS `_bak_20261024_personal_deleted_at` AS
  SELECT `id`, `deleted_at` FROM `personal` WHERE `deleted_at` IS NOT NULL;
CREATE TABLE IF NOT EXISTS `_bak_20261024_contact_deleted_at` AS
  SELECT `id`, `deleted_at` FROM `contact` WHERE `deleted_at` IS NOT NULL;

-- 2. Remove the auto-stamp. INPLACE + LOCK=NONE: MySQL refuses the statement rather than locking if it cannot comply.
ALTER TABLE `account`  MODIFY COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE `personal` MODIFY COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE `contact`  MODIFY COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL, ALGORITHM=INPLACE, LOCK=NONE;

-- 3. Clear stamps. Real deletions are marked by account.status ('erased' / 'pending_deletion', set with deleted_at
--    by DataErasureService and DataPrivacyController) and are kept. Nothing in the codebase ever deliberately sets
--    personal.deleted_at or contact.deleted_at (erasure hard-deletes those rows), so every value there is a stamp.
UPDATE `account`  SET `deleted_at` = NULL WHERE `deleted_at` IS NOT NULL AND `status` NOT IN ('erased', 'pending_deletion');
UPDATE `personal` SET `deleted_at` = NULL WHERE `deleted_at` IS NOT NULL;
UPDATE `contact`  SET `deleted_at` = NULL WHERE `deleted_at` IS NOT NULL;

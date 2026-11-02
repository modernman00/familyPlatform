-- FIX: posts / profile pics / comments disappearing.
--
-- Root cause: the soft-delete columns were created with a timestamp default or
-- an ON UPDATE clause, so a row gets a non-NULL `date_deleted` the moment it is
-- inserted OR updated. The feed queries filter `WHERE date_deleted IS NULL`
-- (app/model/AllMembersData.php:126,259,451), so:
--   * new post          -> published, then hidden on the very next feed fetch
--   * edit / like a post -> UPDATE stamps date_deleted -> post vanishes
--   * re-upload avatar   -> profilePics row updated -> avatar treated as deleted
--
-- This was patched on the dev DB by the ad-hoc fix_schema.php but there is no
-- migration, so a live server that never ran that script still has the bug.
-- `profilePics` was only half-fixed even on dev (ON UPDATE clause still there).
--
-- Run on live, then re-check with the diagnostic block at the bottom.

-- 1. Correct the column definitions (no default, no ON UPDATE).
ALTER TABLE `post`        MODIFY COLUMN `date_deleted` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `profilePics` MODIFY COLUMN `date_deleted` TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `comment`     MODIFY COLUMN `date_deleted` TIMESTAMP NULL DEFAULT NULL;

-- 2. Un-delete rows that were only "deleted" by the broken column, not by a user.
--    A genuine delete sets status via the app; these still say published/new.
UPDATE `post`        SET `date_deleted` = NULL WHERE `date_deleted` IS NOT NULL AND `post_status`    IN ('published','new');
UPDATE `comment`     SET `date_deleted` = NULL WHERE `date_deleted` IS NOT NULL AND `comment_status` IN ('published','new');
UPDATE `profilePics` SET `date_deleted` = NULL WHERE `date_deleted` IS NOT NULL;

-- 3. Any posts stuck at 'new' (publish step never ran) — publish them.
UPDATE `post`    SET `post_status`    = 'published' WHERE `post_status`    = 'new' AND `date_deleted` IS NULL;
UPDATE `comment` SET `comment_status` = 'published' WHERE `comment_status` = 'new' AND `date_deleted` IS NULL;

-- ── DIAGNOSTIC (run these SELECTs on live BEFORE step 1 to confirm the cause) ──
-- SHOW CREATE TABLE `post`\G          -- look for  `date_deleted` ... DEFAULT CURRENT_TIMESTAMP  or  ON UPDATE CURRENT_TIMESTAMP
-- SHOW CREATE TABLE `profilePics`\G
-- SELECT
--   (SELECT COUNT(*) FROM post) AS total,
--   (SELECT COUNT(*) FROM post WHERE post_status='published') AS published,
--   (SELECT COUNT(*) FROM post WHERE post_status='published' AND date_deleted IS NULL) AS visible_in_feed,
--   (SELECT COUNT(*) FROM post WHERE post_status='published' AND date_deleted IS NOT NULL) AS hidden_by_bug,
--   (SELECT COUNT(*) FROM post WHERE post_status='new') AS stuck_unpublished;

-- Migration: 2026_10_25_comment_replies.sql
-- One-level threaded replies on comments. Additive, nullable, no default: no table rebuild, safe on a live table.
-- NULL = top-level comment; otherwise the comment_no of the top-level comment being replied to.
ALTER TABLE `comment`
  ADD COLUMN `parent_comment_no` INT NULL DEFAULT NULL AFTER `post_no`,
  ADD KEY `idx_comment_parent` (`parent_comment_no`),
  ALGORITHM=INPLACE, LOCK=NONE;

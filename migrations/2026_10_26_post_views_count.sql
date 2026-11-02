-- Migration: 2026_10_26_post_views_count.sql
-- Family Post Views Count Tracking. Additive, unsigned int, default 0: safe on a live table.
ALTER TABLE `post`
  ADD COLUMN `views_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `post_likes`;

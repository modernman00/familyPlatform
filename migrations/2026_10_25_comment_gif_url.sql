-- Migration: 2026_10_25_comment_gif_url.sql
-- Family GIF in comments. Additive, nullable, no default: no table rebuild, safe on a live table.
ALTER TABLE `comment`
  ADD COLUMN `gif_url` VARCHAR(2048) NULL DEFAULT NULL AFTER `comment`;

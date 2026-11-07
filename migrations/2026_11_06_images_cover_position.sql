-- Migration: 2026_11_06_images_cover_position.sql
-- RFC: docs/rfcs/0010-adjustable-profile-cover.md
-- Description: Focal point for adjustable profile cover photos (images rows with where_from = 'cover').
--   cover_pos_x / cover_pos_y are CSS background-position percentages (0-100). NULL = centred (50/50).

ALTER TABLE images ADD COLUMN IF NOT EXISTS cover_pos_x TINYINT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE images ADD COLUMN IF NOT EXISTS cover_pos_y TINYINT UNSIGNED NULL DEFAULT NULL;

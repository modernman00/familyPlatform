-- Migration: 2026_10_26_reels_music_studio_and_dual_volume.sql
-- Purpose: Add multi-track audio soundtrack, dual volume sliders, audio snippet offset, and sound index

ALTER TABLE `family_reels`
    ADD COLUMN `audio_url` VARCHAR(500) NULL DEFAULT NULL AFTER `music_title`,
    ADD COLUMN `video_volume` TINYINT UNSIGNED NOT NULL DEFAULT 100 AFTER `audio_url`,
    ADD COLUMN `music_volume` TINYINT UNSIGNED NOT NULL DEFAULT 40 AFTER `video_volume`,
    ADD COLUMN `audio_start_sec` DECIMAL(6,1) UNSIGNED NOT NULL DEFAULT 0.0 AFTER `music_volume`,
    ADD INDEX `idx_fam_music_title` (`famCode`, `music_title`);

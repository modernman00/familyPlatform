-- Migration: 2026_11_10_media_safety_hashes.sql
-- Table: media_safety_hashes
-- Description: Stores 64-bit perceptual hashes (dHash) of known adult/NSFW/abusive images and video keyframes
--              to prevent re-upload across all FamilyPlatform media ingestion surfaces.

CREATE TABLE IF NOT EXISTS `media_safety_hashes` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `hash_hex` VARCHAR(64) NOT NULL UNIQUE,
    `media_type` ENUM('image', 'video_frame') NOT NULL DEFAULT 'image',
    `status` ENUM('blocked', 'quarantined', 'allowlisted') NOT NULL DEFAULT 'blocked',
    `category` VARCHAR(64) NOT NULL DEFAULT 'pornography',
    `hamming_threshold` TINYINT UNSIGNED NOT NULL DEFAULT 6,
    `source_filename` VARCHAR(255) NULL,
    `reported_by` VARCHAR(64) NULL,
    `confirmed_by` VARCHAR(64) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_media_type` (`media_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

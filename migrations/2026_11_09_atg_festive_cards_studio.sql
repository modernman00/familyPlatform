-- RFC-0019: Sovereign Omni-Occasion Festive Card & Living Envelope Engine
-- Table: festive_cards

CREATE TABLE IF NOT EXISTS `festive_cards` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(64) NOT NULL UNIQUE,
    `user_id` VARCHAR(64) NOT NULL,
    `fam_code` VARCHAR(32) NOT NULL,
    `occasion` VARCHAR(32) NOT NULL DEFAULT 'christmas',
    `theme` VARCHAR(32) NOT NULL DEFAULT 'royal_crimson',
    `sender_name` VARCHAR(128) NOT NULL,
    `recipient_name` VARCHAR(128) NULL,
    `headline` VARCHAR(255) NOT NULL,
    `verse` TEXT NOT NULL,
    `sign_off` VARCHAR(128) NOT NULL,
    `hero_type` ENUM('photo', 'avatar', 'crest') NOT NULL DEFAULT 'photo',
    `hero_photo_url` VARCHAR(512) NULL,
    `audio_voice_url` VARCHAR(512) NULL,
    `share_token` VARCHAR(64) NOT NULL UNIQUE,
    `feed_post_id` INT UNSIGNED NULL,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `idx_festive_user_fam` (`user_id`, `fam_code`),
    INDEX `idx_festive_share_token` (`share_token`),
    INDEX `idx_festive_occasion` (`occasion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

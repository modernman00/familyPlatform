-- 2026_10_28_kinship_radar_settings.sql
-- Table: user_radar_settings
-- Persists user-configurable Kinship Radar matching rules, discovery sensitivity, and privacy preferences.

CREATE TABLE IF NOT EXISTS `user_radar_settings` (
    `user_id` VARCHAR(255) NOT NULL,
    `radar_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `include_inlaws` TINYINT(1) NOT NULL DEFAULT 1,
    `include_maternal` TINYINT(1) NOT NULL DEFAULT 1,
    `min_confidence` INT NOT NULL DEFAULT 50,
    `discoverable` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    INDEX `idx_radar_discoverable` (`discoverable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

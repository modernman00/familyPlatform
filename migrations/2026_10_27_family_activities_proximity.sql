-- Migration: 2026_10_27_family_activities_proximity.sql
-- Description: Proximity-localised family activities, venue geocoding & outward district caching

ALTER TABLE `events`
  ADD COLUMN `proximity_mode` VARCHAR(30) NOT NULL DEFAULT 'all_family' AFTER `party_sync_status`,
  ADD COLUMN `target_radius_miles` INT UNSIGNED NOT NULL DEFAULT 25 AFTER `proximity_mode`;

CREATE TABLE IF NOT EXISTS `member_locations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` VARCHAR(255) NOT NULL,
  `country` VARCHAR(50) NOT NULL DEFAULT 'United Kingdom',
  `postal_area` VARCHAR(20) NOT NULL COMMENT 'Outward district or city (e.g. SW19, B15, Edinburgh)',
  `latitude` DECIMAL(10, 7) NOT NULL,
  `longitude` DECIMAL(10, 7) NOT NULL,
  `proximity_sharing` ENUM('on', 'off') NOT NULL DEFAULT 'on',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_location` (`user_id`),
  KEY `idx_spatial_member` (`latitude`, `longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration: 2026_11_03_family_tree_views_and_access_control.sql
-- Purpose:
-- 1. Track family members who viewed a user's family tree (`family_tree_views`).
-- 2. Enforce per-user family tree visibility modes (`family_tree_privacy_settings`).
-- 3. Allow users to limit or grant family tree access to specific persons (`family_tree_access_rules`).

CREATE TABLE IF NOT EXISTS `family_tree_views` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tree_owner_id` VARCHAR(100) NOT NULL,
    `viewer_id` VARCHAR(100) NOT NULL,
    `family_code` VARCHAR(100) NOT NULL,
    `view_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `first_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_viewed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_owner_viewer_code` (`tree_owner_id`, `viewer_id`, `family_code`),
    KEY `idx_owner_last_viewed` (`tree_owner_id`, `last_viewed_at`),
    KEY `idx_viewer_id` (`viewer_id`),
    KEY `idx_family_code` (`family_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_tree_privacy_settings` (
    `user_id` VARCHAR(100) NOT NULL,
    `access_mode` VARCHAR(32) NOT NULL DEFAULT 'family_all',
    `notify_on_view` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    KEY `idx_access_mode` (`access_mode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_tree_access_rules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tree_owner_id` VARCHAR(100) NOT NULL,
    `target_user_id` VARCHAR(100) NOT NULL,
    `rule_type` VARCHAR(16) NOT NULL DEFAULT 'deny',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_owner_target` (`tree_owner_id`, `target_user_id`),
    KEY `idx_owner_rule` (`tree_owner_id`, `rule_type`),
    KEY `idx_target_user` (`target_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

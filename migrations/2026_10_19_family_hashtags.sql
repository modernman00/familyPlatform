-- ============================================================
-- Migration: 2026_10_19_family_hashtags
-- Purpose:   Living Heritage & Private Family Hashtags / Memory Pills.
--            Enables topical indexing, cross-generational albums,
--            and engagement prompts while strictly enforcing multi-tenant
--            family boundary isolation.
-- ============================================================

CREATE TABLE IF NOT EXISTS `family_hashtags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_code` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usage_count` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fam_tag` (`family_code`, `tag`),
  KEY `idx_fam_usage` (`family_code`, `usage_count` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_hashtag_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hashtag_id` INT UNSIGNED NOT NULL,
  `family_code` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` ENUM('post', 'reel', 'image', 'journal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
  `target_id` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_hashtag_target` (`hashtag_id`, `target_type`, `target_id`),
  KEY `idx_target_lookup` (`target_type`, `target_id`),
  KEY `idx_fam_target` (`family_code`, `target_type`, `created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

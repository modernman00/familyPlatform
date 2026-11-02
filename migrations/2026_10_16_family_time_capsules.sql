-- ============================================================
-- Migration: 2026_10_16_family_time_capsules
-- Purpose:   Living Heritage & Digital Family Time Capsule Vault.
--            Allows parents, grandparents, and kin to lock memories,
--            letters, and oral heirlooms until a future milestone date.
-- ============================================================

CREATE TABLE IF NOT EXISTS `family_time_capsules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creator_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_type` enum('none','image','video','audio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `unlock_type` enum('date','milestone_18','milestone_21','wedding','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'date',
  `unlock_date` date NOT NULL,
  `is_unlocked` tinyint(1) NOT NULL DEFAULT '0',
  `unlocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fam_unlock` (`family_code`, `unlock_date`, `is_unlocked`),
  KEY `idx_recipient` (`recipient_user_id`),
  KEY `idx_creator` (`creator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

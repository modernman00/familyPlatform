-- ============================================================================
-- Migration: 2026_11_02_magic_login_tokens.sql
-- Description: Create magic_login_tokens store for 1-click dormant re-engagement
-- Author: Modernman Platform Group / BRATS
-- ============================================================================

CREATE TABLE IF NOT EXISTS `magic_login_tokens` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `token_hash` CHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hash of plaintext token',
  `user_id` VARCHAR(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/organogram',
  `metadata` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token_hash` (`token_hash`),
  KEY `idx_magic_user_exp` (`user_id`, `expires_at`),
  KEY `idx_magic_exp_used` (`expires_at`, `used_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Secure ephemeral single-use magic login tokens';

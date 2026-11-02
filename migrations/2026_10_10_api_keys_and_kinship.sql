-- ==============================================================================
-- KinshipCore Engine & Enterprise API Key Governance Migration
-- (c) 2026 The Modernman Platform Group
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Descriptive client name (e.g. PartyPlatform Production)',
  `key_prefix` VARCHAR(16) NOT NULL COMMENT 'Visible key prefix for lookup (e.g. fp_live_7a8b)',
  `key_hash` VARCHAR(64) NOT NULL UNIQUE COMMENT 'SHA-256 hash of the secret API key token',
  `scopes` TEXT NOT NULL COMMENT 'JSON array of granted scopes e.g. ["tree:read", "tree:write"]',
  `family_codes` TEXT NULL COMMENT 'JSON array of restricted family codes or ["*"] for all',
  `rate_limit_per_min` INT UNSIGNED NOT NULL DEFAULT 120 COMMENT 'Requests allowed per minute',
  `allowed_ips` TEXT NULL COMMENT 'Optional whitelist of IP addresses or CIDRs',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = revoked',
  `expires_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Optional key expiration timestamp',
  `last_used_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Timestamp when key was last successfully used',
  `created_by` VARCHAR(255) NULL COMMENT 'Admin user ID or system who provisioned the key',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_api_keys_prefix_active` (`key_prefix`, `is_active`),
  INDEX `idx_api_keys_hash` (`key_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

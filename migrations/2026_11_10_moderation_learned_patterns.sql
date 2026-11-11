-- Migration: 2026_11_10_moderation_learned_patterns.sql
-- Table: moderation_learned_patterns
-- Description: Stores dynamically learned abusive words, phrases, and n-grams derived from member flags
--              and reinforced/curated via Super Admin moderation actions.

CREATE TABLE IF NOT EXISTS `moderation_learned_patterns` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pattern_hash` VARCHAR(64) NOT NULL UNIQUE,
    `pattern_text` VARCHAR(255) NOT NULL,
    `pattern_type` ENUM('slur', 'animus_phrase', 'profanity', 'harassment', 'general') NOT NULL DEFAULT 'general',
    `ngram_size` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `status` ENUM('candidate', 'active', 'quarantined', 'allowlisted') NOT NULL DEFAULT 'candidate',
    `confidence_score` DECIMAL(4, 3) NOT NULL DEFAULT 0.200,
    `flag_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `distinct_reporters_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `distinct_families_count` INT UNSIGNED NOT NULL DEFAULT 1,
    `admin_confirm_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `admin_reject_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_flagged_at` DATETIME NOT NULL,
    `promoted_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status_confidence` (`status`, `confidence_score`),
    INDEX `idx_ngram_size` (`ngram_size`),
    INDEX `idx_pattern_hash` (`pattern_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

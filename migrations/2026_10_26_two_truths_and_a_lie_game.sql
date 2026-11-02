-- ============================================================================
-- Migration: 2026_10_26_two_truths_and_a_lie_game.sql
-- Feature: The Fun Place — Two Truths & a Lie (Heritage Game Ritual)
-- Authors: Victor (CTO), James (Head of TFT), Gbenga Adebisi (DBRE)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `family_game_two_truths` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `famCode` VARCHAR(64) NOT NULL,
    `author_id` VARCHAR(64) NOT NULL,
    `author_name` VARCHAR(128) NOT NULL DEFAULT '',
    `post_no` INT NULL DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL DEFAULT 'Weekly Family Heritage Challenge',
    `statement_1` TEXT NOT NULL,
    `statement_2` TEXT NOT NULL,
    `statement_3` TEXT NOT NULL,
    `lie_index` TINYINT UNSIGNED NOT NULL,
    `reveal_story_1` TEXT NULL DEFAULT NULL,
    `reveal_story_2` TEXT NULL DEFAULT NULL,
    `reveal_story_3` TEXT NULL DEFAULT NULL,
    `photo_url` VARCHAR(255) NULL DEFAULT NULL,
    `status` ENUM('draft', 'active', 'revealed', 'archived') NOT NULL DEFAULT 'active',
    `reveal_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_two_truths_famCode` (`famCode`),
    INDEX `idx_two_truths_status` (`status`),
    INDEX `idx_two_truths_author` (`author_id`),
    INDEX `idx_two_truths_reveal_at` (`reveal_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_game_two_truths_votes` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `game_id` VARCHAR(64) NOT NULL,
    `famCode` VARCHAR(64) NOT NULL,
    `user_id` VARCHAR(64) NOT NULL,
    `user_name` VARCHAR(128) NOT NULL DEFAULT '',
    `guessed_lie_index` TINYINT UNSIGNED NOT NULL,
    `is_correct` TINYINT(1) NULL DEFAULT NULL,
    `points_awarded` INT NOT NULL DEFAULT 0,
    `voted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_two_truths_vote` (`game_id`, `user_id`),
    INDEX `idx_two_truths_votes_game` (`game_id`),
    INDEX `idx_two_truths_votes_user` (`user_id`),
    INDEX `idx_two_truths_votes_fam` (`famCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Family Arcade (KinZip, KinGrid, KinWord, KinPulse) Schema
-- Date: 2026-11-22

CREATE TABLE IF NOT EXISTS `family_arcade_scores` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` VARCHAR(64) NOT NULL,
    `fam_code` VARCHAR(64) NOT NULL,
    `game_slug` ENUM('kinzip', 'kingrid', 'kinword', 'kinpulse') NOT NULL,
    `mode` ENUM('daily', 'practice', 'ghost', 'live_race') NOT NULL DEFAULT 'daily',
    `puzzle_date` DATE NOT NULL,
    `seed_key` VARCHAR(64) NOT NULL,
    `difficulty` ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
    `score` INT UNSIGNED NOT NULL DEFAULT 0,
    `elapsed_ms` INT UNSIGNED NOT NULL DEFAULT 0,
    `moves_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `hints_used` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `mistakes_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `replay_json` MEDIUMTEXT NULL,
    `challenge_token` VARCHAR(64) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_arcade_challenge_token` (`challenge_token`),
    KEY `idx_arcade_fam_game_date_score` (`fam_code`, `game_slug`, `puzzle_date`, `score`),
    KEY `idx_arcade_user_game_date` (`user_id`, `game_slug`, `puzzle_date`),
    KEY `idx_arcade_fam_created` (`fam_code`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_arcade_badges` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fam_code` VARCHAR(64) NOT NULL,
    `user_id` VARCHAR(64) NOT NULL,
    `badge_key` VARCHAR(64) NOT NULL,
    `badge_title` VARCHAR(100) NOT NULL,
    `period_key` VARCHAR(32) NOT NULL,
    `score` INT UNSIGNED NOT NULL DEFAULT 0,
    `awarded_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_arcade_fam_badge_period` (`fam_code`, `badge_key`, `period_key`),
    KEY `idx_arcade_badge_user` (`user_id`, `fam_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_arcade_races` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `race_token` VARCHAR(64) NOT NULL,
    `fam_code` VARCHAR(64) NOT NULL,
    `game_slug` ENUM('kinzip', 'kingrid', 'kinword', 'kinpulse') NOT NULL,
    `seed_key` VARCHAR(64) NOT NULL,
    `host_user_id` VARCHAR(64) NOT NULL,
    `opponent_user_id` VARCHAR(64) NULL,
    `host_progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `opponent_progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('waiting', 'active', 'completed') NOT NULL DEFAULT 'waiting',
    `winner_user_id` VARCHAR(64) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_arcade_race_token` (`race_token`),
    KEY `idx_arcade_race_fam_status` (`fam_code`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

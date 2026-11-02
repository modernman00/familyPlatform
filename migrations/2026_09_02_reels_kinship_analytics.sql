-- Schema for the Reels, Kinship-suggestion, and Analytics features.
-- These tables exist in the local dev DB but have no committed migration;
-- run this on the live server before deploying the Reels/Kinship/Analytics code.
-- Safe to re-run (IF NOT EXISTS). DDL captured from dev 2026-09-02.

-- ─────────────────────────────  REELS  ─────────────────────────────

CREATE TABLE IF NOT EXISTS `family_reels` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `user_id`       VARCHAR(50)  NOT NULL,
  `famCode`       VARCHAR(50)  NOT NULL,
  `caption`       TEXT,
  `video_url`     VARCHAR(500) NOT NULL,
  `thumbnail_url` VARCHAR(500) DEFAULT NULL,
  `aspect_ratio`  VARCHAR(20)  DEFAULT '9:16',
  `category`      VARCHAR(50)  DEFAULT 'moment',
  `music_title`   VARCHAR(100) DEFAULT 'Original Audio',
  `views_count`   INT          DEFAULT 0,
  `created_at`    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `famCode` (`famCode`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `family_reel_comments` (
  `id`         INT NOT NULL AUTO_INCREMENT,
  `reel_id`    INT NOT NULL,
  `user_id`    VARCHAR(50) NOT NULL,
  `comment`    TEXT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `reel_id` (`reel_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `family_reel_reactions` (
  `id`            INT NOT NULL AUTO_INCREMENT,
  `reel_id`       INT NOT NULL,
  `user_id`       VARCHAR(50) NOT NULL,
  `reaction_type` VARCHAR(20) DEFAULT 'like',
  `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_reel_reaction` (`reel_id`,`user_id`),
  KEY `reel_id` (`reel_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ────────────────────────────  KINSHIP  ────────────────────────────

CREATE TABLE IF NOT EXISTS `kinship_dismissed` (
  `id`                INT NOT NULL AUTO_INCREMENT,
  `user_id`           VARCHAR(50) NOT NULL,
  `dismissed_user_id` VARCHAR(50) NOT NULL,
  `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_dismissal` (`user_id`,`dismissed_user_id`),
  KEY `user_id` (`user_id`),
  KEY `dismissed_user_id` (`dismissed_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ───────────────────────────  ANALYTICS  ───────────────────────────

CREATE TABLE IF NOT EXISTS `platform_analytics` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    VARCHAR(50)  NOT NULL,
  `event_type` VARCHAR(80)  NOT NULL COMMENT 'reel_view, reel_create, kinship_impression, kinship_connect, kinship_dismiss',
  `target_id`  VARCHAR(100) DEFAULT NULL COMMENT 'reel_id or suggested_user_id',
  `metadata`   JSON         DEFAULT NULL,
  `ip_address` VARCHAR(45)  DEFAULT NULL,
  `user_agent` VARCHAR(512) DEFAULT NULL,
  `created_at` TIMESTAMP    NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_event` (`user_id`,`event_type`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ──────────────  COLUMNS ADDED TO EXISTING TABLES  ──────────────
-- Added in recent commits (OAuth login, JWT rotation, premium video, AI
-- consent). MySQL has no "ADD COLUMN IF NOT EXISTS" — check each first with
--   SHOW COLUMNS FROM `account` LIKE 'is_premium';
-- and run only the ALTERs for columns your live `account` table is missing.

-- ALTER TABLE `account` ADD COLUMN `token_version` INT NULL DEFAULT 1;          -- JWT rotation (055b000)
-- ALTER TABLE `account` ADD COLUMN `google_id`     VARCHAR(255) NULL;           -- Google OAuth (f1de4f7)
-- ALTER TABLE `account` ADD COLUMN `facebook_id`   VARCHAR(255) NULL;           -- Facebook OAuth (f1de4f7)
-- ALTER TABLE `account` ADD COLUMN `ai_consent`    TINYINT(1) NULL DEFAULT 0;   -- registration AI consent (98c11fa)
-- ALTER TABLE `account` ADD COLUMN `is_premium`    TINYINT(1) NOT NULL DEFAULT 0; -- premium video tier (d8fb0cc)
-- ALTER TABLE `images`  ADD COLUMN `is_public`     TINYINT(1) NOT NULL DEFAULT 0; -- photo gallery public/private visibility (055b000)

-- login_events self-heals (LoginAnomalyService issues CREATE TABLE IF NOT
-- EXISTS on first use), so it needs no manual step.

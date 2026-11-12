-- 2026_11_11_kin_whispers_and_artifact_messaging.sql
-- Direct Kin Messaging & Asynchronous Kin Whispers Schema (Option B - Contextual Artifact Messaging)
-- Enforces UK Online Safety Act 2023 minor safeguarding and parent oversight.

CREATE TABLE IF NOT EXISTS `kin_threads` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `family_code` VARCHAR(64) NOT NULL,
    `sender_id` VARCHAR(64) NOT NULL,
    `recipient_id` VARCHAR(64) NOT NULL,
    `context_type` VARCHAR(64) NOT NULL DEFAULT 'general',
    `context_id` VARCHAR(64) NULL,
    `context_title` VARCHAR(255) NULL,
    `has_minor_participant` TINYINT(1) NOT NULL DEFAULT 0,
    `supervisor_parent_id` VARCHAR(64) NULL,
    `status` ENUM('active', 'archived', 'blocked') NOT NULL DEFAULT 'active',
    `last_message_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_kin_participants` (`sender_id`, `recipient_id`, `last_message_at`),
    INDEX `idx_kin_recipient` (`recipient_id`, `status`, `last_message_at`),
    INDEX `idx_kin_family` (`family_code`, `last_message_at`),
    INDEX `idx_kin_supervisor` (`supervisor_parent_id`, `has_minor_participant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kin_messages` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `thread_id` VARCHAR(64) NOT NULL,
    `sender_id` VARCHAR(64) NOT NULL,
    `recipient_id` VARCHAR(64) NOT NULL,
    `message_type` ENUM('text', 'audio_whisper', 'memory_prompt') NOT NULL DEFAULT 'text',
    `body` TEXT NOT NULL,
    `audio_url` VARCHAR(512) NULL,
    `audio_duration_seconds` INT UNSIGNED NULL,
    `context_metadata` JSON NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `parent_viewed` TINYINT(1) NOT NULL DEFAULT 0,
    `parent_viewed_at` TIMESTAMP NULL DEFAULT NULL,
    `expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_thread_created` (`thread_id`, `created_at`),
    INDEX `idx_recipient_unread` (`recipient_id`, `is_read`),
    INDEX `idx_expires` (`expires_at`),
    CONSTRAINT `fk_kin_messages_thread` FOREIGN KEY (`thread_id`) REFERENCES `kin_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

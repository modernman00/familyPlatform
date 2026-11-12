-- 2026_11_11_kin_whispers_share_consent_and_screenshot_audit.sql
-- Kin Whispers Bilateral Share Consent Gate & Screenshot Audit Ledger
-- Enforces mutual consent prior to external WhatsApp sharing and logs all screenshot events.

ALTER TABLE `kin_threads`
    ADD COLUMN `share_status` ENUM('none', 'pending', 'approved', 'declined') NOT NULL DEFAULT 'none' AFTER `status`,
    ADD COLUMN `share_requested_by` VARCHAR(64) NULL AFTER `share_status`,
    ADD COLUMN `share_requested_at` TIMESTAMP NULL DEFAULT NULL AFTER `share_requested_by`,
    ADD COLUMN `share_approved_by` VARCHAR(64) NULL AFTER `share_requested_at`,
    ADD COLUMN `share_approved_at` TIMESTAMP NULL DEFAULT NULL AFTER `share_approved_by`;

CREATE TABLE IF NOT EXISTS `kin_thread_events` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `thread_id` VARCHAR(64) NOT NULL,
    `user_id` VARCHAR(64) NOT NULL,
    `user_name` VARCHAR(255) NOT NULL,
    `event_type` ENUM('screenshot_taken', 'share_requested', 'share_approved', 'share_declined', 'share_revoked') NOT NULL,
    `metadata` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_thread_events` (`thread_id`, `created_at`),
    INDEX `idx_user_events` (`user_id`, `event_type`),
    CONSTRAINT `fk_kin_events_thread` FOREIGN KEY (`thread_id`) REFERENCES `kin_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Family Commitments & Recurring Targets (Tier 3)
-- Created: 2026-11-14

CREATE TABLE IF NOT EXISTS family_commitments (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    owner_user_id VARCHAR(255) NOT NULL,
    fam_code VARCHAR(100) NOT NULL DEFAULT '',
    title VARCHAR(120) NOT NULL,
    target_statement VARCHAR(500) NULL DEFAULT NULL,
    schedule_mode ENUM('fixed', 'flexible') NOT NULL DEFAULT 'fixed',
    period_unit ENUM('week', 'month') NOT NULL DEFAULT 'week',
    target_count INT UNSIGNED NOT NULL DEFAULT 1,
    days_of_week JSON NULL,
    month_day TINYINT UNSIGNED NULL DEFAULT NULL,
    preferred_time VARCHAR(5) NULL DEFAULT NULL,
    visibility ENUM('private', 'specific', 'family') NOT NULL DEFAULT 'private',
    linked_member_id VARCHAR(255) NULL DEFAULT NULL,
    in_app_reminder TINYINT(1) NOT NULL DEFAULT 1,
    timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/London',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_fc_owner_active (owner_user_id, is_active, deleted_at),
    INDEX idx_fc_fam_vis (fam_code, visibility, is_active, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commitment_participants (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    commitment_id VARCHAR(64) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    role ENUM('owner', 'participant') NOT NULL DEFAULT 'participant',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cp_commitment_user (commitment_id, user_id),
    INDEX idx_cp_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commitment_checkins (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    commitment_id VARCHAR(64) NOT NULL,
    completed_by_user_id VARCHAR(255) NOT NULL,
    period_key VARCHAR(16) NOT NULL,
    slot_key VARCHAR(32) NULL DEFAULT NULL,
    rating TINYINT UNSIGNED NULL DEFAULT NULL,
    reflection_note TEXT NOT NULL,
    completed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_cc_commitment_period (commitment_id, period_key, deleted_at),
    INDEX idx_cc_slot (commitment_id, slot_key, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commitment_reminder_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    commitment_id VARCHAR(64) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    slot_key VARCHAR(32) NOT NULL,
    status ENUM('sent', 'done', 'dismissed') NOT NULL DEFAULT 'sent',
    notification_no INT NULL DEFAULT NULL,
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    acted_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY uq_crl_slot_user (commitment_id, user_id, slot_key),
    INDEX idx_crl_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Migration: 2026_10_26_judge_yommy_family_disputes.sql
-- Feature: The Fun Place — Judge Yommy Multi-Party Reconciliatory Dispute Settlement
-- Authors: Victor (CTO), James (Head of TFT), Gbenga Adebisi (DBRE)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `family_disputes` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `famCode` VARCHAR(64) NOT NULL,
    `initiator_id` VARCHAR(64) NOT NULL,
    `is_third_party` TINYINT(1) NOT NULL DEFAULT 0,
    `party_a_id` VARCHAR(64) NOT NULL,
    `party_b_id` VARCHAR(64) NOT NULL,
    `witness_ids` TEXT NULL,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(64) NOT NULL DEFAULT 'general',
    `grievance` TEXT NOT NULL,
    `status` VARCHAR(32) NOT NULL DEFAULT 'awaiting_statements',
    `verdict_title` VARCHAR(255) NULL DEFAULT NULL,
    `finding_of_fact` TEXT NULL DEFAULT NULL,
    `reconciliatory_bridge` TEXT NULL DEFAULT NULL,
    `restorative_action_plan` TEXT NULL DEFAULT NULL,
    `yommy_wisdom` TEXT NULL DEFAULT NULL,
    `resolved_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_family_disputes_famCode` (`famCode`),
    INDEX `idx_family_disputes_initiator` (`initiator_id`),
    INDEX `idx_family_disputes_party_a` (`party_a_id`),
    INDEX `idx_family_disputes_party_b` (`party_b_id`),
    INDEX `idx_family_disputes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_dispute_statements` (
    `id` VARCHAR(64) NOT NULL PRIMARY KEY,
    `dispute_id` VARCHAR(64) NOT NULL,
    `user_id` VARCHAR(64) NOT NULL,
    `role` VARCHAR(32) NOT NULL,
    `statement` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_dispute_statements_dispute_id` (`dispute_id`),
    INDEX `idx_dispute_statements_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

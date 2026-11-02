-- ==============================================================================
-- Migration: 2026_09_14_gdpr_and_admin_fortification.sql
-- Purpose: Schema enhancements for GDPR Art. 17 Data Erasure & Admin Auditing
-- Safe & Idempotent
-- ==============================================================================

-- 1. Ensure `family_nodes` has the `is_erased` flag for preserved family trees
ALTER TABLE `family_nodes` ADD COLUMN `is_erased` TINYINT(1) NOT NULL DEFAULT 0 AFTER `avatar_url`;

-- 2. Ensure `audit_logs` has action and details columns
ALTER TABLE `audit_logs` ADD COLUMN `action` VARCHAR(100) NULL AFTER `email`, ADD COLUMN `details` TEXT NULL AFTER `action`;

-- 3. Ensure indexing on `account` for fast soft-delete and deletion lookups
ALTER TABLE `account` ADD INDEX `idx_account_deleted_at` (`deleted_at`);


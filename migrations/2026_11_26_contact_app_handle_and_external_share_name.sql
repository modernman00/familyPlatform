-- Migration: Add app_handle and external_share_name to contact table
-- Author: Platform Team
-- Date: 2026-11-26

ALTER TABLE `contact`
    ADD COLUMN IF NOT EXISTS `app_handle` VARCHAR(50) NULL DEFAULT NULL AFTER `occupation`,
    ADD COLUMN IF NOT EXISTS `external_share_name` VARCHAR(30) NOT NULL DEFAULT 'first_initial' AFTER `app_handle`,
    ADD UNIQUE INDEX IF NOT EXISTS `idx_contact_app_handle` (`app_handle`);

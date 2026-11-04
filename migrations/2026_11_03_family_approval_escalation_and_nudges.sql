-- Migration: 2026_11_03_family_approval_escalation_and_nudges.sql
-- Supports 20-minute family broadcast escalation and member nudges for pending family join requests.

ALTER TABLE `family_approval_requests`
    ADD COLUMN `broadcasted_at` TIMESTAMP NULL DEFAULT NULL AFTER `reminder_sent_at`,
    ADD COLUMN `last_nudged_at` TIMESTAMP NULL DEFAULT NULL AFTER `broadcasted_at`,
    ADD COLUMN `nudge_count` INT NOT NULL DEFAULT 0 AFTER `last_nudged_at`,
    ADD KEY `far_escalation_idx` (`status`, `created_at`, `broadcasted_at`);

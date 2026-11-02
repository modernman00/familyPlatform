-- Migration: 2026_10_22_scheduled_notifications.sql
-- Description: Upgrade notification orchestration for scheduled dispatch, index seek, and device telemetry.

-- 1. Upgrade status & channel from rigid ENUMs to VARCHAR for extensible orchestration
ALTER TABLE `notification_orchestration` 
MODIFY COLUMN `status` VARCHAR(32) NOT NULL DEFAULT 'pending';

-- 2. Add scheduled_at column if not exists
ALTER TABLE `notification_orchestration` 
ADD COLUMN IF NOT EXISTS `scheduled_at` DATETIME NULL AFTER `status`;

-- 3. Composite seek index for 1-minute worker
CREATE INDEX IF NOT EXISTS `idx_notif_sched_status` 
ON `notification_orchestration` (`status`, `scheduled_at`);

-- 4. Extensible channel and status for delivery logs
ALTER TABLE `notification_delivery_logs` 
MODIFY COLUMN `channel` VARCHAR(32) NOT NULL,
MODIFY COLUMN `status` VARCHAR(64) NOT NULL;

-- 5. User push subscriptions device telemetry
CREATE TABLE IF NOT EXISTS `user_push_subscriptions` (
    `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` VARCHAR(255) NOT NULL,
    `endpoint` TEXT NOT NULL,
    `p256dh` VARCHAR(255) NOT NULL,
    `auth_token` VARCHAR(255) NOT NULL,
    `device_platform` VARCHAR(32) NULL,
    `browser` VARCHAR(32) NULL,
    `app_version` VARCHAR(32) NULL,
    `user_agent` TEXT NULL,
    `failure_count` INT DEFAULT 0,
    `last_seen` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_sub_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

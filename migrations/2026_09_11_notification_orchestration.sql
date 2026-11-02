-- Migration: 2026_09_11_notification_orchestration.sql
-- Description: Create tables and indexes for real-time notification orchestration, delivery logging, and presence caching.

-- 1. Extend notifications table if needed, or create unified master table
CREATE TABLE IF NOT EXISTS `notification_orchestration` (
  `id` VARCHAR(64) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `family_code` VARCHAR(255) DEFAULT NULL,
  `category` ENUM('social', 'financial', 'security', 'system') NOT NULL DEFAULT 'social',
  `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `action_url` VARCHAR(500) NOT NULL DEFAULT '/profilePage',
  `tag` VARCHAR(100) NOT NULL DEFAULT 'general',
  `data_payload` JSON DEFAULT NULL,
  `status` ENUM('pending', 'delivered', 'read', 'dismissed') NOT NULL DEFAULT 'pending',
  `read_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`user_id`, `status`),
  KEY `idx_tag` (`tag`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 2. Multi-Channel Delivery Logs (Tracks channel attempts & prevents double sends)
CREATE TABLE IF NOT EXISTS `notification_delivery_logs` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `notification_id` VARCHAR(64) NOT NULL,
  `channel` ENUM('in_app_socket', 'web_push', 'pwa_push', 'email') NOT NULL,
  `status` ENUM('queued', 'sent', 'failed', 'cancelled_user_active', 'cancelled_already_read') NOT NULL,
  `details` TEXT DEFAULT NULL,
  `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_channel` (`notification_id`, `channel`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 3. Socket Presence Cache (Avoids synchronous HTTP rate-limiting against Pusher)
CREATE TABLE IF NOT EXISTS `user_socket_presence` (
  `user_id` VARCHAR(255) NOT NULL,
  `channel_name` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_heartbeat` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `channel_name`),
  KEY `idx_active_heartbeat` (`is_active`, `last_heartbeat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

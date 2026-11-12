-- =====================================================================
-- 🎙️ Migration: Kin Whispers Group Chats & In-App WebRTC Calling
-- Description: Adds thread_type, group title, calling state to kin_threads,
--              creates kin_thread_participants, makes kin_messages.recipient_id nullable,
--              and backfills existing direct threads into participants table.
-- =====================================================================

-- 1. Extend kin_threads with group chat and calling metadata
ALTER TABLE `kin_threads`
    ADD COLUMN `thread_type` ENUM('direct', 'group') NOT NULL DEFAULT 'direct' AFTER `family_code`,
    ADD COLUMN `title` VARCHAR(255) NULL DEFAULT NULL AFTER `thread_type`,
    ADD COLUMN `created_by` VARCHAR(64) NULL DEFAULT NULL AFTER `title`,
    MODIFY COLUMN `recipient_id` VARCHAR(64) NULL DEFAULT NULL,
    ADD COLUMN `call_status` ENUM('idle', 'ringing', 'in_call') NOT NULL DEFAULT 'idle' AFTER `status`,
    ADD COLUMN `active_call_room_id` VARCHAR(64) NULL DEFAULT NULL AFTER `call_status`;

-- 2. Modify kin_messages to allow NULL recipient_id for group broadcasts
ALTER TABLE `kin_messages`
    MODIFY COLUMN `recipient_id` VARCHAR(64) NULL DEFAULT NULL;

-- 3. Create kin_thread_participants table for multi-kin membership & reading tracking
CREATE TABLE IF NOT EXISTS `kin_thread_participants` (
    `id` VARCHAR(64) NOT NULL,
    `thread_id` VARCHAR(64) NOT NULL,
    `user_id` VARCHAR(64) NOT NULL,
    `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    `last_read_at` TIMESTAMP NULL DEFAULT NULL,
    `joined_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_thread_user` (`thread_id`, `user_id`),
    KEY `idx_user_threads` (`user_id`, `deleted_at`),
    KEY `idx_thread_active` (`thread_id`, `deleted_at`),
    CONSTRAINT `fk_kin_thread_participants_thread` FOREIGN KEY (`thread_id`) REFERENCES `kin_threads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Backfill existing direct threads into kin_thread_participants
-- Backfill Sender (Admin)
INSERT IGNORE INTO `kin_thread_participants` (`id`, `thread_id`, `user_id`, `role`, `joined_at`)
SELECT 
    CONCAT('part_', SUBSTRING(SHA2(CONCAT(t.id, '_', t.sender_id), 256), 1, 32)),
    t.id,
    t.sender_id,
    'admin',
    t.created_at
FROM `kin_threads` t;

-- Backfill Recipient (Member)
INSERT IGNORE INTO `kin_thread_participants` (`id`, `thread_id`, `user_id`, `role`, `joined_at`)
SELECT 
    CONCAT('part_', SUBSTRING(SHA2(CONCAT(t.id, '_', t.recipient_id), 256), 1, 32)),
    t.id,
    t.recipient_id,
    'member',
    t.created_at
FROM `kin_threads` t
WHERE t.recipient_id IS NOT NULL 
  AND t.recipient_id != '' 
  AND t.recipient_id != t.sender_id;

-- 5. Backfill created_by on existing threads
UPDATE `kin_threads` SET `created_by` = `sender_id` WHERE `created_by` IS NULL;

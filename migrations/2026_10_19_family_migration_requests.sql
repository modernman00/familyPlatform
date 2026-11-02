-- Family Migration Requests table
-- Handles nudging relatives to migrate to family codes and self-initiated migration requests
CREATE TABLE IF NOT EXISTS `family_migration_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `requester_id` varchar(255) NOT NULL COMMENT 'User who initiated: Member A (nudge) or Member B (request)',
  `target_user_id` varchar(255) NOT NULL COMMENT 'User to be migrated to new family code: Member B',
  `old_family_code` varchar(50) NOT NULL COMMENT 'Current family code of target user before migration',
  `new_family_code` varchar(50) NOT NULL COMMENT 'Target family code to migrate into',
  `approver_id` varchar(255) NULL DEFAULT NULL COMMENT 'Approving member id (set upon nudge or upon request approval)',
  `type` enum('nudge','request') NOT NULL COMMENT 'nudge = relative invited to join family; request = user asks to join family',
  `status` enum('pending','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'pending',
  `message` text NULL COMMENT 'Optional personal note explaining kinship or migration reason',
  `token` varchar(64) NOT NULL COMMENT 'HMAC-SHA256 signature token preventing enumeration',
  `request_expires_at` timestamp NOT NULL COMMENT 'Expiration window (7 days)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `target_user_status_idx` (`target_user_id`, `status`),
  KEY `new_family_code_status_idx` (`new_family_code`, `status`),
  KEY `requester_id_idx` (`requester_id`),
  KEY `status_expires_idx` (`status`, `request_expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

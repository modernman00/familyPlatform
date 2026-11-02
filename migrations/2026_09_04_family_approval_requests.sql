-- Family Code Approval Requests table
-- Stores pending approvals when a new user joins with an existing family code
CREATE TABLE IF NOT EXISTS `family_approval_requests` (
  `no` int NOT NULL AUTO_INCREMENT,
  `id` varchar(255) NOT NULL COMMENT 'UUID of new user requesting to join',
  `family_code` varchar(50) NOT NULL COMMENT 'The family code they are trying to join',
  `inviter_first_name` varchar(100) NOT NULL COMMENT 'First name of person who invited them',
  `inviter_last_name` varchar(100) NOT NULL COMMENT 'Last name of person who invited them',
  `inviter_email_or_mobile` varchar(100) NOT NULL COMMENT 'Email or mobile of the inviter',
  `approver_id` varchar(255) COMMENT 'UUID of family member who approved (null if pending)',
  `temporary_code` varchar(50) NOT NULL COMMENT 'Temp code assigned until approval',
  `status` enum('pending','approved','denied','expired') NOT NULL DEFAULT 'pending',
  `request_expires_at` timestamp NOT NULL COMMENT '7 days from creation',
  `reminder_sent_at` timestamp NULL COMMENT '2-day reminder sent timestamp',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`no`),
  UNIQUE KEY `unique_pending_inviter` (`id`, `approver_id`) COMMENT 'Prevents multiple pending requests from same approver',
  KEY `family_code_idx` (`family_code`),
  KEY `approver_id_idx` (`approver_id`),
  KEY `status_expires_idx` (`status`, `request_expires_at`),
  KEY `created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

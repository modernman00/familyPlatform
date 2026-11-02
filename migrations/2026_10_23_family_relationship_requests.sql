-- Relationship confirmation requests: a half-sibling / step-sibling / former partner
-- is added to the tree and the people on the other side are asked to confirm.
CREATE TABLE IF NOT EXISTS `family_relationship_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `family_code` VARCHAR(64) NOT NULL,
  `requester_id` VARCHAR(191) NOT NULL COMMENT 'account id of the member who added the relative',
  `approver_id` VARCHAR(191) NOT NULL COMMENT 'account id asked to confirm',
  `node_id` INT UNSIGNED NOT NULL COMMENT 'the relative that was added',
  `union_id` INT UNSIGNED NULL COMMENT 'union the relative hangs from / belongs to',
  `kind` ENUM('half_sibling','step_sibling','former_partner') NOT NULL,
  `status` ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY `uniq_rel_req` (`approver_id`, `node_id`, `kind`),
  KEY `idx_rel_req_approver` (`approver_id`, `status`),
  KEY `idx_rel_req_requester` (`requester_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

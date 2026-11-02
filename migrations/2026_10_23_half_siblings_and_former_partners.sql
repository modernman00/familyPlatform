-- ==============================================================================
-- Half-siblings, former partners & placeholder parents (RFC-0001)
-- Additive only, idempotent, no table rewrites of existing columns.
-- 1) Baseline CREATE TABLE IF NOT EXISTS for the graph tables (previously dev-only)
-- 2) Guarded ADD COLUMN / ADD INDEX for the new fields
-- ==============================================================================

CREATE TABLE IF NOT EXISTS `family_nodes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_code` varchar(64) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL COMMENT 'Linked member account ID if registered',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `maiden_name` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL DEFAULT 'Other',
  `birth_date` date DEFAULT NULL,
  `birth_year` int DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `death_year` int DEFAULT NULL,
  `death_place` varchar(255) DEFAULT NULL,
  `is_deceased` tinyint(1) NOT NULL DEFAULT '0',
  `bio` text,
  `occupation` varchar(150) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(50) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `is_erased` tinyint(1) NOT NULL DEFAULT '0',
  `generation_level` smallint NOT NULL DEFAULT '0',
  `voice_capsule_url` varchar(255) DEFAULT NULL COMMENT 'Living voice memory audio file',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fam_code` (`family_code`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_fn_fam_user` (`family_code`,`user_id`),
  KEY `idx_fn_fam_gen` (`family_code`,`generation_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_unions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `family_code` varchar(64) NOT NULL,
  `partner_1_id` int unsigned NOT NULL,
  `partner_2_id` int unsigned NOT NULL,
  `union_type` enum('married','divorced','separated','partner','widowed','annulled') NOT NULL DEFAULT 'married',
  `marriage_date` date DEFAULT NULL,
  `marriage_year` int DEFAULT NULL,
  `divorce_date` date DEFAULT NULL,
  `divorce_year` int DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT '1',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_union_partners` (`partner_1_id`,`partner_2_id`),
  KEY `idx_union_fam` (`family_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `family_node_children` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `union_id` int unsigned DEFAULT NULL COMMENT 'Union ID if born within marriage/partnership',
  `parent_node_id` int unsigned DEFAULT NULL COMMENT 'Direct parent node if single parent',
  `child_id` int unsigned NOT NULL,
  `relationship_type` enum('biological','adopted','foster','step') NOT NULL DEFAULT 'biological',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_union_child` (`union_id`,`child_id`),
  KEY `idx_child_parent` (`child_id`,`parent_node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- additive columns / indexes ---------------------------------------------
-- Plain ALTERs: the migration runner ignores "Duplicate column name" / "Duplicate key name",
-- which keeps them idempotent without PREPARE (PREPARE/EXECUTE breaks PDO::exec).

-- family_nodes.is_placeholder : "Unknown father/mother" ghost nodes so a half-sibling hangs from a known parent.
ALTER TABLE `family_nodes` ADD COLUMN `is_placeholder` TINYINT(1) NOT NULL DEFAULT 0;

-- family_unions.ordinal : display order of a person's successive unions
ALTER TABLE `family_unions` ADD COLUMN `ordinal` TINYINT UNSIGNED NULL;

ALTER TABLE `family_unions` ADD INDEX `idx_union_p2` (`partner_2_id`);
ALTER TABLE `family_node_children` ADD INDEX `idx_child_only` (`child_id`);

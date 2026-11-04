-- Migration: 2026_11_03_post_family_target_lineages.sql
-- Description: Dual-lineage post audience targets (all, birth, married) and relational indexing

-- 1. Ensure target_scope column on post table
SET @col_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'post' 
      AND COLUMN_NAME = 'target_scope'
);

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `post` ADD COLUMN `target_scope` ENUM(\'all\', \'birth\', \'married\') NOT NULL DEFAULT \'all\' AFTER `postFamCode`', 
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Create post_family_targets junction table
CREATE TABLE IF NOT EXISTS `post_family_targets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_no` INT NOT NULL,
  `family_code` VARCHAR(50) NOT NULL,
  `lineage_kind` ENUM('primary', 'birth') NOT NULL DEFAULT 'primary',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_post_family` (`post_no`, `family_code`),
  INDEX `idx_family_code` (`family_code`),
  INDEX `idx_post_no` (`post_no`),
  INDEX `idx_lineage_kind` (`lineage_kind`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Backfill existing published posts into post_family_targets
INSERT IGNORE INTO `post_family_targets` (`post_no`, `family_code`, `lineage_kind`)
SELECT p.post_no, UPPER(TRIM(REPLACE(p.postFamCode, '#', ''))), COALESCE(fc.kind, 'primary')
FROM `post` p
LEFT JOIN `family_codes` fc ON fc.code = UPPER(TRIM(REPLACE(p.postFamCode, '#', '')))
WHERE p.postFamCode IS NOT NULL 
  AND TRIM(p.postFamCode) <> '' 
  AND p.postFamCode NOT LIKE '%,%';

-- RFC-0004 Birth / Maiden Family Code. Additive: new registry table + backfill + case normalisation.
-- (family_approval_requests.purpose is added by FamilyCodeApprovalService::ensureSchema.)
CREATE TABLE IF NOT EXISTS `family_codes` (
  `code` VARCHAR(50) NOT NULL PRIMARY KEY,
  `kind` ENUM('primary','birth') NOT NULL DEFAULT 'primary',
  `created_by` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `family_codes` (`code`, `kind`)
SELECT DISTINCT UPPER(TRIM(REPLACE(`famCode`, '#', ''))), 'primary'
FROM `personal`
WHERE `famCode` IS NOT NULL AND TRIM(`famCode`) <> '';

UPDATE `otherFamily`
SET `otherFamCode` = UPPER(TRIM(REPLACE(`otherFamCode`, '#', '')))
WHERE `otherFamCode` IS NOT NULL AND `otherFamCode` <> '';

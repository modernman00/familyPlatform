-- RFC-0005 Backfill: populate family_codes.created_by for existing primary codes where created_by IS NULL,
-- using the earliest approved admin in user_families or the earliest active primary member in personal (by p.no ASC),
-- and ensure that founder's user_families row carries role = 'admin'.

UPDATE `family_codes` fc
SET fc.`created_by` = COALESCE(
    (
        SELECT uf.`user_id`
        FROM `user_families` uf
        WHERE CONVERT(UPPER(TRIM(REPLACE(uf.`family_code`, '#', ''))) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(fc.`code` USING utf8mb4) COLLATE utf8mb4_unicode_ci
          AND uf.`role` = 'admin'
          AND uf.`status` = 'approved'
        ORDER BY uf.`id` ASC
        LIMIT 1
    ),
    (
        SELECT p.`id`
        FROM `personal` p
        JOIN `account` a ON CONVERT(a.`id` USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(p.`id` USING utf8mb4) COLLATE utf8mb4_unicode_ci AND a.`deleted_at` IS NULL
        WHERE CONVERT(UPPER(TRIM(REPLACE(p.`famCode`, '#', ''))) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(fc.`code` USING utf8mb4) COLLATE utf8mb4_unicode_ci
        ORDER BY p.`no` ASC
        LIMIT 1
    )
)
WHERE (fc.`created_by` IS NULL OR fc.`created_by` = '')
  AND fc.`code` NOT IN ('TEMP123');

UPDATE `user_families` uf
JOIN `family_codes` fc
  ON CONVERT(UPPER(TRIM(REPLACE(uf.`family_code`, '#', ''))) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(fc.`code` USING utf8mb4) COLLATE utf8mb4_unicode_ci
  AND CONVERT(uf.`user_id` USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(fc.`created_by` USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET uf.`role` = 'admin'
WHERE uf.`status` = 'approved'
  AND fc.`created_by` IS NOT NULL
  AND fc.`created_by` <> '';

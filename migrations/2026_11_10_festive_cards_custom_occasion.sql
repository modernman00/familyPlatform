-- RFC-0019 v2: "Your Own Celebration" — free-text occasion name + mood for uncategorised celebrations
-- (e.g. "Dad's 25th Remembrance"). Both nullable: curated occasions keep NULL.
-- MySQL 8 has no ADD COLUMN IF NOT EXISTS; run once. The service tolerates the columns being absent.
ALTER TABLE `festive_cards`
  ADD COLUMN `custom_occasion` VARCHAR(120) NULL DEFAULT NULL AFTER `occasion`,
  ADD COLUMN `mood` VARCHAR(24) NULL DEFAULT NULL AFTER `custom_occasion`;

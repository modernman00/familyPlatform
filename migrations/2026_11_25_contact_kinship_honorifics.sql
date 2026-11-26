-- Add personal preference for viewer-relative respectful kinship honorifics on @mentions and post tags
ALTER TABLE `contact`
    ADD COLUMN IF NOT EXISTS `kinship_honorifics` VARCHAR(10) NOT NULL DEFAULT 'on' AFTER `sms_notifications`;

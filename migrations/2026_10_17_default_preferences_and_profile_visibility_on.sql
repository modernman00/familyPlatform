-- Set default 'on' for notification preferences and 'Public'/'on' for profile discovery in contact table
ALTER TABLE `contact`
  MODIFY COLUMN `email_notifications` varchar(10) DEFAULT 'on',
  MODIFY COLUMN `sms_notifications` varchar(10) DEFAULT 'on',
  MODIFY COLUMN `profile_visibility` varchar(30) DEFAULT 'Public',
  MODIFY COLUMN `show_my_profile` varchar(10) DEFAULT 'on';

-- Backfill existing records to default Public and 'on'
UPDATE `contact`
SET
  `email_notifications` = 'on',
  `sms_notifications` = 'on',
  `profile_visibility` = 'Public',
  `show_my_profile` = 'on';

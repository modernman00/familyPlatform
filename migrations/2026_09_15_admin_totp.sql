-- Migration to add Google Authenticator 2-FA TOTP columns for Administrative Users
ALTER TABLE `account` 
ADD COLUMN `totp_secret` VARCHAR(64) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `totp_secret`;

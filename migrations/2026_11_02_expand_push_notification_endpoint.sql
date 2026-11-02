-- Migration: 2026_11_02_expand_push_notification_endpoint.sql
-- Description: Ensure pushNotification.endpoint supports long FCM/WebKit/Apple Web Push URLs without truncation.

ALTER TABLE `pushNotification` 
MODIFY COLUMN `endpoint` TEXT NOT NULL;

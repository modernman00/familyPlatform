-- ============================================================================
-- Migration: 2026_10_26_party_platform_event_bridge.sql
-- RFC: RFC-0008 (FamilyPlatform <-> PartyPlatform Event Management Bridge)
-- Author: Damilola "Dami" Ogunleye & Gbenga Adebisi (DBRE)
-- ============================================================================

ALTER TABLE `events` ADD COLUMN `party_event_id` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `events` ADD COLUMN `party_event_slug` VARCHAR(150) NULL DEFAULT NULL;
ALTER TABLE `events` ADD COLUMN `party_rsvp_url` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `events` ADD COLUMN `party_card_url` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `events` ADD COLUMN `party_sync_status` VARCHAR(20) NULL DEFAULT NULL;
CREATE INDEX `idx_events_party_event_id` ON `events` (`party_event_id`);

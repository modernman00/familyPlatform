-- Migration: 2026_10_26_reels_chrono_sync_and_mesh.sql
-- FamilyPlatform Reels: Multi-Angle Chrono-Sync Mesh, Angle Vantage Labels, and Kinship Tree Time-Bridge.
-- Retains the 7-day expiration invariant as decreed by the CEO.

ALTER TABLE `family_reels`
  ADD COLUMN `chrono_sync_cluster_id` VARCHAR(64) NULL DEFAULT NULL AFTER `music_title`,
  ADD COLUMN `chrono_sync_label` VARCHAR(60) NULL DEFAULT NULL AFTER `chrono_sync_cluster_id`,
  ADD COLUMN `time_bridge_node_id` INT NULL DEFAULT NULL AFTER `chrono_sync_label`,
  ADD KEY `idx_reel_chrono_cluster` (`chrono_sync_cluster_id`),
  ADD KEY `idx_reel_time_bridge` (`time_bridge_node_id`);

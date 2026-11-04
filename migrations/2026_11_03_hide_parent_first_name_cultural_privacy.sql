-- Migration: Cultural Privacy - Option to show initial only for parents' first names
-- Date: 2026-11-03
-- Cluster: FamilyPlatform

ALTER TABLE `family_tree_privacy_settings`
ADD COLUMN `hide_parent_first_name` TINYINT(1) NOT NULL DEFAULT 0 AFTER `notify_on_view`;

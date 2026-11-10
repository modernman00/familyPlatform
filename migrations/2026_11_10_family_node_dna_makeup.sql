-- Migration: Family Tree Node DNA Make-Up & Biogeographical Ethnicity Profile (Layers 1 & 2)
-- Date: 2026-11-10
-- Cluster: FamilyPlatform

ALTER TABLE `family_nodes`
ADD COLUMN `dna_ethnicity_json` TEXT NULL DEFAULT NULL COMMENT 'Recorded or custom DNA ethnicity and haplogroup profile JSON' AFTER `voice_capsule_url`;

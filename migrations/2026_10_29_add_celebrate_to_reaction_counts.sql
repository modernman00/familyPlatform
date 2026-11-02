-- Migration: 2026_10_29_add_celebrate_to_reaction_counts.sql
-- Description: Add celebrate reaction count column to reaction_counts table for Living Heirloom Glass 3D reactions
-- Author: BRATS & Technical Approval Team

ALTER TABLE `reaction_counts`
ADD COLUMN `celebrate` INT NOT NULL DEFAULT '0' AFTER `angry`;

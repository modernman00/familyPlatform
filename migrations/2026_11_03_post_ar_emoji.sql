-- Migration: 2026_11_03_post_ar_emoji.sql
-- Description: Adds ar_emoji column to post table for featured 3D / Augmented Reality emojis attached to posts.

ALTER TABLE post ADD COLUMN IF NOT EXISTS ar_emoji VARCHAR(64) NULL DEFAULT NULL AFTER voice_capsule_url;

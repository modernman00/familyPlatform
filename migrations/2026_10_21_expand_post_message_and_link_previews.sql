-- Migration: Expand post.postMessage to utf8mb4 TEXT and create link_preview_cache table
-- Execution Date: 2026-10-21
-- Purpose: Prevent truncation of social share links and enable high-performance cached OpenGraph previews

ALTER TABLE post MODIFY COLUMN postMessage TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;

CREATE TABLE IF NOT EXISTS link_preview_cache (
    url_hash VARCHAR(64) NOT NULL PRIMARY KEY,
    url TEXT NOT NULL,
    canonical_url TEXT DEFAULT NULL,
    title VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image_url TEXT DEFAULT NULL,
    site_name VARCHAR(150) DEFAULT NULL,
    domain VARCHAR(150) DEFAULT NULL,
    card_type VARCHAR(50) DEFAULT 'summary_large_image',
    fetched_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

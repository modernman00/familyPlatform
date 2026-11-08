-- Migration: 2026_11_08_member_avatar_traits.sql
-- RFC: docs/rfcs/0011-avatar-stickers.md
-- Description: Persist each member's procedural studio avatar traits so the server can
--   re-render their avatar performing a reaction ("Me-stickers" in posts and comments).
--   Additive only. Rollback: DROP TABLE member_avatar_traits;

CREATE TABLE IF NOT EXISTS member_avatar_traits (
  user_id     VARCHAR(64)  NOT NULL,
  traits      JSON         NOT NULL,
  mode        VARCHAR(20)  NOT NULL DEFAULT 'procedural',
  version     INT UNSIGNED NOT NULL DEFAULT 1,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

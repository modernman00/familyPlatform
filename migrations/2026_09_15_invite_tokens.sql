-- ============================================================
-- Migration: 2026_09_15_invite_tokens
-- Purpose:   Opaque invite token store — replaces all PII-bearing
--            query params (?famCode=, ?name=, ?email=) with a
--            single opaque hex token resolved server-side.
-- ============================================================

CREATE TABLE IF NOT EXISTS `invite_tokens` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `token`        CHAR(64)         NOT NULL COMMENT 'bin2hex(random_bytes(32)) — 256-bit entropy',
  `family_code`  VARCHAR(50)      NOT NULL,
  `first_name`   VARCHAR(100)     DEFAULT NULL,
  `last_name`    VARCHAR(100)     DEFAULT NULL,
  `email`        VARCHAR(150)     DEFAULT NULL,
  `node_id`      INT UNSIGNED     DEFAULT NULL COMMENT 'family_nodes.id if targeting a specific tree node',
  `invited_by`   VARCHAR(255)     DEFAULT NULL COMMENT 'user id of the sender',
  `type`         ENUM('family_invite','onboarding','organogram','referral')
                                  NOT NULL DEFAULT 'family_invite',
  `use_count`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_uses`     TINYINT UNSIGNED NOT NULL DEFAULT 1
                                  COMMENT '1 = personal invite; 50 = general family link',
  `used_at`      TIMESTAMP        NULL DEFAULT NULL,
  `expires_at`   TIMESTAMP        NOT NULL,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE  KEY `uq_token`        (`token`),
  INDEX         `idx_token_exp` (`token`, `expires_at`, `used_at`),
  INDEX         `idx_invited_by`(`invited_by`),
  INDEX         `idx_family`    (`family_code`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Opaque invite token store — all PII in this row, zero PII in URLs';

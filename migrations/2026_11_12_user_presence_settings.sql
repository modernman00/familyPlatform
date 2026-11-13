CREATE TABLE IF NOT EXISTS user_presence_settings (
    user_id VARCHAR(191) NOT NULL PRIMARY KEY,
    last_seen_privacy ENUM('everyone', 'nobody') NOT NULL DEFAULT 'everyone',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

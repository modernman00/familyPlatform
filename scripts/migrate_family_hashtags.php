<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

try {
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
    $dbName = $_ENV['DB_NAME'] ?? 'family';
    $dbUser = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

    $dsn = "mysql:host=" . (string)$dbHost . ";dbname=" . (string)$dbName . ";charset=utf8mb4";
    $pdo = new PDO($dsn, (string)$dbUser, (string)$dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to database successfully.\n";

    // 1. Create family_hashtags table
    $sqlHashtags = "CREATE TABLE IF NOT EXISTS `family_hashtags` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `family_code` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      `tag` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      `usage_count` INT UNSIGNED NOT NULL DEFAULT 1,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_fam_tag` (`family_code`, `tag`),
      KEY `idx_fam_usage` (`family_code`, `usage_count` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlHashtags);
    echo "✅ Table `family_hashtags` verified/created successfully.\n";

    // 2. Create family_hashtag_links table
    $sqlLinks = "CREATE TABLE IF NOT EXISTS `family_hashtag_links` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `hashtag_id` INT UNSIGNED NOT NULL,
      `family_code` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      `target_type` ENUM('post', 'reel', 'image', 'journal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'post',
      `target_id` VARCHAR(64) COLLATE utf8mb4_unicode_ci NOT NULL,
      `author_id` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_hashtag_target` (`hashtag_id`, `target_type`, `target_id`),
      KEY `idx_target_lookup` (`target_type`, `target_id`),
      KEY `idx_fam_target` (`family_code`, `target_type`, `created_at` DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlLinks);
    echo "✅ Table `family_hashtag_links` verified/created successfully.\n";

} catch (\Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

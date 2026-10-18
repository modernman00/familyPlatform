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

    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to database {$dbName} at {$dbHost}\n";

    // 1. Create family_journals table
    $sqlJournals = "CREATE TABLE IF NOT EXISTS `family_journals` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `family_code` VARCHAR(64) NOT NULL,
      `author_id` VARCHAR(255) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `slug` VARCHAR(255) NOT NULL,
      `summary` TEXT NULL,
      `content` LONGTEXT NOT NULL,
      `cover_image` VARCHAR(500) NULL,
      `category` VARCHAR(100) NOT NULL DEFAULT 'Milestone',
      `mood` VARCHAR(50) NULL DEFAULT 'heartwarming',
      `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_fam_journal_code_created` (`family_code`, `created_at`),
      INDEX `idx_fam_journal_author` (`author_id`),
      INDEX `idx_fam_journal_slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlJournals);
    echo "✅ Table `family_journals` verified/created successfully.\n";

    // 2. Create family_announcements table
    $sqlAnnouncements = "CREATE TABLE IF NOT EXISTS `family_announcements` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `family_code` VARCHAR(64) NOT NULL,
      `author_id` VARCHAR(255) NOT NULL,
      `title` VARCHAR(255) NOT NULL,
      `message` TEXT NOT NULL,
      `priority` ENUM('normal', 'important', 'urgent') NOT NULL DEFAULT 'important',
      `type` ENUM('general', 'reunion', 'milestone', 'alert', 'celebration') NOT NULL DEFAULT 'general',
      `is_pinned` TINYINT(1) NOT NULL DEFAULT 1,
      `expires_at` DATE NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX `idx_fam_announce_code_pinned` (`family_code`, `is_pinned`, `created_at`),
      INDEX `idx_fam_announce_author` (`author_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sqlAnnouncements);
    echo "✅ Table `family_announcements` verified/created successfully.\n";

} catch (\Throwable $e) {
    echo "❌ Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}

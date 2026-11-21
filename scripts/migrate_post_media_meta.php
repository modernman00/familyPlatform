<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

try {
    $dbHost = (string)($_ENV['DB_HOST'] ?? 'localhost');
    $dbName = (string)($_ENV['DB_NAME'] ?? 'family');
    $dbUser = (string)($_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root');
    $dbPass = (string)($_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '');

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $checkStmt = $pdo->query("SHOW COLUMNS FROM `post` LIKE 'media_meta'");
    $exists = $checkStmt !== false && $checkStmt->fetch() !== false;

    if ($exists) {
        echo "✅ Column `post.media_meta` already exists.\n";
    } else {
        $pdo->exec("ALTER TABLE `post` ADD COLUMN `media_meta` JSON NULL DEFAULT NULL AFTER `post_img5`");
        echo "✅ Added `post.media_meta` JSON column successfully.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Migration failed: " . $e->getMessage() . "\n");
    exit(1);
}

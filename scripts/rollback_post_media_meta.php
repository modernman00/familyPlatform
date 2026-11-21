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

    if (!$exists) {
        echo "✅ Column `post.media_meta` does not exist (already rolled back).\n";
    } else {
        $pdo->exec("ALTER TABLE `post` DROP COLUMN `media_meta`");
        echo "✅ Rolled back `post.media_meta` column successfully.\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, "❌ Rollback failed: " . $e->getMessage() . "\n");
    exit(1);
}

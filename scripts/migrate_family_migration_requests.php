<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use Src\Db;

try {
    $db = Db::connect2();
    $sql = file_get_contents(__DIR__ . '/../migrations/2026_10_19_family_migration_requests.sql');
    if ($sql === false) {
        throw new \RuntimeException("Unable to read migration file.");
    }

    $db->exec($sql);
    echo "✅ family_migration_requests table created or already exists.\n";

    // Verify table structure
    $stmt = $db->query("DESCRIBE family_migration_requests");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $columns) . "\n";
    echo "Migration completed successfully.\n";
} catch (\Throwable $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

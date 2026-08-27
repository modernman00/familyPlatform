<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

try {
    $db = \Src\Db::connect2();
    
    // 1. Fix the column definition
    $db->exec("ALTER TABLE post MODIFY COLUMN date_deleted timestamp NULL DEFAULT NULL");
    
    // 2. Reset date_deleted for published posts
    $db->exec("UPDATE post SET date_deleted = NULL WHERE post_status = 'published'");
    
    // 3. Fix profilePics table as well just in case
    $db->exec("ALTER TABLE profilePics MODIFY COLUMN date_deleted timestamp NULL DEFAULT NULL");
    
    echo "Schema and data fixed successfully!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

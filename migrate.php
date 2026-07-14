<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

use Src\Db;

class Migration extends Db {
    public function up() {
        $db = self::connect2();
        $sql = "
        CREATE TABLE IF NOT EXISTS user_families (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(255) NOT NULL,
            family_code VARCHAR(255) NOT NULL,
            status ENUM('pending', 'approved') DEFAULT 'pending',
            role ENUM('member', 'admin') DEFAULT 'member',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_family_unique (user_id, family_code)
        );
        ";
        $db->exec($sql);
        echo "Migration user_families executed successfully.\n";
        
        // Also let's port existing users into user_families
        // Assuming personal table has id (which is user_id) and famCode
        $sqlMigrate = "
        INSERT IGNORE INTO user_families (user_id, family_code, status, role)
        SELECT id, famCode, 'approved', 'member' FROM personal WHERE famCode IS NOT NULL;
        ";
        $db->exec($sqlMigrate);
        echo "Existing users migrated to user_families.\n";
    }
}

$m = new Migration();
$m->up();

<?php
require_once __DIR__ . '/../app/config/init.php';

use App\classes\Db;

try {
    $db = Db::getConnection();
    
    $deletedCount = $db->exec("
        DELETE FROM family_nodes 
        WHERE first_name IN (
            'Grandfather (Paternal)', 
            'Grandmother (Paternal)', 
            'Grandfather (Maternal)', 
            'Grandmother (Maternal)', 
            'Great-Grandfather (Paternal Line)', 
            'Great-Grandmother (Paternal Line)'
        )
        AND user_id IS NULL
        AND generation_level IN (-2, -3)
    ");
    
    echo "Successfully deleted $deletedCount ghost nodes from the database.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

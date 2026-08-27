<?php
use Src\Db;
require_once __DIR__ . '/../app/config/init.php';

try {
    $db = Db::connect2();
    
    // Keep Union 6. Move children from Union 10 and 15 to Union 6.
    $db->exec("UPDATE family_node_children SET union_id = 6 WHERE union_id IN (10, 15)");
    
    // Delete duplicate unions
    $db->exec("DELETE FROM family_unions WHERE id IN (10, 15)");
    
    // Delete duplicate parent nodes
    $db->exec("DELETE FROM family_nodes WHERE id IN (29, 30, 43, 44)");
    
    // Delete duplicate children that were generated without user_ids 
    $db->exec("DELETE FROM family_node_children WHERE child_id IN (38, 51, 39, 53, 60)");
    $db->exec("DELETE FROM family_nodes WHERE id IN (38, 51, 39, 53, 60)");
    
    echo "Consolidated duplicate parent unions into a single tree successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

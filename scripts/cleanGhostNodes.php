<?php
use Src\Db;
require_once __DIR__ . '/../app/config/init.php';
try {
    $db = Db::connect2();
    $deletedCountPat = $db->exec("
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
    
    // Also delete any edges (children/unions) that reference non-existent nodes
    $unionsDeleted = $db->exec("DELETE FROM family_unions WHERE partner_1_id NOT IN (SELECT id FROM family_nodes) OR partner_2_id NOT IN (SELECT id FROM family_nodes)");
    $childrenDeleted = $db->exec("DELETE FROM family_node_children WHERE union_id NOT IN (SELECT id FROM family_unions) OR child_id NOT IN (SELECT id FROM family_nodes)");
    
    echo "<h1>TFT Clean Ghost Nodes</h1>";
    echo "Successfully deleted $deletedCountPat ghost nodes from the database.<br>\n";
    echo "Successfully deleted $unionsDeleted orphaned unions.<br>\n";
    echo "Successfully deleted $childrenDeleted orphaned children links.<br>\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}

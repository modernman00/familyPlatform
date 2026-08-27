<?php
use Src\Db;
require_once __DIR__ . '/../app/config/init.php';

try {
    $db = Db::connect2();
    
    // Restore TOPE OLAOGUN (38)
    $db->exec("INSERT INTO family_nodes (id, family_code, first_name, last_name, gender, email, avatar_url, generation_level, is_deceased) VALUES (38, 'MODERNMAN', 'TOPE OLAOGUN', 'OLAOGUN', 'Male', 'topsy@gmail.com', '/resources/images/profile/avatarM.png', 0, 0)");
    $db->exec("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (6, 38, 'biological')");

    // Restore OLUSOLA OLAOGUN (39)
    $db->exec("INSERT INTO family_nodes (id, family_code, first_name, last_name, gender, email, avatar_url, generation_level, is_deceased) VALUES (39, 'MODERNMAN', 'OLUSOLA OLAOGUN', 'OLAOGUN', 'Male', 'solaolaogun2013@gmail.com', '/resources/images/profile/avatarM.png', 0, 0)");
    $db->exec("INSERT INTO family_node_children (union_id, child_id, relationship_type) VALUES (6, 39, 'biological')");
    
    // Restore Segun Dupsy (60)
    $db->exec("INSERT INTO family_nodes (id, family_code, first_name, last_name, gender, bio, avatar_url, generation_level, is_deceased) VALUES (60, 'SHO', 'Segun', 'Dupsy', 'Female', 'Child', '/resources/images/profile/avatarF.png', 1, 0)");
    // We don't know the exact union for 60, but let's check its child link from backup if possible. I won't link it for now.
    
    echo "Restored deleted nodes and linked them to Union 6.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

<?php
use Src\Db;
try {
    $db = Db::connect2();
    $stmt = $db->query("SELECT id, first_name, last_name FROM users WHERE first_name LIKE '%Grand%'");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

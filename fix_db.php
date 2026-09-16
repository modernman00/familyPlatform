<?php
require 'app/config/init.php';
try {
    $db = \Src\Db::connect2();
    $stmt = $db->query("SHOW COLUMNS FROM account");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}

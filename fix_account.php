<?php
require 'app/config/init.php';
try {
    $db = \Src\Db::connect2();
    $db->exec("ALTER TABLE account ADD COLUMN firstName varchar(255) DEFAULT NULL, ADD COLUMN lastName varchar(255) DEFAULT NULL;");
    echo "Success\n";
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
}

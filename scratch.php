<?php
require 'app/config/init.php';
$db = new \App\classes\Db();
$pdo = $db->connect();

$columns = [
    'email_notifications' => "VARCHAR(10) DEFAULT 'off'",
    'sms_notifications' => "VARCHAR(10) DEFAULT 'off'",
    'two_factor_auth' => "VARCHAR(10) DEFAULT 'off'",
    'profile_visibility' => "VARCHAR(30) DEFAULT 'Private'",
    'show_my_profile' => "VARCHAR(10) DEFAULT 'off'",
    'data_sharing' => "VARCHAR(10) DEFAULT 'off'",
    'occupation' => "VARCHAR(50) DEFAULT NULL"
];

$stmt = $pdo->query("DESCRIBE contact");
$existingCols = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');

foreach ($columns as $col => $def) {
    if (!in_array($col, $existingCols)) {
        try {
            $pdo->exec("ALTER TABLE contact ADD COLUMN $col $def");
            echo "Added column $col\n";
        } catch (\PDOException $e) {
            echo "Error adding $col: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Column $col already exists.\n";
    }
}
echo "Done.";

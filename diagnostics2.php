<?php
if (!isset($_GET['token']) || $_GET['token'] !== 'diagnose123') die('Unauthorized');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

$db = \Src\Db::connect2();
$stmt = $db->query("SELECT * FROM post LIMIT 10");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

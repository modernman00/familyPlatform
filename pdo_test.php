<?php
require_once __DIR__ . '/vendor/autoload.php';
// Need a quick way to initialize PDO. Let's just include init.php or whatever sets up the DB
require_once __DIR__ . '/app/config/init.php';

$id = 'cypressUser';
$famCodes = ['FAM1'];
$limit = 10;
$offset = 0;

try {
    $posts = \App\model\AllMembersData::getVisiblePosts($id, $famCodes, $limit, $offset);
    echo "SUCCESS, fetched " . count($posts) . " posts.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

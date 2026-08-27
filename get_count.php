<?php
require_once 'app/config/init.php';
$dataAll = new \App\model\AllMembersData();
$members = $dataAll->getAllMembers(1); // Wally Oguns has ID 1 locally usually
echo "Count: " . count($members) . "\n";

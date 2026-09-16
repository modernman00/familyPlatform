<?php
require 'app/config/init.php';
$stmt = \Src\Db::connect2()->query('DESCRIBE account');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

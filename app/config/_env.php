<?php
declare(strict_types=1);
// use Tracy\Debugger;
require_once __DIR__ . "/../../vendor/autoload.php";

//https://github.com/nette/tracy
// use Tracy\Debugger;
// Debugger::enable();

define('BASE_PATH', realpath(__DIR__ . '/../../'));
$dotEnv = \Dotenv\Dotenv::create(BASE_PATH);
$dotEnv->load();

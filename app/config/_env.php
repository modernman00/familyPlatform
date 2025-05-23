<?php

declare(strict_types=1);

// @sonar-disable-next-line php:S4833
require_once __DIR__ . "/../../vendor/autoload.php";

//https://github.com/nette/tracy
// use Tracy\Debugger;


define('BASE_PATH', realpath(__DIR__ . '/../../'));
$dotEnv = \Dotenv\Dotenv::createUnsafeImmutable(BASE_PATH);
$dotEnv->load();

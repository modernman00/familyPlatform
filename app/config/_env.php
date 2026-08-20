<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Src\LoggerFactory;

// @sonar-disable-next-line php:S4833
require_once __DIR__ . "/../../vendor/autoload.php";

//https://github.com/nette/tracy
// use Tracy\Debugger;


$basePath = realpath(__DIR__ . '/../../');
define('BASE_PATH', $basePath !== false ? $basePath : __DIR__ . '/../../');
$dotEnv = Dotenv::createUnsafeImmutable(BASE_PATH);
$dotEnv->load();

LoggerFactory::createWithMailer();

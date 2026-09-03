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

// phpdotenv leaves a ${VAR} reference to a never-defined variable as literal
// text. That literal then silently poisons whatever reads it — e.g. an
// unresolved ADMIN_EMAIL flows into PHPMailer::setFrom() and throws
// "Invalid address". Blank any still-unresolved reference here, once, so no
// downstream consumer (mailer constants, MAILER_DSN, the error-notification
// logger below) ever sees it. Real fix is still to correct the .env on the box.
foreach ($_ENV as $envKey => $envValue) {
    if (is_string($envValue) && preg_match('/\$\{[A-Za-z_][A-Za-z0-9_]*\}/', $envValue)) {
        error_log("[env] unresolved reference in {$envKey}, blanked: {$envValue}");
        $_ENV[$envKey] = '';
        $_SERVER[$envKey] = '';
        putenv($envKey . '=');
    }
}

$logger = LoggerFactory::createWithMailer();
$handler = new \Monolog\ErrorHandler($logger);
$handler->registerExceptionHandler();
$handler->registerFatalHandler();

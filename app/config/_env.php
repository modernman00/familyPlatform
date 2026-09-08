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

// APP_ENV_DIR is a deployment-set server var (vhost / .htaccess SetEnv), never a
// request value. Still, only honour it when it is an absolute path with no
// traversal segments and points at a readable .env, so a misconfigured
// environment can never redirect the config load somewhere unexpected.
$envDirRaw = getenv('APP_ENV_DIR') ?: ($_SERVER['APP_ENV_DIR'] ?? '');
$envDir = '';
if ($envDirRaw !== '' && $envDirRaw[0] === '/' && !str_contains($envDirRaw, '..')) {
    $candidate = rtrim($envDirRaw, '/');
    // nosemgrep: php.lang.security.injection.tainted-filename.tainted-filename -- APP_ENV_DIR is a server-set environment variable (Apache SetEnv / vhost), not a request input; it is additionally constrained above to an absolute, traversal-free path.
    if (is_readable($candidate . '/.env')) {
        $envDir = $candidate;
    }
}
if ($envDir === '') {
    if (is_readable('/home/bestiias/private/.env')) {
        $envDir = '/home/bestiias/private';
    } else {
        $envDir = BASE_PATH;
    }
}

$dotEnv = Dotenv::createUnsafeImmutable($envDir);
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

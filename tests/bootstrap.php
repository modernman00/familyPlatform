<?php

declare(strict_types=1);

// Reuses the app's real env bootstrap (loads .env, defines BASE_PATH, wires the
// logger) so tests run against the exact same config the app uses — same DB,
// same mailer. It has no session_start()/header() calls, so it's CLI-safe.
require_once __DIR__ . '/../app/config/_env.php';
require_once __DIR__ . '/Support/shims.php';

// Fast-fail outbound SMTP during testing to prevent 75s TCP timeouts to external hosts
$_ENV['SMTP_HOST'] = '127.0.0.1';
$_ENV['MAIL_HOST'] = '127.0.0.1';
$_ENV['SMTP_PORT'] = 2525;
$_SERVER['SMTP_HOST'] = '127.0.0.1';
$_SERVER['MAIL_HOST'] = '127.0.0.1';
$_SERVER['SMTP_PORT'] = 2525;

date_default_timezone_set('Europe/London');

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$_SESSION = [];


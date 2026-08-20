<?php

declare(strict_types=1);

// Reuses the app's real env bootstrap (loads .env, defines BASE_PATH, wires the
// logger) so tests run against the exact same config the app uses — same DB,
// same mailer. It has no session_start()/header() calls, so it's CLI-safe.
require_once __DIR__ . '/../app/config/_env.php';
require_once __DIR__ . '/Support/shims.php';

$_SESSION = [];

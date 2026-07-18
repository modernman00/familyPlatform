<?php

/**
 * Deployment Audit Logger
 * ISO 27001 (A.12.4)
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$commit = $argv[1] ?? 'unknown';
$user = $argv[2] ?? 'unknown';

\Src\AuditLogger::log('deployment', [
    'message' => "Deployment executed by {$user} at commit {$commit}",
    'status'  => 'success',
    'commit'  => $commit,
    'user'    => $user
]);

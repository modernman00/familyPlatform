<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\controller\members\PostMessage;

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE account (id TEXT PRIMARY KEY, email_unsubscribed INTEGER DEFAULT 0)');
$pdo->exec("CREATE TABLE contact (id TEXT PRIMARY KEY, email_notifications TEXT DEFAULT 'on', post_email_notifications TEXT DEFAULT 'off')");

$stmtAcc = $pdo->prepare('INSERT INTO account (id, email_unsubscribed) VALUES (?, ?)');
$stmtCon = $pdo->prepare('INSERT INTO contact (id, email_notifications, post_email_notifications) VALUES (?, ?, ?)');

// u1: default (post_email_notifications = 'off') -> MUST BE EXCLUDED
$stmtAcc->execute(['u1', 0]);
$stmtCon->execute(['u1', 'on', 'off']);

// u2: opted in (post_email_notifications = 'on', email_notifications = 'on') -> MUST BE INCLUDED
$stmtAcc->execute(['u2', 0]);
$stmtCon->execute(['u2', 'on', 'on']);

// u3: opted in to post emails, but global email_notifications = 'off' -> MUST BE EXCLUDED
$stmtAcc->execute(['u3', 0]);
$stmtCon->execute(['u3', 'off', 'on']);

// u4: opted in, but globally unsubscribed (email_unsubscribed = 1) -> MUST BE EXCLUDED
$stmtAcc->execute(['u4', 1]);
$stmtCon->execute(['u4', 'on', 'on']);

// u5: no contact row at all -> defaults to 'off' -> MUST BE EXCLUDED
$stmtAcc->execute(['u5', 0]);

$input = [
    ['id' => 'u1', 'email' => 'u1@example.com'],
    ['id' => 'u2', 'email' => 'u2@example.com'],
    ['id' => 'u3', 'email' => 'u3@example.com'],
    ['id' => 'u4', 'email' => 'u4@example.com'],
    ['id' => 'u5', 'email' => 'u5@example.com'],
];

$filtered = PostMessage::filterOptedInPostEmailRecipients($input, $pdo);
$ids = array_column($filtered, 'id');

if ($ids !== ['u2']) {
    fwrite(STDERR, "FAIL: Expected ['u2'], got " . json_encode($ids) . PHP_EOL);
    exit(1);
}

// Fail-safe test: broken PDO must return [] instead of emailing everyone
$badPdo = new PDO('sqlite::memory:');
$failSafe = PostMessage::filterOptedInPostEmailRecipients($input, $badPdo);
if ($failSafe !== []) {
    fwrite(STDERR, "FAIL: Expected [] on DB failure, got " . json_encode($failSafe) . PHP_EOL);
    exit(1);
}

echo "PASS: PostMessage::filterOptedInPostEmailRecipients enforces opt-in, global email toggle, unsubscribe, and fail-safe.\n";

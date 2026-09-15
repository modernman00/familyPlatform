#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/_env.php';

use App\services\DataErasureService;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Error: This script must be run from the command line.\n");
    exit(1);
}

$options = getopt('', ['account-id:', 'reason:', 'help']);

if (isset($options['help']) || empty($options['account-id'])) {
    echo "FamilyPlatform GDPR Article 17 Data Erasure CLI Tool\n";
    echo "Usage:\n";
    echo "  php bin/erase-account.php --account-id=<ID> [--reason=\"<REASON>\"]\n\n";
    echo "Options:\n";
    echo "  --account-id  Target member account ID to erase and anonymise\n";
    echo "  --reason      Optional administrative reason for audit trail\n";
    echo "  --help        Display this help message\n";
    exit(0);
}

$rawAccountId = $options['account-id'];
$accountId = is_string($rawAccountId) ? $rawAccountId : (string) ($rawAccountId[0] ?? '');

$rawReason = $options['reason'] ?? 'CLI Admin Erasure';
$reason = is_string($rawReason) ? $rawReason : (is_array($rawReason) ? (string) ($rawReason[0] ?? 'CLI Admin Erasure') : 'CLI Admin Erasure');

echo "--------------------------------------------------------\n";
echo "       GDPR Art. 17 Account Erasure Execution          \n";
echo "--------------------------------------------------------\n";
echo "Target Account ID : {$accountId}\n";
echo "Reason            : {$reason}\n";
echo "Started at        : " . gmdate('c') . "\n";
echo "--------------------------------------------------------\n";

$service = new DataErasureService($accountId);
$result = $service->erase('CLI:' . $reason);

if ($result['success']) {
    echo "SUCCESS: " . $result['message'] . "\n";
    echo "Erased Tables Count: " . count($result['erased_tables']) . "\n";
    echo "Erased Tables List : " . implode(', ', $result['erased_tables']) . "\n";
    if (!empty($result['failed_tables'])) {
        echo "WARNING - Non-Critical Skipped Tables: " . implode(', ', $result['failed_tables']) . "\n";
    }
    exit(0);
} else {
    fwrite(STDERR, "FAILURE: " . $result['message'] . "\n");
    if (!empty($result['failed_tables'])) {
        fwrite(STDERR, "Failed Tables: " . implode(', ', $result['failed_tables']) . "\n");
    }
    if (!empty($result['errors'])) {
        fwrite(STDERR, "Errors:\n");
        foreach ($result['errors'] as $tbl => $err) {
            fwrite(STDERR, "  - {$tbl}: {$err}\n");
        }
    }
    exit(1);
}

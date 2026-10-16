<?php

declare(strict_types=1);

/**
 * CLI Worker: Automated Onboarding & Lifecycle Engagement Runner
 *
 * Usage:
 *   php scripts/runOnboardingFollowups.php
 *   php scripts/runOnboardingFollowups.php --dry-run
 *   php scripts/runOnboardingFollowups.php --force-user=USR_123456
 *
 * Designed to run on an hourly or daily cron:
 *   0 * * * * php /Users/waleolaogun/Sites/familyPlatform/scripts/runOnboardingFollowups.php >> /var/log/onboarding_followups.log 2>&1
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "This script can only be run from the command line.\n";
    exit(1);
}

require_once __DIR__ . '/../app/config/init.php';

use App\services\OnboardingFollowupService;

$options = getopt('', ['dry-run', 'force-user:']);
$isDryRun = isset($options['dry-run']);
$forceUserId = isset($options['force-user']) && is_string($options['force-user']) ? trim($options['force-user']) : null;

$mode = $isDryRun ? 'DRY-RUN (Simulation Mode)' : 'LIVE EXECUTION';
$timestamp = date('Y-m-d H:i:s');

echo "====================================================================\n";
echo "  FamilyPlatform Automated Onboarding Lifecycle Engine\n";
echo "  Timestamp: {$timestamp} | Mode: {$mode}\n";
if ($forceUserId !== null) {
    echo "  Target User: {$forceUserId}\n";
}
echo "====================================================================\n";

try {
    $results = OnboardingFollowupService::runAllTouchpoints($isDryRun, $forceUserId);

    echo "[+] Day 1 Lineage Kickstart:  {$results['day1_processed']} users processed\n";
    echo "[+] Day 3 Living Voice Audio: {$results['day3_processed']} users processed\n";
    echo "[+] Day 7 Referral Flywheel:  {$results['day7_processed']} users processed\n";
    echo "====================================================================\n";
    echo "[SUCCESS] Lifecycle cohort run completed successfully.\n";
    exit(0);
} catch (\Throwable $e) {
    echo "====================================================================\n";
    echo "[FATAL ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    echo "====================================================================\n";
    exit(1);
}

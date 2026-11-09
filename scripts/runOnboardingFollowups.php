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

    echo "[+] Day 1 Lineage Tree Kickstart:   {$results['day1_processed']} users processed\n";
    echo "[+] Day 2 First Post & 3D Stages:   {$results['day2_processed']} users processed\n";
    echo "[+] Day 3 Living Voice Audio:       {$results['day3_processed']} users processed\n";
    echo "[+] Day 4 Living Avatar Studio:     {$results['day4_processed']} users processed\n";
    echo "[+] Day 7 Referral & Kin Invites:   {$results['day7_processed']} users processed\n";
    echo "[+] Day 10 Living 3D Emojis:        {$results['day10_processed']} users processed\n";
    echo "[+] Day 14 Then & Now Studio:       {$results['day14_then_now_processed']} users processed\n";
    echo "[+] Day 18 Cinema Video Sharing:    {$results['day18_processed']} users processed\n";
    echo "[+] Day 21 Family Reels Sanctuary:  {$results['day21_reels_processed']} users processed\n";
    echo "[+] Day 25 Family Journal & AI:     {$results['day25_processed']} users processed\n";
    echo "[+] Day 30 Encrypted Vault/Gallery: {$results['day30_processed']} users processed\n";
    echo "[+] Day 35 WhatsApp Flyer Studio:   {$results['day35_flyer_processed']} users processed\n";
    echo "[+] Day 40 Family Roundtable Hub:   {$results['day40_processed']} users processed\n";
    echo "[+] Day 45 Event Studio & RSVP:     {$results['day45_processed']} users processed\n";
    echo "[+] Day 52 Sunday Trivia & Games:   {$results['day52_processed']} users processed\n";
    echo "[+] Day 60 Dynasty Masterpiece:     {$results['day60_processed']} users processed\n";
    echo "--------------------------------------------------------------------\n";
    echo "[+] Inactivity Tier 1 (Ghost):      {$results['tier1_ghost_processed']} users processed\n";
    echo "[+] Inactivity Tier 2 (Delegate):   {$results['tier2_delegate_processed']} users processed\n";
    echo "[+] Inactivity Tier 3 (Banter):     {$results['tier3_banter_processed']} users processed\n";
    echo "[+] Inactivity Tier 4 (Deep):       {$results['tier4_deep_processed']} users processed\n";
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

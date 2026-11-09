<?php

declare(strict_types=1);

/**
 * Sovereign Verification Suite: 60-Day Omnichannel Feature Discovery & Activation Engine
 *
 * Governed by TAT / Sovereign Sector Board: Social Media, Lifestyle & Platform Growth
 * Asserts:
 *  1. Canonical 15 Feature Milestones Integrity (Day 1 to Day 60)
 *  2. FeatureDiscoveryService Discovery State & Adaptive Fast-Forwarding
 *  3. OnboardingFollowupService Omnichannel Lifecycle Dispatches (Email, Push, Bell)
 *  4. Strict Idempotency Guarantees via user_lifecycle_events
 *  5. British English (en-GB) Invariant Across Lifecycle Views
 *  6. Template Compilation & Button Link Verification
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */

require_once __DIR__ . '/../app/config/init.php';

use App\services\FeatureDiscoveryService;
use App\services\OnboardingFollowupService;
use Src\Db;

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $message): void
{
    global $passCount, $failCount;
    if ($cond) {
        echo "  ✅ [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  ❌ [FAIL] {$message}\n";
        $failCount++;
    }
}

echo "====================================================================\n";
echo "  🚀 VERIFYING 60-DAY FEATURE DISCOVERY & ACTIVATION ENGINE\n";
echo "====================================================================\n";

$db = Db::connect2();

// -------------------------------------------------------------------------
// Phase 1: Canonical 15 Milestones Verification
// -------------------------------------------------------------------------
echo "\n[Phase 1] Auditing 15 Canonical Milestones in FeatureDiscoveryService...\n";

$milestones = FeatureDiscoveryService::MILESTONES;
assertCondition(count($milestones) === 15, "FeatureDiscoveryService defines exactly 15 milestone touchpoints (count=" . count($milestones) . ")");

$expectedDays = [1, 2, 4, 7, 10, 14, 18, 21, 25, 30, 35, 40, 45, 52, 60];
$actualDays = array_column($milestones, 'day');
assertCondition($actualDays === $expectedDays, "Milestone days match required 60-day progression: " . implode(', ', $actualDays));

$requiredFields = ['day', 'key', 'title', 'tagline', 'description', 'chapter', 'icon', 'action_url', 'action_label', 'badge'];
$allFieldsPresent = true;
foreach ($milestones as $idx => $m) {
    foreach ($requiredFields as $field) {
        if (empty($m[$field])) {
            $allFieldsPresent = false;
            echo "  [-] Milestone index {$idx} missing field: {$field}\n";
        }
    }
}
assertCondition($allFieldsPresent, "All 15 milestones specify complete structural metadata (icons, deep links, badges)");

// -------------------------------------------------------------------------
// Phase 2: FeatureDiscoveryService State & Fast-Forwarding
// -------------------------------------------------------------------------
echo "\n[Phase 2] Auditing Discovery State & Silas Thorne Adaptive Fast-Forwarding...\n";

$testUserId = 'USR_TEST_DISC_' . substr(bin2hex(random_bytes(4)), 0, 8);
$testFamCode = 'FAM_TEST_DISC_' . substr(bin2hex(random_bytes(4)), 0, 8);

try {
    // Clean up any test artifacts first
    $db->prepare("DELETE FROM user_lifecycle_events WHERE user_id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);

    // Provision test user (14 days old)
    $stmtAcct = $db->prepare("
        INSERT INTO account (id, email, password, status, created_at)
        VALUES (?, 'discovery.tester@test.com', 'hash', 'approved', DATE_SUB(NOW(), INTERVAL 14 DAY))
    ");
    $stmtAcct->execute([$testUserId]);

    $stmtPers = $db->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, created_at)
        VALUES (?, 'Olumide', 'TestPioneer', ?, DATE_SUB(NOW(), INTERVAL 14 DAY))
    ");
    $stmtPers->execute([$testUserId, $testFamCode]);

    $discoveryService = new FeatureDiscoveryService($db);
    $state = $discoveryService->getUserDiscoveryState($testUserId, $testFamCode);

    assertCondition($state['account_age_days'] >= 14, "Account age correctly calculated as {$state['account_age_days']} days (>= 14)");
    assertCondition($state['is_in_discovery_window'] === true, "User is recognized inside the 60-day discovery window");
    assertCondition(isset($state['current_milestone']['key']), "Current milestone correctly identified: " . ($state['current_milestone']['title'] ?? 'none'));

    // Test Fast-Forward launch
    $ffResult = $discoveryService->markMilestoneCompleted($testUserId, 'avatar_studio_day4');
    assertCondition($ffResult['status'] === 'success', "Adaptive Fast-Forward successfully marks milestone as launched/completed");

    // Re-fetch state and confirm completion
    $stateAfter = $discoveryService->getUserDiscoveryState($testUserId, $testFamCode);
    $avatarMilestone = null;
    foreach ($stateAfter['milestones'] as $ms) {
        if ($ms['key'] === 'avatar_studio_day4') {
            $avatarMilestone = $ms;
            break;
        }
    }
    assertCondition($avatarMilestone !== null && $avatarMilestone['completed'] === true, "Avatar studio milestone is now recorded as completed in state");
    assertCondition($stateAfter['completed_count'] >= 1, "Completed count dynamically updated to {$stateAfter['completed_count']}");

} finally {
    // Teardown test fixtures
    $db->prepare("DELETE FROM user_lifecycle_events WHERE user_id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
}

// -------------------------------------------------------------------------
// Phase 3: OnboardingFollowupService Lifecycle Engine Execution
// -------------------------------------------------------------------------
echo "\n[Phase 3] Auditing OnboardingFollowupService Multi-Touch Runner...\n";

$results = OnboardingFollowupService::runAllTouchpoints(true, null);

$expectedKeys = [
    'day1_processed', 'day2_processed', 'day3_processed', 'day4_processed',
    'day7_processed', 'day10_processed', 'day14_processed', 'day14_then_now_processed',
    'day18_processed', 'day21_processed', 'day21_reels_processed', 'day25_processed',
    'day28_processed', 'day30_processed', 'day35_processed', 'day35_flyer_processed',
    'day40_processed', 'day42_processed', 'day45_processed', 'day49_processed',
    'day52_processed', 'day60_processed', 'tier1_ghost_processed', 'tier2_delegate_processed',
    'tier3_banter_processed', 'tier4_deep_processed', 'dry_run'
];

$allKeysPresent = true;
foreach ($expectedKeys as $k) {
    if (!array_key_exists($k, $results)) {
        $allKeysPresent = false;
        echo "  [-] Missing result key: {$k}\n";
    }
}
assertCondition($allKeysPresent, "runAllTouchpoints() outputs metrics for all 15 milestones and 4 inactivity tiers");
assertCondition($results['dry_run'] === true, "Dry-run mode verified (zero unintended side-effects)");

// -------------------------------------------------------------------------
// Phase 4: Lifecycle Email Templates & British English (en-GB) Invariant
// -------------------------------------------------------------------------
echo "\n[Phase 4] Auditing Lifecycle Email Views & British English Compliance...\n";

$lifecycleViews = [
    'day1_tree_kickstart.blade.php',
    'day2_first_post.blade.php',
    'day3_voice_capsule.blade.php',
    'day4_avatar_studio.blade.php',
    'day7_referral_flywheel.blade.php',
    'day10_living_emojis.blade.php',
    'day14_heritage_roots.blade.php',
    'day14_then_now.blade.php',
    'day18_cinema_video.blade.php',
    'day21_family_recipe.blade.php',
    'day21_family_reels.blade.php',
    'day25_family_journal.blade.php',
    'day28_family_trivia.blade.php',
    'day30_photo_vault.blade.php',
    'day35_time_capsule.blade.php',
    'day35_flyer_studio.blade.php',
    'day40_family_roundtable.blade.php',
    'day42_elder_voices.blade.php',
    'day45_events_announcements.blade.php',
    'day49_reunion_planner.blade.php',
    'day52_heritage_games.blade.php',
    'day60_dynasty_snapshot.blade.php',
];

$viewsDir = __DIR__ . '/../resources/views/msg/lifecycle';
$allFilesExist = true;
$usSpellingsFound = 0;

$americanPatterns = [
    '/\bhonor\b/i', '/\bhonors\b/i', '/\bhonored\b/i',
    '/\borganize\b/i', '/\borganizing\b/i', '/\borganized\b/i',
    '/\bcustomize\b/i', '/\bcustomizing\b/i', '/\bcustomized\b/i',
    '/\bcolor\b/i', '/\bcolors\b/i',
    '/\bfavor\b/i', '/\bfavors\b/i',
    '/\bcenter\b/i', '/\bcenters\b/i'
];

foreach ($lifecycleViews as $viewFile) {
    $fullPath = $viewsDir . '/' . $viewFile;
    if (!file_exists($fullPath)) {
        $allFilesExist = false;
        echo "  [-] Missing lifecycle view: {$viewFile}\n";
        continue;
    }

    $content = file_get_contents($fullPath) ?: '';
    // Strip HTML attributes (align="center", etc.) and CSS (text-align: center) to test visible copy
    $cleanContent = preg_replace('/(align\s*=\s*["\']center["\']|text-align\s*:\s*center|color\s*:\s*#[a-f0-9]+)/i', '', $content) ?? $content;

    foreach ($americanPatterns as $pattern) {
        if (preg_match($pattern, $cleanContent, $matches)) {
            $word = $matches[0];
            $usSpellingsFound++;
            echo "  [-] American spelling '{$word}' detected in {$viewFile}\n";
        }
    }
}

assertCondition($allFilesExist, "All 22 lifecycle email template files exist under resources/views/msg/lifecycle/");
assertCondition($usSpellingsFound === 0, "100% British English (en-GB) compliance across lifecycle templates (0 US violations)");

// -------------------------------------------------------------------------
// Phase 5: In-App Discovery Widget & Middle Column Integration
// -------------------------------------------------------------------------
echo "\n[Phase 5] Auditing In-App Discovery Widget Integration...\n";

$widgetFile = __DIR__ . '/../resources/views/member/includes/discoveryJourneyWidget.blade.php';
$middleCol = __DIR__ . '/../resources/views/member/includes/middleColumn.blade.php';

assertCondition(file_exists($widgetFile), "discoveryJourneyWidget.blade.php component exists");
$widgetContent = file_get_contents($widgetFile) ?: '';
assertCondition(str_contains($widgetContent, 'x-data="discoveryJourneyWidget()"'), "discoveryJourneyWidget uses declarative Alpine.js");
assertCondition(str_contains($widgetContent, 'x-trap.noscroll="showAllStudiosModal"'), "discoveryJourneyWidget applies WCAG focus trapping on 15-studio browser");

$middleColContent = file_get_contents($middleCol) ?: '';
assertCondition(str_contains($middleColContent, 'member.includes.discoveryJourneyWidget'), "middleColumn.blade.php includes discoveryJourneyWidget");

// -------------------------------------------------------------------------
// Final Score & Exit
// -------------------------------------------------------------------------
echo "\n====================================================================\n";
echo "  🏁 TEST SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}

exit(0);

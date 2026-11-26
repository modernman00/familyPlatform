<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\services\WeeklyActivityVideoCuratorService;
use Src\Db;

echo "=== STARTING WEEKLY REEL CURATION & APPROVAL TEST SUITE ===\n";

$db = Db::connect2();

$testUserIdA = 'test_user_weekly_' . time();
$testUserIdB = 'test_user_adversary_' . time();
$testFamCode = 'FAM_WEEKLY_' . substr(md5((string)time()), 0, 6);

// 1. Setup Test Post with an Image in family circle
$stmtPost = $db->prepare(
    "INSERT INTO post (id, fullName, profileImg, postMessage, postFamCode, post_time, post_status, post_img0, date_created)
     VALUES (?, 'Test Curated User', '', 'Cherishing a wonderful weekend family reunion!', ?, ?, 'published', '/public/img/posts/sample_reunion.jpg', NOW())"
);
$stmtPost->execute([$testUserIdA, $testFamCode, time()]);
$createdPostNo = (int)$db->lastInsertId();
echo "✓ Fixture post #{$createdPostNo} created for {$testFamCode}\n";

// 2. Test WeeklyActivityVideoCuratorService::curateWeeklyActivity
$reel = WeeklyActivityVideoCuratorService::curateWeeklyActivity($testUserIdA, $testFamCode, true);
assert(!empty($reel['id']), "Expected reel ID to be set");
assert($reel['status'] === 'draft_pending_approval', "Expected status draft_pending_approval");
assert($reel['duration_seconds'] >= 30 && $reel['duration_seconds'] <= 60, "Duration must be between 30 and 60 seconds");
assert(!empty($reel['storyboard']), "Expected storyboard array to be populated");
assert(!empty($reel['audio_url']), "Expected audio_url to be assigned");
assert(!empty($reel['storyboard']['fun_facts']), "Expected fun_facts payload in storyboard");
assert(($reel['storyboard']['fun_facts']['total_posts'] ?? 0) >= 1, "Expected total_posts >= 1");
assert(($reel['storyboard']['aspect_ratios'] ?? []) === ['16:9', '9:16'], "Expected both 16:9 and 9:16 aspect ratios");
assert(!empty($reel['storyboard']['explainer_manifests']['16:9']), "Expected 16:9 SovereignVideoRenderer manifest");
assert(!empty($reel['storyboard']['explainer_manifests']['9:16']), "Expected 9:16 SovereignVideoRenderer manifest");
echo "✓ Curate weekly reel generated reel #{$reel['id']} with Fun Facts + 16:9 & 9:16 manifests (Duration: {$reel['duration_seconds']}s)\n";

// 2b. Test pure computeWeeklyFunFacts on empty posts (quiet-week fallback)
$emptyFacts = WeeklyActivityVideoCuratorService::computeWeeklyFunFacts([], [], new DateTimeImmutable('-7 days'), new DateTimeImmutable(), true);
assert($emptyFacts['total_posts'] === 0, "Expected 0 posts on empty week");
assert($emptyFacts['golden_day'] === 'Sunday', "Expected Sunday fallback golden_day");
echo "✓ Empty-week Fun Facts fallback verified\n";

// 3. Test getPendingReel
$pending = WeeklyActivityVideoCuratorService::getPendingReel($testUserIdA, $testFamCode);
assert($pending !== null, "Expected getPendingReel to find draft");
assert($pending['id'] === $reel['id'], "Expected pending reel ID match");
echo "✓ getPendingReel retrieved active draft #{$pending['id']}\n";

// 4. Adversarial Red Team Assault (Dr. Viktor Kovács & Marcus Gate): IDOR / BOLA Prevention
$adversaryAttempt = WeeklyActivityVideoCuratorService::approveAndPostReel(
    (int)$reel['id'],
    $testUserIdB, // Attacker trying to approve User A's reel
    $testFamCode,
    'Adversary User'
);
assert($adversaryAttempt['success'] === false, "Adversary should not be able to approve another user's reel");
echo "✓ Adversarial IDOR assault neutralized: User B denied approval on User A's reel\n";

// 5. Test Happy Path Approval & Post to Feed
$approvalResult = WeeklyActivityVideoCuratorService::approveAndPostReel(
    (int)$reel['id'],
    $testUserIdA,
    $testFamCode,
    'Test Curated User'
);
assert($approvalResult['success'] === true, "Expected approval to succeed");
assert(!empty($approvalResult['post_no']), "Expected new feed post_no");
echo "✓ Approved reel posted to feed as post #{$approvalResult['post_no']}\n";

// 6. Verify Reel Status Updated to 'approved'
$stmtVerify = $db->prepare("SELECT status, post_no FROM weekly_activity_reels WHERE id = ?");
$stmtVerify->execute([$reel['id']]);
$updatedReel = $stmtVerify->fetch(PDO::FETCH_ASSOC);
assert($updatedReel['status'] === 'approved', "Expected status 'approved'");
assert((int)$updatedReel['post_no'] === $approvalResult['post_no'], "Expected post_no match");
echo "✓ weekly_activity_reels record verified in database with status 'approved'\n";

// 7. Verify no pending reel remains
$pendingAfter = WeeklyActivityVideoCuratorService::getPendingReel($testUserIdA, $testFamCode);
assert($pendingAfter === null, "Expected no pending reel after approval");
echo "✓ No pending reel remaining after approval (clean feed state)\n";

// 8. Test Dismissal Workflow
$secondReel = WeeklyActivityVideoCuratorService::curateWeeklyActivity($testUserIdA, $testFamCode, true);
$dismissResult = WeeklyActivityVideoCuratorService::dismissReel((int)$secondReel['id'], $testUserIdA, $testFamCode);
assert($dismissResult['success'] === true, "Expected dismiss to succeed");

$stmtVerifyDismiss = $db->prepare("SELECT status FROM weekly_activity_reels WHERE id = ?");
$stmtVerifyDismiss->execute([$secondReel['id']]);
$dismissedRow = $stmtVerifyDismiss->fetch(PDO::FETCH_ASSOC);
assert($dismissedRow['status'] === 'discarded', "Expected status 'discarded'");
echo "✓ Dismissal workflow verified (status 'discarded')\n";

// 9. Test getOrCurateWeeklyReel respects recent dismissal within 7 days and auto-curates for fresh user
$afterDismissAuto = WeeklyActivityVideoCuratorService::getOrCurateWeeklyReel($testUserIdA, $testFamCode);
assert($afterDismissAuto === null, "Expected getOrCurateWeeklyReel not to re-create after recent dismissal");
$db->prepare("DELETE FROM weekly_activity_reels WHERE user_id = ?")->execute([$testUserIdA]);
$freshAuto = WeeklyActivityVideoCuratorService::getOrCurateWeeklyReel($testUserIdA, $testFamCode);
assert(is_array($freshAuto) && !empty($freshAuto['id']), "Expected getOrCurateWeeklyReel to auto-curate when no recent reel exists");
echo "✓ getOrCurateWeeklyReel auto-curation and 7-day dismissal idempotency verified\n";

// 10. Test ensureAutoPublishedWeeklyReel auto-approves existing draft and deduplicates per family
$autoPubResult = WeeklyActivityVideoCuratorService::ensureAutoPublishedWeeklyReel(
    $testUserIdA,
    $testFamCode,
    'Test Curated User'
);
assert(is_array($autoPubResult) && !empty($autoPubResult['success']), "Expected ensureAutoPublishedWeeklyReel to auto-publish existing draft");
assert(!empty($autoPubResult['post_no']), "Expected post_no from auto-publish");
assert(WeeklyActivityVideoCuratorService::getPendingReel($testUserIdA, $testFamCode) === null, "Expected no pending draft after auto-publish");

// Calling again for another family member in the same circle must be a no-op (no duplicate post)
$dedupResult = WeeklyActivityVideoCuratorService::ensureAutoPublishedWeeklyReel(
    $testUserIdB,
    $testFamCode,
    'Another Family Member'
);
assert($dedupResult === null, "Expected family-level deduplication to prevent duplicate weekly posts");
echo "✓ ensureAutoPublishedWeeklyReel auto-publish and family-level deduplication verified\n";

// Cleanup fixtures
$db->prepare("DELETE FROM weekly_activity_reels WHERE user_id IN (?, ?)")->execute([$testUserIdA, $testUserIdB]);
$db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$testFamCode]);
$db->prepare("DELETE FROM family_reels WHERE famCode = ?")->execute([$testFamCode]);
echo "✓ Test fixtures cleaned up\n";

echo "=== ALL WEEKLY REEL CURATION TESTS PASSED (100% SUCCESS) ===\n";

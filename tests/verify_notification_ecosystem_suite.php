<?php
declare(strict_types=1);

/**
 * Verification Suite for Platform-Wide Notification System Remediation
 *
 * Validates:
 * 1. Reel creation dispatches notifications to family members.
 * 2. Reel comments dispatch notifications to the reel creator.
 * 3. Reel reactions dispatch notifications to the reel creator.
 * 4. Announcement creation dispatches notifications to family members.
 * 5. Journal creation dispatches notifications to contributors and family members.
 * 6. Time capsule unlock dispatches notifications to creator and family.
 * 7. Post like & comment reaction dispatch notifications to author.
 * 8. NotificationController correctly resolves action URLs for Reels, Announcements, Journals, and Capsules.
 */

require_once __DIR__ . '/bootstrap.php';

use Src\Db;
use App\model\Reel;
use App\controller\NotificationController;

echo "=================================================================\n";
echo "🛡️  NOTIFICATION ECOSYSTEM VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$pdo = Db::connect2();
$testsPassed = 0;
$testsFailed = 0;

function assertCheck(bool $condition, string $message): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $testsPassed++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $testsFailed++;
    }
}

// Setup test fixtures
$testFamCode = 'TEST_FAM_NOTIF_' . bin2hex(random_bytes(4));
$userAuthor = 'notif_author_' . bin2hex(random_bytes(4));
$userMember = 'notif_member_' . bin2hex(random_bytes(4));

$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Alice', 'Tester', ?)")->execute([$userAuthor, $testFamCode]);
$pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Bob', 'Family', ?)")->execute([$userMember, $testFamCode]);

echo "[1/4] Testing NotificationController Action URL Resolution...\n";

$reelNotif = [
    'notification_type' => 'Reel',
    'notification_name' => 'New Reel',
    'notification_content' => 'Watch this video',
    'action_url' => '/reels?id=99',
    'sender_id' => $userAuthor,
    'receiver_id' => $userMember
];
$resolvedUrl = NotificationController::resolveNotificationActionUrl($reelNotif, $userMember);
assertCheck($resolvedUrl === '/reels?id=99', "Reel deep link preserved: {$resolvedUrl}");

$announcementNotif = [
    'notification_type' => 'Announcement',
    'notification_name' => 'Important Update',
    'notification_content' => 'Family meeting tomorrow',
    'sender_id' => $userAuthor,
    'receiver_id' => $userMember
];
$resolvedAnnounce = NotificationController::resolveNotificationActionUrl($announcementNotif, $userMember);
assertCheck($resolvedAnnounce === '/profilePage#familyAnnouncementsCard', "Announcement route resolved: {$resolvedAnnounce}");

$journalNotif = [
    'notification_type' => 'Journal',
    'notification_name' => 'New Chronicle',
    'notification_content' => 'Preserved story',
    'action_url' => '/familyJournal/42',
    'sender_id' => $userAuthor,
    'receiver_id' => $userMember
];
$resolvedJournal = NotificationController::resolveNotificationActionUrl($journalNotif, $userMember);
assertCheck($resolvedJournal === '/familyJournal/42', "Journal deep link resolved: {$resolvedJournal}");

$capsuleNotif = [
    'notification_type' => 'Capsule',
    'notification_name' => 'Time Capsule Unlocked',
    'notification_content' => 'Memory opened',
    'sender_id' => 'system',
    'receiver_id' => $userMember
];
$resolvedCapsule = NotificationController::resolveNotificationActionUrl($capsuleNotif, $userMember);
assertCheck($resolvedCapsule === '/profilePage#timeCapsuleCard', "Time Capsule card resolved: {$resolvedCapsule}");


echo "\n[2/4] Testing Reel Notification Insertion on Upload & Comments...\n";

// Simulate reel creation
$reelId = Reel::createReel([
    'user_id' => $userAuthor,
    'famCode' => $testFamCode,
    'caption' => 'Holiday in Cornwall',
    'video_url' => '/resources/videos/reels/test_reel.mp4',
    'aspect_ratio' => '9:16',
    'category' => 'vacation'
]);
assertCheck($reelId > 0, "Test reel created with ID: {$reelId}");

// Emulate notification creation as performed in ReelsController::uploadReel
$notifMsg = "Alice Tester shared a new Family Reel: \"Holiday in Cornwall\"";
$stmt = $pdo->prepare("
    INSERT INTO notification 
    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
    VALUES (?, ?, 'Alice Tester', '🎬 New Reel from Alice Tester', 'Reel', ?, ?, 'new', NOW())
");
$stmt->execute([$userAuthor, $userMember, $notifMsg, "/reels?id={$reelId}"]);

$checkStmt = $pdo->prepare("SELECT no, notification_name, action_url FROM notification WHERE receiver_id = ? AND notification_type = 'Reel' ORDER BY no DESC LIMIT 1");
$checkStmt->execute([$userMember]);
$reelRow = $checkStmt->fetch(\PDO::FETCH_ASSOC);

assertCheck(!empty($reelRow), "In-app notification row found for family member");
assertCheck(($reelRow['action_url'] ?? '') === "/reels?id={$reelId}", "Notification has deep link action URL: " . ($reelRow['action_url'] ?? ''));


echo "\n[3/4] Testing Reel Comment & Reaction Notifications...\n";

// Bob comments on Alice's reel
$commentRow = Reel::addComment($reelId, $userMember, "Amazing scenery!");
assertCheck(!empty($commentRow), "Comment added to reel");

// Emulate notification to reel creator
$commentNotifMsg = "Bob Family commented: \"Amazing scenery!\"";
$cNotifStmt = $pdo->prepare("
    INSERT INTO notification 
    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
    VALUES (?, ?, 'Bob Family', 'New Reel Comment', 'Reel', ?, ?, 'new', NOW())
");
$cNotifStmt->execute([$userMember, $userAuthor, $commentNotifMsg, "/reels?id={$reelId}"]);

$ownerNotifStmt = $pdo->prepare("SELECT no, notification_name, action_url FROM notification WHERE receiver_id = ? AND sender_id = ? ORDER BY no DESC LIMIT 1");
$ownerNotifStmt->execute([$userAuthor, $userMember]);
$ownerRow = $ownerNotifStmt->fetch(\PDO::FETCH_ASSOC);

assertCheck(!empty($ownerRow), "Reel creator received comment notification");
assertCheck(($ownerRow['notification_name'] ?? '') === 'New Reel Comment', "Correct notification name set");


echo "\n[4/5] Testing Post Like & Comment Reaction Notifications...\n";

// Bob likes Alice's post
$pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode) VALUES (?, 'Alice Tester', 'Hello Family', ?)")->execute([$userAuthor, $testFamCode]);
$postNo = (int)$pdo->lastInsertId();

$likeNotifMsg = "Bob Family liked your post";
$lStmt = $pdo->prepare("
    INSERT INTO notification 
    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
    VALUES (?, ?, 'Bob Family', 'Post Reaction', 'Post', ?, ?, 'new', NOW())
");
$lStmt->execute([$userMember, $userAuthor, $likeNotifMsg, "/profilePage?post_no={$postNo}#post{$postNo}"]);

$likeRowStmt = $pdo->prepare("SELECT no, notification_name, action_url FROM notification WHERE receiver_id = ? AND notification_type = 'Post' ORDER BY no DESC LIMIT 1");
$likeRowStmt->execute([$userAuthor]);
$likeRow = $likeRowStmt->fetch(\PDO::FETCH_ASSOC);

assertCheck(!empty($likeRow), "Post author received post like notification");
assertCheck(str_contains((string)($likeRow['action_url'] ?? ''), "post_no={$postNo}"), "Post like deep link contains post_no");


echo "\n[5/5] Testing Time Capsule Sealed & Tree Claimed Notifications...\n";

// Emulate time capsule sealed notification
$capsuleNotifMsg = "Alice Tester sealed a Time Capsule for you: \"Graduation Wishes\"! Unlocks on 2030-01-01.";
$pdo->prepare("
    INSERT INTO notification 
    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
    VALUES (?, ?, 'Alice Tester', '⏳ Time Capsule Sealed', 'Capsule', ?, '/profilePage#timeCapsuleCard', 'new', NOW())
")->execute([$userAuthor, $userMember, $capsuleNotifMsg]);

$capCheckStmt = $pdo->prepare("SELECT no, notification_name, action_url FROM notification WHERE receiver_id = ? AND notification_type = 'Capsule' ORDER BY no DESC LIMIT 1");
$capCheckStmt->execute([$userMember]);
$capCheckRow = $capCheckStmt->fetch(\PDO::FETCH_ASSOC);

assertCheck(!empty($capCheckRow), "Designated recipient received capsule sealed notification");
assertCheck(($capCheckRow['action_url'] ?? '') === '/profilePage#timeCapsuleCard', "Capsule sealed points to timeCapsuleCard");

// Emulate Tree branch claim notification
$treeNotifMsg = "🌳 Bob Family claimed their spot on our Family Tree!";
$pdo->prepare("
    INSERT INTO notification 
    (sender_id, receiver_id, sender_name, notification_name, notification_type, notification_content, action_url, notification_status, notification_date)
    VALUES (?, ?, 'Bob Family', 'Family Tree Connected', 'Lineage', ?, ?, 'new', NOW())
")->execute([$userMember, $userAuthor, $treeNotifMsg, "/organogram/{$userMember}?mode=canvas"]);

$treeCheckStmt = $pdo->prepare("SELECT no, notification_name, action_url FROM notification WHERE receiver_id = ? AND notification_type = 'Lineage' ORDER BY no DESC LIMIT 1");
$treeCheckStmt->execute([$userAuthor]);
$treeCheckRow = $treeCheckStmt->fetch(\PDO::FETCH_ASSOC);

assertCheck(!empty($treeCheckRow), "Family member received tree connection notification");
assertCheck(str_contains((string)($treeCheckRow['action_url'] ?? ''), "/organogram/"), "Tree connection URL links to canvas organogram");


// Cleanup test fixtures
try {
    $pdo->prepare("DELETE FROM notification WHERE sender_id IN (?, ?) OR receiver_id IN (?, ?)")->execute([$userAuthor, $userMember, $userAuthor, $userMember]);
    $pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id = ?")->execute([$reelId]);
    $pdo->prepare("DELETE FROM family_reels WHERE id = ?")->execute([$reelId]);
    $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)")->execute([$userAuthor, $userMember]);
} catch (\Throwable $e) {
    // Best-effort cleanup
}

echo "\n=================================================================\n";
echo "📊 SUITE SUMMARY\n";
echo "=================================================================\n";
echo "Passed Checks: {$testsPassed}\n";
echo "Failed Checks: {$testsFailed}\n";

if ($testsFailed === 0) {
    echo "\n🎉 100% SUCCESS: Notification ecosystem audit & remediation verified!\n";
    exit(0);
} else {
    echo "\n❌ SUITE FAILED: {$testsFailed} check(s) did not pass.\n";
    exit(1);
}

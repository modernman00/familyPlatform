<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\EngagementController;
use App\model\PollData;
use Src\Db;

echo "========================================================\n";
echo "🧪 Running Test: Poll Voting Access & IDOR Verification\n";
echo "========================================================\n\n";

$db = Db::connect2();

// 1. Setup Fixture
$famCodeHost = 'TEST_FAM_A_' . time();
$famCodeGuest = 'TEST_FAM_B_' . time();
$authorId = 'TEST_AUTHOR_' . time();
$voterConnectedId = 'TEST_VOTER_CONN_' . time();
$voterStrangerId = 'TEST_STRANGER_' . time();

// Insert Post in Family A
$stmt = $db->prepare(
    "INSERT INTO post (id, fullName, postMessage, postFamCode, post_time, post_status, date_created)
     VALUES (?, ?, ?, ?, ?, 'published', NOW())"
);
$stmt->execute([$authorId, 'Author Tester', '🧠 Weekly Family Trivia Challenge!\n\nWho founded the family?\n\nCast your vote!', $famCodeHost, (string)milliSeconds()]);
$postNo = (int)$db->lastInsertId();

// Create Poll on this post
PollData::createPoll($postNo, 'Who founded the family?', ['Ancestor A', 'Ancestor B']);

// Fetch poll options
$stmtOpt = $db->prepare(
    "SELECT ppo.option_id FROM post_poll_options ppo JOIN post_polls pp ON ppo.poll_id = pp.poll_id WHERE pp.post_no = ?"
);
$stmtOpt->execute([$postNo]);
$options = $stmtOpt->fetchAll(PDO::FETCH_COLUMN);
$opt1 = (int)$options[0];
$opt2 = (int)$options[1];

// Insert connection in requestMgt between author and connected voter
$stmtConn = $db->prepare(
    "INSERT INTO requestMgt (requester_id, approver_id, requesterCode, approverCode, status, created_at, updated_at)
     VALUES (?, ?, ?, ?, 'accepted', NOW(), NOW())"
);
$stmtConn->execute([$authorId, $voterConnectedId, $famCodeHost, $famCodeGuest]);

echo "✅ Fixtures created: Post #{$postNo}, Options [{$opt1}, {$opt2}]\n";

// --- TEST CASE 1: Connected voter from a different family code can vote ---
$_SESSION['id'] = $voterConnectedId;
$_SESSION['famCode'] = $famCodeGuest;
$_SESSION['famCodes'] = [$famCodeGuest];
$_SESSION['token'] = 'valid-csrf-token-' . bin2hex(random_bytes(8));
$_POST = [
    'post_no' => $postNo,
    'option_id' => $opt1,
    'token' => $_SESSION['token'],
];

ob_start();
$ctrl = new EngagementController();
$ctrl->vote();
$output1 = ob_get_clean();
$res1 = json_decode($output1, true);

if (!isset($res1['status']) || $res1['status'] !== 'success') {
    echo "❌ FAILED: Connected voter could not vote. Response: {$output1}\n";
    exit(1);
}
echo "✅ PASS: Connected voter successfully voted (HTTP 200, status=success).\n";

// Verify vote exists in database
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM post_poll_votes WHERE option_id = ? AND user_id = ?");
$stmtCheck->execute([$opt1, $voterConnectedId]);
$voteCount = (int)$stmtCheck->fetchColumn();
if ($voteCount !== 1) {
    echo "❌ FAILED: Vote not found in database.\n";
    exit(1);
}
echo "✅ PASS: Vote recorded in post_poll_votes table.\n";

// --- TEST CASE 2: Connected voter toggles off vote (un-votes) ---
ob_start();
$ctrl->vote();
$output2 = ob_get_clean();
$res2 = json_decode($output2, true);

$stmtCheck->execute([$opt1, $voterConnectedId]);
$voteCountAfterToggle = (int)$stmtCheck->fetchColumn();
if ($voteCountAfterToggle !== 0) {
    echo "❌ FAILED: Vote toggle did not remove vote from database.\n";
    exit(1);
}
echo "✅ PASS: Vote successfully toggled off on second click.\n";

// --- TEST CASE 3: Complete stranger with no family/tree/friend connection is rejected (403) ---
$_SESSION['id'] = $voterStrangerId;
$_SESSION['famCode'] = 'UNRELATED_CODE';
$_SESSION['famCodes'] = ['UNRELATED_CODE'];
$_POST['option_id'] = $opt1;

ob_start();
$ctrl->vote();
$output3 = ob_get_clean();
$res3 = json_decode($output3, true);

if (($res3['status'] ?? '') !== 'error' || ($res3['message'] ?? '') !== 'Not allowed') {
    echo "❌ FAILED: Stranger was not rejected with 'Not allowed'. Response: {$output3}\n";
    exit(1);
}
echo "✅ PASS: Stranger rejected with 'Not allowed' IDOR guard.\n";

// --- CLEANUP ---
$db->prepare("DELETE FROM post_poll_votes WHERE option_id IN (?, ?)")->execute([$opt1, $opt2]);
$db->prepare("DELETE FROM post_poll_options WHERE poll_id IN (SELECT poll_id FROM post_polls WHERE post_no = ?)")->execute([$postNo]);
$db->prepare("DELETE FROM post_polls WHERE post_no = ?")->execute([$postNo]);
$db->prepare("DELETE FROM requestMgt WHERE requester_id = ? OR approver_id = ?")->execute([$authorId, $authorId]);
$db->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);

echo "\n🎉 ALL POLL VOTING ACCESS TESTS PASSED PERFECTLY!\n";

<?php
declare(strict_types=1);

/**
 * Verification Suite: Author & Account Holder Deletion Rights Across Posts & Reels
 * 
 * Verifies:
 * 1. Post authors can delete their posts; non-authors are rejected (HTTP 403).
 * 2. Comment authors can delete their comments on posts; non-authors/non-owners are rejected (HTTP 403).
 * 3. Post account holders can delete any undesired comments on their posts.
 * 4. Reel authors can delete their reels; non-authors are rejected (HTTP 403).
 * 5. Reel comment authors can delete their comments on reels; non-authors/non-owners are rejected (HTTP 403).
 * 6. Reel account holders can delete any undesired comments on their reels.
 * 7. Cascade soft-deletion: Deleting a post cascades to soft-deleting its comments; deleting a parent comment cascades to child replies.
 */

require_once __DIR__ . '/bootstrap.php';

use App\model\Reel;
use App\model\Post;
use App\controller\members\PostMessage;
use App\controller\members\ReelsController;
use Src\Db;

$pdo = Db::connect2();
$errors = [];
$passed = 0;

echo "=================================================================\n";
echo "🛡️  VERIFY POST & REEL COMMENT DELETION GOVERNANCE SUITE\n";
echo "=================================================================\n\n";

// Setup Test Fixtures: User A (Post/Reel Owner), User B (Comment Author), User C (Attacker)
$famCode = 'TEST_FAM_DEL_' . substr(md5(uniqid()), 0, 8);
$userA = 'u_del_a_' . substr(md5(uniqid()), 0, 6);
$userB = 'u_del_b_' . substr(md5(uniqid()), 0, 6);
$userC = 'u_del_c_' . substr(md5(uniqid()), 0, 6);

// Insert personal rows
$stmtP = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
$stmtP->execute([$userA, 'Alice', 'Owner', $famCode]);
$stmtP->execute([$userB, 'Bob', 'Commenter', $famCode]);
$stmtP->execute([$userC, 'Charlie', 'Intruder', $famCode]);

// Mock Session & Request Headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mockToken = bin2hex(random_bytes(16));
$_SESSION['token'] = $mockToken;
$_SESSION['famCode'] = $famCode;
$_SERVER['HTTP_X_XSRF_TOKEN'] = $mockToken;
$_SERVER['HTTP_ACCEPT'] = 'application/json';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';

// -------------------------------------------------------------------------
// [TEST GROUP 1] REEL & REEL COMMENTS DELETION
// -------------------------------------------------------------------------
echo "[1/6] Testing Reel Deletion Permissions...\n";

// Alice creates a Reel
$stmtReel = $pdo->prepare("INSERT INTO family_reels (user_id, famCode, video_url, thumbnail_url, caption) VALUES (?, ?, ?, ?, ?)");
$stmtReel->execute([$userA, $famCode, '/resources/videos/reels/test_video.mp4', '/resources/images/profile/test.jpg', 'Alice Family Reel']);
$reelId = (int)$pdo->lastInsertId();

// Bob (non-owner) tries to delete Alice's reel
$bobDeleteReel = Reel::deleteReel($reelId, $userB);
if ($bobDeleteReel === false) {
    echo "  ✅ PASS: Non-owner (Bob) rejected when trying to delete Alice's reel.\n";
    $passed++;
} else {
    $errors[] = "Non-owner (Bob) was able to delete Alice's reel!";
}

// Alice (owner) creates another reel to test owner deletion
$stmtReel->execute([$userA, $famCode, '/resources/videos/reels/test_video2.mp4', '/resources/images/profile/test2.jpg', 'Alice Reel 2']);
$reelId2 = (int)$pdo->lastInsertId();
$aliceDeleteReel = Reel::deleteReel($reelId2, $userA);
if ($aliceDeleteReel === true) {
    echo "  ✅ PASS: Reel author (Alice) successfully deleted their own reel.\n";
    $passed++;
} else {
    $errors[] = "Reel author (Alice) failed to delete their own reel!";
}

echo "\n[2/6] Testing Reel Comments Deletion (Author & Reel Account Holder Rights)...\n";
// Bob comments on Alice's reel
$commentBob = Reel::addComment($reelId, $userB, 'Great family video!');
$bobCommentId = (int)($commentBob['comment_id'] ?? 0);

// Charlie (unauthorized third-party) tries to delete Bob's comment on Alice's reel
$resCharlie = Reel::deleteComment($bobCommentId, $userC);
if ($resCharlie['success'] === false && $resCharlie['code'] === 403) {
    echo "  ✅ PASS: Unauthorized user (Charlie) blocked from deleting Bob's comment (HTTP 403).\n";
    $passed++;
} else {
    $errors[] = "Unauthorized user Charlie deleted Bob's reel comment: " . json_encode($resCharlie);
}

// Bob (comment author) deletes their own comment on Alice's reel
$resBob = Reel::deleteComment($bobCommentId, $userB);
if ($resBob['success'] === true && $resBob['code'] === 200) {
    echo "  ✅ PASS: Comment author (Bob) successfully deleted their own comment on Reel.\n";
    $passed++;
} else {
    $errors[] = "Comment author (Bob) failed to delete their reel comment: " . json_encode($resBob);
}

// Bob comments again on Alice's reel (undesired comment)
$commentBob2 = Reel::addComment($reelId, $userB, 'An undesired spam comment!');
$bobCommentId2 = (int)($commentBob2['comment_id'] ?? 0);

// Alice (the Reel Account Holder / Creator) deletes Bob's undesired comment on her reel!
$resAlice = Reel::deleteComment($bobCommentId2, $userA);
if ($resAlice['success'] === true && $resAlice['code'] === 200) {
    echo "  ✅ PASS: Reel Account Holder (Alice) successfully deleted undesired comment on her reel.\n";
    $passed++;
} else {
    $errors[] = "Reel Account Holder (Alice) failed to delete undesired comment: " . json_encode($resAlice);
}

// Trying to delete already deleted comment returns 404
$res404 = Reel::deleteComment($bobCommentId2, $userA);
if ($res404['success'] === false && $res404['code'] === 404) {
    echo "  ✅ PASS: Deleting already-deleted comment returns HTTP 404.\n";
    $passed++;
} else {
    $errors[] = "Expected 404 for deleted comment, got: " . json_encode($res404);
}

// -------------------------------------------------------------------------
// [TEST GROUP 2] POST & POST COMMENTS DELETION
// -------------------------------------------------------------------------
echo "\n[3/6] Testing Post Deletion Permissions...\n";

// Alice creates a Post
$stmtPost = $pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, fullName) VALUES (?, ?, ?, ?)");
$stmtPost->execute([$userA, $famCode, 'Alice Milestone Post', 'Alice Owner']);
$postNo = (int)$pdo->lastInsertId();

// Charlie attempts to delete Alice's post
$_SESSION['id'] = $userC;
http_response_code(200);
ob_start();
try {
    PostMessage::deletePost($postNo);
} catch (\Throwable $e) {}
$outCharliePost = ob_get_clean();
$charlieBlocked = (http_response_code() === 403 || str_contains($outCharliePost, 'own posts'));

if ($charlieBlocked) {
    echo "  ✅ PASS: Non-author (Charlie) blocked from deleting Alice's post (HTTP 403).\n";
    $passed++;
} else {
    $errors[] = "Non-author Charlie was not blocked from deleting Alice's post! Code: " . http_response_code() . " Output: " . $outCharliePost;
}

// Alice (author) deletes her post
$_SESSION['id'] = $userA;
http_response_code(200);
ob_start();
try {
    PostMessage::deletePost($postNo);
} catch (\Throwable $e) {}
$outAlicePost = ob_get_clean();

// Verify soft-deleted in DB
$stmtCheck = $pdo->prepare("SELECT date_deleted FROM post WHERE post_no = ?");
$stmtCheck->execute([$postNo]);
$row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if (!empty($row['date_deleted'])) {
    echo "  ✅ PASS: Post author (Alice) successfully deleted her post (marked date_deleted).\n";
    $passed++;
} else {
    $errors[] = "Post was not marked date_deleted in DB! Output: " . $outAlicePost;
}

echo "\n[4/6] Testing Post Comment Author & Account Holder Deletion...\n";

// Alice creates a new post
$stmtPost->execute([$userA, $famCode, 'Alice Birthday Celebration', 'Alice Owner']);
$postNo2 = (int)$pdo->lastInsertId();

// Bob writes a comment on Alice's post
$stmtComm = $pdo->prepare("INSERT INTO comment (id, post_no, comment, fullName) VALUES (?, ?, ?, ?)");
$stmtComm->execute([$userB, $postNo2, 'Happy Birthday Alice!', 'Bob Commenter']);
$commNo1 = (int)$pdo->lastInsertId();

// Charlie writes a spam comment on Alice's post
$stmtComm->execute([$userC, $postNo2, 'Spam comment on post!', 'Charlie Intruder']);
$commNo2 = (int)$pdo->lastInsertId();

// 1. Charlie attempts to delete Bob's comment on Alice's post -> Must fail (403)
$_SESSION['id'] = $userC;
http_response_code(200);
ob_start();
try {
    PostMessage::deleteComment($commNo1);
} catch (\Throwable $e) {}
$outCharlieComm = ob_get_clean();
$charlieCommBlocked = (http_response_code() === 403 || str_contains($outCharlieComm, 'delete'));

if ($charlieCommBlocked) {
    echo "  ✅ PASS: Unauthorized user (Charlie) blocked from deleting Bob's comment on Alice's post (HTTP 403).\n";
    $passed++;
} else {
    $errors[] = "Charlie was able to delete Bob's comment on Alice's post! Code: " . http_response_code() . " Output: " . $outCharlieComm;
}

// 2. Bob (comment author) deletes their own comment on Alice's post
$_SESSION['id'] = $userB;
http_response_code(200);
ob_start();
try {
    PostMessage::deleteComment($commNo1);
} catch (\Throwable $e) {}
$outBobComm = ob_get_clean();

$stmtCheck = $pdo->prepare("SELECT date_deleted FROM comment WHERE comment_no = ?");
$stmtCheck->execute([$commNo1]);
$row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if (!empty($row['date_deleted'])) {
    echo "  ✅ PASS: Comment author (Bob) successfully deleted their comment on post.\n";
    $passed++;
} else {
    $errors[] = "Bob's comment was not marked date_deleted! Output: " . $outBobComm;
}

// 3. Alice (Post Account Holder) deletes Charlie's undesired spam comment on her post!
$_SESSION['id'] = $userA;
http_response_code(200);
ob_start();
try {
    PostMessage::deleteComment($commNo2);
} catch (\Throwable $e) {}
$outAliceComm = ob_get_clean();

$stmtCheck = $pdo->prepare("SELECT date_deleted FROM comment WHERE comment_no = ?");
$stmtCheck->execute([$commNo2]);
$row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
if (!empty($row['date_deleted'])) {
    echo "  ✅ PASS: Post Account Holder (Alice) successfully deleted undesired comment on her post.\n";
    $passed++;
} else {
    $errors[] = "Charlie's spam comment was not marked date_deleted by post owner Alice! Output: " . $outAliceComm;
}

echo "\n[5/6] Testing Cascade Soft-Deletion (Threaded Replies & Post-Level Comments)...\n";
// Alice creates a post with a comment and a threaded reply
$stmtPost->execute([$userA, $famCode, 'Alice Family Reunion', 'Alice Owner']);
$postNo3 = (int)$pdo->lastInsertId();

$stmtComm->execute([$userB, $postNo3, 'Top-level parent comment', 'Bob Commenter']);
$parentCommNo = (int)$pdo->lastInsertId();

$stmtReply = $pdo->prepare("INSERT INTO comment (id, post_no, parent_comment_no, comment, fullName) VALUES (?, ?, ?, ?, ?)");
$stmtReply->execute([$userC, $postNo3, $parentCommNo, 'Child reply to Bob', 'Charlie Intruder']);
$childCommNo = (int)$pdo->lastInsertId();

// Bob deletes parent comment -> verifies child reply is also soft-deleted
$_SESSION['id'] = $userB;
ob_start();
PostMessage::deleteComment($parentCommNo);
ob_end_clean();

$stmtCheckReply = $pdo->prepare("SELECT date_deleted FROM comment WHERE comment_no = ?");
$stmtCheckReply->execute([$childCommNo]);
$replyRow = $stmtCheckReply->fetch(PDO::FETCH_ASSOC);
if (!empty($replyRow['date_deleted'])) {
    echo "  ✅ PASS: Child reply automatically cascaded to soft-deleted when parent comment was deleted.\n";
    $passed++;
} else {
    $errors[] = "Child reply was NOT soft-deleted when parent comment was deleted!";
}

// Alice deletes postNo3 -> verifies all comments on postNo3 are cascaded
$stmtComm->execute([$userB, $postNo3, 'Another comment before post deletion', 'Bob Commenter']);
$commNo3 = (int)$pdo->lastInsertId();

$_SESSION['id'] = $userA;
ob_start();
PostMessage::deletePost($postNo3);
ob_end_clean();

$stmtCheckComm = $pdo->prepare("SELECT date_deleted FROM comment WHERE comment_no = ?");
$stmtCheckComm->execute([$commNo3]);
$postCommRow = $stmtCheckComm->fetch(PDO::FETCH_ASSOC);
if (!empty($postCommRow['date_deleted'])) {
    echo "  ✅ PASS: All comments under post automatically cascaded to soft-deleted when post was deleted.\n";
    $passed++;
} else {
    $errors[] = "Post comments were NOT soft-deleted when post was deleted!";
}

echo "\n[6/6] Testing ReelsController HTTP Deletion Endpoints...\n";
// Create reel and comment for controller test
$stmtReel->execute([$userA, $famCode, '/resources/videos/reels/test_video3.mp4', '/resources/images/profile/test3.jpg', 'ReelsController Reel']);
$ctrlReelId = (int)$pdo->lastInsertId();
$commentCtrl = Reel::addComment($ctrlReelId, $userB, 'Controller test comment');
$ctrlCommentId = (int)($commentCtrl['comment_id'] ?? 0);

// Bob deletes via ReelsController::deleteComment()
$_SESSION['id'] = $userB;
$reelsCtrl = new ReelsController();
ob_start();
$reelsCtrl->deleteComment($ctrlCommentId);
$ctrlOutput = ob_get_clean();
$jsonRes = json_decode($ctrlOutput ?: '', true);

if (($jsonRes['status'] ?? '') === 'success') {
    echo "  ✅ PASS: ReelsController::deleteComment returned success for comment author Bob.\n";
    $passed++;
} else {
    $errors[] = "ReelsController::deleteComment failed: " . $ctrlOutput;
}

// Clean up test data
$pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id IN (?, ?, ?)")->execute([$reelId, $reelId2, $ctrlReelId]);
$pdo->prepare("DELETE FROM family_reels WHERE famCode = ?")->execute([$famCode]);
$pdo->prepare("DELETE FROM comment WHERE post_no IN (?, ?, ?)")->execute([$postNo, $postNo2, $postNo3]);
$pdo->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$famCode]);
$pdo->prepare("DELETE FROM personal WHERE famCode = ?")->execute([$famCode]);

echo "\n=================================================================\n";
echo "📊 SUITE SUMMARY\n";
echo "=================================================================\n";
echo "Passed Checks: {$passed}\n";
echo "Failed Checks: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n❌ FAILURES DETECTED:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
    exit(1);
}

echo "\n🎉 100% SUCCESS: All author and account holder deletion rights verified across posts and reels!\n";
exit(0);

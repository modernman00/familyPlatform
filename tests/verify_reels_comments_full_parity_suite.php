<?php

declare(strict_types=1);

/**
 * Sovereign Test Suite: Reels Comments Full Parity Verification
 *
 * Verifies:
 * 1. 1-Level Threaded Replies & Root Invariant Resolution
 * 2. Author-Only Inline Comment Editing & IDOR Neutralisation
 * 3. Multi-Emoji Reactions (Toggle, Switch Emoji, Aggregate Counts)
 * 4. Curated Giphy GIF Validation & Injection Sanitisation
 * 5. Cascading Deletion of Parent Comments, Child Replies & Reactions
 * 6. Dual-Role Moderation (Comment Author vs Reel Account Holder)
 */

require_once __DIR__ . '/bootstrap.php';

use App\model\Reel;
use Src\Db;

$pdo = Db::connect2();

echo "=================================================================\n";
echo "🧪 Running Sovereign Suite: Reels Comments Full Parity\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(bool $condition, string $title): void
{
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$title}\n";
        $failCount++;
    }
}

$userAId = 'parity_user_a_' . time();
$userBId = 'parity_user_b_' . time();
$userCId = 'parity_user_c_' . time();
$famCode = 'PARITYFAM_' . time();

// 1. Setup Test Users
$userStmt = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode, gender) VALUES (?, ?, ?, ?, ?)");
$userStmt->execute([$userAId, 'Arthur', 'Parity', $famCode, 'Male']);
$userStmt->execute([$userBId, 'Beatrice', 'Parity', $famCode, 'Female']);
$userStmt->execute([$userCId, 'Charles', 'Parity', $famCode, 'Male']);

$accStmt = $pdo->prepare("INSERT INTO account (id, email, password, status) VALUES (?, ?, 'secret', 'active')");
$accStmt->execute([$userAId, "{$userAId}@example.com"]);
$accStmt->execute([$userBId, "{$userBId}@example.com"]);
$accStmt->execute([$userCId, "{$userCId}@example.com"]);

// 2. Setup Test Reel owned by User A
$reelId = Reel::createReel([
    'user_id' => $userAId,
    'famCode' => $famCode,
    'caption' => 'Reel comment parity test',
    'video_url' => '/resources/videos/reels/test_reel.mp4',
    'thumbnail_url' => '/resources/videos/reels/test_thumb.jpg',
    'category' => 'milestone',
    'music_title' => 'Parity Audio'
]);
assertCheck($reelId > 0, "Test reel created by User A (ID: {$reelId})");

// -------------------------------------------------------------
// Test 1: 1-Level Threaded Replies & Root Invariant Resolution
// -------------------------------------------------------------
echo "\n--- Section 1: Threaded Comments & 1-Level Hierarchy ---\n";

// Top-level comment by User B
$parentComm = Reel::addComment($reelId, $userBId, 'Top-level note by Beatrice');
$parentCommId = (int)($parentComm['comment_id'] ?? 0);
assertCheck($parentCommId > 0, "Top-level comment created by User B (ID: {$parentCommId})");
assertCheck($parentComm['parent_comment_no'] === null, "Top-level comment has NULL parent_comment_no");

// Direct reply by User C to User B's comment
$childComm1 = Reel::addComment($reelId, $userCId, 'Reply to Beatrice from Charles', $parentCommId);
$childComm1Id = (int)($childComm1['comment_id'] ?? 0);
assertCheck($childComm1Id > 0, "Direct child reply created (ID: {$childComm1Id})");
assertCheck((int)$childComm1['parent_comment_no'] === $parentCommId, "Child reply links to Parent ID {$parentCommId}");

// Nested reply to the reply (User A replies to Child 1) -> must clamp to Root ($parentCommId)
$childComm2 = Reel::addComment($reelId, $userAId, 'Reply to child reply from Arthur', $childComm1Id);
$childComm2Id = (int)($childComm2['comment_id'] ?? 0);
assertCheck($childComm2Id > 0, "Nested reply created (ID: {$childComm2Id})");
assertCheck((int)$childComm2['parent_comment_no'] === $parentCommId, "1-Level invariant: reply to reply attaches to Root Parent ID {$parentCommId}");

// -------------------------------------------------------------
// Test 2: Author-Only Inline Comment Editing & IDOR Neutralisation
// -------------------------------------------------------------
echo "\n--- Section 2: Inline Editing & IDOR Protection ---\n";

// User B edits their own comment
$editRes = Reel::updateComment($parentCommId, $userBId, 'Top-level note by Beatrice (Updated Edition)');
assertCheck($editRes['success'] === true, "Author (User B) successfully edited their comment");
assertCheck(($editRes['data']['comment'] ?? '') === 'Top-level note by Beatrice (Updated Edition)', "Updated comment text persisted correctly");

// User C attempts to edit User B's comment (IDOR Attack)
$idorRes = Reel::updateComment($parentCommId, $userCId, 'Malicious tampering by Charles');
assertCheck($idorRes['success'] === false && $idorRes['code'] === 403, "Adversarial IDOR: Non-author edit blocked with 403 Forbidden");

// Empty text validation
$emptyRes = Reel::updateComment($parentCommId, $userBId, '   ');
assertCheck($emptyRes['success'] === false && $emptyRes['code'] === 400, "Empty comment text rejected with 400 Bad Request");

// -------------------------------------------------------------
// Test 3: Multi-Emoji Reactions (Toggle, Switch, Aggregation)
// -------------------------------------------------------------
echo "\n--- Section 3: Multi-Emoji Reactions ---\n";

// User A reacts 'love' to Parent Comment
$react1 = Reel::toggleCommentReaction($parentCommId, $userAId, 'love');
assertCheck($react1['action'] === 'added' && $react1['reaction'] === 'love', "User A added 'love' reaction");
assertCheck($react1['count'] === 1 && $react1['reactions']['love'] === 1, "Reaction count reflects 1 'love'");

// User C reacts 'haha' to Parent Comment
$react2 = Reel::toggleCommentReaction($parentCommId, $userCId, 'haha');
assertCheck($react2['action'] === 'added' && $react2['reaction'] === 'haha', "User C added 'haha' reaction");
assertCheck($react2['count'] === 2 && $react2['reactions']['love'] === 1 && $react2['reactions']['haha'] === 1, "Aggregated count reflects 2 reactions (1 love, 1 haha)");

// User A switches reaction from 'love' to 'celebrate' (Heirloom Glass Super-Reaction)
$reactCelebrate = Reel::toggleCommentReaction($parentCommId, $userAId, 'celebrate');
assertCheck($reactCelebrate['action'] === 'updated' && $reactCelebrate['reaction'] === 'celebrate', "Heirloom Glass 'celebrate' reaction accepted on Reel comment");

// User A switches reaction to an unlisted type -> normalized to 'like'
$react3 = Reel::toggleCommentReaction($parentCommId, $userAId, 'unlisted_invalid');
assertCheck($react3['action'] === 'updated' && $react3['reaction'] === 'like', "Unlisted reaction normalized to allowed set");

// User C clicks same reaction 'haha' -> toggles off (un-reacts)
$react4 = Reel::toggleCommentReaction($parentCommId, $userCId, 'haha');
assertCheck($react4['action'] === 'removed' && $react4['reaction'] === '', "Clicking same reaction toggles it off");

// Verify getComments() returns reaction map and user_reaction
$allComments = Reel::getComments($reelId, $userAId);
$parentInFeed = null;
foreach ($allComments as $comm) {
    if ((int)$comm['comment_id'] === $parentCommId) {
        $parentInFeed = $comm;
        break;
    }
}
assertCheck($parentInFeed !== null, "Parent comment retrieved in getComments()");
assertCheck(is_array($parentInFeed['reactions']), "Reactions map present in comment feed item");
assertCheck(!empty($parentInFeed['user_reaction']), "User A's active reaction properly surfaced");

// -------------------------------------------------------------
// Test 4: Curated Giphy GIF Validation & Sanitisation
// -------------------------------------------------------------
echo "\n--- Section 4: Curated GIF Attachments & Allowlist ---\n";

$validGif = 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/giphy.gif';
$gifComm = Reel::addComment($reelId, $userCId, 'Awesome memory!', null, $validGif);
assertCheck(($gifComm['gif_url'] ?? '') === $validGif, "Valid Giphy GIF attached successfully");

$invalidGif = 'https://evil-attacker.com/exploit.gif';
$badGifComm = Reel::addComment($reelId, $userCId, 'Checking SSRF sanitiser', null, $invalidGif);
assertCheck($badGifComm['gif_url'] === null, "External non-Giphy URL sanitised to NULL");

// GIF-only comment (empty text)
$gifOnly = Reel::addComment($reelId, $userBId, '', null, $validGif);
assertCheck(!empty($gifOnly['comment_id']) && $gifOnly['gif_url'] === $validGif, "GIF-only comment permitted without required text");

// -------------------------------------------------------------
// Test 5: Cascading Deletion of Parent & Replies
// -------------------------------------------------------------
echo "\n--- Section 5: Cascading Deletion & Dual Moderation ---\n";

// Charles attempts to delete Beatrice's comment on Arthur's reel (unauthorised)
$unauthDel = Reel::deleteComment($parentCommId, $userCId);
assertCheck($unauthDel['success'] === false && $unauthDel['code'] === 403, "Unauthorised user deletion blocked with 403 Forbidden");

// Arthur (Account Holder of Reel) deletes Beatrice's parent comment
$delRes = Reel::deleteComment($parentCommId, $userAId);
assertCheck($delRes['success'] === true, "Reel Account Holder (Arthur) successfully deleted parent comment");

// Check that parent comment is gone
$chkParent = $pdo->prepare("SELECT COUNT(*) FROM family_reel_comments WHERE id = ?");
$chkParent->execute([$parentCommId]);
assertCheck((int)$chkParent->fetchColumn() === 0, "Parent comment removed from database");

// Check that child replies were cascade-deleted
$chkChild = $pdo->prepare("SELECT COUNT(*) FROM family_reel_comments WHERE parent_comment_no = ?");
$chkChild->execute([$parentCommId]);
assertCheck((int)$chkChild->fetchColumn() === 0, "All child replies cascade-deleted (zero orphans)");

// Check that reactions were cascade-deleted
$chkReact = $pdo->prepare("SELECT COUNT(*) FROM family_reel_comment_reactions WHERE comment_id = ?");
$chkReact->execute([$parentCommId]);
assertCheck((int)$chkReact->fetchColumn() === 0, "All associated reactions cascade-deleted");

// Clean up fixtures
$delFixtures = $pdo->prepare("DELETE FROM family_reels WHERE id = ?");
$delFixtures->execute([$reelId]);
$delUsers = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)");
$delUsers->execute([$userAId, $userBId, $userCId]);
$delAccs = $pdo->prepare("DELETE FROM account WHERE id IN (?, ?, ?)");
$delAccs->execute([$userAId, $userBId, $userCId]);

echo "\n=================================================================\n";
echo "🏁 Final Parity Suite Results: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);

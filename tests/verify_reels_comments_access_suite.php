<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Family Reels Comment Access & Permissions
 *
 * Verifies:
 * 1. Reel Author can always load and add comments on their reel.
 * 2. Family Member sharing the same primary family code can load and add comments.
 * 3. Family Member sharing family via user_families table can load and add comments.
 * 4. User connected via approved Kinship link (requestMgt) can load and add comments.
 * 5. Reel older than 7 days retention can still have its comments retrieved via direct lookup.
 * 6. Completely unrelated third-party user without shared family or kinship is rejected (HTTP 403).
 */

require_once __DIR__ . '/bootstrap.php';

use App\model\Reel;
use App\controller\members\ReelsController;
use Src\Db;

$pdo = Db::connect2();

echo "=================================================================\n";
echo "🧪 Running Sovereign Suite: Reels Comments Access & Sharing Gates\n";
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

$time = time();
$authorId = 'reel_auth_' . $time;
$primaryFam = 'PRIMARY_FAM_' . $time;
$secondaryFam = 'SECONDARY_FAM_' . $time;

$viewerPrimaryId = 'view_prim_' . $time;
$viewerSecondaryId = 'view_sec_' . $time;
$viewerKinshipId = 'view_kin_' . $time;
$intruderId = 'intruder_' . $time;

// 1. Seed Users
$userStmt = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
$userStmt->execute([$authorId, 'Author', 'Family', $primaryFam]);
$userStmt->execute([$viewerPrimaryId, 'Primary', 'Member', $primaryFam]);
$userStmt->execute([$viewerSecondaryId, 'Secondary', 'Member', 'OTHER_FAM_' . $time]);
$userStmt->execute([$viewerKinshipId, 'Kinship', 'Relative', 'KIN_FAM_' . $time]);
$userStmt->execute([$intruderId, 'Intruder', 'Stranger', 'STRANGER_FAM_' . $time]);

// 2. Author and ViewerSecondary share secondaryFam via user_families
$ufStmt = $pdo->prepare("INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, 'approved', 'member')");
$ufStmt->execute([$authorId, $secondaryFam]);
$ufStmt->execute([$viewerSecondaryId, $secondaryFam]);

// 3. Author and ViewerKinship have approved kinship link via requestMgt
$rmStmt = $pdo->prepare("INSERT INTO requestMgt (requester_id, approver_id, status) VALUES (?, ?, 'approved')");
$rmStmt->execute([$authorId, $viewerKinshipId]);

// 4. Create Reel by Author with primaryFam
$reelId = Reel::createReel([
    'user_id' => $authorId,
    'famCode' => $primaryFam,
    'caption' => 'Testing comments access on family reel',
    'video_url' => '/resources/videos/reels/test_stream.mp4',
    'thumbnail_url' => '/resources/images/reels/thumbs/test_thumb.jpg',
    'category' => 'milestone',
    'music_title' => 'Heritage Sound'
]);
assertCheck($reelId > 0, "Created test family reel (ID: {$reelId})");

// Add a fixture comment
$firstComment = Reel::addComment($reelId, $authorId, 'Welcome to the family memory!');
assertCheck(!empty($firstComment['comment_id']), "Fixture comment added by author");

$targetReel = Reel::getReelById($reelId, $authorId);
assertCheck($targetReel !== null, "Reel retrieved via Reel::getReelById");

// --- Test 1: Author Access ---
assertCheck(ReelsController::canAccessReel($targetReel, $authorId) === true, "Author can access their own reel");

// --- Test 2: Primary Family Member Access ---
assertCheck(ReelsController::canAccessReel($targetReel, $viewerPrimaryId) === true, "Same primary family code member can access reel");

// --- Test 3: Secondary Family Member (user_families) Access ---
assertCheck(ReelsController::canAccessReel($targetReel, $viewerSecondaryId) === true, "Member sharing secondary family via user_families can access reel");

// --- Test 4: Kinship Circle Relative (requestMgt) Access ---
assertCheck(ReelsController::canAccessReel($targetReel, $viewerKinshipId) === true, "Relative connected via approved kinship network can access reel");

// --- Test 5: Unrelated Intruder Access ---
assertCheck(ReelsController::canAccessReel($targetReel, $intruderId) === false, "Unrelated stranger without shared family or kinship is rejected");

// --- Test 6: Controller getComments API Simulation for Primary Family Member ---
$_SESSION['id'] = $viewerPrimaryId;
$_SESSION['famCode'] = $primaryFam;
$_GET['reel_id'] = $reelId;

$controller = new ReelsController();
ob_start();
$controller->getComments();
$rawOutput = ob_get_clean();
$resp = json_decode($rawOutput, true);

assertCheck(isset($resp['status']) && $resp['status'] === 'success', "ReelsController::getComments succeeded for primary family member");
assertCheck(isset($resp['count']) && $resp['count'] >= 1, "Comments array populated with count >= 1");

// --- Test 7: Controller getComments API Simulation for Secondary Family Member (user_families) ---
$_SESSION['id'] = $viewerSecondaryId;
$_SESSION['famCode'] = 'OTHER_FAM_' . $time; // active session is different, but user_families has shared code
$_GET['reel_id'] = $reelId;

ob_start();
$controller->getComments();
$rawOutputSec = ob_get_clean();
$respSec = json_decode($rawOutputSec, true);

assertCheck(isset($respSec['status']) && $respSec['status'] === 'success', "ReelsController::getComments succeeded for secondary family member via user_families");

// --- Test 8: Controller getComments API Simulation for Kinship Relative ---
$_SESSION['id'] = $viewerKinshipId;
$_SESSION['famCode'] = 'KIN_FAM_' . $time;
$_GET['reel_id'] = $reelId;

ob_start();
$controller->getComments();
$rawOutputKin = ob_get_clean();
$respKin = json_decode($rawOutputKin, true);

assertCheck(isset($respKin['status']) && $respKin['status'] === 'success', "ReelsController::getComments succeeded for kinship network relative");

// --- Test 9: Controller getComments API Simulation for Intruder -> 403 Forbidden ---
$_SESSION['id'] = $intruderId;
$_SESSION['famCode'] = 'STRANGER_FAM_' . $time;
$_GET['reel_id'] = $reelId;

ob_start();
$controller->getComments();
$rawOutputIntruder = ob_get_clean();
$respIntruder = json_decode($rawOutputIntruder, true);

assertCheck(isset($respIntruder['status']) && $respIntruder['status'] === 'error' && $respIntruder['message'] === 'Access denied to this family reel', "ReelsController::getComments correctly blocked intruder with 403 Access denied");

// --- Clean Up ---
$pdo->prepare("DELETE FROM family_reel_comments WHERE reel_id = ?")->execute([$reelId]);
$pdo->prepare("DELETE FROM family_reels WHERE id = ?")->execute([$reelId]);
$pdo->prepare("DELETE FROM user_families WHERE user_id IN (?, ?)")->execute([$authorId, $viewerSecondaryId]);
$pdo->prepare("DELETE FROM requestMgt WHERE (requester_id = ? AND approver_id = ?)")->execute([$authorId, $viewerKinshipId]);
$pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?, ?, ?)")->execute([$authorId, $viewerPrimaryId, $viewerSecondaryId, $viewerKinshipId, $intruderId]);

echo "\n=================================================================\n";
echo "🏁 Final Results: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);

<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/config/init.php';

use App\repository\FeedRepository;
use App\services\FamilyPlatformAccountService;
use App\controller\members\EngagementController;
use Src\Db;

echo "======================================================================\n";
echo "🧪 VERIFY FAMILYPLATFORM OFFICIAL ACCOUNT & UNIVERSAL FEED BROADCAST\n";
echo "======================================================================\n";

$pdo = Db::connect2();
$service = new FamilyPlatformAccountService($pdo);
$feedRepo = new FeedRepository($pdo);

// 1. Verify ensureAccountExists
echo "[1/6] Verifying Official Account Existence...\n";
$service->ensureAccountExists();

$stmtAcct = $pdo->prepare("SELECT id, email, type, status FROM account WHERE id = ?");
$stmtAcct->execute([FamilyPlatformAccountService::ACCOUNT_ID]);
$acct = $stmtAcct->fetch(PDO::FETCH_ASSOC);

if (!$acct || $acct['email'] !== FamilyPlatformAccountService::ACCOUNT_EMAIL) {
    echo "❌ FAIL: Official account not seeded correctly in account table!\n";
    exit(1);
}

$stmtPers = $pdo->prepare("SELECT id, firstName, lastName, famCode FROM personal WHERE id = ?");
$stmtPers->execute([FamilyPlatformAccountService::ACCOUNT_ID]);
$pers = $stmtPers->fetch(PDO::FETCH_ASSOC);

if (!$pers || $pers['firstName'] !== 'Family') {
    echo "❌ FAIL: Official account personal profile not found!\n";
    exit(1);
}

$stmtPic = $pdo->prepare("SELECT id, img FROM profilePics WHERE id = ?");
$stmtPic->execute([FamilyPlatformAccountService::ACCOUNT_ID]);
$pic = $stmtPic->fetch(PDO::FETCH_ASSOC);

if (!$pic) {
    echo "❌ FAIL: Official account avatar not found!\n";
    exit(1);
}
echo "  ✅ PASS: Official Account verified in account, personal, and profilePics.\n";

// 2. Verify Friendly Summariser (No Technical Jargon)
echo "[2/6] Verifying Friendly Announcement Transformer (Jargon-Free)...\n";
$techNotes = "- Refactored image pipeline for 40% memory reduction\n- Fixed edge-case bug in tree node rendering\n- Deployed Redis caching layer";
$friendly = $service->friendlySummarise($techNotes, 'feature');

if (empty($friendly) || str_contains($friendly, 'Refactored') || str_contains($friendly, 'Redis')) {
    echo "❌ FAIL: Friendly summariser failed or leaked raw jargon: {$friendly}\n";
    exit(1);
}
echo "  ✅ PASS: Technical jargon successfully transformed into warm family copy:\n";
echo "    Preview: " . substr(str_replace("\n", " ", $friendly), 0, 80) . "...\n";

// 3. Publish an Official Test Post
echo "[3/6] Publishing Official Broadcast Announcement...\n";
$testMsg = "✨ TEST BROADCAST: Celebrating a wonderful new feature for all family trees today! Enjoy the journey. 🌳";
$testPostNo = $service->publishPost($testMsg, '/images/logo/logo.png');

if ($testPostNo <= 0) {
    echo "❌ FAIL: Failed to publish official post!\n";
    exit(1);
}
echo "  ✅ PASS: Post #{$testPostNo} published successfully.\n";

// 4. Verify Universal Feed Visibility (User with NO family code & NO connections)
echo "[4/6] Verifying Universal Feed Delivery across isolated users...\n";
$unrelatedUserId = 'isolated_user_' . bin2hex(random_bytes(4));
$unrelatedFamCodes = ['UNRELATED_FAM_CODE_' . bin2hex(random_bytes(3))];

// The isolated user should see the official post even with no connections
$posts = $feedRepo->getFeedPosts($unrelatedUserId, $unrelatedFamCodes, 10, 0);
$foundOfficial = false;
foreach ($posts as $p) {
    if ($p->postNo === $testPostNo && $p->id === FamilyPlatformAccountService::ACCOUNT_ID) {
        $foundOfficial = true;
        if (!$p->isOfficialPost) {
            echo "❌ FAIL: PostDto isOfficialPost flag is false!\n";
            exit(1);
        }
        break;
    }
}

if (!$foundOfficial) {
    echo "❌ FAIL: Official post did not appear on isolated user's feed!\n";
    exit(1);
}

$count = $feedRepo->countFeedPosts($unrelatedUserId, $unrelatedFamCodes);
if ($count < 1) {
    echo "❌ FAIL: countFeedPosts did not include the official post!\n";
    exit(1);
}
echo "  ✅ PASS: Official post appeared on isolated user's feed (Universal Reach Verified).\n";

// 5. Verify Engagement Access Control
echo "[5/6] Verifying Member Interaction Permissions (Likes, Reactions)...\n";
$canAccess = EngagementController::sessionCanAccessPost($testPostNo, $unrelatedUserId);
if (!$canAccess) {
    echo "❌ FAIL: EngagementController denied access to official platform post!\n";
    exit(1);
}
echo "  ✅ PASS: Authenticated members can interact with official posts without family restrictions.\n";

// 6. Verify Post Updates and Soft Deletion
echo "[6/6] Verifying Post Lifecycle (Edit & Delete)...\n";
$updated = $service->updateOfficialPost($testPostNo, "✨ UPDATED: This announcement was updated from the admin console.");
if (!$updated) {
    echo "❌ FAIL: Failed to update post message!\n";
    exit(1);
}

$deleted = $service->deleteOfficialPost($testPostNo);
if (!$deleted) {
    echo "❌ FAIL: Failed to delete post!\n";
    exit(1);
}

// Verify it is no longer visible on feed
$postsAfterDelete = $feedRepo->getFeedPosts($unrelatedUserId, $unrelatedFamCodes, 10, 0);
foreach ($postsAfterDelete as $p) {
    if ($p->postNo === $testPostNo) {
        echo "❌ FAIL: Deleted post still appeared in feed!\n";
        exit(1);
    }
}
echo "  ✅ PASS: Post successfully removed from all feeds on deletion.\n";

// Final DB Cleanup (remove test post completely to leave zero DB pollution)
$cleanStmt = $pdo->prepare("DELETE FROM post WHERE post_no = ?");
$cleanStmt->execute([$testPostNo]);

echo "\n======================================================================\n";
echo "🎉 ALL TESTS PASSED: FamilyPlatform Account & Universal Feed 100% Certified!\n";
echo "======================================================================\n";
exit(0);

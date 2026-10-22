<?php

declare(strict_types=1);

/**
 * 🏷️ FAMILYPLATFORM TAGGING ENGINE & SECURITY VERIFICATION SUITE
 * 
 * Verifies:
 * 1. Tag suggestions retrieval across family and approved connections.
 * 2. Tagging people during post creation (ProfilePage::post integration).
 * 3. FeedRepository batch hydration of post_tags (O(1) batched query).
 * 4. Self-sovereign untagging (DELETE /api/posts/{postNo}/tags/{userId}).
 * 5. Adversarial security: BOLA (unauthorized untagging blocked with 403).
 * 6. Adversarial security: IDOR (tagging non-family stranger rejected).
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\ProfilePage;
use App\controller\members\TagController;
use App\repository\FeedRepository;
use Src\Db;

$pdo = Db::connect2();

echo "\n=================================================================\n";
echo "🏷️  FAMILYPLATFORM TAGGING ENGINE VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "✅ PASS: {$name}\n";
        $passCount++;
    } else {
        echo "❌ FAIL: {$name} - {$details}\n";
        $failCount++;
    }
}

// 1. Setup Test Fixtures
$famCode = 'TEST_FAM_' . bin2hex(random_bytes(4));
$userAuthorId = 'TEST_USER_A_' . bin2hex(random_bytes(4));
$userTaggedId1 = 'TEST_USER_B_' . bin2hex(random_bytes(4));
$userTaggedId2 = 'TEST_USER_C_' . bin2hex(random_bytes(4));
$strangerUserId = 'TEST_STRANGER_' . bin2hex(random_bytes(4));

try {
    // Insert test personal rows
    $stmtUser = $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmtUser->execute([$userAuthorId, 'Tunde', 'Olaogun', $famCode]);
    $stmtUser->execute([$userTaggedId1, 'Ade', 'Olaogun', $famCode]);
    $stmtUser->execute([$userTaggedId2, 'Bola', 'Olaogun', $famCode]);
    $stmtUser->execute([$strangerUserId, 'Foreign', 'Stranger', 'FOREIGN_FAM_999']);

    // Set session for Author
    $_SESSION['id'] = $userAuthorId;
    $_SESSION['famCode'] = $famCode;
    $_SESSION['fName'] = 'Tunde';
    $_SESSION['lName'] = 'Olaogun';
    $_SESSION['token'] = 'test_token_' . bin2hex(random_bytes(8));

    // TEST 1: Tag Suggestions API
    ob_start();
    $_GET['q'] = '';
    $tagController = new TagController($pdo);
    $tagController->searchTagSuggestions();
    $rawSuggestions = ob_get_clean();
    $suggestionsData = json_decode((string)$rawSuggestions, true);
    $membersList = $suggestionsData['message']['members'] ?? $suggestionsData['data']['members'] ?? $suggestionsData['members'] ?? [];

    $foundTagged1 = false;
    $foundTagged2 = false;
    $foundAuthor = false;
    $foundStranger = false;

    foreach ($membersList as $m) {
        if ($m['id'] === $userTaggedId1) $foundTagged1 = true;
        if ($m['id'] === $userTaggedId2) $foundTagged2 = true;
        if ($m['id'] === $userAuthorId) $foundAuthor = true; // should be excluded (cannot tag self)
        if ($m['id'] === $strangerUserId) $foundStranger = true; // should be excluded (foreign family)
    }

    assertTest('Tag Suggestions includes family members', $foundTagged1 && $foundTagged2, 'Family members not returned');
    assertTest('Tag Suggestions excludes caller (author)', !$foundAuthor, 'Author was returned in suggestions');
    assertTest('Tag Suggestions excludes foreign family strangers', !$foundStranger, 'Stranger was returned in suggestions');

    // TEST 2: Tag Suggestions with Search Query Filtering
    ob_start();
    $_GET['q'] = 'Bola';
    $tagController->searchTagSuggestions();
    $rawFiltered = ob_get_clean();
    $filteredData = json_decode((string)$rawFiltered, true);
    $filteredMembers = $filteredData['message']['members'] ?? $filteredData['data']['members'] ?? $filteredData['members'] ?? [];

    assertTest('Tag Suggestions search filters correctly', count($filteredMembers) === 1 && $filteredMembers[0]['id'] === $userTaggedId2, 'Search filter failed');

    // TEST 3: Sync Post Tags & Verification
    // Create a dummy post
    $stmtPost = $pdo->prepare("
        INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created)
        VALUES (?, 'Tunde Olaogun', 'Enjoying Sunday dinner with family!', ?, 'published', NOW())
    ");
    $stmtPost->execute([$userAuthorId, $famCode]);
    $testPostNo = (int)$pdo->lastInsertId();

    $syncedTags = TagController::syncPostTags(
        $testPostNo,
        $userAuthorId,
        [$userTaggedId1, $userTaggedId2],
        $famCode
    );

    assertTest('syncPostTags attaches tags to post', count($syncedTags) === 2, 'Expected 2 tags');

    // TEST 4: FeedRepository Batch Hydration
    $feedRepo = new FeedRepository($pdo);
    $feedPosts = $feedRepo->getFeedPostsAsArray($userAuthorId, [$famCode], 10, 0);

    $hydratedPost = null;
    foreach ($feedPosts as $p) {
        if ((int)$p['post_no'] === $testPostNo) {
            $hydratedPost = $p;
            break;
        }
    }

    assertTest('FeedRepository hydrates post_tags', !empty($hydratedPost['post_tags']) && count($hydratedPost['post_tags']) === 2, 'post_tags missing or empty');
    $tagNames = !empty($hydratedPost['post_tags']) ? array_column($hydratedPost['post_tags'], 'fullName') : [];
    assertTest('Hydrated tag fullNames match family members', in_array('Ade Olaogun', $tagNames) && in_array('Bola Olaogun', $tagNames), 'Tag names do not match');

    // TEST 5: Self-Sovereign Untagging
    // Switch session to tagged member Ade Olaogun
    $_SESSION['id'] = $userTaggedId1;
    $_SESSION['famCode'] = $famCode;

    ob_start();
    $tagController->removePostTag($testPostNo, $userTaggedId1);
    $removeOut = ob_get_clean();

    $postTagsAfterUntag = TagController::getPostTags($testPostNo);
    assertTest('Self-sovereign untagging removes user tag', count($postTagsAfterUntag) === 1 && $postTagsAfterUntag[0]['user_id'] === $userTaggedId2, 'Untagging failed');

    // TEST 6: Adversarial BOLA Untagging Defense
    // Attacker (Stranger) attempts to untag Bola Olaogun from Tunde's post
    $_SESSION['id'] = $strangerUserId;
    $_SESSION['famCode'] = 'FOREIGN_FAM_999';

    $blocked = false;
    try {
        $tagController->removePostTag($testPostNo, $userTaggedId2);
    } catch (\Src\Exceptions\HttpException $e) {
        $blocked = ($e->getCode() === 403);
    }

    $postTagsAfterAttack = TagController::getPostTags($testPostNo);
    assertTest('BOLA unauthorized untagging rejected (tag retained, 403 returned)', $blocked && count($postTagsAfterAttack) === 1, 'BOLA defense failed');

    // TEST 7: Adversarial IDOR Cross-Tenant Tagging Defense
    // Switch session back to Author
    $_SESSION['id'] = $userAuthorId;
    $_SESSION['famCode'] = $famCode;

    $idorTags = TagController::syncPostTags(
        $testPostNo,
        $userAuthorId,
        [$strangerUserId],
        $famCode
    );

    $hasStranger = false;
    foreach ($idorTags as $t) {
        if ($t['user_id'] === $strangerUserId) $hasStranger = true;
    }
    assertTest('IDOR Cross-tenant tagging rejected (stranger blocked)', !$hasStranger, 'Stranger was improperly tagged');

} finally {
    // Cleanup test fixtures
    try {
        if (!empty($testPostNo)) {
            $pdo->prepare("DELETE FROM post_tags WHERE post_no = ?")->execute([$testPostNo]);
            $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$testPostNo]);
        }
        $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?, ?)")->execute([
            $userAuthorId, $userTaggedId1, $userTaggedId2, $strangerUserId
        ]);
        $pdo->prepare("DELETE FROM notification WHERE receiver_id IN (?, ?, ?, ?)")->execute([
            $userAuthorId, $userTaggedId1, $userTaggedId2, $strangerUserId
        ]);
    } catch (\Throwable $cleanEx) {
        echo "Cleanup note: " . $cleanEx->getMessage() . "\n";
    }
}

echo "\n=================================================================\n";
echo "SUMMARY: {$passCount} Passed, {$failCount} Failed\n";
echo "=================================================================\n\n";

if ($failCount > 0) {
    exit(1);
}

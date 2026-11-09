<?php

declare(strict_types=1);

if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

require_once __DIR__ . '/../app/config/init.php';

use App\controller\PostShareController;
use Src\Db;

echo "=================================================================\n";
echo "🛡️  VERIFY POST SHARE (AUTHOR-ONLY & GUEST REDIRECT ENGINE)\n";
echo "=================================================================\n";

$failures = 0;
function assertShare(bool $cond, string $msg): void
{
    global $failures;
    if ($cond) {
        echo "  [PASS] {$msg}\n";
    } else {
        echo "  [FAIL] {$msg}\n";
        $failures++;
    }
}

$db = Db::connect2();

// 1. Prepare test fixtures (Author and Non-Author users, plus a Test Post)
$authorId = 'test_share_author_' . bin2hex(random_bytes(4));
$otherUserId = 'test_share_other_' . bin2hex(random_bytes(4));
$testFamCode = 'FAM' . strtoupper(bin2hex(random_bytes(3)));

// Insert a test post
$stmt = $db->prepare('INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created) VALUES (?, ?, ?, ?, ?, NOW())');
$stmt->execute([$authorId, 'Author Relative', 'Beautiful family picnic memories in the garden.', $testFamCode, 'published']);
$testPostNo = (int)$db->lastInsertId();

assertShare($testPostNo > 0, "Test post created with post_no = {$testPostNo}");

try {
    $controller = new PostShareController();

    // 2. Test Guest Redirection on /post/{postNo}
    $_SESSION = [];
    $_COOKIE = [];
    unset($_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_X_REQUESTED_WITH']);
    $GLOBALS['__testRedirect'] = null;

    $controller->handlePostLink($testPostNo);
    $redirectLoc = (string)($GLOBALS['__testRedirect'] ?? '');

    $hasRegisterRedirect = str_starts_with($redirectLoc, '/register') && str_contains($redirectLoc, 'famCode=' . $testFamCode);
    assertShare($hasRegisterRedirect, "Guest visitor to /post/{$testPostNo} is redirected to /register?famCode={$testFamCode}&redirect=... (Got: '{$redirectLoc}')");

    // 3. Test Authenticated User Redirection on /post/{postNo}
    $_SESSION['id'] = $authorId;
    $_SESSION['famCode'] = $testFamCode;
    $GLOBALS['__testRedirect'] = null;

    $controller->handlePostLink($testPostNo);
    $authRedirectLoc = (string)($GLOBALS['__testRedirect'] ?? '');

    $hasFeedRedirect = str_contains($authRedirectLoc, '/member/profilePage?post_no=' . $testPostNo);
    assertShare($authFeedRedirect = $hasFeedRedirect, "Authenticated visitor to /post/{$testPostNo} is redirected to /member/profilePage?post_no={$testPostNo}#post{$testPostNo} (Got: '{$authRedirectLoc}')");

    // 4. Test Author-Only API: Author can generate share link
    $_SESSION['id'] = $authorId;
    ob_start();
    $controller->sharePostApi($testPostNo);
    $authorResponseJson = ob_get_clean();

    $authorData = json_decode((string)$authorResponseJson, true);
    assertShare(
        is_array($authorData) && ($authorData['status'] ?? '') === 'success' && str_contains($authorData['data']['share_url'] ?? '', '/post/' . $testPostNo),
        "Author can generate post share link via POST /api/posts/{$testPostNo}/share"
    );

    // 5. Test Author-Only API: Non-Author is BLOCKED with 403 Forbidden
    $_SESSION['id'] = $otherUserId;
    http_response_code(200);
    ob_start();
    $controller->sharePostApi($testPostNo);
    $otherResponseJson = ob_get_clean();

    $otherData = json_decode((string)$otherResponseJson, true);
    assertShare(
        ($otherData['status'] ?? '') === 'error' && str_contains($otherData['message'] ?? '', 'Only the author can share this post'),
        "Non-author ({$otherUserId}) is blocked with 403 Forbidden when attempting to share post {$testPostNo}"
    );

    // 6. Test Unauthenticated caller to API: BLOCKED with 401 Unauthorised
    $_SESSION = [];
    $_COOKIE = [];
    ob_start();
    $controller->sharePostApi($testPostNo);
    $unauthResponseJson = ob_get_clean();

    $unauthData = json_decode((string)$unauthResponseJson, true);
    assertShare(
        ($unauthData['status'] ?? '') === 'error' && str_contains($unauthData['message'] ?? '', 'Unauthorised'),
        "Unauthenticated caller is blocked with 401 Unauthorised"
    );

} finally {
    // Cleanup fixture
    $db->prepare('DELETE FROM post WHERE post_no = ?')->execute([$testPostNo]);
}

if ($failures > 0) {
    echo "\n❌ Test suite failed with {$failures} failure(s).\n";
    exit(1);
}

echo "\n🏆 ALL AUTHOR-ONLY POST SHARE & GUEST REDIRECT TESTS PASSED (Exit Code: 0)!\n";
exit(0);

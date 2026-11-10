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

    // 2. Test Guest Landing Preview on /post/{postNo}
    $_SESSION = [];
    $_COOKIE = [];
    unset($_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_X_REQUESTED_WITH']);
    $GLOBALS['__testRedirect'] = null;
    $GLOBALS['__testView'] = null;

    $controller->handlePostLink($testPostNo);
    $guestView = $GLOBALS['__testView'] ?? [];

    assertShare(
        ($guestView['path'] ?? '') === 'public.postShareLanding',
        "Guest human visitor receives high-converting landing preview (public.postShareLanding)"
    );
    assertShare(
        !empty($guestView['data']['targetRedirectUrl']) && str_contains($guestView['data']['targetRedirectUrl'], 'famCode=' . $testFamCode),
        "Guest landing CTA contains pre-populated famCode={$testFamCode}"
    );

    // 2b. Test direct redirection flag (?direct=1)
    $_GET['direct'] = '1';
    $GLOBALS['__testRedirect'] = null;
    $controller->handlePostLink($testPostNo);
    $directRedirect = (string)($GLOBALS['__testRedirect'] ?? '');
    unset($_GET['direct']);

    assertShare(
        str_starts_with($directRedirect, '/register') && str_contains($directRedirect, 'famCode=' . $testFamCode),
        "Direct flag (?direct=1) performs 302 forward to register with famCode={$testFamCode}"
    );

    // 2c. Test signed share intent cookie verification
    $dummyPayload = json_encode(['post_no' => $testPostNo, 'famCode' => $testFamCode, 't' => time()]);
    $secret = (string)($_ENV['APP_KEY'] ?? 'familyPlatformIntentKey');
    $dummyCookie = hash_hmac('sha256', (string)$dummyPayload, $secret) . '.' . base64_encode((string)$dummyPayload);
    $parsedIntent = PostShareController::parseShareIntentCookie($dummyCookie);
    assertShare(
        is_array($parsedIntent) && $parsedIntent['post_no'] === $testPostNo && $parsedIntent['famCode'] === $testFamCode,
        "Signed fp_share_intent cookie is cryptographically verified and extracted"
    );

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

    // 7. Test Social Media Crawler detection & Dynamic Open Graph metadata
    $_SESSION = [];
    $_COOKIE = [];
    $_SERVER['HTTP_USER_AGENT'] = 'WhatsApp/2.23.20.76 i';
    $GLOBALS['__testRedirect'] = null;
    $GLOBALS['__testView'] = null;

    $controller->handlePostLink($testPostNo);
    $viewData = $GLOBALS['__testView'] ?? [];

    assertShare(
        PostShareController::isSocialCrawler('WhatsApp/2.23.20.76 i'),
        "WhatsApp crawler is correctly identified by isSocialCrawler()"
    );
    assertShare(
        PostShareController::isSocialCrawler('facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'),
        "Facebook external hit crawler is correctly identified by isSocialCrawler()"
    );
    assertShare(
        PostShareController::isSocialCrawler('Twitterbot/1.0'),
        "Twitterbot is correctly identified by isSocialCrawler()"
    );
    assertShare(
        !PostShareController::isSocialCrawler('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15'),
        "Regular mobile Safari browser is NOT treated as a crawler"
    );
    assertShare(
        ($viewData['path'] ?? '') === 'public.postShareLanding',
        "WhatsApp crawler receives rich Open Graph landing view (public.postShareLanding)"
    );
    assertShare(
        str_contains($viewData['data']['ogTitle'] ?? '', 'Author Relative'),
        "Open Graph title includes author name ('{$viewData['data']['ogTitle']}')"
    );
    assertShare(
        str_contains($viewData['data']['ogDescription'] ?? '', $testFamCode),
        "Open Graph description includes family circle ('{$viewData['data']['ogDescription']}')"
    );
    assertShare(
        !empty($viewData['data']['ogImage']),
        "Open Graph image is provided ('{$viewData['data']['ogImage']}')"
    );
    assertShare(
        $GLOBALS['__testRedirect'] === null,
        "Social crawlers are NEVER redirected away from the canonical post URL"
    );

    // 8. Test Video Post Sanitisation (Zero /resources/ leaked into user-facing copy)
    $videoPostNo = 0;
    $stmtVid = $db->prepare('INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmtVid->execute([$authorId, 'Laf OLAOGUN', '/resources/videos/posts/post_vid_test_12345.webm#t=0,30', $testFamCode, 'published']);
    $videoPostNo = (int)$db->lastInsertId();

    $GLOBALS['__testView'] = null;
    $controller->handlePostLink($videoPostNo);
    $vidViewData = $GLOBALS['__testView'] ?? [];

    assertShare(
        str_contains($vidViewData['data']['ogTitle'] ?? '', 'Laf OLAOGUN shared a family video memory'),
        "Video post OG title is cleanly humanised ('{$vidViewData['data']['ogTitle']}')"
    );
    assertShare(
        !str_contains($vidViewData['data']['ogTitle'] ?? '', '/resources/'),
        "Zero '/resources/' leaks in video post title"
    );
    assertShare(
        !str_contains($vidViewData['data']['ogDescription'] ?? '', '/resources/'),
        "Zero '/resources/' leaks in video post description"
    );

    $db->prepare('DELETE FROM post WHERE post_no = ?')->execute([$videoPostNo]);

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

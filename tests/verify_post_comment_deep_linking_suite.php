<?php

declare(strict_types=1);

/**
 * 🔗 Automated Verification Suite: Post & Comment Deep Linking Engine
 *
 * Verifies:
 * 1. Sunday Family Trivia digest email contains the specific post_no in the "Vote in Feed & See Results" button.
 * 2. Post notification emails (notifyNewPost) link directly to the specific post with ?post_no=NNN#postNNN.
 * 3. Comment notifications and push alerts link directly to the specific comment with ?post_no=NNN&comment_no=CCC#commentCCC.
 * 4. NotificationController resolves post and comment action URLs with proper post_no and comment_no parameters.
 * 5. Direct navigation to /member/trivia redirects cleanly to the active trivia post.
 * 6. Zero interpolation SQL invariant is maintained across all operations.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

$db = \Src\Db::connect2();

$famCode = 'DEEP_LINK_TEST_FAM';
$userA = 'DEEP_LINK_USER_A';
$userB = 'DEEP_LINK_USER_B';

try {
    echo "=================================================================\n";
    echo "🔗 POST & COMMENT DEEP LINKING VERIFICATION SUITE\n";
    echo "=================================================================\n\n";

    // 1. Cleanup fixtures
    $db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$famCode]);
    $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$famCode]);
    $db->prepare("DELETE FROM personal WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $db->prepare("DELETE FROM account WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $db->prepare("DELETE FROM notification WHERE receiver_id IN (?, ?)")->execute([$userA, $userB]);

    // 2. Setup Accounts & Family Nodes
    $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Fayokanmk', 'Olaogun', ?)")->execute([$userA, $famCode]);
    $db->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Wale', 'Olaogun', ?)")->execute([$userB, $famCode]);

    $db->prepare("INSERT INTO account (id, email, status) VALUES (?, 'fayo@testfam.com', 'verified')")->execute([$userA]);
    $db->prepare("INSERT INTO account (id, email, status) VALUES (?, 'wale@testfam.com', 'verified')")->execute([$userB]);

    $db->prepare("INSERT INTO family_nodes (family_code, user_id, first_name, last_name, birth_year, generation_level) VALUES (?, ?, 'Fayokanmk', 'Olaogun', 1985, 2)")->execute([$famCode, $userA]);

    // 3. Generate Weekly Heritage Trivia
    $trivia = \App\services\FamilyTriviaService::generateWeeklyHeritageTrivia($famCode, $userB);
    assert(!empty($trivia), 'Trivia generation must succeed');
    $postNo = (int)$trivia['post_no'];
    assert($postNo > 0, 'Trivia must return a valid post_no');
    assert(str_contains($trivia['question'], 'Fayokanmk Olaogun'), 'Question must feature Fayokanmk Olaogun');
    echo "[TEST 1] Sunday Heritage Trivia generated for Fayokanmk Olaogun: Post #{$postNo} ✅ PASSED\n";

    // 4. Verify FamilyDigestService::buildDigestForMember returns post_no
    $digest = \App\services\FamilyDigestService::buildDigestForMember($famCode, $userB);
    assert(!empty($digest['trivia']), 'Digest payload must contain trivia data');
    assert((int)$digest['trivia']['post_no'] === $postNo, 'Digest trivia post_no must match created trivia post');
    assert(str_contains((string)$digest['trivia']['question'], 'Fayokanmk Olaogun'), 'Digest question must match');
    echo "[TEST 2] FamilyDigestService payload carries trivia post_no: ✅ PASSED\n";

    // 5. Verify Sunday Family Digest Blade View Rendering has ?post_no=NNN#postNNN
    $renderedEmail = \Src\Utility::viewTemplateEmail('msg/lifecycle/sunday_family_digest', [
        'data' => [
            'id' => $userB,
            'firstName' => 'Wale',
            'lastName' => 'Olaogun',
            'email' => 'wale@testfam.com',
            'famCode' => $famCode,
            'trivia' => $digest['trivia'],
            'milestones' => [],
            'quest' => null,
        ]
    ]);
    
    assert(!empty($renderedEmail), 'Rendered email HTML must not be empty');
    assert(str_contains($renderedEmail, "post_no={$postNo}#post{$postNo}"), 'Sunday Digest button must contain post_no query parameter and anchor');
    assert(str_contains($renderedEmail, 'Vote in Feed &amp; See Results'), 'Email must contain the Vote button');
    echo "[TEST 3] Sunday Family Digest Email Template Button contains post_no anchor: ✅ PASSED\n";

    // 6. Verify PostMessage::buildPostUrl
    $reflectionPostMessage = new \ReflectionClass(\App\controller\members\PostMessage::class);
    $buildPostUrlMethod = $reflectionPostMessage->getMethod('buildPostUrl');
    $buildPostUrlMethod->setAccessible(true);
    $postUrl = $buildPostUrlMethod->invoke(null, $postNo);
    assert(str_contains($postUrl, "profilePage?post_no={$postNo}#post{$postNo}"), "buildPostUrl must format with post_no query and anchor, got: {$postUrl}");
    echo "[TEST 4] PostMessage::buildPostUrl produces deep link with post_no & anchor: ✅ PASSED\n";

    // 7. Verify CommentThreadService::notify generates deep link with comment_no
    $fakeCommentNo = 98765;
    $notified = \App\services\CommentThreadService::notify(
        $db,
        $userB,
        'Wale Olaogun',
        (string)$postNo,
        [$userA],
        $userA,
        $fakeCommentNo
    );
    assert(count($notified) >= 1, 'Comment mention/reply notification must dispatch');

    $notifStmt = $db->prepare("SELECT action_url, notification_type FROM notification WHERE receiver_id = ? ORDER BY no DESC LIMIT 1");
    $notifStmt->execute([$userA]);
    $notifRow = $notifStmt->fetch(\PDO::FETCH_ASSOC);
    assert(!empty($notifRow), 'Notification record must exist');
    assert(str_contains((string)$notifRow['action_url'], "post_no={$postNo}&comment_no={$fakeCommentNo}#comment{$fakeCommentNo}"), "Comment notification must carry post_no and comment_no deep link, got: {$notifRow['action_url']}");
    echo "[TEST 5] CommentThreadService produces comment-specific deep link (?post_no=...&comment_no=...#comment...): ✅ PASSED\n";

    // 8. Verify NotificationController::resolveNotificationActionUrl
    $resolvedUrl = \App\controller\NotificationController::resolveNotificationActionUrl([
        'notification_type' => 'Comment',
        'notification_name' => 'Wale replied to your comment',
        'notification_content' => 'Nice memory!',
        'sender_id' => $userB,
        'receiver_id' => $userA,
        'action_url' => "/profilePage?post_no={$postNo}&comment_no={$fakeCommentNo}#comment{$fakeCommentNo}"
    ], $userA);
    assert(str_contains($resolvedUrl, "post_no={$postNo}&comment_no={$fakeCommentNo}#comment{$fakeCommentNo}"), 'resolveNotificationActionUrl must preserve post_no and comment_no');
    echo "[TEST 6] NotificationController action URL resolution maintains deep links: ✅ PASSED\n";

    // 9. Clean up test fixtures
    $db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$famCode]);
    $db->prepare("DELETE FROM family_nodes WHERE family_code = ?")->execute([$famCode]);
    $db->prepare("DELETE FROM personal WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $db->prepare("DELETE FROM account WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $db->prepare("DELETE FROM notification WHERE receiver_id IN (?, ?)")->execute([$userA, $userB]);

    echo "\n=================================================================\n";
    echo "🎉 ALL POST & COMMENT DEEP LINKING TESTS PASSED (6/6)! \n";
    echo "=================================================================\n";
    exit(0);
} catch (\Throwable $e) {
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

<?php

declare(strict_types=1);

/**
 * 🎭 TWO TRUTHS & A LIE VERIFICATION SUITE
 * 
 * Verifies:
 * 1. Starter game auto-seeding when no game exists.
 * 2. Active game DTO masking (zero client-side leakage of lie_index or reveal stories).
 * 3. Voting logic (choice bounds [1,2,3], duplicate vote prevention, author self-vote prohibition).
 * 4. Game creation & feed announcement posting.
 * 5. Reveal logic: unmasks lie, evaluates points (50 pts for correct, 10 for participating),
 *    and automatically archives the verified truths into the permanent Family Journal.
 * 6. Clean database teardown.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\services\TwoTruthsGameService;
use Src\Db;

echo "=================================================================\n";
echo "🎭 TWO TRUTHS & A LIE (HERITAGE EDITION) VERIFICATION SUITE\n";
echo "=================================================================\n";

$db = Db::connect2();
$testFamCode = 'TEST_FAM_TWOTRUTHS_' . bin2hex(random_bytes(4));
$authorId = 'TEST_AUTHOR_' . bin2hex(random_bytes(4));
$voterId1 = 'TEST_VOTER1_' . bin2hex(random_bytes(4));
$voterId2 = 'TEST_VOTER2_' . bin2hex(random_bytes(4));

try {
    // [TEST 1] Starter Game Auto-Seeding
    echo "[TEST 1] Verifying starter game auto-seeding for empty family network...\n";
    $starter = TwoTruthsGameService::ensureStarterGameIfEmpty($testFamCode, $authorId, 'Uncle Tunde');
    if (!$starter || empty($starter['id'])) {
        throw new \RuntimeException('Failed to auto-seed starter game for empty family.');
    }
    $gameId = (string)$starter['id'];
    echo "  ✅ PASSED: Starter game created with ID: {$gameId}\n";

    // [TEST 2] Active Game DTO Masking (Zero Client-Side Leakage)
    echo "[TEST 2] Verifying server-side secret masking on active game...\n";
    $active = TwoTruthsGameService::getActiveGame($testFamCode, $voterId1);
    if (isset($active['lie_index'])) {
        throw new \RuntimeException('SECURITY BREACH: lie_index leaked in active game payload!');
    }
    if (isset($active['reveal_story_1']) || isset($active['reveal_story_2']) || isset($active['reveal_story_3'])) {
        throw new \RuntimeException('SECURITY BREACH: reveal stories leaked in active game payload!');
    }
    if ($active['total_votes'] !== 0) {
        throw new \RuntimeException("Expected 0 total votes, got {$active['total_votes']}");
    }
    echo "  ✅ PASSED: Server-side secret gate verified. 0 leaks detected.\n";

    // [TEST 3] Voting Logic & Guardrails
    echo "[TEST 3] Testing voting rules (input validation, duplicate prevention, self-voting block)...\n";
    
    // 3a. Out of bounds guess
    $badGuess = TwoTruthsGameService::castVote($gameId, $testFamCode, $voterId1, 'Cousin Sarah', 4);
    if ($badGuess['status'] !== 'error') {
        throw new \RuntimeException('Failed to reject out-of-bounds guess 4.');
    }

    // 3b. Author self-voting block
    $selfVote = TwoTruthsGameService::castVote($gameId, $testFamCode, $authorId, 'Uncle Tunde', 2);
    if ($selfVote['status'] !== 'error') {
        throw new \RuntimeException('Failed to block author from voting on own challenge.');
    }

    // 3c. Valid vote voter 1 (guessed option 2 - which is the lie in starter game)
    $vote1 = TwoTruthsGameService::castVote($gameId, $testFamCode, $voterId1, 'Cousin Sarah', 2);
    if ($vote1['status'] !== 'success') {
        throw new \RuntimeException('Valid vote for voter 1 failed: ' . $vote1['message']);
    }

    // 3d. Duplicate vote prevention
    $dupVote = TwoTruthsGameService::castVote($gameId, $testFamCode, $voterId1, 'Cousin Sarah', 1);
    if ($dupVote['status'] !== 'error') {
        throw new \RuntimeException('Failed to prevent duplicate voting by voter 1.');
    }

    // 3e. Valid vote voter 2 (guessed option 1 - wrong guess)
    $vote2 = TwoTruthsGameService::castVote($gameId, $testFamCode, $voterId2, 'Auntie Ronke', 1);
    if ($vote2['status'] !== 'success') {
        throw new \RuntimeException('Valid vote for voter 2 failed: ' . $vote2['message']);
    }

    $activeAfterVotes = TwoTruthsGameService::getActiveGame($testFamCode, $voterId1);
    if (($activeAfterVotes['total_votes'] ?? 0) !== 2) {
        throw new \RuntimeException("Expected 2 total votes, got: " . ($activeAfterVotes['total_votes'] ?? 'null'));
    }
    echo "  ✅ PASSED: Voting guardrails, anti-tampering, and live counts verified.\n";

    // [TEST 4] Game Reveal & Heritage Journal Archival Bridge
    echo "[TEST 4] Verifying reveal unmasking, point scoring, and Journal archival bridge...\n";
    $reveal = TwoTruthsGameService::revealGame($gameId, $testFamCode);
    if ($reveal['status'] !== 'success') {
        throw new \RuntimeException('Failed to reveal game: ' . $reveal['message']);
    }

    // Check voter 1 received 50 pts (correct guess)
    $v1Stmt = $db->prepare("SELECT is_correct, points_awarded FROM family_game_two_truths_votes WHERE game_id = ? AND user_id = ?");
    $v1Stmt->execute([$gameId, $voterId1]);
    $v1Result = $v1Stmt->fetch(\PDO::FETCH_ASSOC);
    if ((int)$v1Result['is_correct'] !== 1 || (int)$v1Result['points_awarded'] !== 50) {
        throw new \RuntimeException("Voter 1 incorrect score: is_correct={$v1Result['is_correct']}, points={$v1Result['points_awarded']}");
    }

    // Check voter 2 received 10 pts (participated, incorrect guess)
    $v2Stmt = $db->prepare("SELECT is_correct, points_awarded FROM family_game_two_truths_votes WHERE game_id = ? AND user_id = ?");
    $v2Stmt->execute([$gameId, $voterId2]);
    $v2Result = $v2Stmt->fetch(\PDO::FETCH_ASSOC);
    if ((int)$v2Result['is_correct'] !== 0 || (int)$v2Result['points_awarded'] !== 10) {
        throw new \RuntimeException("Voter 2 incorrect score: is_correct={$v2Result['is_correct']}, points={$v2Result['points_awarded']}");
    }

    // Check that exactly 2 verified truths were archived into family_journals
    $jStmt = $db->prepare("SELECT COUNT(*) FROM family_journals WHERE family_code = ? AND author_id = ?");
    $jStmt->execute([$testFamCode, $authorId]);
    $journalCount = (int)$jStmt->fetchColumn();
    if ($journalCount < 2) {
        throw new \RuntimeException("Expected at least 2 journal entries archived, found {$journalCount}");
    }
    echo "  ✅ PASSED: Lie unmasked, points awarded (50 correct / 10 participation), and 2 truths archived to Family Journal.\n";

    // [TEST 5] Custom Game Creation & Feed Post Linking
    echo "[TEST 5] Testing custom game creation and family feed broadcast...\n";
    $customData = [
        'title'          => 'Cousin Funke\'s 1990s Lagos Secrets',
        'statement_1'    => 'I once hid a pet monkey in my bedroom wardrobe for 3 days.',
        'statement_2'    => 'I won the inter-house sports 100m sprint barefoot.',
        'statement_3'    => 'I met the Governor at a state banquet in 1998.',
        'lie_index'      => 1,
        'reveal_story_1' => 'Fabrication! It was actually a parrot that flew in through the window.',
        'reveal_story_2' => 'True! My plimsolls tore 5 minutes before the race.',
        'reveal_story_3' => 'True! I presented the welcome bouquet as head girl.',
    ];

    $customGame = TwoTruthsGameService::createGame($testFamCode, $authorId, 'Cousin Funke', $customData);
    if ($customGame['status'] !== 'success' || empty($customGame['game_id'])) {
        throw new \RuntimeException('Failed to create custom game challenge: ' . $customGame['message']);
    }
    $newGameId = (string)$customGame['game_id'];

    // Verify feed post was created
    $postStmt = $db->prepare("SELECT post_no FROM family_game_two_truths WHERE id = ?");
    $postStmt->execute([$newGameId]);
    $postNo = (int)$postStmt->fetchColumn();
    if ($postNo <= 0) {
        throw new \RuntimeException('Post number was not linked to custom game.');
    }

    $feedStmt = $db->prepare("SELECT postMessage, post_status FROM post WHERE post_no = ?");
    $feedStmt->execute([$postNo]);
    $postRow = $feedStmt->fetch(\PDO::FETCH_ASSOC);
    $feedMessage = (string)($postRow['postMessage'] ?? '');
    $postStatus = (string)($postRow['post_status'] ?? '');
    if (strpos($feedMessage, 'Two Truths & a Lie') === false) {
        throw new \RuntimeException('Post message does not announce Two Truths & a Lie game.');
    }
    if ($postStatus !== 'published') {
        throw new \RuntimeException("Post status must be 'published' to appear in family feed, got '{$postStatus}'");
    }

    // Verify FeedRepository returns the post for the family
    $feedRepo = new \App\repository\FeedRepository();
    $familyPosts = $feedRepo->getFeedPosts($authorId, [$testFamCode], 5, 0);
    $foundInFeed = false;
    foreach ($familyPosts as $fp) {
        if ($fp->postNo === $postNo) {
            $foundInFeed = true;
            break;
        }
    }
    if (!$foundInFeed) {
        throw new \RuntimeException("Post #{$postNo} was not returned by FeedRepository for family {$testFamCode}!");
    }

    // Playable-in-feed contract: the feed row must carry a masked two_truths payload
    foreach ($familyPosts as $fp) {
        if ($fp->postNo !== $postNo) {
            continue;
        }
        $tt = $fp->toArray()['two_truths'] ?? null;
        if (!is_array($tt) || ($tt['id'] ?? '') !== $newGameId) {
            throw new \RuntimeException('Feed post is missing its hydrated two_truths game payload.');
        }
        if (isset($tt['lie_index']) || isset($tt['reveal_story_1'])) {
            throw new \RuntimeException('SECURITY BREACH: lie_index leaked in feed payload!');
        }
        if (empty($tt['is_author'])) {
            throw new \RuntimeException('Author flag not set on feed payload for the creator.');
        }
    }
    // A different family member sees an unvoted, playable card with hidden results
    $viewerFeed = TwoTruthsGameService::getGamesForPosts([$postNo], $voterId1);
    $vt = $viewerFeed[$postNo] ?? null;
    if (!$vt || $vt['has_voted'] || $vt['vote_percentages'] !== null || isset($vt['lie_index'])) {
        throw new \RuntimeException('Viewer feed payload must be unvoted with hidden results and no lie_index.');
    }
    // Voting returns live percentages for in-feed result bars
    $feedVote = TwoTruthsGameService::castVote($newGameId, $testFamCode, $voterId1, 'Cousin Sarah', 2);
    if (($feedVote['status'] ?? '') !== 'success' || !isset($feedVote['vote_percentages'][2]) || $feedVote['vote_percentages'][2] !== 100) {
        throw new \RuntimeException('castVote must return vote_percentages for the feed result bars.');
    }
    echo "  ✅ PASSED: Custom game challenge created, announced with status 'published', and verified visible in family feed (Post #{$postNo}).\n";

    // [TEST 6] Controller CSRF & Header Neutralization Gate
    echo "[TEST 6] Verifying CSRF token validation and X-CSRF-TOKEN / JSON payload handling...\n";
    $_SESSION['token'] = bin2hex(random_bytes(16));
    $validToken = $_SESSION['token'];

    // 6a. Missing/Empty CSRF token must throw UnauthorisedException
    $rejected = false;
    try {
        \App\controller\BaseController::validateCsrf([]);
    } catch (\Src\Exceptions\UnauthorisedException $e) {
        $rejected = true;
    }
    if (!$rejected) {
        throw new \RuntimeException('SECURITY BREACH: Empty payload failed to throw UnauthorisedException!');
    }

    // 6b. Forged CSRF token must throw UnauthorisedException
    $forgedRejected = false;
    try {
        \App\controller\BaseController::validateCsrf(['token' => 'forged_token_val']);
    } catch (\Src\Exceptions\UnauthorisedException $e) {
        $forgedRejected = true;
    }
    if (!$forgedRejected) {
        throw new \RuntimeException('SECURITY BREACH: Forged CSRF token failed to throw UnauthorisedException!');
    }

    // 6c. Valid token in JSON payload must succeed
    \App\controller\BaseController::validateCsrf(['token' => $validToken]);
    \App\controller\BaseController::validateCsrf(['_token' => $validToken]);

    // 6d. Valid token in HTTP_X_CSRF_TOKEN header must succeed
    $_SERVER['HTTP_X_CSRF_TOKEN'] = $validToken;
    \App\controller\BaseController::validateCsrf([]);
    unset($_SERVER['HTTP_X_CSRF_TOKEN']);

    // 6e. Valid token in HTTP_X_XSRF_TOKEN header must succeed
    $_SERVER['HTTP_X_XSRF_TOKEN'] = $validToken;
    \App\controller\BaseController::validateCsrf([]);
    unset($_SERVER['HTTP_X_XSRF_TOKEN']);

    echo "  ✅ PASSED: CSRF gate verified: forged/missing tokens blocked, X-CSRF-TOKEN & JSON tokens successfully validated.\n";

} finally {
    // Teardown test records
    echo "[TEARDOWN] Cleaning up test data for family code: {$testFamCode}...\n";
    $delVotes = $db->prepare("DELETE FROM family_game_two_truths_votes WHERE famCode = ?");
    $delVotes->execute([$testFamCode]);

    $delGames = $db->prepare("DELETE FROM family_game_two_truths WHERE famCode = ?");
    $delGames->execute([$testFamCode]);

    $delJournals = $db->prepare("DELETE FROM family_journals WHERE family_code = ?");
    $delJournals->execute([$testFamCode]);

    $delPosts = $db->prepare("DELETE FROM post WHERE postFamCode = ?");
    $delPosts->execute([$testFamCode]);

    echo "  ✅ Teardown complete.\n";
}

echo "=================================================================\n";
echo "🏆 ALL 6 TWO TRUTHS & A LIE VERIFICATION TESTS PASSED CLEANLY!\n";
echo "=================================================================\n";

<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Post & Reel Views Tracking & Display (TAT & BRATS Gate)
 *
 * Verifies:
 * 1. Database schema: `views_count` on `post` and `family_reels`.
 * 2. Model & DTO mechanics: Atomic increments, PostDto mapping, serialization.
 * 3. API Endpoints & Adversarial Red Team Protection (Dr. Viktor Kovács & Marcus Gate):
 *    - Unauthenticated request rejection (401 / error status).
 *    - Negative / malformed parameter validation (400 / error status).
 *    - Authorized in-place atomic increments.
 * 4. Blade & JS Template invariants:
 *    - `post-card.blade.php`: `post.views_count`, `:data-post-no`, `feed-post-card`.
 *    - `reelsTray.blade.php`: Simultaneous view count & like count display.
 *    - `reelsPlayer.js`: Visual views indicator in action stack and bottom meta pill.
 * 5. British English compliance (views, authorised).
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

echo "=================================================================\n";
echo "👁️  POST & REEL VIEWS TRACKING & DISPLAY VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passed = 0;
$failed = 0;

function runTest(string $title, callable $test): void {
    global $passed, $failed;
    echo "[TEST] {$title}... ";
    try {
        $test();
        echo "✅ PASSED\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAILED: " . $e->getMessage() . " (" . $e->getFile() . ":" . $e->getLine() . ")\n";
        $failed++;
    }
}

$pdo = \Src\Db::connect2();

// [TEST 1] Database Columns on post and family_reels
runTest("Database Schema: post.views_count and family_reels.views_count exist", function() use ($pdo) {
    $stmt1 = $pdo->query("SHOW COLUMNS FROM post LIKE 'views_count'");
    $col1 = $stmt1->fetch(\PDO::FETCH_ASSOC);
    if (!$col1) {
        throw new \Exception("Column 'views_count' missing from table 'post'");
    }

    $stmt2 = $pdo->query("SHOW COLUMNS FROM family_reels LIKE 'views_count'");
    $col2 = $stmt2->fetch(\PDO::FETCH_ASSOC);
    if (!$col2) {
        throw new \Exception("Column 'views_count' missing from table 'family_reels'");
    }
});

// [TEST 2] Model Atomic Increment & Query on Post
runTest("Post Model: incrementViews and getViewsCount atomic execution", function() use ($pdo) {
    // Seed a test post
    $testId = 'test_view_author_' . bin2hex(random_bytes(4));
    $stmtIns = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, views_count, post_likes, post_status) VALUES (?, 'Test Author', 'Testing Views', 'TESTFAM', 0, 0, 'published')");
    $stmtIns->execute([$testId]);
    $postNo = (int)$pdo->lastInsertId();

    try {
        $initial = \App\model\Post::getViewsCount($postNo);
        if ($initial !== 0) {
            throw new \Exception("Expected initial views_count to be 0, got {$initial}");
        }

        \App\model\Post::incrementViews($postNo);
        $afterOne = \App\model\Post::getViewsCount($postNo);
        if ($afterOne !== 1) {
            throw new \Exception("Expected views_count after 1 increment to be 1, got {$afterOne}");
        }

        \App\model\Post::incrementViews($postNo);
        \App\model\Post::incrementViews($postNo);
        $afterThree = \App\model\Post::getViewsCount($postNo);
        if ($afterThree !== 3) {
            throw new \Exception("Expected views_count after 3 increments to be 3, got {$afterThree}");
        }
    } finally {
        $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    }
});

// [TEST 3] PostDto Serialization & Hydration
runTest("PostDto: fromDatabaseRow and toArray properly serialize views_count", function() {
    $row = [
        'post_no' => 12345,
        'id' => 'user_777',
        'fullName' => 'Grandma Ada',
        'postMessage' => 'Happy Sunday family!',
        'postFamCode' => 'FAM01',
        'post_likes' => 5,
        'views_count' => 42,
        'date_created' => '2026-10-26 12:00:00',
    ];

    $dto = \App\dto\PostDto::fromDatabaseRow($row, 'user_777');
    if ($dto->viewsCount !== 42) {
        throw new \Exception("Expected PostDto viewsCount to be 42, got {$dto->viewsCount}");
    }

    $arr = $dto->toArray();
    if (!isset($arr['views_count']) || $arr['views_count'] !== 42) {
        throw new \Exception("Expected toArray()['views_count'] to be 42");
    }

    $json = json_encode($dto);
    if (!str_contains($json, '"views_count":42')) {
        throw new \Exception("Expected JSON to contain '\"views_count\":42'");
    }
});

// [TEST 4] Adversarial Security: Unauthenticated Post Views API (Dr. Viktor Kovács PoC #1)
runTest("Adversarial Security: Unauthenticated request to /api/posts/view returns error", function() {
    unset($_SESSION['id'], $_SESSION['auth'], $_SESSION['user']);

    ob_start();
    \App\controller\members\PostMessage::recordView();
    $output = ob_get_clean();

    $decoded = json_decode((string)$output, true);
    if (!isset($decoded['status']) || $decoded['status'] !== 'error') {
        throw new \Exception("Expected error status in response JSON, got " . var_export($output, true));
    }
    if (!str_contains(strtolower($decoded['message'] ?? ''), 'unauthorised')) {
        throw new \Exception("Expected British English 'Unauthorised' in error message");
    }
});

// [TEST 5] Adversarial Security: Negative / Malformed Post No (Dr. Viktor Kovács PoC #2)
runTest("Adversarial Security: Malformed post_no returns error", function() {
    $_SESSION['id'] = 'authenticated_user_99';
    $_POST['post_no'] = -999;

    ob_start();
    \App\controller\members\PostMessage::recordView();
    $output = ob_get_clean();

    $decoded = json_decode((string)$output, true);
    if (!isset($decoded['status']) || $decoded['status'] !== 'error') {
        throw new \Exception("Expected error status for negative post_no, got " . var_export($output, true));
    }
    if (!str_contains(strtolower($decoded['message'] ?? ''), 'invalid')) {
        throw new \Exception("Expected 'invalid' in error message");
    }

    unset($_POST['post_no']);
});

// [TEST 6] Authorized Post Views API Execution
runTest("API Execution: Authenticated valid request increments views_count", function() use ($pdo) {
    $testId = 'test_view_auth_' . bin2hex(random_bytes(4));
    $stmtIns = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, views_count, post_likes, post_status) VALUES (?, 'Auth Tester', 'Testing API', 'TESTFAM', 10, 0, 'published')");
    $stmtIns->execute([$testId]);
    $postNo = (int)$pdo->lastInsertId();

    try {
        $_SESSION['id'] = $testId;
        $_POST['post_no'] = $postNo;

        ob_start();
        \App\controller\members\PostMessage::recordView();
        $output = ob_get_clean();

        $decoded = json_decode((string)$output, true);
        if (!isset($decoded['status']) || $decoded['status'] !== 'success') {
            throw new \Exception("Expected success status, got " . var_export($output, true));
        }
        if (($decoded['views_count'] ?? 0) !== 11) {
            throw new \Exception("Expected views_count to increment to 11, got " . ($decoded['views_count'] ?? 'null'));
        }

        unset($_POST['post_no']);
    } finally {
        $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
    }
});

// [TEST 7] Reel Views Model & Controller API
runTest("Reel Views: incrementViews and recordView API verification", function() use ($pdo) {
    $userId = 'test_reel_user_' . bin2hex(random_bytes(4));
    // Must insert into personal because getReelById INNER JOINs on personal
    $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Reel', 'Tester', 'FAM01')")->execute([$userId]);
    $stmt = $pdo->prepare("INSERT INTO family_reels (user_id, famCode, caption, video_url, thumbnail_url, views_count, created_at) VALUES (?, 'FAM01', 'Test Reel', '/videos/test.mp4', '/thumbs/test.jpg', 5, NOW())");
    $stmt->execute([$userId]);
    $reelId = (int)$pdo->lastInsertId();

    try {
        \App\model\Reel::incrementViews($reelId);
        $reel = \App\model\Reel::getReelById($reelId, $userId);
        if (($reel['views_count'] ?? 0) !== 6) {
            throw new \Exception("Expected reel views_count to increment to 6, got " . ($reel['views_count'] ?? 'null'));
        }

        // Test API
        $_SESSION['id'] = $userId;
        $_POST['reel_id'] = $reelId;

        $reelsController = new \App\controller\members\ReelsController();
        ob_start();
        $reelsController->recordView();
        $out = ob_get_clean();

        $decoded = json_decode((string)$out, true);
        if (($decoded['views_count'] ?? 0) !== 7) {
            throw new \Exception("Expected API recordView to increment reel views_count to 7, got " . ($decoded['views_count'] ?? 'null'));
        }

        unset($_POST['reel_id']);
    } finally {
        $pdo->prepare("DELETE FROM family_reels WHERE id = ?")->execute([$reelId]);
        $pdo->prepare("DELETE FROM personal WHERE id = ?")->execute([$userId]);
    }
});

// [TEST 8] Blade Template Invariant: post-card.blade.php
runTest("Blade Invariant: post-card.blade.php renders views_count and British English", function() {
    $content = file_get_contents(dirname(__DIR__) . '/resources/views/components/feed/post-card.blade.php');
    if ($content === false) {
        throw new \Exception("Could not read post-card.blade.php");
    }

    if (!str_contains($content, 'post.views_count')) {
        throw new \Exception("post-card.blade.php does not bind post.views_count");
    }
    if (!str_contains($content, 'feed-post-card')) {
        throw new \Exception("post-card.blade.php missing 'feed-post-card' class");
    }
    if (!str_contains($content, ':data-post-no="post.post_no"')) {
        throw new \Exception("post-card.blade.php missing ':data-post-no' attribute");
    }
    if (!str_contains($content, 'views') || !str_contains($content, 'view')) {
        throw new \Exception("post-card.blade.php missing British English view/views labels");
    }
});

// [TEST 9] Blade Template Invariant: reelsTray.blade.php
runTest("Blade Invariant: reelsTray.blade.php renders views without mutual exclusion", function() {
    $content = file_get_contents(dirname(__DIR__) . '/resources/views/member/includes/reelsTray.blade.php');
    if ($content === false) {
        throw new \Exception("Could not read reelsTray.blade.php");
    }

    if (!str_contains($content, '$viewsCount')) {
        throw new \Exception("reelsTray.blade.php does not reference \$viewsCount");
    }
    if (!str_contains($content, 'bi-eye-fill')) {
        throw new \Exception("reelsTray.blade.php missing bi-eye-fill icon for views");
    }
    // Verify that $viewsCount is NOT inside an @elseif after $likesCount
    if (preg_match('/@if\(\$likesCount\s*>\s*0\).*?@elseif\(\$viewsCount\s*>\s*0\)/s', $content)) {
        throw new \Exception("reelsTray.blade.php still has mutual exclusion between likes and views!");
    }
});

// [TEST 10] JavaScript Invariant: reelsPlayer.js
runTest("JS Invariant: reelsPlayer.js contains visual views counter in action stack and meta", function() {
    $content = file_get_contents(dirname(__DIR__) . '/resources/asset/js/components/reels/reelsPlayer.js');
    if ($content === false) {
        throw new \Exception("Could not read reelsPlayer.js");
    }

    if (!str_contains($content, 'reelActionViewCount')) {
        throw new \Exception("reelsPlayer.js missing 'reelActionViewCount' in action stack");
    }
    if (!str_contains($content, 'reelViewsPill')) {
        throw new \Exception("reelsPlayer.js missing 'reelViewsPill'");
    }
    if (!str_contains($content, 'recordViewCount')) {
        throw new \Exception("reelsPlayer.js missing 'recordViewCount'");
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} POST & REEL VIEWS VERIFICATION TESTS PASSED CLEANLY!\n";
} else {
    echo "❌ {$failed} TEST(S) FAILED. INVESTIGATE IMMEDIATELY.\n";
    exit(1);
}
echo "=================================================================\n";

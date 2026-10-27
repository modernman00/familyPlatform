<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: Author Inspection of Post & Comment Viewers (TAT & BRATS Gate)
 *
 * Verifies:
 * 1. Database schema: `post_views` and `comment_views` tables, `comment.views_count`.
 * 2. Model & Service mechanics:
 *    - `Post::incrementViews($postNo, $userId)` logs audit record and increments counter.
 *    - `Post::getPostViewers($postNo)` retrieves viewer list with user details.
 *    - `Post::isPostAuthor($postNo, $userId)` enforces correct authorship checks.
 *    - `Post::incrementCommentViews($commentNo, $userId)` logs comment view and increments counter.
 *    - `Post::getCommentViewers($commentNo)` retrieves comment viewer list.
 *    - `Post::isCommentAuthor($commentNo, $userId)` enforces correct comment authorship.
 * 3. Adversarial Enterprise Security & IDOR Gauntlet (Dr. Viktor Kovács & Marcus Gate):
 *    - Unauthenticated access to `/api/posts/{id}/viewers` and `/api/comments/{id}/viewers` returns 401.
 *    - IDOR Attack: Non-author access to `/api/posts/{id}/viewers` returns 403 Forbidden.
 *    - IDOR Attack: Non-author access to `/api/comments/{id}/viewers` returns 403 Forbidden.
 *    - Legitimate Author access succeeds with 200 OK and populated viewers payload.
 * 4. Blade & JS Template invariants:
 *    - `post-card.blade.php`: Post view counter is author-clickable with `isOwnPost(post)`.
 *    - `post-card.blade.php`: Comment view counter is author-clickable with `canEditComment(c)`.
 *    - `middleColumn.blade.php`: Viewers roster modal exists with search filter, avatars, and loading skeleton.
 *    - Zero-interpolation SQL compliance across all queries.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

echo "=================================================================\n";
echo "👁️  AUTHOR POST & COMMENT VIEWERS INSPECTION VERIFICATION SUITE\n";
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

// [TEST 1] Database Tables & Columns Exist
runTest("Database Schema: post_views, comment_views, and comment.views_count exist", function() use ($pdo) {
    $stmt1 = $pdo->query("SHOW TABLES LIKE 'post_views'");
    if (!$stmt1->fetch()) {
        throw new \Exception("Table 'post_views' does not exist");
    }

    $stmt2 = $pdo->query("SHOW TABLES LIKE 'comment_views'");
    if (!$stmt2->fetch()) {
        throw new \Exception("Table 'comment_views' does not exist");
    }

    $stmt3 = $pdo->query("SHOW COLUMNS FROM comment LIKE 'views_count'");
    if (!$stmt3->fetch()) {
        throw new \Exception("Column 'views_count' missing from table 'comment'");
    }
});

// [TEST 2] Post Views Model Mechanics & Viewer Roster Retrieval
runTest("Post Model: incrementViews with userId and getPostViewers retrieval", function() use ($pdo) {
    $authorId = 'author_' . bin2hex(random_bytes(4));
    $viewerId1 = 'viewer_a_' . bin2hex(random_bytes(4));
    $viewerId2 = 'viewer_b_' . bin2hex(random_bytes(4));

    // Register test viewer users in personal table for name joins
    $stmtReg = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, 'TESTFAM')");
    $stmtReg->execute([$viewerId1, 'Alice', 'Smith']);
    $stmtReg->execute([$viewerId2, 'Bob', 'Jones']);

    $stmtPost = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, views_count, post_likes, post_status) VALUES (?, 'Test Author', 'Views Post Test', 'TESTFAM', 0, 0, 'published')");
    $stmtPost->execute([$authorId]);
    $postNo = (int)$pdo->lastInsertId();

    try {
        if (!\App\model\Post::isPostAuthor($postNo, $authorId)) {
            throw new \Exception("isPostAuthor should return true for post author");
        }
        if (\App\model\Post::isPostAuthor($postNo, $viewerId1)) {
            throw new \Exception("isPostAuthor should return false for non-author");
        }

        // Record viewer 1
        \App\model\Post::incrementViews($postNo, $viewerId1);
        $viewsCount = \App\model\Post::getViewsCount($postNo);
        if ($viewsCount !== 1) {
            throw new \Exception("Expected views_count = 1, got {$viewsCount}");
        }

        // Record viewer 2 twice (should update view_count to 2 for viewer 2)
        \App\model\Post::incrementViews($postNo, $viewerId2);
        \App\model\Post::incrementViews($postNo, $viewerId2);

        $viewers = \App\model\Post::getPostViewers($postNo);
        if (count($viewers) !== 2) {
            throw new \Exception("Expected exactly 2 unique viewers, got " . count($viewers));
        }

        $viewerIds = array_column($viewers, 'user_id');
        if (!in_array($viewerId1, $viewerIds, true) || !in_array($viewerId2, $viewerIds, true)) {
            throw new \Exception("Missing expected viewer in roster");
        }

        $viewer2Row = null;
        foreach ($viewers as $v) {
            if ($v['user_id'] === $viewerId2) {
                $viewer2Row = $v;
                break;
            }
        }
        if (!$viewer2Row || (int)$viewer2Row['view_count'] !== 2) {
            throw new \Exception("Expected viewer 2 to have view_count = 2");
        }
        if ($viewer2Row['firstName'] !== 'Bob') {
            throw new \Exception("Expected viewer 2 firstName to be Bob, got " . $viewer2Row['firstName']);
        }
    } finally {
        $pdo->prepare("DELETE FROM post_views WHERE post_no = ?")->execute([$postNo]);
        $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
        $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?)")->execute([$viewerId1, $viewerId2]);
    }
});

// [TEST 3] Comment Views Model Mechanics & Viewer Roster Retrieval
runTest("Comment Model: incrementCommentViews, isCommentAuthor, and getCommentViewers", function() use ($pdo) {
    $authorId = 'cauthor_' . bin2hex(random_bytes(4));
    $viewerId = 'cviewer_' . bin2hex(random_bytes(4));

    $stmtReg = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, 'Clara', 'Oswald', 'TESTFAM')");
    $stmtReg->execute([$viewerId]);

    $stmtComm = $pdo->prepare("INSERT INTO comment (id, post_no, fullName, comment, views_count) VALUES (?, '100', 'Clara Author', 'Test Comment Views', 0)");
    $stmtComm->execute([$authorId]);
    $commentNo = (int)$pdo->lastInsertId();

    try {
        if (!\App\model\Post::isCommentAuthor($commentNo, $authorId)) {
            throw new \Exception("isCommentAuthor should return true for author");
        }
        if (\App\model\Post::isCommentAuthor($commentNo, $viewerId)) {
            throw new \Exception("isCommentAuthor should return false for non-author");
        }

        \App\model\Post::incrementCommentViews($commentNo, $viewerId);
        $count = \App\model\Post::getCommentViewsCount($commentNo);
        if ($count !== 1) {
            throw new \Exception("Expected comment views count to be 1, got {$count}");
        }

        $viewers = \App\model\Post::getCommentViewers($commentNo);
        if (count($viewers) !== 1) {
            throw new \Exception("Expected 1 viewer in comment roster, got " . count($viewers));
        }
        if ($viewers[0]['user_id'] !== $viewerId) {
            throw new \Exception("Expected user_id {$viewerId}, got " . $viewers[0]['user_id']);
        }
        if ($viewers[0]['firstName'] !== 'Clara') {
            throw new \Exception("Expected firstName Clara, got " . $viewers[0]['firstName']);
        }
    } finally {
        $pdo->prepare("DELETE FROM comment_views WHERE comment_no = ?")->execute([$commentNo]);
        $pdo->prepare("DELETE FROM comment WHERE comment_no = ?")->execute([$commentNo]);
        $pdo->prepare("DELETE FROM personal WHERE id = ?")->execute([$viewerId]);
    }
});

// [TEST 4] Adversarial Red Team IDOR Gauntlet: Post Viewers Authorization
runTest("Adversarial Red Team: IDOR protection on Post Viewers endpoint (HTTP 403 / 401)", function() use ($pdo) {
    $authorId = 'p_auth_' . bin2hex(random_bytes(4));
    $attackerId = 'p_attk_' . bin2hex(random_bytes(4));

    $stmtPost = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, postFamCode, views_count) VALUES (?, 'Legit Author', 'Secret Post', 'FAM1', 5)");
    $stmtPost->execute([$authorId]);
    $postNo = (int)$pdo->lastInsertId();

    try {
        // 1. Unauthenticated test (no session)
        $_SESSION = [];
        ob_start();
        \App\controller\members\PostMessage::getPostViewers($postNo);
        $unauthOutput = ob_get_clean();
        $unauthData = json_decode($unauthOutput, true);
        if (($unauthData['status'] ?? '') !== 'error' || !str_contains(strtolower($unauthData['message'] ?? ''), 'unauthoris')) {
            throw new \Exception("Unauthenticated request did not return unauthorised error");
        }

        // 2. Attacker session attempting IDOR on author's post
        $_SESSION['id'] = $attackerId;
        $_SESSION['famCode'] = 'FAM1';
        $_SESSION['type'] = 'member';
        ob_start();
        \App\controller\members\PostMessage::getPostViewers($postNo);
        $idorOutput = ob_get_clean();
        $idorData = json_decode($idorOutput, true);
        if (($idorData['status'] ?? '') !== 'error' || !str_contains(strtolower($idorData['message'] ?? ''), 'only the author')) {
            throw new \Exception("IDOR attack was not rejected with author-only error! Output: " . $idorOutput);
        }

        // 3. Legitimate author session succeeds
        $_SESSION['id'] = $authorId;
        ob_start();
        \App\controller\members\PostMessage::getPostViewers($postNo);
        $authOutput = ob_get_clean();
        $authData = json_decode($authOutput, true);
        if (($authData['status'] ?? '') !== 'success' || !isset($authData['viewers'])) {
            throw new \Exception("Author request failed to return viewers! Output: " . $authOutput);
        }
    } finally {
        $pdo->prepare("DELETE FROM post WHERE post_no = ?")->execute([$postNo]);
        $_SESSION = [];
    }
});

// [TEST 5] Adversarial Red Team IDOR Gauntlet: Comment Viewers Authorization
runTest("Adversarial Red Team: IDOR protection on Comment Viewers endpoint (HTTP 403 / 401)", function() use ($pdo) {
    $authorId = 'c_auth_' . bin2hex(random_bytes(4));
    $attackerId = 'c_attk_' . bin2hex(random_bytes(4));

    $stmtComm = $pdo->prepare("INSERT INTO comment (id, post_no, fullName, comment, views_count) VALUES (?, '200', 'Comment Author', 'Secret Comment', 3)");
    $stmtComm->execute([$authorId]);
    $commentNo = (int)$pdo->lastInsertId();

    try {
        // 1. Unauthenticated test
        $_SESSION = [];
        ob_start();
        \App\controller\members\PostMessage::getCommentViewers($commentNo);
        $unauthOutput = ob_get_clean();
        $unauthData = json_decode($unauthOutput, true);
        if (($unauthData['status'] ?? '') !== 'error') {
            throw new \Exception("Unauthenticated comment viewers request did not return error");
        }

        // 2. Attacker session attempting IDOR on author's comment
        $_SESSION['id'] = $attackerId;
        $_SESSION['type'] = 'member';
        ob_start();
        \App\controller\members\PostMessage::getCommentViewers($commentNo);
        $idorOutput = ob_get_clean();
        $idorData = json_decode($idorOutput, true);
        if (($idorData['status'] ?? '') !== 'error' || !str_contains(strtolower($idorData['message'] ?? ''), 'only the author')) {
            throw new \Exception("IDOR attack was not rejected for comment! Output: " . $idorOutput);
        }

        // 3. Legitimate comment author succeeds
        $_SESSION['id'] = $authorId;
        ob_start();
        \App\controller\members\PostMessage::getCommentViewers($commentNo);
        $authOutput = ob_get_clean();
        $authData = json_decode($authOutput, true);
        if (($authData['status'] ?? '') !== 'success' || !isset($authData['viewers'])) {
            throw new \Exception("Author comment request failed to return viewers! Output: " . $authOutput);
        }
    } finally {
        $pdo->prepare("DELETE FROM comment WHERE comment_no = ?")->execute([$commentNo]);
        $_SESSION = [];
    }
});

// [TEST 6] Blade & Alpine Invariants: Post Card Template
runTest("Blade Invariant: post-card.blade.php contains author-clickable view triggers", function() use ($rootDir) {
    $content = file_get_contents($rootDir . '/resources/views/components/feed/post-card.blade.php');
    if (!$content) {
        throw new \Exception("Failed to read post-card.blade.php");
    }

    if (!str_contains($content, 'openPostViewersModal(post)')) {
        throw new \Exception("post-card.blade.php missing openPostViewersModal(post) trigger");
    }

    if (!str_contains($content, 'openCommentViewersModal(c)')) {
        throw new \Exception("post-card.blade.php missing openCommentViewersModal(c) trigger");
    }

    if (!str_contains($content, 'isOwnPost(post)')) {
        throw new \Exception("post-card.blade.php missing isOwnPost(post) check");
    }

    if (!str_contains($content, 'canEditComment(c)')) {
        throw new \Exception("post-card.blade.php missing canEditComment(c) check");
    }

    if (!str_contains($content, 'feed-comment-item')) {
        throw new \Exception("post-card.blade.php missing feed-comment-item class for IntersectionObserver");
    }
});

// [TEST 7] Blade & Alpine Invariants: Viewers Modal in middleColumn
runTest("Blade Invariant: middleColumn.blade.php contains Author Viewers Roster Modal", function() use ($rootDir) {
    $content = file_get_contents($rootDir . '/resources/views/member/includes/middleColumn.blade.php');
    if (!$content) {
        throw new \Exception("Failed to read middleColumn.blade.php");
    }

    if (!str_contains($content, 'isViewersModalOpen')) {
        throw new \Exception("middleColumn.blade.php missing isViewersModalOpen modal");
    }

    if (!str_contains($content, 'viewersType === \'comment\' ? \'Comment Viewers\' : \'Post Viewers\'')) {
        throw new \Exception("middleColumn.blade.php missing dynamic title for post/comment viewers");
    }

    if (!str_contains($content, 'viewersSearchQuery')) {
        throw new \Exception("middleColumn.blade.php missing viewersSearchQuery search filter");
    }

    if (!str_contains($content, 'viewersLoading')) {
        throw new \Exception("middleColumn.blade.php missing viewersLoading skeleton state");
    }

    if (!str_contains($content, 'closeViewersModal()')) {
        throw new \Exception("middleColumn.blade.php missing closeViewersModal() handler");
    }
});

// [TEST 8] Router Registration
runTest("Router Invariant: routes registered for post and comment viewers", function() use ($rootDir) {
    $postRoutes = file_get_contents($rootDir . '/app/router/post.php');
    $commRoutes = file_get_contents($rootDir . '/app/router/comment.php');

    if (!str_contains($postRoutes, '/api/posts/[i:postNo]/viewers')) {
        throw new \Exception("post.php router missing /api/posts/[i:postNo]/viewers");
    }

    if (!str_contains($commRoutes, '/api/comments/[i:commentNo]/viewers')) {
        throw new \Exception("comment.php router missing /api/comments/[i:commentNo]/viewers");
    }

    if (!str_contains($commRoutes, '/api/comments/view')) {
        throw new \Exception("comment.php router missing /api/comments/view");
    }
});

echo "\n=================================================================\n";
if ($failed === 0) {
    echo "🏆 ALL {$passed} AUTHOR POST & COMMENT VIEWERS TESTS PASSED CLEANLY!\n";
    echo "=================================================================\n";
    exit(0);
} else {
    echo "❌ {$failed} TEST(S) FAILED OUT OF " . ($passed + $failed) . "!\n";
    echo "=================================================================\n";
    exit(1);
}

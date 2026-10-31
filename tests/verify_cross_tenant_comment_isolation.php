<?php

declare(strict_types=1);

/**
 * 🛡️ Automated Verification Suite: SEC-CVE-2026-CROSS-TENANT-COMMENT-LEAK
 *
 * Verifies that:
 * 1. RegisterTableData no longer seeds phantom comments on non-existent post 1000.
 * 2. PostMessage::getNewCommentPusher() strictly isolates comments by validated postFamCode.
 * 3. Orphan comments on non-existent posts do not trigger notifications to active families.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

$pdo = \Src\Db::connect2();
$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $description): void {
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: $description\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: $description\n";
        $failed++;
    }
}

echo "=== SEC-CVE-2026 Cross-Tenant Comment Isolation Suite ===\n";

// Test 1: RegisterTableData does not generate 'comment'
$regData = \App\model\RegisterTableData::createRegisterTable([
    'id' => 'REG_TEST_' . bin2hex(random_bytes(4)),
    'firstName' => 'Karen',
    'lastName' => 'Jane',
    'famCode' => 'TEST_FAM_' . bin2hex(random_bytes(3)),
]);

assertCondition(
    !isset($regData['comment']),
    "RegisterTableData does not generate phantom starter comment on post 1000"
);

// Setup test families and users in DB
$famA = 'FAMA_' . bin2hex(random_bytes(4));
$famB = 'FAMB_' . bin2hex(random_bytes(4));

$userA1 = 'UA1_' . bin2hex(random_bytes(4));
$userA2 = 'UA2_' . bin2hex(random_bytes(4));
$userB1 = 'UB1_' . bin2hex(random_bytes(4));
$userB2 = 'UB2_' . bin2hex(random_bytes(4));

$cleanupUsers = [$userA1, $userA2, $userB1, $userB2];
$cleanupPosts = [];
$cleanupComments = [];

try {
    // Insert accounts
    foreach ([[$userA1, 'Alice', 'A', $famA], [$userA2, 'Bob', 'A', $famA], [$userB1, 'Charlie', 'B', $famB], [$userB2, 'David', 'B', $famB]] as $u) {
        $stmt = $pdo->prepare("INSERT INTO account (id, email, type, status) VALUES (?, ?, 'member', 'approved')");
        $stmt->execute([$u[0], strtolower($u[0]) . '@test.com']);
        $stmt = $pdo->prepare("INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)");
        $stmt->execute([$u[0], $u[1], $u[2], $u[3]]);
    }

    // Insert post in Family A
    $stmt = $pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, post_status, post_time) VALUES (?, ?, 'Post in Family A', 'published', ?)");
    $stmt->execute([$userA1, $famA, (string)(time() * 1000)]);
    $postANo = (int)$pdo->lastInsertId();
    $cleanupPosts[] = $postANo;

    // Insert post in Family B
    $stmt = $pdo->prepare("INSERT INTO post (id, postFamCode, postMessage, post_status, post_time) VALUES (?, ?, 'Post in Family B', 'published', ?)");
    $stmt->execute([$userB1, $famB, (string)(time() * 1000)]);
    $postBNo = (int)$pdo->lastInsertId();
    $cleanupPosts[] = $postBNo;

    // Insert comment in Family A (new)
    $stmt = $pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (?, ?, 'Alice A', 'Family A Comment', 'new', NOW())");
    $stmt->execute([$postANo, $userA1]);
    $cleanupComments[] = (int)$pdo->lastInsertId();

    // Insert comment in Family B (new)
    $stmt = $pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (?, ?, 'Charlie B', 'Family B Comment', 'new', NOW())");
    $stmt->execute([$postBNo, $userB1]);
    $cleanupComments[] = (int)$pdo->lastInsertId();

    // Insert orphan comment on non-existent post 999999 (new)
    $stmt = $pdo->prepare("INSERT INTO comment (post_no, id, fullName, comment, comment_status, date_created) VALUES (999999, 'ORPHAN_USER', 'Orphan User', 'Orphan Comment', 'new', NOW())");
    $stmt->execute();
    $cleanupComments[] = (int)$pdo->lastInsertId();

    // Process batch via PostMessage::getNewCommentPusher()
    $_SESSION['id'] = $userA1;
    $_SESSION['famCode'] = $famA;
    \App\controller\members\PostMessage::getNewCommentPusher();

    // Verification 1: Bob in Family A received notification from Alice
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = ?");
    $stmt->execute([$userA2, $userA1]);
    assertCondition((int)$stmt->fetchColumn() > 0, "Family A member Bob received notification from Alice");

    // Verification 2: Bob in Family A DID NOT receive notification from Charlie (Family B)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = ?");
    $stmt->execute([$userA2, $userB1]);
    assertCondition((int)$stmt->fetchColumn() === 0, "Family A member Bob did NOT receive notification from Charlie (Family B)");

    // Verification 3: Bob in Family A DID NOT receive notification from Orphan User
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = 'ORPHAN_USER'");
    $stmt->execute([$userA2]);
    assertCondition((int)$stmt->fetchColumn() === 0, "Family A member Bob did NOT receive notification from Orphan User on post 999999");

    // Verification 4: David in Family B received notification from Charlie
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = ?");
    $stmt->execute([$userB2, $userB1]);
    assertCondition((int)$stmt->fetchColumn() > 0, "Family B member David received notification from Charlie");

    // Verification 5: David in Family B DID NOT receive notification from Alice (Family A)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND sender_id = ?");
    $stmt->execute([$userB2, $userA1]);
    assertCondition((int)$stmt->fetchColumn() === 0, "Family B member David did NOT receive notification from Alice (Family A)");

} finally {
    // Teardown
    if (!empty($cleanupComments)) {
        $in = implode(',', array_fill(0, count($cleanupComments), '?'));
        $pdo->prepare("DELETE FROM comment WHERE comment_no IN ($in)")->execute($cleanupComments);
    }
    if (!empty($cleanupPosts)) {
        $in = implode(',', array_fill(0, count($cleanupPosts), '?'));
        $pdo->prepare("DELETE FROM post WHERE post_no IN ($in)")->execute($cleanupPosts);
    }
    if (!empty($cleanupUsers)) {
        $in = implode(',', array_fill(0, count($cleanupUsers), '?'));
        $pdo->prepare("DELETE FROM notification WHERE receiver_id IN ($in) OR sender_id IN ($in)")->execute(array_merge($cleanupUsers, $cleanupUsers));
        $pdo->prepare("DELETE FROM personal WHERE id IN ($in)")->execute($cleanupUsers);
        $pdo->prepare("DELETE FROM account WHERE id IN ($in)")->execute($cleanupUsers);
    }
    $pdo->prepare("DELETE FROM notification WHERE sender_id = 'ORPHAN_USER'")->execute();
}

echo "=== Results: $passed Passed, $failed Failed ===\n";
exit($failed > 0 ? 1 : 0);

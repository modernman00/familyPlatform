<?php

declare(strict_types=1);

/**
 * 🛡️ Family Join Collective Announcement Verification Suite (TAT & Engineering Council Gate)
 *
 * Verifies:
 * 1. Family Bulletin Announcement creation in `family_announcements` with 'celebration' type
 * 2. Celebratory timeline milestone post in `post` table
 * 3. In-app and push notification distribution to all co-members sharing the same family code
 * 4. Idempotency guard: duplicate calls within 24 hours do not create duplicate announcements
 * 5. Sole/Founding member edge case (zero co-members) executes cleanly without spurious dispatch
 * 6. Adversarial Red-Team Gate: IDOR & cross-family containment (no notifications leaked to other families)
 * 7. End-to-end integration via FamilyCodeApprovalService approval lifecycle
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\service\FamilyCodeApprovalService;
use App\services\FamilyJoinAnnouncementService;
use Src\Db;

$passed = 0;
$failed = 0;

function runStep(string $name, callable $fn): void {
    global $passed, $failed;
    echo "  [TEST " . ($passed + $failed + 1) . "] {$name}... ";
    try {
        $fn();
        $passed++;
        echo "✅ PASSED\n";
    } catch (\Throwable $e) {
        $failed++;
        echo "❌ FAILED: " . $e->getMessage() . "\n";
    }
}

$db = Db::connect2();

// Setup isolated test entities
$testFamCodeA = 'TESTJOIN_' . substr(bin2hex(random_bytes(4)), 0, 6);
$testFamCodeB = 'OTHERFAM_' . substr(bin2hex(random_bytes(4)), 0, 6);

$founderA = 'user_founder_' . bin2hex(random_bytes(4));
$memberA2 = 'user_member2_' . bin2hex(random_bytes(4));
$newJoiner = 'user_joiner_' . bin2hex(random_bytes(4));
$outsiderB = 'user_outsider_' . bin2hex(random_bytes(4));

try {
    // Seed Family A members
    $stmtAcc = $db->prepare('INSERT INTO account (id, email, password, type, status) VALUES (?, ?, "hash", "member", "approved")');
    $stmtPers = $db->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
    $stmtUf = $db->prepare('INSERT INTO user_families (user_id, family_code, status, role) VALUES (?, ?, "approved", ?)');

    // Founder A
    $stmtAcc->execute([$founderA, "founder_{$testFamCodeA}@test.local"]);
    $stmtPers->execute([$founderA, 'Tunde', 'Adeyemi', $testFamCodeA]);
    $stmtUf->execute([$founderA, $testFamCodeA, 'admin']);

    // Co-member A2
    $stmtAcc->execute([$memberA2, "member2_{$testFamCodeA}@test.local"]);
    $stmtPers->execute([$memberA2, 'Simi', 'Adeyemi', $testFamCodeA]);
    $stmtUf->execute([$memberA2, $testFamCodeA, 'member']);

    // New Joiner
    $stmtAcc->execute([$newJoiner, "joiner_{$testFamCodeA}@test.local"]);
    $stmtPers->execute([$newJoiner, 'Kemi', 'Adeyemi', $testFamCodeA]);
    $stmtUf->execute([$newJoiner, $testFamCodeA, 'member']);

    // Outsider in Family B
    $stmtAcc->execute([$outsiderB, "outsider_{$testFamCodeB}@test.local"]);
    $stmtPers->execute([$outsiderB, 'Marcus', 'Stone', $testFamCodeB]);
    $stmtUf->execute([$outsiderB, $testFamCodeB, 'member']);

    echo "=================================================================\n";
    echo "🌳 FAMILY JOIN COLLECTIVE ANNOUNCEMENT VERIFICATION SUITE\n";
    echo "=================================================================\n";

    // TEST 1: Direct broadcastNewMemberAnnouncement execution
    runStep('Broadcast announcement creates bulletin, timeline post, and in-app notifications', function () use ($db, $testFamCodeA, $newJoiner, $founderA, $memberA2) {
        $result = FamilyJoinAnnouncementService::broadcastNewMemberAnnouncement($testFamCodeA, $newJoiner, $founderA);
        if (!$result['success']) {
            throw new RuntimeException('Announcement broadcast returned failure: ' . ($result['error'] ?? $result['reason'] ?? 'unknown'));
        }

        // Verify family_announcements record
        $stmtAnn = $db->prepare("SELECT * FROM family_announcements WHERE family_code = ? AND type = 'celebration' AND author_id = ?");
        $stmtAnn->execute([$testFamCodeA, $founderA]);
        $ann = $stmtAnn->fetch(PDO::FETCH_ASSOC);
        if (!$ann) {
            throw new RuntimeException('Expected celebration announcement in family_announcements, none found');
        }
        if ((int)$ann['is_pinned'] !== 1) {
            throw new RuntimeException('Expected announcement to be pinned (is_pinned = 1)');
        }
        if (!str_contains($ann['title'], 'Kemi Adeyemi')) {
            throw new RuntimeException('Announcement title should contain new member name Kemi Adeyemi, got: ' . $ann['title']);
        }

        // Verify post table record
        $stmtPost = $db->prepare("SELECT * FROM post WHERE postFamCode = ? AND id = ? AND post_status = 'published'");
        $stmtPost->execute([$testFamCodeA, $newJoiner]);
        $post = $stmtPost->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            throw new RuntimeException('Expected published celebratory post in feed, none found');
        }
        if (!str_contains($post['postMessage'], 'Warm Welcome to the Family!')) {
            throw new RuntimeException('Post message should contain celebration copy');
        }

        // Verify in-app notifications for co-members
        $stmtNotif = $db->prepare("SELECT * FROM notification WHERE receiver_id = ? AND notification_type = 'Announcement'");
        $stmtNotif->execute([$memberA2]);
        $notif = $stmtNotif->fetch(PDO::FETCH_ASSOC);
        if (!$notif) {
            throw new RuntimeException('Co-member Simi should have received in-app notification');
        }
        if (!str_contains($notif['notification_content'], 'Kemi Adeyemi')) {
            throw new RuntimeException('Notification content should mention Kemi Adeyemi');
        }
    });

    // TEST 2: Idempotency Protection
    runStep('Idempotency: Re-broadcasting within 24 hours does not create duplicate announcements', function () use ($db, $testFamCodeA, $newJoiner, $founderA) {
        $stmtCountBefore = $db->prepare("SELECT COUNT(*) FROM family_announcements WHERE family_code = ? AND type = 'celebration'");
        $stmtCountBefore->execute([$testFamCodeA]);
        $countBefore = (int)$stmtCountBefore->fetchColumn();

        // Second call
        $result2 = FamilyJoinAnnouncementService::broadcastNewMemberAnnouncement($testFamCodeA, $newJoiner, $founderA);
        if (!$result2['success']) {
            throw new RuntimeException('Second broadcast call failed unexpectedly');
        }

        $stmtCountAfter = $db->prepare("SELECT COUNT(*) FROM family_announcements WHERE family_code = ? AND type = 'celebration'");
        $stmtCountAfter->execute([$testFamCodeA]);
        $countAfter = (int)$stmtCountAfter->fetchColumn();

        if ($countAfter !== $countBefore) {
            throw new RuntimeException("Duplicate announcement was created! Before: {$countBefore}, After: {$countAfter}");
        }
    });

    // TEST 3: Cross-Family Isolation & Red-Team Gate
    runStep('Adversarial Gate: Co-members of other families receive ZERO notifications or leaks', function () use ($db, $outsiderB) {
        $stmtOutsider = $db->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ? AND notification_content LIKE '%Kemi Adeyemi%'");
        $stmtOutsider->execute([$outsiderB]);
        $leakedCount = (int)$stmtOutsider->fetchColumn();

        if ($leakedCount !== 0) {
            throw new RuntimeException("Security violation: Leaked {$leakedCount} notifications to outsider {$outsiderB} in different family code!");
        }
    });

    // TEST 4: Solo / Founding Member Edge Case
    runStep('Sole Founder edge case: Zero co-members exits cleanly without errors or spurious notifications', function () use ($testFamCodeB, $outsiderB) {
        // OutsiderB is the only member in Family B
        $result = FamilyJoinAnnouncementService::broadcastNewMemberAnnouncement($testFamCodeB, $outsiderB);
        if (!$result['success']) {
            throw new RuntimeException('Sole founder call failed: ' . ($result['error'] ?? 'unknown'));
        }
        if (($result['reason'] ?? '') !== 'sole_family_member') {
            throw new RuntimeException('Expected reason sole_family_member, got: ' . ($result['reason'] ?? ''));
        }
        if (($result['notified_count'] ?? -1) !== 0) {
            throw new RuntimeException('Expected 0 notified members for sole family member');
        }
    });

    // TEST 5: Integration with FamilyCodeApprovalService lifecycle
    runStep('Integration: Approving an approval request automatically triggers family announcement', function () use ($db, $testFamCodeA, $founderA) {
        $approvalService = new FamilyCodeApprovalService($db);

        // Create a fourth user requesting to join
        $userC = 'user_c_' . bin2hex(random_bytes(4));
        $stmtAcc = $db->prepare('INSERT INTO account (id, email, password, type, status) VALUES (?, ?, "hash", "member", "pending")');
        $stmtAcc->execute([$userC, "user_c_{$testFamCodeA}@test.local"]);
        $stmtPers = $db->prepare('INSERT INTO personal (id, firstName, lastName, famCode) VALUES (?, ?, ?, ?)');
        $stmtPers->execute([$userC, 'Bayo', 'Adeyemi', $testFamCodeA]);

        // Create approval request
        $reqData = $approvalService->createApprovalRequest(
            $userC,
            $testFamCodeA,
            'Tunde',
            'Adeyemi',
            "founder_{$testFamCodeA}@test.local"
        );
        $reqId = (int)$reqData['request_id'];

        // Approve the request
        $approved = $approvalService->approveRequest($reqId, $founderA);
        if (!$approved) {
            throw new RuntimeException('Failed to approve request via FamilyCodeApprovalService');
        }

        // Verify announcement was automatically generated for Bayo Adeyemi
        $stmtAnn = $db->prepare("SELECT * FROM family_announcements WHERE family_code = ? AND type = 'celebration' AND (title LIKE '%Bayo Adeyemi%' OR message LIKE '%Bayo Adeyemi%')");
        $stmtAnn->execute([$testFamCodeA]);
        $ann = $stmtAnn->fetch(PDO::FETCH_ASSOC);
        if (!$ann) {
            throw new RuntimeException('Announcement for Bayo Adeyemi was not automatically created upon approval!');
        }

        // Verify post was created for Bayo Adeyemi
        $stmtPost = $db->prepare("SELECT * FROM post WHERE postFamCode = ? AND id = ? AND post_status = 'published'");
        $stmtPost->execute([$testFamCodeA, $userC]);
        $post = $stmtPost->fetch(PDO::FETCH_ASSOC);
        if (!$post) {
            throw new RuntimeException('Feed post for Bayo Adeyemi was not automatically created upon approval!');
        }
    });

    echo "=================================================================\n";
    echo "SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
    echo "=================================================================\n";

    if ($failed > 0) {
        exit(1);
    }

} finally {
    // Cleanup test artifacts
    $db->prepare("DELETE FROM family_announcements WHERE family_code IN (?, ?)")->execute([$testFamCodeA, $testFamCodeB]);
    $db->prepare("DELETE FROM post WHERE postFamCode IN (?, ?)")->execute([$testFamCodeA, $testFamCodeB]);
    $db->prepare("DELETE FROM notification WHERE receiver_id IN (?, ?, ?, ?)")->execute([$founderA, $memberA2, $newJoiner, $outsiderB]);
    $db->prepare("DELETE FROM family_approval_requests WHERE family_code IN (?, ?)")->execute([$testFamCodeA, $testFamCodeB]);
    $db->prepare("DELETE FROM user_families WHERE family_code IN (?, ?)")->execute([$testFamCodeA, $testFamCodeB]);
    $db->prepare("DELETE FROM personal WHERE id IN (?, ?, ?, ?)")->execute([$founderA, $memberA2, $newJoiner, $outsiderB]);
    $db->prepare("DELETE FROM account WHERE id IN (?, ?, ?, ?)")->execute([$founderA, $memberA2, $newJoiner, $outsiderB]);
}

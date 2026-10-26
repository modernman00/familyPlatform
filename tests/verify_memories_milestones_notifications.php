<?php

declare(strict_types=1);

/**
 * 🌟 Automated Verification Suite: Memories & Milestones Notifications & Deep Card Navigation
 *
 * Verifies:
 * 1. Nostalgia flashback memories ("On This Day") generate in-app notifications and WebPush PWA payloads.
 * 2. Today's milestones (Birthdays, Anniversaries, Events) generate notifications.
 * 3. Both in-app and WebPush notifications contain canonical action URL `/profilePage#memoriesCard`.
 * 4. Idempotent daily de-duplication prevents duplicate alerts on subsequent runs or polls.
 * 5. NotificationController resolves memories/milestones notifications to `/profilePage#memoriesCard`.
 * 6. Clean database teardown with zero pollution.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

require_once $rootDir . '/app/config/init.php';

use App\controller\members\MemoryMilestoneController;
use App\controller\NotificationController;
use App\services\MemoryMilestoneService;
use Src\Db;

$db = Db::connect2();

$testFamCode = 'TEST_FLASHBACK_FAM_' . bin2hex(random_bytes(4));
$testUserId = 'TEST_USER_MEM_' . bin2hex(random_bytes(4));

echo "=================================================================\n";
echo "🌟 MEMORIES & MILESTONES NOTIFICATION SUITE (TAT & BRATS GATE)\n";
echo "=================================================================\n\n";

try {
    // 0. Initial Cleanup
    $db->prepare("DELETE FROM notification WHERE receiver_id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM notification_orchestration WHERE user_id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$testFamCode]);
    $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
    $db->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);

    // 1. Setup Test User
    $bdayDay = (int)date('j');
    $bdayMonth = date('F');
    $bdayYear = (int)date('Y') - 25;

    $userStmt = $db->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, day, month, year)
        VALUES (?, 'Simisola', 'Ogunleye', ?, ?, ?, ?)
    ");
    $userStmt->execute([$testUserId, $testFamCode, $bdayDay, $bdayMonth, $bdayYear]);

    $accStmt = $db->prepare("INSERT INTO account (id, email, status) VALUES (?, 'simi@example.test', 'verified')");
    $accStmt->execute([$testUserId]);

    echo "✅ [Step 1] Test user and family environment provisioned successfully.\n";

    // 2. Seed a Nostalgia Flashback Memory Post (Exactly 2 Years Ago Today)
    $pastDate = date('Y-m-d H:i:s', strtotime('-2 years'));
    $postStmt = $db->prepare("
        INSERT INTO post (id, fullName, postMessage, postFamCode, post_status, date_created)
        VALUES (?, 'Simisola Ogunleye', 'Cherished childhood memories by the lakeside!', ?, 'published', ?)
    ");
    $postStmt->execute([$testUserId, $testFamCode, $pastDate]);
    $memoryPostNo = (int)$db->lastInsertId();

    echo "✅ [Step 2] Seeded flashback memory post #{$memoryPostNo} from 2 years ago today.\n";

    // 3. Test Service Memory & Milestone Dispatching
    $service = new MemoryMilestoneService($db);
    $result = $service->dispatchDailyMemoriesAndMilestonesForUser($testUserId, [$testFamCode]);

    echo "   Dispatch Result: Memories = {$result['memories_dispatched']}, Milestones = {$result['milestones_dispatched']}\n";
    if ($result['memories_dispatched'] < 1) {
        throw new \RuntimeException("FAILED: Memory flashback notification was not dispatched!");
    }
    if ($result['milestones_dispatched'] < 1) {
        throw new \RuntimeException("FAILED: Birthday milestone notification was not dispatched!");
    }
    echo "✅ [Step 3] Service dispatched both memory and milestone notifications.\n";

    // 4. Verify Durable In-App Notifications in `notification` Table
    $notifStmt = $db->prepare("
        SELECT * FROM notification 
        WHERE receiver_id = ? 
        ORDER BY no DESC
    ");
    $notifStmt->execute([$testUserId]);
    $notifications = $notifStmt->fetchAll(\PDO::FETCH_ASSOC);

    if (count($notifications) < 2) {
        throw new \RuntimeException("FAILED: Expected at least 2 in-app notifications, got " . count($notifications));
    }

    $memoryNotif = null;
    $milestoneNotif = null;
    foreach ($notifications as $n) {
        if ($n['notification_type'] === 'Memories') {
            $memoryNotif = $n;
        } elseif ($n['notification_type'] === 'Milestones') {
            $milestoneNotif = $n;
        }
    }

    if (!$memoryNotif) {
        throw new \RuntimeException("FAILED: No notification with notification_type = 'Memories' found!");
    }
    if ($memoryNotif['action_url'] !== '/profilePage#memoriesCard') {
        throw new \RuntimeException("FAILED: Memory notification action_url is '{$memoryNotif['action_url']}', expected '/profilePage#memoriesCard'");
    }
    if (strpos($memoryNotif['notification_content'], 'cherished family memory') === false) {
        throw new \RuntimeException("FAILED: Memory notification content did not match expected British English copy!");
    }

    if (!$milestoneNotif) {
        throw new \RuntimeException("FAILED: No notification with notification_type = 'Milestones' found!");
    }
    if ($milestoneNotif['action_url'] !== '/profilePage#memoriesCard') {
        throw new \RuntimeException("FAILED: Milestone notification action_url is '{$milestoneNotif['action_url']}', expected '/profilePage#memoriesCard'");
    }

    echo "✅ [Step 4] In-app durable notifications verified in `notification` table with action_url = '/profilePage#memoriesCard'.\n";

    // 5. Verify Multi-Channel Orchestration in `notification_orchestration` Table
    $orchStmt = $db->prepare("
        SELECT * FROM notification_orchestration 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $orchStmt->execute([$testUserId]);
    $orchRows = $orchStmt->fetchAll(\PDO::FETCH_ASSOC);

    $hasMemOrch = false;
    foreach ($orchRows as $orch) {
        if ($orch['action_url'] === '/profilePage#memoriesCard') {
            $hasMemOrch = true;
            break;
        }
    }
    if (!$hasMemOrch) {
        throw new \RuntimeException("FAILED: Notification orchestration missing `/profilePage#memoriesCard` record!");
    }

    echo "✅ [Step 5] Notification orchestration records verified for multi-channel WebPush / Socket delivery.\n";

    // 6. Test Idempotency (Zero Duplicate Alerts on Subsequent Invocations)
    $secondResult = $service->dispatchDailyMemoriesAndMilestonesForUser($testUserId, [$testFamCode]);
    if ($secondResult['memories_dispatched'] !== 0 || $secondResult['milestones_dispatched'] !== 0) {
        throw new \RuntimeException("FAILED: Idempotency violated! Second run dispatched: " . json_encode($secondResult));
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM notification WHERE receiver_id = ?");
    $countStmt->execute([$testUserId]);
    $postCount = (int)$countStmt->fetchColumn();
    if ($postCount !== count($notifications)) {
        throw new \RuntimeException("FAILED: Duplicate in-app notifications created on repeated run! Count grew from " . count($notifications) . " to {$postCount}");
    }

    echo "✅ [Step 6] Idempotency verified: zero duplicate notifications generated on recurring scan.\n";

    // 7. Test Canonical Action URL Resolution in NotificationController
    $resolvedMemUrl = NotificationController::resolveNotificationActionUrl([
        'notification_type' => 'Memories',
        'notification_name' => '✨ On This Day Flashback',
        'notification_content' => 'You have a cherished memory from 2 years ago today!'
    ], $testUserId);

    if ($resolvedMemUrl !== '/profilePage#memoriesCard') {
        throw new \RuntimeException("FAILED: NotificationController resolved memory URL as '{$resolvedMemUrl}', expected '/profilePage#memoriesCard'");
    }

    $resolvedMsUrl = NotificationController::resolveNotificationActionUrl([
        'notification_type' => 'Milestones',
        'notification_name' => '🎂 Birthday Milestone',
        'notification_content' => 'Today is a family birthday! Celebrate this milestone.'
    ], $testUserId);

    if ($resolvedMsUrl !== '/profilePage#memoriesCard') {
        throw new \RuntimeException("FAILED: NotificationController resolved milestone URL as '{$resolvedMsUrl}', expected '/profilePage#memoriesCard'");
    }

    echo "✅ [Step 7] Canonical action URL resolution strictly verified to route to '/profilePage#memoriesCard'.\n";

    // 8. Test MemoryMilestoneController API Output
    $_SESSION['id'] = $testUserId;
    $_SESSION['famCode'] = $testFamCode;
    $_SESSION['famCodes'] = [$testFamCode];

    $controller = new MemoryMilestoneController($service);
    ob_start();
    $controller->getMemoriesAndMilestones();
    $apiOutput = (string)ob_get_clean();

    $apiData = json_decode($apiOutput, true);
    if (($apiData['status'] ?? '') !== 'success') {
        throw new \RuntimeException("FAILED: API getMemoriesAndMilestones returned error: " . $apiOutput);
    }
    if (empty($apiData['data']['memories']) || empty($apiData['data']['milestones'])) {
        throw new \RuntimeException("FAILED: API response missing memories or milestones data!");
    }

    echo "✅ [Step 8] Controller GET /api/memories/today verified with valid memory and milestone payload.\n";

    echo "\n=================================================================\n";
    echo "🎉 ALL TESTS PASSED: Memories & Milestones Notifications Certified!\n";
    echo "=================================================================\n";

} catch (\Throwable $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
} finally {
    // Teardown
    if (isset($testUserId)) {
        $db->prepare("DELETE FROM notification WHERE receiver_id = ?")->execute([$testUserId]);
        $db->prepare("DELETE FROM notification_orchestration WHERE user_id = ?")->execute([$testUserId]);
        $db->prepare("DELETE FROM personal WHERE id = ?")->execute([$testUserId]);
        $db->prepare("DELETE FROM account WHERE id = ?")->execute([$testUserId]);
    }
    if (isset($testFamCode)) {
        $db->prepare("DELETE FROM post WHERE postFamCode = ?")->execute([$testFamCode]);
    }
}

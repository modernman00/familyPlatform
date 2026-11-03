<?php

declare(strict_types=1);

/**
 * Sovereign Realtime Broadcasting & Failover Verification Suite
 * Engineered by ATG for FamilyPlatform.
 */

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';
if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\classes\Pusher;
use Src\Realtime\SovereignBroadcaster;

echo "=================================================================\n";
echo "🧪 FAMILYPLATFORM: SOVEREIGN BROADCASTING FAILOVER SUITE\n";
echo "=================================================================\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $msg, &$passCount, &$failCount): void {
    if ($cond) {
        echo "  ✓ {$msg}\n";
        $passCount++;
    } else {
        echo "  ❌ FAILED: {$msg}\n";
        $failCount++;
    }
}

try {
    // 1. Test Pusher::broadcast with fallback to SovereignBroadcaster
    $userChannel = 'private-user-test-member-' . uniqid();
    $userEvent = 'chore.points_awarded';
    $userData = [
        'points_added' => 50,
        'chore_type'   => 'Dishes and Kitchen Clean',
        'reward_title' => 'Family Movie Night',
        'timestamp'    => time(),
    ];

    Pusher::broadcast($userChannel, $userEvent, $userData);

    $broadcaster = new SovereignBroadcaster();
    $events = $broadcaster->getEvents($userChannel, 0, 10);

    assertCondition(!empty($events), 'Pusher::broadcast recorded event to Sovereign fallback queue', $passCount, $failCount);
    $lastEvent = end($events);
    assertCondition(($lastEvent['event'] ?? '') === $userEvent, 'Event name matches chore.points_awarded', $passCount, $failCount);
    assertCondition(($lastEvent['data']['reward_title'] ?? '') === 'Family Movie Night', 'Payload data preserved with high fidelity', $passCount, $failCount);

    // 2. Test Pusher::broadcastToFamily
    $famCode = 'DEMOFAM' . rand(100, 999);
    $famChannel = Pusher::familyChannel($famCode);
    $famEvent = 'roundtable.dispute_arbitrated';
    $famData = [
        'dispute_id' => 'disp_999',
        'action_plan' => 'Mutual respect and shared household harmony',
        'status' => 'resolved'
    ];

    Pusher::broadcastToFamily($famCode, $famEvent, $famData);

    $famEvents = $broadcaster->getEvents($famChannel, 0, 10);
    assertCondition(!empty($famEvents), 'Pusher::broadcastToFamily captured by Sovereign fallback queue', $passCount, $failCount);
    $lastFamEvent = end($famEvents);
    assertCondition(($lastFamEvent['event'] ?? '') === $famEvent, 'Family channel event matches roundtable.dispute_arbitrated', $passCount, $failCount);

    // 3. Test HMAC Channel Authentication
    $auth = $broadcaster->authenticateChannel($famChannel, 'socket_12345.67890', [
        'user_id' => 'user_42'
    ]);
    assertCondition(!empty($auth['auth']), 'HMAC authorization token generated', $passCount, $failCount);
    assertCondition(str_contains($auth['auth'], ':'), 'Auth string contains expected key:signature format', $passCount, $failCount);

    // 4. Test RealtimeBroadcastingController BOLA/IDOR & Channel Ownership Guards
    $_SESSION['id'] = 'user_42';
    $_SESSION['famCode'] = $famCode;
    $_SESSION['famCodes'] = [$famCode];

    assertCondition(
        \App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('user_42', 'private-user-user_42'),
        'Owner is authorised for their own private-user channel',
        $passCount,
        $failCount
    );
    assertCondition(
        !\App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('user_42', 'private-user-user_999'),
        'Cross-user private channel eavesdropping blocked (BOLA/IDOR defense)',
        $passCount,
        $failCount
    );
    assertCondition(
        \App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('user_42', $famChannel),
        'Family member is authorised for their own private-family channel',
        $passCount,
        $failCount
    );
    assertCondition(
        !\App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('user_42', 'private-family-OTHERFAM999'),
        'Cross-family channel eavesdropping blocked (BOLA/IDOR defense)',
        $passCount,
        $failCount
    );
    assertCondition(
        !\App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('', $famChannel),
        'Unauthenticated empty user ID rejected immediately',
        $passCount,
        $failCount
    );
    assertCondition(
        !\App\controller\RealtimeBroadcastingController::isAuthorizedForChannel('user_42', "private-user-user_42\r\nevent: injected"),
        'CRLF / SSE stream injection attempt rejected',
        $passCount,
        $failCount
    );

    echo "\n=================================================================\n";
    if ($failCount === 0) {
        echo "🏆 ALL SOVEREIGN BROADCASTING TESTS PASSED! ({$passCount} passed)\n";
        echo "=================================================================\n";
        exit(0);
    } else {
        echo "❌ TESTS FAILED: {$failCount} failed, {$passCount} passed\n";
        echo "=================================================================\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "\n❌ Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

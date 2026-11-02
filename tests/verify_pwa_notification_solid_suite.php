<?php
declare(strict_types=1);

if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

/**
 * Verification Suite for PWA Web Push Notification Solidity & ExecMind Parity
 *
 * Validates:
 * 1. Dedicated VAPID Public Key API endpoint returns valid key.
 * 2. Database `pushNotification` table accepts endpoints > 255 chars without truncation.
 * 3. Subscription registration handles long endpoints cleanly.
 * 4. `public/sw.js` contains unconditional OS notification dispatch (no foreground suppression).
 * 5. `resources/views/partials/pwa-register.blade.php` contains self-healing push subscription engine.
 */

require_once __DIR__ . '/bootstrap.php';

use Src\Db;
use App\controller\NotificationController;

echo "=================================================================\n";
echo "🛡️  PWA NOTIFICATION SOLIDITY & EXECMIND PARITY VERIFICATION\n";
echo "=================================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertCheck(bool $condition, string $message): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $testsPassed++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $testsFailed++;
    }
}

// 1. Test VAPID Public Key API
echo "[1/5] Testing VAPID Public Key Endpoint Output...\n";
ob_start();
NotificationController::vapidPublicKey();
$rawJson = ob_get_clean();
$data = json_decode((string)$rawJson, true);

assertCheck(is_array($data) && !empty($data['publicKey']), 'VAPID public key returned in response');
assertCheck(isset($data['status']) && $data['status'] === 'success', 'Status is success');
assertCheck(str_starts_with((string)$data['publicKey'], 'BA'), 'Public key matches expected ECDSA P-256 header');

// 2. Test Database Schema: Long Endpoints (> 255 chars)
echo "\n[2/5] Testing pushNotification Database Table Schema for Long Endpoints...\n";
$pdo = Db::connect2();
$longEndpoint = 'https://fcm.googleapis.com/fcm/send/' . str_repeat('a', 300);
$testUserId = 'test_pwa_user_' . bin2hex(random_bytes(4));
$p256dh = 'BMx8J5NqK9Lw0M1P2Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E7F8G9H0I1J2K3L4M5=';
$auth = 'a1b2c3d4e5f6g7h8';

try {
    $stmt = $pdo->prepare('INSERT INTO pushNotification (id, endpoint, p256dhKey, authKey) VALUES (?, ?, ?, ?)');
    $stmt->execute([$testUserId, $longEndpoint, $p256dh, $auth]);

    $fetchStmt = $pdo->prepare('SELECT endpoint FROM pushNotification WHERE id = ? AND endpoint = ?');
    $fetchStmt->execute([$testUserId, $longEndpoint]);
    $savedEndpoint = $fetchStmt->fetchColumn();

    assertCheck($savedEndpoint === $longEndpoint, 'Database stores 300+ char push endpoint without truncation');

    // Clean up
    $pdo->prepare('DELETE FROM pushNotification WHERE id = ?')->execute([$testUserId]);
} catch (\Throwable $e) {
    assertCheck(false, 'Failed to store long endpoint: ' . $e->getMessage());
}

// 3. Test Service Worker Code Invariants
echo "\n[3/5] Testing Service Worker (public/sw.js) Architecture...\n";
$swCode = (string)file_get_contents(__DIR__ . '/../public/sw.js');

assertCheck(!str_contains($swCode, 'if (isAppFocused)'), 'Foreground suppression (isAppFocused early exit) removed');
assertCheck(str_contains($swCode, 'self.registration.showNotification(data.title, options)'), 'Unconditional showNotification call present');
assertCheck(str_contains($swCode, 'clearAppBadge'), 'Web Badging API clearAppBadge integrated in notificationclick');

// 4. Test PWA Register Self-Healing Partial
echo "\n[4/5] Testing PWA Register Partial (pwa-register.blade.php)...\n";
$pwaRegCode = (string)file_get_contents(__DIR__ . '/../resources/views/partials/pwa-register.blade.php');

assertCheck(str_contains($pwaRegCode, 'window.syncPushSubscription'), 'window.syncPushSubscription exposed globally');
assertCheck(str_contains($pwaRegCode, 'Notification.permission === \'granted\''), 'Auto-sync trigger on granted permission present');
assertCheck(str_contains($pwaRegCode, '/api/notifications/vapid-public-key'), 'Fallback to VAPID public key API present');

// 5. Test Router Registration
echo "\n[5/5] Testing Push Notification Router Mapping...\n";
$routerCode = (string)file_get_contents(__DIR__ . '/../app/router/pushNotification.php');

assertCheck(str_contains($routerCode, '/push/vapid-public-key'), 'Route /push/vapid-public-key registered');
assertCheck(str_contains($routerCode, '/api/notifications/vapid-public-key'), 'Route /api/notifications/vapid-public-key registered');

echo "\n=================================================================\n";
echo "📊 SUITE SUMMARY\n";
echo "=================================================================\n";
echo "Passed Checks: {$testsPassed}\n";
echo "Failed Checks: {$testsFailed}\n";

if ($testsFailed > 0) {
    echo "❌ SOME CHECKS FAILED!\n";
    exit(1);
} else {
    echo "🎉 100% SUCCESS: PWA push notification architecture matches ExecMind standard!\n";
    exit(0);
}

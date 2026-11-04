<?php
declare(strict_types=1);

if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

require_once __DIR__ . '/bootstrap.php';

use Src\Db;
use App\controller\NotificationController;

echo "=================================================================\n";
echo "🛡️  PUSH NOTIFICATION CSRF & AUTH ROBUSTNESS SUITE\n";
echo "=================================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(bool $condition, string $message): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  ✅ PASS: {$message}\n";
        $testsPassed++;
    } else {
        echo "  ❌ FAIL: {$message}\n";
        $testsFailed++;
    }
}

// Setup common fixtures
$testToken = bin2hex(random_bytes(32));
$validUserId = 'csrf_test_user_' . bin2hex(random_bytes(4));
$validEndpoint = 'https://fcm.googleapis.com/fcm/send/' . bin2hex(random_bytes(16));
$validPayload = [
    'endpoint' => $validEndpoint,
    'keys' => [
        'p256dh' => 'BMx8J5NqK9Lw0M1P2Q3R4S5T6U7V8W9X0Y1Z2A3B4C5D6E7F8G9H0I1J2K3L4M5=',
        'auth' => 'a1b2c3d4e5f6g7h8'
    ]
];

function resetRequestState(): void {
    unset(
        $_SERVER['HTTP_X_XSRF_TOKEN'],
        $_SERVER['HTTP_X_CSRF_TOKEN'],
        $_SERVER['HTTP_CSRF_TOKEN'],
        $_COOKIE['XSRF-TOKEN'],
        $_POST['token']
    );
    $_SESSION = [];
}

// 1. Unauthenticated request must yield authentication error (not generic CSRF)
resetRequestState();
$caughtCode = null;
$caughtMsg = '';
try {
    // Inject mock php://input via reflection or simulated inputData
    // We test postSubscriberData with empty session
    $tempStream = fopen('php://memory', 'r+');
    fwrite($tempStream, json_encode($validPayload));
    rewind($tempStream);
    NotificationController::postSubscriberData();
} catch (\Src\Exceptions\HttpException $e) {
    $caughtCode = $e->getCode();
    $caughtMsg = $e->getMessage();
} catch (\Throwable $e) {
    $caughtMsg = $e->getMessage();
}
assertTest($caughtCode === 401 && str_contains($caughtMsg, 'User authentication required'), 'Unauthenticated caller rejected with explicit 401 auth required');

// 2. Authenticated user with missing CSRF token rejected with 401 Unauthorized CSRF Token
resetRequestState();
$_SESSION['id'] = $validUserId;
$_SESSION['token'] = $testToken;
$caughtCode = null;
$caughtMsg = '';
try {
    NotificationController::postSubscriberData();
} catch (\Src\Exceptions\HttpException $e) {
    $caughtCode = $e->getCode();
    $caughtMsg = $e->getMessage();
}
assertTest($caughtCode === 401 && $caughtMsg === 'Unauthorized CSRF Token', 'Missing token rejected with 401 Unauthorized CSRF Token');

// 3. Attacker with Cookie only (no header/body client token) MUST FAIL (CSRF attack neutralization)
resetRequestState();
$_SESSION['id'] = $validUserId;
$_SESSION['token'] = $testToken;
$_COOKIE['XSRF-TOKEN'] = $testToken; // Browser cookie present on cross-origin request
$caughtCode = null;
$caughtMsg = '';
try {
    NotificationController::postSubscriberData();
} catch (\Src\Exceptions\HttpException $e) {
    $caughtCode = $e->getCode();
    $caughtMsg = $e->getMessage();
}
assertTest($caughtCode === 401 && $caughtMsg === 'Unauthorized CSRF Token', 'Attacker with cookie-only (no explicit request token) neutralized');

// 4. Invalid/tampered client token rejected
resetRequestState();
$_SESSION['id'] = $validUserId;
$_SESSION['token'] = $testToken;
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'tampered_malicious_token_12345';
$caughtCode = null;
$caughtMsg = '';
try {
    NotificationController::postSubscriberData();
} catch (\Src\Exceptions\HttpException $e) {
    $caughtCode = $e->getCode();
    $caughtMsg = $e->getMessage();
}
assertTest($caughtCode === 401 && $caughtMsg === 'Unauthorized CSRF Token', 'Tampered client token rejected');

// 5. Verification method accessibility via Reflection to test all token channels
$refClass = new ReflectionClass(NotificationController::class);
$refVerify = $refClass->getMethod('verifyCsrf');
$refVerify->setAccessible(true);

// Channel A: HTTP_X_XSRF_TOKEN header
resetRequestState();
$_SESSION['token'] = $testToken;
$_SERVER['HTTP_X_XSRF_TOKEN'] = $testToken;
assertTest($refVerify->invoke(null, []) === true, 'Valid token in HTTP_X_XSRF_TOKEN passes');

// Channel B: HTTP_X_CSRF_TOKEN header
resetRequestState();
$_SESSION['token'] = $testToken;
$_SERVER['HTTP_X_CSRF_TOKEN'] = $testToken;
assertTest($refVerify->invoke(null, []) === true, 'Valid token in HTTP_X_CSRF_TOKEN passes');

// Channel C: JSON body token (previously skipped by ?? bug!)
resetRequestState();
$_SESSION['token'] = $testToken;
assertTest($refVerify->invoke(null, ['token' => $testToken]) === true, 'Valid token in JSON body inputData["token"] passes');

// Channel D: URL-encoded token
resetRequestState();
$_SESSION['token'] = $testToken;
$_SERVER['HTTP_X_CSRF_TOKEN'] = urlencode($testToken);
assertTest($refVerify->invoke(null, []) === true, 'URL-encoded token in header passes');

// Channel E: Re-anchoring from valid 64-hex cookie token if session token was empty
resetRequestState();
$_SESSION['token'] = ''; // Empty session token
$_COOKIE['XSRF-TOKEN'] = $testToken; // Valid 64-hex cookie
assertTest($refVerify->invoke(null, ['token' => $testToken]) === true, 'Valid cookie token re-anchors session and passes');
assertTest($_SESSION['token'] === $testToken, 'Session token correctly populated from verified cookie');

// Channel F: Double-submit validation against XSRF-TOKEN cookie
resetRequestState();
$_SESSION['token'] = 'some_other_session_token';
$_COOKIE['XSRF-TOKEN'] = $testToken;
$_SERVER['HTTP_X_XSRF_TOKEN'] = $testToken;
assertTest($refVerify->invoke(null, []) === true, 'Double-submit cookie validation with explicit client header passes');

echo "\n=================================================================\n";
echo "📊 TEST RESULTS: {$testsPassed} passed, {$testsFailed} failed\n";
echo "=================================================================\n";

if ($testsFailed > 0) {
    exit(1);
}

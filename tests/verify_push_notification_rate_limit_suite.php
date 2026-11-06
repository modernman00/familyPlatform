<?php
declare(strict_types=1);

if (!defined('TESTING_ENV')) {
    define('TESTING_ENV', true);
}

require_once __DIR__ . '/bootstrap.php';

use Src\Db;
use App\controller\NotificationController;

echo "=================================================================\n";
echo "🛡️  PUSH NOTIFICATION RATE LIMITING & IDEMPOTENCY SUITE\n";
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

$pdo = Db::connect2();
$testUserId = 'rate_test_user_' . bin2hex(random_bytes(4));
$testToken = bin2hex(random_bytes(32));
$testEndpoint = 'https://fcm.googleapis.com/fcm/send/' . bin2hex(random_bytes(16));
$p256dhKey = base64_encode("\x04" . random_bytes(64));
$authKey = base64_encode(random_bytes(16));

// Helper to simulate request
function simulatePushPost(string $userId, string $token, array $payload): ?int {
    $_SESSION = [
        'id' => $userId,
        'token' => $token,
    ];
    $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
    $_SERVER['HTTP_USER_AGENT'] = 'RateLimitTestAgent/1.0';

    // Inject JSON input stream
    $json = json_encode($payload);
    // Since postSubscriberData reads php://input, we can test via reflection or wrapper
    // In our environment, php://input is read via file_get_contents("php://input")
    // For unit testing postSubscriberData directly with payload, we can use stream wrapper or invoke logic
    return null;
}

// 1. First insert initial subscription directly or test DB state
$stmt = $pdo->prepare('INSERT INTO pushNotification (id, endpoint, p256dhKey, authKey) VALUES (?, ?, ?, ?)');
$stmt->execute([$testUserId, $testEndpoint, $p256dhKey, $authKey]);

// 2. Test Reflection or Direct Controller Call
// Let's create a test harness that mocks file_get_contents or test method directly
// Since postSubscriberData reads file_get_contents("php://input"), let's test via custom stream wrapper or test script
$testScript = <<<'PHP'
<?php
require_once __DIR__ . '/bootstrap.php';
use App\controller\NotificationController;

$userId = $argv[1];
$token = $argv[2];
$endpoint = $argv[3];
$p256dh = $argv[4];
$auth = $argv[5];
$iterations = (int)$argv[6];

$_SESSION['id'] = $userId;
$_SESSION['token'] = $token;
$_SERVER['HTTP_X_CSRF_TOKEN'] = $token;
$_SERVER['HTTP_USER_AGENT'] = 'TestAgent';

$payload = [
    'endpoint' => $endpoint,
    'token' => $token,
    'keys' => [
        'p256dh' => $p256dh,
        'auth' => $auth
    ]
];

// Mock php://input
// We can use stream_wrapper_unregister('php') or test via internal logic
PHP;

// Let's test the controller logic directly using custom stream wrapper or php://input replacement
class StreamWrapper {
    public $context;
    private static $content = '';
    private $position = 0;

    public static function setContent(string $content): void {
        self::$content = $content;
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr(self::$content, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_tell() {
        return $this->position;
    }

    public function stream_eof() {
        return $this->position >= strlen(self::$content);
    }

    public function stream_stat() {
        return [];
    }
}

// Register stream wrapper to mock php://input if needed, or invoke via temporary script / stream
// In PHP, stream_wrapper_unregister('php') and stream_wrapper_register('php', StreamWrapper::class) allows mocking php://input!
stream_wrapper_unregister('php');
stream_wrapper_register('php', StreamWrapper::class);

$_SESSION = [
    'id' => $testUserId,
    'token' => $testToken,
];
$_SERVER['HTTP_X_CSRF_TOKEN'] = $testToken;
$_SERVER['HTTP_USER_AGENT'] = 'TestAgent/1.0';

// Test 1: 15 consecutive idempotent calls for existing subscription must NEVER 429
echo "[1/3] Testing Idempotent Subscription Sync (15 Rapid Requests)...\n";
$idempotentPayload = json_encode([
    'endpoint' => $testEndpoint,
    'token' => $testToken,
    'keys' => [
        'p256dh' => $p256dhKey,
        'auth' => $authKey
    ]
]);

$allPassed = true;
for ($i = 1; $i <= 15; $i++) {
    StreamWrapper::setContent($idempotentPayload);
    $GLOBALS['__testResponse'] = null;
    try {
        NotificationController::postSubscriberData();
        $res = $GLOBALS['__testResponse'] ?? [];
        if (!isset($res['status']) || $res['status'] !== 'success') {
            $allPassed = false;
            break;
        }
    } catch (\Throwable $e) {
        $allPassed = false;
        echo "  ❌ Failed on iteration {$i} with: " . $e->getMessage() . "\n";
        break;
    }
}
assertTest($allPassed, '15 consecutive identical push syncs passed without 429 rate-limiting');

// Test 2: Key rotation on existing endpoint succeeds
echo "\n[2/3] Testing Key Rotation on Existing Endpoint...\n";
$rotatedAuthKey = base64_encode(random_bytes(16));
$rotationPayload = json_encode([
    'endpoint' => $testEndpoint,
    'token' => $testToken,
    'keys' => [
        'p256dh' => $p256dhKey,
        'auth' => $rotatedAuthKey
    ]
]);
StreamWrapper::setContent($rotationPayload);
$GLOBALS['__testResponse'] = null;
NotificationController::postSubscriberData();
$resRotate = $GLOBALS['__testResponse'] ?? [];
assertTest(isset($resRotate['status']) && $resRotate['status'] === 'success', 'Key rotation succeeded with 200');

$checkStmt = $pdo->prepare('SELECT authKey FROM pushNotification WHERE id = ? AND endpoint = ?');
$checkStmt->execute([$testUserId, $testEndpoint]);
$updatedAuth = $checkStmt->fetchColumn();
assertTest($updatedAuth === $rotatedAuthKey, 'Rotated authKey correctly updated in database');

// Test 3: New subscription endpoint flooding is rate-limited at 10 / min
echo "\n[3/3] Testing Flood Protection on Brand New Subscriptions (Max 10 / min)...\n";
$floodCaught429 = false;
$newEndpointsSuccess = 0;

for ($j = 1; $j <= 12; $j++) {
    $newEp = 'https://fcm.googleapis.com/fcm/send/new_' . bin2hex(random_bytes(8)) . '_' . $j;
    $newPayload = json_encode([
        'endpoint' => $newEp,
        'token' => $testToken,
        'keys' => [
            'p256dh' => $p256dhKey,
            'auth' => $authKey
        ]
    ]);
    StreamWrapper::setContent($newPayload);
    $GLOBALS['__testResponse'] = null;
    try {
        NotificationController::postSubscriberData();
        $resNew = $GLOBALS['__testResponse'] ?? [];
        if (isset($resNew['status']) && $resNew['status'] === 'success') {
            $newEndpointsSuccess++;
        }
    } catch (\Src\Exceptions\HttpException $e) {
        if ($e->getCode() === 429 && str_contains($e->getMessage(), 'Too many subscription requests')) {
            $floodCaught429 = true;
            break;
        }
    } catch (\Throwable $e) {
    }
}

assertTest($newEndpointsSuccess === 10, "First 10 new endpoints allowed (actual: {$newEndpointsSuccess})");
assertTest($floodCaught429, '11th new endpoint registration rejected with 429 Too many subscription requests');

// Restore stream wrapper
stream_wrapper_restore('php');

// Cleanup
$pdo->prepare('DELETE FROM pushNotification WHERE id = ?')->execute([$testUserId]);

echo "\n=================================================================\n";
echo "📊 SUITE SUMMARY: {$testsPassed} passed, {$testsFailed} failed\n";
echo "=================================================================\n";

if ($testsFailed > 0) {
    exit(1);
}

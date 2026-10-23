<?php

declare(strict_types=1);

ob_start();

require_once __DIR__ . '/../app/config/init.php';

use App\controller\members\Organogram;
use App\services\LineagePromptService;
use Src\Db;

echo "=================================================================\n";
echo "🧪 RUNNING LINEAGE PROMPT ENGINE VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$pdo = Db::connect2();

$testFamCode = 'PROMPT_TEST_' . bin2hex(random_bytes(4));
$senderId = 'prompt_sender_' . bin2hex(random_bytes(4));
$targetId = 'prompt_target_' . bin2hex(random_bytes(4));
$outsiderId = 'prompt_outsider_' . bin2hex(random_bytes(4));

$passed = 0;
$total = 7;

function assertCondition(bool $cond, string $name): void {
    global $passed;
    if ($cond) {
        echo "  ✅ PASS: {$name}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$name}\n";
    }
}

try {
    // 1. Fixture Setup
    $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
        VALUES (?, 'John', 'Doe', ?, 'Male', 1, 'Jan', 1990, 0)
    ")->execute([$senderId, $testFamCode]);

    $pdo->prepare("
        INSERT INTO contact (id, email, country, mobile)
        VALUES (?, 'sender@example.com', 'UK', '07123456789')
    ")->execute([$senderId]);

    $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
        VALUES (?, 'Olusola', 'Olaogun', ?, 'Male', 1, 'Jan', 1985, 0)
    ")->execute([$targetId, $testFamCode]);

    $pdo->prepare("
        INSERT INTO contact (id, email, country, mobile)
        VALUES (?, 'olusola@example.com', 'UK', '07123456788')
    ")->execute([$targetId]);

    $pdo->prepare("
        INSERT INTO personal (id, firstName, lastName, famCode, gender, day, month, year, kids)
        VALUES (?, 'Eve', 'Outsider', 'OTHER_FAMILY_99', 'Female', 1, 'Jan', 1992, 0)
    ")->execute([$outsiderId]);

    $pdo->prepare("
        INSERT INTO contact (id, email, country, mobile)
        VALUES (?, 'outsider@example.com', 'UK', '07123456787')
    ")->execute([$outsiderId]);

    echo "📦 Fixtures provisioned successfully.\n\n";

    // Test 1: Self-prompt is strictly rejected
    $service = new LineagePromptService();
    $result1 = $service->sendTreeBuildPrompt($senderId, $senderId, 'Check your tree');
    assertCondition(
        $result1['success'] === false && str_contains($result1['message'], 'cannot prompt yourself'),
        "Test 1: Self-prompt is strictly rejected (422)"
    );

    // Test 2: Cross-family prompt without view access is rejected
    $_SESSION = [
        'id' => $outsiderId,
        'famCode' => 'OTHER_FAMILY_99'
    ];
    $result2 = $service->sendTreeBuildPrompt($outsiderId, $targetId);
    assertCondition(
        $result2['success'] === false && str_contains($result2['message'], 'own family network'),
        "Test 2: Cross-family prompt is rejected without view access"
    );

    // Test 3: Successful prompt dispatches notification
    $_SESSION = [
        'id' => $senderId,
        'famCode' => $testFamCode
    ];
    $result3 = $service->sendTreeBuildPrompt($senderId, $targetId, 'Please add your parents!');
    $stmtNotif = $pdo->prepare("
        SELECT * FROM notification 
        WHERE sender_id = ? AND receiver_id = ? AND notification_type = 'Lineage'
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmtNotif->execute([$senderId, $targetId]);
    $notifRow = $stmtNotif->fetch(PDO::FETCH_ASSOC);

    assertCondition(
        $result3['success'] === true 
        && str_contains($result3['message'], 'Olusola')
        && is_array($notifRow)
        && stripos((string)($notifRow['notification_name'] ?? ''), 'family tree') !== false
        && str_contains((string)($notifRow['action_url'] ?? ''), '/organogram'),
        "Test 3: Successful prompt dispatches notification & email queue pointing to family tree"
    );

    // Test 4: Subsequent prompt within 24 hours is debounced
    $result4 = $service->sendTreeBuildPrompt($senderId, $targetId);
    assertCondition(
        $result4['success'] === false && str_contains($result4['message'], 'already sent an invitation'),
        "Test 4: Subsequent prompt within 24 hours is debounced (anti-spam)"
    );

    // Test 5: Controller requires valid CSRF token
    $_SESSION = [
        'id' => $senderId,
        'famCode' => $testFamCode,
        'token' => 'real_token_123'
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_XSRF_TOKEN'] = 'invalid_tampered_token';
    $_POST = ['target_user_id' => $targetId];

    $controller = (new ReflectionClass(Organogram::class))->newInstanceWithoutConstructor();
    ob_start();
    $controller->promptBuildTree();
    $raw5 = ob_get_clean() ?: '';
    $resp5 = json_decode($raw5, true) ?: [];
    assertCondition(
        ($resp5['success'] ?? true) === false && (http_response_code() === 403 || str_contains($resp5['error'] ?? '', 'CSRF')),
        "Test 5: Controller requires valid CSRF token (HTTP 403)"
    );

    // Test 6: Controller requires authentication
    $csrfToken = 'test_csrf_token_' . bin2hex(random_bytes(8));
    $_SESSION = [
        'token' => $csrfToken
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_XSRF_TOKEN'] = $csrfToken;
    $_POST = [
        'target_user_id' => $targetId,
        'token' => $csrfToken
    ];
    http_response_code(200);

    ob_start();
    $controller->promptBuildTree();
    $raw6 = ob_get_clean() ?: '';
    $resp6 = json_decode($raw6, true) ?: [];
    assertCondition(
        ($resp6['success'] ?? true) === false && (http_response_code() === 401 || str_contains($resp6['error'] ?? '', 'sign in')),
        "Test 6: Controller requires authentication (HTTP 401)"
    );

    // Test 7: Controller validates target_user_id presence
    $_SESSION = [
        'id' => $senderId,
        'famCode' => $testFamCode,
        'token' => $csrfToken
    ];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_X_XSRF_TOKEN'] = $csrfToken;
    $_POST = [
        'target_user_id' => '',
        'token' => $csrfToken
    ];
    http_response_code(200);

    ob_start();
    $controller->promptBuildTree();
    $raw7 = ob_get_clean() ?: '';
    $resp7 = json_decode($raw7, true) ?: [];
    assertCondition(
        ($resp7['success'] ?? true) === false && (http_response_code() === 400 || str_contains($resp7['error'] ?? '', 'required')),
        "Test 7: Controller validates target_user_id presence (HTTP 400)"
    );

} finally {
    // Teardown
    $stmt = $pdo->prepare("DELETE FROM notification WHERE sender_id IN (?, ?, ?) OR receiver_id IN (?, ?, ?)");
    $stmt->execute([$senderId, $targetId, $outsiderId, $senderId, $targetId, $outsiderId]);

    $stmt = $pdo->prepare("DELETE FROM contact WHERE id IN (?, ?, ?)");
    $stmt->execute([$senderId, $targetId, $outsiderId]);

    $stmt = $pdo->prepare("DELETE FROM personal WHERE id IN (?, ?, ?)");
    $stmt->execute([$senderId, $targetId, $outsiderId]);
    echo "\n🧹 Fixtures cleaned up.\n";
}

echo "\n=================================================================\n";
echo "SCORE: {$passed}/{$total} PASSED\n";
echo "=================================================================\n";

ob_end_flush();

if ($passed !== $total) {
    exit(1);
}

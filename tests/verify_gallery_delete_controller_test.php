<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\controller\members\ProfilePage;
use App\model\Post;
use Src\Db;

echo "======================================================================\n";
echo "  🧪  PHOTO GALLERY CONTROLLER INTEGRATION TEST                     \n";
echo "======================================================================\n";

$pdo = Db::connect2();
$reflector = new \ReflectionClass(ProfilePage::class);
$controller = $reflector->newInstanceWithoutConstructor();

$testUserId = 'test_ctrl_user_' . substr(md5((string)microtime(true)), 0, 8);
$testImg = 'test_ctrl_img_' . uniqid() . '.jpg';
$postDir = $rootDir . '/resources/images/post/';
file_put_contents($postDir . $testImg, 'DUMMY_CTRL_IMG');

$_SESSION['id'] = $testUserId;
$_SESSION['token'] = 'valid_test_token_123';

try {
    // 1. Provision record in images
    $stmt = $pdo->prepare("INSERT INTO images (id, img, where_from, is_public, created_at) VALUES (?, ?, 'gallery', 1, CURRENT_TIMESTAMP)");
    $stmt->execute([$testUserId, $testImg]);

    // 2. Test missing imageName (Expect 400)
    $_POST = [];
    ob_start();
    $controller->deleteImage();
    $out = ob_get_clean();
    $resp = json_decode((string)$out, true);
    assert(http_response_code() === 400 || ($resp['status'] ?? 0) === 400, 'ASSERTION FAILED: Missing imageName should return 400');
    assert($resp['success'] === false, 'ASSERTION FAILED: Response success should be false');
    echo "  ✅ Missing imageName returns 400 Bad Request with descriptive message\n";

    // 3. Test CSRF token mismatch (Expect 403)
    $_POST = [
        'imageName' => $testImg,
        'token' => 'invalid_csrf_token'
    ];
    ob_start();
    $controller->deleteImage();
    $out = ob_get_clean();
    $resp = json_decode((string)$out, true);
    assert(http_response_code() === 403 || ($resp['status'] ?? 0) === 403, 'ASSERTION FAILED: CSRF mismatch should return 403');
    echo "  ✅ CSRF mismatch rejected with 403 Forbidden\n";

    // 4. Test Legitimate deletion with matching CSRF token (Expect 200)
    $_POST = [
        'imageName' => $testImg,
        'token' => 'valid_test_token_123'
    ];
    ob_start();
    $controller->deleteImage();
    $out = ob_get_clean();
    $resp = json_decode((string)$out, true);
    assert(http_response_code() === 200 || ($resp['status'] ?? 0) === 200, 'ASSERTION FAILED: Legitimate owner deletion should return 200');
    assert($resp['success'] === true, 'ASSERTION FAILED: Response success should be true');
    assert(str_contains($resp['message'], 'successfully'), 'ASSERTION FAILED: Success message expected');
    echo "  ✅ Legitimate owner deletion returns 200 OK with success JSON\n";

    echo "\n======================================================================\n";
    echo "🎉 ALL CONTROLLER INTEGRATION CHECKS PASSED (100%)\n";
    echo "======================================================================\n";
} finally {
    $pdo->prepare("DELETE FROM images WHERE id = ?")->execute([$testUserId]);
    if (file_exists($postDir . $testImg)) {
        @unlink($postDir . $testImg);
    }
    unset($_SESSION['id'], $_SESSION['token']);
    echo "🧹 Controller test environment cleaned up cleanly.\n";
}

<?php

declare(strict_types=1);

/**
 * FamilyPlatform Batch (Multiple) Photo Deletion Verification & Red Team Suite
 *
 * Verifies:
 * 1. User can successfully delete multiple photos in a single atomic batch.
 * 2. Partial IDOR / Batch Smuggling Attack: If User B tries to delete [User B's img, User A's img],
 *    the entire transaction is rolled back, returning 403 Forbidden, and ZERO images are deleted.
 * 3. Batch size limit enforcement: Requesting >50 images returns 400 Bad Request.
 * 4. Empty batch array returns 400 Bad Request.
 * 5. Controller handles `imageNames` array payload with CSRF protection and returns accurate deleted_count.
 * 6. Zero DB and file pollution: all test artifacts cleaned up cleanly.
 */

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
echo "  🛡️  BATCH PHOTO DELETION & ATOMIC IDOR ROLLBACK TEST SUITE          \n";
echo "======================================================================\n";

$pdo = Db::connect2();
$postDir = $rootDir . '/resources/images/post/';
if (!is_dir($postDir)) {
    mkdir($postDir, 0755, true);
}

$userA = 'test_batch_a_' . substr(md5((string)microtime(true)), 0, 8);
$userB = 'test_batch_b_' . substr(md5((string)microtime(true)), 0, 8);

$imgA1 = 'test_batch_a1_' . uniqid() . '.jpg';
$imgA2 = 'test_batch_a2_' . uniqid() . '.jpg';
$imgA3 = 'test_batch_a3_' . uniqid() . '.jpg';
$imgB1 = 'test_batch_b1_' . uniqid() . '.jpg';

file_put_contents($postDir . $imgA1, 'DUMMY_A1');
file_put_contents($postDir . $imgA2, 'DUMMY_A2');
file_put_contents($postDir . $imgA3, 'DUMMY_A3');
file_put_contents($postDir . $imgB1, 'DUMMY_B1');

try {
    // -------------------------------------------------------------------------
    // Phase 1: Provision test data for User A and User B
    // -------------------------------------------------------------------------
    echo "\n[Phase 1] Provisioning test records...\n";
    $ins = $pdo->prepare("INSERT INTO images (id, img, where_from, is_public, created_at) VALUES (?, ?, 'gallery', 1, CURRENT_TIMESTAMP)");
    $ins->execute([$userA, $imgA1]);
    $ins->execute([$userA, $imgA2]);
    $ins->execute([$userA, $imgA3]);
    $ins->execute([$userB, $imgB1]);

    $imgsA = Post::getAllImagesByAuthor($userA);
    assert(count($imgsA) >= 3, 'ASSERTION FAILED: User A should have at least 3 provisioned images');
    echo "  ✅ Provisioned 3 images for User A and 1 image for User B\n";

    // -------------------------------------------------------------------------
    // Phase 2: Batch Smuggling / Partial IDOR Attack
    // User B attempts to delete [their own image $imgB1 AND User A's image $imgA1]
    // -------------------------------------------------------------------------
    echo "\n[Phase 2] Red Team Attack: Batch Smuggling & Atomic IDOR Rollback...\n";
    $attackResult = Post::deleteMultipleGalleryImages($userB, [$imgB1, $imgA1]);
    assert($attackResult['status'] === 403, 'ASSERTION FAILED: Smuggled batch containing foreign image must be rejected with 403');
    assert($attackResult['success'] === false, 'ASSERTION FAILED: Attack result success must be false');
    assert(str_contains($attackResult['error'] ?? '', 'only delete your own pictures'), 'ASSERTION FAILED: Error message should cite ownership');

    // Verify User B's own image was NOT deleted due to atomic rollback
    $chkRollback = $pdo->prepare("SELECT deleted_at FROM images WHERE id = ? AND img = ?");
    $chkRollback->execute([$userB, $imgB1]);
    $bRow = $chkRollback->fetch(PDO::FETCH_ASSOC);
    assert(empty($bRow['deleted_at']), 'ASSERTION FAILED: Atomic transaction must rollback User B own image deletion on mixed batch');

    // Verify User A's image remains untouched
    $chkRollback->execute([$userA, $imgA1]);
    $aRow = $chkRollback->fetch(PDO::FETCH_ASSOC);
    assert(empty($aRow['deleted_at']), 'ASSERTION FAILED: User A image must remain untouched');
    echo "  ✅ Batch Smuggling Attack Neutralized: All-or-nothing rollback confirmed; 0 rows modified\n";

    // -------------------------------------------------------------------------
    // Phase 3: Legitimate Multiple Photo Deletion
    // User A deletes [$imgA1, $imgA2]
    // -------------------------------------------------------------------------
    echo "\n[Phase 3] User A legitimately deletes multiple photos...\n";
    $validBatchResult = Post::deleteMultipleGalleryImages($userA, [$imgA1, $imgA2]);
    assert($validBatchResult['status'] === 200, 'ASSERTION FAILED: Legitimate batch should return 200');
    assert($validBatchResult['success'] === true, 'ASSERTION FAILED: Legitimate batch success should be true');
    assert($validBatchResult['deleted_count'] === 2, 'ASSERTION FAILED: deleted_count must equal 2');

    $chkDel = $pdo->prepare("SELECT deleted_at FROM images WHERE id = ? AND img = ?");
    $chkDel->execute([$userA, $imgA1]);
    assert(!empty($chkDel->fetch(PDO::FETCH_ASSOC)['deleted_at']), 'ASSERTION FAILED: $imgA1 must be marked deleted_at');
    $chkDel->execute([$userA, $imgA2]);
    assert(!empty($chkDel->fetch(PDO::FETCH_ASSOC)['deleted_at']), 'ASSERTION FAILED: $imgA2 must be marked deleted_at');

    // User A's 3rd image remains alive
    $chkDel->execute([$userA, $imgA3]);
    assert(empty($chkDel->fetch(PDO::FETCH_ASSOC)['deleted_at']), 'ASSERTION FAILED: $imgA3 must remain active');

    $galleryAfter = Post::getAllImagesByAuthor($userA);
    $activeNames = array_column($galleryAfter, 'img');
    assert(!in_array($imgA1, $activeNames, true), 'ASSERTION FAILED: $imgA1 must be excluded from gallery');
    assert(!in_array($imgA2, $activeNames, true), 'ASSERTION FAILED: $imgA2 must be excluded from gallery');
    assert(in_array($imgA3, $activeNames, true), 'ASSERTION FAILED: $imgA3 must remain in gallery');
    echo "  ✅ Multiple photo deletion succeeded atomically (2 deleted, 1 remaining)\n";

    // -------------------------------------------------------------------------
    // Phase 4: Batch Size Cap & Input Validation
    // -------------------------------------------------------------------------
    echo "\n[Phase 4] Testing boundary conditions (empty batch & >50 cap)...\n";
    $emptyResult = Post::deleteMultipleGalleryImages($userA, []);
    assert($emptyResult['status'] === 400, 'ASSERTION FAILED: Empty batch must return 400');

    $hugeList = array_map(fn($i) => "overflow_{$i}.jpg", range(1, 55));
    $oversizeResult = Post::deleteMultipleGalleryImages($userA, $hugeList);
    assert($oversizeResult['status'] === 400, 'ASSERTION FAILED: Oversized batch (>50) must return 400');
    assert(str_contains($oversizeResult['error'] ?? '', 'maximum of 50'), 'ASSERTION FAILED: Expected maximum 50 error');
    echo "  ✅ Boundary conditions passed: empty batch (400) and >50 cap (400) enforced\n";

    // -------------------------------------------------------------------------
    // Phase 5: Controller Integration for Batch Deletion
    // -------------------------------------------------------------------------
    echo "\n[Phase 5] Controller integration test for batch payload...\n";
    $reflector = new \ReflectionClass(ProfilePage::class);
    $controller = $reflector->newInstanceWithoutConstructor();

    $_SESSION['id'] = $userA;
    $_SESSION['token'] = 'batch_csrf_token_999';

    // Call deleteImage with imageNames array
    $_POST = [
        'imageNames' => [$imgA3],
        'token' => 'batch_csrf_token_999'
    ];
    ob_start();
    $controller->deleteImage();
    $ctrlOut = ob_get_clean();
    $ctrlResp = json_decode((string)$ctrlOut, true);

    assert(http_response_code() === 200 || ($ctrlResp['status'] ?? 0) === 200, 'ASSERTION FAILED: Controller batch delete should return 200');
    assert(($ctrlResp['deleted_count'] ?? 0) === 1, 'ASSERTION FAILED: Controller response deleted_count should be 1');
    assert($ctrlResp['success'] === true, 'ASSERTION FAILED: Controller response success must be true');
    echo "  ✅ Controller processed batch deletion via imageNames payload with 200 OK\n";

    echo "\n======================================================================\n";
    echo "🎉 ALL BATCH DELETION & ATOMIC RED TEAM CHECKS PASSED (100%)\n";
    echo "======================================================================\n";
} finally {
    $pdo->prepare("DELETE FROM images WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $pdo->prepare("DELETE FROM post WHERE id IN (?, ?)")->execute([$userA, $userB]);

    foreach ([$imgA1, $imgA2, $imgA3, $imgB1] as $f) {
        if (file_exists($postDir . $f)) {
            @unlink($postDir . $f);
        }
    }
    unset($_SESSION['id'], $_SESSION['token'], $_POST);
    echo "🧹 Batch test fixtures cleanly deleted.\n";
}

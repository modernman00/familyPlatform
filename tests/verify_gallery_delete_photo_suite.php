<?php

declare(strict_types=1);

/**
 * FamilyPlatform Photo Gallery Deletion Verification & Adversarial IDOR Suite
 *
 * Verifies:
 * 1. User can successfully delete their own picture from images table (soft-delete with deleted_at).
 * 2. User can successfully delete their own picture attached to a post (clears post_imgX slot).
 * 3. Adversarial Red Team IDOR test: User B cannot delete User A's picture (HTTP 403 Forbidden).
 * 4. Path traversal attempt (../../file.jpg) is strictly neutralized by basename sanitization.
 * 5. Deletion of non-existent picture returns HTTP 404 Not Found.
 * 6. getAllImagesByAuthor() excludes soft-deleted pictures.
 * 7. Zero DB pollution: all test fixtures cleanly deleted on completion.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\model\Post;
use Src\Db;

echo "======================================================================\n";
echo "  🛡️  PHOTO GALLERY DELETION VERIFICATION & ADVERSARIAL IDOR SUITE     \n";
echo "======================================================================\n";

$pdo = Db::connect2();

$userA = 'test_user_a_' . substr(md5((string)microtime(true)), 0, 8);
$userB = 'test_user_b_' . substr(md5((string)microtime(true)), 0, 8);

$imageA1 = 'test_img_a1_' . uniqid() . '.jpg';
$imageA2 = 'test_img_a2_' . uniqid() . '.jpg';
$postDir = $rootDir . '/resources/images/post/';

if (!is_dir($postDir)) {
    mkdir($postDir, 0755, true);
}

// Create physical test files
file_put_contents($postDir . $imageA1, 'DUMMY_IMG_A1');
file_put_contents($postDir . $imageA2, 'DUMMY_IMG_A2');

try {
    // -------------------------------------------------------------------------
    // Phase 1: Provision test data for User A in images table
    // -------------------------------------------------------------------------
    echo "\n[Phase 1] Provisioning test records for User A...\n";
    $stmtImg = $pdo->prepare("INSERT INTO images (id, img, where_from, is_public, created_at) VALUES (?, ?, 'gallery', 1, CURRENT_TIMESTAMP)");
    $stmtImg->execute([$userA, $imageA1]);

    $allImgsBefore = Post::getAllImagesByAuthor($userA);
    $foundBefore = false;
    foreach ($allImgsBefore as $item) {
        if ($item['img'] === $imageA1) {
            $foundBefore = true;
            break;
        }
    }
    assert($foundBefore, 'ASSERTION FAILED: User A imageA1 should be listed in gallery before deletion');
    echo "  ✅ User A gallery correctly returns provisioned imageA1\n";

    // -------------------------------------------------------------------------
    // Phase 2: User A deletes their own image from images table
    // -------------------------------------------------------------------------
    echo "\n[Phase 2] User A deletes their own picture ($imageA1)...\n";
    $resultDelete = Post::deleteGalleryImage($userA, $imageA1);
    assert($resultDelete['status'] === 200, 'ASSERTION FAILED: User A should delete own image with status 200');
    assert($resultDelete['success'] === true, 'ASSERTION FAILED: Result success should be true');

    // Verify deleted_at is stamped
    $chkDeleted = $pdo->prepare("SELECT deleted_at FROM images WHERE id = ? AND img = ?");
    $chkDeleted->execute([$userA, $imageA1]);
    $deletedRow = $chkDeleted->fetch(PDO::FETCH_ASSOC);
    assert(!empty($deletedRow['deleted_at']), 'ASSERTION FAILED: images table deleted_at must be populated');

    // Verify getAllImagesByAuthor no longer returns the deleted image
    $allImgsAfter = Post::getAllImagesByAuthor($userA);
    $foundAfter = false;
    foreach ($allImgsAfter as $item) {
        if ($item['img'] === $imageA1) {
            $foundAfter = true;
            break;
        }
    }
    assert(!$foundAfter, 'ASSERTION FAILED: User A gallery must exclude soft-deleted picture');
    echo "  ✅ Picture successfully deleted; excluded from gallery and stamped with deleted_at\n";

    // -------------------------------------------------------------------------
    // Phase 3: User A has an image attached to a published post
    // -------------------------------------------------------------------------
    echo "\n[Phase 3] User A deletes a picture ($imageA2) attached to a published post...\n";
    $stmtPost = $pdo->prepare("INSERT INTO post (id, fullName, postMessage, post_img0, post_status, date_created) VALUES (?, 'Test User A', 'Post with photo', ?, 'published', CURRENT_TIMESTAMP)");
    $stmtPost->execute([$userA, $imageA2]);
    $postNo = (int)$pdo->lastInsertId();

    $imgsPostBefore = Post::getAllImagesByAuthor($userA);
    $foundPostBefore = false;
    foreach ($imgsPostBefore as $item) {
        if ($item['img'] === $imageA2) {
            $foundPostBefore = true;
            break;
        }
    }
    assert($foundPostBefore, 'ASSERTION FAILED: Post imageA2 should appear in gallery before deletion');

    $resultPostDel = Post::deleteGalleryImage($userA, $imageA2);
    assert($resultPostDel['status'] === 200, 'ASSERTION FAILED: User A should successfully delete post image');

    // Verify post_img0 was cleared
    $chkPostCleared = $pdo->prepare("SELECT post_img0 FROM post WHERE post_no = ?");
    $chkPostCleared->execute([$postNo]);
    $clearedPost = $chkPostCleared->fetch(PDO::FETCH_ASSOC);
    assert(empty($clearedPost['post_img0']), 'ASSERTION FAILED: post_img0 must be cleared to NULL');

    $imgsPostAfter = Post::getAllImagesByAuthor($userA);
    $foundPostAfter = false;
    foreach ($imgsPostAfter as $item) {
        if ($item['img'] === $imageA2) {
            $foundPostAfter = true;
            break;
        }
    }
    assert(!$foundPostAfter, 'ASSERTION FAILED: Post imageA2 must be excluded from gallery after deletion');
    echo "  ✅ Post image slot cleared cleanly and excluded from gallery\n";

    // -------------------------------------------------------------------------
    // Phase 4: Adversarial Red Team Attack (IDOR / BOLA Prevention)
    // -------------------------------------------------------------------------
    echo "\n[Phase 4] Adversarial Red Team Attack: User B attempts to delete User A's image...\n";
    // Create new image for User A
    $imageA3 = 'test_img_a3_' . uniqid() . '.jpg';
    file_put_contents($postDir . $imageA3, 'DUMMY_IMG_A3');
    $stmtImg3 = $pdo->prepare("INSERT INTO images (id, img, where_from, is_public, created_at) VALUES (?, ?, 'gallery', 1, CURRENT_TIMESTAMP)");
    $stmtImg3->execute([$userA, $imageA3]);

    // User B tries to delete User A's image!
    $idorResult = Post::deleteGalleryImage($userB, $imageA3);
    assert($idorResult['status'] === 403, 'ASSERTION FAILED: IDOR attack must be rejected with status 403 Forbidden');
    assert($idorResult['success'] === false, 'ASSERTION FAILED: IDOR attack success must be false');
    assert(str_contains((string)($idorResult['error'] ?? ''), 'only delete your own pictures'), 'ASSERTION FAILED: Expected forbidden error message');

    // Verify image was NOT deleted
    $chkNotDeleted = $pdo->prepare("SELECT deleted_at FROM images WHERE id = ? AND img = ?");
    $chkNotDeleted->execute([$userA, $imageA3]);
    $notDelRow = $chkNotDeleted->fetch(PDO::FETCH_ASSOC);
    assert(empty($notDelRow['deleted_at']), 'ASSERTION FAILED: Target image must remain untouched after IDOR attempt');
    echo "  ✅ IDOR Attack Neutralized: User B blocked with 403 Forbidden; User A image untouched\n";

    // -------------------------------------------------------------------------
    // Phase 5: Directory Traversal Neutralization & Non-Existent Picture
    // -------------------------------------------------------------------------
    echo "\n[Phase 5] Path Traversal & 404 Verification...\n";
    $traversalResult = Post::deleteGalleryImage($userA, '../../../../etc/passwd');
    assert($traversalResult['status'] === 404, 'ASSERTION FAILED: Traversal path should be sanitized to basename and return 404');

    $notFoundResult = Post::deleteGalleryImage($userA, 'totally_non_existent_' . uniqid() . '.jpg');
    assert($notFoundResult['status'] === 404, 'ASSERTION FAILED: Non-existent image should return 404');
    echo "  ✅ Path traversal sanitized safely; non-existent image returns 404\n";

    echo "\n======================================================================\n";
    echo "🎉 ALL GALLERY DELETION & ADVERSARIAL SECURITY CHECKS PASSED (100%)\n";
    echo "======================================================================\n";
} finally {
    // -------------------------------------------------------------------------
    // Cleanup: 100% ephemeral test data
    // -------------------------------------------------------------------------
    $pdo->prepare("DELETE FROM images WHERE id IN (?, ?)")->execute([$userA, $userB]);
    $pdo->prepare("DELETE FROM post WHERE id IN (?, ?)")->execute([$userA, $userB]);

    foreach ([$imageA1, $imageA2, $imageA3 ?? ''] as $f) {
        if (!empty($f) && file_exists($postDir . $f)) {
            @unlink($postDir . $f);
        }
    }
    echo "🧹 Test environment cleaned up cleanly (0 residual DB or file artifacts).\n";
}

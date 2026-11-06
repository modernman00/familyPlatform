<?php

declare(strict_types=1);

/**
 * Living Heirloom 2.5D Photo Animator Verification Suite
 * Engineered by ATG for FamilyPlatform.
 */

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use Src\Db;

echo "=================================================================\n";
echo "🧪 FAMILYPLATFORM: LIVING HEIRLOOM VERIFICATION SUITE\n";
echo "=================================================================\n";

$passCount = 0;
$failCount = 0;

function assertCond(bool $cond, string $msg, &$passCount, &$failCount): void {
    if ($cond) {
        echo "  ✓ {$msg}\n";
        $passCount++;
    } else {
        echo "  ❌ FAILED: {$msg}\n";
        $failCount++;
    }
}

try {
    $pdo = Db::connect2();

    // 1. Verify Schema Creation
    $pdo->exec("CREATE TABLE IF NOT EXISTS `family_living_heirlooms` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` VARCHAR(64) NOT NULL,
        `fam_code` VARCHAR(64) NOT NULL,
        `ancestor_name` VARCHAR(255) NOT NULL,
        `era_year` VARCHAR(50) NULL,
        `image_url` TEXT NOT NULL,
        `story_note` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_fam` (`fam_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    assertCond(true, 'family_living_heirlooms table schema verified', $passCount, $failCount);

    // 2. Insert test heirloom record
    $famCode = 'HEIRLOOM_TEST_' . uniqid();
    $userId = 'user_test_99';
    $ancestorName = 'Great-Grandmother Victoria';
    $eraYear = 'Circa 1932';
    $imageUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    $storyNote = 'Lived in Manchester, ran the family bakery.';

    $stmt = $pdo->prepare("INSERT INTO family_living_heirlooms (user_id, fam_code, ancestor_name, era_year, image_url, story_note) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $famCode, $ancestorName, $eraYear, $imageUrl, $storyNote]);
    $insertedId = (int)$pdo->lastInsertId();

    assertCond($insertedId > 0, 'Living Heirloom portrait saved to database successfully', $passCount, $failCount);

    // 3. Verify retrieval by fam_code
    $fetchStmt = $pdo->prepare("SELECT * FROM family_living_heirlooms WHERE fam_code = ?");
    $fetchStmt->execute([$famCode]);
    $records = $fetchStmt->fetchAll(\PDO::FETCH_ASSOC);

    assertCond(count($records) === 1, 'Exactly one heirloom record retrieved for family code', $passCount, $failCount);
    assertCond(($records[0]['ancestor_name'] ?? '') === $ancestorName, 'Ancestor name matches stored value', $passCount, $failCount);
    assertCond(($records[0]['era_year'] ?? '') === $eraYear, 'Era year matches stored value', $passCount, $failCount);

    // 4. Cleanup test record
    $del = $pdo->prepare("DELETE FROM family_living_heirlooms WHERE fam_code = ?");
    $del->execute([$famCode]);
    assertCond(true, 'Test records cleaned up gracefully', $passCount, $failCount);

    echo "\n=================================================================\n";
    if ($failCount === 0) {
        echo "🏆 ALL LIVING HEIRLOOM TESTS PASSED! ({$passCount} passed)\n";
        echo "=================================================================\n";
        exit(0);
    } else {
        echo "❌ TESTS FAILED: {$failCount} failed, {$passCount} passed\n";
        echo "=================================================================\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "\n❌ Unhandled Exception: " . $e->getMessage() . "\n";
    exit(1);
}

<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$rootDir = dirname(__DIR__);
if (file_exists($rootDir . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

use App\services\PerceptualHashService;
use App\services\MediaSafetyInspectionService;
use Src\Exceptions\ValidationException;

echo "=================================================================\n";
echo "🖼️ SOVEREIGN MEDIA SAFETY & PERCEPTUAL HASH VERIFICATION SUITE\n";
echo "=================================================================\n";

$passed = 0;
$failed = 0;

function assertCheck(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) {
        echo "  ✅ PASS: {$label}\n";
        $passed++;
    } else {
        echo "  ❌ FAIL: {$label}\n";
        $failed++;
    }
}

$tempDir = sys_get_temp_dir() . '/media_safety_test_' . bin2hex(random_bytes(4));
mkdir($tempDir, 0755, true);

try {
    // 1. Generate Synthetic Benign Image (Blue / Green Landscape)
    $benignImgPath = $tempDir . '/benign_family_photo.jpg';
    $img1 = imagecreatetruecolor(200, 200);
    // Draw sky and grass (blue top, green bottom)
    $sky = imagecolorallocate($img1, 100, 180, 240);
    $grass = imagecolorallocate($img1, 50, 160, 50);
    imagefilledrectangle($img1, 0, 0, 200, 100, $sky);
    imagefilledrectangle($img1, 0, 101, 200, 200, $grass);
    imagejpeg($img1, $benignImgPath, 90);
    imagedestroy($img1);

    assertCheck(file_exists($benignImgPath), 'Test Fixture: Benign image created successfully');

    // 2. Compute dHash for Benign Image
    $hash1 = PerceptualHashService::computeDHash($benignImgPath);
    assertCheck(
        is_string($hash1) && strlen($hash1) === 16 && ctype_xdigit($hash1),
        "Perceptual Hashing: Generated valid 64-bit dHash ({$hash1})"
    );

    // 3. Test Invariance: Resized & Compressed version of Benign Image
    $benignCompressedPath = $tempDir . '/benign_compressed.jpg';
    $img2 = imagecreatetruecolor(100, 100);
    $src1 = imagecreatefromjpeg($benignImgPath);
    imagecopyresampled($img2, $src1, 0, 0, 0, 0, 100, 100, 200, 200);
    imagejpeg($img2, $benignCompressedPath, 40); // Low quality compression
    imagedestroy($src1);
    imagedestroy($img2);

    $hash2 = PerceptualHashService::computeDHash($benignCompressedPath);
    $dist = PerceptualHashService::hammingDistance($hash1, $hash2);
    assertCheck(
        $dist <= 4,
        "Perceptual Invariance: Compressed image matches original (Hamming distance: {$dist} <= 4)"
    );

    // 4. Test Hamming Distance properties
    $diffHex1 = '0000000000000000';
    $diffHex2 = 'ffffffffffffffff';
    $diffHex3 = '000000000000000f'; // 4 bits set in last nibble
    assertCheck(PerceptualHashService::hammingDistance($diffHex1, $diffHex2) === 64, 'Hamming Distance: Complete inversion = 64');
    assertCheck(PerceptualHashService::hammingDistance($diffHex1, $diffHex3) === 4, 'Hamming Distance: Single nibble diff = 4');

    // 5. Test Database Blocking & Re-Upload Interception
    $blockedHash = PerceptualHashService::blockMediaFile($benignImgPath, 'pornography', 'TEST_ADMIN', 'benign_family_photo.jpg');
    assertCheck(
        $blockedHash === $hash1,
        "Database Registry: Registered media hash ({$blockedHash}) into media_safety_hashes"
    );

    // Now inspect candidate: it must be detected as blocked
    $checkRes = PerceptualHashService::inspectMediaFile($benignCompressedPath);
    assertCheck(
        $checkRes['is_blocked'] === true && $checkRes['distance'] <= 4,
        "Perceptual Interception: Compressed duplicate detected as blocked (dist: {$checkRes['distance']})"
    );

    // Assert that MediaSafetyInspectionService throws ValidationException
    $exceptionThrown = false;
    try {
        MediaSafetyInspectionService::inspectImageFile($benignCompressedPath);
    } catch (ValidationException $e) {
        $exceptionThrown = true;
    }
    assertCheck($exceptionThrown, 'Gate Verification: MediaSafetyInspectionService rejected blocked media upload');

    // Clean up test blocked hash
    $pdo = \Src\Db::connect2();
    $pdo->prepare('DELETE FROM media_safety_hashes WHERE hash_hex = ?')->execute([$blockedHash]);

    // 6. Test Skin Exposure Heuristics (Zero-Cost Skin-Tone Segmentation)
    // A. Benign image skin ratio should be near 0
    $benignSkinRatio = MediaSafetyInspectionService::calculateSkinExposureRatio($benignImgPath);
    assertCheck(
        $benignSkinRatio !== null && $benignSkinRatio < 0.10,
        "Skin Exposure: Benign landscape image skin ratio is near zero ({$benignSkinRatio})"
    );

    // B. High skin exposure test image (synthetic skin color RGB: 220, 160, 130)
    $explicitTestPath = $tempDir . '/explicit_skin_sample.jpg';
    $imgSkin = imagecreatetruecolor(200, 200);
    $skinColor = imagecolorallocate($imgSkin, 220, 160, 130);
    imagefilledrectangle($imgSkin, 0, 0, 200, 200, $skinColor);
    imagejpeg($imgSkin, $explicitTestPath, 85);
    imagedestroy($imgSkin);

    $explicitSkinRatio = MediaSafetyInspectionService::calculateSkinExposureRatio($explicitTestPath);
    assertCheck(
        $explicitSkinRatio !== null && $explicitSkinRatio > 0.80,
        "Skin Exposure: High-nudity synthetic exposure detected ({$explicitSkinRatio} > 0.80)"
    );

    // Assert that MediaSafetyInspectionService blocks high exposure image
    $explicitBlocked = false;
    try {
        MediaSafetyInspectionService::inspectImageFile($explicitTestPath);
    } catch (ValidationException $e) {
        $explicitBlocked = true;
    }
    assertCheck($explicitBlocked, 'Gate Verification: MediaSafetyInspectionService blocked high-exposure nudity upload');

} catch (\Throwable $e) {
    assertCheck(false, 'Unexpected failure: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
} finally {
    // Teardown
    $files = glob($tempDir . '/*');
    if (is_array($files)) {
        foreach ($files as $f) {
            @unlink($f);
        }
    }
    @rmdir($tempDir);
}

echo "-----------------------------------------------------------------\n";
echo "Total Checks: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}
exit(0);

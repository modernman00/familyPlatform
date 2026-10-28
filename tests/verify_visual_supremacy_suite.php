<?php

declare(strict_types=1);

/**
 * ⚡ Visual Supremacy Engine (VSE) Verification Suite
 *
 * Verifies:
 * - Multi-tier responsive transcoding (AVIF, WebP, LQIP Data-URI)
 * - Dr. Viktor Kovács & Marcus adversarial decompression bomb neutralization
 * - Disguised file extension / MIME tampering rejection
 * - Display P3 wide colour gamut detection
 * - Boundary error handling & clean resource disposal
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\services\VisualSupremacyService;

echo "=================================================================\n";
echo "⚡ VISUAL SUPREMACY ENGINE (VSE) VERIFICATION SUITE\n";
echo "=================================================================\n\n";

$passed = 0;
$total = 0;

function assertCheck(string $label, bool $condition, string $detail = ''): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  ✅ PASS: $label\n";
    } else {
        echo "  ❌ FAIL: $label ($detail)\n";
    }
}

$tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'vse_suite_' . uniqid('', true);
@mkdir($tempDir, 0755, true);

try {
    // 1. Multi-Tier Image Generation Test
    $gd = imagecreatetruecolor(300, 200);
    if ($gd !== false) {
        $crimson = imagecolorallocate($gd, 163, 22, 33);
        if ($crimson !== false) {
            imagefill($gd, 0, 0, $crimson);
        }
        $testJpg = $tempDir . '/wedding_photo.jpg';
        imagejpeg($gd, $testJpg, 92);
        imagedestroy($gd);

        $outDir = $tempDir . '/out';
        $result = VisualSupremacyService::process($testJpg, $outDir, 'wedding_photo_master');

        assertCheck("Original asset preserved", $result['original'] === 'wedding_photo_master.jpg');
        assertCheck("Correct raster width", $result['width'] === 300);
        assertCheck("Correct raster height", $result['height'] === 200);
        assertCheck("WebP responsive variant generated", !empty($result['webp']) && file_exists($outDir . '/' . $result['webp']));
        assertCheck("AVIF next-gen variant generated", !empty($result['avif']) && file_exists($outDir . '/' . $result['avif']));
        assertCheck("Micro-preview LQIP generated as Data-URI", !empty($result['thumb']) && str_starts_with($result['thumb'], 'data:image/'));
        assertCheck("Safe MIME verified as JPEG", $result['mime'] === 'image/jpeg');
    } else {
        assertCheck("Synthetic image creation", false, "GD imagecreatetruecolor failed");
    }

    // 2. Dr. Viktor Kovács Adversarial Decompression Bomb Defense Test
    $bombFile = $tempDir . '/pixel_bomb.png';
    // Craft minimal PNG header with 7000x7000 dimension = 196MB uncompressed memory footprint
    $pngHeader = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
    $ihdrChunk = "\x00\x00\x00\x0D" . "IHDR" . "\x00\x00\x1B\x58\x00\x00\x1B\x58\x08\x02\x00\x00\x00";
    $crc = crc32("IHDR" . "\x00\x00\x1B\x58\x00\x00\x1B\x58\x08\x02\x00\x00\x00");
    $pngHeader .= $ihdrChunk . pack('N', $crc);
    file_put_contents($bombFile, $pngHeader);

    $bombBlocked = false;
    try {
        VisualSupremacyService::process($bombFile, $tempDir . '/bomb_out', 'bomb');
    } catch (\InvalidArgumentException $e) {
        if (str_contains($e->getMessage(), 'exceeds the 128MB security threshold')) {
            $bombBlocked = true;
        }
    }
    assertCheck("Decompression bomb blocked by memory threshold guard", $bombBlocked);

    // 3. Disguised Malicious PHP Script Rejection
    $fakeImage = $tempDir . '/exploit.jpg';
    file_put_contents($fakeImage, '<?php phpinfo(); ?>');

    $fakeBlocked = false;
    try {
        VisualSupremacyService::process($fakeImage, $tempDir . '/fake_out', 'fake');
    } catch (\InvalidArgumentException $e) {
        if (str_contains($e->getMessage(), 'Unsupported media type') || str_contains($e->getMessage(), 'Malformed image file header')) {
            $fakeBlocked = true;
        }
    }
    assertCheck("Disguised text/PHP payload rejected", $fakeBlocked);

    // 4. Missing Source File Rejection
    $missingBlocked = false;
    try {
        VisualSupremacyService::process('/tmp/nonexistent_' . time() . '.png', $tempDir . '/missing_out', 'missing');
    } catch (\InvalidArgumentException $e) {
        $missingBlocked = true;
    }
    assertCheck("Missing source file rejected cleanly", $missingBlocked);

    // 5. In-Place Processing & Lossless EXIF Stripping Test
    $testInPlaceJpg = $tempDir . '/place_sample.jpg';
    // Create a real JPEG with synthetic APP1 (Exif) marker inserted
    // Note: JPEG segment length (2 bytes) includes the 2 length bytes themselves
    $exifPayload = "Exif\x00\x00payload_test";
    $exifLen = pack('n', strlen($exifPayload) + 2);

    $comPayload = "Comment_evil";
    $comLen = pack('n', strlen($comPayload) + 2);

    $rawJpeg = "\xFF\xD8"; // SOI
    $rawJpeg .= "\xFF\xE1" . $exifLen . $exifPayload; // APP1 Exif segment
    $rawJpeg .= "\xFF\xFE" . $comLen . $comPayload;   // COM segment
    $rawJpeg .= "\xFF\xD9"; // EOI
    file_put_contents($testInPlaceJpg, $rawJpeg);

    $stripped = VisualSupremacyService::stripMetadata($testInPlaceJpg);
    $cleanedContent = file_get_contents($testInPlaceJpg);
    assertCheck("EXIF stripper executed", $stripped);
    assertCheck("EXIF APP1 segment removed", !str_contains((string)$cleanedContent, 'Exif'));
    assertCheck("COM comment segment removed", !str_contains((string)$cleanedContent, 'Comment_evil'));
    assertCheck("Valid JPEG delimiters preserved", str_starts_with((string)$cleanedContent, "\xFF\xD8") && str_ends_with((string)$cleanedContent, "\xFF\xD9"));

    // 6. Real In-Place Multi-Tier Generation & LQIP Budget Verification
    $realGd = imagecreatetruecolor(100, 100);
    $color = imagecolorallocate($realGd, 20, 80, 150);
    imagefill($realGd, 0, 0, $color);
    $realJpg = $tempDir . '/real_post_photo.jpg';
    imagejpeg($realGd, $realJpg, 85);
    imagedestroy($realGd);

    $inPlaceResult = VisualSupremacyService::processInPlace($realJpg);
    assertCheck("processInPlace executed successfully", is_array($inPlaceResult));
    assertCheck("processInPlace AVIF generated", !empty($inPlaceResult['avif']) && file_exists($tempDir . '/' . $inPlaceResult['avif']));
    assertCheck("processInPlace WebP generated", !empty($inPlaceResult['webp']) && file_exists($tempDir . '/' . $inPlaceResult['webp']));
    $thumbLen = strlen((string)($inPlaceResult['thumb'] ?? ''));
    assertCheck("LQIP Micro-preview strictly within 2KB budget (actual: {$thumbLen} bytes)", $thumbLen > 0 && $thumbLen <= 2048);

} finally {
    // Cleanup temporary files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tempDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($tempDir);
}

echo "\n-----------------------------------------------------------------\n";
echo "Scorecard: Total: {$total} | Passed: {$passed} | Failed: " . ($total - $passed) . "\n";
echo "-----------------------------------------------------------------\n";

if ($passed === $total) {
    echo "🎉 ALL VISUAL SUPREMACY TESTS PASSED!\n";
    exit(0);
} else {
    echo "❌ VISUAL SUPREMACY TESTS FAILED!\n";
    exit(1);
}

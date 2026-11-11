<?php

declare(strict_types=1);

namespace App\services;

use Src\Exceptions\ValidationException;
use Throwable;

/**
 * MediaSafetyInspectionService — Zero-Cost Comprehensive Media Screening Engine
 *
 * Inspects uploaded images and video media across FamilyPlatform for pornography,
 * sexually explicit exposure, and previously blocked media fingerprints.
 */
final class MediaSafetyInspectionService
{
    public const MAX_ALLOWED_PIXELS = 100000000; // 100 megapixels (prevents decompression memory exhaustion)
    public const SKIN_EXPOSURE_THRESHOLD = 0.75; // > 75% untextured skin coverage triggers explicit flag

    /**
     * Inspect an uploaded file (from $_FILES) and assert it is safe to ingest.
     * Throws ValidationException if the media violates safety policies.
     *
     * @param array<string, mixed> $file Single file entry from $_FILES (with 'tmp_name', 'name', etc.)
     * @param string $context Context label (e.g. 'post_image', 'comment_sticker', 'video')
     * @throws ValidationException
     */
    public static function assertSafeUpload(array $file, string $context = 'media_upload'): void
    {
        $tmpPath = (string)($file['tmp_name'] ?? '');
        $origName = (string)($file['name'] ?? 'upload');

        if ($tmpPath === '' || !is_file($tmpPath) || !is_readable($tmpPath)) {
            return;
        }

        // 1. Magic-Byte MIME verification
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo !== false ? finfo_file($finfo, $tmpPath) : '';
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        $isImage = str_starts_with((string)$mime, 'image/');
        $isVideo = str_starts_with((string)$mime, 'video/');

        if (!$isImage && !$isVideo) {
            return; // Non-media uploads handled by their respective validators
        }

        // 2. Image-Specific Structural & Perceptual Check
        if ($isImage) {
            self::inspectImageFile($tmpPath, $origName);
        }

        // 3. Video-Specific Keyframe Check
        if ($isVideo) {
            self::inspectVideoFile($tmpPath, $origName);
        }
    }

    /**
     * Inspect multiple uploaded files (e.g. $_FILES['post_img']).
     *
     * @param array<string, mixed> $filesArray
     * @throws ValidationException
     */
    public static function assertSafeUploads(array $filesArray, string $context = 'feed_post'): void
    {
        if (empty($filesArray['name'])) {
            return;
        }

        if (is_array($filesArray['name'])) {
            $count = count($filesArray['name']);
            for ($i = 0; $i < $count; $i++) {
                if (!empty($filesArray['tmp_name'][$i]) && is_uploaded_file((string)$filesArray['tmp_name'][$i])) {
                    self::assertSafeUpload([
                        'name'     => $filesArray['name'][$i],
                        'type'     => $filesArray['type'][$i] ?? '',
                        'tmp_name' => $filesArray['tmp_name'][$i],
                        'error'    => $filesArray['error'][$i] ?? 0,
                        'size'     => $filesArray['size'][$i] ?? 0,
                    ], $context);
                }
            }
        } elseif (!empty($filesArray['tmp_name'])) {
            self::assertSafeUpload($filesArray, $context);
        }
    }

    /**
     * Inspect an image file on disk.
     *
     * @throws ValidationException
     */
    public static function inspectImageFile(string $filePath, string $filename = 'image'): void
    {
        // 1. Decompression bomb safeguard
        $info = @getimagesize($filePath);
        if ($info !== false) {
            $w = $info[0];
            $h = $info[1];
            if (($w * $h) > self::MAX_ALLOWED_PIXELS) {
                throw new ValidationException('Image resolution exceeds maximum allowable safety limits.');
            }
        }

        // 2. Perceptual Hash & Skin Exposure Check (Single-Pass GD Decode)
        $singlePass = self::inspectAndHashSinglePass($filePath);
        if ($singlePass['is_blocked']) {
            throw new ValidationException(
                'This image has been identified and blocked as violating FamilyPlatform community standards. FamilyPlatform is dedicated to safe, family-friendly memories.'
            );
        }

        $skinScore = $singlePass['skin_ratio'];
        if ($skinScore !== null && $skinScore >= self::SKIN_EXPOSURE_THRESHOLD) {
            throw new ValidationException(
                'This image appears to contain explicit content or high nudity exposure and cannot be posted. FamilyPlatform is dedicated to safe, family-friendly memories for all generations.'
            );
        }
    }

    /**
     * Single-pass GD image decode that computes both the 64-bit perceptual dHash
     * and the 64x64 YCbCr skin exposure ratio from a single imagecreatefromstring() call.
     *
     * @return array{is_blocked: bool, hash: ?string, skin_ratio: ?float}
     */
    private static function inspectAndHashSinglePass(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return ['is_blocked' => false, 'hash' => null, 'skin_ratio' => null];
        }

        try {
            $raw = file_get_contents($filePath);
            if ($raw === false || strlen($raw) < 16) {
                return ['is_blocked' => false, 'hash' => null, 'skin_ratio' => null];
            }

            $srcImg = @imagecreatefromstring($raw);
            unset($raw);
            if ($srcImg === false) {
                return ['is_blocked' => false, 'hash' => null, 'skin_ratio' => null];
            }

            $srcW = imagesx($srcImg);
            $srcH = imagesy($srcImg);

            // Downsample once to 64x64 truecolor grid
            $sampleW = 64;
            $sampleH = 64;
            $sample = imagecreatetruecolor($sampleW, $sampleH);
            if ($sample === false) {
                imagedestroy($srcImg);
                return ['is_blocked' => false, 'hash' => null, 'skin_ratio' => null];
            }

            imagecopyresampled($sample, $srcImg, 0, 0, 0, 0, $sampleW, $sampleH, $srcW, $srcH);
            imagedestroy($srcImg);

            // A. Compute 9x8 dHash from the 64x64 sample (sub-millisecond)
            $candidateHash = null;
            $thumb = imagecreatetruecolor(9, 8);
            if ($thumb !== false) {
                imagecopyresampled($thumb, $sample, 0, 0, 0, 0, 9, 8, $sampleW, $sampleH);
                imagefilter($thumb, IMG_FILTER_GRAYSCALE);

                $bits = '';
                for ($y = 0; $y < 8; $y++) {
                    for ($x = 0; $x < 8; $x++) {
                        $brightLeft = (imagecolorat($thumb, $x, $y) >> 16) & 0xFF;
                        $brightRight = (imagecolorat($thumb, $x + 1, $y) >> 16) & 0xFF;
                        $bits .= ($brightLeft > $brightRight) ? '1' : '0';
                    }
                }
                imagedestroy($thumb);

                $hex = '';
                for ($i = 0; $i < 64; $i += 4) {
                    $hex .= dechex((int)bindec(substr($bits, $i, 4)));
                }
                $candidateHash = strtolower($hex);
            }

            // B. Compute YCbCr skin ratio on the 64x64 sample
            $skinPixels = 0;
            $totalPixels = $sampleW * $sampleH;
            for ($y = 0; $y < $sampleH; $y++) {
                for ($x = 0; $x < $sampleW; $x++) {
                    $rgb = imagecolorat($sample, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    $cb = (int)round(128.0 - (0.168736 * $r) - (0.331264 * $g) + (0.500000 * $b));
                    $cr = (int)round(128.0 + (0.500000 * $r) - (0.418688 * $g) - (0.081312 * $b));

                    if ($cb >= 77 && $cb <= 127 && $cr >= 133 && $cr <= 173) {
                        if ($r > $g && $r > $b && ($r - $g) >= 12) {
                            $skinPixels++;
                        }
                    }
                }
            }
            imagedestroy($sample);

            $skinRatio = round($skinPixels / $totalPixels, 4);
            $isBlocked = false;
            if ($candidateHash !== null) {
                $check = PerceptualHashService::inspectHash($candidateHash);
                $isBlocked = $check['is_blocked'];
            }

            return [
                'is_blocked' => $isBlocked,
                'hash' => $candidateHash,
                'skin_ratio' => $skinRatio,
            ];
        } catch (Throwable $e) {
            error_log('[MediaSafetyInspectionService::inspectAndHashSinglePass] ' . $e->getMessage());
            return ['is_blocked' => false, 'hash' => null, 'skin_ratio' => null];
        }
    }

    /**
     * Inspect a video file on disk using temporal keyframe decimation.
     *
     * @throws ValidationException
     */
    public static function inspectVideoFile(string $filePath, string $filename = 'video'): void
    {
        $ffmpeg = self::findFfmpeg();
        if ($ffmpeg === null) {
            return; // FFmpeg not on host; rely on client-side frame pre-screening
        }

        // Extract up to 4 keyframes at scene changes or intervals
        $tempDir = sys_get_temp_dir() . '/fp_video_screen_' . bin2hex(random_bytes(6));
        if (!mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
            return;
        }

        try {
            $outputPattern = $tempDir . '/frame_%02d.jpg';
            // Sample 1 frame every 3 seconds, up to max 6 frames
            $cmd = [
                $ffmpeg,
                '-y',
                '-i', $filePath,
                '-vf', "fps=1/3",
                '-vframes', '6',
                '-q:v', '5',
                $outputPattern,
            ];

            $pipes = [];
            $proc = @proc_open($cmd, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            if (is_resource($proc)) {
                fclose($pipes[0]);
                stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                proc_close($proc);
            }

            // Inspect each extracted keyframe
            $frames = glob($tempDir . '/*.jpg') ?: [];
            foreach ($frames as $framePath) {
                self::inspectImageFile($framePath, basename($filePath));
            }
        } catch (ValidationException $e) {
            self::cleanupTempDir($tempDir);
            throw $e;
        } catch (Throwable $e) {
            error_log('[MediaSafetyInspectionService::inspectVideoFile] ' . $e->getMessage());
        } finally {
            self::cleanupTempDir($tempDir);
        }
    }

    /**
     * Compute the proportion of skin-tone pixels in an image using YCbCr chrominance segmentation.
     * Returns a float from 0.0 (no skin) to 1.0 (all skin), or null on non-image/error.
     */
    public static function calculateSkinExposureRatio(string $filePath): ?float
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }

        try {
            $raw = file_get_contents($filePath);
            if ($raw === false || strlen($raw) < 16) {
                return null;
            }

            $img = @imagecreatefromstring($raw);
            if ($img === false) {
                return null;
            }

            $srcW = imagesx($img);
            $srcH = imagesy($img);

            // Downsample to 64x64 grid for instantaneous sub-millisecond evaluation
            $sampleW = 64;
            $sampleH = 64;
            $sample = imagecreatetruecolor($sampleW, $sampleH);
            if ($sample === false) {
                imagedestroy($img);
                return null;
            }

            imagecopyresampled($sample, $img, 0, 0, 0, 0, $sampleW, $sampleH, $srcW, $srcH);
            imagedestroy($img);

            $skinPixels = 0;
            $totalPixels = $sampleW * $sampleH;

            for ($y = 0; $y < $sampleH; $y++) {
                for ($x = 0; $x < $sampleW; $x++) {
                    $rgb = imagecolorat($sample, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Convert RGB to YCbCr chrominance space (ITU-R BT.601)
                    $cb = (int)round(128.0 - (0.168736 * $r) - (0.331264 * $g) + (0.500000 * $b));
                    $cr = (int)round(128.0 + (0.500000 * $r) - (0.418688 * $g) - (0.081312 * $b));

                    // Standard human skin color distribution boundary
                    // Cb in [77, 127] and Cr in [133, 173]
                    if ($cb >= 77 && $cb <= 127 && $cr >= 133 && $cr <= 173) {
                        // Additional sanity check: R > G and R > B to avoid saturated uniform yellows/blues
                        if ($r > $g && $r > $b && ($r - $g) >= 12) {
                            $skinPixels++;
                        }
                    }
                }
            }

            imagedestroy($sample);

            return round($skinPixels / $totalPixels, 4);
        } catch (Throwable $e) {
            error_log('[MediaSafetyInspectionService::calculateSkinExposureRatio] ' . $e->getMessage());
            return null;
        }
    }

    private static function findFfmpeg(): ?string
    {
        $paths = ['/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', '/opt/homebrew/bin/ffmpeg'];
        foreach ($paths as $p) {
            if (is_file($p) && is_executable($p)) {
                return $p;
            }
        }
        return null;
    }

    private static function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . '/*');
        if (is_array($files)) {
            foreach ($files as $f) {
                if (is_file($f)) {
                    @unlink($f);
                }
            }
        }
        @rmdir($dir);
    }
}

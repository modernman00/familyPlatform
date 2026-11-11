<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Throwable;

/**
 * PerceptualHashService — Zero-Dependency 64-bit Perceptual Difference Hashing (dHash)
 *
 * Computes robust visual fingerprints for images and video keyframes using pure native
 * PHP 8.2+ and ext-gd. Detects visually identical or cropped/re-encoded pornographic
 * and abusive media using Hamming distance matching.
 */
final class PerceptualHashService
{
    public const DEFAULT_HAMMING_THRESHOLD = 6; // Out of 64 bits (<= 6 bits diff = > 90% visual identity)
    
    /** @var array<int, array{id: int, hash_hex: string, category: string, threshold: int}>|null In-request memory cache */
    private static ?array $blockedHashesCache = null;

    /**
     * Compute a 64-bit difference hash (dHash) for an image file.
     * Returns a 16-character lowercase hexadecimal string (e.g. '8f4c2e1a9b3d7f0e'), or null on error.
     */
    public static function computeDHash(string $filePath): ?string
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return null;
        }

        try {
            $imageContent = file_get_contents($filePath);
            if ($imageContent === false || strlen($imageContent) < 16) {
                return null;
            }

            // Create GD image resource from string (supports JPEG, PNG, WebP, GIF)
            $srcImg = @imagecreatefromstring($imageContent);
            if ($srcImg === false) {
                return null;
            }

            $srcW = imagesx($srcImg);
            $srcH = imagesy($srcImg);

            // Resize to 9 columns x 8 rows in grayscale (dHash standard)
            $thumb = imagecreatetruecolor(9, 8);
            if ($thumb === false) {
                imagedestroy($srcImg);
                return null;
            }

            imagecopyresampled($thumb, $srcImg, 0, 0, 0, 0, 9, 8, $srcW, $srcH);
            imagefilter($thumb, IMG_FILTER_GRAYSCALE);

            // Compute adjacent pixel brightness differences (8 rows x 8 comparisons = 64 bits)
            $bits = '';
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $rgbLeft = imagecolorat($thumb, $x, $y);
                    $rgbRight = imagecolorat($thumb, $x + 1, $y);

                    // Since it's grayscale, red component equals brightness
                    $brightLeft = ($rgbLeft >> 16) & 0xFF;
                    $brightRight = ($rgbRight >> 16) & 0xFF;

                    $bits .= ($brightLeft > $brightRight) ? '1' : '0';
                }
            }

            imagedestroy($thumb);
            imagedestroy($srcImg);

            // Convert 64 bits to 16 hex characters
            $hex = '';
            for ($i = 0; $i < 64; $i += 4) {
                $nibble = substr($bits, $i, 4);
                $hex .= dechex((int)bindec($nibble));
            }

            return strtolower($hex);
        } catch (Throwable $e) {
            error_log('[PerceptualHashService::computeDHash] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate Hamming distance (number of differing bits) between two 16-hex perceptual hashes.
     * Returns an integer from 0 (identical) to 64 (completely inverted).
     */
    public static function hammingDistance(string $hex1, string $hex2): int
    {
        $h1 = strtolower(trim($hex1));
        $h2 = strtolower(trim($hex2));

        if (strlen($h1) !== 16 || strlen($h2) !== 16) {
            return 64;
        }

        // Split 64 bits into two 32-bit integer operations for platform architecture safety
        $val1A = (int)hexdec(substr($h1, 0, 8));
        $val1B = (int)hexdec(substr($h1, 8, 8));
        $val2A = (int)hexdec(substr($h2, 0, 8));
        $val2B = (int)hexdec(substr($h2, 8, 8));

        $xorA = $val1A ^ $val2A;
        $xorB = $val1B ^ $val2B;

        // Bit population count
        $count = 0;
        for ($i = 0; $i < 32; $i++) {
            $count += (($xorA >> $i) & 1) + (($xorB >> $i) & 1);
        }

        return $count;
    }

    /**
     * Check if a given media file matches any blocked visual hash in the database.
     *
     * @return array{
     *   is_blocked: bool,
     *   hash: ?string,
     *   matched_hash: ?string,
     *   distance: ?int,
     *   category: ?string,
     *   reason: ?string
     * }
     */
    public static function inspectMediaFile(string $filePath): array
    {
        $candidateHash = self::computeDHash($filePath);
        if ($candidateHash === null) {
            return [
                'is_blocked' => false,
                'hash' => null,
                'matched_hash' => null,
                'distance' => null,
                'category' => null,
                'reason' => null,
            ];
        }

        return self::inspectHash($candidateHash);
    }

    /**
     * Check if a pre-computed 16-hex perceptual hash matches any blocked visual hash in the database.
     *
     * @return array{
     *   is_blocked: bool,
     *   hash: string,
     *   matched_hash: ?string,
     *   distance: ?int,
     *   category: ?string,
     *   reason: ?string
     * }
     */
    public static function inspectHash(string $candidateHash): array
    {
        $blockedList = self::loadBlockedHashes();
        foreach ($blockedList as $entry) {
            $dist = self::hammingDistance($candidateHash, $entry['hash_hex']);
            $threshold = $entry['threshold'] > 0 ? $entry['threshold'] : self::DEFAULT_HAMMING_THRESHOLD;

            if ($dist <= $threshold) {
                return [
                    'is_blocked' => true,
                    'hash' => $candidateHash,
                    'matched_hash' => $entry['hash_hex'],
                    'distance' => $dist,
                    'category' => $entry['category'],
                    'reason' => 'Matches previously confirmed prohibited/explicit media fingerprint.',
                ];
            }
        }

        return [
            'is_blocked' => false,
            'hash' => $candidateHash,
            'matched_hash' => null,
            'distance' => null,
            'category' => null,
            'reason' => null,
        ];
    }

    /**
     * Block a media file permanently in the database by registering its visual hash.
     */
    public static function blockMediaFile(
        string $filePath,
        string $category = 'pornography',
        ?string $adminId = null,
        ?string $sourceFilename = null,
        string $mediaType = 'image'
    ): ?string {
        $hash = self::computeDHash($filePath);
        if ($hash === null) {
            return null;
        }

        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare(
                'INSERT INTO media_safety_hashes (
                    hash_hex, media_type, status, category, hamming_threshold,
                    source_filename, confirmed_by, created_at, updated_at
                 ) VALUES (?, ?, "blocked", ?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                    status = "blocked",
                    category = VALUES(category),
                    confirmed_by = VALUES(confirmed_by),
                    updated_at = NOW()'
            );

            $stmt->execute([
                $hash,
                $mediaType,
                $category,
                self::DEFAULT_HAMMING_THRESHOLD,
                $sourceFilename,
                $adminId,
            ]);

            // Invalidate cache
            self::$blockedHashesCache = null;

            return $hash;
        } catch (Throwable $e) {
            error_log('[PerceptualHashService::blockMediaFile] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Load active blocked hashes from the database.
     *
     * @return array<int, array{id: int, hash_hex: string, category: string, threshold: int}>
     */
    private static function loadBlockedHashes(): array
    {
        if (self::$blockedHashesCache !== null) {
            return self::$blockedHashesCache;
        }

        try {
            $pdo = Db::connect2();
            $stmt = $pdo->query('SELECT id, hash_hex, category, hamming_threshold FROM media_safety_hashes WHERE status = "blocked"');
            $rows = ($stmt instanceof \PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false) ?: [];

            $result = [];
            foreach ($rows as $r) {
                $result[] = [
                    'id' => (int)$r['id'],
                    'hash_hex' => (string)$r['hash_hex'],
                    'category' => (string)($r['category'] ?? 'pornography'),
                    'threshold' => (int)($r['hamming_threshold'] ?? self::DEFAULT_HAMMING_THRESHOLD),
                ];
            }

            self::$blockedHashesCache = $result;
            return $result;
        } catch (Throwable $e) {
            error_log('[PerceptualHashService::loadBlockedHashes] ' . $e->getMessage());
            return [];
        }
    }
}

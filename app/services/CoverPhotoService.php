<?php
declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Src\Exceptions\ValidationException;
use Src\functionality\middleware\FileUploadProcess;

/**
 * CoverPhotoService
 *
 * Member-chosen cover photo for the profile header band.
 *
 * Storage reuses the existing `images` table (where_from = 'cover') and the
 * shared-lib upload pipeline (FileUploadProcess → FileUploader: MIME sniffing,
 * virus scan, resize to 1920px, re-encode). No schema change: the newest
 * non-deleted 'cover' row is the active cover, and older covers stay in the
 * member's photo gallery like any other upload.
 */
final class CoverPhotoService
{
    public const WHERE_FROM = 'cover';
    public const FORM_INPUT = 'coverImageFile';
    public const UPLOAD_DIR = 'resources/images/profile/';
    public const PUBLIC_URL_PREFIX = '/resources/images/profile/';

    /** Covers are photos only — FileUploader alone would also accept pdf/doc/video. */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'heic'];

    private const MAX_BYTES = 10 * 1024 * 1024;

    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    /**
     * Public URL of the member's active cover, or null when none is set
     * (or the file is missing on disk), so the template falls back to the brand gradient.
     */
    public function getCurrentUrl(string $userId): ?string
    {
        if ($userId === '') {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare(
                "SELECT img FROM images
                 WHERE id = ? AND where_from = ? AND deleted_at IS NULL AND img IS NOT NULL AND img != ''
                 ORDER BY no DESC LIMIT 1"
            );
            $stmt->execute([$userId, self::WHERE_FROM]);
            $img = $stmt->fetchColumn();
        } catch (\Throwable $e) {
            // A cover is decorative — never let it take the profile page down.
            error_log('[CoverPhoto] lookup failed: ' . $e->getMessage());
            return null;
        }

        if (!is_string($img) || $img === '') {
            return null;
        }

        $safeName = basename($img);
        if (!is_file(self::UPLOAD_DIR . $safeName)) {
            return null;
        }

        return self::PUBLIC_URL_PREFIX . rawurlencode($safeName);
    }

    /**
     * Active cover with its focal point (CSS background-position %, 50/50 = centred).
     * Degrades to a centred cover if the cover_pos_* columns are not migrated yet.
     *
     * @return array{url: string, x: int, y: int}|null
     */
    public function getCurrent(string $userId): ?array
    {
        $url = $this->getCurrentUrl($userId);
        if ($url === null) {
            return null;
        }

        $x = 50;
        $y = 50;
        try {
            $stmt = $this->pdo->prepare(
                "SELECT cover_pos_x, cover_pos_y FROM images
                 WHERE id = ? AND where_from = ? AND deleted_at IS NULL AND img IS NOT NULL AND img != ''
                 ORDER BY no DESC LIMIT 1"
            );
            $stmt->execute([$userId, self::WHERE_FROM]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($row)) {
                $x = self::clampPercent($row['cover_pos_x'] ?? 50);
                $y = self::clampPercent($row['cover_pos_y'] ?? 50);
            }
        } catch (\Throwable $e) {
            error_log('[CoverPhoto] position lookup failed (migration 2026_11_06 pending?): ' . $e->getMessage());
        }

        return ['url' => $url, 'x' => $x, 'y' => $y];
    }

    /**
     * Save the focal point of the member's active cover.
     *
     * @throws ValidationException When the member has no active cover
     */
    public function setPosition(string $userId, mixed $x, mixed $y): void
    {
        if (!is_numeric($x) || !is_numeric($y)) {
            throw new ValidationException('Invalid cover position.');
        }

        $stmt = $this->pdo->prepare(
            "SELECT no FROM images
             WHERE id = ? AND where_from = ? AND deleted_at IS NULL AND img IS NOT NULL AND img != ''
             ORDER BY no DESC LIMIT 1"
        );
        $stmt->execute([$userId, self::WHERE_FROM]);
        $no = $stmt->fetchColumn();
        if ($no === false) {
            throw new ValidationException('Add a cover photo before adjusting it.');
        }

        // Scoped by both primary key and owner id — a member can only move their own cover.
        $upd = $this->pdo->prepare('UPDATE images SET cover_pos_x = ?, cover_pos_y = ? WHERE no = ? AND id = ?');
        $upd->execute([self::clampPercent($x), self::clampPercent($y), (int) $no, $userId]);
    }

    private static function clampPercent(mixed $value): int
    {
        $n = is_numeric($value) ? (int) round((float) $value) : 50;
        return max(0, min(100, $n));
    }

    /**
     * Validate and store an uploaded cover from $_FILES[FORM_INPUT].
     *
     * @param string $userId Optional member ID override (for admin management)
     * @return string Public URL of the new cover
     * @throws ValidationException
     */
    public function upload(string $userId = ''): string
    {
        $file = $_FILES[self::FORM_INPUT] ?? null;
        if (!is_array($file) || is_array($file['name'] ?? null) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new ValidationException('Please choose one photo for your cover.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new ValidationException('Cover photos must be JPG, PNG, WebP or HEIC.');
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new ValidationException('Cover photos must be 10 MB or smaller.');
        }

        // Shared-lib pipeline: validates + sniffs MIME, scans, optimises, moves,
        // and records an `images` row (id = session user, where_from = 'cover').
        $result = FileUploadProcess::process([], self::WHERE_FROM, self::FORM_INPUT, self::UPLOAD_DIR, 'images');
        $fileName = $result['sanitisedData'][self::WHERE_FROM][self::FORM_INPUT] ?? '';

        if (!is_string($fileName) || $fileName === '') {
            throw new ValidationException('Your cover photo could not be saved. Please try again.');
        }

        if ($userId !== '' && !empty($_SESSION['id']) && $userId !== $_SESSION['id']) {
            $baseName = basename($fileName);
            $upd = $this->pdo->prepare("UPDATE images SET id = ? WHERE img = ? AND where_from = ? AND id = ? ORDER BY no DESC LIMIT 1");
            $upd->execute([$userId, $baseName, self::WHERE_FROM, (string)$_SESSION['id']]);
        }

        return self::PUBLIC_URL_PREFIX . rawurlencode(basename($fileName));
    }

    /**
     * Revert to the default gradient. Soft-deletes the member's cover rows
     * (files are kept so gallery/erasure flows stay authoritative).
     */
    public function remove(string $userId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE images SET deleted_at = NOW() WHERE id = ? AND where_from = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$userId, self::WHERE_FROM]);
    }
}

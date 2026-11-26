<?php

declare(strict_types=1);

namespace App\services;

use PDO;
use Src\Db;
use Throwable;

/**
 * Service for managing user app handles (@handle) and privacy-safe external share display names.
 */
final class UserHandleService
{
    /** @var array<int, bool> */
    private static array $ensuredConnections = [];

    /**
     * Ensure `contact.app_handle` and `contact.external_share_name` exist idempotently.
     */
    public static function ensureSchema(?PDO $pdo = null): void
    {
        try {
            $db = $pdo ?? Db::connect2();
            $connId = spl_object_id($db);
            if (isset(self::$ensuredConnections[$connId])) {
                return;
            }
            self::$ensuredConnections[$connId] = true;

            $chkHandle = $db->query("SHOW COLUMNS FROM `contact` LIKE 'app_handle'");
            if ($chkHandle && !$chkHandle->fetch()) {
                $db->exec(
                    "ALTER TABLE `contact` ADD COLUMN `app_handle` VARCHAR(50) NULL DEFAULT NULL AFTER `occupation`"
                );
            }

            $chkPref = $db->query("SHOW COLUMNS FROM `contact` LIKE 'external_share_name'");
            if ($chkPref && !$chkPref->fetch()) {
                $db->exec(
                    "ALTER TABLE `contact` ADD COLUMN `external_share_name` VARCHAR(30) NOT NULL DEFAULT 'first_initial' AFTER `app_handle`"
                );
            }

            $chkIdx = $db->query("SHOW INDEX FROM `contact` WHERE Key_name = 'idx_contact_app_handle'");
            if ($chkIdx && !$chkIdx->fetch()) {
                $db->exec(
                    "ALTER TABLE `contact` ADD UNIQUE INDEX `idx_contact_app_handle` (`app_handle`)"
                );
            }
        } catch (Throwable $e) {
            error_log('[UserHandleService::ensureSchema] ' . $e->getMessage());
        }
    }

    /**
     * Normalises a raw handle string:
     * - Strips leading '@' and whitespace
     * - Validates format: 2-30 characters, alphanumeric and underscore only
     * - Converts to lowercase for consistent matching
     *
     * Returns null if empty or invalid.
     */
    public static function normaliseHandle(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $clean = strtolower(trim(ltrim(trim($raw), '@')));
        if ($clean === '') {
            return null;
        }
        if (!preg_match('/^[a-z0-9_]{2,30}$/', $clean)) {
            return null;
        }
        return $clean;
    }

    /**
     * Validate whether a handle format is valid.
     */
    public static function isValidHandleFormat(string $raw): bool
    {
        $clean = strtolower(trim(ltrim(trim($raw), '@')));
        return (bool)preg_match('/^[a-z0-9_]{2,30}$/', $clean);
    }

    /**
     * Checks if a handle is available (not taken by another user).
     */
    public static function isHandleAvailable(PDO $db, string $handle, string $excludeUserId = ''): bool
    {
        $clean = self::normaliseHandle($handle);
        if ($clean === null) {
            return false;
        }

        self::ensureSchema($db);

        if ($excludeUserId !== '') {
            $stmt = $db->prepare('SELECT id FROM contact WHERE LOWER(app_handle) = ? AND id != ? LIMIT 1');
            $stmt->execute([$clean, $excludeUserId]);
        } else {
            $stmt = $db->prepare('SELECT id FROM contact WHERE LOWER(app_handle) = ? LIMIT 1');
            $stmt->execute([$clean]);
        }

        return $stmt->fetch() === false;
    }

    /**
     * Normalise external share display preference.
     * Allowed: 'first_initial', 'handle', 'first_only', 'full_name', 'anonymous'.
     */
    public static function normalisePreference(?string $pref): string
    {
        $allowed = ['first_initial', 'handle', 'first_only', 'full_name', 'anonymous'];
        $val = strtolower(trim((string)$pref));
        return in_array($val, $allowed, true) ? $val : 'first_initial';
    }

    /**
     * Resolves the author's privacy-safe external display name for public cards and WhatsApp previews.
     */
    public static function resolveExternalAuthorName(PDO $db, string $authorId, string $fallbackFullName = ''): string
    {
        self::ensureSchema($db);

        $firstName = '';
        $lastName = '';
        $appHandle = null;
        $preference = 'first_initial';

        if ($authorId !== '') {
            try {
                $stmt = $db->prepare('
                    SELECT p.firstName, p.lastName, c.app_handle, c.external_share_name 
                    FROM personal p 
                    LEFT JOIN contact c ON c.id = p.id 
                    WHERE p.id = ? 
                    LIMIT 1
                ');
                $stmt->execute([$authorId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (is_array($row)) {
                    $firstName = trim((string)($row['firstName'] ?? ''));
                    $lastName = trim((string)($row['lastName'] ?? ''));
                    $appHandle = self::normaliseHandle((string)($row['app_handle'] ?? ''));
                    $preference = self::normalisePreference((string)($row['external_share_name'] ?? 'first_initial'));
                }
            } catch (Throwable $e) {
                error_log('[UserHandleService::resolveExternalAuthorName] ' . $e->getMessage());
            }
        }

        // If names were not found in personal table, parse from fallback
        if ($firstName === '' && $fallbackFullName !== '') {
            $parts = explode(' ', trim($fallbackFullName), 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
        }

        return self::formatExternalName($firstName, $lastName, $appHandle, $preference);
    }

    /**
     * Formats the external display name according to the member's chosen preference.
     */
    public static function formatExternalName(
        string $firstName,
        string $lastName,
        ?string $appHandle = null,
        string $preference = 'first_initial'
    ): string {
        $pref = self::normalisePreference($preference);

        if ($pref === 'anonymous') {
            return 'A family member';
        }

        if ($pref === 'handle' && $appHandle !== null && $appHandle !== '') {
            return '@' . $appHandle;
        }

        $f = trim($firstName);
        $l = trim($lastName);

        // Title case for readability
        $cleanFirst = $f !== '' ? mb_convert_case(mb_strtolower($f), MB_CASE_TITLE) : '';
        $cleanLast = $l !== '' ? mb_convert_case(mb_strtolower($l), MB_CASE_TITLE) : '';

        if ($pref === 'first_only' && $cleanFirst !== '') {
            return $cleanFirst;
        }

        if ($pref === 'full_name' && ($cleanFirst !== '' || $cleanLast !== '')) {
            return trim($cleanFirst . ' ' . $cleanLast);
        }

        // Default: 'first_initial' (e.g. "Wally O.")
        if ($cleanFirst !== '' && $cleanLast !== '') {
            $initial = mb_substr($cleanLast, 0, 1);
            return $cleanFirst . ' ' . mb_strtoupper($initial) . '.';
        }

        if ($cleanFirst !== '') {
            return $cleanFirst;
        }

        if ($appHandle !== null && $appHandle !== '') {
            return '@' . $appHandle;
        }

        return 'A family member';
    }

    /**
     * Format a full name string directly using first-initial privacy logic.
     */
    public static function formatInitialFallback(string $fullName): string
    {
        $clean = trim($fullName);
        if ($clean === '' || strcasecmp($clean, 'family member') === 0 || strcasecmp($clean, 'a relative') === 0) {
            return 'A family member';
        }

        $parts = preg_split('/\s+/u', $clean);
        if ($parts === false) {
            return $clean;
        }

        if (count($parts) === 1) {
            return mb_convert_case(mb_strtolower($parts[0]), MB_CASE_TITLE);
        }

        $first = mb_convert_case(mb_strtolower($parts[0]), MB_CASE_TITLE);
        $last = $parts[count($parts) - 1];
        $initial = mb_substr($last, 0, 1);

        return $first . ' ' . mb_strtoupper($initial) . '.';
    }
}

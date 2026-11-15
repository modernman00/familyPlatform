<?php
declare(strict_types=1);

namespace App\services;

use PDO;
use PDOException;
use Src\Db;

/**
 * ProfileTourService
 *
 * Persists per-user permanent dismissal preferences for interactive spotlight tours
 * (such as the Profile Page tour), with session caching and safe schema auto-provisioning.
 */
class ProfileTourService
{
    public const PROFILE_TOUR_KEY = 'profile_page_v1';

    /** @var array<int, string> */
    private const ALLOWED_TOUR_KEYS = [
        self::PROFILE_TOUR_KEY,
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Db::connect2();
    }

    public function isAllowedTourKey(string $tourKey): bool
    {
        return in_array($tourKey, self::ALLOWED_TOUR_KEYS, true);
    }

    public function isDismissed(string $userId, string $tourKey = self::PROFILE_TOUR_KEY): bool
    {
        $userId = trim($userId);
        $tourKey = trim($tourKey);

        if ($userId === '' || !$this->isAllowedTourKey($tourKey)) {
            return false;
        }

        $sessionKey = 'profile_tour_dismissed_' . $tourKey;
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION[$sessionKey])) {
            return (bool)$_SESSION[$sessionKey];
        }

        try {
            $this->ensureTable();
            $stmt = $this->db->prepare(
                'SELECT is_dismissed FROM user_tour_preferences WHERE user_id = ? AND tour_key = ? LIMIT 1'
            );
            $stmt->execute([$userId, $tourKey]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $dismissed = is_array($row) && ((int)($row['is_dismissed'] ?? 0) === 1);

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION[$sessionKey] = $dismissed;
            }

            return $dismissed;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * @return array{status: string, tour_key: string, dismissed: bool, message?: string}
     */
    public function setDismissed(string $userId, bool $dismissed, string $tourKey = self::PROFILE_TOUR_KEY): array
    {
        $userId = trim($userId);
        $tourKey = trim($tourKey);

        if ($userId === '') {
            return [
                'status' => 'error',
                'tour_key' => $tourKey,
                'dismissed' => false,
                'message' => 'User ID is required.',
            ];
        }

        if (!$this->isAllowedTourKey($tourKey)) {
            return [
                'status' => 'error',
                'tour_key' => $tourKey,
                'dismissed' => false,
                'message' => 'Invalid tour key.',
            ];
        }

        $sessionKey = 'profile_tour_dismissed_' . $tourKey;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$sessionKey] = $dismissed;
        }

        try {
            $this->ensureTable();
            $dismissedInt = $dismissed ? 1 : 0;
            $dismissedAt = $dismissed ? date('Y-m-d H:i:s') : null;

            $stmt = $this->db->prepare(
                'INSERT INTO user_tour_preferences (user_id, tour_key, is_dismissed, dismissed_at)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    is_dismissed = VALUES(is_dismissed),
                    dismissed_at = VALUES(dismissed_at)'
            );
            $stmt->execute([$userId, $tourKey, $dismissedInt, $dismissedAt]);

            return [
                'status' => 'success',
                'tour_key' => $tourKey,
                'dismissed' => $dismissed,
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'tour_key' => $tourKey,
                'dismissed' => $dismissed,
                'message' => 'Unable to save tour preference.',
            ];
        }
    }

    private function ensureTable(): void
    {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS user_tour_preferences (
                user_id VARCHAR(191) NOT NULL,
                tour_key VARCHAR(100) NOT NULL,
                is_dismissed TINYINT(1) NOT NULL DEFAULT 0,
                dismissed_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, tour_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}

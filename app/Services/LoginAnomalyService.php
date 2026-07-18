<?php

declare(strict_types=1);

namespace App\Services;

use Src\Db;

/**
 * LoginAnomalyService — Detects and alerts on suspicious login activity.
 *
 * On every successful login, this service:
 *  1. Checks if the current IP + User-Agent fingerprint is new for this user.
 *  2. If new, flags it as an anomaly (returns true so the caller can send an alert email).
 *  3. Records the current fingerprint to the login_events table regardless.
 *
 * Design decisions:
 *  - NO third-party IP geolocation calls are made (GDPR compliance).
 *  - Fingerprint = SHA-256 hash of IP + normalised User-Agent string.
 *  - Only stores last 20 events per user to keep the table lean.
 */
class LoginAnomalyService
{
    /**
     * Check if the current request fingerprint is new for this user.
     * Returns an array with anomaly details if new, null if familiar.
     *
     * @param int $userId
     * @return array|null ['ip' => string, 'user_agent' => string, 'is_new' => true]
     */
    public static function check(int|string $userId): ?array
    {
        try {
            $ip        = self::getClientIp();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
            $fingerprint = self::fingerprint($ip, $userAgent);

            $db = Db::connect2();
            if ($db === null) {
                throw new \RuntimeException('Database connection unavailable during anomaly check.');
            }
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM login_events WHERE user_id = ? AND fingerprint = ?"
            );
            $stmt->execute([$userId, $fingerprint]);

            $count = (int) $stmt->fetchColumn();

            if ($count === 0) {
                return [
                    'ip'         => $ip,
                    'user_agent' => $userAgent,
                    'is_new'     => true,
                ];
            }

            return null; // Familiar fingerprint — no anomaly
        } catch (\Throwable $e) {
            // Never crash the login flow — just log and return null
            error_log('[LoginAnomalyService] check() failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Record the current login event fingerprint for this user.
     * Automatically prunes old records, keeping only the last 50.
     *
     * @param int $userId
     */
    public static function record(int $userId): void
    {
        try {
            $ip          = self::getClientIp();
            $userAgent   = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);
            $fingerprint = self::fingerprint($ip, $userAgent);

            $db = Db::connect2();
            if ($db === null) {
                throw new \RuntimeException('Database connection unavailable during anomaly record.');
            }

            // Insert the new event
            $stmt = $db->prepare(
                "INSERT INTO login_events (user_id, ip_address, user_agent, fingerprint)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $ip, $userAgent, $fingerprint]);

            // Prune: keep only the 50 most recent events per user
            $db->prepare(
                "DELETE FROM login_events WHERE user_id = ? AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM login_events WHERE user_id = ?
                        ORDER BY created_at DESC LIMIT 50
                    ) AS recent
                 )"
            )->execute([$userId, $userId]);
        } catch (\Throwable $e) {
            error_log('[LoginAnomalyService] record() failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a deterministic fingerprint from IP + user-agent.
     */
    private static function fingerprint(string $ip, string $userAgent): string
    {
        return hash('sha256', $ip . '|' . $userAgent);
    }

    /**
     * Get the real client IP, respecting trusted reverse proxies.
     */
    private static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',   // Cloudflare
            'HTTP_X_FORWARDED_FOR',    // Load balancer / proxy
            'HTTP_X_REAL_IP',          // Nginx proxy
            'REMOTE_ADDR',             // Direct connection (fallback)
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                // X-Forwarded-For can be a comma-separated list — take the first
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}

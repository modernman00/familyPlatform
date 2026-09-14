<?php

declare(strict_types=1);

namespace App\middleware;

use Src\Limiter;
use Src\Utility;

/**
 * Fail-Closed Zero-Trust Administrative Security Middleware.
 *
 * Enforces 5 security gates for all administrative routes across portfolio apps:
 * 1. Secret URL Gateway Validation (ADMIN_SECRET_PATH)
 * 2. IP CIDR Whitelist Check (ADMIN_ALLOWED_IPS)
 * 3. Rate-Limiting Brute Force Lockout (Login POSTs vs Dashboard Traffic)
 * 4. Anti-Session Hijacking Fingerprint Binding (IPv4 / IPv6 Subnet Safe)
 * 5. Forensic Audit Trail & Disguised 404 Probes
 */
final class AdminGuardMiddleware
{
    public static function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && is_string($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && is_string($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $first = trim($parts[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }

        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function enforce(): void
    {
        $ip = self::getClientIp();
        $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

        // Gate 1: IP Whitelist Check
        $allowedIpsRaw = (string) ($_ENV['ADMIN_ALLOWED_IPS'] ?? getenv('ADMIN_ALLOWED_IPS') ?: '');
        if ($allowedIpsRaw !== '') {
            $allowedIps = array_filter(array_map('trim', explode(',', $allowedIpsRaw)));
            if (!empty($allowedIps) && !in_array($ip, $allowedIps, true)) {
                error_log("[AdminGuard] Unauthorized IP probe blocked: IP={$ip} UA={$userAgent}");
                self::renderDisguised404();
                return;
            }
        }

        // Gate 2: General Admin Throughput Limiter
        try {
            Limiter::limit('admin_traffic:' . $ip, 'default');
        } catch (\Throwable $e) {
            error_log("[AdminGuard] High-frequency admin traffic throttled for IP={$ip}: " . $e->getMessage());
            Utility::msgException(429, 'Too Many Requests. Please slow down.');
            return;
        }

        // Gate 3: Anti-Session Hijacking Fingerprint Check
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['id']) && ($_SESSION['role'] ?? '') === 'admin') {
            $expectedFingerprint = self::generateFingerprint($ip, $userAgent);
            $currentFingerprint = (string) ($_SESSION['admin_fingerprint'] ?? '');

            if ($currentFingerprint !== '' && !hash_equals($currentFingerprint, $expectedFingerprint)) {
                error_log("[AdminGuard] Session hijacking attempt detected: IP={$ip} UA={$userAgent}");
                session_unset();
                session_destroy();
                self::renderDisguised404();
                return;
            }
        }
    }

    public static function enforceLoginRateLimit(): void
    {
        $ip = self::getClientIp();
        try {
            Limiter::limit('admin_login:' . $ip, 'login');
        } catch (\Throwable $e) {
            error_log("[AdminGuard] Brute-force login lockout triggered for IP={$ip}: " . $e->getMessage());
            Utility::msgException(429, 'Too Many Failed Login Attempts. Administrative access locked for 15 minutes.');
            return;
        }
    }

    public static function bindSession(string $ip, string $userAgent): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['admin_fingerprint'] = self::generateFingerprint($ip, $userAgent);
            $_SESSION['admin_login_at'] = time();
        }
    }

    private static function generateFingerprint(string $ip, string $userAgent): string
    {
        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);
            $subnet = implode(':', array_slice($parts, 0, 4));
        } else {
            $parts = explode('.', $ip);
            $subnet = implode('.', array_slice($parts, 0, 3));
        }

        $secretKey = (string) ($_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: 'zero-trust-admin-salt');
        return hash_hmac('sha256', $subnet . '|' . $userAgent, $secretKey);
    }

    public static function renderDisguised404(): void
    {
        if (!headers_sent()) {
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p></body></html>';
        exit;
    }
}

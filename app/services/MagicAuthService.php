<?php

declare(strict_types=1);

namespace App\services;

use App\model\AllMembersData as AllMembersDataModel;
use InvalidArgumentException;
use PDO;
use Src\Db;
use Src\JwtHandler;
use Src\LoginUtility;
use Src\Utility;
use Throwable;

/**
 * 1-Click Ephemeral Magic Authentication Engine
 *
 * Facilitates frictionless re-engagement for dormant or zero-action pioneers
 * (e.g. Karen, Adesoji, Ekundayo, Taiwo) by generating cryptographic, single-use,
 * time-bounded login tokens that bypass password prompts and drop members directly
 * onto their family tree, Sunday banter poll, or heritage time capsules.
 *
 * Security Architecture (Adversarial Red Team Hardened):
 * - Raw tokens are never stored in the database. Only SHA-256 hashes are persisted.
 * - Single-use consumption is enforced via atomic database row locking (`FOR UPDATE`).
 * - Strict open-redirect sanitisation blocks off-site redirection attacks.
 * - Explicitly prevents administrative privilege escalation (admins must use full 2FA).
 * - Full audit logging and JWT login cookie issuance.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class MagicAuthService
{
    /** Default token time-to-live: 72 hours (3 days) */
    public const DEFAULT_TTL_HOURS = 72;

    /** Allowed path pattern: must start with a single slash, no double slashes, only safe URI characters */
    private const SAFE_PATH_REGEX = '~^/[a-zA-Z0-9_\-/?=&%#.]*$~';

    /**
     * Generate an ephemeral, single-use magic login token for an eligible member.
     *
     * @param string $userId Target member account ID
     * @param string $destination In-app landing path upon successful consumption
     * @param int $ttlHours Lifespan in hours (defaults to 72h)
     * @param array<string, mixed> $metadata Contextual tracking data (e.g. cohort, campaign)
     * @return string Plaintext 64-character hex token to be embedded in email/WhatsApp links
     *
     * @throws InvalidArgumentException If user is missing, deleted, or has administrative privileges
     */
    public static function generateToken(
        string $userId,
        string $destination = '/organogram',
        int $ttlHours = self::DEFAULT_TTL_HOURS,
        array $metadata = []
    ): string {
        $cleanUserId = trim($userId);
        if ($cleanUserId === '') {
            throw new InvalidArgumentException('User ID cannot be empty for magic token generation.');
        }

        $db = Db::connect2();

        // Verify account existence and privilege level
        $stmt = $db->prepare('SELECT id, email, type, status, deleted_at FROM account WHERE id = ? LIMIT 1');
        $stmt->execute([$cleanUserId]);
        /** @var array<string, mixed>|false $account */
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$account) {
            throw new InvalidArgumentException("User ID {$cleanUserId} does not exist.");
        }

        if (!empty($account['deleted_at'])) {
            throw new InvalidArgumentException("User ID {$cleanUserId} is permanently deleted.");
        }

        $accountStatus = strtolower(trim((string)($account['status'] ?? 'active')));
        if (in_array($accountStatus, ['suspended', 'banned', 'rejected', 'disabled'], true)) {
            throw new InvalidArgumentException("User ID {$cleanUserId} is not in an active state.");
        }

        $accountType = strtolower(trim((string)($account['type'] ?? 'member')));
        if ($accountType === 'admin' || $accountType === 'super_admin') {
            throw new InvalidArgumentException('Administrative accounts are strictly prohibited from magic link authentication.');
        }

        $safeDest = self::sanitizeDestination($destination, '/organogram');

        // Generate 32 bytes of cryptographically secure random entropy (64 hex characters)
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $expiresAt = date('Y-m-d H:i:s', time() + ($ttlHours * 3600));
        $metadataJson = !empty($metadata) ? json_encode($metadata, JSON_UNESCAPED_SLASHES) : null;

        $insStmt = $db->prepare('
            INSERT INTO magic_login_tokens (token_hash, user_id, destination, metadata, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $insStmt->execute([$tokenHash, $cleanUserId, $safeDest, $metadataJson, $expiresAt]);

        return $rawToken;
    }

    /**
     * Generate a complete, signed magic login URL.
     *
     * @param string $userId Target member account ID
     * @param string $destination In-app landing path
     * @param int $ttlHours Lifespan in hours
     * @param array<string, mixed> $metadata Contextual tracking data
     * @return string Fully qualified magic authentication URL
     */
    public static function generateMagicUrl(
        string $userId,
        string $destination = '/organogram',
        int $ttlHours = self::DEFAULT_TTL_HOURS,
        array $metadata = []
    ): string {
        $token = self::generateToken($userId, $destination, $ttlHours, $metadata);
        $rawBaseUrl = (string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com');
        $baseUrl = rtrim($rawBaseUrl, '/');
        $safeDest = self::sanitizeDestination($destination, '/organogram');

        return "{$baseUrl}/auth/magic?token={$token}&dest=" . urlencode($safeDest);
    }

    /**
     * Validate and consume a magic login token, establishing an authenticated session.
     *
     * @param string $rawToken Plaintext token supplied via query parameter
     * @param string|null $requestedDest Optional override destination from request query
     * @return array{
     *     success: bool,
     *     user_id: string,
     *     email: string,
     *     destination: string,
     *     error?: string
     * }
     */
    public static function consume(string $rawToken, ?string $requestedDest = null): array
    {
        $cleanToken = trim($rawToken);

        // Enforce strict format: exactly 64 hexadecimal characters
        if (strlen($cleanToken) !== 64 || !ctype_xdigit($cleanToken)) {
            return [
                'success'     => false,
                'user_id'     => '',
                'email'       => '',
                'destination' => '/login',
                'error'       => 'invalid_token_format',
            ];
        }

        $tokenHash = hash('sha256', $cleanToken);
        $db = Db::connect2();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare('
                SELECT id, user_id, destination, expires_at, used_at
                FROM magic_login_tokens
                WHERE token_hash = ?
                FOR UPDATE
            ');
            $stmt->execute([$tokenHash]);
            /** @var array<string, mixed>|false $record */
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => '',
                    'email'       => '',
                    'destination' => '/login',
                    'error'       => 'token_not_found',
                ];
            }

            if (!empty($record['used_at'])) {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => (string)($record['user_id'] ?? ''),
                    'email'       => '',
                    'destination' => '/login',
                    'error'       => 'token_already_consumed',
                ];
            }

            $expiresTimestamp = strtotime((string)($record['expires_at'] ?? ''));
            if ($expiresTimestamp === false || $expiresTimestamp < time()) {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => (string)($record['user_id'] ?? ''),
                    'email'       => '',
                    'destination' => '/login',
                    'error'       => 'token_expired',
                ];
            }

            $userId = (string)$record['user_id'];

            // Fetch authoritative account
            $accStmt = $db->prepare('SELECT id, email, type, status, deleted_at FROM account WHERE id = ? LIMIT 1');
            $accStmt->execute([$userId]);
            /** @var array<string, mixed>|false $account */
            $account = $accStmt->fetch(PDO::FETCH_ASSOC);

            if (!$account || !empty($account['deleted_at'])) {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => $userId,
                    'email'       => '',
                    'destination' => '/login',
                    'error'       => 'account_unavailable',
                ];
            }

            $accountStatus = strtolower(trim((string)($account['status'] ?? 'active')));
            if (in_array($accountStatus, ['suspended', 'banned', 'rejected', 'disabled'], true)) {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => $userId,
                    'email'       => '',
                    'destination' => '/login',
                    'error'       => 'account_unavailable',
                ];
            }

            // Prohibit admin escalation
            $accountType = strtolower(trim((string)($account['type'] ?? 'member')));
            if ($accountType === 'admin' || $accountType === 'super_admin') {
                $db->rollBack();
                return [
                    'success'     => false,
                    'user_id'     => $userId,
                    'email'       => (string)($account['email'] ?? ''),
                    'destination' => '/login',
                    'error'       => 'admin_escalation_blocked',
                ];
            }

            // Atomically mark token as consumed
            $updStmt = $db->prepare('UPDATE magic_login_tokens SET used_at = NOW() WHERE id = ?');
            $updStmt->execute([$record['id']]);

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[MagicAuthService] Database exception during consumption: ' . $e->getMessage());

            return [
                'success'     => false,
                'user_id'     => '',
                'email'       => '',
                'destination' => '/login',
                'error'       => 'system_error',
            ];
        }

        // Determine destination with fallback and open-redirect sanitisation
        $targetDest = !empty($requestedDest) ? $requestedDest : (string)$record['destination'];
        $safeDestination = self::sanitizeDestination($targetDest, '/profilePage');

        $userEmail = (string)($account['email'] ?? '');

        // Bootstrap the user session
        self::bootstrapMemberSession($account);

        // Audit log the magic authentication event
        try {
            LoginUtility::logAudit(
                $userId,
                $userEmail,
                'magic_login_success',
                Utility::getUserIpAddr(),
                substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 245)
            );
        } catch (Throwable $e) {
            // Non-fatal
        }

        return [
            'success'     => true,
            'user_id'     => $userId,
            'email'       => $userEmail,
            'destination' => $safeDestination,
        ];
    }

    /**
     * Sanitizes a requested redirect destination, neutralizing open redirect exploits.
     *
     * @param string $path Requested path
     * @param string $fallback Default fallback path if untrusted
     * @return string Safe relative URI path
     */
    public static function sanitizeDestination(string $path, string $fallback = '/profilePage'): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            return $fallback;
        }

        // Disallow scheme prefixes (e.g. http:, https:, javascript:, data:)
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $trimmed)) {
            return $fallback;
        }

        // Disallow protocol-relative URLs (//attacker.com) and backslashes
        if (str_starts_with($trimmed, '//') || str_contains($trimmed, '\\')) {
            return $fallback;
        }

        // Must start with a single slash
        if (!str_starts_with($trimmed, '/')) {
            return $fallback;
        }

        // Must match safe path characters
        if (!preg_match(self::SAFE_PATH_REGEX, $trimmed)) {
            return $fallback;
        }

        return $trimmed;
    }

    /**
     * Bootstraps authenticated member session state and issues JWT cookie.
     *
     * @param array<string, mixed> $user Account record
     */
    private static function bootstrapMemberSession(array $user): void
    {
        $userId = (string)($user['id'] ?? '');
        $userEmail = (string)($user['email'] ?? '');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        // Issue JWT HttpOnly login cookie
        JwtHandler::issueLoginCookie($user);

        // Clear any lingering authentication stage data
        unset($_SESSION['auth']['pendingUser'], $_SESSION['auth']['2FA_token_ts']);

        if (empty($_SESSION['token']) || !is_string($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        // Establish authoritative authenticated session
        $_SESSION['auth'] = [
            'identifyCust'    => $userId,
            'email'           => $userEmail,
            'codeVerified'    => true,
            'codeVerified_ts' => time(),
            'flow'            => 'authenticated',
            'type'            => (string)($user['type'] ?? 'member'),
        ];
        $_SESSION['id'] = $userId;
        $_SESSION['loggedIn'] = true;

        if (!function_exists('isTestEnv') || !isTestEnv()) {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
        }

        // Hydrate family codes and member profile details for graph and organogram access
        $famInfo = AllMembersDataModel::getFamCode($userId);
        $primaryFamCode = (string)($famInfo['famCode'] ?? '');
        $_SESSION['famCode'] = $primaryFamCode;
        $_SESSION['famCodes'] = $primaryFamCode !== '' ? [$primaryFamCode] : [];

        try {
            $db = Db::connect2();
            $pStmt = $db->prepare('SELECT firstName, lastName, famCode FROM personal WHERE id = ? LIMIT 1');
            $pStmt->execute([$userId]);
            $pRow = $pStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($pRow)) {
                $_SESSION['fName'] = (string)($pRow['firstName'] ?? '');
                $_SESSION['lName'] = (string)($pRow['lastName'] ?? '');
                $_SESSION['fullName'] = trim($_SESSION['fName'] . ' ' . $_SESSION['lName']);
                if ($_SESSION['famCode'] === '' && !empty($pRow['famCode'])) {
                    $_SESSION['famCode'] = (string)$pRow['famCode'];
                    $_SESSION['famCodes'] = [(string)$pRow['famCode']];
                }
            }
        } catch (Throwable) {
            // Non-fatal fallback
        }
    }
}

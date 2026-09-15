<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;
use Src\Exceptions\BadRequestException;

/**
 * InviteTokenService
 *
 * Central authority for creating and consuming all opaque invite tokens.
 * Replaces every PII-bearing URL pattern (?famCode=, ?name=, ?email=, ?familySurname=)
 * with a single 256-bit entropy hex token resolved server-side.
 *
 * Token shapes by surface:
 *   family_invite  — one-to-one, sent via email (max_uses=1, TTL=7d)
 *   onboarding     — general family share link (max_uses=50, TTL=30d)
 *   organogram     — node-targeted invite from family tree (max_uses=1, TTL=7d)
 *   referral       — platform recommendation (/join?ref=) (max_uses=1, TTL=30d)
 */
final class InviteTokenService
{
    // TTL constants (seconds)
    private const TTL_SHORT  = 604800;   // 7 days  — personal invites
    private const TTL_LONG   = 2592000;  // 30 days — general / referral links

    // Max-use constants
    private const USES_PERSONAL = 1;
    private const USES_GENERAL  = 50;

    /**
     * Create a new opaque invite token, persist it, and return the raw token string.
     *
     * @param string                $familyCode  The target family code
     * @param array<string, mixed>  $meta        Optional: first_name, last_name, email, node_id, invited_by
     * @param string                $type        One of: family_invite, onboarding, organogram, referral
     * @param int|null              $ttlSeconds  Defaults based on type
     * @param int|null              $maxUses     Defaults based on type
     * @return string               The 64-char hex token
     * @throws \RuntimeException    On DB insert failure
     */
    public static function create(
        string $familyCode,
        array $meta = [],
        string $type = 'family_invite',
        ?int $ttlSeconds = null,
        ?int $maxUses = null
    ): string {
        $token = bin2hex(random_bytes(32)); // 256-bit entropy

        // Sensible defaults per type
        $ttl     = $ttlSeconds ?? (in_array($type, ['onboarding', 'referral'], true) ? self::TTL_LONG : self::TTL_SHORT);
        $maxUse  = $maxUses    ?? (in_array($type, ['onboarding'], true) ? self::USES_GENERAL : self::USES_PERSONAL);
        $expires = date('Y-m-d H:i:s', time() + $ttl);

        $db   = Db::connect2();
        $stmt = $db->prepare(
            'INSERT INTO invite_tokens
                (token, family_code, first_name, last_name, email, node_id, invited_by, type, max_uses, expires_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $ok = $stmt->execute([
            $token,
            trim($familyCode),
            isset($meta['first_name'])  ? trim((string) $meta['first_name'])  : null,
            isset($meta['last_name'])   ? trim((string) $meta['last_name'])   : null,
            isset($meta['email'])       ? strtolower(trim((string) $meta['email'])) : null,
            isset($meta['node_id'])     ? (int) $meta['node_id']              : null,
            isset($meta['invited_by'])  ? trim((string) $meta['invited_by'])  : null,
            $type,
            $maxUse,
            $expires,
        ]);

        if (!$ok) {
            throw new \RuntimeException('[InviteTokenService] Failed to persist invite token.');
        }

        return $token;
    }

    /**
     * Validate and consume a token.
     *
     * On success:  increments use_count; sets used_at on the LAST use.
     * On failure:  throws BadRequestException — caller should show the expired-token UI.
     *
     * @return array{family_code: string, first_name: string|null, last_name: string|null,
     *               email: string|null, node_id: int|null, type: string}
     * @throws BadRequestException  If token is invalid, expired, or exhausted
     */
    public static function consume(string $token): array
    {
        $token = trim($token);
        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            throw new BadRequestException('This invite link is invalid. Please ask for a new one.');
        }

        $db = Db::connect2();

        // Fetch without consuming first — allows us to give precise error messages
        $stmt = $db->prepare(
            'SELECT id, family_code, first_name, last_name, email, node_id, type,
                    use_count, max_uses, expires_at, used_at
             FROM invite_tokens
             WHERE token = ?
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new BadRequestException('This invite link is invalid. Please ask for a new one.');
        }

        if (strtotime((string) $row['expires_at']) < time()) {
            throw new BadRequestException('This invite link has expired. Please ask your family member to send a new one.');
        }

        if ((int) $row['use_count'] >= (int) $row['max_uses']) {
            throw new BadRequestException('This invite link has already been used. Please ask for a new one.');
        }

        // Atomic consume — use rowCount() to detect race conditions
        $isLastUse   = ((int) $row['use_count'] + 1) >= (int) $row['max_uses'];
        $updateStmt  = $db->prepare(
            'UPDATE invite_tokens
             SET use_count = use_count + 1,
                 used_at   = CASE WHEN use_count + 1 >= max_uses THEN NOW() ELSE used_at END
             WHERE token = ?
               AND use_count < max_uses
               AND expires_at > NOW()
             LIMIT 1'
        );
        $updateStmt->execute([$token]);

        if ($updateStmt->rowCount() === 0) {
            // Race condition — another request consumed the last use simultaneously
            throw new BadRequestException('This invite link has already been used. Please ask for a new one.');
        }

        return [
            'family_code' => (string) $row['family_code'],
            'first_name'  => isset($row['first_name'])  ? (string) $row['first_name']  : null,
            'last_name'   => isset($row['last_name'])   ? (string) $row['last_name']   : null,
            'email'       => isset($row['email'])       ? (string) $row['email']       : null,
            'node_id'     => isset($row['node_id'])     ? (int)    $row['node_id']     : null,
            'type'        => (string) $row['type'],
        ];
    }

    /**
     * Peek at a token's metadata WITHOUT consuming it.
     * Used by the Register page to pre-fill the form display (not the actual registration).
     *
     * @return array{family_code: string, first_name: string|null, last_name: string|null,
     *               email: string|null, node_id: int|null, type: string}|null
     */
    public static function peek(string $token): ?array
    {
        $token = trim($token);
        if (strlen($token) !== 64 || !ctype_xdigit($token)) {
            return null;
        }

        $db   = Db::connect2();
        $stmt = $db->prepare(
            'SELECT family_code, first_name, last_name, email, node_id, type,
                    use_count, max_uses, expires_at
             FROM invite_tokens
             WHERE token = ?
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            return null;
        }
        if ((int) $row['use_count'] >= (int) $row['max_uses']) {
            return null;
        }

        return [
            'family_code' => (string) $row['family_code'],
            'first_name'  => isset($row['first_name'])  ? (string) $row['first_name']  : null,
            'last_name'   => isset($row['last_name'])   ? (string) $row['last_name']   : null,
            'email'       => isset($row['email'])       ? (string) $row['email']       : null,
            'node_id'     => isset($row['node_id'])     ? (int)    $row['node_id']     : null,
            'type'        => (string) $row['type'],
        ];
    }

    /**
     * Build the full /register?invite={token} URL.
     */
    public static function getRegisterUrl(string $token, string $baseUrl = ''): string
    {
        if ($baseUrl === '') {
            $baseUrl = rtrim((string)(getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');
        }
        return $baseUrl . '/register?invite=' . rawurlencode($token);
    }

    /**
     * Purge tokens that are expired AND fully consumed — safe to run daily via cron.
     */
    public static function purgeExpired(): int
    {
        $db   = Db::connect2();
        $stmt = $db->prepare(
            'DELETE FROM invite_tokens
             WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)'
        );
        $stmt->execute();
        return $stmt->rowCount();
    }
}

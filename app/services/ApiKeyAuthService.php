<?php

declare(strict_types=1);

namespace App\services;

use Src\Db;

/**
 * Enterprise API Key Governance & Authentication Service
 * 
 * Provides cryptographically secure API key issuance, constant-time validation,
 * scope enforcement, and audit telemetry for the KinshipCore Engine.
 * 
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class ApiKeyAuthService
{
    private const KEY_PREFIX = 'fp_live_';

    /**
     * Generate a new cryptographically secure API key
     * 
     * @param string $name Descriptive client name (e.g. "PartyPlatform Production")
     * @param string[] $scopes Array of permitted scopes (e.g. ["tree:read", "tree:write"])
     * @param string[] $familyCodes Array of permitted family codes or ["*"] for universal access
     * @param int $rateLimitPerMin Requests allowed per minute
     * @param string|null $expiresAt Optional ISO/SQL timestamp for expiration
     * @param string|null $allowedIps Optional IP address or CIDR whitelist
     * @param string|null $createdBy Admin user or system creating the key
     * @return array{id: int, name: string, raw_key: string, prefix: string, scopes: string[], expires_at: string|null}
     */
    public static function generateKey(
        string $name,
        array $scopes = ['tree:read'],
        array $familyCodes = ['*'],
        int $rateLimitPerMin = 120,
        ?string $expiresAt = null,
        ?string $allowedIps = null,
        ?string $createdBy = null
    ): array {
        $db = Db::connect2();

        $randomEntropy = bin2hex(random_bytes(24)); // 48 chars
        $rawKey = self::KEY_PREFIX . $randomEntropy;
        $prefix = substr($rawKey, 0, 16);
        $keyHash = hash('sha256', $rawKey);

        $stmt = $db->prepare("
            INSERT INTO api_keys (
                name, key_prefix, key_hash, scopes, family_codes, 
                rate_limit_per_min, allowed_ips, is_active, expires_at, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
        ");

        $stmt->execute([
            trim($name),
            $prefix,
            $keyHash,
            json_encode(array_values($scopes), JSON_THROW_ON_ERROR),
            json_encode(array_values($familyCodes), JSON_THROW_ON_ERROR),
            $rateLimitPerMin,
            $allowedIps,
            $expiresAt,
            $createdBy
        ]);

        $keyId = (int)$db->lastInsertId();

        return [
            'id' => $keyId,
            'name' => $name,
            'raw_key' => $rawKey,
            'prefix' => $prefix,
            'scopes' => $scopes,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Authenticate an incoming HTTP request using X-API-Key or Bearer token
     * 
     * @param string $requiredScope The scope required for this operation (e.g. 'tree:read')
     * @param string|null $familyCode Optional family context to verify tenant isolation
     * @return array<string, mixed> Validated key record
     * @throws \RuntimeException If authentication fails or permissions are denied
     */
    public static function authenticate(string $requiredScope = 'tree:read', ?string $familyCode = null): array
    {
        $rawKey = self::extractTokenFromRequest();

        if (empty($rawKey)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized: Missing API Key. Provide via X-API-Key or Authorization: Bearer <key> header.'
            ]);
            exit;
        }

        $keyHash = hash('sha256', $rawKey);
        $db = Db::connect2();

        $stmt = $db->prepare("
            SELECT * FROM api_keys 
            WHERE key_hash = ? AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$keyHash]);
        /** @var array<string, mixed>|false $keyRecord */
        $keyRecord = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$keyRecord) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized: Invalid or revoked API key.'
            ]);
            exit;
        }

        // Check expiration
        if (!empty($keyRecord['expires_at'])) {
            $expiresTimestamp = strtotime((string)$keyRecord['expires_at']);
            if ($expiresTimestamp !== false && $expiresTimestamp < time()) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Unauthorized: API key has expired.'
                ]);
                exit;
            }
        }

        // Check scopes
        $scopes = json_decode((string)($keyRecord['scopes'] ?? '[]'), true);
        if (!is_array($scopes)) {
            $scopes = [];
        }

        if (!in_array('*', $scopes, true) && !in_array($requiredScope, $scopes, true)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => "Forbidden: API key does not grant scope '{$requiredScope}'."
            ]);
            exit;
        }

        // Check family context restriction (tenant isolation)
        if ($familyCode !== null) {
            $allowedFamilies = json_decode((string)($keyRecord['family_codes'] ?? '["*"]'), true);
            if (!is_array($allowedFamilies)) {
                $allowedFamilies = ['*'];
            }

            if (!in_array('*', $allowedFamilies, true) && !in_array($familyCode, $allowedFamilies, true)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => "Forbidden: API key does not have access to family '{$familyCode}'."
                ]);
                exit;
            }
        }

        // Update last_used_at asynchronously / non-blocking
        try {
            $upd = $db->prepare("UPDATE api_keys SET last_used_at = NOW() WHERE id = ?");
            $upd->execute([(int)$keyRecord['id']]);
        } catch (\Throwable) {
            // Ignore telemetry write failure
        }

        return $keyRecord;
    }

    /**
     * Revoke an active API key
     */
    public static function revokeKey(int $id): bool
    {
        $db = Db::connect2();
        $stmt = $db->prepare("UPDATE api_keys SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * List all provisioned API keys (masked tokens)
     * 
     * @return array<int, array<string, mixed>>
     */
    public static function listKeys(): array
    {
        $db = Db::connect2();
        $stmt = $db->query("
            SELECT id, name, key_prefix, scopes, family_codes, rate_limit_per_min, 
                   is_active, expires_at, last_used_at, created_by, created_at 
            FROM api_keys 
            ORDER BY id DESC
        ");
        if (!$stmt) {
            return [];
        }
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $rows ?: [];
    }

    /**
     * Extract API key token from HTTP headers
     */
    private static function extractTokenFromRequest(): ?string
    {
        // 1. Check X-API-Key header
        $customHeader = $_SERVER['HTTP_X_API_KEY'] ?? null;
        if (!empty($customHeader) && is_string($customHeader)) {
            return trim($customHeader);
        }

        // 2. Check Authorization: Bearer <key>
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
        if (!empty($authHeader) && is_string($authHeader)) {
            if (preg_match('/Bearer\s+(fp_live_[a-zA-Z0-9]+)/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
        }

        // 3. Optional fallback for CLI or local testing via query param (disabled in production if needed)
        if (isset($_GET['api_key']) && is_string($_GET['api_key']) && str_starts_with($_GET['api_key'], self::KEY_PREFIX)) {
            return trim($_GET['api_key']);
        }

        return null;
    }
}

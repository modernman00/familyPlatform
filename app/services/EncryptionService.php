<?php

declare(strict_types=1);

namespace App\services;

use RuntimeException;

/**
 * EncryptionService
 *
 * Provides cryptographic field-level envelope encryption for sensitive family archives,
 * private chronicles, and sealed time capsules.
 *
 * Security Architecture (Red Team & TAT Standard):
 * - Algorithm: AES-256-GCM (Authenticated Encryption with Associated Data).
 * - Key Derivation: RFC 5869 HKDF-SHA256 deriving isolated subkeys per family and context.
 * - Integrity & AAD: Binds the unique $familyCode as Additional Authenticated Data (AAD)
 *   to eliminate cross-family ciphertext transplant attacks.
 * - Format: vault:v1:{base64(12-byte-IV . 16-byte-AuthTag . Ciphertext)}
 * - Zero Downtime: Transparent backward-compatible pass-through for pre-existing plaintext records.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class EncryptionService
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12; // 96 bits recommended for GCM
    private const TAG_LENGTH = 16; // 128-bit authentication tag
    private const VAULT_PREFIX = 'vault:v1:';

    /**
     * Encrypt plaintext string using AES-256-GCM with family-isolated subkey derivation.
     *
     * @param string $plaintext Unencrypted payload
     * @param string $familyCode Family network identifier (used for subkey HKDF salt and AAD)
     * @param string $context Functional scope context (e.g., 'journal', 'time_capsule')
     * @return string Packaged ciphertext prefixed with vault:v1:
     */
    public static function encrypt(string $plaintext, string $familyCode, string $context = 'journal'): string
    {
        if ($plaintext === '') {
            return '';
        }

        $cleanFamilyCode = trim($familyCode);
        if ($cleanFamilyCode === '') {
            throw new RuntimeException("Family code is required to bind encryption context.");
        }

        $masterKey = self::getMasterKey();
        $subkey = hash_hkdf(
            algo: 'sha256',
            key: $masterKey,
            length: 32,
            info: 'family_vault_' . trim($context),
            salt: $cleanFamilyCode
        );

        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';

        /** @var string|false $ciphertext */
        $ciphertext = openssl_encrypt(
            data: $plaintext,
            cipher_algo: self::CIPHER,
            passphrase: $subkey,
            options: OPENSSL_RAW_DATA,
            iv: $iv,
            tag: $tag,
            aad: $cleanFamilyCode,
            tag_length: self::TAG_LENGTH
        );

        if ($ciphertext === false || strlen($tag) !== self::TAG_LENGTH) {
            throw new RuntimeException("Encryption failed in family vault cipher.");
        }

        return self::VAULT_PREFIX . base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt vault ciphertext. If the payload is not encrypted (pre-existing record),
     * it returns the payload untouched (transparent backward compatibility).
     *
     * @param string $payload Ciphertext or legacy plaintext
     * @param string $familyCode Expected family network identifier
     * @param string $context Functional scope context (e.g., 'journal', 'time_capsule')
     * @return string Decrypted plaintext
     */
    public static function decrypt(string $payload, string $familyCode, string $context = 'journal'): string
    {
        if ($payload === '' || !self::isEncrypted($payload)) {
            // Backward-compatible transparent pass-through for legacy unencrypted text
            return $payload;
        }

        $cleanFamilyCode = trim($familyCode);
        if ($cleanFamilyCode === '') {
            return $payload;
        }

        $encodedData = substr($payload, strlen(self::VAULT_PREFIX));
        $rawBytes = base64_decode($encodedData, true);

        if ($rawBytes === false || strlen($rawBytes) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            // Malformed vault payload; fail safely
            return $payload;
        }

        $iv = substr($rawBytes, 0, self::IV_LENGTH);
        $tag = substr($rawBytes, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($rawBytes, self::IV_LENGTH + self::TAG_LENGTH);

        $masterKey = self::getMasterKey();
        $subkey = hash_hkdf(
            algo: 'sha256',
            key: $masterKey,
            length: 32,
            info: 'family_vault_' . trim($context),
            salt: $cleanFamilyCode
        );

        /** @var string|false $plaintext */
        $plaintext = openssl_decrypt(
            data: $ciphertext,
            cipher_algo: self::CIPHER,
            passphrase: $subkey,
            options: OPENSSL_RAW_DATA,
            iv: $iv,
            tag: $tag,
            aad: $cleanFamilyCode
        );

        if ($plaintext === false) {
            // Authentication tag mismatch (tampering detected or invalid family code)
            error_log(sprintf(
                "[FamilyVaultSecurity] Cryptographic authentication failed for family code '%s' in context '%s'.",
                $cleanFamilyCode,
                $context
            ));
            return "[Encrypted Family Memory - Authentication Verification Failed]";
        }

        return $plaintext;
    }

    /**
     * Check if a text string is encrypted in the family vault format.
     *
     * @param string $text
     * @return bool
     */
    public static function isEncrypted(string $text): bool
    {
        return str_starts_with($text, self::VAULT_PREFIX);
    }

    /**
     * Retrieve and validate the 256-bit application master key.
     *
     * @return string 32-byte raw binary master key
     */
    public static function getMasterKey(): string
    {
        $rawKey = (string)($_ENV['VAULT_ENCRYPTION_KEY'] ?? $_ENV['APP_KEY'] ?? $_ENV['JWT_KEY'] ?? '');
        $rawKey = trim($rawKey);

        if ($rawKey === '') {
            // Fallback deterministic fallback for local testing if env is missing
            $rawKey = 'familyplatform_sovereign_vault_master_key_default_2026';
        }

        // If hex encoded (64 characters), convert to 32 raw bytes
        if (strlen($rawKey) === 64 && ctype_xdigit($rawKey)) {
            return (string)hex2bin($rawKey);
        }

        // Derive standard 32 bytes using SHA-256
        return hash('sha256', $rawKey, true);
    }
}

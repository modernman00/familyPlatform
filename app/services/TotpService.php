<?php

declare(strict_types=1);

namespace App\services;

/**
 * Native RFC 6238 Time-based One-Time Password (TOTP) Service for Google Authenticator.
 * Zero external vendor dependencies — relies purely on native hash_hmac and pack/unpack.
 */
final class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a secret key in base32 format (default 16 chars / 80 bits).
     *
     * @param int<1, max> $length
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[ord($bytes[$i]) & 31];
        }
        return $secret;
    }

    /**
     * Generate otpauth:// URI suitable for Google Authenticator QR Code rendering.
     */
    public static function getQrCodeUrl(string $accountName, string $secret, string $issuer = 'FamilyPlatform'): string
    {
        $label = rawurlencode($issuer . ':' . $accountName);
        $issuerEncoded = rawurlencode($issuer);
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerEncoded}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Calculate 6-digit TOTP code for a given timestamp and secret.
     */
    public static function getCode(string $secret, ?int $timestamp = null, int $period = 30): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / $period);

        $secretKey = self::base32Decode($secret);
        // Pack timeSlice into 8-byte big-endian binary string
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);

        $hash = hash_hmac('sha1', $binaryTime, $secretKey, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $truncatedHash = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        );

        $code = (string) ($truncatedHash % 1000000);
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit code against secret with an optional window offset (default ±1 period = ±30s).
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timestamp = null, int $period = 30): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }

        $timestamp = $timestamp ?? time();
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $evalTime = $timestamp + ($i * $period);
            if (hash_equals(self::getCode($secret, $evalTime, $period), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Base32 decoding helper.
     */
    private static function base32Decode(string $base32): string
    {
        $base32 = strtoupper(trim($base32));
        $base32 = str_replace('=', '', $base32);
        $binary = '';

        for ($i = 0; $i < strlen($base32); $i++) {
            $position = strpos(self::ALPHABET, $base32[$i]);
            if ($position === false) {
                continue;
            }
            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $bytes .= chr((int) bindec($byte));
            }
        }

        return $bytes;
    }
}

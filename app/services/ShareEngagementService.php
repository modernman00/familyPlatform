<?php

declare(strict_types=1);

namespace App\services;

use PDO;

/**
 * ShareEngagementService
 *
 * Privacy-preserving funnel telemetry for public share landings and the
 * unified Guest Gate overlay (post, spark, arcade, festive_card).
 * Stores zero PII — only a daily-salted SHA-256 visitor hash.
 */
final class ShareEngagementService
{
    public const VALID_SHARE_TYPES = ['post', 'spark', 'arcade', 'festive_card'];
    public const VALID_EVENT_TYPES = ['view', 'skip', 'signup_click', 'auth_click'];
    public const VALID_PROVIDERS = ['', 'google', 'facebook', 'apple', 'email', 'login'];

    public static function ensureTableExists(PDO $db): void
    {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `public_share_engagements` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `share_type` ENUM('post', 'spark', 'arcade', 'festive_card') NOT NULL DEFAULT 'post',
                `item_key` VARCHAR(64) NOT NULL DEFAULT '',
                `event_type` ENUM('view', 'skip', 'signup_click', 'auth_click') NOT NULL,
                `provider` VARCHAR(32) NOT NULL DEFAULT '',
                `visitor_hash` VARCHAR(64) NOT NULL DEFAULT '',
                `referrer_source` VARCHAR(64) NOT NULL DEFAULT 'direct',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_pub_share_type_event` (`share_type`, `event_type`),
                KEY `idx_pub_share_item` (`item_key`, `created_at`),
                KEY `idx_pub_share_visitor` (`visitor_hash`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function computeVisitorHash(): string
    {
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $salt = (string)($_ENV['APP_KEY'] ?? 'familyplatform_share_salt') . ':' . date('Y-m-d');
        return hash('sha256', $ip . '|' . $ua . '|' . $salt);
    }

    public static function detectReferrerSource(): string
    {
        $utm = trim((string)($_GET['utm_source'] ?? $_GET['src'] ?? ''));
        if ($utm !== '') {
            return substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $utm) ?? 'direct', 0, 64) ?: 'direct';
        }
        $ref = strtolower((string)($_SERVER['HTTP_REFERER'] ?? ''));
        if ($ref === '') {
            return 'direct';
        }
        if (str_contains($ref, 'whatsapp') || str_contains($ref, 'wa.me')) {
            return 'whatsapp';
        }
        if (str_contains($ref, 'facebook') || str_contains($ref, 'fb.com')) {
            return 'facebook';
        }
        if (str_contains($ref, 't.co') || str_contains($ref, 'twitter') || str_contains($ref, 'x.com')) {
            return 'x';
        }
        return 'web';
    }

    /**
     * @param array{
     *   share_type: string,
     *   item_key: string,
     *   event_type: string,
     *   provider?: string,
     *   visitor_hash?: string,
     *   referrer_source?: string
     * } $data
     */
    public static function recordEvent(PDO $db, array $data): bool
    {
        $shareType = in_array($data['share_type'], self::VALID_SHARE_TYPES, true) ? $data['share_type'] : 'post';
        $eventType = in_array($data['event_type'], self::VALID_EVENT_TYPES, true) ? $data['event_type'] : '';
        if ($eventType === '') {
            return false;
        }

        $provider = in_array($data['provider'] ?? '', self::VALID_PROVIDERS, true) ? (string)($data['provider'] ?? '') : '';
        $itemKey = substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)$data['item_key']) ?? '', 0, 64);
        $visitorHash = substr((string)($data['visitor_hash'] ?? self::computeVisitorHash()), 0, 64);
        $refSource = substr(preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($data['referrer_source'] ?? 'direct')) ?? 'direct', 0, 64) ?: 'direct';

        self::ensureTableExists($db);

        $stmt = $db->prepare(
            'INSERT INTO public_share_engagements
                (share_type, item_key, event_type, provider, visitor_hash, referrer_source)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        return $stmt->execute([$shareType, $itemKey, $eventType, $provider, $visitorHash, $refSource]);
    }
}

<?php
declare(strict_types=1);

namespace App\services;

use Src\Select;
use PDO;

/**
 * Lightweight, fire-and-forget analytics tracker.
 *
 * Records discrete platform events (reel views, kinship impressions,
 * connect-through, dismissals) to `platform_analytics` for business
 * intelligence, conversion funnels, and product iteration.
 */
final class AnalyticsService
{
    /**
     * Track a single analytics event.
     *
     * @param string      $userId    Alphanumeric user ID
     * @param string      $eventType e.g. 'reel_view','reel_create','kinship_impression','kinship_connect','kinship_dismiss'
     * @param string|null $targetId  Optional target (reel ID, suggested user ID)
     * @param array<string, mixed>|null $metadata Optional JSON metadata
     */
    public static function track(
        string $userId,
        string $eventType,
        ?string $targetId = null,
        ?array $metadata = null
    ): void {
        // Fire-and-forget: never let analytics crash the request
        try {
            $pdo = Select::connect2();
            $stmt = $pdo->prepare("
                INSERT INTO platform_analytics (user_id, event_type, target_id, metadata, ip_address, user_agent)
                VALUES (:uid, :event, :target, :meta, :ip, :ua)
            ");
            $stmt->execute([
                ':uid'    => $userId,
                ':event'  => $eventType,
                ':target' => $targetId,
                ':meta'   => $metadata !== null ? json_encode($metadata) : null,
                ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua'     => isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 512) : null,
            ]);
        } catch (\Throwable $e) {
            error_log("[AnalyticsService] Track failed: " . $e->getMessage());
        }
    }

    /**
     * Batch-track multiple events (e.g. kinship impressions).
     *
     * @param string $userId
     * @param string $eventType
     * @param array<int, array{target_id: string, metadata?: array<string, mixed>}> $events
     */
    public static function trackBatch(string $userId, string $eventType, array $events): void
    {
        if (empty($events)) {
            return;
        }

        try {
            $pdo = Select::connect2();
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 512) : null;

            $stmt = $pdo->prepare("
                INSERT INTO platform_analytics (user_id, event_type, target_id, metadata, ip_address, user_agent)
                VALUES (:uid, :event, :target, :meta, :ip, :ua)
            ");

            foreach ($events as $event) {
                $stmt->execute([
                    ':uid'    => $userId,
                    ':event'  => $eventType,
                    ':target' => $event['target_id'],
                    ':meta'   => isset($event['metadata']) ? json_encode($event['metadata']) : null,
                    ':ip'     => $ip,
                    ':ua'     => $ua,
                ]);
            }
        } catch (\Throwable $e) {
            error_log("[AnalyticsService] Batch track failed: " . $e->getMessage());
        }
    }

    /**
     * Simple per-user, per-action rate limiter using the platform_analytics table.
     *
     * Returns true if the user is within the allowed rate window; false if rate-limited.
     *
     * @param string $userId
     * @param string $action   The action to rate-limit (e.g. 'kinship_suggestions_api')
     * @param int    $maxHits  Maximum allowed hits
     * @param int    $windowSeconds Time window in seconds
     */
    public static function isRateLimited(string $userId, string $action, int $maxHits = 30, int $windowSeconds = 60): bool
    {
        try {
            $pdo = Select::connect2();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS cnt
                FROM platform_analytics
                WHERE user_id = :uid
                  AND event_type = :action
                  AND created_at >= DATE_SUB(NOW(), INTERVAL :window SECOND)
            ");
            $stmt->execute([
                ':uid'    => $userId,
                ':action' => $action,
                ':window' => $windowSeconds,
            ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ((int)($row['cnt'] ?? 0)) >= $maxHits;
        } catch (\Throwable $e) {
            error_log("[AnalyticsService] Rate check failed: " . $e->getMessage());
            return false; // Fail open — don't break the feature
        }
    }
}

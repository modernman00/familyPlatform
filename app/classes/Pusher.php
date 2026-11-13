<?php

declare(strict_types=1);

namespace App\classes;

use Pusher\Pusher as PusherNotification;

class Pusher
{
    /**
     * True when this process must NOT deliver anything to real users (realtime,
     * web push, email). Local/dev/testing share the production Pusher app and
     * a prod-derived DB, so without this guard test runs notify live members.
     * Opt in explicitly with ALLOW_OUTBOUND_NOTIFICATIONS=true.
     */
    public static function outboundBlocked(): bool
    {
        $allow = strtolower(trim((string) ($_ENV['ALLOW_OUTBOUND_NOTIFICATIONS'] ?? getenv('ALLOW_OUTBOUND_NOTIFICATIONS') ?: ''), "'\""));
        if ($allow === 'true' || $allow === '1') {
            return false;
        }
        $env = strtolower(trim((string) ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production'), "'\""));
        return defined('TESTING_ENV') || in_array($env, ['local', 'development', 'dev', 'testing'], true);
    }

    /**
     * @param bool $signingOnly Channel-auth signing is a local HMAC with no network call, so it
     *                          is exempt from the outbound guard (which only protects delivery).
     */
    public static function pusher(bool $signingOnly = false): ?PusherNotification
    {
        if (!$signingOnly && self::outboundBlocked()) {
            return null;
        }
        $key = trim((string)($_ENV['MIX_PUSHER_APP_KEY'] ?? $_SERVER['MIX_PUSHER_APP_KEY'] ?? $_ENV['PUSHER_APP_KEY'] ?? $_SERVER['PUSHER_APP_KEY'] ?? (getenv('MIX_PUSHER_APP_KEY') ?: (getenv('PUSHER_APP_KEY') ?: ''))));
        $secret = trim((string)($_ENV['MIX_PUSHER_APP_SECRET'] ?? $_SERVER['MIX_PUSHER_APP_SECRET'] ?? $_ENV['PUSHER_APP_SECRET'] ?? $_SERVER['PUSHER_APP_SECRET'] ?? (getenv('MIX_PUSHER_APP_SECRET') ?: (getenv('PUSHER_APP_SECRET') ?: ''))));
        $appId = trim((string)($_ENV['MIX_PUSHER_APP_ID'] ?? $_SERVER['MIX_PUSHER_APP_ID'] ?? $_ENV['PUSHER_APP_ID'] ?? $_SERVER['PUSHER_APP_ID'] ?? (getenv('MIX_PUSHER_APP_ID') ?: (getenv('PUSHER_APP_ID') ?: ''))));
        $cluster = trim((string)($_ENV['MIX_PUSHER_APP_CLUSTER'] ?? $_SERVER['MIX_PUSHER_APP_CLUSTER'] ?? $_ENV['PUSHER_APP_CLUSTER'] ?? $_SERVER['PUSHER_APP_CLUSTER'] ?? (getenv('MIX_PUSHER_APP_CLUSTER') ?: (getenv('PUSHER_APP_CLUSTER') ?: 'eu'))));

        // Strip any surrounding quotes from .env parsing
        $key = trim($key, "'\"");
        $secret = trim($secret, "'\"");
        $appId = trim($appId, "'\"");
        $cluster = trim($cluster, "'\"");

        if ($key === '' || $secret === '' || $appId === '') {
            error_log("[Pusher] Warning: Pusher credentials missing in environment (key={$key}, appId={$appId}). Real-time broadcast skipped.");
            return null;
        }

        try {
            return new PusherNotification(
                $key,
                $secret,
                $appId,
                [
                    'cluster' => $cluster !== '' ? $cluster : 'eu',
                    'useTLS' => true,
                    'timeout' => 2,
                ]
            );
        } catch (\Throwable $e) {
            error_log("[Pusher] Failed to instantiate Pusher client: " . $e->getMessage());
            return null;
        }
    }

    public static function broadcast(string $theChannel, string $theEvent, array $theData): void
    {
        self::trigger([$theChannel], $theEvent, $theData);
    }

    private static function quotaBreakerPath(): string
    {
        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fp_pusher_quota_breaker.txt';
    }

    /**
     * True when Pusher recently rejected broadcasts due to daily quota / rate-limit exhaustion.
     */
    public static function isQuotaDegraded(): bool
    {
        if (function_exists('apcu_fetch')) {
            $ok = false;
            $val = apcu_fetch('fp_pusher_quota_degraded', $ok);
            if ($ok && is_int($val) && $val > time()) {
                return true;
            }
        }
        $path = self::quotaBreakerPath();
        if (!is_file($path)) {
            return false;
        }
        $expiresAt = (int) @file_get_contents($path);
        if ($expiresAt > time()) {
            return true;
        }
        @unlink($path);
        return false;
    }

    /**
     * Trip the Pusher quota circuit-breaker for $ttlSeconds so requests fail over
     * immediately to SovereignBroadcaster without burning 2s HTTP timeouts.
     */
    public static function markQuotaDegraded(int $ttlSeconds = 300): void
    {
        $ttl = max(30, min($ttlSeconds, 3600));
        $expiresAt = time() + $ttl;
        if (function_exists('apcu_store')) {
            @apcu_store('fp_pusher_quota_degraded', $expiresAt, $ttl);
        }
        @file_put_contents(self::quotaBreakerPath(), (string) $expiresAt, LOCK_EX);
    }

    public static function clearQuotaDegraded(): void
    {
        if (function_exists('apcu_delete')) {
            @apcu_delete('fp_pusher_quota_degraded');
        }
        $path = self::quotaBreakerPath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * High-concurrency multi-channel broadcast trigger.
     * Batches all channels into a single HTTP request to Pusher instead of N round-trips.
     *
     * @param string|list<string> $channels
     * @param string $event
     * @param array<string, mixed> $data
     */
    public static function trigger(string|array $channels, string $event, array $data): void
    {
        $rawList = is_array($channels) ? $channels : [$channels];
        /** @var list<string> $channelList */
        $channelList = array_values(array_unique(array_filter(array_map('strval', $rawList))));
        if (empty($channelList)) {
            return;
        }

        // 1. Primary Attempt: Pusher Multi-Channel trigger (1 single HTTP request for up to 100 channels)
        if (!self::isQuotaDegraded()) {
            try {
                $pusher = self::pusher();
                if ($pusher !== null) {
                    $pusher->trigger($channelList, $event, $data);
                }
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (preg_match('/\b(403|429)\b|quota|limit|exceeded/i', $msg)) {
                    self::markQuotaDegraded(300);
                }
                error_log("[Pusher] Multi-channel broadcast to (" . implode(',', $channelList) . ") failed: " . $msg . ". Defaulting to SovereignBroadcaster.");
            }
        }

        // 2. Local Fallback & Synchronisation Rail: SovereignBroadcaster
        try {
            if (class_exists('\\Src\\Realtime\\SovereignBroadcaster')) {
                $broadcaster = new \Src\Realtime\SovereignBroadcaster();
                /** @var array<string, mixed> $payload */
                $payload = [];
                foreach ($data as $k => $v) {
                    $payload[(string)$k] = $v;
                }
                foreach ($channelList as $ch) {
                    $broadcaster->trigger($ch, $event, $payload);
                }
            }
        } catch (\Throwable $e) {
            error_log("[SovereignBroadcaster] Broadcast to (" . implode(',', $channelList) . ") failed: " . $e->getMessage());
        }
    }

    /**
     * Private, per-family channel name. Only members of $famCode — verified by
     * authoriseChannel() below — can subscribe. This is what stops one family's
     * feed and events being readable by anyone who has the (public) Pusher key.
     */
    public static function familyChannel(string $famCode): string
    {
        return 'private-family-' . self::slug($famCode);
    }

    /** Private, per-user channel (personal notifications, friend requests). */
    public static function userChannel(string $userId): string
    {
        return 'private-user-' . self::slug($userId);
    }

    /**
     * Broadcast an event to a family's private channel. No-ops (and logs) when
     * the family code is empty so a mis-scoped call fails closed instead of
     * silently falling back to a world-readable channel.
     *
     * @param array<int|string, mixed> $data
     */
    public static function broadcastToFamily(string $famCode, string $event, array $data): void
    {
        $famCode = trim($famCode);
        if ($famCode === '') {
            error_log("Pusher::broadcastToFamily called with an empty famCode for event '{$event}'");
            return;
        }

        self::broadcast(self::familyChannel($famCode), $event, $data);
    }

    /**
     * Pusher private-channel authorisation endpoint — POST /pusher/auth.
     *
     * pusher-js calls this whenever a client subscribes to a `private-*`
     * channel. We only return a signed grant when the *server session* proves
     * the caller may be on that channel: their own `private-user-<id>`, or a
     * `private-family-<code>` for a family code held in their session. Router
     * auth (RouteDispatch fail-closed) already guarantees a logged-in user here.
     */
    public static function authoriseChannel(): void
    {
        header('Content-Type: application/json');

        $userId   = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string) $_SESSION['id'] : '';
        $channel  = isset($_POST['channel_name']) && is_string($_POST['channel_name']) ? $_POST['channel_name'] : '';
        $socketId = isset($_POST['socket_id']) && is_string($_POST['socket_id']) ? $_POST['socket_id'] : '';

        // Fallback for JSON body payloads sent by clients/proxies
        if ($channel === '' || $socketId === '') {
            $rawBody = (string)file_get_contents('php://input');
            if ($rawBody !== '') {
                $decoded = json_decode($rawBody, true);
                if (is_array($decoded)) {
                    $channel = isset($decoded['channel_name']) && is_string($decoded['channel_name']) ? $decoded['channel_name'] : $channel;
                    $socketId = isset($decoded['socket_id']) && is_string($decoded['socket_id']) ? $decoded['socket_id'] : $socketId;
                }
            }
        }

        if ($userId === '' || $channel === '' || $socketId === '') {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $allowed = false;

        if (preg_match('/^private-user-(.+)$/', $channel, $m)) {
            $expectedUserSlug = self::slug($userId);
            $allowed = $expectedUserSlug !== '' && (
                hash_equals($expectedUserSlug, $m[1]) ||
                hash_equals(strtolower($expectedUserSlug), strtolower($m[1]))
            );
        } elseif (preg_match('/^private-family-(.+)$/', $channel, $m)) {
            foreach (self::sessionFamCodes() as $fc) {
                $fcSlug = self::slug($fc);
                if ($fcSlug !== '' && (
                    hash_equals($fcSlug, $m[1]) ||
                    hash_equals(strtolower($fcSlug), strtolower($m[1]))
                )) {
                    $allowed = true;
                    break;
                }
            }
            if (!$allowed && class_exists('\App\controller\BaseController')) {
                $allowed = \App\controller\BaseController::sessionSharesFamily($m[1]);
            }
        }

        if (function_exists('releaseSessionLock')) {
            \releaseSessionLock();
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (!$allowed) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $pusher = self::pusher(true);
        if ($pusher === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Pusher not configured on server']);
            return;
        }

        try {
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- $channel is validated against a strict private-user-*/private-family-* pattern AND hash_equals-checked against the session before we get here; the output is a Pusher-signed auth grant ({"auth":"key:sig"}) served as application/json, not HTML.
            echo $pusher->authorizeChannel($channel, $socketId);
        } catch (\Throwable $e) {
            error_log("[Pusher] authorizeChannel exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Authorization signing failed']);
        }
    }

    public static function slug(string $value): string
    {
        return (string) preg_replace('/[^A-Za-z0-9_-]/', '', $value);
    }

    /** @return list<string> every family code the current session belongs to */
    private static function sessionFamCodes(): array
    {
        $codes = [];

        $many = $_SESSION['famCodes'] ?? [];
        if (is_string($many)) {
            $many = [$many];
        }
        if (is_array($many)) {
            foreach ($many as $fc) {
                if (is_string($fc) && trim($fc) !== '') {
                    $codes[] = trim($fc);
                }
            }
        }

        if (!empty($_SESSION['famCode']) && is_string($_SESSION['famCode'])) {
            $codes[] = trim($_SESSION['famCode']);
        }

        return array_values(array_unique($codes));
    }
}

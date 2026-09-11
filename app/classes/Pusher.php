<?php

declare(strict_types=1);

namespace App\classes;

use Pusher\Pusher as PusherNotification;

class Pusher
{
    public static function pusher(): ?PusherNotification
    {
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
                    'timeout' => 5,
                ]
            );
        } catch (\Throwable $e) {
            error_log("[Pusher] Failed to instantiate Pusher client: " . $e->getMessage());
            return null;
        }
    }

    public static function broadcast(string $theChannel, string $theEvent, array $theData): void
    {
        try {
            $pusher = self::pusher();
            if ($pusher === null) {
                return;
            }
            $pusher->trigger($theChannel, $theEvent, $theData);
        } catch (\Throwable $e) {
            error_log("[Pusher] Broadcast to {$theChannel} failed: " . $e->getMessage());
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
        try {
            $pusher = self::pusher();
            if ($pusher === null) {
                return;
            }
            $pusher->trigger(self::familyChannel($famCode), $event, $data);
        } catch (\Throwable $e) {
            error_log("[Pusher] broadcastToFamily to famCode '{$famCode}' failed: " . $e->getMessage());
        }
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
            $allowed = hash_equals(self::slug($userId), $m[1]);
        } elseif (preg_match('/^private-family-(.+)$/', $channel, $m)) {
            foreach (self::sessionFamCodes() as $fc) {
                if (hash_equals(self::slug($fc), $m[1])) {
                    $allowed = true;
                    break;
                }
            }
        }

        if (!$allowed) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $pusher = self::pusher();
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

    private static function slug(string $value): string
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

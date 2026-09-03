<?php

declare(strict_types=1);

namespace App\classes;

use Pusher\Pusher as PusherNotification;

class Pusher
{
    public static function pusher(): PusherNotification
    {
        return new PusherNotification(
            getenv('MIX_PUSHER_APP_KEY') ?: '',
            getenv('MIX_PUSHER_APP_SECRET') ?: '',
            getenv('MIX_PUSHER_APP_ID') ?: '',
            [
                'cluster' => getenv('MIX_PUSHER_APP_CLUSTER') ?: '',
                'useTLS' => true,
            ]
        );
    }

    public static function broadcast(string $theChannel, string $theEvent, array $theData): void
    {
        self::pusher()->trigger($theChannel, $theEvent, $theData);
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
        self::pusher()->trigger(self::familyChannel($famCode), $event, $data);
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

        echo self::pusher()->authorizeChannel($channel, $socketId);
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

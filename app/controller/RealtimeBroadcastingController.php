<?php

declare(strict_types=1);

namespace App\controller;

use Src\Db;
use Src\Realtime\SovereignBroadcaster;
use Throwable;

final class RealtimeBroadcastingController
{
    /**
     * Verify whether the authenticated session user is authorised to access the target channel.
     * Prevents BOLA/IDOR cross-family and cross-user eavesdropping.
     */
    public static function isAuthorizedForChannel(string $userId, string $channel): bool
    {
        $userId = trim($userId);
        $channel = trim($channel);

        if ($userId === '' || $channel === '' || strlen($channel) > 128) {
            return false;
        }

        // Reject any channel containing control characters or newlines (SSE injection guard)
        if (preg_match('/[\r\n\x00-\x1F]/', $channel)) {
            return false;
        }

        $userSlug = self::slug($userId);

        if (preg_match('/^(?:private-)?user-(.+)$/', $channel, $m)) {
            return $userSlug !== '' && hash_equals($userSlug, $m[1]);
        }

        if (preg_match('/^(?:private-)?family-(.+)$/', $channel, $m)) {
            $requestedFamSlug = $m[1];
            foreach (self::resolveAuthorizedFamilyCodes($userId) as $fc) {
                $famSlug = self::slug($fc);
                if ($famSlug !== '' && hash_equals($famSlug, $requestedFamSlug)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Resolve all family codes the user is legitimately entitled to access.
     *
     * @return list<string>
     */
    private static function resolveAuthorizedFamilyCodes(string $userId): array
    {
        $codes = [];

        $many = $_SESSION['famCodes'] ?? [];
        if (is_string($many) && trim($many) !== '') {
            $codes[] = trim($many);
        } elseif (is_array($many)) {
            foreach ($many as $fc) {
                if (is_string($fc) && trim($fc) !== '') {
                    $codes[] = trim($fc);
                }
            }
        }

        if (!empty($_SESSION['famCode']) && is_string($_SESSION['famCode'])) {
            $codes[] = trim($_SESSION['famCode']);
        }

        if (empty($codes)) {
            try {
                $db = Db::connect2();
                $stmt = $db->prepare('SELECT famCode FROM personal WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                $primary = $stmt->fetchColumn();
                if (is_string($primary) && trim($primary) !== '') {
                    $codes[] = trim($primary);
                }

                $ufStmt = $db->prepare("SELECT family_code FROM user_families WHERE user_id = ? AND status = 'approved'");
                $ufStmt->execute([$userId]);
                $extra = $ufStmt->fetchAll(\PDO::FETCH_COLUMN);
                if (is_array($extra)) {
                    foreach ($extra as $c) {
                        if (is_string($c) && trim($c) !== '') {
                            $codes[] = trim($c);
                        }
                    }
                }
            } catch (Throwable) {
                // Fail closed to session codes
            }
        }

        return array_values(array_unique($codes));
    }

    private static function slug(string $value): string
    {
        return (string)preg_replace('/[^A-Za-z0-9_-]/', '', $value);
    }

    /**
     * Server-Sent Events (SSE) stream endpoint for SovereignBroadcaster
     * Route: GET /api/broadcasting/events
     */
    public function stream(): void
    {
        $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
        if ($userId === '') {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorised']);
            if (!defined('TESTING_ENV')) {
                exit;
            }
            return;
        }

        $channel = trim((string)($_GET['channel'] ?? ''));
        if ($channel === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Channel required']);
            if (!defined('TESTING_ENV')) {
                exit;
            }
            return;
        }

        if (!self::isAuthorizedForChannel($userId, $channel)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden: channel access denied']);
            if (!defined('TESTING_ENV')) {
                exit;
            }
            return;
        }

        // Release PHP session lock immediately before starting SSE stream to prevent session DoS
        if (function_exists('releaseSessionLock')) {
            \releaseSessionLock();
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Disable output buffering and compression for SSE
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // For Nginx

        $lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID'])
            ? max(0, (int)$_SERVER['HTTP_LAST_EVENT_ID'])
            : max(0, (int)($_GET['last_event_id'] ?? 0));

        try {
            $broadcaster = new SovereignBroadcaster();
            $broadcaster->ensureSchema();

            // Heartbeat
            echo ": heartbeat " . time() . "\n\n";
            flush();

            // Fetch any events missed since lastEventId
            $events = $broadcaster->getEvents($channel, $lastEventId, 25);
            foreach ($events as $ev) {
                $safeId = (int)($ev['id'] ?? 0);
                $safeEvent = htmlspecialchars((string)preg_replace('/[^a-zA-Z0-9_.\-]/', '', (string)($ev['event'] ?? 'message')), ENT_QUOTES, 'UTF-8');
                echo "id: {$safeId}\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                echo "event: {$safeEvent}\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                echo "data: " . json_encode($ev['data'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "\n\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                $lastEventId = $safeId;
            }
            flush();

            // Long-poll / SSE poll loop (up to 20 seconds before graceful client reconnect)
            $startTime = time();
            while ((time() - $startTime) < 20) {
                if (connection_aborted()) {
                    break;
                }

                $newEvents = $broadcaster->getEvents($channel, $lastEventId, 25);
                if (!empty($newEvents)) {
                    foreach ($newEvents as $ev) {
                        $safeId = (int)($ev['id'] ?? 0);
                        $safeEvent = htmlspecialchars((string)preg_replace('/[^a-zA-Z0-9_.\-]/', '', (string)($ev['event'] ?? 'message')), ENT_QUOTES, 'UTF-8');
                        echo "id: {$safeId}\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                        echo "event: {$safeEvent}\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                        echo "data: " . json_encode($ev['data'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . "\n\n"; // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                        $lastEventId = $safeId;
                    }
                    flush();
                } else {
                    if ((time() - $startTime) % 5 === 0) {
                        echo ": ping\n\n";
                        flush();
                    }
                }

                usleep(500000); // 500ms
            }
        } catch (Throwable $e) {
            error_log('[RealtimeBroadcastingController::stream] ' . $e->getMessage());
            echo "event: error\ndata: " . json_encode(['error' => 'Stream interrupted']) . "\n\n";
            flush();
        }
        if (!defined('TESTING_ENV')) {
            exit;
        }
    }

    /**
     * Channel authorization endpoint for private/presence channels
     * Route: POST /api/broadcasting/auth
     */
    public function auth(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $userId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorised']);
                return;
            }

            $raw = file_get_contents('php://input');
            $data = json_decode((string)$raw, true) ?: $_POST;

            $channelName = is_string($data['channel_name'] ?? null) ? trim($data['channel_name']) : '';
            $socketId = is_string($data['socket_id'] ?? null) ? trim($data['socket_id']) : '';

            if ($channelName === '' || $socketId === '') {
                http_response_code(400);
                echo json_encode(['error' => 'channel_name and socket_id required']);
                return;
            }

            if (!self::isAuthorizedForChannel($userId, $channelName)) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden: channel access denied']);
                return;
            }

            $broadcaster = new SovereignBroadcaster();
            $authData = $broadcaster->authenticateChannel($channelName, $socketId, [
                'user_id' => $userId,
            ]);

            echo json_encode($authData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (Throwable $e) {
            error_log('[RealtimeBroadcastingController::auth] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Channel authorisation failed']);
        }
    }
}

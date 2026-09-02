<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\AnalyticsService;
use Src\functionality\SignIn;

/**
 * Lightweight client-side analytics beacon endpoint.
 *
 * Accepts fire-and-forget event tracking POSTs from the browser.
 * Always returns 204 No Content to minimise payload.
 */
final class AnalyticsController extends BaseController
{
    /**
     * POST /api/analytics/track
     *
     * Body: { event_type: string, target_id?: string, metadata?: object }
     */
    public function track(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if (empty($userId)) {
                http_response_code(401);
                echo json_encode(['status' => 'error']);
                return;
            }

            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $eventType = (string)($payload['event_type'] ?? '');

            // Whitelist allowed client-side event types
            $allowedEvents = [
                'reel_view',
                'reel_create',
                'kinship_connect',
                'kinship_dismiss',
                'kinship_impression',
            ];

            if (empty($eventType) || !in_array($eventType, $allowedEvents, true)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid event type']);
                return;
            }

            $targetId = isset($payload['target_id']) ? (string)$payload['target_id'] : null;
            $metadata = isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : null;

            AnalyticsService::track($userId, $eventType, $targetId, $metadata);

            http_response_code(204);
        } catch (\Throwable $e) {
            http_response_code(204); // Never fail analytics — swallow and return 204
        }
    }
}

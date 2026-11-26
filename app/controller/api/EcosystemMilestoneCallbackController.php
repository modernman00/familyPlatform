<?php

declare(strict_types=1);

namespace App\controller\api;

use App\services\MilestoneSurpriseBridgeService;
use InvalidArgumentException;
use Src\ApiResponse;
use Src\Limiter;

/**
 * S2S HMAC-authenticated callback endpoint receiving PartyPlatform's
 * claimed standalone celebration binding (`party_event_id` + contributor links).
 *
 * Route: POST /api/v1/ecosystem/milestone-link-callback
 * Listed in RouteDispatch::$publicControllers (authenticates via X-Ecosystem-Signature; reads zero user session state).
 */
final class EcosystemMilestoneCallbackController
{
    private MilestoneSurpriseBridgeService $bridgeService;

    public function __construct(?MilestoneSurpriseBridgeService $bridgeService = null)
    {
        $this->bridgeService = $bridgeService ?? new MilestoneSurpriseBridgeService();
    }

    public function handleCallback(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            try {
                Limiter::limit('ecosystem_milestone_cb_' . (string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            } catch (\Throwable) {
                ApiResponse::error('Too many requests.', 429);
                return;
            }

            $rawBody    = file_get_contents('php://input');
            $rawBodyStr = is_string($rawBody) ? $rawBody : '';
            $tsHeader   = isset($_SERVER['HTTP_X_ECOSYSTEM_TIMESTAMP']) ? (string) $_SERVER['HTTP_X_ECOSYSTEM_TIMESTAMP'] : null;
            $sigHeader  = isset($_SERVER['HTTP_X_ECOSYSTEM_SIGNATURE']) ? (string) $_SERVER['HTTP_X_ECOSYSTEM_SIGNATURE'] : null;

            if (!MilestoneSurpriseBridgeService::verifyEcosystemSignature($rawBodyStr, $tsHeader, $sigHeader)) {
                ApiResponse::error('Unauthorised: Invalid or expired ecosystem HMAC signature.', 401);
                return;
            }

            $decoded = json_decode($rawBodyStr, true);
            if (!is_array($decoded)) {
                ApiResponse::error('Invalid JSON payload.', 400);
                return;
            }

            $saved = $this->bridgeService->recordCallbackPayload($decoded);
            ApiResponse::success([
                'milestone_key'  => (string) ($saved['milestone_key'] ?? ''),
                'party_event_id' => (string) ($saved['party_event_id'] ?? ''),
            ], 'Milestone surprise celebration linked.', 200);
        } catch (InvalidArgumentException $e) {
            ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[EcosystemMilestoneCallbackController::handleCallback] ' . $e->getMessage());
            ApiResponse::error('Failed to record milestone link.', 500);
        }
    }
}

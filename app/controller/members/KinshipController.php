<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\KinshipEngineService;
use App\services\AnalyticsService;
use Src\functionality\SignIn;
use Exception;

final class KinshipController extends BaseController
{
    /**
     * API: Get Suggested Kin List
     *
     * Rate-limited to 30 requests per minute per user.
     * Strips famCode from response for privacy.
     * Tracks kinship_impression analytics events.
     */
    public function getSuggestions(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if (empty($userId)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthenticated']);
                return;
            }

            // ── Rate Limiting: 30 requests/minute ──
            if (AnalyticsService::isRateLimited($userId, 'kinship_suggestions_api', 30, 60)) {
                http_response_code(429);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again in a moment.'
                ]);
                return;
            }

            // Track the API call itself for rate limiting
            AnalyticsService::track($userId, 'kinship_suggestions_api');

            $limit = min(isset($_GET['limit']) ? (int)$_GET['limit'] : 8, 20);
            $suggestions = KinshipEngineService::getSuggestedKin($userId, $limit);

            // ── Privacy hardening: strip famCode from response ──
            // famCode is an internal identifier and should not be exposed
            // to the client. An attacker could enumerate family structures.
            $sanitisedSuggestions = array_map(static function (array $s): array {
                unset($s['famCode']);
                return $s;
            }, $suggestions);

            // ── Track kinship impressions for analytics ──
            if (!empty($sanitisedSuggestions)) {
                $impressionEvents = array_map(static fn(array $s) => [
                    'target_id' => $s['user_id'],
                    'metadata'  => [
                        'confidence_score' => $s['confidence_score'],
                        'kinship_type'     => $s['kinship_type'],
                    ]
                ], $sanitisedSuggestions);

                AnalyticsService::trackBatch($userId, 'kinship_impression', $impressionEvents);
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $sanitisedSuggestions,
                'count'  => count($sanitisedSuggestions)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
        }
    }

    /**
     * API: Dismiss a suggested relative.
     *
     * Tracks kinship_dismiss analytics event.
     */
    public function dismiss(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $dismissedUserId = (string)($payload['dismissed_user_id'] ?? '');

            if (empty($userId) || empty($dismissedUserId)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing dismissed_user_id']);
                return;
            }

            $success = KinshipEngineService::dismissSuggestion($userId, $dismissedUserId);

            // Track dismissal
            if ($success) {
                AnalyticsService::track($userId, 'kinship_dismiss', $dismissedUserId);
            }

            echo json_encode([
                'status'  => $success ? 'success' : 'error',
                'message' => $success ? 'Suggestion dismissed' : 'Failed to dismiss suggestion'
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        }
    }
}

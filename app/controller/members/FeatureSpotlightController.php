<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FeatureSpotlightService;
use Src\CorsHandler;
use Src\Limiter;

/**
 * FeatureSpotlightController
 *
 * REST API controller for the in-feed Feature Discovery Spotlight Engine.
 */
final class FeatureSpotlightController extends BaseController
{
    /**
     * GET /api/spotlights/feed
     *
     * Returns the curated, rotated list of Feature Spotlights for the authenticated user.
     */
    public function getFeedSpotlights(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            Limiter::limit($userId . ':spotlight_get');

            $dismissed = $this->getSessionDismissedIds();
            \releaseSessionLock();

            $spotlights = FeatureSpotlightService::getFeedSpotlights($userId, $dismissed);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'spotlights' => $spotlights,
                'dismissed_ids' => $dismissed,
                'total_catalog' => count(FeatureSpotlightService::getCatalog($userId)),
            ]);
        } catch (\Throwable $e) {
            error_log('[FeatureSpotlightController::getFeedSpotlights] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to load feature spotlights right now.']);
        }
    }

    /**
     * POST /api/spotlights/interact
     *
     * Records a user interaction (dismiss, launch, shuffle) for a Feature Spotlight.
     */
    public function interact(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            Limiter::limit($userId . ':spotlight_interact');

            $raw = file_get_contents('php://input');
            $payload = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            if (!is_array($payload)) {
                $payload = $_POST;
            }

            $spotlightId = isset($payload['spotlight_id']) && is_string($payload['spotlight_id'])
                ? trim($payload['spotlight_id'])
                : '';
            $action = isset($payload['action']) && is_string($payload['action'])
                ? trim($payload['action'])
                : '';

            if ($spotlightId === '' || !FeatureSpotlightService::exists($spotlightId)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Invalid feature spotlight identifier.']);
                return;
            }

            if ($action === '' || !FeatureSpotlightService::isValidAction($action)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Invalid spotlight action.']);
                return;
            }

            $dismissed = $this->getSessionDismissedIds();
            if ($action === 'dismiss' && !in_array($spotlightId, $dismissed, true)) {
                $dismissed[] = $spotlightId;
                $_SESSION['feature_spotlights_dismissed'] = $dismissed;
            }

            \releaseSessionLock();

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'spotlight_id' => $spotlightId,
                'action' => $action,
                'dismissed_ids' => $dismissed,
            ]);
        } catch (\Throwable $e) {
            error_log('[FeatureSpotlightController::interact] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to save spotlight preference.']);
        }
    }

    /**
     * POST /api/spotlights/reset
     *
     * Restores all dismissed Feature Spotlights for the current user session.
     */
    public function reset(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            Limiter::limit($userId . ':spotlight_reset');

            $_SESSION['feature_spotlights_dismissed'] = [];
            \releaseSessionLock();

            $spotlights = FeatureSpotlightService::getFeedSpotlights($userId, []);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'spotlights' => $spotlights,
            ]);
        } catch (\Throwable $e) {
            error_log('[FeatureSpotlightController::reset] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Unable to restore feature spotlights.']);
        }
    }

    /**
     * @return list<string>
     */
    private function getSessionDismissedIds(): array
    {
        $raw = $_SESSION['feature_spotlights_dismissed'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $clean = [];
        foreach ($raw as $item) {
            if (is_string($item) && $item !== '' && FeatureSpotlightService::exists($item)) {
                $clean[] = $item;
            }
        }
        return array_values(array_unique($clean));
    }
}

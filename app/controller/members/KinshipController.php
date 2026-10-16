<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\KinshipEngineService;
use App\services\KinshipEntityResolver;
use App\services\AnalyticsService;
use Src\functionality\SignIn;
use Src\CheckToken;
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
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
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

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json set above); json_encode escapes the payload.
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
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
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

    /**
     * API: Get pending auto-matches for the authenticated user on the current family tree.
     */
    public function getPendingMatches(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $familyCode = (string)($_SESSION['famCode'] ?? '');

            if (empty($userId) || empty($familyCode)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthenticated or missing family context']);
                return;
            }

            // Rate Limiting: 30 requests/minute
            if (AnalyticsService::isRateLimited($userId, 'kinship_matches_api', 30, 60)) {
                http_response_code(429);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again shortly.'
                ]);
                return;
            }
            AnalyticsService::track($userId, 'kinship_matches_api');

            /** @var array<string, mixed> $memberData */
            $memberData = BaseController::findMemberById($userId);
            $matches = KinshipEntityResolver::findProbableMatches($familyCode, $userId, $memberData);

            echo json_encode([
                'status' => 'success',
                'data'   => $matches,
                'count'  => count($matches)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
        }
    }

    /**
     * API: Claim an auto-matched node on the family tree.
     */
    public function claimMatch(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $familyCode = (string)($_SESSION['famCode'] ?? '');

            if (empty($userId) || empty($familyCode)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            // Rate limiting: 10 claims per minute
            if (AnalyticsService::isRateLimited($userId, 'kinship_claim_api', 10, 60)) {
                http_response_code(429);
                echo json_encode(['status' => 'error', 'message' => 'Too many claim requests. Please wait a moment.']);
                return;
            }
            AnalyticsService::track($userId, 'kinship_claim_api');

            $rawInput = file_get_contents('php://input');
            $rawString = is_string($rawInput) ? $rawInput : '';
            /** @var array<string, mixed> $payload */
            $payload = (is_array($decoded = json_decode($rawString, true)) ? $decoded : null) ?: $_POST;

            // CSRF Verification
            $sessionToken = (string)($_SESSION['token'] ?? '');
            $reqToken = (string)($payload['token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_SERVER['HTTP_X_XSRF_TOKEN'] ?? '')));
            if (empty($sessionToken) || !hash_equals($sessionToken, $reqToken)) {
                if (!empty($_POST['token'])) {
                    CheckToken::tokenCheck();
                } else {
                    http_response_code(403);
                    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing CSRF token']);
                    return;
                }
            }

            $targetNodeId = (int)($payload['node_id'] ?? 0);
            if ($targetNodeId <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Valid node_id is required']);
                return;
            }

            /** @var array<string, mixed> $memberData */
            $memberData = BaseController::findMemberById($userId);
            $result = KinshipEntityResolver::claimAndMergeNode($familyCode, $userId, $targetNodeId, $memberData);

            http_response_code((int)$result['code']);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode([
                'status'  => $result['success'] ? 'success' : 'error',
                'message' => htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8'),
                'data'    => $result
            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()]);
        }
    }

    /**
     * API: Dismiss an auto-matched candidate node.
     */
    public function dismissMatch(): void
    {
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $familyCode = (string)($_SESSION['famCode'] ?? '');

            if (empty($userId) || empty($familyCode)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $rawString = is_string($rawInput) ? $rawInput : '';
            /** @var array<string, mixed> $payload */
            $payload = (is_array($decoded = json_decode($rawString, true)) ? $decoded : null) ?: $_POST;

            $nodeId = (int)($payload['node_id'] ?? 0);
            if ($nodeId <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Valid node_id is required']);
                return;
            }

            $success = KinshipEntityResolver::dismissMatch($familyCode, $userId, $nodeId);

            echo json_encode([
                'status'  => $success ? 'success' : 'error',
                'message' => $success ? 'Match dismissed successfully' : 'Failed to dismiss match'
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
        }
    }
}


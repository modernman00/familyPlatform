<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\OnboardingService;
use App\services\FeatureDiscoveryService;
use Src\CheckToken;

final class OnboardingController
{
    private OnboardingService $onboardingService;
    private FeatureDiscoveryService $discoveryService;

    public function __construct(
        ?OnboardingService $onboardingService = null,
        ?FeatureDiscoveryService $discoveryService = null
    ) {
        $this->onboardingService = $onboardingService ?? new OnboardingService();
        $this->discoveryService = $discoveryService ?? new FeatureDiscoveryService();
    }

    /**
     * GET /api/onboarding/state
     *
     * Returns current onboarding checklist progress and viral family invite links.
     */
    public function getOnboardingState(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

            // Release session lock for non-blocking concurrent AJAX fetching
            \releaseSessionLock();

            $state = $this->onboardingService->getOnboardingState($userId, $famCode);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $state,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * POST /api/onboarding/step
     *
     * Records completion of an onboarding checklist item.
     */
    public function completeStep(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                if ($raw !== false && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $input = $decoded;
                    }
                }
            }

            $stepKey = is_string($input['step'] ?? null) ? trim($input['step']) : '';
            if ($stepKey === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Step key is required']);
                return;
            }

            $result = $this->onboardingService->completeStep($userId, $stepKey);

            $statusCode = ($result['status'] === 'success') ? 200 : 400;
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $th) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * GET /api/discovery/journey
     *
     * Returns full 60-day feature discovery status, active milestone, and unlocked studios.
     */
    public function getDiscoveryJourney(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

            \releaseSessionLock();

            $state = $this->discoveryService->getUserDiscoveryState($userId, $famCode);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $state,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * POST /api/discovery/fast-forward
     *
     * Records interactive launch/completion of any feature milestone (Silas Thorne Fast-Forward).
     */
    public function fastForwardFeature(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $input = $_POST;
            if (empty($input)) {
                $raw = file_get_contents('php://input');
                if ($raw !== false && $raw !== '') {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        $input = $decoded;
                    }
                }
            }

            $featureKey = is_string($input['feature_key'] ?? null) ? trim($input['feature_key']) : '';
            if ($featureKey === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Feature key is required']);
                return;
            }

            $result = $this->discoveryService->markMilestoneCompleted($userId, $featureKey);

            $statusCode = ($result['status'] === 'success') ? 200 : 400;
            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $th) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
}

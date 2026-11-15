<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\OnboardingService;
use App\services\FeatureDiscoveryService;
use App\services\ProfileTourService;
use Src\CheckToken;
use Src\Exceptions\UnauthorisedException;

final class OnboardingController
{
    private OnboardingService $onboardingService;
    private FeatureDiscoveryService $discoveryService;
    private ProfileTourService $profileTourService;

    public function __construct(
        ?OnboardingService $onboardingService = null,
        ?FeatureDiscoveryService $discoveryService = null,
        ?ProfileTourService $profileTourService = null
    ) {
        $this->onboardingService = $onboardingService ?? new OnboardingService();
        $this->discoveryService = $discoveryService ?? new FeatureDiscoveryService();
        $this->profileTourService = $profileTourService ?? new ProfileTourService();
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

    /**
     * GET /api/profile/tour-preference
     *
     * Returns whether the authenticated user has permanently dismissed the profile tour.
     */
    public function getProfileTourPreference(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $tourKey = is_string($_GET['tour_key'] ?? null) && trim($_GET['tour_key']) !== ''
                ? trim($_GET['tour_key'])
                : ProfileTourService::PROFILE_TOUR_KEY;

            if (!$this->profileTourService->isAllowedTourKey($tourKey)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Invalid tour key']);
                return;
            }

            if (function_exists('releaseSessionLock')) {
                \releaseSessionLock();
            }

            $dismissed = $this->profileTourService->isDismissed($userId, $tourKey);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'tour_key' => $tourKey,
                'dismissed' => $dismissed,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unable to load tour preference.']);
        }
    }

    /**
     * POST /api/profile/tour-preference
     *
     * Persists permanent dismissal (or re-enabling) of the profile tour for the authenticated user.
     */
    public function saveProfileTourPreference(): void
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

            $tourKey = is_string($input['tour_key'] ?? null) && trim($input['tour_key']) !== ''
                ? trim($input['tour_key'])
                : ProfileTourService::PROFILE_TOUR_KEY;

            if (!$this->profileTourService->isAllowedTourKey($tourKey)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Invalid tour key']);
                return;
            }

            $rawDismissed = $input['dismissed'] ?? true;
            $dismissed = filter_var($rawDismissed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($dismissed === null) {
                $dismissed = true;
            }

            $result = $this->profileTourService->setDismissed($userId, $dismissed, $tourKey);
            $statusCode = ($result['status'] === 'success') ? 200 : 400;

            http_response_code($statusCode);
            header('Content-Type: application/json');
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (UnauthorisedException $e) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $th) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }
}

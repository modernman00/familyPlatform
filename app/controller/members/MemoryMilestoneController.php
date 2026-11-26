<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\MemoryMilestoneService;
use App\services\MilestoneSurpriseBridgeService;
use Src\CheckToken;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;

final class MemoryMilestoneController
{
    private MemoryMilestoneService $memoryService;
    private MilestoneSurpriseBridgeService $surpriseBridge;

    public function __construct(
        ?MemoryMilestoneService $memoryService = null,
        ?MilestoneSurpriseBridgeService $surpriseBridge = null
    ) {
        $this->memoryService = $memoryService ?? new MemoryMilestoneService();
        $this->surpriseBridge = $surpriseBridge ?? new MilestoneSurpriseBridgeService();
    }

    /**
     * GET /api/memories/today
     *
     * Returns today's nostalgia memories and upcoming milestones for the active user's family.
     */
    public function getMemoriesAndMilestones(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $primaryFamCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
            $famCodes = $_SESSION['famCodes'] ?? ($primaryFamCode !== '' ? [$primaryFamCode] : []);

            // Release session lock for non-blocking asynchronous UI fetching
            \releaseSessionLock();

            $memories = $this->memoryService->getNostalgiaMemories($userId, (array)$famCodes);
            $milestones = $this->memoryService->getUpcomingMilestones($userId, (array)$famCodes, 7);

            // Automatically dispatch in-app and WebPush PWA notifications for today's flashbacks and milestones
            if (!empty($memories) || !empty($milestones)) {
                $this->memoryService->dispatchDailyMemoriesAndMilestonesForUser($userId, (array)$famCodes);
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'memories' => $memories,
                    'milestones' => $milestones,
                    'total_memories' => count($memories),
                    'total_milestones' => count($milestones),
                ]
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * POST /api/memories/share
     *
     * Reshares a past nostalgia flashback memory to the live family feed.
     */
    public function shareMemory(): void
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

            $postNo = (int)($input['post_no'] ?? 0);
            $reflectionMessage = is_string($input['reflection_message'] ?? null) ? trim($input['reflection_message']) : '';
            $famCode = is_string($input['fam_code'] ?? null) ? trim($input['fam_code']) : (string)($_SESSION['famCode'] ?? '');

            if ($postNo <= 0) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Invalid memory post number']);
                return;
            }

            if ($famCode === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Family code required']);
                return;
            }

            $result = $this->memoryService->shareMemoryToFeed($userId, $postNo, $reflectionMessage, $famCode);

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
     * GET /member/milestones/surprise-launch
     *
     * Verifies family membership & celebrant/partner privacy shield, then redirects
     * either to PartyPlatform's standalone claim flow (first clicker) or directly
     * to the shared group card / goodwill video recorder (all subsequent relatives).
     */
    public function launchSurpriseCelebration(): void
    {
        try {
            $userId = $_SESSION['id'] ?? null;
            if (!is_string($userId) || trim($userId) === '') {
                http_response_code(401);
                echo 'Authentication is required.';
                return;
            }

            $primaryFamCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
            $famCodes = $_SESSION['famCodes'] ?? ($primaryFamCode !== '' ? [$primaryFamCode] : []);

            $type     = is_string($_GET['type'] ?? null) ? trim($_GET['type']) : '';
            $entityId = is_string($_GET['entity_id'] ?? null) ? trim($_GET['entity_id']) : '';
            $feature  = is_string($_GET['feature'] ?? null) ? trim($_GET['feature']) : 'card';

            \releaseSessionLock();

            $redirectUrl = $this->surpriseBridge->resolveLaunchRedirectUrl(
                $userId,
                array_map('strval', (array) $famCodes),
                $type,
                $entityId,
                $feature
            );

            if (!headers_sent()) {
                header('Location: ' . $redirectUrl, true, 302);
            }
        } catch (ForbiddenException $e) {
            http_response_code(403);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } catch (NotFoundException $e) {
            http_response_code(404);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } catch (\Throwable $e) {
            error_log('[MemoryMilestoneController::launchSurpriseCelebration] ' . $e->getMessage());
            http_response_code(500);
            echo 'Unable to launch celebration workspace right now. Please try again.';
        }
    }
}

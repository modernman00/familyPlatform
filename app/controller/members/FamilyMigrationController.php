<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyMigrationService;
use Src\CheckToken;
use Src\Limiter;
use Src\functionality\SignIn;
use Src\functionality\middleware\GetRequestData;

final class FamilyMigrationController extends BaseController
{
    private FamilyMigrationService $migrationService;

    public function __construct(?FamilyMigrationService $migrationService = null)
    {
        $this->migrationService = $migrationService ?? new FamilyMigrationService();
    }

    /**
     * Helper to authenticate the current session or bearer token.
     */
    private function resolveAuthUserId(): string
    {
        $sessId = isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? (string)$_SESSION['id'] : '';
        if ($sessId !== '') {
            return $sessId;
        }

        try {
            $payload = SignIn::verify('users');
            return (string)cleanSession((string)$payload['id']);
        } catch (\Throwable) {
            // Authentication fallback
        }

        return '';
    }

    /**
     * API: Send a migration nudge to a discovered relative.
     * POST /api/family-migration/nudge
     */
    public function nudge(): void
    {
        header('Content-Type: application/json');

        try {
            Limiter::limit($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        } catch (\Throwable) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please slow down.']);
            return;
        }

        $input = GetRequestData::getRequestData();

        try {
            self::validateCsrf($input);
        } catch (\Throwable $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF security token.']);
            return;
        }

        $userId = $this->resolveAuthUserId();
        if ($userId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Please sign in to send a migration invitation.']);
            return;
        }

        $targetUserId = trim((string)($input['target_user_id'] ?? $_POST['target_user_id'] ?? ''));
        $note = isset($input['note']) ? (string)$input['note'] : (isset($_POST['note']) ? (string)$_POST['note'] : null);

        if ($targetUserId === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Target family relative ID is required.']);
            return;
        }

        $result = $this->migrationService->createNudge($userId, $targetUserId, $note);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            return;
        }

        echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
    }

    /**
     * API: Request to migrate into a discovered family code.
     * POST /api/family-migration/request
     */
    public function request(): void
    {
        header('Content-Type: application/json');

        try {
            Limiter::limit($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        } catch (\Throwable) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests. Please slow down.']);
            return;
        }

        $input = GetRequestData::getRequestData();

        try {
            self::validateCsrf($input);
        } catch (\Throwable $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF security token.']);
            return;
        }

        $userId = $this->resolveAuthUserId();
        if ($userId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Please sign in to request a family migration.']);
            return;
        }

        $familyCode = trim((string)($input['family_code'] ?? $_POST['family_code'] ?? ''));
        $targetUserId = isset($input['target_user_id']) ? (string)$input['target_user_id'] : (isset($_POST['target_user_id']) ? (string)$_POST['target_user_id'] : null);
        $note = isset($input['note']) ? (string)$input['note'] : (isset($_POST['note']) ? (string)$_POST['note'] : null);

        if ($familyCode === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Target family code is required.']);
            return;
        }

        $result = $this->migrationService->createRequest($userId, $familyCode, $targetUserId, $note);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            return;
        }

        echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
    }

    /**
     * API: Relative accepts incoming migration nudge.
     * POST /api/family-migration/accept-nudge
     */
    public function acceptNudge(): void
    {
        header('Content-Type: application/json');

        $input = GetRequestData::getRequestData();

        try {
            self::validateCsrf($input);
        } catch (\Throwable $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF security token.']);
            return;
        }

        $userId = $this->resolveAuthUserId();
        if ($userId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $id = (int)($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        $token = trim((string)($input['token'] ?? $_POST['token'] ?? $_GET['token'] ?? ''));

        if ($id <= 0 || $token === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Migration ID and authorization token are required.']);
            return;
        }

        $result = $this->migrationService->acceptNudge($id, $userId, $token);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            return;
        }

        echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
    }

    /**
     * API: Family member approves self-initiated migration request.
     * POST /api/family-migration/approve-request
     */
    public function approveRequest(): void
    {
        header('Content-Type: application/json');

        $input = GetRequestData::getRequestData();

        try {
            self::validateCsrf($input);
        } catch (\Throwable $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF security token.']);
            return;
        }

        $userId = $this->resolveAuthUserId();
        if ($userId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $id = (int)($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        $token = trim((string)($input['token'] ?? $_POST['token'] ?? $_GET['token'] ?? ''));

        if ($id <= 0 || $token === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Migration ID and authorization token are required.']);
            return;
        }

        $result = $this->migrationService->approveRequest($id, $userId, $token);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            return;
        }

        echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
    }

    /**
     * API: Decline a nudge or deny a migration request.
     * POST /api/family-migration/decline
     */
    public function decline(): void
    {
        header('Content-Type: application/json');

        $input = GetRequestData::getRequestData();

        try {
            self::validateCsrf($input);
        } catch (\Throwable $e) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid or missing CSRF security token.']);
            return;
        }

        $userId = $this->resolveAuthUserId();
        if ($userId === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $id = (int)($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);
        $token = trim((string)($input['token'] ?? $_POST['token'] ?? $_GET['token'] ?? ''));
        $reason = trim((string)($input['reason'] ?? $_POST['reason'] ?? ''));

        if ($id <= 0 || $token === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Migration ID and authorization token are required.']);
            return;
        }

        $result = $this->migrationService->declineOrReject($id, $userId, $token, $reason);
        if (!$result['success']) {
            http_response_code(422);
            echo json_encode(['error' => $result['message']]); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            return;
        }

        echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
    }
}

<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyCommitmentService;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\ValidationException;
use Src\SelectFn;

/**
 * Controller for Family Commitments & Recurring Targets.
 */
final class FamilyCommitmentController extends BaseController
{
    private FamilyCommitmentService $service;

    public function __construct(?FamilyCommitmentService $service = null)
    {
        parent::__construct();
        $this->service = $service ?? new FamilyCommitmentService();
    }

    /**
     * Render the Family Commitments dashboard page.
     * GET /commitments
     */
    public function index(): void
    {
        $userId = $this->currentUserId();
        if ($userId === '') {
            header('Location: /login');
            exit;
        }

        $famCodes = $this->currentFamCodes($userId);
        $primaryFamCode = $famCodes[0] ?? '';

        $userData = SelectFn::selectOneRow('personal', 'id', $userId) ?: [];
        $data = $userData;

        $familyMembers = $this->fetchFamilyMembers($primaryFamCode, $userId);
        try {
            $commitments = $this->service->listForUser($userId, $famCodes);
        } catch (\Throwable $e) {
            error_log('FamilyCommitmentController::index listForUser error: ' . $e->getMessage());
            $commitments = [];
        }
        try {
            $dueReminders = $this->service->getDueInAppReminders($userId);
        } catch (\Throwable $e) {
            error_log('FamilyCommitmentController::index getDueInAppReminders error: ' . $e->getMessage());
            $dueReminders = [];
        }

        view('member.commitments', [
            'data' => $data,
            'userData' => $userData,
            'page_title' => 'Family Commitments — FamilyPlatform',
            'currentUserId' => $userId,
            'primaryFamCode' => $primaryFamCode,
            'familyMembers' => $familyMembers,
            'commitments' => $commitments,
            'dueReminders' => $dueReminders,
            'csrfToken' => $this->activeCsrfToken(),
        ]);
    }

    /**
     * List all accessible commitments and active in-app reminders.
     * GET /api/commitments
     */
    public function listApi(): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $famCodes = $this->currentFamCodes($userId);
        try {
            $commitments = $this->service->listForUser($userId, $famCodes);
            $reminders = $this->service->getDueInAppReminders($userId);
        } catch (\Throwable $e) {
            error_log('FamilyCommitmentController::listApi error: ' . $e->getMessage());
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to load commitments right now.']);
            return;
        }

        $this->sendApiPayload(200, [
            'status' => 'success',
            'commitments' => $commitments,
            'reminders' => $reminders,
        ]);
    }

    /**
     * Create a new commitment.
     * POST /api/commitments
     */
    public function store(): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $payload = $this->readRequestPayload();
        if (!$this->verifyCsrf($payload)) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
            return;
        }

        $famCodes = $this->currentFamCodes($userId);
        $primaryFamCode = $famCodes[0] ?? '';

        try {
            $created = $this->service->createCommitment($userId, $primaryFamCode, $payload);
            $this->sendApiPayload(201, [
                'status' => 'success',
                'message' => 'Commitment created.',
                'commitment' => $created,
            ]);
        } catch (ValidationException $e) {
            $this->sendApiPayload(422, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            error_log('FamilyCommitmentController::store error: ' . $e->getMessage());
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to save commitment right now.']);
        }
    }

    /**
     * Update an existing commitment (owner only).
     * PUT|POST /api/commitments/[*:id]
     */
    public function update(string $id): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $payload = $this->readRequestPayload();
        if (!$this->verifyCsrf($payload)) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
            return;
        }

        try {
            $updated = $this->service->updateCommitment($userId, $id, $payload);
            $this->sendApiPayload(200, [
                'status' => 'success',
                'message' => 'Commitment updated.',
                'commitment' => $updated,
            ]);
        } catch (NotFoundException $e) {
            $this->sendApiPayload(404, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $this->sendApiPayload(422, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to update commitment right now.']);
        }
    }

    /**
     * Delete a commitment (owner only).
     * DELETE /api/commitments/[*:id]
     */
    public function destroy(string $id): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $payload = $this->readRequestPayload();
        if (!$this->verifyCsrf($payload)) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
            return;
        }

        try {
            $this->service->deleteCommitment($userId, $id);
            $this->sendApiPayload(200, ['status' => 'success', 'message' => 'Commitment removed.']);
        } catch (NotFoundException $e) {
            $this->sendApiPayload(404, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to delete commitment right now.']);
        }
    }

    /**
     * Log a Rich Check-in (requires reflection note or rating).
     * POST /api/commitments/[*:id]/checkin
     */
    public function checkin(string $id): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $payload = $this->readRequestPayload();
        if (!$this->verifyCsrf($payload)) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
            return;
        }

        $famCodes = $this->currentFamCodes($userId);

        try {
            $result = $this->service->logCheckin($userId, $famCodes, $id, $payload);
            $this->sendApiPayload(201, [
                'status' => 'success',
                'message' => 'Check-in logged. Well done!',
                'checkin_id' => $result['checkin_id'],
                'commitment' => $result['commitment'],
            ]);
        } catch (NotFoundException $e) {
            $this->sendApiPayload(404, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $this->sendApiPayload(422, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to log check-in right now.']);
        }
    }

    /**
     * Dismiss an exact-time in-app reminder.
     * POST /api/commitments/[*:id]/dismiss-reminder
     */
    public function dismissReminder(string $id): void
    {
        $userId = $this->requireApiUserId();
        if ($userId === '') {
            return;
        }

        $payload = $this->readRequestPayload();
        if (!$this->verifyCsrf($payload)) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => 'Invalid or missing CSRF token.']);
            return;
        }

        $slotKey = trim((string)($payload['slot_key'] ?? ''));
        if ($slotKey === '') {
            $this->sendApiPayload(422, ['status' => 'error', 'message' => 'Missing reminder slot key.']);
            return;
        }

        $famCodes = $this->currentFamCodes($userId);

        try {
            $this->service->dismissReminder($userId, $famCodes, $id, $slotKey);
            $this->sendApiPayload(200, ['status' => 'success', 'message' => 'Reminder dismissed.']);
        } catch (NotFoundException $e) {
            $this->sendApiPayload(404, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (ForbiddenException $e) {
            $this->sendApiPayload(403, ['status' => 'error', 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->sendApiPayload(500, ['status' => 'error', 'message' => 'Unable to dismiss reminder right now.']);
        }
    }

    private function currentUserId(): string
    {
        return isset($_SESSION['id']) && is_scalar($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';
    }

    private function requireApiUserId(): string
    {
        $userId = $this->currentUserId();
        if ($userId === '') {
            $this->sendApiPayload(401, ['status' => 'error', 'message' => 'Unauthorised. Please sign in.']);
            return '';
        }
        return $userId;
    }

    /**
     * @return list<string>
     */
    private function currentFamCodes(string $userId): array
    {
        $codes = [];
        if (isset($_SESSION['famCodes']) && is_array($_SESSION['famCodes'])) {
            foreach ($_SESSION['famCodes'] as $c) {
                $s = trim((string)$c);
                if ($s !== '' && !in_array($s, $codes, true)) {
                    $codes[] = $s;
                }
            }
        }
        $single = trim((string)($_SESSION['famCode'] ?? ''));
        if ($single !== '' && !in_array($single, $codes, true)) {
            $codes[] = $single;
        }

        if ($codes === []) {
            $row = SelectFn::selectOneRow('personal', 'id', $userId);
            $dbCode = is_array($row) ? trim((string)($row['famCode'] ?? '')) : '';
            if ($dbCode !== '') {
                $_SESSION['famCode'] = $dbCode;
                $codes[] = $dbCode;
            }
        }

        return $codes;
    }

    /**
     * @return list<array<string, string>>
     */
    private function fetchFamilyMembers(string $famCode, string $currentUserId): array
    {
        if ($famCode === '') {
            return [];
        }
        $pdo = Db::connect2();
        $stmt = $pdo->prepare(
            'SELECT p.id, p.firstName, p.lastName
             FROM personal p
             WHERE p.famCode = ? AND p.id <> ? AND p.deleted_at IS NULL
             ORDER BY p.firstName ASC, p.lastName ASC'
        );
        $stmt->execute([$famCode, $currentUserId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return array_values(array_map(static function (array $r): array {
            $name = trim(((string)($r['firstName'] ?? '')) . ' ' . ((string)($r['lastName'] ?? '')));
            return [
                'id' => (string)$r['id'],
                'name' => $name !== '' ? $name : 'Family Member',
            ];
        }, $rows));
    }

    /**
     * @return array<string, mixed>
     */
    private function readRequestPayload(): array
    {
        $raw = file_get_contents('php://input');
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function verifyCsrf(array $payload): bool
    {
        $provided = (string)($payload['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '')));
        if ($provided === '') {
            return false;
        }
        $candidates = array_filter([
            (string)($_SESSION['token'] ?? ''),
            (string)($_SESSION['csrf_token'] ?? ''),
        ]);
        foreach ($candidates as $expected) {
            if (hash_equals($expected, $provided)) {
                return true;
            }
        }
        return false;
    }

    private function activeCsrfToken(): string
    {
        $token = (string)($_SESSION['token'] ?? ($_SESSION['csrf_token'] ?? ''));
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['token'] = $token;
            $_SESSION['csrf_token'] = $token;
        }
        return $token;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendApiPayload(int $status, array $data): void
    {
        $message = isset($data['message']) && is_string($data['message']) ? $data['message'] : '';
        if ($status >= 400) {
            \Src\ApiResponse::error($message !== '' ? $message : 'Request failed.', $status, null, $data);
            return;
        }
        \Src\ApiResponse::success($data, $message !== '' ? $message : 'OK', $status);
    }
}

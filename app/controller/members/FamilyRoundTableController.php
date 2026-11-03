<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use Src\Db;
use Src\SelectFn;
use Src\functionality\SignIn;
use Src\Exceptions\ForbiddenException;
use App\services\FamilyRoundTableService;

final class FamilyRoundTableController extends BaseController
{
    private string $userId;
    private string $famCode;

    public function __construct()
    {
        parent::__construct();
        if (empty($_SESSION['id'])) {
            SignIn::verify();
        }

        $this->userId = (string)($_SESSION['id'] ?? '');
        if (empty($this->userId)) {
            throw new ForbiddenException("Authentication required to access Family RoundTable.");
        }

        $famCode = (string)($_SESSION['famCode'] ?? '');
        if (empty($famCode)) {
            $userRow = SelectFn::selectOneRow('personal', 'id', $this->userId);
            $famCode = (string)($userRow['famCode'] ?? '');
            $_SESSION['famCode'] = $famCode;
        }
        $this->famCode = $famCode;
    }

    /**
     * Render the Family RoundTable dashboard view
     */
    public function index(): void
    {
        $userData = SelectFn::selectOneRow('personal', 'id', $this->userId) ?: [];
        $data = $userData;

        // Fetch family members in the same family code
        $pdo = Db::connect2();
        $stmt = $pdo->prepare("SELECT p.id, p.firstName, p.lastName, pp.img AS profilePics FROM personal p LEFT JOIN profilePics pp ON p.id = pp.id WHERE p.famCode = ? ORDER BY p.firstName ASC");
        $stmt->execute([$this->famCode]);
        $familyMembers = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Fetch RoundTable disputes
        $roundTableService = new FamilyRoundTableService();
        $disputes = $roundTableService->listFamilyDisputes($this->famCode);

        $selectedDisputeId = isset($_GET['dispute_id']) ? (string)$_GET['dispute_id'] : '';
        $selectedDispute = null;
        if (!empty($selectedDisputeId)) {
            $selectedDispute = $roundTableService->getDisputeDetails($selectedDisputeId, $this->famCode);
        }

        view('member.familyRoundTable', compact('data', 'userData', 'familyMembers', 'disputes', 'selectedDispute'));
    }

    /**
     * Enforce per-session action rate limiting against automated bots and AI farming agents.
     */
    private function checkActionRateLimit(string $action, int $maxAttempts = 10, int $windowSeconds = 60): bool
    {
        $rateKey = 'rt_rate_' . $action . '_' . $this->userId;
        $now = time();
        $history = isset($_SESSION[$rateKey]) && is_array($_SESSION[$rateKey]) ? $_SESSION[$rateKey] : [];
        $history = array_values(array_filter($history, static fn($t) => is_int($t) && ($now - $t) < $windowSeconds));
        if (count($history) >= $maxAttempts) {
            return false;
        }
        $history[] = $now;
        $_SESSION[$rateKey] = $history;
        return true;
    }

    /**
     * Endpoint: File a Dispute before the Family RoundTable
     */
    public function fileDispute(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            try {
                self::validateCsrf(is_array($json) ? $json : null);
            } catch (\Src\Exceptions\UnauthorisedException) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
                return;
            }

            if (!$this->checkActionRateLimit('file_dispute', 5, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many dispute filings. Please wait a minute and try again.']);
                return;
            }

            $isThirdParty = !empty($json['is_third_party'] ?? $_POST['is_third_party'] ?? false);
            $partyAId = (string)($json['party_a_id'] ?? $_POST['party_a_id'] ?? '');
            $partyBId = (string)($json['party_b_id'] ?? $_POST['party_b_id'] ?? '');
            $witnessIds = (array)($json['witness_ids'] ?? $_POST['witness_ids'] ?? []);
            $title = (string)($json['title'] ?? $_POST['title'] ?? '');
            $category = (string)($json['category'] ?? $_POST['category'] ?? 'general_harmony');
            $grievance = (string)($json['grievance'] ?? $_POST['grievance'] ?? '');

            if (!$isThirdParty && empty($partyAId)) {
                $partyAId = $this->userId;
            }

            $service = new FamilyRoundTableService();
            $result = $service->fileDispute(
                famCode: $this->famCode,
                initiatorId: $this->userId,
                isThirdParty: (bool)$isThirdParty,
                partyAId: $partyAId,
                partyBId: $partyBId,
                witnessIds: $witnessIds,
                title: $title,
                category: $category,
                grievance: $grievance
            );

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::fileDispute] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to file council dispute at this time.']);
        }
    }

    /**
     * Endpoint: Submit testimony / counter-statement in an ongoing council hearing
     */
    public function submitStatement(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            try {
                self::validateCsrf(is_array($json) ? $json : null);
            } catch (\Src\Exceptions\UnauthorisedException) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
                return;
            }

            if (!$this->checkActionRateLimit('submit_statement', 10, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many statements submitted. Please wait a moment.']);
                return;
            }

            $disputeId = (string)($json['dispute_id'] ?? $_POST['dispute_id'] ?? '');
            $statement = (string)($json['statement'] ?? $_POST['statement'] ?? '');

            \releaseSessionLock();
            $service = new FamilyRoundTableService();
            $result = $service->submitStatement($disputeId, $this->userId, $statement, $this->famCode);

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::submitStatement] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to submit statement at this time.']);
        }
    }

    /**
     * Endpoint: Convene council and seal the RoundTable Accord
     */
    public function adjudicate(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            try {
                self::validateCsrf(is_array($json) ? $json : null);
            } catch (\Src\Exceptions\UnauthorisedException) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
                return;
            }

            if (!$this->checkActionRateLimit('adjudicate', 6, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many adjudication requests. Please wait a moment.']);
                return;
            }

            $disputeId = (string)($json['dispute_id'] ?? $_POST['dispute_id'] ?? '');

            \releaseSessionLock();
            $service = new FamilyRoundTableService();
            $result = $service->adjudicate($disputeId, $this->famCode);

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::adjudicate] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to adjudicate dispute at this time.']);
        }
    }

    /**
     * Endpoint: Fetch details of a specific dispute docket
     *
     * @param string $disputeId
     */
    public function getDisputeDetails(string $disputeId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $service = new FamilyRoundTableService();
            $details = $service->getDisputeDetails($disputeId, $this->famCode);

            if (!$details) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Dispute docket not found.']);
                return;
            }

            echo json_encode(['success' => true, 'dispute' => $details]);
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::getDisputeDetails] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to load dispute details.']);
        }
    }

    /**
     * Endpoint: Arbitrate fast 2-minute instant referee
     */
    public function arbitrateInstant(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            try {
                self::validateCsrf(is_array($json) ? $json : null);
            } catch (\Src\Exceptions\UnauthorisedException) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
                return;
            }

            if (!$this->checkActionRateLimit('arbitrate_instant', 8, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many arbitration requests. Please wait a minute and try again.']);
                return;
            }

            $partyA = (string)($json['party_a_name'] ?? $_POST['party_a_name'] ?? '');
            $claimA = (string)($json['party_a_claim'] ?? $_POST['party_a_claim'] ?? '');
            $partyB = (string)($json['party_b_name'] ?? $_POST['party_b_name'] ?? '');
            $claimB = (string)($json['party_b_claim'] ?? $_POST['party_b_claim'] ?? '');
            $context = (string)($json['context'] ?? $_POST['context'] ?? 'household_harmony');
            $isThirdParty = !empty($json['is_third_party'] ?? $_POST['is_third_party'] ?? false);
            $initiatorName = (string)($json['initiator_name'] ?? $_POST['initiator_name'] ?? null);

            \releaseSessionLock();
            $service = new FamilyRoundTableService();
            $result = $service->arbitrateInstant(
                partyAName: $partyA,
                partyAClaim: $claimA,
                partyBName: $partyB,
                partyBClaim: $claimB,
                context: $context,
                isThirdParty: (bool)$isThirdParty,
                initiatorName: $initiatorName
            );

            if ($result['success']) {
                $result['verdict'] = $result['finding_of_fact'] . "\n\n" . $result['reconciliatory_bridge'];
                $result['compromise_action_plan'] = $result['restorative_action_plan'];
                $result['penalty_or_chore'] = 'Restorative Family Act: Share a warm embrace, acknowledge each other, and complete the action plan together.';
                $result['closing_quip'] = $result['yommy_wisdom'];
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::arbitrateInstant] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to complete instant arbitration.']);
        }
    }

    /**
     * Endpoint: Record completed chore points and claim household rewards
     */
    public function recordChorePoints(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

            try {
                self::validateCsrf(is_array($json) ? $json : null);
            } catch (\Src\Exceptions\UnauthorisedException) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.']);
                return;
            }

            if (!$this->checkActionRateLimit('chore_points', 10, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many reward claims in a short period. Please wait a minute.']);
                return;
            }

            $points = (int)($json['points'] ?? $_POST['points'] ?? 50);
            // Defensive bounds check: 10 to 500 points per batch to prevent tampering
            $points = max(10, min(500, $points));
            $choreType = htmlspecialchars(mb_substr(trim(strip_tags((string)($json['chore_type'] ?? $_POST['chore_type'] ?? 'Household Quest'))), 0, 100), ENT_QUOTES, 'UTF-8');
            $rewardTitle = htmlspecialchars(mb_substr(trim(strip_tags((string)($json['reward_title'] ?? $_POST['reward_title'] ?? 'Family Activity Treat'))), 0, 200), ENT_QUOTES, 'UTF-8');

            $pdo = Db::connect2();
            $pdo->exec("CREATE TABLE IF NOT EXISTS `family_chore_points` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` VARCHAR(64) NOT NULL,
                `fam_code` VARCHAR(64) NOT NULL,
                `points` INT NOT NULL DEFAULT 0,
                `chore_type` VARCHAR(100) NOT NULL,
                `reward_title` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user_fam` (`user_id`, `fam_code`),
                INDEX `idx_fam` (`fam_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $stmt = $pdo->prepare("INSERT INTO family_chore_points (user_id, fam_code, points, chore_type, reward_title) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$this->userId, $this->famCode, $points, $choreType, $rewardTitle]);

            $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(points), 0) AS total_points FROM family_chore_points WHERE fam_code = ?");
            $sumStmt->execute([$this->famCode]);
            $total = (int)($sumStmt->fetchColumn() ?: 0);

            // Real-time broadcast strictly scoped to private-family-<famCode> channel
            if (class_exists('\\App\\classes\\Pusher')) {
                \App\classes\Pusher::broadcastToFamily($this->famCode, 'chore.points_awarded', [
                    'user_id' => $this->userId,
                    'points_added' => $points,
                    'total_points' => $total,
                    'chore_type' => $choreType,
                    'reward_title' => $rewardTitle,
                ]);
            }

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode([
                'success' => true,
                'points_added' => $points,
                'total_points' => $total,
                'message' => "Successfully recorded {$points} points towards {$rewardTitle}!"
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        } catch (\Throwable $e) {
            error_log('[FamilyRoundTableController::recordChorePoints] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to record chore points at this time.']);
        }
    }
}

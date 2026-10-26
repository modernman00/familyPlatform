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
     * Endpoint: File a Dispute before the Family RoundTable
     */
    public function fileDispute(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $raw = file_get_contents('php://input');
            $json = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
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

            $disputeId = (string)($json['dispute_id'] ?? $_POST['dispute_id'] ?? '');

            \releaseSessionLock();
            $service = new FamilyRoundTableService();
            $result = $service->adjudicate($disputeId, $this->famCode);

            if (!$result['success']) {
                http_response_code(422);
            }

            echo json_encode($result); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
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

            $partyA = (string)($json['party_a_name'] ?? $_POST['party_a_name'] ?? '');
            $claimA = (string)($json['party_a_claim'] ?? $_POST['party_a_claim'] ?? '');
            $partyB = (string)($json['party_b_name'] ?? $_POST['party_b_name'] ?? '');
            $claimB = (string)($json['party_b_claim'] ?? $_POST['party_b_claim'] ?? '');
            $context = (string)($json['context'] ?? $_POST['context'] ?? 'household_harmony');
            $isThirdParty = !empty($json['is_third_party'] ?? $_POST['is_third_party'] ?? false);
            $initiatorName = (string)($json['initiator_name'] ?? $_POST['initiator_name'] ?? null);

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
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

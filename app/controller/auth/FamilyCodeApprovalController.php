<?php

namespace App\controller\auth;

use App\service\FamilyCodeApprovalService;
use App\service\NotificationService;
use PDO;

class FamilyCodeApprovalController
{
    private FamilyCodeApprovalService $approvalService;
    private NotificationService $notificationService;
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \Src\Db::connect2() ?? (new \Src\Db())->connect() ?? new PDO(
            "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['DB_NAME'] ?? 'family') . ";charset=utf8mb4",
            $_ENV['DB_USERNAME'] ?? 'root',
            $_ENV['DB_PASSWORD'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $this->approvalService = new FamilyCodeApprovalService($this->pdo);
        $this->notificationService = new NotificationService($this->pdo);
    }

    /**
     * API: Check if family code exists
     * POST /api/family-code/check
     */
    public function checkFamilyCode(): void
    {
        header('Content-Type: application/json');

        $rawInput = file_get_contents('php://input');
        $input = $rawInput ? json_decode($rawInput, true) : [];
        $familyCode = $input['family_code'] ?? $_POST['family_code'] ?? '';

        if (!$familyCode) {
            http_response_code(400);
            echo json_encode(['error' => 'Family code is required', 'exists' => false]);
            return;
        }

        try {
            $exists = $this->approvalService->familyCodeExists(trim((string)$familyCode));

            if ($exists) {
                $tempCode = $this->approvalService->generateTemporaryCode();
                echo json_encode([
                    'exists' => true,
                    'temporary_code' => $tempCode
                ]);
            } else {
                echo json_encode(['exists' => false]);
            }
        } catch (\Throwable $e) {
            error_log("checkFamilyCode error: " . $e->getMessage());
            echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API: Verify inviter details
     * POST /api/family-code/verify-inviter
     */
    public function verifyInviter(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $familyCode = $input['family_code'] ?? '';
        $inviterFirstName = $input['inviter_first_name'] ?? '';
        $inviterLastName = $input['inviter_last_name'] ?? '';
        $inviterContact = $input['inviter_email_or_mobile'] ?? '';

        // Validate inputs
        if (!$familyCode || !$inviterFirstName || !$inviterLastName || !$inviterContact) {
            http_response_code(400);
            echo json_encode(['error' => 'All fields are required', 'verified' => false]);
            return;
        }

        // Find matching inviter in the family
        $inviter = $this->approvalService->findMatchingInviter(
            trim($familyCode),
            trim($inviterFirstName),
            trim($inviterLastName),
            trim($inviterContact)
        );

        if (!$inviter) {
            http_response_code(422);
            echo json_encode([
                'verified' => false,
                'message' => 'Could not find a matching family member with the provided information. Please check and try again.'
            ]);
            return;
        }

        echo json_encode([
            'verified' => true,
            'message' => 'Inviter verified successfully',
            'inviter_id' => $inviter['id']
        ]);
    }

    /**
     * Complete registration with family code approval
     * POST /api/family-code/complete-registration
     */
    public function completeRegistration(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $userId = $input['user_id'] ?? '';
        $familyCode = $input['family_code'] ?? '';
        $joiningViaInvitation = $input['joining_via_invitation'] ?? false;

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'User ID is required']);
            return;
        }

        // If joining with existing family code
        if ($joiningViaInvitation && $familyCode) {
            $inviterFirstName = $input['inviter_first_name'] ?? '';
            $inviterLastName = $input['inviter_last_name'] ?? '';
            $inviterContact = $input['inviter_email_or_mobile'] ?? '';

            // Create approval request
            $approvalData = $this->approvalService->createApprovalRequest(
                $userId,
                $familyCode,
                $inviterFirstName,
                $inviterLastName,
                $inviterContact
            );

            // Find the inviter to send notification
            $inviter = $this->approvalService->findMatchingInviter(
                $familyCode,
                $inviterFirstName,
                $inviterLastName,
                $inviterContact
            );

            if ($inviter) {
                // Get new user's info for notification
                $newUserInfo = $this->getUserInfo($userId);

                // Send approval notification with signed token
                $this->notificationService->sendFamilyApprovalNotification(
                    $inviter['id'],
                    $newUserInfo,
                    $approvalData['request_id'],
                    $familyCode,
                    $approvalData['approval_token']
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! Your family member has been notified to approve your request.',
                    'temporary_code' => $approvalData['temporary_code'],
                    'approval_request_id' => $approvalData['request_id']
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration successful! An approval request has been created.',
                    'temporary_code' => $approvalData['temporary_code']
                ]);
            }
        } else {
            // Registering without family code (creating new family)
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful!',
                'family_code_generated' => true
            ]);
        }
    }

    /**
     * Approve a family code registration request
     * POST /api/family-code/approve/{requestId}
     * Required query param: ?token={approval_token}
     */
    public function approveRequest(int $requestId): void
    {
        header('Content-Type: application/json');

        // Verify approval token
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        if (!$token || !$this->approvalService->verifyApprovalToken($requestId, $token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or missing approval token']);
            return;
        }

        $request = $this->approvalService->getApprovalRequest($requestId);

        if (!$request) {
            http_response_code(404);
            echo json_encode(['error' => 'Approval request not found']);
            return;
        }

        if ($request['status'] !== 'pending') {
            http_response_code(422);
            echo json_encode(['error' => 'This request has already been ' . $request['status']]);
            return;
        }

        // Approve the request
        $approved = $this->approvalService->approveRequest($requestId);

        if ($approved) {
            // Link user to family code
            $this->approvalService->linkUserToFamily($request['id'], $request['family_code']);

            // Send confirmation notification to new user
            $this->notificationService->sendApprovalConfirmationNotification(
                $request['id'],
                $request['family_code']
            );

            echo json_encode([
                'success' => true,
                'message' => 'Request approved! The user has been linked to your family.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to approve request']);
        }
    }

    /**
     * Deny a family code registration request
     * POST /api/family-code/deny/{requestId}
     * Required query param: ?token={approval_token}
     */
    public function denyRequest(int $requestId): void
    {
        header('Content-Type: application/json');

        // Verify approval token
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        if (!$token || !$this->approvalService->verifyApprovalToken($requestId, $token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or missing approval token']);
            return;
        }

        $request = $this->approvalService->getApprovalRequest($requestId);

        if (!$request) {
            http_response_code(404);
            echo json_encode(['error' => 'Approval request not found']);
            return;
        }

        if ($request['status'] !== 'pending') {
            http_response_code(422);
            echo json_encode(['error' => 'This request has already been ' . $request['status']]);
            return;
        }

        $denied = $this->approvalService->denyRequest($requestId);

        if ($denied) {
            echo json_encode([
                'success' => true,
                'message' => 'Request denied.'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to deny request']);
        }
    }

    /**
     * Get user info by ID
     */
    private function getUserInfo(string $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id, a.email, p.firstName, p.lastName, c.mobile
             FROM account a
             LEFT JOIN contact c ON c.id = a.id
             LEFT JOIN personal p ON p.id = a.id
             WHERE a.id = ?'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

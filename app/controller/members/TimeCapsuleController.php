<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyTimeCapsuleService;
use Src\{CheckToken, CorsHandler, Limiter};

/**
 * TimeCapsuleController
 *
 * REST API controller for the Living Heritage & Digital Family Time Capsule Vault.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class TimeCapsuleController extends BaseController
{
    /**
     * GET /api/capsules
     *
     * List all family time capsules with zero-trust server-side content redaction for locked items.
     */
    public function listCapsules(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if (empty($userId)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $requestedFamilyCode = isset($_GET['family_code']) && is_string($_GET['family_code'])
                ? trim($_GET['family_code'])
                : '';

            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = $requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode;

            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            Limiter::limit($userId . ':list_capsules');

            \releaseSessionLock();

            $capsules = FamilyTimeCapsuleService::getFamilyCapsules($familyCode, $userId);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'data'   => $capsules,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to load time capsules: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/capsules/create
     *
     * Create and seal a new time capsule until a future date or milestone.
     */
    public function createCapsule(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if (empty($userId)) {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = is_string($rawInput) ? json_decode($rawInput, true) : null;
            if (!is_array($input)) {
                $input = $_POST;
            }

            self::validateCsrf($input);
            Limiter::limit($userId . ':create_capsule');

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $recipientName = isset($input['recipient_name']) && is_string($input['recipient_name'])
                ? trim($input['recipient_name'])
                : '';
            $title = isset($input['title']) && is_string($input['title'])
                ? trim($input['title'])
                : '';
            $message = isset($input['message']) && is_string($input['message'])
                ? trim($input['message'])
                : '';
            $unlockDate = isset($input['unlock_date']) && is_string($input['unlock_date'])
                ? trim($input['unlock_date'])
                : '';

            if (empty($recipientName) || empty($title) || empty($message) || empty($unlockDate)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'All fields (recipient, title, message, and unlock date) are required.']);
                return;
            }

            $today = date('Y-m-d');
            if ($unlockDate <= $today) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'A time capsule unlock date must be set to a future date.']);
                return;
            }

            $capsuleId = FamilyTimeCapsuleService::createCapsule($familyCode, $userId, [
                'recipient_name'    => $recipientName,
                'recipient_user_id' => isset($input['recipient_user_id']) && is_string($input['recipient_user_id']) ? trim($input['recipient_user_id']) : null,
                'title'             => $title,
                'message'           => $message,
                'media_url'         => isset($input['media_url']) && is_string($input['media_url']) ? trim($input['media_url']) : null,
                'media_type'        => isset($input['media_type']) && is_string($input['media_type']) ? trim($input['media_type']) : 'none',
                'unlock_type'       => isset($input['unlock_type']) && is_string($input['unlock_type']) ? trim($input['unlock_type']) : 'date',
                'unlock_date'       => $unlockDate,
            ]);

            http_response_code(201);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status'     => 'success',
                'capsule_id' => $capsuleId,
                'message'    => 'Time capsule successfully sealed until ' . date('d M Y', (int)strtotime($unlockDate)) . '!',
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to seal time capsule: ' . $e->getMessage(),
            ]);
        }
    }
}

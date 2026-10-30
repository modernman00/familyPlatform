<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\CloudflareStreamService;
use Src\functionality\SignIn;

class StreamController
{
    private CloudflareStreamService $streamService;

    public function __construct()
    {
        $this->streamService = new CloudflareStreamService();
    }

    /**
     * Request a Direct Upload URL for video creation
     */
    public function getDirectUploadUrl(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                $verifyJWT = SignIn::verify();
                if (empty($verifyJWT['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    return;
                }
                $userId = (string)$verifyJWT['id'];
            }

            // Check if member has Premium status
            $isPremium = false;
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare("SELECT type, is_premium FROM account WHERE id = ?");
                $stmt->execute([$userId]);
                $acc = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($acc && (!empty($acc['is_premium']) || ($acc['type'] ?? '') === '9090@' || ($acc['type'] ?? '') === 'premium')) {
                    $isPremium = true;
                }
            } catch (\Throwable $err) {
                error_log('[StreamController] DB error checking premium status: ' . $err->getMessage());
            }

            $rawInput = file_get_contents("php://input");
            $data = json_decode($rawInput !== false ? $rawInput : '', true);
            $maxDuration = max(1, min((int) ($data['maxDuration'] ?? 30), 30));
            $requestedExpiry = isset($data['expirySeconds']) ? (int)$data['expirySeconds'] : 2592000;

            // Enforce Premium Tier: 1 Year (31536000s) and Permanent (0s) are restricted to Premium members
            if (!$isPremium && ($requestedExpiry === 0 || $requestedExpiry > 2592000)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => '1 Year and Permanent video retention is available exclusively for Premium members. Please upgrade your plan or select 30 days.'
                ]);
                return;
            }

            $result = $this->streamService->createDirectUploadUrl($maxDuration, $requestedExpiry);

            if (!empty($result['success'])) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                // Seamless fallback to Sovereign Local Video Auto-Trim & Upload endpoint
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'fallbackLocal' => true,
                    'uploadUrl' => '/api/video/upload-file',
                    'providerNote' => $result['error'] ?? 'Using sovereign local video stream.'
                ]);
            }
        } catch (\Throwable $e) {
            error_log('[StreamController] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error processing video upload request.']);
        }
    }

    /**
     * Sovereign Video Upload & 30-Second Auto-Trim Endpoint (POST /api/video/upload-file)
     */
    public function uploadAndTrimVideo(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                $verifyJWT = SignIn::verify();
                if (!is_array($verifyJWT) || empty($verifyJWT['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    return;
                }
                $userId = (string)$verifyJWT['id'];
            }

            // Enforce CSRF token validation
            \Src\CheckToken::tokenCheck();

            // Check if member has Premium status for retention rules
            $isPremium = false;
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare("SELECT type, is_premium FROM account WHERE id = ?");
                $stmt->execute([$userId]);
                $acc = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($acc && (!empty($acc['is_premium']) || ($acc['type'] ?? '') === '9090@' || ($acc['type'] ?? '') === 'premium')) {
                    $isPremium = true;
                }
            } catch (\Throwable $err) {
                error_log('[StreamController::uploadAndTrimVideo] DB error checking premium status: ' . $err->getMessage());
            }

            $requestedExpiry = isset($_POST['expiry_seconds']) ? (int)$_POST['expiry_seconds'] : 2592000;
            if (!$isPremium && ($requestedExpiry === 0 || $requestedExpiry > 2592000)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => '1 Year and Permanent video retention is available exclusively for Premium members. Please upgrade your plan or select 30 days.'
                ]);
                return;
            }

            $file = $_FILES['video_file'] ?? ($_FILES['file'] ?? null);
            if (!is_array($file)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please attach a valid video file (MP4, WebM, or MOV).']);
                return;
            }

            $trimStart = $_POST['trim_start'] ?? 0.0;
            $trimEnd = $_POST['trim_end'] ?? 30.0;
            $clientDuration = $_POST['client_duration'] ?? null;

            $result = \App\services\VideoAutoTrimService::processUploadAndTrim(
                file: $file,
                userId: $userId,
                trimStart: is_scalar($trimStart) ? (float)$trimStart : 0.0,
                trimEnd: is_scalar($trimEnd) ? (float)$trimEnd : 30.0,
                clientDuration: is_scalar($clientDuration) ? (float)$clientDuration : null
            );

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        } catch (\Src\Exceptions\ValidationException $ve) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $ve->getMessage()]);
        } catch (\Src\Exceptions\ForbiddenException $fe) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => $fe->getMessage()]);
        } catch (\Throwable $e) {
            error_log('[StreamController::uploadAndTrimVideo] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to process and auto-trim video clip.']);
        }
    }
}

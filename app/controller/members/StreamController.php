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
            $verifyJWT = SignIn::verify();
            if (empty($verifyJWT['id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $userId = (string)$verifyJWT['id'];

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
                http_response_code(400);
                echo json_encode($result);
            }
        } catch (\Throwable $e) {
            error_log('[StreamController] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error processing video upload request.']);
        }
    }
}

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
            if (!\is_array($verifyJWT) || empty($verifyJWT['id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Authentication required']);
                return;
            }

            $rawInput = file_get_contents("php://input");
            $data = json_decode($rawInput !== false ? $rawInput : '', true);
            $maxDuration = max(1, min((int) ($data['maxDuration'] ?? 30), 30));

            $result = $this->streamService->createDirectUploadUrl($maxDuration);

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

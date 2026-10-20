<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\InviteTokenService;
use App\services\LineageQuestService;
use Src\{CheckToken, CorsHandler, Db, Limiter};
use Src\Exceptions\BadRequestException;
use PDO;

/**
 * LineageQuestController
 *
 * REST API controller for gamified "Missing Link" lineage quests and WhatsApp sharing.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class LineageQuestController extends BaseController
{
    /**
     * GET /api/lineage/quests
     *
     * Returns tree completeness metrics and active WhatsApp quests.
     */
    public function getQuests(): void
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

            // Rate-limit fetching to 60 requests per minute
            Limiter::limit($userId . ':lineage_quests');

            \releaseSessionLock();

            $data = LineageQuestService::getFamilyQuests($familyCode, $userId);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'data'   => $data,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to calculate lineage quests: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/lineage/quest-token
     *
     * Generates a targeted invite token for a quest with node validation.
     * Request JSON or form POST:
     *   { "family_code": "...", "node_id": 123, "quest_type": "..." }
     */
    public function generateQuestToken(): void
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
            Limiter::limit($userId . ':quest_token');

            $familyCode = isset($input['family_code']) && is_string($input['family_code'])
                ? trim($input['family_code'])
                : (string)($_SESSION['famCode'] ?? '');

            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $nodeId = isset($input['node_id']) && is_numeric($input['node_id']) ? (int)$input['node_id'] : null;
            $type = isset($input['type']) && is_string($input['type']) ? trim($input['type']) : 'organogram';

            $meta = [
                'invited_by' => $userId,
            ];

            // If targeting a specific node, verify node ownership within this family
            if ($nodeId !== null && $nodeId > 0) {
                $db = Db::connect2();
                $stmt = $db->prepare('SELECT id, first_name, last_name FROM family_nodes WHERE id = ? AND family_code = ? LIMIT 1');
                $stmt->execute([$nodeId, $familyCode]);
                /** @var array<string, mixed>|false $node */
                $node = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$node) {
                    http_response_code(404);
                    echo json_encode(['status' => 'error', 'message' => 'Target tree node not found in this family.']);
                    return;
                }

                $meta['node_id'] = $nodeId;
                $meta['first_name'] = (string)($node['first_name'] ?? '');
                $meta['last_name'] = (string)($node['last_name'] ?? '');
            }

            $token = InviteTokenService::create($familyCode, $meta, $type);
            $inviteUrl = InviteTokenService::getRegisterUrl($token);

            $targetName = !empty($meta['first_name']) ? trim($meta['first_name'] . ' ' . $meta['last_name']) : 'family member';
            $waMessage = "👋 Hi! We're building our family tree on FamilyPlatform. Tap here to view where {$targetName} sits and join our family circle: " . $inviteUrl;
            $waUrl = 'https://api.whatsapp.com/send?text=' . rawurlencode($waMessage);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status'       => 'success',
                'token'        => $token,
                'invite_url'   => $inviteUrl,
                'whatsapp_url' => $waUrl,
                'message'      => $waMessage,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to generate quest token: ' . $e->getMessage(),
            ]);
        }
    }
}

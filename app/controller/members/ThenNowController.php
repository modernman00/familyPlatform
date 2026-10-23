<?php
declare(strict_types=1);

namespace App\controller\members;

use App\model\PostComparison;

/**
 * GET /api/members/{id}/then-now — a member's Then & Now timeline (RFC-0003).
 */
final class ThenNowController
{
    public function memberTimeline(string $userId = ''): void
    {
        header('Content-Type: application/json');
        try {
            $viewerId = isset($_SESSION['id']) ? (string)$_SESSION['id'] : '';
            if ($viewerId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
                return;
            }

            $memberId = trim($userId);
            if ($memberId === '' || !preg_match('/^[A-Za-z0-9_\-]{1,64}$/', $memberId)) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Invalid member']);
                return;
            }

            $primary = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
            $famCodes = (array)($_SESSION['famCodes'] ?? ($primary !== '' ? [$primary] : []));
            \releaseSessionLock();

            $items = PostComparison::forMember($memberId, $viewerId, $famCodes);
            error_log((string)json_encode(['event' => 'then_now.timeline_viewed', 'user_id' => $viewerId, 'member_id' => $memberId, 'count' => count($items)]));

            http_response_code(200);
            echo json_encode(['status' => 'success', 'data' => ['items' => $items]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            error_log('[ThenNowController::memberTimeline] ' . $th->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not load Then & Now']);
        }
    }
}

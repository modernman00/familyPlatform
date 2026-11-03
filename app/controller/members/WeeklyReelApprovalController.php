<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\WeeklyActivityVideoCuratorService;
use Src\functionality\SignIn;
use Src\Exceptions\UnauthorisedException;
use Throwable;

final class WeeklyReelApprovalController extends BaseController
{
    /**
     * API: Get the active pending weekly reel for the logged-in member.
     * GET /api/weekly-reel/pending
     */
    public function getPending(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorised'], JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $userId = (string)$_SESSION['id'];
            $famCode = (string)($_SESSION['famCode'] ?? '');

            $reel = WeeklyActivityVideoCuratorService::getPendingReel($userId, $famCode);

            if (!$reel) {
                echo json_encode(['success' => true, 'has_pending' => false, 'reel' => null], JSON_UNESCAPED_SLASHES);
                return;
            }

            echo json_encode([
                'success' => true,
                'has_pending' => true,
                'reel' => $reel
            ], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {
            error_log('[WeeklyReelApprovalController::getPending] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to load weekly reel draft.'], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Approve the weekly reel draft and post it to the family feed.
     * POST /api/weekly-reel/approve
     */
    public function approve(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorised'], JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $input = json_decode((string)file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            // CSRF Security Gate
            try {
                self::validateCsrf($input);
            } catch (UnauthorisedException $csrfEx) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.'], JSON_UNESCAPED_SLASHES);
                return;
            }

            $userId = (string)$_SESSION['id'];
            $famCode = (string)($_SESSION['famCode'] ?? '');
            $userName = trim(strip_tags((string)($_SESSION['fullName'] ?? (($_SESSION['fName'] ?? $_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lName'] ?? $_SESSION['lastName'] ?? '')))));
            if ($userName === '') {
                $userName = 'Family Member';
            }
            $userName = mb_substr($userName, 0, 120);
            $profileImg = isset($_SESSION['profile_pix']) ? (string)$_SESSION['profile_pix'] : null;

            $reelId = (int)($input['reel_id'] ?? 0);
            if ($reelId <= 0) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Invalid weekly reel identifier.'], JSON_UNESCAPED_SLASHES);
                return;
            }

            $result = WeeklyActivityVideoCuratorService::approveAndPostReel(
                $reelId,
                $userId,
                $famCode,
                $userName,
                $profileImg
            );

            if (empty($result['success'])) {
                http_response_code(403);
            }

            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request

        } catch (Throwable $e) {
            error_log('[WeeklyReelApprovalController::approve] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to approve weekly reel.'], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: Dismiss the weekly reel without posting.
     * POST /api/weekly-reel/dismiss
     */
    public function dismiss(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorised'], JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $input = json_decode((string)file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            try {
                self::validateCsrf($input);
            } catch (UnauthorisedException $csrfEx) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.'], JSON_UNESCAPED_SLASHES);
                return;
            }

            $userId = (string)$_SESSION['id'];
            $famCode = (string)($_SESSION['famCode'] ?? '');
            $reelId = (int)($input['reel_id'] ?? 0);

            if ($reelId <= 0) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Invalid weekly reel identifier.'], JSON_UNESCAPED_SLASHES);
                return;
            }

            $result = WeeklyActivityVideoCuratorService::dismissReel($reelId, $userId, $famCode);
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // nosemgrep: php.lang.security.injection.echoed-request.echoed-request

        } catch (Throwable $e) {
            error_log('[WeeklyReelApprovalController::dismiss] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to dismiss weekly reel.'], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * API: On-demand generation/curation of the weekly activity reel.
     * POST /api/weekly-reel/generate
     */
    public function generate(): void
    {
        header('Content-Type: application/json');

        if (empty($_SESSION['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorised'], JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            $input = json_decode((string)file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            try {
                self::validateCsrf($input);
            } catch (UnauthorisedException $csrfEx) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token.'], JSON_UNESCAPED_SLASHES);
                return;
            }

            $userId = (string)$_SESSION['id'];
            $famCode = (string)($_SESSION['famCode'] ?? '');

            // Rate-limit on-demand reel generation (max 5 requests per 60 seconds per user)
            $rateKey = 'weekly_reel_gen_rate_' . $userId;
            $now = time();
            $history = isset($_SESSION[$rateKey]) && is_array($_SESSION[$rateKey]) ? $_SESSION[$rateKey] : [];
            $history = array_values(array_filter($history, static fn($t) => is_int($t) && ($now - $t) < 60));
            if (count($history) >= 5) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many reel generation requests. Please wait a moment and try again.'], JSON_UNESCAPED_SLASHES);
                return;
            }
            $history[] = $now;
            $_SESSION[$rateKey] = $history;

            $reel = WeeklyActivityVideoCuratorService::curateWeeklyActivity($userId, $famCode, true);

            echo json_encode([
                'success' => true,
                'message' => 'Weekly reel generated successfully.',
                'reel' => $reel
            ], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {
            error_log('[WeeklyReelApprovalController::generate] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to generate weekly reel.'], JSON_UNESCAPED_SLASHES);
        }
    }
}

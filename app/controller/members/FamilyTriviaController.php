<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\FamilyTriviaService;
use Src\{CheckToken, CorsHandler, Db, Limiter};
use PDO;

/**
 * FamilyTriviaController
 *
 * REST API controller for Sunday Family Trivia & "Guess Who" community rituals.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class FamilyTriviaController extends BaseController
{
    /**
     * GET /api/trivia/current
     *
     * Returns the active weekly trivia for the caller's family.
     * Auto-generates one if the current week has no published trivia challenge.
     */
    public function getCurrentTrivia(): void
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

            Limiter::limit($userId . ':get_trivia');

            \releaseSessionLock();

            // Auto-generate if not currently active
            if (!FamilyTriviaService::hasActiveWeeklyTrivia($familyCode)) {
                FamilyTriviaService::generateWeeklyHeritageTrivia($familyCode, $userId);
            }

            // Fetch the active trivia post
            $db = Db::connect2();
            $stmt = $db->prepare(
                "SELECT p.post_no, p.fullName, p.postMessage, p.date_created, pp.poll_id, pp.question
                 FROM post p
                 JOIN post_polls pp ON pp.post_no = p.post_no
                 WHERE p.postFamCode = ?
                   AND p.postMessage LIKE '%Weekly Family Trivia%'
                   AND p.date_created >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY p.post_no DESC
                 LIMIT 1"
            );
            $stmt->execute([$familyCode]);
            /** @var array<string, mixed>|false $triviaPost */
            $triviaPost = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$triviaPost) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'data'   => null,
                ]);
                return;
            }

            // Fetch poll options
            $pollId = (int)$triviaPost['poll_id'];
            $optStmt = $db->prepare('SELECT option_id, option_text FROM post_poll_options WHERE poll_id = ?');
            $optStmt->execute([$pollId]);
            /** @var array<int, array<string, mixed>> $options */
            $options = $optStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'post_no'      => (int)$triviaPost['post_no'],
                    'poll_id'      => $pollId,
                    'question'     => (string)$triviaPost['question'],
                    'options'      => $options,
                    'date_created' => (string)$triviaPost['date_created'],
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to retrieve family trivia: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/trivia/auto-generate
     *
     * Explicit trigger for Sunday weekly trivia generation.
     */
    public function autoGenerate(): void
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

            CheckToken::tokenCheck();
            Limiter::limit($userId . ':auto_trivia');

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $result = FamilyTriviaService::generateWeeklyHeritageTrivia($familyCode, $userId);

            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'data'   => $result,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to generate weekly trivia: ' . $e->getMessage(),
            ]);
        }
    }
}

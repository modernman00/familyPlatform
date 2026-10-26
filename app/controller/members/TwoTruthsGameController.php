<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\TwoTruthsGameService;
use Src\{CheckToken, CorsHandler, Limiter};

/**
 * TwoTruthsGameController
 *
 * REST API controller for "Two Truths & a Lie: Heritage Edition" game show.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class TwoTruthsGameController extends BaseController
{
    /**
     * GET /api/games/two-truths/active
     *
     * Returns the active or latest revealed game for the caller's family.
     */
    public function getActive(): void
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

            Limiter::limit($userId . ':two_truths_get');
            \releaseSessionLock();

            $userName = trim((string)(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')));

            // Fetch active game or seed starter game if none exists
            $game = TwoTruthsGameService::ensureStarterGameIfEmpty($familyCode, $userId, $userName);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'data'   => $game,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to load game round: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/games/two-truths/vote
     *
     * Casts a guess for which statement is the lie.
     */
    public function vote(): void
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
            Limiter::limit($userId . ':two_truths_vote');

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            /** @var array<string, mixed> $input */
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $gameId = trim((string)($input['game_id'] ?? ''));
            $guess = (int)($input['guess'] ?? 0);

            if ($gameId === '' || !in_array($guess, [1, 2, 3], true)) {
                http_response_code(422);
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Invalid vote payload. Must select statement 1, 2, or 3.',
                ]);
                return;
            }

            $userName = trim((string)(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')));

            $result = TwoTruthsGameService::castVote(
                gameId: $gameId,
                famCode: $familyCode,
                userId: $userId,
                userName: $userName,
                guessedLieIndex: $guess
            );

            $httpCode = ($result['status'] === 'success') ? 200 : 400;
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to record vote: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/games/two-truths/create
     *
     * Creates a new round authored by a family member.
     */
    public function create(): void
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
            Limiter::limit($userId . ':two_truths_create');

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            /** @var array<string, mixed> $input */
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $userName = trim((string)(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')));

            $result = TwoTruthsGameService::createGame(
                famCode: $familyCode,
                authorId: $userId,
                authorName: $userName ?: 'Family Member',
                data: $input
            );

            $httpCode = ($result['status'] === 'success') ? 201 : 422;
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to create game challenge: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /api/games/two-truths/reveal
     *
     * Unmasks the lie and archives memories to the Family Journal.
     */
    public function reveal(): void
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
            Limiter::limit($userId . ':two_truths_reveal');

            $familyCode = (string)($_SESSION['famCode'] ?? '');
            if (empty($familyCode) || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            /** @var array<string, mixed> $input */
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $gameId = trim((string)($input['game_id'] ?? ''));

            if ($gameId === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Missing game ID.']);
                return;
            }

            $result = TwoTruthsGameService::revealGame($gameId, $familyCode);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to reveal game: ' . $e->getMessage(),
            ]);
        }
    }
}

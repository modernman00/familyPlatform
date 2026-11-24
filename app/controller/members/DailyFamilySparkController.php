<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\DailyFamilySparkService;
use Src\CorsHandler;
use Src\Db;
use Src\Limiter;

/**
 * DailyFamilySparkController
 *
 * REST API controller for the Unified Daily Family Spark engine.
 *
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class DailyFamilySparkController extends BaseController
{
    /**
     * GET /api/sparks/active
     *
     * Returns the active Daily Family Spark and aggregated replies for the caller's family.
     */
    public function getActive(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            $requestedFamilyCode = isset($_GET['family_code']) && is_string($_GET['family_code'])
                ? trim($_GET['family_code'])
                : '';

            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = ltrim(strtoupper($requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode), '#');

            if ($familyCode === '' || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            Limiter::limit($userId . ':spark_get');
            \releaseSessionLock();

            $spark = DailyFamilySparkService::getActiveSpark($familyCode, $userId);

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode([
                'status' => 'success',
                'data' => $spark,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\HttpException $e) {
            http_response_code($e->getStatusCode());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to load today\'s Family Spark.',
            ]);
        }
    }

    /**
     * POST /api/sparks/respond
     *
     * Records a 1-tap poll vote or conversational reply inside the Living Spark Card.
     */
    public function respond(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            /** @var array<string, mixed> $input */
            $input = is_array($decoded) ? $decoded : $_POST;

            self::validateCsrf($input);
            Limiter::limit($userId . ':spark_respond');

            $requestedFamilyCode = isset($input['family_code']) && is_string($input['family_code'])
                ? trim($input['family_code'])
                : '';
            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = ltrim(strtoupper($requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode), '#');

            if ($familyCode === '' || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $sparkId = trim((string)($input['spark_id'] ?? ''));
            if ($sparkId === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Spark identifier is required.']);
                return;
            }

            $authorName = self::resolveSessionName(Db::connect2())['fullName'];
            $result = DailyFamilySparkService::submitResponse(
                sparkId: $sparkId,
                famCode: $familyCode,
                userId: $userId,
                authorName: $authorName,
                payload: $input
            );

            $httpCode = (int)($result['http_code'] ?? 200);
            unset($result['http_code']);
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\HttpException $e) {
            http_response_code($e->getStatusCode());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to save your response right now.',
            ]);
        }
    }

    /**
     * POST /api/sparks/create
     *
     * Allows a family member to "Spark a Question" for their family hub.
     */
    public function create(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            /** @var array<string, mixed> $input */
            $input = is_array($decoded) ? $decoded : $_POST;

            self::validateCsrf($input);
            Limiter::limit($userId . ':spark_create');

            $requestedFamilyCode = isset($input['family_code']) && is_string($input['family_code'])
                ? trim($input['family_code'])
                : '';
            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = ltrim(strtoupper($requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode), '#');

            if ($familyCode === '' || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $title = trim((string)($input['title'] ?? ''));
            $promptText = trim((string)($input['prompt_text'] ?? ''));
            $rawOptions = $input['options'] ?? [];
            $options = is_array($rawOptions) ? array_values(array_map(static fn ($v): string => (string)$v, $rawOptions)) : [];

            $authorName = self::resolveSessionName(Db::connect2())['fullName'];
            $result = DailyFamilySparkService::createMemberSpark(
                famCode: $familyCode,
                userId: $userId,
                authorName: $authorName,
                title: $title,
                promptText: $promptText,
                options: $options
            );

            $httpCode = (int)($result['http_code'] ?? 200);
            unset($result['http_code']);
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\HttpException $e) {
            http_response_code($e->getStatusCode());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to publish your Family Spark right now.',
            ]);
        }
    }

    /**
     * POST /api/sparks/reply/update
     *
     * Allows the author of a Daily Family Spark / Archive Flashback reply to edit it.
     */
    public function updateReply(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            /** @var array<string, mixed> $input */
            $input = is_array($decoded) ? $decoded : $_POST;

            self::validateCsrf($input);
            Limiter::limit($userId . ':spark_reply_update');

            $requestedFamilyCode = isset($input['family_code']) && is_string($input['family_code'])
                ? trim($input['family_code'])
                : '';
            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = ltrim(strtoupper($requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode), '#');

            if ($familyCode === '' || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $sparkId = trim((string)($input['spark_id'] ?? ''));
            $replyId = trim((string)($input['reply_id'] ?? ''));
            $body = (string)($input['body'] ?? '');

            if ($sparkId === '' || $replyId === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Spark and reply identifiers are required.']);
                return;
            }

            $result = DailyFamilySparkService::updateReply(
                sparkId: $sparkId,
                replyId: $replyId,
                famCode: $familyCode,
                userId: $userId,
                body: $body
            );

            $httpCode = (int)($result['http_code'] ?? 200);
            unset($result['http_code']);
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\HttpException $e) {
            http_response_code($e->getStatusCode());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to update your reply right now.',
            ]);
        }
    }

    /**
     * POST /api/sparks/reply/delete
     *
     * Allows the author of a Daily Family Spark / Archive Flashback reply to delete it.
     */
    public function deleteReply(): void
    {
        CorsHandler::setHeaders();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
                return;
            }

            /** @var array<string, mixed>|null $decoded */
            $decoded = json_decode((string)file_get_contents('php://input'), true);
            /** @var array<string, mixed> $input */
            $input = is_array($decoded) ? $decoded : $_POST;

            self::validateCsrf($input);
            Limiter::limit($userId . ':spark_reply_delete');

            $requestedFamilyCode = isset($input['family_code']) && is_string($input['family_code'])
                ? trim($input['family_code'])
                : '';
            $primaryFamCode = (string)($_SESSION['famCode'] ?? '');
            $familyCode = ltrim(strtoupper($requestedFamilyCode !== '' ? $requestedFamilyCode : $primaryFamCode), '#');

            if ($familyCode === '' || !BaseController::sessionSharesFamily($familyCode)) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Access denied to this family network.']);
                return;
            }

            $sparkId = trim((string)($input['spark_id'] ?? ''));
            $replyId = trim((string)($input['reply_id'] ?? ''));

            if ($sparkId === '' || $replyId === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'message' => 'Spark and reply identifiers are required.']);
                return;
            }

            $result = DailyFamilySparkService::deleteReply(
                sparkId: $sparkId,
                replyId: $replyId,
                famCode: $familyCode,
                userId: $userId
            );

            $httpCode = (int)($result['http_code'] ?? 200);
            unset($result['http_code']);
            http_response_code($httpCode);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes output safely
            echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\HttpException $e) {
            http_response_code($e->getStatusCode());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to delete your reply right now.',
            ]);
        }
    }
}

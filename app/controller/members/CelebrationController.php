<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\CelebrationService;
use Src\CheckToken;
use Src\Exceptions\UnauthorisedException;
use Src\Limiter;

/**
 * Family Celebrations API (RFC-0008).
 *
 * GET  /api/celebrations       the caller's family celebrations (today and the
 *                              last 2 days) and the wishes the caller received
 * POST /api/celebrations/wish  send private wishes (type, target_id, message)
 */
final class CelebrationController
{
    public function index(): void
    {
        try {
            $userId = self::sessionUserId();
            // Unqualified on purpose: tests/Support/shims.php swaps in a no-op so the
            // shared PHPUnit session stays open for later tests.
            releaseSessionLock();

            self::json(200, [
                'status' => 'success',
                'data' => [
                    'occasions' => CelebrationService::occasionsFor($userId),
                    'wishes' => CelebrationService::receivedWishes($userId),
                ],
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    public function wish(): void
    {
        try {
            CheckToken::tokenCheck();
            $userId = self::sessionUserId();
            Limiter::limit('celebration_wish_' . $userId);

            CelebrationService::sendWish(
                senderId: $userId,
                type: is_string($_POST['type'] ?? null) ? $_POST['type'] : '',
                targetId: is_scalar($_POST['target_id'] ?? null) ? (string) $_POST['target_id'] : '',
                message: is_string($_POST['message'] ?? null) ? $_POST['message'] : ''
            );

            self::json(200, ['status' => 'success', 'message' => 'Your wishes have been sent 🎉']);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    private static function sessionUserId(): string
    {
        $userId = $_SESSION['id'] ?? null;
        if (!is_scalar($userId) || (string) $userId === '') {
            throw new UnauthorisedException('Please sign in again.');
        }
        return (string) $userId;
    }

    /** @param array<string, mixed> $payload */
    private static function json(int $code, array $payload): void
    {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json');
        }
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

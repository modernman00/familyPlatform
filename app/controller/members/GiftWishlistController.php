<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\GiftWishlistService;
use InvalidArgumentException;
use Src\functionality\SignIn;
use Src\SelectFn;

/**
 * Controller for FamilyPlatform Pinned Gift Wishlists (Christmas, Birthday, etc.).
 * Authenticated member routes — MUST NOT be added to RouteDispatch::$publicControllers.
 */
final class GiftWishlistController extends BaseController
{
    private GiftWishlistService $service;

    public function __construct(?GiftWishlistService $service = null)
    {
        parent::__construct();
        $this->service = $service ?? new GiftWishlistService();
    }

    /** GET /wishlists */
    public function index(): void
    {
        $userId = $this->currentUserId();
        if ($userId === '') {
            header('Location: /login');
            exit;
        }

        $famCodes = $this->currentFamCodes($userId);
        $userData = SelectFn::selectOneRow('personal', 'id', $userId) ?: [];
        $occasion = is_string($_GET['occasion'] ?? null) ? trim($_GET['occasion']) : null;

        $wishlists = $this->service->listWishlistsForFamilyCodes($famCodes, $occasion);

        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        if ($this->wantsJson()) {
            \Src\ApiResponse::success(['wishlists' => $wishlists], 'Wishlists loaded.', 200);
            return;
        }

        view('member.wishlists', [
            'userId' => $userId,
            'data' => $userData,
            'famCodes' => $famCodes,
            'wishlists' => $wishlists,
            'occasions' => GiftWishlistService::OCCASIONS,
            'csrfToken' => (string) $_SESSION['token'],
        ]);
    }

    /** POST /member/wishlists */
    public function store(): void
    {
        try {
            $userId = $this->currentUserId();
            if ($userId === '') {
                \Src\ApiResponse::error('Please sign in to post a wishlist.', 401);
                return;
            }

            $payload = $this->readInput();
            BaseController::validateCsrf($payload);

            $famCodes = $this->currentFamCodes($userId);
            $primaryFamCode = (string) ($famCodes[0] ?? '');
            if ($primaryFamCode === '') {
                throw new InvalidArgumentException('No active family group found for your account.');
            }

            $userData = SelectFn::selectOneRow('personal', 'id', $userId) ?: [];
            $authorName = trim((string) (($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? '')));
            if ($authorName === '') {
                $authorName = (string) ($_SESSION['fullName'] ?? 'Family Member');
            }

            $created = $this->service->createWishlist($userId, $authorName, $primaryFamCode, [
                'occasion' => $payload['occasion'] ?? 'Christmas',
                'title' => $payload['title'] ?? '',
                'notes' => $payload['notes'] ?? '',
                'pin_days' => $payload['pin_days'] ?? 14,
                'currency' => $payload['currency'] ?? '£',
                'items' => is_array($payload['items'] ?? null) ? $payload['items'] : [],
            ]);

            if ($this->wantsJson()) {
                \Src\ApiResponse::success(['wishlist' => $created], 'Your gift wishlist is now pinned at the top!', 201);
                return;
            }

            header('Location: /wishlists#wishlist-' . (int) $created['id']);
            exit;
        } catch (InvalidArgumentException $e) {
            \Src\ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            $code = (int) $e->getCode();
            $httpCode = ($code >= 400 && $code < 600) ? $code : 500;
            \Src\ApiResponse::error($httpCode >= 500 ? 'Could not publish wishlist. Please try again.' : $e->getMessage(), $httpCode);
        }
    }

    /** POST /member/wishlists/items/[i:itemId]/claim */
    public function claimItem(int|string $itemId): void
    {
        try {
            $userId = $this->currentUserId();
            if ($userId === '') {
                \Src\ApiResponse::error('Please sign in to claim a gift.', 401);
                return;
            }

            $payload = $this->readInput();
            BaseController::validateCsrf($payload);

            $famCodes = $this->currentFamCodes($userId);
            $userData = SelectFn::selectOneRow('personal', 'id', $userId) ?: [];
            $claimerName = trim((string) (($userData['firstName'] ?? '') . ' ' . ($userData['lastName'] ?? '')));
            if ($claimerName === '') {
                $claimerName = (string) ($_SESSION['fullName'] ?? 'Family Member');
            }

            $claimed = $this->service->claimItem(
                (int) $itemId,
                $famCodes,
                $userId,
                $claimerName,
                !empty($payload['is_anonymous'])
            );

            \Src\ApiResponse::success(['item' => $claimed], 'Gift item claimed!', 200);
        } catch (InvalidArgumentException $e) {
            \Src\ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            $code = (int) $e->getCode();
            $httpCode = ($code >= 400 && $code < 600) ? $code : 500;
            \Src\ApiResponse::error($httpCode >= 500 ? 'Could not claim gift. Please try again.' : $e->getMessage(), $httpCode);
        }
    }

    /** POST /member/wishlists/[i:id]/delete */
    public function delete(int|string $id): void
    {
        try {
            $userId = $this->currentUserId();
            if ($userId === '') {
                \Src\ApiResponse::error('Please sign in.', 401);
                return;
            }

            $payload = $this->readInput();
            BaseController::validateCsrf($payload);

            $deleted = $this->service->deleteWishlist((int) $id, $userId);
            if (!$deleted) {
                \Src\ApiResponse::error('You can only remove your own wishlist.', 403);
                return;
            }

            if ($this->wantsJson()) {
                \Src\ApiResponse::success([], 'Wishlist removed.', 200);
                return;
            }

            header('Location: /wishlists');
            exit;
        } catch (\Throwable $e) {
            $code = (int) $e->getCode();
            $httpCode = ($code >= 400 && $code < 600) ? $code : 500;
            \Src\ApiResponse::error($e->getMessage(), $httpCode);
        }
    }

    private function currentUserId(): string
    {
        if (!empty($_SESSION['id'])) {
            return (string) \cleanSession((string) $_SESSION['id']);
        }
        try {
            $verified = SignIn::verify('users');
            return !empty($verified['id']) ? (string) \cleanSession((string) $verified['id']) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    /** @return array<int, string> */
    private function currentFamCodes(string $userId): array
    {
        $codes = [];
        if (!empty($_SESSION['famCodes']) && is_array($_SESSION['famCodes'])) {
            foreach ($_SESSION['famCodes'] as $c) {
                $clean = trim((string) \cleanSession((string) $c));
                if ($clean !== '') {
                    $codes[] = $clean;
                }
            }
        }
        if (!empty($_SESSION['famCode'])) {
            $clean = trim((string) \cleanSession((string) $_SESSION['famCode']));
            if ($clean !== '') {
                $codes[] = $clean;
            }
        }
        if ($codes === [] && $userId !== '') {
            $user = SelectFn::selectOneRow('users', 'id', $userId);
            if (is_array($user) && !empty($user['famCode'])) {
                $codes[] = trim((string) $user['famCode']);
            }
        }
        return array_values(array_unique($codes));
    }

    /** @return array<string, mixed> */
    private function readInput(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }
        $raw = file_get_contents('php://input');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    private function wantsJson(): bool
    {
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $xhr = (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        $ct = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        return str_contains($accept, 'application/json')
            || strcasecmp($xhr, 'XMLHttpRequest') === 0
            || str_contains($ct, 'application/json');
    }

}

<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\MemberRestrictionService;
use PDO;
use Src\CheckToken;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\UnauthorisedException;
use Src\Exceptions\ValidationException;
use Src\functionality\SignIn;
use Throwable;

/**
 * MemberRestrictionController
 *
 * Manages member-level visibility and notification restrictions for the logged-in user.
 */
final class MemberRestrictionController
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Db::connect2();
    }

    private static function getAuthenticatedUserId(): string
    {
        $userId = isset($_SESSION['id']) ? (string)$_SESSION['id'] : '';
        if ($userId === '') {
            try {
                $payload = SignIn::verify('users');
                if (!empty($payload['id'])) {
                    $userId = (string)\cleanSession((string)$payload['id']);
                    $_SESSION['id'] = $userId;
                }
            } catch (Throwable) {
                $userId = '';
            }
        }

        if ($userId === '') {
            throw new UnauthorisedException('Unauthorised: Please sign in to manage member restrictions.');
        }

        return $userId;
    }

    /**
     * Read merged request parameters from POST form or JSON body.
     *
     * @return array<string, mixed>
     */
    private static function readRequestPayload(): array
    {
        $payload = $_POST;
        $rawInput = file_get_contents('php://input');
        if (is_string($rawInput) && trim($rawInput) !== '') {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $payload = array_merge($payload, $decoded);
            }
        }

        if (empty($_POST['token']) && !empty($payload['token'])) {
            $_POST['token'] = (string)$payload['token'];
        }

        return $payload;
    }

    /**
     * GET /api/privacy/restrictions
     * Returns family/connected members and current owner restriction flags.
     */
    public function listDirectory(): void
    {
        try {
            $userId = self::getAuthenticatedUserId();
            $query = trim((string)($_GET['q'] ?? $_GET['query'] ?? ''));
            if ($query !== '') {
                $members = MemberRestrictionService::searchRestrictionDirectory($this->pdo, $userId, $query);
            } else {
                $members = MemberRestrictionService::getRestrictionDirectoryForOwner($this->pdo, $userId);
            }
            \msgSuccess(200, ['members' => $members]);
        } catch (UnauthorisedException $e) {
            \msgException(401, $e->getMessage());
        } catch (Throwable $e) {
            error_log('[MemberRestrictionController::listDirectory] ' . $e->getMessage());
            \msgException(500, 'Unable to load member restriction settings.');
        }
    }

    /**
     * POST /api/privacy/restrictions/update
     * Creates or updates granular restrictions for a target family member.
     */
    public function upsert(): void
    {
        try {
            $payload = self::readRequestPayload();
            CheckToken::tokenCheck();

            $userId = self::getAuthenticatedUserId();
            $targetUserId = trim((string)($payload['restricted_user_id'] ?? $payload['target_user_id'] ?? ''));
            $note = isset($payload['note']) ? (string)$payload['note'] : null;

            $flags = [];
            foreach (MemberRestrictionService::SCOPE_COLUMNS as $scopeKey => $colName) {
                if (array_key_exists($scopeKey, $payload)) {
                    $flags[$scopeKey] = $payload[$scopeKey];
                } elseif (array_key_exists($colName, $payload)) {
                    $flags[$colName] = $payload[$colName];
                }
            }

            $record = MemberRestrictionService::upsertRestriction(
                $this->pdo,
                $userId,
                $targetUserId,
                $flags,
                $note
            );

            \msgSuccess(200, ['restriction' => $record, 'message' => 'Restriction preferences saved.']);
        } catch (UnauthorisedException $e) {
            \msgException(401, $e->getMessage());
        } catch (ValidationException $e) {
            \msgException(422, $e->getMessage());
        } catch (ForbiddenException $e) {
            \msgException(403, $e->getMessage());
        } catch (Throwable $e) {
            $code = (int)$e->getCode();
            if ($code === 401 || $code === 403 || $code === 419) {
                \msgException($code === 401 ? 401 : 403, $e->getMessage());
                return;
            }
            error_log('[MemberRestrictionController::upsert] ' . $e->getMessage());
            \msgException(500, 'Unable to update member restriction settings.');
        }
    }

    /**
     * POST /api/privacy/restrictions/remove
     * Removes all restrictions placed by the logged-in user on the target member.
     */
    public function remove(): void
    {
        try {
            $payload = self::readRequestPayload();
            CheckToken::tokenCheck();

            $userId = self::getAuthenticatedUserId();
            $targetUserId = trim((string)($payload['restricted_user_id'] ?? $payload['target_user_id'] ?? ''));
            if ($targetUserId === '') {
                throw new ValidationException('Member identifier is required.');
            }

            $removed = MemberRestrictionService::removeRestriction($this->pdo, $userId, $targetUserId);
            \msgSuccess(200, ['removed' => $removed, 'message' => 'Member restriction removed.']);
        } catch (UnauthorisedException $e) {
            \msgException(401, $e->getMessage());
        } catch (ValidationException $e) {
            \msgException(422, $e->getMessage());
        } catch (Throwable $e) {
            $code = (int)$e->getCode();
            if ($code === 401 || $code === 403 || $code === 419) {
                \msgException($code === 401 ? 401 : 403, $e->getMessage());
                return;
            }
            error_log('[MemberRestrictionController::remove] ' . $e->getMessage());
            \msgException(500, 'Unable to remove member restriction.');
        }
    }
}

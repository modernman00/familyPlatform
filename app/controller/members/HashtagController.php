<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\HashtagService;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\functionality\SignIn;
use Throwable;

/**
 * Controller for Family Hashtags & Memory Pills APIs.
 *
 * Strictly enforces session verification and family-level boundary isolation.
 */
final class HashtagController
{
    /**
     * Authenticates the current user and returns their verified family code.
     *
     * @return string Verified family code
     * @throws ForbiddenException If unauthenticated
     */
    private static function getAuthenticatedFamCode(): string
    {
        $id = !empty($_SESSION['id']) ? (string)$_SESSION['id'] : '';
        if ($id === '') {
            $verify = SignIn::verify('users');
            if (!empty($verify['id'])) {
                $id = (string)$verify['id'];
                $_SESSION['id'] = $id;
            }
        }

        if ($id === '') {
            throw new ForbiddenException('User session is not verified.');
        }

        $famCode = (string)($_SESSION['famCode'] ?? '');
        if ($famCode === '') {
            // Retrieve from database if not cached in session
            $userRow = \Src\SelectFn::selectOneRow('personal', 'id', $id);
            if (!empty($userRow['famCode'])) {
                $famCode = (string)$userRow['famCode'];
                $_SESSION['famCode'] = $famCode;
            }
        }

        return $famCode;
    }

    /**
     * GET /api/hashtags/trending
     * Returns popular and trending hashtags for the current family.
     */
    public static function trending(): void
    {
        try {
            $famCode = self::getAuthenticatedFamCode();
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

            $tags = HashtagService::getTrendingHashtags($famCode, $limit);

            msgSuccess(200, [
                'status' => 'success',
                'tags' => $tags
            ]);
        } catch (ForbiddenException $fe) {
            msgException(403, $fe->getMessage());
        } catch (Throwable $th) {
            showError($th);
        }
    }

    /**
     * GET /api/hashtags/suggest?q=...
     * Autocomplete suggestions as a user types '#' in post/reel composers.
     */
    public static function suggest(): void
    {
        try {
            $famCode = self::getAuthenticatedFamCode();
            $query = isset($_GET['q']) ? (string)$_GET['q'] : '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

            $suggestions = HashtagService::suggestHashtags($famCode, $query, $limit);

            msgSuccess(200, [
                'status' => 'success',
                'suggestions' => $suggestions
            ]);
        } catch (ForbiddenException $fe) {
            msgException(403, $fe->getMessage());
        } catch (Throwable $th) {
            showError($th);
        }
    }

    /**
     * GET /api/hashtags/stream?tag=...
     * Dedicated topical stream of posts and memories tagged with a specific hashtag.
     */
    public static function stream(): void
    {
        try {
            $famCode = self::getAuthenticatedFamCode();
            $tag = isset($_GET['tag']) ? (string)$_GET['tag'] : '';

            if (trim($tag) === '') {
                throw new ValidationException('Hashtag parameter is required.');
            }

            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 50)) : 30;
            $offset = ($page - 1) * $limit;

            $stream = HashtagService::getTagStream($famCode, $tag, $limit, $offset);

            msgSuccess(200, [
                'status' => 'success',
                'data' => $stream
            ]);
        } catch (ForbiddenException $fe) {
            msgException(403, $fe->getMessage());
        } catch (ValidationException $ve) {
            msgException(422, $ve->getMessage());
        } catch (Throwable $th) {
            showError($th);
        }
    }
}

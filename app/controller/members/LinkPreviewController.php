<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\LinkPreviewService;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\ValidationException;
use Src\functionality\SignIn;
use Throwable;

/**
 * LinkPreviewController
 *
 * Exposes live OpenGraph unfurling API for the post composer and feed cards.
 */
final class LinkPreviewController
{
    /**
     * Authenticates the current user session.
     */
    private static function authenticate(): string
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

        return $id;
    }

    /**
     * GET /api/link-preview?url=...
     * Unfurls OpenGraph metadata and returns the 16:9 social card data.
     */
    public static function preview(): void
    {
        try {
            self::authenticate();

            $rawUrl = (string)($_GET['url'] ?? '');
            if (trim($rawUrl) === '') {
                throw new ValidationException('A valid URL query parameter is required.');
            }

            // Cap URL length to prevent buffer strain
            if (strlen($rawUrl) > 2048) {
                throw new ValidationException('URL exceeds maximum allowable length.');
            }

            $extracted = LinkPreviewService::extractFirstPreviewableUrl($rawUrl);
            if ($extracted === null || !LinkPreviewService::isSafeUrl($extracted)) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'The provided URL cannot be previewed or violates safety rules.',
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                return;
            }

            $preview = LinkPreviewService::getPreviewForPostText($rawUrl);

            header('Content-Type: application/json; charset=utf-8');
            if ($preview !== null && (!empty($preview['title']) || !empty($preview['image']))) {
                echo json_encode([ // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                    'status' => 'success',
                    'data' => $preview,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([ // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                    'status' => 'empty',
                    'data' => null,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        } catch (Throwable $e) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }
}

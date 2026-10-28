<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\OmniSearchService;
use Src\functionality\SignIn;
use Throwable;

/**
 * 🔍 OmniSearchController
 *
 * REST API Controller for Omni-Search discovery across functions, activities,
 * and multi-tenant data.
 */
final class OmniSearchController extends BaseController
{
    private OmniSearchService $service;

    public function __construct(?OmniSearchService $service = null)
    {
        try {
            if (!empty($_SESSION['id'])) {
                parent::__construct();
            }
        } catch (Throwable $e) {
            \error_log('[OmniSearchController::__construct] Auth notice: ' . $e->getMessage());
        }
        $this->service = $service ?? new OmniSearchService();
    }

    /**
     * Executes real-time omni-search query.
     * Route: GET /api/omni-search?q=...
     */
    public function search(): void
    {
        \header('Content-Type: application/json; charset=utf-8');
        \header('Cache-Control: private, no-cache, no-store, must-revalidate');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                try {
                    $payload = SignIn::verify();
                    if (!empty($payload['id'])) {
                        $userId = (string)$payload['id'];
                        $_SESSION['id'] = $userId;
                    }
                } catch (Throwable) {
                    $userId = '';
                }
            }

            if ($userId === '') {
                \http_response_code(401);
                echo \json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorised: Please sign in to search family data and activities.',
                ], JSON_UNESCAPED_SLASHES);
                return;
            }

            $query = isset($_GET['q']) ? \trim((string)$_GET['q']) : '';
            $sessionFamCode = (string)($_SESSION['famCode'] ?? '');

            // Cap query length defensively
            if (\strlen($query) > 100) {
                $query = \substr($query, 0, 100);
            }

            $result = $this->service->search($query, $userId, $sessionFamCode);

            \http_response_code(200);
            echo \json_encode([ // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
                'status' => 'success',
                'data' => $result,
            ], JSON_UNESCAPED_SLASHES);
        } catch (Throwable $th) {
            \error_log('[OmniSearchController::search] Error: ' . $th->getMessage());
            \http_response_code(500);
            echo \json_encode([
                'status' => 'error',
                'message' => 'Unable to execute search at this moment. Please try again.',
            ], JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Returns the full client-side catalog of actions and direct tools.
     * Route: GET /api/omni-search/catalog
     */
    public function catalog(): void
    {
        \header('Content-Type: application/json; charset=utf-8');
        \header('Cache-Control: private, max-age=300');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $sessionFamCode = (string)($_SESSION['famCode'] ?? '');

            $catalog = $this->service->getActionCatalog($userId, $sessionFamCode);

            \http_response_code(200);
            echo \json_encode([
                'status' => 'success',
                'data' => $catalog,
            ], JSON_UNESCAPED_SLASHES);
        } catch (Throwable $th) {
            \error_log('[OmniSearchController::catalog] Error: ' . $th->getMessage());
            \http_response_code(500);
            echo \json_encode([
                'status' => 'error',
                'message' => 'Unable to retrieve action catalog.',
            ], JSON_UNESCAPED_SLASHES);
        }
    }
}

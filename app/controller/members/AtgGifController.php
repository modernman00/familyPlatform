<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\services\AtgFamilyGifService;
use Src\Db;
use Src\functionality\SignIn;
use Src\Utility;
use Throwable;

/**
 * AtgGifController — Sovereign ATG Brand GIF Studio & Synthesizer Controller
 *
 * Authored by: Advanced Technology Group (ATG Pods 3 & 4)
 * Governed by: Master Engineering Charter (v3.0 Executive Edition) & TAT 2.0
 * Compliant with: Universal British English, Zero SQL Interpolation, Safe Touch Targets
 */
final class AtgGifController extends BaseController
{
    private string $userId;

    public function __construct()
    {
        if (empty($_SESSION['id'])) {
            try {
                SignIn::verify();
            } catch (Throwable $e) {
                // Read-only GIF serving paths allow guest/crawler unfurls
            }
        }

        $this->userId = (string)($_SESSION['id'] ?? '');
    }

    /**
     * Render the dedicated ATG Brand GIF Studio.
     * Route: GET /member/gif-studio
     */
    public function studio(): void
    {
        if (empty($this->userId)) {
            header('Location: /login');
            exit;
        }

        $catalog = AtgFamilyGifService::getCatalog();
        $familyName = $this->resolveFamilyName();

        Utility::view('member/gifStudio', [
            'pageTitle'    => 'FamilyPlatform Brand GIF Studio — ATG Sovereign Visuals',
            'catalog'      => $catalog,
            'defaultName'  => $familyName,
            'userId'       => $this->userId,
        ]);
    }

    /**
     * JSON Catalog API of official FamilyPlatform brand GIFs.
     * Route: GET /api/atg/gif/catalog
     */
    public function catalog(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        echo json_encode([
            'status'  => 'success',
            'brand'   => 'FamilyPlatform',
            'catalog' => array_values(AtgFamilyGifService::getCatalog()),
        ], JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Public stream endpoint for an official brand GIF.
     * Route: GET /api/atg/gif/view/[*:key]
     */
    public function viewGif(string $key): void
    {
        $cleanKey = trim(strip_tags($key));

        if (!AtgFamilyGifService::isOfficialGifKey($cleanKey)) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Brand GIF not found.';
            exit;
        }

        try {
            $binary = AtgFamilyGifService::getOfficialGifBinary($cleanKey);
            $etag = '"' . hash('sha256', 'atg_official_gif:' . $cleanKey . ':' . strlen($binary)) . '"';

            header('ETag: ' . $etag);
            header('Cache-Control: public, max-age=31536000, immutable');
            header('X-Content-Type-Options: nosniff');

            if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
                http_response_code(304);
                exit;
            }

            header('Content-Type: image/gif');
            header('Content-Length: ' . (string)strlen($binary));
            echo $binary;
            exit;
        } catch (Throwable $e) {
            error_log('[AtgGifController::viewGif] ' . $e->getMessage());
            http_response_code(500);
            echo 'Error rendering brand GIF.';
            exit;
        }
    }

    /**
     * Public stream endpoint for a dynamically synthesized dynasty GIF.
     * Route: GET /api/atg/gif/custom
     */
    public function viewCustom(): void
    {
        $rawName = isset($_GET['name']) ? (string)$_GET['name'] : (isset($_GET['n']) ? (string)$_GET['n'] : 'Our Family Circle');
        $rawCaption = isset($_GET['caption']) ? (string)$_GET['caption'] : (isset($_GET['c']) ? (string)$_GET['c'] : 'Generations of Love');
        $theme = isset($_GET['theme']) ? (string)$_GET['theme'] : (isset($_GET['t']) ? (string)$_GET['t'] : 'crimson_gold');

        $cleanName = mb_substr(trim(strip_tags($rawName)), 0, 40);
        $cleanCaption = mb_substr(trim(strip_tags($rawCaption)), 0, 50);

        try {
            $binary = AtgFamilyGifService::getCustomGifBinary($cleanName, $cleanCaption, $theme);
            $etag = '"' . hash('sha256', "atg_custom_gif:{$cleanName}|{$cleanCaption}|{$theme}|" . strlen($binary)) . '"';

            header('ETag: ' . $etag);
            header('Cache-Control: public, max-age=31536000, immutable');
            header('X-Content-Type-Options: nosniff');

            if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
                http_response_code(304);
                exit;
            }

            header('Content-Type: image/gif');
            header('Content-Length: ' . (string)strlen($binary));
            echo $binary;
            exit;
        } catch (Throwable $e) {
            error_log('[AtgGifController::viewCustom] ' . $e->getMessage());
            http_response_code(500);
            echo 'Error synthesizing dynasty GIF.';
            exit;
        }
    }

    /**
     * Synthesize a custom dynasty GIF via AJAX.
     * Route: POST /api/atg/gif/synthesize
     */
    public function synthesize(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($this->userId)) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Authentication required.'], JSON_THROW_ON_ERROR);
            exit;
        }

        $input = [];
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }
        if (empty($input)) {
            $input = $_POST;
        }

        $familyName = trim((string)($input['family_name'] ?? ''));
        $caption = trim((string)($input['caption'] ?? ''));
        $theme = trim((string)($input['theme'] ?? 'crimson_gold'));

        if ($familyName === '') {
            $familyName = $this->resolveFamilyName();
        }

        $cleanName = mb_substr(strip_tags($familyName), 0, 40);
        $cleanCaption = mb_substr(strip_tags($caption), 0, 50);

        try {
            // Warm cache and pre-generate binary
            AtgFamilyGifService::getCustomGifBinary($cleanName, $cleanCaption, $theme);

            $streamUrl = '/api/atg/gif/custom?' . http_build_query([
                'name'    => $cleanName,
                'caption' => $cleanCaption,
                'theme'   => $theme,
            ]);

            echo json_encode([
                'status'      => 'success',
                'url'         => $streamUrl,
                'family_name' => $cleanName,
                'caption'     => $cleanCaption,
                'theme'       => $theme,
                'message'     => 'Dynasty GIF synthesized successfully.',
            ], JSON_THROW_ON_ERROR);
            exit;
        } catch (Throwable $e) {
            error_log('[AtgGifController::synthesize] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'GIF synthesis failed.'], JSON_THROW_ON_ERROR);
            exit;
        }
    }

    /**
     * Resolve family name for the current logged-in member.
     */
    private function resolveFamilyName(): string
    {
        if (empty($this->userId)) {
            return 'Our Family Circle';
        }

        try {
            $pdo = Db::connect2();
            $stmt = $pdo->prepare("SELECT lastName, famCode FROM personal WHERE id = ? LIMIT 1");
            $stmt->execute([$this->userId]);
            $row = $stmt->fetch();

            if ($row && !empty($row['lastName'])) {
                $last = trim((string)$row['lastName']);
                return "The {$last} Family";
            }
        } catch (Throwable $e) {
            error_log('[AtgGifController::resolveFamilyName] ' . $e->getMessage());
        }

        return 'Our Family Circle';
    }
}

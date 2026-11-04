<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\Post;
use App\services\AtgEmojiService;
use Exception;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\functionality\SignIn;
use Src\SelectFn;

/**
 * ATG Sovereign 3D Emoji & Avatar Controller
 *
 * Developed by the Advanced Technology Group (ATG Pods 3 & 4)
 * Lead Architect: Kaelen Vance & Maya Lindqvist
 * Core Systems: Callum Wright & Damilola Ogunleye
 */
final class AtgEmojiController extends BaseController
{
    private AtgEmojiService $service;
    private string $userId;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AtgEmojiService();

        // Verify session / authentication
        if (empty($_SESSION['id'])) {
            try {
                SignIn::verify();
            } catch (\Throwable $e) {
                // For public API catalog, allow guest read-only; for state modifications, block.
            }
        }
        $this->userId = (string)($_SESSION['id'] ?? '');
    }

    /**
     * Render the full dedicated 3D Emoji & Avatar Studio view.
     * Route: GET /member/emoji-studio
     */
    public function studio(): void
    {
        if (empty($this->userId)) {
            redirect('/login');
            return;
        }

        $userData = SelectFn::selectOneRow('personal', 'id', $this->userId) ?: [];
        $data = $userData;
        $data['profileImg'] = Post::getProfilePicsById($this->userId) ?: 'avatarM.png';

        $catalog = $this->service->getCatalog();

        view('member.emojiStudio', [
            'data'     => $data,
            'userData' => $userData,
            'catalog'  => $catalog,
        ]);
    }

    /**
     * Return JSON catalog of 3D emojis and traits.
     * Route: GET /api/emoji/catalog
     */
    public function catalog(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $catalog = $this->service->getCatalog();
            echo json_encode([
                'success' => true,
                'data'    => $catalog,
            ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Render procedural 3D avatar SVG for real-time live preview.
     * Route: POST /api/emoji/render-avatar
     */
    public function renderAvatar(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        try {
            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?: [];

            $traits = [
                'skin_tone'  => (string)($input['skin_tone'] ?? 'warm_chestnut'),
                'hairstyle'  => (string)($input['hairstyle'] ?? 'afro_crown'),
                'expression' => (string)($input['expression'] ?? 'warm_smile'),
                'accessory'  => (string)($input['accessory'] ?? 'none'),
                'backdrop'   => (string)($input['backdrop'] ?? 'warm_sunset'),
            ];

            $size = isset($input['size']) ? (int)$input['size'] : 256;
            $svg = $this->service->renderProceduralAvatar($traits, $size);

            echo json_encode([
                'success' => true,
                'svg'     => $svg,
            ], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Render single 3D SVG emoji by ID.
     * Route: GET /api/emoji/svg/[*:id]
     */
    public function renderEmojiSvg(string $id = ''): void
    {
        $id = trim($id);
        if ($id === '') {
            $id = (string)($_GET['id'] ?? '');
        }

        $size = isset($_GET['size']) ? (int)$_GET['size'] : 120;
        $svg = $this->service->renderSvgEmoji($id, $size);

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo $svg;
    }

    /**
     * Save procedural 3D avatar as the active user's profile image.
     * Route: POST /api/emoji/save-avatar
     */
    public function saveAvatar(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            if (empty($this->userId)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Authentication required to save profile avatar.',
                ]);
                return;
            }

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?: [];

            // CSRF Verification
            $clientToken = (string)($input['csrf_token'] ?? $input['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_POST['token'] ?? '');
            $sessionToken = (string)($_SESSION['token'] ?? '');

            if ($sessionToken !== '' && !hash_equals($sessionToken, $clientToken)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Invalid security token. Please refresh the page and try again.',
                ]);
                return;
            }

            $traits = [
                'skin_tone'  => (string)($input['skin_tone'] ?? 'warm_chestnut'),
                'hairstyle'  => (string)($input['hairstyle'] ?? 'afro_crown'),
                'expression' => (string)($input['expression'] ?? 'warm_smile'),
                'accessory'  => (string)($input['accessory'] ?? 'none'),
                'backdrop'   => (string)($input['backdrop'] ?? 'warm_sunset'),
            ];

            $pngBase64 = isset($input['png_base64']) && is_string($input['png_base64']) ? $input['png_base64'] : null;

            $result = $this->service->saveAvatar($this->userId, $traits, $pngBase64);

            echo json_encode([
                'success'  => true,
                'message'  => 'Your 3D Avatar has been successfully set as your profile picture!',
                'filename' => $result['filename'],
                'url'      => $result['url'],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

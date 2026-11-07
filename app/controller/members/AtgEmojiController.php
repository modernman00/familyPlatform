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

        // Re-open the customiser on the member's saved studio look (if any).
        $savedTraits = $this->service->traitsFromProfileImage((string)$data['profileImg']);
        // First visit: start from the member's profile gender (still freely changeable).
        $genderDefault = match (strtolower((string)($userData['gender'] ?? ''))) {
            'female' => ['presentation' => 'feminine'],
            'male'   => ['presentation' => 'masculine', 'hairstyle' => 'high_fade'],
            default  => [],
        };
        $initialTraits = $this->service->sanitiseTraits($savedTraits ?? $genderDefault);

        $rawProfileImg = (string)$data['profileImg'];
        $currentProfilePhotoUrl = str_starts_with($rawProfileImg, '/')
            ? $rawProfileImg
            : '/resources/images/profile/' . $rawProfileImg;
        if (str_ends_with($currentProfilePhotoUrl, '.svg')) {
            $pngCompanion = substr($currentProfilePhotoUrl, 0, -4) . '.png';
            if (is_file(ltrim($pngCompanion, '/'))) {
                $currentProfilePhotoUrl = $pngCompanion;
            }
        }

        view('member.emojiStudio', [
            'data'                   => $data,
            'userData'               => $userData,
            'catalog'                => $catalog,
            'initialTraits'          => $initialTraits,
            'initialSvg'             => $this->service->renderProceduralAvatar($initialTraits, 256, true),
            'hasSavedLook'           => $savedTraits !== null,
            'currentProfilePhotoUrl' => $currentProfilePhotoUrl,
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

            $traits = $this->service->sanitiseTraits($input);

            $size = isset($input['size']) ? (int)$input['size'] : 256;
            $animated = !isset($input['animated']) || (bool)$input['animated'];
            $svg = $this->service->renderProceduralAvatar($traits, $size, $animated);

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

            $traits = $this->service->sanitiseTraits($input);

            $pngBase64 = isset($input['png_base64']) && is_string($input['png_base64']) ? $input['png_base64'] : null;

            $animated = !empty($input['animated']);
            $mode = isset($input['mode']) && is_string($input['mode']) ? $input['mode'] : 'procedural';
            $choreography = isset($input['choreography']) && is_string($input['choreography']) ? $input['choreography'] : 'smile';
            $sampleRgb = [
                isset($input['sample_r']) ? max(0, min(255, (int)$input['sample_r'])) : 138,
                isset($input['sample_g']) ? max(0, min(255, (int)$input['sample_g'])) : 84,
                isset($input['sample_b']) ? max(0, min(255, (int)$input['sample_b'])) : 52,
            ];

            $result = $this->service->saveAvatar($this->userId, $traits, $pngBase64, $animated, $mode, $choreography, $sampleRgb);

            echo json_encode([
                'success'  => true,
                'message'  => $mode === 'living_photo'
                    ? 'Your Living Photo is now your official profile picture — it smiles, blinks, and moves wherever your profile appears!'
                    : ($animated
                        ? 'Your living avatar is now your profile picture — it blinks and breathes wherever your photo appears.'
                        : 'Your 3D Avatar has been successfully set as your profile picture!'),
                'filename' => $result['filename'],
                'url'      => $result['url'],
                'png_url'  => $result['png_url'] ?? $result['url'],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Analyse an uploaded portrait photo to preserve identity, extract complexion & hair
     * traits, and calibrate neural realism parameters (denoising, CFG scale, face restoration).
     * Route: POST /api/emoji/photo-to-avatar
     */
    public function photoToAvatar(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            if (empty($this->userId)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error'   => 'Authentication required to synthesise photo avatar.',
                ]);
                return;
            }

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?: [];

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

            $analysis = $this->service->analysePhotoToAvatar($input);

            echo json_encode([
                'success' => true,
                'data'    => $analysis,
            ], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\Post;
use App\services\AtgEmojiService;
use App\services\AtgPhotoAvatarEngine;
use App\services\AvatarStickerService;
use App\services\TalkingAvatarNeuralService;
use Exception;
use InvalidArgumentException;
use Src\ApiResponse;
use Src\AuditLogger;
use Src\Db;
use Src\Exceptions\ForbiddenException;
use Src\Exceptions\TooManyRequestsException;
use Src\functionality\SignIn;
use Src\Limiter;
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

        $stickerSvc = new AvatarStickerService();
        view('member.emojiStudio', [
            'data'                   => $data,
            'userData'               => $userData,
            'catalog'                => $catalog,
            'avatarStyles'           => AtgPhotoAvatarEngine::getStylesForUi(),
            'initialTraits'          => $initialTraits,
            'initialSvg'             => $this->service->renderProceduralAvatar($initialTraits, 256, true),
            'hasSavedLook'           => $savedTraits !== null,
            'currentProfilePhotoUrl' => $currentProfilePhotoUrl,
            // Photo Me-stickers: the member's real portrait acts out each reaction.
            'stickerUserId'          => $this->userId,
            'hasProfilePhoto'        => $stickerSvc->hasProfilePhoto($this->userId),
            'stickerKind'            => $stickerSvc->getStickerSource($this->userId)['kind'] ?? null,
            'stickerPhotoUrl'        => $stickerSvc->stickerPhotoUrl($this->userId),
            'stickerFaceKnown'       => $stickerSvc->hasFaceBox($this->userId),
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

        $size = isset($_GET['size']) ? max(24, min(512, (int)$_GET['size'])) : 120;
        $etag = '"' . hash('sha256', "atg_emoji_svg:{$id}:{$size}:v2") . '"';

        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('X-Content-Type-Options: nosniff');

        if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
            http_response_code(304);
            return;
        }

        $svg = $this->service->renderSvgEmoji($id, $size);

        header('Content-Type: image/svg+xml; charset=UTF-8');
        echo $svg;
    }

    /**
     * Me-sticker image (RFC-0011): the owner's avatar performing a reaction.
     * Route: GET /api/emoji/avatar-sticker/[:userId]/[a:reaction]
     * Logged-in + same family only; every other case is a 404 so existence is not leaked.
     */
    public function avatarSticker(string $ownerId = '', string $reaction = ''): void
    {
        $ownerId = trim($ownerId);
        $reaction = strtolower(trim($reaction));
        $stickers = new AvatarStickerService();

        $traits = null;
        if (
            $this->userId !== ''
            && AvatarStickerService::isValidUserId($ownerId)
            && AvatarStickerService::isValidReaction($reaction)
            && $stickers->canView($this->userId, $ownerId)
        ) {
            $traits = $stickers->getStickerSource($ownerId);
            // Studio preview: the owner may preview their photo sticker before choosing it.
            if (
                $traits !== null && $traits['kind'] !== 'photo'
                && ($_GET['look'] ?? '') === 'photo' && hash_equals($this->userId, $ownerId)
            ) {
                $traits = $stickers->getStickerSource($ownerId, true) ?? $traits;
            }
        }

        header('X-Content-Type-Options: nosniff');
        if ($traits === null) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=UTF-8');
            header('Cache-Control: no-store');
            echo 'Not found';
            return;
        }

        $etag = '"' . hash('sha256', $ownerId . ':' . $traits['version'] . ':' . $reaction . ':v1') . '"';
        header('ETag: ' . $etag);
        // Feed posts reference the UNVERSIONED url (the token carries no version), so it must
        // revalidate every time — otherwise a re-designed avatar shows the old look for a day.
        // Composer/picker urls carry ?v=<version> and may be cached for a day.
        $versioned = isset($_GET['v']) && (string)$_GET['v'] === (string)$traits['version'];
        header($versioned ? 'Cache-Control: private, max-age=86400' : 'Cache-Control: private, no-cache');
        header('Vary: Cookie');
        if (trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
            http_response_code(304);
            return;
        }

        header('Content-Type: image/svg+xml; charset=UTF-8');
        header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
        if ($traits['kind'] === 'photo') {
            $photoSvg = $stickers->renderPhotoSticker($traits['photo'], $reaction, 160);
            if ($photoSvg !== null) {
                echo $photoSvg;
                return;
            }
            // Unreadable photo: fall back to the studio avatar if one exists.
            $fallback = $stickers->getTraits($ownerId);
            echo $fallback !== null
                ? $stickers->renderSticker($fallback['traits'], $reaction, 160)
                : $stickers->renderSticker([], $reaction, 160);
            return;
        }
        echo $stickers->renderSticker($traits['traits'], $reaction, 160);
    }

    /**
     * The signed-in member's own Me-sticker set for the composer / comment picker.
     * Route: GET /api/emoji/me-stickers
     */
    public function meStickers(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'available' => false]);
            return;
        }
        $saved = (new AvatarStickerService())->getStickerSource($this->userId);
        if ($saved === null) {
            echo json_encode(['success' => true, 'available' => false, 'studio_url' => '/member/emoji-studio']);
            return;
        }
        $items = [];
        foreach (AvatarStickerService::REACTION_LABELS as $reaction => $label) {
            $items[] = [
                'reaction' => $reaction,
                'label'    => $label,
                'token'    => ':me_' . $reaction . '@' . $this->userId . ':',
                'url'      => '/api/emoji/avatar-sticker/' . rawurlencode($this->userId) . '/' . $reaction . '?v=' . rawurlencode($saved['version']),
            ];
        }
        echo json_encode([
            'success'   => true,
            'available' => true,
            'user_id'   => $this->userId,
            'version'   => $saved['version'],
            'kind'      => $saved['kind'],
            'stickers'  => $items,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Save the studio look as the member's Me-sticker avatar WITHOUT touching their
     * profile picture (members who keep a real photo can still post Me-stickers).
     * Route: POST /api/emoji/me-stickers/look
     */
    public function saveStickerLook(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            return;
        }
        $input = json_decode((string)file_get_contents('php://input'), true);
        $input = is_array($input) ? $input : [];

        $clientToken = (string)($input['csrf_token'] ?? $input['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $sessionToken = (string)($_SESSION['token'] ?? '');
        if ($sessionToken !== '' && !hash_equals($sessionToken, $clientToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh the page and try again.']);
            return;
        }

        try {
            $svc = new AvatarStickerService();
            // source=photo: stickers use the member's real portrait, exactly as uploaded.
            $usePhoto = ($input['source'] ?? '') === 'photo';
            if ($usePhoto && !$svc->hasProfilePhoto($this->userId)) {
                http_response_code(422);
                echo json_encode(['success' => false, 'error' => 'Add a profile photo first, then try again.']);
                return;
            }
            $svc->saveTraits($this->userId, $this->service->sanitiseTraits($input), $usePhoto ? 'photo' : 'procedural');
            $saved = $svc->getStickerSource($this->userId);
            echo json_encode(['success' => true, 'version' => $saved['version'] ?? null]);
        } catch (\Throwable $e) {
            error_log('[AtgEmojiController::saveStickerLook] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not save your sticker look.']);
        }
    }

    /**
     * Store the face box the studio's in-browser detector found on the member's own
     * sticker photo, so photo Me-stickers zoom in on the face.
     * Route: POST /api/emoji/me-stickers/face  (JSON {x,y,w,h} as fractions 0..1)
     */
    public function saveStickerFace(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            return;
        }
        $input = json_decode((string)file_get_contents('php://input'), true);
        $input = is_array($input) ? $input : [];
        $clientToken = (string)($input['csrf_token'] ?? $input['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $sessionToken = (string)($_SESSION['token'] ?? '');
        if ($sessionToken !== '' && !hash_equals($sessionToken, $clientToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh the page and try again.']);
            return;
        }
        $svc = new AvatarStickerService();
        $box = is_array($input['box'] ?? null) ? $input['box'] : [];
        if (!$svc->saveFaceBox($this->userId, $box)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'Face position not saved.']);
            return;
        }
        echo json_encode(['success' => true, 'version' => $svc->getStickerSource($this->userId)['version'] ?? null]);
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
            $avatarStyle = isset($input['avatar_style']) && is_string($input['avatar_style'])
                ? ($input['avatar_style'] === 'original' ? 'original' : AtgPhotoAvatarEngine::sanitiseStyle($input['avatar_style']))
                : 'original';
            $sampleRgb = [
                isset($input['sample_r']) ? max(0, min(255, (int)$input['sample_r'])) : 138,
                isset($input['sample_g']) ? max(0, min(255, (int)$input['sample_g'])) : 84,
                isset($input['sample_b']) ? max(0, min(255, (int)$input['sample_b'])) : 52,
            ];

            $result = $this->service->saveAvatar($this->userId, $traits, $pngBase64, $animated, $mode, $choreography, $sampleRgb, $avatarStyle);

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

    /**
     * Transform a portrait photo into an OpenArt-grade identity-preserving stylised avatar
     * across 6 curated art styles (Neural AI image-to-image with Sovereign Deterministic fallback).
     * Route: POST /api/emoji/transform-photo-avatar
     */
    public function transformPhotoAvatar(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        try {
            if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
                http_response_code(401);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Authentication required to transform your photo into an avatar.', 'AUTH_REQUIRED'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $input = is_array($input) ? $input : [];

            $clientToken = (string)($input['csrf_token'] ?? $input['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_POST['token'] ?? '');
            $sessionToken = (string)($_SESSION['token'] ?? '');

            if ($sessionToken !== '' && !hash_equals($sessionToken, $clientToken)) {
                http_response_code(403);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Invalid security token. Please refresh the page and try again.', 'CSRF_MISMATCH'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            try {
                Limiter::limit('atg_photo_avatar:' . $this->userId, 'post');
            } catch (TooManyRequestsException $rateEx) {
                http_response_code(429);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope($rateEx->getMessage(), 'RATE_LIMITED'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $imageBase64 = isset($input['image_base64']) && is_string($input['image_base64'])
                ? trim($input['image_base64'])
                : '';

            if ($imageBase64 === '') {
                http_response_code(422);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Please upload or choose a portrait photo first.', 'IMAGE_REQUIRED'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $rawStyle = isset($input['style']) && is_string($input['style']) ? trim($input['style']) : 'illustrated_comic';
            if ($rawStyle !== '' && !AtgPhotoAvatarEngine::isValidStyle($rawStyle)) {
                http_response_code(422);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Unsupported avatar style selected.', 'INVALID_STYLE'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $engine = new AtgPhotoAvatarEngine();
            $result = $engine->transform($imageBase64, $rawStyle, [
                'strength'        => $input['strength'] ?? 0.78,
                'line_weight'     => $input['line_weight'] ?? 0.68,
                'detail_lock'     => $input['detail_lock'] ?? 0.80,
                'force_sovereign' => !empty($input['force_sovereign']),
            ]);

            try {
                AuditLogger::log('atg_photo_avatar_transform', [
                    'user_id'       => $this->userId,
                    'style'         => $result['style'],
                    'engine'        => $result['engine'],
                    'fallback_used' => $result['fallback_used'],
                    'processing_ms' => $result['processing_ms'],
                ]);
            } catch (\Throwable) {
                // Non-blocking audit log
            }

            echo json_encode(array_merge(
                ApiResponse::toSuccessEnvelope($result, 'Your portrait has been transformed into a stylised avatar.'),
                ['success' => true]
            ), JSON_UNESCAPED_SLASHES);
        } catch (InvalidArgumentException $valEx) {
            http_response_code(422);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope($valEx->getMessage(), 'VALIDATION_ERROR'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            error_log('[AtgEmojiController::transformPhotoAvatar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope('Could not transform your portrait right now. Please try again.', 'SERVER_ERROR'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Queue a Tier-B in-house neural talking-avatar HD video render.
     * Route: POST /api/emoji/talking-avatar/render
     */
    public function renderTalkingAvatar(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        try {
            if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
                http_response_code(401);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Authentication required to generate a talking avatar.', 'AUTH_REQUIRED'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $rawInput = (string)file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            $input = is_array($input) ? $input : [];

            $clientToken = (string)($input['csrf_token'] ?? $input['token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? $_POST['token'] ?? '');
            $sessionToken = (string)($_SESSION['token'] ?? '');
            if ($sessionToken !== '' && !hash_equals($sessionToken, $clientToken)) {
                http_response_code(403);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope('Invalid security token. Please refresh the page and try again.', 'CSRF_MISMATCH'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            try {
                Limiter::limit('atg_talking_avatar:' . $this->userId, 'post');
            } catch (TooManyRequestsException $rateEx) {
                http_response_code(429);
                echo json_encode(array_merge(
                    ApiResponse::toErrorEnvelope($rateEx->getMessage(), 'RATE_LIMITED'),
                    ['success' => false]
                ), JSON_UNESCAPED_SLASHES);
                return;
            }

            $svc = new TalkingAvatarNeuralService();
            $result = $svc->enqueueRender($this->userId, $input);

            try {
                AuditLogger::log('atg_talking_avatar_render', [
                    'user_id'        => $this->userId,
                    'job_id'         => $result['job_id'],
                    'status'         => $result['status'],
                    'sidecar_online' => $result['sidecar_online'],
                ]);
            } catch (\Throwable) {
                // Non-blocking audit log
            }

            http_response_code($result['sidecar_online'] ? 202 : 200);
            echo json_encode(array_merge(
                ApiResponse::toSuccessEnvelope($result, $result['message']),
                ['success' => true]
            ), JSON_UNESCAPED_SLASHES);
        } catch (InvalidArgumentException $valEx) {
            http_response_code(422);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope($valEx->getMessage(), 'VALIDATION_ERROR'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            error_log('[AtgEmojiController::renderTalkingAvatar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope('Could not queue your talking avatar video. Please try again.', 'SERVER_ERROR'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Poll owner-scoped status of a Tier-B neural talking-avatar job.
     * Route: GET /api/emoji/talking-avatar/status/[:jobId]
     */
    public function talkingAvatarStatus(string $jobId = ''): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');

        if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
            http_response_code(401);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope('Authentication required.', 'AUTH_REQUIRED'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
            return;
        }

        $svc = new TalkingAvatarNeuralService();
        $status = $svc->getJobStatus($this->userId, $jobId);
        if ($status === null) {
            http_response_code(404);
            echo json_encode(array_merge(
                ApiResponse::toErrorEnvelope('Talking avatar job not found.', 'NOT_FOUND'),
                ['success' => false]
            ), JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode(array_merge(
            ApiResponse::toSuccessEnvelope($status, 'Job status retrieved.'),
            ['success' => true]
        ), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Stream an owner-scoped neural talking-avatar MP4 from private storage.
     * Route: GET /api/emoji/talking-avatar/video/[:jobId]
     */
    public function streamTalkingAvatarVideo(string $jobId): void
    {
        if ($this->userId === '' || !AvatarStickerService::isValidUserId($this->userId)) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(ApiResponse::toErrorEnvelope('Authentication required.', 'AUTH_REQUIRED'), JSON_UNESCAPED_SLASHES);
            return;
        }

        $svc = new TalkingAvatarNeuralService();
        $path = $svc->getVideoFilePath($this->userId, $jobId);
        if ($path === null) {
            http_response_code(404);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(ApiResponse::toErrorEnvelope('Video not found.', 'NOT_FOUND'), JSON_UNESCAPED_SLASHES);
            return;
        }

        header('Content-Type: video/mp4');
        header('Content-Length: ' . (string)filesize($path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
    }
}

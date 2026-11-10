<?php

declare(strict_types=1);

namespace App\controller\members;

use App\services\AtgFestiveCardService;
use App\services\AtgFestiveCardRenderer;
use Src\CheckToken;
use Src\Db;
use PDO;

/**
 * FestiveCardController — Festive Greeting Cards & Living Envelope Studio
 *
 * Governed by TAT / ATG (Olutobi & Victor)
 */
final class FestiveCardController
{
    private AtgFestiveCardService $service;

    public function __construct(?AtgFestiveCardService $service = null)
    {
        $this->service = $service ?? new AtgFestiveCardService();
    }

    /**
     * GET /member/festive-cards
     *
     * Renders the in-app Festivity & Celebration Cards Studio Creator.
     */
    /**
     * Creative Studio hub: single entry point for flyers, posters and festive cards.
     */
    public function hub(): void
    {
        $userId = $_SESSION['id'] ?? null;
        if (!$userId || !is_string($userId)) {
            header('Location: /login');
            exit;
        }

        view('member/creativeStudio');
    }

    public function index(): void
    {
        $userId = $_SESSION['id'] ?? null;
        if (!$userId || !is_string($userId)) {
            header('Location: /login');
            exit;
        }

        $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

        $pdo = Db::connect2();
        $userStmt = $pdo->prepare('SELECT id, firstName AS name, lastName, (SELECT img FROM profilePics WHERE id = personal.id LIMIT 1) as profile_img FROM personal WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $cards = $this->service->getFamilyCards($userId, $famCode, 20);

        view('member/festiveCardsStudio', [
            'user' => $user,
            'famCode' => $famCode,
            'occasions' => AtgFestiveCardService::OCCASIONS,
            'themes' => AtgFestiveCardService::THEMES,
            'templateStyles' => AtgFestiveCardService::TEMPLATE_STYLES,
            'moods' => AtgFestiveCardService::MOODS,
            'cards' => $cards,
        ]);
    }

    /**
     * POST /api/festive-cards/create
     *
     * Creates a new festive card, strips EXIF metadata from photo, and returns public share URL.
     */
    public function create(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

            $data = $_POST;

            // Never trust a client-supplied asset path; only a fresh upload may set the hero photo.
            unset($data['hero_photo_url'], $data['audio_voice_url']);

            // Optional hero photo: validated, auto-oriented, downscaled and re-encoded (purges all EXIF incl. GPS)
            $upload = $_FILES['hero_photo'] ?? null;
            if (is_array($upload) && ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $err = (int)($upload['error'] ?? UPLOAD_ERR_NO_FILE);
                if ($err !== UPLOAD_ERR_OK) {
                    $this->jsonError(422, in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                        ? 'That photo is too large. Please choose one under 12 MB.'
                        : 'The photo did not upload completely. Please try again.');
                    return;
                }
                if ((int)($upload['size'] ?? 0) > 12 * 1024 * 1024) {
                    $this->jsonError(422, 'That photo is too large. Please choose one under 12 MB.');
                    return;
                }
                $tmpPath = (string)($upload['tmp_name'] ?? '');
                if (!is_uploaded_file($tmpPath)) {
                    $this->jsonError(400, 'Invalid photo upload.');
                    return;
                }
                try {
                    $data['hero_photo_url'] = AtgFestiveCardService::storeHeroPhoto($tmpPath);
                    $data['hero_type'] = 'photo';
                } catch (\InvalidArgumentException $e) {
                    $this->jsonError(422, $e->getMessage());
                    return;
                }
            }

            try {
                $card = $this->service->createCard($userId, $famCode, $data);
            } catch (\InvalidArgumentException | \Src\Exceptions\ValidationException $e) {
                // Validation and content-safety rejections carry user-facing messages
                $this->jsonError(422, $e->getMessage());
                return;
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Festive card created successfully!',
                'data' => $card,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Src\Exceptions\UnauthorisedException $th) {
            $this->jsonError(403, 'Your session has expired. Please refresh the page and try again.');
        } catch (\Throwable $th) {
            error_log('[FestiveCardController::create] ' . $th->getMessage());
            $this->jsonError(500, 'We could not create your card just now. Please try again.');
        }
    }

    /**
     * POST /api/festive-cards/post-to-feed
     *
     * Cross-posts the card directly to the private family feed.
     */
    public function postToFeed(): void
    {
        try {
            CheckToken::tokenCheck();

            $userId = $_SESSION['id'] ?? null;
            if (!$userId || !is_string($userId)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                return;
            }

            $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';
            $token = trim((string)($_POST['share_token'] ?? ''));

            if ($token === '') {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Missing share token.']);
                return;
            }

            $result = $this->service->postCardToFeed($userId, $famCode, $token);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'data' => $result,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
        }
    }

    /**
     * GET /greetings/f/{token}
     *
     * Public recipient page displaying the 60fps Living Festive Envelope.
     *
     * @param string $token
     */
    public function publicView(string $token): void
    {
        $token = trim($token);
        $card = $this->service->getCardByToken($token);

        if (!$card) {
            http_response_code(404);
            view('errors.404');
            return;
        }

        // Increment anonymous view counter safely
        $this->service->recordCardView($token);

        header('X-Robots-Tag: noindex, nofollow');
        view('public/livingFestiveEnvelope', [
            'card' => $card,
            'occasion' => $card['occasion_meta'],
            'theme' => $card['theme_meta'],
            'templateStyle' => $card['template_style_meta'],
        ]);
    }

    /**
     * GET /greetings/i/{token}.jpg|.png
     *
     * 1200x630 link-preview image (WhatsApp, iMessage, Facebook, X) rendered by AtgFestiveCardRenderer.
     *
     * @param string $token
     * @param string $ext
     */
    public function renderCardImage(string $token, string $ext = 'png'): void
    {
        $token = preg_replace('/\.(png|jpe?g)$/i', '', trim($token)) ?? '';
        $card = $this->service->getCardByToken($token);

        if (!$card) {
            http_response_code(404);
            exit;
        }

        try {
            if (strtolower($ext) === 'jpg') {
                $this->sendImage(AtgFestiveCardRenderer::renderJpeg($card, 'og'), 'image/jpeg');
            }
            $this->sendImage(AtgFestiveCardRenderer::renderPng($card), 'image/png');
        } catch (\Throwable $th) {
            error_log('[FestiveCardController::renderCardImage] ' . $th->getMessage());
            // Fallback to SVG if GD fails
            $svg = AtgFestiveCardRenderer::renderSvg($card);
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=86400');
            echo $svg;
            exit;
        }
    }

    /**
     * GET /greetings/p/{token}.jpg
     *
     * 1080x1350 portrait keepsake for "Save card", native share-sheet files and the family feed.
     */
    public function renderPortraitImage(string $token): void
    {
        $card = $this->service->getCardByToken(trim($token));
        if (!$card) {
            http_response_code(404);
            exit;
        }

        try {
            $jpg = AtgFestiveCardRenderer::renderJpeg($card, 'portrait');
            if (isset($_GET['download'])) {
                $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower((string)$card['occasion_label'])), '-') ?: 'card';
                header('Content-Disposition: attachment; filename="familyplatform-' . $slug . '-card.jpg"');
            }
            $this->sendImage($jpg, 'image/jpeg');
        } catch (\Throwable $th) {
            error_log('[FestiveCardController::renderPortraitImage] ' . $th->getMessage());
            http_response_code(500);
            exit;
        }
    }

    private function sendImage(string $bytes, string $type): never
    {
        header('Content-Type: ' . $type);
        header('Cache-Control: public, max-age=604800, stale-while-revalidate=2592000');
        header('X-Content-Type-Options: nosniff');
        header('Content-Length: ' . strlen($bytes));
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            echo $bytes;
        }
        exit;
    }

    private function jsonError(int $code, string $message): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    }
}

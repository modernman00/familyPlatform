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
    public function index(): void
    {
        $userId = $_SESSION['id'] ?? null;
        if (!$userId || !is_string($userId)) {
            header('Location: /login');
            exit;
        }

        $famCode = is_string($_SESSION['famCode'] ?? null) ? $_SESSION['famCode'] : '';

        $pdo = Db::connect2();
        $userStmt = $pdo->prepare('SELECT id, name, lastName, (SELECT img FROM profilePics WHERE id = account.id LIMIT 1) as profile_img FROM account WHERE id = ? LIMIT 1');
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $cards = $this->service->getFamilyCards($userId, $famCode, 20);

        view('member/festiveCardsStudio', [
            'user' => $user,
            'famCode' => $famCode,
            'occasions' => AtgFestiveCardService::OCCASIONS,
            'themes' => AtgFestiveCardService::THEMES,
            'templateStyles' => AtgFestiveCardService::TEMPLATE_STYLES,
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

            // Handle optional hero photo upload with automated EXIF GPS scrubbing
            if (isset($_FILES['hero_photo']) && is_array($_FILES['hero_photo']) && ($_FILES['hero_photo']['error'] ?? 1) === UPLOAD_ERR_OK) {
                $tmpPath = (string)$_FILES['hero_photo']['tmp_name'];
                $imageInfo = @getimagesize($tmpPath);

                if ($imageInfo && in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    // Sanitize EXIF GPS immediately
                    AtgFestiveCardService::stripExifGps($tmpPath);

                    $ext = ($imageInfo['mime'] === 'image/png') ? 'png' : (($imageInfo['mime'] === 'image/webp') ? 'webp' : 'jpg');
                    $filename = 'festive_' . bin2hex(random_bytes(10)) . '.' . $ext;
                    $uploadDir = __DIR__ . '/../../../public/resources/images/festive_cards';

                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0755, true);
                    }

                    $destPath = $uploadDir . '/' . $filename;
                    if (@move_uploaded_file($tmpPath, $destPath)) {
                        $data['hero_photo_url'] = '/resources/images/festive_cards/' . $filename;
                        $data['hero_type'] = 'photo';
                    }
                }
            }

            $card = $this->service->createCard($userId, $famCode, $data);

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Festive card created successfully!',
                'data' => $card,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $th->getMessage()]);
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

        view('public/livingFestiveEnvelope', [
            'card' => $card,
            'occasion' => $card['occasion_meta'],
            'theme' => $card['theme_meta'],
            'templateStyle' => $card['template_style_meta'],
        ]);
    }

    /**
     * GET /greetings/i/{token}.png
     *
     * Dynamic OpenGraph image preview rendered via AtgFestiveCardRenderer.
     *
     * @param string $token
     */
    public function renderCardImage(string $token): void
    {
        $token = preg_replace('/\.png$/i', '', trim($token));
        $card = $this->service->getCardByToken($token);

        if (!$card) {
            http_response_code(404);
            exit;
        }

        try {
            $png = AtgFestiveCardRenderer::renderPng($card);
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=86400, stale-while-revalidate=604800');
            header('Content-Length: ' . strlen($png));
            echo $png;
            exit;
        } catch (\Throwable $th) {
            // Fallback to SVG if GD fails
            $svg = AtgFestiveCardRenderer::renderSvg($card);
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=86400');
            echo $svg;
            exit;
        }
    }
}

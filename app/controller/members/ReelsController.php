<?php
declare(strict_types=1);

namespace App\controller\members;

use App\controller\BaseController;
use App\model\Reel;
use App\classes\VideoParser;
use App\services\AnalyticsService;
use Src\functionality\SignIn;
use Exception;

final class ReelsController extends BaseController
{
    /**
     * Render the Full-Screen Immersive Family Reels View
     */
    public function index(): void
    {
        try {
            $targetReelId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $targetReel = null;
            if ($targetReelId > 0) {
                $targetReel = Reel::getReelById($targetReelId);
            }

            $baseUrl = rtrim((string)($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'https://myfamilyplatform.com'), '/');

            $ogMeta = null;
            if ($targetReel) {
                $creatorName = trim(($targetReel['firstName'] ?? 'Family') . ' ' . ($targetReel['lastName'] ?? ''));
                $title = "Family Reel by {$creatorName}";
                $description = !empty($targetReel['caption']) ? (string)$targetReel['caption'] : "Watch this family memory by {$creatorName} on Family Platform.";
                $rawThumb = !empty($targetReel['thumbnail_url']) ? (string)$targetReel['thumbnail_url'] : '';

                if (str_starts_with($rawThumb, 'http://') || str_starts_with($rawThumb, 'https://')) {
                    $image = $rawThumb;
                } elseif (!empty($rawThumb)) {
                    $image = $baseUrl . '/' . ltrim($rawThumb, '/');
                } elseif (!empty($targetReel['profilePics'])) {
                    $image = $baseUrl . '/resources/images/profile/' . basename((string)$targetReel['profilePics']);
                } else {
                    $image = $baseUrl . '/public/img/favicon/android-chrome-512x512.png';
                }

                $ogMeta = [
                    'title' => $title,
                    'description' => $description,
                    'image' => $image,
                    'url' => $baseUrl . '/reels?id=' . $targetReel['id'],
                ];
            }

            // Detect social bot/crawler scrapers for WhatsApp, iMessage, Facebook, Twitter, etc.
            $userAgent = strtolower((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $isBot = false;
            $botSignatures = [
                'whatsapp', 'facebookexternalhit', 'facebot', 'twitterbot',
                'applebot', 'slackbot', 'telegrambot', 'skypeuripreview',
                'linkedinbot', 'discordbot', 'pinterest', 'googlebot', 'bingbot'
            ];
            foreach ($botSignatures as $sig) {
                if (str_contains($userAgent, $sig)) {
                    $isBot = true;
                    break;
                }
            }

            // If crawler bot, serve instant OG HTML markup without requiring login session
            if ($isBot && $ogMeta) {
                header('Content-Type: text/html; charset=utf-8');
                $escTitle = htmlspecialchars($ogMeta['title'], ENT_QUOTES, 'UTF-8');
                $escDesc = htmlspecialchars($ogMeta['description'], ENT_QUOTES, 'UTF-8');
                $escImg = htmlspecialchars($ogMeta['image'], ENT_QUOTES, 'UTF-8');
                $escUrl = htmlspecialchars($ogMeta['url'], ENT_QUOTES, 'UTF-8');

                echo "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n" .
                     "    <meta charset=\"UTF-8\">\n" .
                     "    <title>{$escTitle}</title>\n" .
                     "    <meta name=\"description\" content=\"{$escDesc}\">\n" .
                     "    <meta property=\"og:type\" content=\"video.other\">\n" .
                     "    <meta property=\"og:site_name\" content=\"Family Platform\">\n" .
                     "    <meta property=\"og:title\" content=\"{$escTitle}\">\n" .
                     "    <meta property=\"og:description\" content=\"{$escDesc}\">\n" .
                     "    <meta property=\"og:image\" content=\"{$escImg}\">\n" .
                     "    <meta property=\"og:image:secure_url\" content=\"{$escImg}\">\n" .
                     "    <meta property=\"og:image:type\" content=\"image/jpeg\">\n" .
                     "    <meta property=\"og:image:width\" content=\"720\">\n" .
                     "    <meta property=\"og:image:height\" content=\"1280\">\n" .
                     "    <meta property=\"og:url\" content=\"{$escUrl}\">\n" .
                     "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n" .
                     "    <meta name=\"twitter:title\" content=\"{$escTitle}\">\n" .
                     "    <meta name=\"twitter:description\" content=\"{$escDesc}\">\n" .
                     "    <meta name=\"twitter:image\" content=\"{$escImg}\">\n" .
                     "</head>\n<body>\n" .
                     "    <h1>{$escTitle}</h1>\n" .
                     "    <p>{$escDesc}</p>\n" .
                     "    <img src=\"{$escImg}\" alt=\"Reel Thumbnail\">\n" .
                     "</body>\n</html>";
                exit;
            }

            SignIn::verify();

            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');

            $data = $this->membersData();
            $initialReels = Reel::getReelsFeed($userId, $famCode, 30, 0);

            // Track reel theater view
            AnalyticsService::track($userId, 'reel_theater_view', null, ['count' => count($initialReels)]);

            view('member/reels', [
                'data' => $data,
                'initialReels' => $initialReels,
                'totalReels' => count($initialReels),
                'ogMeta' => $ogMeta
            ]);
        } catch (\Throwable $th) {
            showError($th);
        }
    }

    /**
     * API: Get JSON Feed of Family Reels (Infinite Scroll)
     */
    public function getFeed(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            $reels = Reel::getReelsFeed($userId, $famCode, $limit, $offset);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload, never HTML-rendered.
            echo json_encode([
                'status' => 'success',
                'data' => $reels,
                'count' => count($reels)
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Upload & Publish a Family Reel
     */
    public function uploadReel(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $famCode = (string)($_SESSION['famCode'] ?? '');

            if (empty($userId) || empty($famCode)) {
                throw new Exception('Unauthorized user session', 401);
            }

            $caption = isset($_POST['caption']) ? trim((string)$_POST['caption']) : '';
            $category = isset($_POST['category']) ? trim((string)$_POST['category']) : 'milestone';
            $musicTitle = isset($_POST['music_title']) ? trim((string)$_POST['music_title']) : 'Original Family Audio';
            $videoUrlInput = isset($_POST['video_url']) ? trim((string)$_POST['video_url']) : '';
            $videoPath = '';
            $thumbnailPath = null;

            // Handle direct video file upload
            if (!empty($_FILES['video_file']['name'])) {
                $file = $_FILES['video_file'];
                // MIME => extension. The stored extension is derived from the
                // *sniffed* type only — never from the client-supplied filename
                // or $_FILES['type'] (both attacker-controlled), so an uploaded
                // .php can't land in the webroot with a .php name.
                $allowedMimes = [
                    'video/mp4'          => 'mp4',
                    'video/webm'         => 'webm',
                    'video/quicktime'    => 'mov',
                    'video/x-matroska'   => 'mkv',
                    'video/ogg'          => 'ogv',
                ];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = $finfo !== false ? finfo_file($finfo, $file['tmp_name']) : false;
                if ($finfo !== false) {
                    finfo_close($finfo);
                }

                if (!is_string($mimeType) || !isset($allowedMimes[$mimeType])) {
                    throw new Exception('Invalid video format. Supported formats: MP4, WebM, QuickTime (MOV).', 400);
                }

                if ($file['size'] > 100 * 1024 * 1024) { // 100MB limit
                    throw new Exception('Video file exceeds maximum allowed size (100MB).', 400);
                }

                $ext = $allowedMimes[$mimeType];
                $uniqueName = 'reel_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = __DIR__ . '/../../../resources/videos/reels/';

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $targetPath = $targetDir . $uniqueName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to save uploaded video on server.', 500);
                }

                $videoPath = '/resources/videos/reels/' . $uniqueName;
            } elseif (!empty($videoUrlInput)) {
                // Parse video URL (YouTube, Vimeo, Cloudflare Stream, or direct video)
                $videoData = VideoParser::parseVideoUrl($videoUrlInput);
                if (!$videoData) {
                    $videoPath = $videoUrlInput;
                } else {
                    $videoPath = $videoData['originalUrl'];
                    $thumbnailPath = $videoData['thumbnailUrl'];
                }
            } else {
                throw new Exception('Please provide a video file or a supported video URL.', 400);
            }

            // Check for client-generated canvas video thumbnail (Base64 JPEG)
            $thumbnailData = isset($_POST['thumbnail_data']) ? (string)$_POST['thumbnail_data'] : '';
            if (!empty($thumbnailData) && str_contains($thumbnailData, 'base64,')) {
                $rawBase64 = explode('base64,', $thumbnailData)[1];
                $decodedImg = base64_decode($rawBase64, true);
                // Confirm the bytes really are a raster image before writing.
                $imgInfo = is_string($decodedImg) ? @getimagesizefromstring($decodedImg) : false;
                $okThumb = is_array($imgInfo)
                    && in_array($imgInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)
                    && strlen((string) $decodedImg) <= 5 * 1024 * 1024;
                if ($okThumb) {
                    $thumbDir = __DIR__ . '/../../../resources/images/reels/thumbs/';
                    if (!is_dir($thumbDir)) {
                        mkdir($thumbDir, 0755, true);
                    }
                    $thumbName = 'thumb_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.jpg';
                    file_put_contents($thumbDir . $thumbName, $decodedImg);
                    $thumbnailPath = '/resources/images/reels/thumbs/' . $thumbName;
                }
            }

            // Create Reel Record
            $reelId = Reel::createReel([
                'user_id' => $userId,
                'famCode' => $famCode,
                'caption' => $caption,
                'video_url' => $videoPath,
                'thumbnail_url' => $thumbnailPath,
                'aspect_ratio' => '9:16',
                'category' => $category,
                'music_title' => $musicTitle
            ]);

            $createdReel = Reel::getReelById($reelId, $userId);

            // Track reel creation
            AnalyticsService::track($userId, 'reel_create', (string)$reelId, [
                'category' => $category,
                'has_upload' => !empty($_FILES['video_file']['name']),
            ]);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => 'Family Reel published successfully!',
                'data' => $createdReel
            ]);
        } catch (\Throwable $e) {
            http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Toggle Reaction on Reel (Like, Love, Celebrate)
     */
    public function toggleReaction(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $reelId = (int)($payload['reel_id'] ?? 0);
            $type = (string)($payload['reaction_type'] ?? 'like');

            if (!$reelId || empty($userId)) {
                throw new Exception('Invalid reel ID or user session', 400);
            }

            $result = Reel::toggleReaction($reelId, $userId, $type);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Get Reel Comments
     */
    public function getComments(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $reelId = isset($_GET['reel_id']) ? (int)$_GET['reel_id'] : 0;
            if (!$reelId) {
                throw new Exception('Invalid reel ID', 400);
            }

            $comments = Reel::getComments($reelId);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'data' => $comments,
                'count' => count($comments)
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Add Reel Comment
     */
    public function addComment(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;

            $reelId = (int)($payload['reel_id'] ?? 0);
            $commentText = trim((string)($payload['comment'] ?? ''));

            if (!$reelId || empty($userId) || empty($commentText)) {
                throw new Exception('Comment content, user session and reel ID are required', 400);
            }

            $newComment = Reel::addComment($reelId, $userId, $commentText);

            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request -- JSON API response (Content-Type: application/json); json_encode escapes the payload.
            echo json_encode([
                'status' => 'success',
                'message' => 'Comment added',
                'data' => $newComment
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Delete Reel (IDOR protected)
     */
    public function deleteReel(): void
    {
        SignIn::verify();
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true) ?? $_POST;
            $reelId = (int)($payload['reel_id'] ?? 0);

            if (!$reelId || empty($userId)) {
                throw new Exception('Invalid reel ID or user session', 400);
            }

            $deleted = Reel::deleteReel($reelId, $userId);
            if (!$deleted) {
                throw new Exception('You are not authorized to delete this reel or it does not exist.', 403);
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Reel deleted successfully.'
            ]);
        } catch (\Throwable $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

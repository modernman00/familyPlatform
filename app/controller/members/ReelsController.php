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
                'totalReels' => count($initialReels)
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
                $targetDir = __DIR__ . '/../../../public/resources/videos/reels/';

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $targetPath = $targetDir . $uniqueName;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to save uploaded video on server.', 500);
                }

                $videoPath = '/public/resources/videos/reels/' . $uniqueName;
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
                    $thumbDir = __DIR__ . '/../../../public/resources/images/reels/thumbs/';
                    if (!is_dir($thumbDir)) {
                        mkdir($thumbDir, 0755, true);
                    }
                    $thumbName = 'thumb_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $userId) . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.jpg';
                    file_put_contents($thumbDir . $thumbName, $decodedImg);
                    $thumbnailPath = '/public/resources/images/reels/thumbs/' . $thumbName;
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

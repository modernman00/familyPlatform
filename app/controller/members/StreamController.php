<?php
declare(strict_types=1);

namespace App\controller\members;

use App\services\CloudflareStreamService;
use Src\functionality\SignIn;

class StreamController
{
    private CloudflareStreamService $streamService;

    public function __construct()
    {
        $this->streamService = new CloudflareStreamService();
    }

    /**
     * Request a Direct Upload URL for video creation
     */
    public function getDirectUploadUrl(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                $verifyJWT = SignIn::verify();
                if (empty($verifyJWT['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    return;
                }
                $userId = (string)$verifyJWT['id'];
            }

            // Enforce CSRF token validation (Marcus SecOps Gate)
            \Src\CheckToken::tokenCheck();

            // Fast-Response Session Unlock: Release PHP session file lock before DB queries and cURL
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Check if member has Premium status
            $isPremium = false;
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare("SELECT type, is_premium FROM account WHERE id = ?");
                $stmt->execute([$userId]);
                $acc = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($acc && (!empty($acc['is_premium']) || ($acc['type'] ?? '') === '9090@' || ($acc['type'] ?? '') === 'premium')) {
                    $isPremium = true;
                }
            } catch (\Throwable $err) {
                error_log('[StreamController] DB error checking premium status: ' . $err->getMessage());
            }

            $rawInput = file_get_contents("php://input");
            $data = json_decode($rawInput !== false ? $rawInput : '', true);
            $data = is_array($data) ? $data : [];
            $orientation = isset($data['orientation']) && is_string($data['orientation']) ? $data['orientation'] : null;
            $capSeconds = CloudflareStreamService::resolveMaxDurationSeconds($orientation);
            $maxDuration = max(1, min((int) ($data['maxDuration'] ?? $capSeconds), $capSeconds));
            $requestedExpiry = isset($data['expirySeconds']) ? (int)$data['expirySeconds'] : 2592000;
            $useTus = !empty($data['useTus']);
            $uploadLength = isset($data['uploadLength']) && is_numeric($data['uploadLength']) ? (int)$data['uploadLength'] : 0;

            // Enforce Premium Tier: 1 Year (31536000s) and Permanent (0s) are restricted to Premium members
            if (!$isPremium && ($requestedExpiry === 0 || $requestedExpiry > 2592000)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => '1 Year and Permanent video retention is available exclusively for Premium members. Please upgrade your plan or select 30 days.'
                ]);
                return;
            }

            if ($useTus && $uploadLength > 0) {
                $result = $this->streamService->createTusUploadUrl($uploadLength, $maxDuration, $requestedExpiry, $orientation);
            } else {
                $result = $this->streamService->createDirectUploadUrl($maxDuration, $requestedExpiry, $orientation);
            }

            if (!empty($result['success'])) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                // Seamless fallback to Sovereign Local Video Auto-Trim & Upload endpoint
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'fallbackLocal' => true,
                    'tusSupported' => false,
                    'maxDuration' => $capSeconds,
                    'uploadUrl' => '/api/video/upload-file',
                    'providerNote' => $result['error'] ?? 'Using sovereign local video stream.'
                ]);
            }
        } catch (\Src\Exceptions\UnauthorisedException $ue) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or missing security token. Please refresh the page and try again.']);
        } catch (\Throwable $e) {
            error_log('[StreamController] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Internal server error processing video upload request.']);
        }
    }

    /**
     * Sovereign Video Upload & 30-Second Auto-Trim Endpoint (POST /api/video/upload-file)
     */
    public function uploadAndTrimVideo(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                $verifyJWT = SignIn::verify();
                if (!is_array($verifyJWT) || empty($verifyJWT['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required']);
                    return;
                }
                $userId = (string)$verifyJWT['id'];
            }

            // Enforce CSRF token validation
            \Src\CheckToken::tokenCheck();

            // Fast-Response Session Unlock: Release PHP session file lock before DB queries and cURL
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Check if member has Premium status for retention rules
            $isPremium = false;
            try {
                $db = \Src\Db::connect2();
                $stmt = $db->prepare("SELECT type, is_premium FROM account WHERE id = ?");
                $stmt->execute([$userId]);
                $acc = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($acc && (!empty($acc['is_premium']) || ($acc['type'] ?? '') === '9090@' || ($acc['type'] ?? '') === 'premium')) {
                    $isPremium = true;
                }
            } catch (\Throwable $err) {
                error_log('[StreamController::uploadAndTrimVideo] DB error checking premium status: ' . $err->getMessage());
            }

            $requestedExpiry = isset($_POST['expiry_seconds']) ? (int)$_POST['expiry_seconds'] : 2592000;
            if (!$isPremium && ($requestedExpiry === 0 || $requestedExpiry > 2592000)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => '1 Year and Permanent video retention is available exclusively for Premium members. Please upgrade your plan or select 30 days.'
                ]);
                return;
            }

            $file = $_FILES['video_file'] ?? ($_FILES['file'] ?? null);
            if (!is_array($file)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Please attach a valid video file (MP4, WebM, or MOV).']);
                return;
            }

            $orientation = isset($_POST['orientation']) && is_string($_POST['orientation']) ? $_POST['orientation'] : null;
            $capSeconds = (float)CloudflareStreamService::resolveMaxDurationSeconds($orientation);
            $trimStart = $_POST['trim_start'] ?? 0.0;
            $trimEnd = $_POST['trim_end'] ?? $capSeconds;
            $clientDuration = $_POST['client_duration'] ?? null;
            $uploadId = isset($_POST['upload_id']) && is_scalar($_POST['upload_id']) ? trim((string)$_POST['upload_id']) : '';
            $totalChunks = isset($_POST['total_chunks']) && is_numeric($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 1;
            $chunkIndex = isset($_POST['chunk_index']) && is_numeric($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : 0;

            if ($uploadId !== '' && $totalChunks > 1) {
                $result = \App\services\VideoAutoTrimService::processChunkedUpload(
                    file: $file,
                    userId: $userId,
                    uploadId: $uploadId,
                    chunkIndex: $chunkIndex,
                    totalChunks: $totalChunks,
                    trimStart: is_scalar($trimStart) ? (float)$trimStart : 0.0,
                    trimEnd: is_scalar($trimEnd) ? (float)$trimEnd : $capSeconds,
                    clientDuration: is_scalar($clientDuration) ? (float)$clientDuration : null,
                    maxDuration: $capSeconds
                );
            } else {
                $result = \App\services\VideoAutoTrimService::processUploadAndTrim(
                    file: $file,
                    userId: $userId,
                    trimStart: is_scalar($trimStart) ? (float)$trimStart : 0.0,
                    trimEnd: is_scalar($trimEnd) ? (float)$trimEnd : $capSeconds,
                    clientDuration: is_scalar($clientDuration) ? (float)$clientDuration : null,
                    maxDuration: $capSeconds
                );
            }

            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        } catch (\Src\Exceptions\UnauthorisedException $ue) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or missing security token. Please refresh the page and try again.']);
        } catch (\Src\Exceptions\ValidationException $ve) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $ve->getMessage()]);
        } catch (\Src\Exceptions\ForbiddenException $fe) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => $fe->getMessage()]);
        } catch (\Throwable $e) {
            error_log('[StreamController::uploadAndTrimVideo] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to process and auto-trim video clip.']);
        }
    }

    /**
     * Verify Cloudflare Stream Webhook-Signature header (`time=<unix>,sig1=<hex_hmac_sha256>`).
     * Enforces 5-minute replay window and constant-time comparison.
     */
    public static function verifyWebhookSignature(
        string $rawBody,
        string $signatureHeader,
        ?string $secret = null,
        ?int $now = null
    ): bool {
        $resolvedSecret = $secret ?? (string)($_ENV['CLOUDFLARE_STREAM_WEBHOOK_SECRET'] ?? $_SERVER['CLOUDFLARE_STREAM_WEBHOOK_SECRET'] ?? getenv('CLOUDFLARE_STREAM_WEBHOOK_SECRET') ?: '');
        if ($resolvedSecret === '' || trim($signatureHeader) === '') {
            return false;
        }

        $timestamp = null;
        $sig1 = null;
        foreach (explode(',', $signatureHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            $k = strtolower(trim($kv[0]));
            $v = trim($kv[1]);
            if ($k === 'time') {
                $timestamp = $v;
            } elseif ($k === 'sig1') {
                $sig1 = $v;
            }
        }

        if ($timestamp === null || $sig1 === null || !ctype_digit($timestamp) || $sig1 === '') {
            return false;
        }

        $currentTime = $now ?? time();
        if (abs($currentTime - (int)$timestamp) > 300) {
            return false;
        }

        $expectedSig = hash_hmac('sha256', $timestamp . '.' . $rawBody, $resolvedSecret);
        return hash_equals($expectedSig, strtolower($sig1));
    }

    /**
     * Cloudflare Stream Encoding Completion Webhook (POST /api/webhooks/stream).
     * Verifies HMAC-SHA256 signature, persists final hls_url, duration_sec, aspect_ratio,
     * and mirrored thumbnail.jpg?time=0s into post.media_meta.
     */
    public function handleWebhook(?string $rawInput = null, ?string $signatureHeader = null, ?\PDO $pdo = null): void
    {
        header('Content-Type: application/json');

        $rawBody = $rawInput ?? (file_get_contents('php://input') ?: '');
        $sigHeader = $signatureHeader ?? (string)($_SERVER['HTTP_WEBHOOK_SIGNATURE'] ?? '');

        if (!self::verifyWebhookSignature($rawBody, $sigHeader)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid or missing webhook signature.']);
            return;
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload) || empty($payload['uid']) || !is_string($payload['uid'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid Stream webhook payload.']);
            return;
        }

        $uid = preg_replace('/[^a-zA-Z0-9_-]/', '', $payload['uid']) ?: '';
        if ($uid === '' || !preg_match('/^[a-zA-Z0-9_-]{16,64}$/', $uid)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid Stream video UID.']);
            return;
        }

        $statusState = strtolower((string)($payload['status']['state'] ?? ''));
        $isReady = !empty($payload['readyToStream']) || $statusState === 'ready';
        if (!$isReady) {
            http_response_code(200);
            echo json_encode(['success' => true, 'ignored' => true, 'state' => $statusState !== '' ? $statusState : 'pending']);
            return;
        }

        $hlsUrl = '';
        if (!empty($payload['playback']['hls']) && is_string($payload['playback']['hls'])) {
            $hlsUrl = trim($payload['playback']['hls']);
        }
        if ($hlsUrl === '') {
            $hlsUrl = $this->streamService->buildHlsUrl($uid);
        }

        $durationSec = isset($payload['duration']) && is_numeric($payload['duration']) && (float)$payload['duration'] > 0
            ? round((float)$payload['duration'], 2)
            : null;

        $aspectRatio = null;
        if (
            isset($payload['input']['width'], $payload['input']['height'])
            && is_numeric($payload['input']['width'])
            && is_numeric($payload['input']['height'])
        ) {
            $aspectRatio = \App\classes\VideoParser::formatAspectRatio(
                (int)$payload['input']['width'],
                (int)$payload['input']['height']
            );
        }

        $rawThumb = !empty($payload['thumbnail']) && is_string($payload['thumbnail'])
            ? trim($payload['thumbnail'])
            : '';
        if ($rawThumb === '') {
            $rawSubdomain = (string)($_ENV['CLOUDFLARE_STREAM_SUBDOMAIN'] ?? $_SERVER['CLOUDFLARE_STREAM_SUBDOMAIN'] ?? getenv('CLOUDFLARE_STREAM_SUBDOMAIN') ?: '');
            $host = preg_replace('/[^a-zA-Z0-9.\-]/', '', $rawSubdomain) ?: 'videodelivery.net';
            $rawThumb = "https://{$host}/{$uid}/thumbnails/thumbnail.jpg?time=0s";
        } elseif (!str_contains($rawThumb, 'time=')) {
            $rawThumb .= (str_contains($rawThumb, '?') ? '&' : '?') . 'time=0s';
        }

        $mirroredPoster = \App\services\LinkPreviewService::mirrorThumbnail($rawThumb);

        $updatedPosts = 0;
        try {
            $db = $pdo ?? \Src\Db::connect2();
            $likeUid = '%' . $uid . '%';
            $stmt = $db->prepare(
                'SELECT post_no, media_meta FROM post WHERE postMessage LIKE ? OR post_img0 LIKE ? OR post_img1 LIKE ? OR post_img2 LIKE ? OR post_img3 LIKE ? OR post_img4 LIKE ? OR post_img5 LIKE ? OR media_meta LIKE ?'
            );
            $stmt->execute([$likeUid, $likeUid, $likeUid, $likeUid, $likeUid, $likeUid, $likeUid, $likeUid]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $updateStmt = $db->prepare('UPDATE post SET media_meta = ? WHERE post_no = ?');
            foreach ($rows as $row) {
                $existingMeta = \App\classes\VideoParser::normaliseMediaMeta($row['media_meta'] ?? null) ?? [];
                if ($hlsUrl !== '') {
                    $existingMeta['hls_url'] = $hlsUrl;
                }
                if ($durationSec !== null) {
                    $existingMeta['duration_sec'] = $durationSec;
                }
                if ($aspectRatio !== null) {
                    $existingMeta['aspect_ratio'] = $aspectRatio;
                }
                if ($mirroredPoster !== null) {
                    $existingMeta['poster_url'] = $mirroredPoster;
                }
                $normalised = \App\classes\VideoParser::normaliseMediaMeta($existingMeta);
                if ($normalised !== null) {
                    $encoded = json_encode($normalised, JSON_UNESCAPED_SLASHES);
                    if ($encoded !== false) {
                        $updateStmt->execute([$encoded, $row['post_no']]);
                        $updatedPosts++;
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('[StreamController::handleWebhook] DB Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to persist Stream webhook metadata.']);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'uid' => $uid,
            'updated_posts' => $updatedPosts,
            'hls_url' => $hlsUrl,
            'duration_sec' => $durationSec,
            'aspect_ratio' => $aspectRatio,
            'poster_url' => $mirroredPoster,
        ], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate or retrieve cached WebVTT closed captions / subtitles for a video clip.
     */
    public function generateSubtitles(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (string)($_SESSION['id'] ?? '');
            if ($userId === '') {
                $verifyJWT = SignIn::verify();
                if (empty($verifyJWT['id'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'error' => 'Authentication required.']);
                    return;
                }
                $userId = (string)$verifyJWT['id'];
            }

            \Src\CheckToken::tokenCheck();

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            if (\App\services\AnalyticsService::isRateLimited($userId, 'video_subtitles', 20, 60)) {
                http_response_code(429);
                echo json_encode(['success' => false, 'error' => 'Too many subtitle requests. Please wait a moment.']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $json = is_string($rawInput) && $rawInput !== '' ? json_decode($rawInput, true) : null;
            $src = is_array($json) && isset($json['src']) && is_string($json['src'])
                ? $json['src']
                : (isset($_POST['src']) && is_string($_POST['src']) ? $_POST['src'] : '');
            $lang = is_array($json) && isset($json['lang']) && is_string($json['lang'])
                ? $json['lang']
                : (isset($_POST['lang']) && is_string($_POST['lang']) ? $_POST['lang'] : 'en-GB');

            $result = \App\services\VideoSubtitleService::generateForVideo($src, $lang);
            http_response_code(200);
            // nosemgrep: php.lang.security.injection.echoed-request.echoed-request
            echo json_encode($result, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        } catch (\Src\Exceptions\UnauthorisedException $ue) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid or missing security token. Please refresh and try again.']);
        } catch (\Src\Exceptions\ValidationException $ve) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $ve->getMessage()]);
        } catch (\Throwable $e) {
            error_log('[StreamController::generateSubtitles] Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Unable to generate subtitles for this video.']);
        }
    }
}
